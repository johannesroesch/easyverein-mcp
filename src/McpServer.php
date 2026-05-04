<?php

declare(strict_types=1);

namespace EasyVerein\Mcp;

use EasyVerein\Mcp\Tools\ContactDetailsTools;
use EasyVerein\Mcp\Tools\EventTools;
use EasyVerein\Mcp\Tools\FinanceTools;
use EasyVerein\Mcp\Tools\ForumTools;
use EasyVerein\Mcp\Tools\MemberTools;
use EasyVerein\Mcp\Tools\MiscTools;
use EasyVerein\Mcp\Tools\PasscreatorTools;

class McpServer
{
    private const PROTOCOL_VERSION   = '2025-11-25';
    private const SUPPORTED_VERSIONS = ['2025-11-25', '2025-03-26'];
    private const ALLOWED_ORIGINS    = ['localhost', '127.0.0.1', '::1'];

    private const INSTRUCTIONS = <<<'TEXT'
Du bist mit dem EasyVerein MCP Server verbunden. EasyVerein ist eine deutschsprachige Vereinsverwaltungssoftware.

## Authentifizierung
Alle tool/call- und resources/read-Anfragen erfordern einen EasyVerein API-Token.
Der Token wird vom Client via Authorization: Bearer-Header übermittelt und automatisch weitergeleitet.
Du musst den Token nicht selbst verwalten oder nachfragen.

## Resources vs. Tools
- Resources (resources/read): ausschließlich lesend. URIs nach Schema easyverein://entity/{id} (Einzelobjekt) oder easyverein://entity/{?limit,page} (Liste).
- Tools: schreibende Operationen (create / update / delete).
Bevorzuge Resources für Lesezugriffe, da sie cachebar sind.

## Pagination
Alle list-Resources und list-Tools unterstützen die Parameter `limit` (Seitengröße) und `page` (Seitennummer, ab 1).
Ist im Response `next` nicht null, gibt es weitere Seiten — rufe dieselbe Resource/dasselbe Tool mit `page=current+1` auf.

## Token-Erneuerung
Die EasyVerein API sendet den Header `token_refresh_needed: True`, wenn der aktuelle Token bald abläuft.
Der Server erkennt diesen Header automatisch und sendet eine Warnung als Log-Notification.
Wenn du diese Warnung siehst:
1. Rufe das Tool `refreshToken` auf — es gibt den neuen Token im Feld `Bearer` zurück.
2. Weise den Nutzer an, den alten Token in seiner Client-Konfiguration durch den neuen zu ersetzen
   (z. B. in claude_desktop_config.json, .mcp.json oder der jeweiligen MCP-Einstellung).

## Löschoperationen (Elicitation)
delete*-Tools lösen beim Nutzer eine Bestätigungsabfrage aus, bevor die Aktion ausgeführt wird.
Kündige Löschvorgänge im Chat an, damit der Nutzer vorbereitet ist. Warte auf das Ergebnis,
bevor du weitere Aktionen planst — der Nutzer kann abbrechen.

## Prompts
Für häufige Workflows stehen vordefinierte Prompts bereit (member-overview, member-search,
member-onboard, open-invoices, monthly-bookings, invoice-for-member, upcoming-events,
event-participants, club-summary, pending-tasks, finance-summary, member-birthday,
event-create, inventory-overview, forum-overview). Nutze diese bevorzugt statt mehrere
Tools manuell zu kombinieren.

## Fachdomänen
- Mitglieder: member, member-group, member-group-assignment, member-custom-field-assignment, member-custom-field-assignment-change-request, former-member-data, anniversary-mailing
- Kontakt: contact-details, contact-details-group, contact-details-change-request, contact-details-custom-field-assignment, contact-details-log
- Veranstaltungen: event, participation, participation-price-group, event-custom-field-assignment, application-form, application-form-element
- Finanzen: booking, invoice, invoice-item, bank-account, billing-account, booking-project, debit-order, debit-collection, custom-tax-rate, payment-method, discount-code
- Forum: forum, topic, post
- Aufgaben: task, task-group, task-comment
- Protokolle: protocol, protocol-element, protocol-element-comment, protocol-upload
- Inventar: inventory-object, inventory-object-group, inventory-object-custom-field-assignment, lending
- Abstimmungen: voting, voting-question
- Kalender & Orte: calendar, location
- Dokumente: document-template, document-template-settings, page-template
- Verwaltung: custom-field, custom-field-collection, custom-filter, session-filter, notification-log, wastebasket, accounting-plan
- Organisation: organization, organization-settings, price-group, website, chairman-level, chairman-note, chairman-tutorial, article-object
- Passcreator: pass, pass-field, pass-template, passcreator-integration
- Integration & Auth: organization-token, oauth-credentials, oauth2-application, oauth2-custom-claim, smtp-email-setting, chat-settings, public-chat-room, file-system-path, update-highlight, update-highlight-entry, dosb-sport, lsb-sport, select-option, community-function-feedback, feature-request
TEXT;

    private array         $tools            = [];
    private array         $toolHandlers     = [];
    private array         $resources        = [];
    private array         $resourceHandlers = [];
    private array         $resourcesByPath  = [];
    private string        $requestToken     = '';
    private PromptRegistry $prompts;
    private ApiClient     $client;
    private array         $pendingNotifications = [];
    private string        $clientLogLevel       = 'info';
    private bool          $clientSupportsElicitation = false;

    public function __construct(ApiClient $client)
    {
        $this->client  = $client;
        $this->prompts = new PromptRegistry();

        foreach ([
            new MemberTools($client),
            new ContactDetailsTools($client),
            new EventTools($client),
            new FinanceTools($client),
            new ForumTools($client),
            new MiscTools($client),
            new PasscreatorTools($client),
        ] as $toolClass) {
            foreach ($toolClass->getDefinitions() as $def) {
                if (isset($def['uri'])) {
                    $this->resources[$def['name']]        = $def;
                    $this->resourceHandlers[$def['name']] = $toolClass;
                    $this->indexResourceByPath($def);
                } else {
                    $this->tools[$def['name']]        = $def;
                    $this->toolHandlers[$def['name']] = $toolClass;
                }
            }
        }
    }

