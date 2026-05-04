<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Integration;

/**
 * Tests for token resolution priority:
 *   1. Bearer header
 *   2. arguments['token']
 *   3. $_SESSION['token']
 *   4. No token → 401
 */
class McpServerTokenTest extends AbstractMcpTest
{
    // ── Bearer header ─────────────────────────────────────────────────────────

    public function testBearerHeaderTokenIsForwardedToApi(): void
    {
        $this->http->addResponse('{"id":1}', 201);
        $this->post(
            $this->jsonRpc('tools/call', ['name' => 'createMember', 'arguments' => ['join_date' => '2025-01-01']]),
            'Bearer my-header-token',
        );
        $headers    = $this->http->getLastCall()['headers'];
        $authHeader = array_values(array_filter($headers, fn($h) => str_starts_with($h, 'Authorization:')));
        self::assertStringContainsString('my-header-token', $authHeader[0] ?? '');
    }

    // ── arguments['token'] ────────────────────────────────────────────────────

    public function testArgumentsTokenUsedWhenNoBearerHeader(): void
    {
        $this->http->addResponse('{"id":1}', 201);
        $result = $this->post(
            $this->jsonRpc('tools/call', ['name' => 'createMember', 'arguments' => ['token' => 'arg-token', 'join_date' => '2025-01-01']]),
            '',  // empty auth header
        );
        $toolResult = $result['data']['result'] ?? null;
        self::assertNotNull($toolResult);
        self::assertFalse($toolResult['isError']);
        $headers    = $this->http->getLastCall()['headers'];
        $authHeader = array_values(array_filter($headers, fn($h) => str_starts_with($h, 'Authorization:')));
        self::assertStringContainsString('arg-token', $authHeader[0] ?? '');
    }

    // ── No token at all → 401 ─────────────────────────────────────────────────

    public function testNoTokenReturnsHttp401(): void
    {
        $result = $this->post(
            $this->jsonRpc('tools/call', ['name' => 'createMember', 'arguments' => ['join_date' => '2025-01-01']]),
            '',  // no bearer header, no token in arguments
        );
        self::assertSame(401, $result['status']);
    }

    // ── Bearer header wins over arguments['token'] ────────────────────────────

    public function testBearerHeaderTakesPrecedenceOverArgumentToken(): void
    {
        $this->http->addResponse('{"id":1}', 201);
        $result = $this->post(
            $this->jsonRpc('tools/call', ['name' => 'createMember', 'arguments' => ['token' => 'arg-token', 'join_date' => '2025-01-01']]),
            'Bearer header-wins',
        );
        $headers    = $this->http->getLastCall()['headers'];
        $authHeader = array_values(array_filter($headers, fn($h) => str_starts_with($h, 'Authorization:')));
        self::assertStringContainsString('header-wins', $authHeader[0] ?? '');
        self::assertStringNotContainsString('arg-token', $authHeader[0] ?? '');
    }

    // ── tools/list does NOT require a token ───────────────────────────────────

    public function testToolsListDoesNotRequireToken(): void
    {
        $result = $this->post($this->jsonRpc('tools/list'), '');
        self::assertSame(200, $result['status']);
        self::assertIsArray($result['data']['result']['tools'] ?? null);
    }

    // ── initialize does NOT require a token ───────────────────────────────────

    public function testInitializeDoesNotRequireToken(): void
    {
        $result = $this->post(
            $this->jsonRpc('initialize', ['protocolVersion' => '2025-11-25', 'clientInfo' => ['name' => 'test', 'version' => '1.0']]),
            '',
        );
        self::assertSame(200, $result['status']);
    }

    // ── API 401 → HTTP 401 ────────────────────────────────────────────────────

    public function testApiUnauthorizedResponseReturnsHttp401(): void
    {
        $this->http->addResponse('Unauthorized', 401);
        $result = $this->post(
            $this->jsonRpc('tools/call', ['name' => 'createMember', 'arguments' => ['join_date' => '2025-01-01']]),
            'Bearer valid-but-expired',
        );
        self::assertSame(401, $result['status']);
    }

    // ── Session token fallback ────────────────────────────────────────────────

    public function testSessionTokenFallback(): void
    {
        // Store a token in $_SESSION manually to simulate a session that has one
        $_SESSION['token'] = 'session-token';

        $this->http->addResponse('{"id":1}', 201);
        $result = $this->post(
            $this->jsonRpc('tools/call', ['name' => 'createMember', 'arguments' => ['join_date' => '2025-01-01']]),
            '',  // no bearer header
        );
        $toolResult = $result['data']['result'] ?? null;
        self::assertNotNull($toolResult, json_encode($result['data']));
        self::assertFalse($toolResult['isError']);
        $headers    = $this->http->getLastCall()['headers'];
        $authHeader = array_values(array_filter($headers, fn($h) => str_starts_with($h, 'Authorization:')));
        self::assertStringContainsString('session-token', $authHeader[0] ?? '');
    }
}
