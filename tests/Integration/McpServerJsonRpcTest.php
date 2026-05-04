<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Integration;

class McpServerJsonRpcTest extends AbstractMcpTest
{
    // ── Parse Errors ─────────────────────────────────────────────────────────

    public function testInvalidJsonReturnsParseError(): void
    {
        $result = $this->post('not valid json');
        self::assertSame(-32700, $result['data']['error']['code']);
    }

    public function testMissingJsonrpcFieldReturnsInvalidRequest(): void
    {
        $result = $this->post(json_encode(['method' => 'ping', 'id' => 1]));
        self::assertSame(-32600, $result['data']['error']['code']);
    }

    public function testMissingMethodFieldReturnsInvalidRequest(): void
    {
        $result = $this->post(json_encode(['jsonrpc' => '2.0', 'id' => 1]));
        self::assertSame(-32600, $result['data']['error']['code']);
    }

    public function testBatchRequestReturnsError(): void
    {
        $result = $this->post(json_encode([
            ['jsonrpc' => '2.0', 'method' => 'ping', 'id' => 1],
            ['jsonrpc' => '2.0', 'method' => 'ping', 'id' => 2],
        ]));
        self::assertSame(-32600, $result['data']['error']['code']);
    }

    public function testUnknownMethodReturnsMethodNotFound(): void
    {
        $this->initialize();
        $result = $this->post($this->jsonRpc('unknown/method'));
        self::assertSame(-32601, $result['data']['error']['code']);
    }

    // ── Basic Methods ─────────────────────────────────────────────────────────

    public function testPingReturnsEmptyObject(): void
    {
        $result = $this->post($this->jsonRpc('ping'));
        self::assertArrayHasKey('result', $result['data']);
        self::assertEmpty($result['data']['result']);
    }

    public function testLoggingSetLevelReturnsEmptyObject(): void
    {
        $result = $this->post($this->jsonRpc('logging/setLevel', ['level' => 'info']));
        self::assertArrayHasKey('result', $result['data']);
    }

    // ── initialize ────────────────────────────────────────────────────────────

    public function testInitializeReturnsCapabilitiesAndProtocolVersion(): void
    {
        $result = $this->initialize();
        $res    = $result['data']['result'];
        self::assertArrayHasKey('capabilities', $res);
        self::assertArrayHasKey('protocolVersion', $res);
        self::assertSame('2025-11-25', $res['protocolVersion']);
    }

    public function testInitializeReturnsServerInfo(): void
    {
        $result = $this->initialize();
        $res    = $result['data']['result'];
        self::assertArrayHasKey('serverInfo', $res);
        self::assertArrayHasKey('name', $res['serverInfo']);
    }

    public function testInitializeReturnsSessionIdHeader(): void
    {
        $result = $this->initialize();
        self::assertArrayHasKey('MCP-Session-Id', $result['headers']);
        self::assertNotEmpty($result['headers']['MCP-Session-Id']);
    }

    public function testInitializeHasInstructions(): void
    {
        $result = $this->initialize();
        $res    = $result['data']['result'];
        self::assertArrayHasKey('instructions', $res);
        self::assertNotEmpty($res['instructions']);
    }

    public function testInitializeCapabilitiesHaveTools(): void
    {
        $result = $this->initialize();
        $caps   = $result['data']['result']['capabilities'];
        self::assertArrayHasKey('tools', $caps);
    }

    public function testInitializeCapabilitiesHaveResources(): void
    {
        $result = $this->initialize();
        $caps   = $result['data']['result']['capabilities'];
        self::assertArrayHasKey('resources', $caps);
    }

    // ── JSON-RPC id passthrough ───────────────────────────────────────────────

    public function testResponseIdMatchesRequestId(): void
    {
        $result = $this->post($this->jsonRpc('ping', [], 42));
        self::assertSame(42, $result['data']['id']);
    }

    public function testStringIdPassedThrough(): void
    {
        $result = $this->post($this->jsonRpc('ping', [], 'abc-123'));
        self::assertSame('abc-123', $result['data']['id']);
    }
}