    public function getToolNames(): array
    {
        return array_keys($this->tools);
    }

    public function getResourceNames(): array
    {
        return array_keys($this->resources);
    }

    public function getPromptCount(): int
    {
        return $this->prompts->count();
    }

    // -------------------------------------------------------------------------
    // Streamable HTTP transport (spec 2025-11-25)
    // -------------------------------------------------------------------------

    /**
     * POST /mcp — Returns ['status', 'headers', 'body'].
     */
    public function handleStreamablePost(string $sessionId, string $protocolVersion, string $origin, string $authHeader, string $acceptHeader, string $rawBody): array
    {
        Logger::newRequest('http');
        if ($sessionId !== '') {
            Logger::setSession($sessionId);
        }
        $this->requestToken = $this->parseBearerToken($authHeader);

        if ($error = $this->validateOrigin($origin)) {
            return $error;
        }

        $req = json_decode($rawBody, true);
        if (!is_array($req) || array_is_list($req)) {
            $msg = !is_array($req)
                ? 'Parse error: ' . json_last_error_msg()
                : 'Batch requests are not supported';
            $code = !is_array($req) ? -32700 : -32600;
            Logger::warning('Invalid request', ['error' => $msg]);
            return [
                'status'  => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => $this->errorResponse(null, $code, $msg),
            ];
        }

        // Elicitation response: no "method", has "id" + "result" → store for polling
        if (!isset($req['method']) && isset($req['result']) && isset($req['id'])) {
            if ($sessionId) {
                $this->resumeSession($sessionId);
                $_SESSION['elicitation_response_' . $req['id']] = $req['result'];
                session_write_close();
                Logger::info('Elicitation response stored', ['req_id' => $req['id'], 'result' => json_encode($req['result'])]);
            }
            return ['status' => 200, 'headers' => [], 'body' => ''];
        }

        if (($req['jsonrpc'] ?? '') !== '2.0') {
            Logger::warning('Invalid JSON-RPC version', ['jsonrpc' => $req['jsonrpc'] ?? null]);
            return [
                'status'  => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => $this->errorResponse($req['id'] ?? null, -32600, 'Invalid Request: jsonrpc must be "2.0"'),
            ];
        }

        if (!isset($req['method'])) {
            Logger::warning('Missing method field');
            return [
                'status'  => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => $this->errorResponse($req['id'] ?? null, -32600, 'Invalid Request: method field required'),
            ];
        }

        $id     = $req['id']     ?? null;
        $method = $req['method'];
        $params = $req['params'] ?? [];

        Logger::debug('Request received', ['transport' => 'streamable', 'method' => $method, 'token' => Logger::tokenHint($this->requestToken)]);

        try {
            if ($method === 'initialize') {
                $newSessionId = $this->startNewSession($params);
                Logger::info('Session initialized', ['transport' => 'streamable']);
                return [
                    'status'  => 200,
                    'headers' => ['Content-Type' => 'application/json', 'MCP-Session-Id' => $newSessionId],
                    'body'    => $this->respond($id, $this->initializeResult()),
                ];
            }

            if ($versionError = $this->validateProtocolVersion($protocolVersion)) {
                return $versionError;
            }

            if ($id === null) {
                return ['status' => 202, 'headers' => [], 'body' => ''];
            }

            if (in_array($method, ['tools/list', 'resources/list', 'resources/templates/list', 'prompts/list', 'logging/setLevel', 'ping', 'completion/complete'], true)) {
                Logger::info('Stateless request', ['method' => $method]);
                return [
                    'status'  => 200,
                    'headers' => ['Content-Type' => 'application/json'],
                    'body'    => $this->dispatchStateless($id, $method, $params),
                ];
            }

            if ($sessionId) {
                $this->resumeSession($sessionId);
            }

            $result = match ($method) {
                'tools/call'     => $this->callTool($params, $id),
                'resources/read' => $this->safeReadResource($params['uri'] ?? ''),
                'prompts/get'    => $this->safeGetPrompt($params),
                default          => null,
            };

            if ($sessionId) {
                session_write_close();
            }

            if ($result === null) {
                return [
                    'status'  => 200,
                    'headers' => ['Content-Type' => 'application/json'],
                    'body'    => $this->unknownMethod($id, $method),
                ];
            }

            if (!empty($this->pendingNotifications) && str_contains($acceptHeader, 'text/event-stream')) {
                return $this->sseResponse($id, $result);
            }

            return [
                'status'  => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => $this->respond($id, $result),
            ];
        } catch (AuthException $e) {
            Logger::warning('Authentication failed', ['error' => $e->getMessage()]);
            return [
                'status'  => 401,
                'headers' => ['Content-Type' => 'application/json', 'WWW-Authenticate' => 'Bearer'],
                'body'    => json_encode(['error' => $e->getMessage()]),
            ];
        } catch (\InvalidArgumentException $e) {
            Logger::warning('Invalid params', ['method' => $method, 'error' => $e->getMessage()]);
            return [
                'status'  => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => $this->errorResponse($id, -32602, 'Invalid params: ' . $e->getMessage()),
            ];
        } catch (ExitException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Logger::error('Unhandled exception in streamable handler', [
                'method' => $method,
                'class'  => get_class($e),
                'error'  => $e->getMessage(),
                'file'   => $e->getFile() . ':' . $e->getLine(),
            ]);
            return [
                'status'  => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => $this->errorResponse($id, -32603, 'Internal error'),
            ];
        }
    }

    /**
     * DELETE /mcp — terminates a session.
     */
    public function handleStreamableDelete(string $sessionId, string $origin): array
    {
        if ($error = $this->validateOrigin($origin)) {
            return $error;
        }

        if (!$sessionId) {
            return ['status' => 400, 'headers' => [], 'body' => ''];
        }

        if ($this->resumeSession($sessionId)) {
            session_destroy();
        }

        return ['status' => 200, 'headers' => [], 'body' => ''];
    }

