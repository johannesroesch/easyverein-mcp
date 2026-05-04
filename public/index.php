<?php

declare(strict_types=1);

use EasyVerein\Mcp\ApiClient;
use EasyVerein\Mcp\ExitException;
use EasyVerein\Mcp\McpServer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

\EasyVerein\Mcp\Logger::init();

$baseUrl = $_ENV['EASYVEREIN_BASE_URL'] ?? 'https://easyverein.com/api/v3.0';

$client = new ApiClient($baseUrl);
$mcp    = new McpServer($client);

$mcpMetadata = static function () use ($mcp): array {
    return [
        'name'    => 'easyverein-mcp',
        'version' => '1.0.0',
        'transports' => [
            [
                'type'            => 'streamable-http',
                'protocolVersion' => '2025-11-25',
                'endpoint'        => '/mcp',
            ],
            [
                'type'            => 'sse',
                'protocolVersion' => '2024-11-05',
                'endpoints'       => ['sse' => '/sse', 'messages' => '/messages'],
            ],
            [
                'type'    => 'stdio',
                'command' => 'php bin/mcp-stdio.php',
            ],
        ],
        'capabilities' => [
            'tools'     => ['listChanged' => false],
            'resources' => ['listChanged' => false, 'subscribe' => false],
            'prompts'   => ['listChanged' => false],
        ],
        'tools'     => count($mcp->getToolNames()),
        'resources' => count($mcp->getResourceNames()),
        'prompts'   => $mcp->getPromptCount(),
    ];
};

$app = AppFactory::create();

// ─── Discovery endpoints ────────────────────────────────────────────────────

