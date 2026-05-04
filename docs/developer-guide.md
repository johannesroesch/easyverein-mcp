# Entwickler-Leitfaden

Dieser Leitfaden richtet sich an Entwickler, die den EasyVerein MCP Server erweitern, testen oder in eigene Projekte integrieren möchten.

---

## Architektur

```
public/index.php          Slim 4 Entry Point — HTTP-Routing, Transport-Wiring
bin/mcp-stdio.php         stdio Entry Point

src/
├── McpServer.php         MCP-Protokoll, JSON-RPC Dispatch, Session-Management
├── ApiClient.php         cURL-Proxy zu EasyVerein API v3.0
├── HttpClientInterface.php  Abstraktion für HTTP-Calls (testbar)
├── CurlHttpClient.php    Produktiv-Implementierung via cURL
├── Logger.php            Strukturierter JSON-Logger (NDJSON → stderr)
├── PromptRegistry.php    15 geführte Workflows
├── AuthException.php     401/403-Fehler
├── ExitException.php     Ersatz für exit() — für testbaren Code
├── RateLimitException.php  HTTP 429 mit Retry-After
└── Tools/
    ├── MemberTools.php          ~23 Tools
    ├── ContactDetailsTools.php  ~20 Tools
    ├── EventTools.php           ~25 Tools
    ├── FinanceTools.php         ~52 Tools
    ├── ForumTools.php           ~15 Tools
    ├── PasscreatorTools.php     ~17 Tools
    ├── TaskTools.php            ~13 Tools (task, task-group, task-comment)
    ├── ProtocolTools.php        ~19 Tools (protocol, protocol-element, protocol-upload)
    ├── InventoryTools.php       ~16 Tools (inventory-object, lending)
    └── MiscTools.php            ~110 Tools
```

---

## Konventionen

### Tool-Klassen

Jede Tool-Klasse implementiert zwei Methoden:

```php
// Definitionen für tools/list (inputSchema) und resources/read (uri)
public function getDefinitions(): array { ... }

// Ausführung für tools/call
public function dispatch(string $name, array $params): string { ... }
```

**Definitionen** haben immer diese Felder:
```php
[
    'name'        => 'listMembers',
    'description' => 'Listet alle Vereinsmitglieder auf.',
    'required'    => ['token'],
    'props'       => [
        'token'  => ['type' => 'string', 'description' => '...'],
        'limit'  => ['type' => 'integer', 'description' => '...'],
        'offset' => ['type' => 'integer', 'description' => '...'],
    ],
]
```

Resources haben zusätzlich ein `uri`-Feld:
```php
'uri' => 'easyverein://member/{id}',
```

**Dispatch** gibt immer einen JSON-String zurück (direkt aus der EasyVerein API).

### Hyperlinked Relations (DRF)

EasyVerein verwendet Django REST Framework — Referenzfelder erwarten volle URLs, keine Integer-IDs.

```php
// Falsch:
$body['application_form'] = 42;

// Richtig:
$body['application_form'] = $this->client->urlRef('/application-form/', 42);
// → "https://easyverein.com/api/v3.0/application-form/42"
```

`bodyFrom()` unterstützt automatische Konvertierung via `$urlFields`:
```php
private function bodyFrom(array $p, array $fields, array $urlFields = []): string
```

### Optionale Parameter

Optionale Parameter werden **nur** in den Request-Body aufgenommen, wenn sie im `$p`-Array vorhanden sind:

```php
foreach ($fields as $field) {
    if (array_key_exists($field, $p)) {
        $body[$field] = $p[$field];
    }
}
```

Kein `null` im Body — EasyVerein interpretiert `null` als „Feld auf null setzen".

---

## Neues Tool hinzufügen

1. Passende Tool-Klasse wählen (oder neue erstellen).
2. In `getDefinitions()` einen neuen Eintrag hinzufügen.
3. In `dispatch()` einen neuen `case` hinzufügen.
4. Bei neuer Klasse: in `McpServer::__construct()` instanziieren und zum `$toolClasses`-Array hinzufügen.

Beispiel:

```php
// In getDefinitions():
[
    'name'        => 'getMyEntity',
    'description' => 'Liest eine Entität.',
    'required'    => ['token', 'id'],
    'props'       => [
        'token' => ['type' => 'string', 'description' => 'EasyVerein API-Token'],
        'id'    => ['type' => 'integer', 'description' => 'Entitäts-ID'],
    ],
],

// In dispatch():
'getMyEntity' => $this->client->get($p['token'], '/my-entity/' . $p['id']),
```