    // -------------------------------------------------------------------------
    // Legacy HTTP+SSE transport (spec 2024-11-05, kept for backwards compat)
    // -------------------------------------------------------------------------

    public function handleSse(string $sessionId): void
    {
        set_time_limit(0);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ini_set('output_buffering', 'off');
        ini_set('zlib.output_compression', 'off');

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        header('Connection: keep-alive');

        $base     = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        $endpoint = $base . '/messages?session=' . $sessionId;

        $this->sendEvent('endpoint', $endpoint);
        flush();

        while (true) {
            $this->sendEvent('ping', '');
            flush();
            sleep(15);
        }
    }

    public function handleMessage(string $authHeader, string $rawBody): string
    {
        Logger::newRequest('sse');
        $this->requestToken = $this->parseBearerToken($authHeader);

        $request = json_decode($rawBody, true);
        if (!is_array($request) || array_is_list($request)) {
            $msg  = !is_array($request) ? 'Parse error: ' . json_last_error_msg() : 'Batch requests are not supported';
            $code = !is_array($request) ? -32700 : -32600;
            Logger::warning('Invalid request', ['error' => $msg]);
            return $this->errorResponse(null, $code, $msg);
        }

        if (($request['jsonrpc'] ?? '') !== '2.0') {
            Logger::warning('Invalid JSON-RPC version', ['jsonrpc' => $request['jsonrpc'] ?? null]);
            return $this->errorResponse($request['id'] ?? null, -32600, 'Invalid Request: jsonrpc must be "2.0"');
        }

        if (!isset($request['method'])) {
            Logger::warning('Missing method field');
            return $this->errorResponse($request['id'] ?? null, -32600, 'Invalid Request: method field required');
        }

        $id     = $request['id']     ?? null;
        $method = $request['method'];
        $params = $request['params'] ?? [];

        Logger::debug('Request received', ['transport' => 'sse', 'method' => $method, 'token' => Logger::tokenHint($this->requestToken)]);

        try {
            return match ($method) {
                'initialize'                => $this->respond($id, $this->initializeLegacy($params)),
                'tools/list'                => $this->respond($id, $this->toolListResult($params)),
                'tools/call'                => $this->respond($id, $this->callTool($params)),
                'resources/list'            => $this->respond($id, $this->resourceListResult()),
                'resources/templates/list'  => $this->respond($id, $this->resourceTemplateListResult()),
                'resources/read'            => $this->respond($id, $this->safeReadResource($params['uri'] ?? '')),
                'prompts/list'              => $this->respond($id, $this->prompts->list()),
                'prompts/get'               => $this->respond($id, $this->safeGetPrompt($params)),
                'logging/setLevel'          => $this->respond($id, $this->handleLoggingSetLevel($params)),
                'completion/complete'       => $this->respond($id, $this->handleCompletion($params)),
                'ping'                      => $this->respond($id, new \stdClass()),
                'notifications/initialized' => $this->respond($id, []),
                default                     => $this->unknownMethod($id, $method),
            };
        } catch (AuthException $e) {
            Logger::warning('Authentication failed', ['error' => $e->getMessage()]);
            return $this->errorResponse($id, -32001, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            Logger::warning('Invalid params', ['method' => $method, 'error' => $e->getMessage()]);
            return $this->errorResponse($id, -32602, 'Invalid params: ' . $e->getMessage());
        } catch (\Throwable $e) {
            Logger::error('Unhandled exception in SSE handler', [
                'method' => $method,
                'class'  => get_class($e),
                'error'  => $e->getMessage(),
                'file'   => $e->getFile() . ':' . $e->getLine(),
            ]);
            return $this->errorResponse($id, -32603, 'Internal error');
        }
    }

    /**
     * stdio transport (MCP spec 2025-11-25).
     * Reads newline-delimited JSON-RPC from STDIN, writes responses to STDOUT.
     * Token is taken from the EASYVEREIN_TOKEN env variable.
     * Runs until STDIN is closed.
     */
    public function handleStdio(): void
    {
        $this->requestToken = $_ENV['EASYVEREIN_TOKEN'] ?? '';
        $this->clientSupportsElicitation = false; // no SSE stream available

        while (($line = fgets(STDIN)) !== false) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            Logger::newRequest('stdio');

            $req = json_decode($line, true);
            if (!is_array($req) || array_is_list($req)) {
                $this->stdioWrite($this->errorResponse(null, -32700, 'Parse error: ' . json_last_error_msg()));
                continue;
            }

            if (($req['jsonrpc'] ?? '') !== '2.0') {
                $this->stdioWrite($this->errorResponse($req['id'] ?? null, -32600, 'Invalid Request: jsonrpc must be "2.0"'));
                continue;
            }

            $id     = $req['id']     ?? null;
            $method = $req['method'] ?? '';
            $params = $req['params'] ?? [];

            Logger::debug('Request received', ['method' => $method, 'token' => Logger::tokenHint($this->requestToken)]);

            // Notifications have no id — process silently, no response
            if (!isset($req['id'])) {
                continue;
            }

            try {
                $result = match ($method) {
                    'initialize'               => $this->initializeResult(),
                    'tools/list'               => $this->toolListResult($params),
                    'tools/call'               => $this->callTool($params),
                    'resources/list'           => $this->resourceListResult(),
                    'resources/templates/list' => $this->resourceTemplateListResult(),
                    'resources/read'           => $this->safeReadResource($params['uri'] ?? ''),
                    'prompts/list'             => $this->prompts->list(),
                    'prompts/get'              => $this->safeGetPrompt($params),
                    'logging/setLevel'         => $this->handleLoggingSetLevel($params),
                    'completion/complete'      => $this->handleCompletion($params),
                    'ping'                     => new \stdClass(),
                    default                    => null,
                };

                if ($result === null) {
                    Logger::warning('Unknown method', ['method' => $method]);
                    $this->stdioWrite($this->errorResponse($id, -32601, 'Method not found: ' . $method));
                } else {
                    $this->stdioWrite($this->respond($id, $result));
                }
            } catch (ExitException $e) {
                throw $e;
            } catch (AuthException $e) {
                Logger::warning('Authentication failed', ['error' => $e->getMessage()]);
                $this->stdioWrite($this->errorResponse($id, -32001, $e->getMessage()));
            } catch (RateLimitException $e) {
                Logger::warning('Rate limit on tool call', ['retry_after' => $e->getRetryAfter()]);
                $this->stdioWrite($this->respond($id, [
                    'content' => [['type' => 'text', 'text' => $e->getMessage()]],
                    'isError' => true,
                ]));
            } catch (\InvalidArgumentException $e) {
                Logger::warning('Invalid params', ['method' => $method, 'error' => $e->getMessage()]);
                $this->stdioWrite($this->errorResponse($id, -32602, 'Invalid params: ' . $e->getMessage()));
            } catch (\Throwable $e) {
                Logger::error('Unhandled exception in stdio handler', [
                    'method' => $method,
                    'class'  => get_class($e),
                    'error'  => $e->getMessage(),
                    'file'   => $e->getFile() . ':' . $e->getLine(),
                ]);
                $this->stdioWrite($this->errorResponse($id, -32603, 'Internal error'));
            }
        }
    }

