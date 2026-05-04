<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Integration;

/**
 * Tests for the SSE transport (handleMessage).
 */
class McpServerSseTest extends AbstractMcpTest
{
    private function sse(string $body, string $authHeader = 'Bearer test-token'): array
    {
        $json   = $this->server->handleMessage($authHeader, $body);
        $decoded = json_decode($json, true);
        return ['raw' => $json, 'data' => $decoded];
    }

    // ── tools/list ────────────────────────────────────────────────────────────

    public function testSseToolsListReturnsTools(): void
    {
        $result = $this->sse($this->jsonRpc('tools/list'));
        $tools  = $result['data']['result']['tools'] ?? null;
        self::assertIsArray($tools);
        self::assertNotEmpty($tools);
    }

    // ── tools/call ────────────────────────────────────────────────────────────

    public function testSseToolsCallSuccessReturnsContent(): void
    {
        $this->http->addResponse('{"id":1}', 201);
        $result = $this->sse(
            $this->jsonRpc('tools/call', ['name' => 'createMember', 'arguments' => ['join_date' => '2025-01-01']]),
        );
        self::assertFalse($result['data']['result']['isError'] ?? true);
    }

    public function testSseUnknownMethodReturnsError(): void
    {
        $result = $this->sse($this->jsonRpc('no/such/method'));
        self::assertSame(-32601, $result['data']['error']['code'] ?? null);
    }

    public function testSseInvalidJsonReturnsParseError(): void
    {
        $result = $this->sse('not-json');
        self::assertSame(-32700, $result['data']['error']['code'] ?? null);
    }

    public function testSseInvalidJsonRpcVersionReturnsError(): void
    {
        $result = $this->sse(json_encode(['jsonrpc' => '1.0', 'method' => 'tools/list', 'id' => 1]));
        self::assertSame(-32600, $result['data']['error']['code'] ?? null);
    }

    public function testSseMissingMethodReturnsError(): void
    {
        $result = $this->sse(json_encode(['jsonrpc' => '2.0', 'id' => 1]));
        self::assertSame(-32600, $result['data']['error']['code'] ?? null);
    }

    // ── initialize ────────────────────────────────────────────────────────────

    public function testSseInitializeReturnsProtocolVersion(): void
    {
        $result = $this->sse(
            $this->jsonRpc('initialize', ['protocolVersion' => '2024-11-05', 'clientInfo' => ['name' => 'test', 'version' => '1.0']]),
        );
        self::assertArrayHasKey('protocolVersion', $result['data']['result']);
    }

    // ── ping ─────────────────────────────────────────────────────────────────

    public function testSsePingReturnsResult(): void
    {
        $result = $this->sse($this->jsonRpc('ping'));
        self::assertArrayHasKey('result', $result['data']);
    }

    // ── Auth failure ──────────────────────────────────────────────────────────

    public function testSseAuthExceptionOnApiError(): void
    {
        $this->http->addResponse('Unauthorized', 401);
        $result = $this->sse(
            $this->jsonRpc('tools/call', ['name' => 'createMember', 'arguments' => ['join_date' => '2025-01-01']]),
        );
        // SSE transport encodes AuthException as JSON-RPC error -32001
        self::assertSame(-32001, $result['data']['error']['code'] ?? null);
    }

    // ── Unknown tool ──────────────────────────────────────────────────────────

    public function testSseUnknownToolReturnsInvalidParams(): void
    {
        $result = $this->sse(
            $this->jsonRpc('tools/call', ['name' => 'noSuchTool', 'arguments' => []]),
        );
        self::assertSame(-32602, $result['data']['error']['code'] ?? null);
    }

    // ── notifications/initialized (no error) ─────────────────────────────────

    public function testSseNotificationsInitializedReturnsEmptyResult(): void
    {
        $result = $this->sse($this->jsonRpc('notifications/initialized'));
        self::assertArrayHasKey('result', $result['data']);
    }

    // ── resources/list ───────────────────────────────────────────────────────

    public function testSseResourcesListReturnsResources(): void
    {
        $result = $this->sse($this->jsonRpc('resources/list'));
        $resources = $result['data']['result']['resources'] ?? null;
        self::assertIsArray($resources);
        self::assertNotEmpty($resources);
    }

    // ── prompts/list ─────────────────────────────────────────────────────────

    public function testSsePromptsListReturnsPrompts(): void
    {
        $result = $this->sse($this->jsonRpc('prompts/list'));
        $prompts = $result['data']['result']['prompts'] ?? null;
        self::assertIsArray($prompts);
        self::assertNotEmpty($prompts);
    }
}