---

## Neue Resource hinzufügen

Resources sind read-only und werden über URIs adressiert. Eintrag in `getDefinitions()`:

```php
// Einzelobjekt:
[
    'name'        => 'myEntityResource',
    'description' => 'Liest eine Entität.',
    'required'    => ['token'],
    'props'       => [...],
    'uri'         => 'easyverein://my-entity/{id}',
],

// Liste (URI-Template):
[
    'name'        => 'myEntityListResource',
    'description' => 'Listet Entitäten auf.',
    'required'    => ['token'],
    'props'       => [...],
    'uri'         => 'easyverein://my-entity{?limit,page}',
],
```

Der `McpServer` leitet `resources/read`-Anfragen automatisch an `dispatch()` weiter.

---

## Testing

### Infrastruktur

```bash
composer install           # dev-deps: phpunit/phpunit
php vendor/bin/phpunit     # alle Tests
php vendor/bin/phpunit --testsuite Unit
php vendor/bin/phpunit --testsuite Integration
php vendor/bin/phpunit --coverage-text
```

### MockHttpClient

`ApiClient` akzeptiert ein `HttpClientInterface` im Konstruktor. Tests injizieren einen Mock:

```php
$mock = new MockHttpClient([
    ['body' => '{"results":[]}', 'statusCode' => 200, 'headers' => []],
]);
$client = new ApiClient('https://example.com/api/v3.0', $mock);
```

`MockHttpClient` steht in `tests/MockHttpClient.php` und implementiert `HttpClientInterface`:

### ExitException

`callTool()` wirft `ExitException` statt `exit()` aufzurufen (für die Elicitation-Flows). Tests fangen diese:

```php
try {
    $result = $mcp->processMessage($token, $json);
} catch (ExitException) {
    // erwartet — Elicitation abgebrochen
}
```

---

## Transporte im Detail

### Streamable HTTP

`handleStreamablePost()` in `McpServer`:
1. Origin-Check (nur localhost/127.0.0.1)
2. Session laden oder neu anlegen
3. Token aus Bearer-Header extrahieren
4. JSON-RPC dispatchen
5. Response als JSON zurückgeben

### SSE (Legacy)

`handleSse()` + `handleMessage()`:
- `handleSse()` sendet `endpoint`-Event mit `/messages?session=<id>` und wartet
- `handleMessage()` empfängt JSON-RPC, verarbeitet, schreibt SSE-Event in Session

### stdio

`handleStdio()`:
- Liest von STDIN (newline-delimited JSON)
- Schreibt nach STDOUT
- Token aus `EASYVEREIN_TOKEN` env var
- Keine Sessions, keine Elicitation

---

## Rate Limiting

`ApiClient` behandelt HTTP 429 automatisch:
1. Erster 429: `sleep(min(Retry-After, 60))`, dann Retry
2. Zweiter 429: wirft `RateLimitException`

`McpServer::callTool()` fängt `RateLimitException` und gibt `{isError: true}` zurück.

---

## Annotations

`McpServer` setzt automatisch Tool-Annotations basierend auf dem Tool-Namen:

| Präfix | `readOnlyHint` | `destructiveHint` | `idempotentHint` |
|--------|---------------|------------------|-----------------|
| `list*` / `get*` | `true` | `false` | `true` |
| `create*` | `false` | `false` | `false` |
| `update*` | `false` | `false` | `true` |
| `delete*` | `false` | `true` | `true` |
| `cancellation` | `false` | `true` | `false` |

---

## Verzeichnisstruktur

```
easyverein-mcp/
├── bin/
│   └── mcp-stdio.php          stdio Entry Point
├── docs/                      Dokumentation
├── public/
│   └── index.php              HTTP Entry Point (Slim 4)
├── src/                       PHP-Quellcode
├── tests/
│   ├── bootstrap.php
│   ├── Fixtures/              JSON-Fixtures für Tests
│   ├── Unit/                  Unit-Tests
│   └── Integration/           Integrations-Tests (McpServer)
├── vendor/                    Composer-Dependencies
├── .env.example
├── composer.json
└── phpunit.xml
```