    private function stdioWrite(string $json): void
    {
        fwrite(STDOUT, $json . "\n");
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    private function validateOrigin(string $origin): ?array
    {
        if ($origin === '') {
            return null;
        }
        $host = parse_url($origin, PHP_URL_HOST) ?? '';
        if (!in_array($host, self::ALLOWED_ORIGINS, true)) {
            Logger::warning('Origin rejected', ['origin' => $origin]);
            return $this->httpError(403, 'Forbidden: invalid Origin');
        }
        return null;
    }

    private function validateProtocolVersion(string $version): ?array
    {
        if ($version === '') {
            return null;
        }
        if (!in_array($version, self::SUPPORTED_VERSIONS, true)) {
            Logger::warning('Unsupported protocol version', ['version' => $version]);
            return $this->httpError(400, 'Unsupported MCP-Protocol-Version: ' . $version);
        }
        return null;
    }

    // -------------------------------------------------------------------------
    // Session helpers
    // -------------------------------------------------------------------------

    private function startNewSession(array $params): string
    {
        ini_set('session.use_cookies', '0');
        session_start();
        if (!empty($params['token'])) {
            $_SESSION['token'] = $params['token'];
        }
        $caps = $params['capabilities'] ?? [];
        $_SESSION['elicitation'] = isset($caps['elicitation']);
        $id = session_id();
        Logger::setSession($id);
        session_write_close();
        return $id;
    }

    private function resumeSession(string $sessionId): bool
    {
        ini_set('session.use_cookies', '0');
        session_id($sessionId);
        session_start();
        Logger::setSession($sessionId);
        $this->clientSupportsElicitation = $_SESSION['elicitation'] ?? false;
        return true;
    }

    private function initializeLegacy(array $params): array
    {
        if (!empty($params['token'])) {
            $_SESSION['token'] = $params['token'];
        }
        return $this->initializeResult('2024-11-05');
    }

    // -------------------------------------------------------------------------
    // Resource index helpers
    // -------------------------------------------------------------------------

    private function indexResourceByPath(array $def): void
    {
        $uri  = $def['uri'];
        $host = parse_url($uri, PHP_URL_HOST);

        if (str_contains($uri, '{id}')) {
            $this->resourcesByPath[$host]['get'] = $def['name'];
        } elseif (str_contains($uri, '{?')) {
            $this->resourcesByPath[$host]['list'] = $def['name'];
        } else {
            $this->resourcesByPath[$host]['static'] = $def['name'];
        }
    }

    // -------------------------------------------------------------------------
    // JSON-RPC message handlers
    // -------------------------------------------------------------------------

    private function initializeResult(string $version = self::PROTOCOL_VERSION): array
    {
        return [
            'protocolVersion' => $version,
            'capabilities'    => [
                'tools'       => ['listChanged' => false],
                'resources'   => ['listChanged' => false, 'subscribe' => false],
                'prompts'     => ['listChanged' => false],
                'logging'     => new \stdClass(),
                'completions' => new \stdClass(),
            ],
            'serverInfo' => [
                'name'    => 'easyverein-mcp',
                'title'   => 'EasyVerein MCP Server',
                'version' => '1.0.0',
            ],
            'instructions' => self::INSTRUCTIONS,
        ];
    }

    private function dispatchStateless(mixed $id, string $method, array $params): string
    {
        try {
            return match ($method) {
                'tools/list'               => $this->respond($id, $this->toolListResult($params)),
                'resources/list'           => $this->respond($id, $this->resourceListResult()),
                'resources/templates/list' => $this->respond($id, $this->resourceTemplateListResult()),
                'prompts/list'             => $this->respond($id, $this->prompts->list()),
                'logging/setLevel'         => $this->respond($id, $this->handleLoggingSetLevel($params)),
                'completion/complete'      => $this->respond($id, $this->handleCompletion($params)),
                'ping'                     => $this->respond($id, new \stdClass()),
                default                    => $this->errorResponse($id, -32601, 'Method not found'),
            };
        } catch (\InvalidArgumentException $e) {
            Logger::warning('Invalid params in stateless dispatch', ['method' => $method, 'error' => $e->getMessage()]);
            return $this->errorResponse($id, -32602, 'Invalid params: ' . $e->getMessage());
        } catch (\Throwable $e) {
            Logger::error('Unhandled exception in stateless dispatch', [
                'method' => $method,
                'class'  => get_class($e),
                'error'  => $e->getMessage(),
                'file'   => $e->getFile() . ':' . $e->getLine(),
            ]);
            return $this->errorResponse($id, -32603, 'Internal error');
        }
    }

    private function toolListResult(array $params): array
    {
        $tools = array_values(array_map(fn($def) => [
            'name'        => $def['name'],
            'description' => $def['description'],
            'inputSchema' => [
                'type'                 => 'object',
                'properties'           => (object) array_filter($def['props'], fn($k) => $k !== 'token', ARRAY_FILTER_USE_KEY),
                'required'             => array_values(array_filter($def['required'], fn($r) => $r !== 'token')),
                'additionalProperties' => false,
            ],
            'annotations' => $this->inferAnnotations($def['name']),
        ], $this->tools));

        return ['tools' => $tools];
    }

    private function resourceListResult(): array
    {
        $resources = [];
        foreach ($this->resources as $name => $def) {
            if (!str_contains($def['uri'], '{')) {
                $resources[] = [
                    'uri'         => $def['uri'],
                    'name'        => $name,
                    'description' => $def['description'],
                    'mimeType'    => 'application/json',
                ];
            }
        }
        return ['resources' => $resources];
    }

    private function resourceTemplateListResult(): array
    {
        $templates = [];
        foreach ($this->resources as $name => $def) {
            if (str_contains($def['uri'], '{')) {
                $templates[] = [
                    'uriTemplate' => $def['uri'],
                    'name'        => $name,
                    'description' => $def['description'],
                    'mimeType'    => 'application/json',
                ];
            }
        }
        return ['resourceTemplates' => $templates];
    }

    private function readResource(string $uri): array
    {
        if ($uri === '') {
            throw new \RuntimeException('resources/read: uri parameter required');
        }

        $parsed  = parse_url($uri);
        $scheme  = $parsed['scheme'] ?? '';
        $host    = $parsed['host']   ?? '';
        $rawPath = $parsed['path']   ?? '';
        $query   = [];

        if ($scheme !== 'easyverein') {
            throw new \RuntimeException("Unknown resource URI scheme: $uri");
        }

        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $query);
        }

