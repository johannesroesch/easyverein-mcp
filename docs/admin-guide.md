# Administrator-Leitfaden

Dieser Leitfaden richtet sich an Systemadministratoren, die den EasyVerein MCP Server installieren, konfigurieren und betreiben.

---

## Voraussetzungen

| Komponente | Mindestversion |
|-----------|---------------|
| PHP | 8.2 |
| Composer | 2.x |
| Erweiterungen | `curl`, `json`, `session` |

Für den integrierten PHP-Dev-Server wird kein Webserver benötigt. Für den Produktivbetrieb empfiehlt sich Nginx + PHP-FPM oder Apache + mod_php.

---

## Installation

```bash
git clone <repo-url> easyverein-mcp
cd easyverein-mcp
composer install --no-dev --optimize-autoloader
cp .env.example .env
```

Dann `.env` anpassen:

```ini
EASYVEREIN_BASE_URL=https://easyverein.com/api/v3.0
LOG_LEVEL=INFO
```

---

## Starten

### Entwicklung (PHP Built-in Server)

```bash
PHP_CLI_SERVER_WORKERS=4 php -S localhost:8080 -t public/
```

> **Wichtig:** `PHP_CLI_SERVER_WORKERS=4` ist für die Elicitation-Funktion (Lösch-Bestätigung) erforderlich. Mit nur einem Worker kann die Bestätigung des Nutzers nicht empfangen werden, während der Server auf sie wartet.

### Produktion (Nginx + PHP-FPM)

Beispiel-Nginx-Konfiguration:

```nginx
server {
    listen 443 ssl;
    server_name mcp.example.com;

    root /var/www/easyverein-mcp/public;
    index index.php;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        # SSE braucht kein Buffering
        fastcgi_buffering off;
    }

    ssl_certificate     /etc/ssl/certs/mcp.example.com.crt;
    ssl_certificate_key /etc/ssl/private/mcp.example.com.key;
}
```

`.env` im Projektwurzelverzeichnis liegt **außerhalb** des Document Roots (`public/`) und ist damit nicht erreichbar.

---

## Umgebungsvariablen

| Variable | Standard | Beschreibung |
|----------|----------|--------------|
| `EASYVEREIN_BASE_URL` | `https://easyverein.com/api/v3.0` | API-Basis-URL |
| `LOG_LEVEL` | `INFO` | `DEBUG` / `INFO` / `WARNING` / `ERROR` |

---

## Logging

Der Server schreibt strukturierte JSON-Logs (NDJSON) nach `stderr`. Jede Zeile hat folgende Felder:

```json
{"ts":"2025-10-01T12:00:00+00:00","level":"INFO","msg":"Tool called","req":"a1b2c3d4","transport":"http","sess":"014bb7","name":"listMembers","ms":42}
```

| Feld | Beschreibung |
|------|-------------|
| `ts` | ISO 8601 Zeitstempel |
| `level` | `DEBUG`, `INFO`, `WARNING`, `ERROR` |
| `msg` | Meldungstext |
| `req` | 8-stellige Hex-ID pro Anfrage |
| `transport` | `http`, `sse` oder `stdio` |
| `sess` | 6-stelliger MD5-Hash der Session-ID (nicht umkehrbar) |

**Niemals geloggt** (Datenschutz):
- Vollständige API-Tokens (nur Hint: erste 5 Zeichen + `...`)
- Session-IDs im Klartext
- Request-/Response-Bodies (können Personendaten enthalten)
- Tool-Argumente

Logs auf stderr umleiten:
```bash
php -S localhost:8080 -t public/ 2>> /var/log/easyverein-mcp.log
```

---

## Sicherheit

### Token-Modell

Der Server **speichert keine** EasyVerein-Tokens. Jede Anfrage muss einen gültigen Token mitbringen:

- HTTP: `Authorization: Bearer <token>`
- stdio: `EASYVEREIN_TOKEN` Umgebungsvariable

Der Token wird direkt an die EasyVerein API weitergeleitet und nach der Anfrage verworfen.

### CORS / Origin-Prüfung

Für den Streamable-HTTP-Transport wird der `Origin`-Header geprüft. Erlaubt sind nur:
- `localhost` (alle Ports)
- `127.0.0.1`
- `::1`
- Anfragen ohne `Origin`-Header

Externe Domains werden mit HTTP 403 abgewiesen. Das schützt vor CSRF-Angriffen über den Browser.

### HTTPS empfohlen

Im Produktivbetrieb sollte der Server hinter einem HTTPS-Reverse-Proxy betrieben werden. API-Tokens werden sonst im Klartext übertragen.

### Dateiberechtigungen

```bash
chmod 600 .env          # Nur Webserver-User lesen
chmod -R 755 public/    # Web-zugänglich
chmod -R 750 src/ bin/  # Nicht Web-zugänglich
```

---

## Sessions

Elicitation (Lösch-Bestätigungen) nutzt PHP-Sessions. Standard-Konfiguration:

- Session-Handler: `files` (Standard)
- Session-Verzeichnis: `/tmp` oder `session.save_path` in `php.ini`

Für den Dev-Server mit `PHP_CLI_SERVER_WORKERS=4` teilen sich alle Worker dasselbe Dateisystem, sodass Sessions über Worker hinweg lesbar sind.

Für Produktionsumgebungen mit mehreren PHP-FPM-Workern ist ein gemeinsamer Session-Store erforderlich (z. B. Redis):

```ini
session.save_handler = redis
session.save_path    = "tcp://localhost:6379"
```

---

## Updates

```bash
git pull
composer install --no-dev --optimize-autoloader
```

Bei Composer-Updates:
```bash
composer update --no-dev
```

---

## Gesundheitsprüfung

```bash
curl -s http://localhost:8080/.well-known/mcp | jq .
```

Erwartete Ausgabe enthält `name`, `version`, `tools`, `resources`, `prompts`.

```bash
curl -s http://localhost:8080/ -H "Accept: application/json" | jq .tools
# → 176
```