$app->get('/', function (Request $request, Response $response) use ($mcp, $mcpMetadata): Response {
    $toolCount     = count($mcp->getToolNames());
    $resourceCount = count($mcp->getResourceNames());
    $promptCount   = $mcp->getPromptCount();
    $accept        = $request->getHeaderLine('Accept');

    if (str_contains($accept, 'application/json') && !str_contains($accept, 'text/html')) {
        $response->getBody()->write(json_encode($mcpMetadata()));
        return $response->withHeader('Content-Type', 'application/json');
    }

    $uri    = $request->getUri();
    $port   = $uri->getPort();
    $base   = $uri->getScheme() . '://' . $uri->getHost()
            . ($port && !in_array($port, [80, 443]) ? ':' . $port : '');
    $mcpUrl = $base . '/mcp';
    $sseUrl = $base . '/sse';

    $claudeDesktop = htmlspecialchars(json_encode([
        'mcpServers' => [
            'easyverein' => [
                'type'    => 'http',
                'url'     => $mcpUrl,
                'headers' => ['Authorization' => 'Bearer YOUR_TOKEN'],
            ],
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $cursor = htmlspecialchars(json_encode([
        'mcpServers' => [
            'easyverein' => [
                'url'     => $mcpUrl,
                'headers' => ['Authorization' => 'Bearer YOUR_TOKEN'],
            ],
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $vscode = htmlspecialchars(json_encode([
        'mcp' => [
            'servers' => [
                'easyverein' => [
                    'type'    => 'http',
                    'url'     => $mcpUrl,
                    'headers' => ['Authorization' => 'Bearer YOUR_TOKEN'],
                ],
            ],
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $cline = htmlspecialchars(json_encode([
        'easyverein' => [
            'transportType' => 'streamable-http',
            'url'           => $mcpUrl,
            'headers'       => ['Authorization' => 'Bearer YOUR_TOKEN'],
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $legacySse = htmlspecialchars(json_encode([
        'mcpServers' => [
            'easyverein' => [
                'type' => 'sse',
                'url'  => $sseUrl,
            ],
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $claudeCode = htmlspecialchars(json_encode([
        'mcpServers' => [
            'easyverein' => [
                'type'    => 'http',
                'url'     => $mcpUrl,
                'headers' => ['Authorization' => 'Bearer YOUR_TOKEN'],
            ],
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $claudeCodeCli = htmlspecialchars(
        "claude mcp add --transport http easyverein $mcpUrl \\\n  --header \"Authorization: Bearer YOUR_TOKEN\""
    );

    $html = <<<HTML
<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"><title>EasyVerein MCP Server</title>
<style>
body{font-family:monospace;max-width:720px;margin:4rem auto;padding:0 1rem;color:#333}
h1{font-size:1.4rem}h2{font-size:1rem;margin-top:2rem}
code{background:#f4f4f4;padding:2px 6px;border-radius:3px}
table{border-collapse:collapse;width:100%}td,th{padding:.4rem .8rem;border:1px solid #ddd;text-align:left}
th{background:#f4f4f4}
.badge{font-size:.75rem;padding:1px 6px;border-radius:3px;background:#e0f0e0;color:#2a6}
.old{background:#fff3e0;color:#a60}
/* Tabs */
.tabs{margin-top:.6rem}
.tab-bar{display:flex;gap:0;border-bottom:2px solid #ddd;flex-wrap:wrap}
.tab-btn{background:none;border:none;padding:.45rem .9rem;cursor:pointer;font-family:monospace;font-size:.85rem;color:#666;border-bottom:2px solid transparent;margin-bottom:-2px}
.tab-btn:hover{color:#333}
.tab-btn.active{color:#333;font-weight:bold;border-bottom:2px solid #333}
.tab-panel{display:none;padding-top:.8rem}
.tab-panel.active{display:block}
.path{font-size:.78rem;color:#888;margin:0 0 .4rem}
/* Code block */
.snippet{position:relative}
pre{background:#1e1e1e;color:#d4d4d4;padding:1rem;border-radius:6px;overflow-x:auto;font-size:.8rem;line-height:1.5;margin:0}
.copy-btn{position:absolute;top:.5rem;right:.5rem;background:#444;color:#ccc;border:none;padding:2px 8px;border-radius:3px;cursor:pointer;font-size:.75rem}
.copy-btn:hover{background:#666}
</style></head>
<body>
<h1>EasyVerein MCP Server</h1>
<p>Status: <strong>running</strong> &mdash; {$toolCount} Tools, {$resourceCount} Ressourcen, {$promptCount} Prompts</p>

<h2>Streamable HTTP <span class="badge">2025-11-25</span></h2>
<table>
  <tr><th>Endpoint</th><th>Methode</th><th>Beschreibung</th></tr>
  <tr><td><code>/mcp</code></td><td>POST</td><td>JSON-RPC 2.0 (initialize, tools/list, tools/call, resources/*, prompts/*)</td></tr>
  <tr><td><code>/mcp</code></td><td>DELETE</td><td>Session beenden</td></tr>
</table>

<h2>HTTP+SSE <span class="badge old">2024-11-05 (legacy)</span></h2>
<table>
  <tr><th>Endpoint</th><th>Methode</th><th>Beschreibung</th></tr>
  <tr><td><code>/sse</code></td><td>GET</td><td>SSE-Stream (Handshake)</td></tr>
  <tr><td><code>/messages</code></td><td>POST</td><td>JSON-RPC 2.0</td></tr>
</table>

<h2>Discovery</h2>
<table>
  <tr><td><code>/.well-known/mcp</code></td><td>GET</td><td>Server-Metadaten (JSON)</td></tr>
  <tr><td><code>/manifest</code></td><td>GET</td><td>Server-Metadaten (JSON, Alias)</td></tr>
</table>

<h2>Client-Konfiguration</h2>
<p>Ersetze <code>YOUR_TOKEN</code> durch deinen EasyVerein API-Token.</p>

<div class="tabs">
  <div class="tab-bar">
    <button class="tab-btn active" onclick="tab(this,'claude')">Claude Desktop</button>
    <button class="tab-btn" onclick="tab(this,'claudecode')">Claude Code</button>
    <button class="tab-btn" onclick="tab(this,'cursor')">Cursor</button>
    <button class="tab-btn" onclick="tab(this,'vscode')">VS Code</button>
    <button class="tab-btn" onclick="tab(this,'cline')">Cline / Roo Code</button>
    <button class="tab-btn" onclick="tab(this,'sse')">Legacy SSE</button>
  </div>

  <div id="tab-claude" class="tab-panel active">
    <p class="path">~/Library/Application Support/Claude/claude_desktop_config.json &nbsp;(macOS)<br>
    %APPDATA%\Claude\claude_desktop_config.json &nbsp;(Windows)</p>
    <div class="snippet">
      <button class="copy-btn" onclick="copy(this)">Kopieren</button>
      <pre>{$claudeDesktop}</pre>
    </div>
  </div>

  <div id="tab-claudecode" class="tab-panel">
    <p class="path">CLI (empfohlen) &mdash; fügt den Server zum lokalen Profil hinzu:</p>
    <div class="snippet">
      <button class="copy-btn" onclick="copy(this)">Kopieren</button>
      <pre>{$claudeCodeCli}</pre>
    </div>
    <p class="path">Oder manuell in <code>.mcp.json</code> (Projekt) bzw. <code>~/.claude.json</code> (global):</p>
    <div class="snippet">
      <button class="copy-btn" onclick="copy(this)">Kopieren</button>
      <pre>{$claudeCode}</pre>
    </div>
  </div>

  <div id="tab-cursor" class="tab-panel">
    <p class="path">.cursor/mcp.json &nbsp;(Projekt) &nbsp;oder&nbsp; ~/.cursor/mcp.json &nbsp;(global)</p>
    <div class="snippet">
      <button class="copy-btn" onclick="copy(this)">Kopieren</button>
      <pre>{$cursor}</pre>
    </div>
  </div>

  <div id="tab-vscode" class="tab-panel">
    <p class="path">.vscode/mcp.json &nbsp;(Projekt) &nbsp;oder&nbsp; User Settings JSON</p>
    <div class="snippet">
      <button class="copy-btn" onclick="copy(this)">Kopieren</button>
      <pre>{$vscode}</pre>
    </div>
  </div>

  <div id="tab-cline" class="tab-panel">
    <p class="path">Cline / Roo Code &rarr; Einstellungen &rarr; &bdquo;Edit MCP Settings&ldquo;</p>
    <div class="snippet">
      <button class="copy-btn" onclick="copy(this)">Kopieren</button>
      <pre>{$cline}</pre>
    </div>
  </div>

  <div id="tab-sse" class="tab-panel">
    <p class="path">Für ältere Clients ohne Streamable-HTTP-Unterstützung. Token via <code>initialize</code>-Params übergeben.</p>
    <div class="snippet">
      <button class="copy-btn" onclick="copy(this)">Kopieren</button>
      <pre>{$legacySse}</pre>
    </div>
  </div>
</div>

<script>
function tab(btn, id) {
  document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
  document.querySelectorAll('.tab-panel').forEach(function(p){ p.classList.remove('active'); });
  btn.classList.add('active');
  document.getElementById('tab-' + id).classList.add('active');
}
function copy(btn) {
  navigator.clipboard.writeText(btn.nextElementSibling.innerText).then(function() {
    btn.textContent = 'Kopiert!';
    setTimeout(function(){ btn.textContent = 'Kopieren'; }, 1500);
  });
}
</script>
</body></html>
HTML;
    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html; charset=UTF-8');
});

$app->get('/manifest', function (Request $request, Response $response) use ($mcpMetadata): Response {
    $response->getBody()->write(json_encode($mcpMetadata()));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/.well-known/mcp', function (Request $request, Response $response) use ($mcpMetadata): Response {
    $response->getBody()->write(json_encode($mcpMetadata()));
    return $response->withHeader('Content-Type', 'application/json');
});

// ─── Streamable HTTP transport (spec 2025-11-25) ────────────────────────────

$app->post('/mcp', function (Request $request, Response $response) use ($mcp): Response {
    $sessionId       = $request->getHeaderLine('MCP-Session-Id');
    $protocolVersion = $request->getHeaderLine('MCP-Protocol-Version');
    $origin          = $request->getHeaderLine('Origin');
    $authHeader      = $request->getHeaderLine('Authorization');
    $acceptHeader    = $request->getHeaderLine('Accept');
    $result          = $mcp->handleStreamablePost($sessionId, $protocolVersion, $origin, $authHeader, $acceptHeader, (string) $request->getBody());

    foreach ($result['headers'] as $name => $value) {
        $response = $response->withHeader($name, $value);
    }
    if ($result['body'] !== '') {
        $response->getBody()->write($result['body']);
    }
    return $response->withStatus($result['status']);
});

$app->get('/mcp', function (Request $request, Response $response): Response {
    // Server has no server-initiated messages → 405 per spec
    return $response->withStatus(405)->withHeader('Allow', 'POST, DELETE');
});

$app->delete('/mcp', function (Request $request, Response $response) use ($mcp): Response {
    $sessionId = $request->getHeaderLine('MCP-Session-Id');
    $origin    = $request->getHeaderLine('Origin');
    $result    = $mcp->handleStreamableDelete($sessionId, $origin);
    return $response->withStatus($result['status']);
});

// ─── Legacy HTTP+SSE transport (spec 2024-11-05) ────────────────────────────

$app->get('/sse', function (Request $request, Response $response) use ($mcp): Response {
    ini_set('session.use_cookies', '0');
    session_start();
    $sessionId = session_id();
    session_write_close();
    try {
        $mcp->handleSse($sessionId);
    } catch (ExitException) {
        exit(0);
    }
    return $response;
});

$app->post('/messages', function (Request $request, Response $response) use ($mcp): Response {
    $sessionId  = $request->getQueryParams()['session'] ?? '';
    $authHeader = $request->getHeaderLine('Authorization');
    if ($sessionId) {
        ini_set('session.use_cookies', '0');
        session_id($sessionId);
        session_start();
    }
    try {
        $result = $mcp->handleMessage($authHeader, (string) $request->getBody());
    } catch (ExitException) {
        exit(0);
    }
    if ($sessionId) {
        session_write_close();
    }
    $response->getBody()->write($result);
    return $response->withHeader('Content-Type', 'application/json');
});

// ─── Error handling ──────────────────────────────────────────────────────────

$app->addErrorMiddleware(false, false, false)
    ->setErrorHandler(
        \Slim\Exception\HttpNotFoundException::class,
        function (Request $request, \Throwable $e, bool $displayErrorDetails) use ($app): Response {
            $response = $app->getResponseFactory()->createResponse(404);
            $response->getBody()->write(json_encode([
                'error'     => 'Not found',
                'path'      => $request->getUri()->getPath(),
                'available' => ['GET /', 'POST /mcp', 'GET /mcp', 'DELETE /mcp', 'GET /sse', 'POST /messages'],
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }
    );

$app->run();