        $byPath = $this->resourcesByPath[$host] ?? null;
        if ($byPath === null) {
            throw new \RuntimeException("Unknown resource: $uri");
        }

        $pathId = trim($rawPath, '/');

        if ($pathId !== '' && ctype_digit($pathId) && isset($byPath['get'])) {
            $name = $byPath['get'];
            $args = ['id' => (int)$pathId] + $query;
        } elseif (isset($byPath['list'])) {
            $name = $byPath['list'];
            $args = $query;
        } elseif (isset($byPath['static'])) {
            $name = $byPath['static'];
            $args = [];
        } else {
            throw new \RuntimeException("No matching resource for: $uri");
        }

        $args['token'] = $this->resolveToken($args);
        $t0   = microtime(true);
        $raw  = $this->resourceHandlers[$name]->dispatch($name, $args);
        $ms   = Logger::ms($t0);
        $text = $this->processPaginatedResponse($raw);
        Logger::info('Resource read', ['name' => $name, 'duration_ms' => $ms, 'token' => Logger::tokenHint($this->requestToken)]);
        $this->queueNotification('info', "Resource $name read ({$ms}ms)");
        $this->checkTokenRefresh();

        return ['contents' => [['uri' => $uri, 'mimeType' => 'application/json', 'text' => $text]]];
    }

    private function safeReadResource(string $uri): array
    {
        try {
            return $this->readResource($uri);
        } catch (AuthException $e) {
            throw $e; // bubble up to top-level handler → -32001
        } catch (\Throwable $e) {
            Logger::warning('Resource read failed', ['uri' => $uri, 'error' => $e->getMessage()]);
            $this->queueNotification('warning', "Resource read failed: " . $e->getMessage());
            return ['contents' => [['uri' => $uri, 'mimeType' => 'application/json', 'text' => json_encode(['error' => $e->getMessage()])]]];
        }
    }

    private function callTool(array $params, mixed $requestId = null): array
    {
        $name      = $params['name']      ?? '';
        $arguments = $params['arguments'] ?? [];

        if (!isset($this->toolHandlers[$name])) {
            Logger::warning('Unknown tool called', ['name' => $name]);
            throw new \InvalidArgumentException("Unknown tool: $name", -32602);
        }

        // Destructive tools: ask for confirmation via elicitation if client supports it
        if (str_starts_with($name, 'delete') && $this->clientSupportsElicitation && $requestId !== null) {
            $confirmed = $this->elicitConfirmation($name, $requestId);
            if ($confirmed !== true) {
                Logger::info('Tool cancelled via elicitation', ['name' => $name]);
                $result = [
                    'content' => [['type' => 'text', 'text' => 'Aktion wurde abgebrochen.']],
                    'isError' => false,
                ];
                // We already opened the SSE stream — send the final result and terminate
                $this->streamFinalResult($requestId, $result);
                throw new ExitException();
            }
        }

        $t0 = microtime(true);
        try {
            $arguments['token'] = $this->resolveToken($arguments);
            $raw = $this->toolHandlers[$name]->dispatch($name, $arguments);
            $ms  = Logger::ms($t0);
            Logger::info('Tool called', ['name' => $name, 'duration_ms' => $ms, 'token' => Logger::tokenHint($this->requestToken)]);
            $this->queueNotification('info', "Tool $name executed ({$ms}ms)");
            $this->checkTokenRefresh();
            $result = [
                'content' => $this->buildToolContent($raw),
                'isError' => false,
            ];
            // If SSE stream was already opened for elicitation, send final result directly
            if ($this->clientSupportsElicitation && str_starts_with($name, 'delete') && $requestId !== null) {
                $this->streamFinalResult($requestId, $result);
                throw new ExitException();
            }
            return $result;
        } catch (AuthException $e) {
            throw $e; // bubble up to top-level handler → -32001
        } catch (ExitException $e) {
            throw $e; // propagate process-exit signal
        } catch (RateLimitException $e) {
            Logger::warning('Rate limit on tool call', ['tool' => $name, 'retry_after' => $e->getRetryAfter()]);
            $this->queueNotification('warning', $e->getMessage());
            $result = [
                'content' => [['type' => 'text', 'text' => $e->getMessage()]],
                'isError' => true,
            ];
            if ($this->clientSupportsElicitation && str_starts_with($name, 'delete') && $requestId !== null) {
                $this->streamFinalResult($requestId, $result);
                throw new ExitException();
            }
            return $result;
        } catch (\Throwable $e) {
            $ms = Logger::ms($t0);
            Logger::warning('Tool call failed', ['name' => $name, 'duration_ms' => $ms, 'error' => $e->getMessage()]);
            $this->queueNotification('warning', "Tool $name failed: " . $e->getMessage());
            $result = [
                'content' => [['type' => 'text', 'text' => $e->getMessage()]],
                'isError' => true,
            ];
            if ($this->clientSupportsElicitation && str_starts_with($name, 'delete') && $requestId !== null) {
                $this->streamFinalResult($requestId, $result);
                throw new ExitException();
            }
            return $result;
        }
    }

    /**
     * Sends an elicitation/create request via SSE and polls the session for the response.
     * Returns true if the user accepted, false on decline/cancel/timeout.
     * Opens the SSE output stream directly (no return via Slim).
     */
    private function elicitConfirmation(string $toolName, mixed $requestId): bool
    {
        $elicitId  = 'elicit-' . bin2hex(random_bytes(4));
        $sessionId = session_id();

        $request = json_encode([
            'jsonrpc' => '2.0',
            'id'      => $elicitId,
            'method'  => 'elicitation/create',
            'params'  => [
                'message' => "Bist du sicher, dass du **$toolName** ausführen möchtest? Diese Aktion kann nicht rückgängig gemacht werden.",
                'requestedSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'confirm' => [
                            'type'        => 'boolean',
                            'title'       => 'Löschen bestätigen',
                            'description' => 'Ja, ich möchte diese Aktion ausführen.',
                        ],
                    ],
                    'required' => ['confirm'],
                ],
            ],
        ]);

        // Close session first (ini_set fails while session is active),
        // then configure it before any output so session_start() in the
        // polling loop won't try to send cookie or cache-control headers.
        session_write_close();
        ini_set('session.use_cookies', '0');
        ini_set('session.cache_limiter', '');

        // Flush elicitation request to client via SSE
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        echo "event: message\ndata: $request\n\n";
        flush();

        // Poll session for the client's response (max 30 s)
        $key     = 'elicitation_response_' . $elicitId;
        $timeout = time() + 30;
        $result  = null;

        set_time_limit(60); // polling loop exceeds default max_execution_time

        while (time() < $timeout) {
            usleep(300_000); // 300 ms
            session_id($sessionId);
            session_start();
            if (isset($_SESSION[$key])) {
                $result = $_SESSION[$key];
                unset($_SESSION[$key]);
            }
            session_write_close();
            if ($result !== null) {
                break;
            }
        }

        Logger::debug('Elicitation result', ['key' => $key, 'result' => json_encode($result)]);

        if (!is_array($result) || ($result['action'] ?? '') !== 'accept') {
            return false;
        }
        return ($result['content']['confirm'] ?? false) === true;
    }

    /** Writes the final tool-result event to an already-open SSE stream. */
    private function streamFinalResult(mixed $requestId, array $result): void
    {
        foreach ($this->pendingNotifications as $n) {
            echo 'event: message' . "\n" . 'data: ' . json_encode([
                'jsonrpc' => '2.0',
                'method'  => 'notifications/message',
                'params'  => ['level' => $n['level'], 'logger' => 'easyverein-mcp', 'data' => $n['data']],
            ]) . "\n\n";
        }
        $this->pendingNotifications = [];
        echo 'event: message' . "\n" . 'data: ' . $this->respond($requestId, $result) . "\n\n";
        flush();
    }

    /**
     * Builds the tool content array from a raw API response.
     * Paginated responses are normalized to {results, pagination}; others pass through.
     *
     * @return array<int, array{type: string, text: string}>
     */
    private function buildToolContent(string $raw): array
    {
        return [['type' => 'text', 'text' => $this->processPaginatedResponse($raw)]];
    }

    private function getPrompt(array $params): array
    {
        $name      = $params['name']      ?? '';
        $arguments = $params['arguments'] ?? [];
        return $this->prompts->get($name, $arguments);
    }

    private function safeGetPrompt(array $params): array
    {
        try {
            return $this->getPrompt($params);
        } catch (\InvalidArgumentException $e) {
            Logger::warning('Prompt get failed', ['name' => $params['name'] ?? '', 'error' => $e->getMessage()]);
            throw $e; // bubble up as -32602
        } catch (\Throwable $e) {
            Logger::warning('Prompt get failed', ['name' => $params['name'] ?? '', 'error' => $e->getMessage()]);
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }

    private function resolveToken(array $arguments): string
    {
        if ($this->requestToken !== '') {
            return $this->requestToken;
        }
        if (!empty($arguments['token'])) {
            return $arguments['token'];
        }
        if (!empty($_SESSION['token'])) {
            return $_SESSION['token'];
        }
        Logger::warning('No token available for request');
        throw new AuthException('Kein Token verfügbar. Authorization: Bearer <token> Header setzen.');
    }

    private function parseBearerToken(string $header): string
    {
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        return '';
    }

    private function unknownMethod(mixed $id, string $method): string
    {
        Logger::warning('Unknown method', ['method' => $method]);
        return $this->errorResponse($id, -32601, 'Method not found');
    }

    // -------------------------------------------------------------------------
    // Response helpers
    // -------------------------------------------------------------------------

    private function respond(mixed $id, mixed $result): string
    {
        return json_encode(['jsonrpc' => '2.0', 'id' => $id, 'result' => $result]);
    }

    private function errorResponse(mixed $id, int $code, string $message): string
    {
        return json_encode(['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]]);
    }

    private function httpError(int $status, string $message): array
    {
        return [
            'status'  => $status,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode(['error' => $message]),
        ];
    }

    private function sendEvent(string $event, string $data): void
    {
        echo "event: $event\n";
        echo "data: $data\n\n";
    }

    // -------------------------------------------------------------------------
    // Logging / Annotations
    // -------------------------------------------------------------------------

    private function handleLoggingSetLevel(array $params): \stdClass
    {
        $level = $params['level'] ?? 'info';
        $this->clientLogLevel = $level;
        $map = [
            'debug'     => Logger::DEBUG,
            'info'      => Logger::INFO,
            'notice'    => Logger::INFO,
            'warning'   => Logger::WARNING,
            'error'     => Logger::ERROR,
            'critical'  => Logger::ERROR,
            'alert'     => Logger::ERROR,
            'emergency' => Logger::ERROR,
        ];
        if (isset($map[$level])) {
            Logger::setMinLevel($map[$level]);
        }
        Logger::info('Log level set by client', ['level' => $level]);
        return new \stdClass();
    }

    private function queueNotification(string $level, string $data): void
    {
        static $severity = ['debug' => 0, 'info' => 1, 'notice' => 1, 'warning' => 2, 'error' => 3, 'critical' => 3, 'alert' => 3, 'emergency' => 3];
        $min = $severity[$this->clientLogLevel] ?? 1;
        if (($severity[$level] ?? 1) >= $min) {
            $this->pendingNotifications[] = ['level' => $level, 'data' => $data];
        }
    }

    private function sseResponse(mixed $id, mixed $result): array
    {
        $body = '';
        foreach ($this->pendingNotifications as $n) {
            $body .= 'event: message' . "\n" . 'data: ' . json_encode([
                'jsonrpc' => '2.0',
                'method'  => 'notifications/message',
                'params'  => ['level' => $n['level'], 'logger' => 'easyverein-mcp', 'data' => $n['data']],
            ]) . "\n\n";
        }
        $this->pendingNotifications = [];
        $body .= 'event: message' . "\n" . 'data: ' . $this->respond($id, $result) . "\n\n";

        return [
            'status'  => 200,
            'headers' => ['Content-Type' => 'text/event-stream', 'Cache-Control' => 'no-cache'],
            'body'    => $body,
        ];
    }

    private function inferAnnotations(string $name): array
    {
        if (str_starts_with($name, 'list') || str_starts_with($name, 'get')) {
            return ['readOnlyHint' => true,  'destructiveHint' => false, 'idempotentHint' => true,  'openWorldHint' => false];
        }
        if (str_starts_with($name, 'create')) {
            return ['readOnlyHint' => false, 'destructiveHint' => false, 'idempotentHint' => false, 'openWorldHint' => false];
        }
        if (str_starts_with($name, 'update')) {
            return ['readOnlyHint' => false, 'destructiveHint' => false, 'idempotentHint' => true,  'openWorldHint' => false];
        }
        if (str_starts_with($name, 'delete')) {
            return ['readOnlyHint' => false, 'destructiveHint' => true,  'idempotentHint' => true,  'openWorldHint' => false];
        }
        if ($name === 'refreshToken') {
            return ['readOnlyHint' => false, 'destructiveHint' => false, 'idempotentHint' => false, 'openWorldHint' => false];
        }
        if ($name === 'cancellation') {
            return ['readOnlyHint' => false, 'destructiveHint' => true,  'idempotentHint' => false, 'openWorldHint' => false];
        }
        if ($name === 'normalize') {
            return ['readOnlyHint' => false, 'destructiveHint' => false, 'idempotentHint' => false, 'openWorldHint' => false];
        }
        if ($name === 'checkDiscountCode') {
            return ['readOnlyHint' => true,  'destructiveHint' => false, 'idempotentHint' => true,  'openWorldHint' => false];
        }
        if ($name === 'resetPassword') {
            return ['readOnlyHint' => false, 'destructiveHint' => false, 'idempotentHint' => false, 'openWorldHint' => false];
        }
        return ['readOnlyHint' => false, 'destructiveHint' => false, 'idempotentHint' => false, 'openWorldHint' => true];
    }

    // -------------------------------------------------------------------------
    // Pagination helpers
    // -------------------------------------------------------------------------

    /**
     * Normalizes a raw API response.
     * EasyVerein list responses ({next, previous, current, results}) are rewritten to
     * {results, pagination: {page, limit?, nextPage?, previousPage?}}.
     * Single-object responses are returned unchanged.
     */
    private function processPaginatedResponse(string $raw): string
    {
        $data = json_decode($raw, true);

        if (!is_array($data) || !array_key_exists('results', $data)) {
            return $raw;
        }

        $next     = $data['next']     ?? null;
        $previous = $data['previous'] ?? null;
        $current  = $data['current']  ?? 1;

        $pagination = ['page' => $current];

        $limit = $this->extractQueryInt($next ?? $previous, 'limit');
        if ($limit !== null) {
            $pagination['limit'] = $limit;
        }

        if ($next !== null) {
            $nextPage = $this->extractQueryInt($next, 'page');
            if ($nextPage !== null) {
                $pagination['nextPage'] = $nextPage;
            }
        }

        if ($previous !== null) {
            $prevPage = $this->extractQueryInt($previous, 'page');
            $pagination['previousPage'] = $prevPage ?? 1;
        }

        return json_encode(['results' => $data['results'], 'pagination' => $pagination]);
    }

    private function extractQueryInt(?string $url, string $key): ?int
    {
        if ($url === null) {
            return null;
        }
        parse_str((string) parse_url($url, PHP_URL_QUERY), $q);
        return isset($q[$key]) ? (int) $q[$key] : null;
    }

    private function checkTokenRefresh(): void
    {
        if ($this->client->isTokenRefreshNeeded()) {
            Logger::warning('Token refresh needed');
            $this->queueNotification('warning', 'Dein EasyVerein API-Token läuft bald ab. Rufe GET /api/v3.0/refresh-token/ mit dem aktuellen Token auf, um einen neuen Token zu erhalten.');
        }
    }

    private function handleCompletion(array $params): array
    {
        $ref      = $params['ref']      ?? [];
        $arg      = $params['argument'] ?? [];
        $argName  = $arg['name']  ?? '';
        $argValue = $arg['value'] ?? '';

        Logger::debug('completion/complete', [
            'ref_type' => $ref['type']  ?? 'MISSING',
            'ref_name' => $ref['name']  ?? ($ref['uri'] ?? 'MISSING'),
            'argName'  => $argName,
            'argValue' => $argValue,
            'token'    => Logger::tokenHint($this->requestToken),
        ]);

        $values = match ($ref['type'] ?? '') {
            'ref/prompt'   => $this->completePromptArg($ref['name'] ?? '', $argName, $argValue),
            'ref/resource' => $this->completeResourceArg($ref['uri'] ?? '', $argName, $argValue),
            default        => [],
        };

        $total   = count($values);
        $limited = array_slice($values, 0, 100);

        return ['completion' => [
            'values'  => $limited,
            'total'   => $total,
            'hasMore' => $total > 100,
        ]];
    }

    private function completePromptArg(string $promptName, string $argName, string $value): array
    {
        // Static completions — no token needed
        if ($argName === 'month') {
            return $this->filterStatic(['1','2','3','4','5','6','7','8','9','10','11','12'], $value);
        }
        if ($argName === 'year') {
            $y = (int) date('Y');
            return $this->filterStatic(array_map('strval', range($y - 2, $y + 1)), $value);
        }

        // Dynamic completions — require token
        if (in_array($argName, ['query', 'member'], true) && in_array($promptName, ['member-search', 'invoice-for-member'], true)) {
            return $this->fetchCompletions('/member/', ['search' => $value, 'limit' => 20], 'name_for_sorting');
        }
        if ($argName === 'event' && $promptName === 'event-participants') {
            return $this->fetchCompletions('/event/', ['search' => $value, 'limit' => 20], 'name');
        }

        return [];
    }

    private function completeResourceArg(string $uriTemplate, string $argName, string $value): array
    {
        $host = parse_url($uriTemplate, PHP_URL_HOST) ?? '';

        // ID completions: return "id – name" strings for the most common entities
        if ($argName === 'id') {
            return match ($host) {
                'member'       => $this->fetchIdCompletions('/member/',       ['search' => $value, 'limit' => 20], 'name_for_sorting'),
                'member-group' => $this->fetchIdCompletions('/member-group/', ['limit' => 50],                     'name', $value),
                'event'        => $this->fetchIdCompletions('/event/',        ['search' => $value, 'limit' => 20], 'name'),
                'custom-field' => $this->fetchIdCompletions('/custom-field/', ['limit' => 50],                     'name', $value),
                default        => [],
            };
        }

        // Non-id template params (search, page, limit) — static hints
        if ($argName === 'limit') {
            return $this->filterStatic(['10', '20', '50', '100'], $value);
        }
        if ($argName === 'page') {
            return $this->filterStatic(['1', '2', '3', '4', '5'], $value);
        }

        return [];
    }

    /**
     * Fetches a list from the API and returns the given field as completion values.
     * Falls back to [] if no token or API error.
     * If $field is not found on a result, tries common fallback fields (username, email, name).
     */
    private function fetchCompletions(string $path, array $query, string $field): array
    {
        if ($this->requestToken === '') {
            Logger::debug('fetchCompletions: no token', ['path' => $path]);
            return [];
        }
        try {
            $raw     = $this->client->get($this->requestToken, $path, $query);
            $data    = json_decode($raw, true);
            $results = $data['results'] ?? $data ?? [];
            if (!is_array($results) || array_is_list($results) === false) {
                $results = [];
            }
            Logger::debug('fetchCompletions', ['path' => $path, 'count' => count($results), 'field' => $field]);
            $fallbacks = [$field, 'name_for_sorting', 'username', 'email', 'email_or_user_name', 'name', 'title'];
            return array_values(array_unique(array_filter(array_map(function ($r) use ($fallbacks) {
                foreach ($fallbacks as $f) {
                    if (isset($r[$f]) && $r[$f] !== '') {
                        return (string) $r[$f];
                    }
                }
                return null;
            }, $results))));
        } catch (\Throwable $e) {
            Logger::debug('fetchCompletions error', ['path' => $path, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Like fetchCompletions, but returns "id – name" strings and optionally
     * filters by a partial value against the name field.
     */
    private function fetchIdCompletions(string $path, array $query, string $nameField, string $filter = ''): array
    {
        if ($this->requestToken === '') {
            return [];
        }
        try {
            $raw     = $this->client->get($this->requestToken, $path, $query);
            $data    = json_decode($raw, true);
            $results = $data['results'] ?? $data ?? [];
            if (!is_array($results) || !array_is_list($results)) {
                $results = [];
            }
            Logger::debug('fetchIdCompletions', ['path' => $path, 'count' => count($results), 'nameField' => $nameField]);
            $nameFallbacks = [$nameField, 'name_for_sorting', 'name', 'title', 'username', 'email', 'email_or_user_name'];
            $out = [];
            foreach ($results as $r) {
                $id = (string) ($r['id'] ?? '');
                if ($id === '') {
                    continue;
                }
                $name = '';
                foreach ($nameFallbacks as $f) {
                    if (!empty($r[$f])) {
                        $name = (string) $r[$f];
                        break;
                    }
                }
                if ($filter !== '' && !str_contains(strtolower($name), strtolower($filter)) && !str_starts_with($id, $filter)) {
                    continue;
                }
                $out[] = $name !== '' ? "$id – $name" : $id;
            }
            return array_values(array_unique($out));
        } catch (\Throwable) {
            return [];
        }
    }

    private function filterStatic(array $values, string $prefix): array
    {
        if ($prefix === '') {
            return $values;
        }
        return array_values(array_filter($values, fn($v) => str_starts_with($v, $prefix)));
    }
}
