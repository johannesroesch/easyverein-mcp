<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Integration;

/**
 * Additional coverage for McpServer methods not exercised by other test files:
 *  - handleStreamableDelete
 *  - completion/complete (handleCompletion → completePromptArg / completeResourceArg)
 *  - prompts/get
 *  - resources/read with static URI (no template)
 *  - notifications/initialized (via handleStreamablePost)
 *  - session token stored during initialize
 */
class McpServerExtrasTest extends AbstractMcpTest
{
    // ── handleStreamableDelete ────────────────────────────────────────────────

    public function testDeleteWithoutSessionIdReturns400(): void
    {
        $result = $this->server->handleStreamableDelete('', '');
        self::assertSame(400, $result['status']);
    }

    public function testDeleteWithUnknownSessionIdReturns200(): void
    {
        // A non-existent session still returns 200 (graceful)
        $result = $this->server->handleStreamableDelete('nonexistent-session-id', '');
        self::assertSame(200, $result['status']);
    }

    public function testDeleteWithBadOriginReturns403(): void
    {
        $result = $this->server->handleStreamableDelete('some-session', 'https://evil.com');
        self::assertSame(403, $result['status']);
    }

    // ── prompts/get ───────────────────────────────────────────────────────────

    public function testPromptsGetReturnsMessages(): void
    {
        $result = $this->post($this->jsonRpc('prompts/get', ['name' => 'member-search', 'arguments' => ['query' => 'Max']]));
        self::assertArrayHasKey('messages', $result['data']['result'] ?? []);
    }

    public function testPromptsGetUnknownPromptReturnsError(): void
    {
        $result = $this->post($this->jsonRpc('prompts/get', ['name' => 'no-such-prompt', 'arguments' => []]));
        self::assertSame(-32602, $result['data']['error']['code'] ?? null);
    }

    // ── completion/complete ───────────────────────────────────────────────────

    public function testCompletionCompleteForPromptMonthArgument(): void
    {
        $result = $this->post($this->jsonRpc('completion/complete', [
            'ref'      => ['type' => 'ref/prompt', 'name' => 'member-search'],
            'argument' => ['name' => 'month', 'value' => '1'],
        ]));
        $completion = $result['data']['result']['completion'] ?? null;
        self::assertIsArray($completion);
        self::assertArrayHasKey('values', $completion);
    }

    public function testCompletionCompleteForPromptYearArgument(): void
    {
        $result = $this->post($this->jsonRpc('completion/complete', [
            'ref'      => ['type' => 'ref/prompt', 'name' => 'member-search'],
            'argument' => ['name' => 'year', 'value' => '202'],
        ]));
        $completion = $result['data']['result']['completion'] ?? null;
        self::assertIsArray($completion);
        self::assertNotEmpty($completion['values']);
    }

    public function testCompletionCompleteUnknownRefTypeReturnsEmpty(): void
    {
        $result = $this->post($this->jsonRpc('completion/complete', [
            'ref'      => ['type' => 'ref/unknown', 'name' => 'foo'],
            'argument' => ['name' => 'bar', 'value' => 'baz'],
        ]));
        $completion = $result['data']['result']['completion'] ?? null;
        self::assertIsArray($completion);
        self::assertSame([], $completion['values'] ?? [null]);
    }

    public function testCompletionCompleteForResourceArgument(): void
    {
        // resource completions require a token and make an API call
        $this->http->addResponse('{"count":1,"next":null,"previous":null,"results":[{"name_for_sorting":"Max Muster","id":1}]}', 200);
        $result = $this->post($this->jsonRpc('completion/complete', [
            'ref'      => ['type' => 'ref/resource', 'uri' => 'easyverein://member/{id}'],
            'argument' => ['name' => 'id', 'value' => '1'],
        ]));
        $completion = $result['data']['result']['completion'] ?? null;
        self::assertIsArray($completion);
        self::assertArrayHasKey('values', $completion);
    }

    public function testCompletionCompleteForResourceLimitArgument(): void
    {
        $result = $this->post($this->jsonRpc('completion/complete', [
            'ref'      => ['type' => 'ref/resource', 'uri' => 'easyverein://member/{?limit,page}'],
            'argument' => ['name' => 'limit', 'value' => '1'],
        ]));
        $completion = $result['data']['result']['completion'] ?? null;
        self::assertIsArray($completion);
        self::assertNotEmpty($completion['values']);
    }

    public function testCompletionCompleteForResourcePageArgument(): void
    {
        $result = $this->post($this->jsonRpc('completion/complete', [
            'ref'      => ['type' => 'ref/resource', 'uri' => 'easyverein://member/{?limit,page}'],
            'argument' => ['name' => 'page', 'value' => ''],
        ]));
        $completion = $result['data']['result']['completion'] ?? null;
        self::assertIsArray($completion);
        self::assertNotEmpty($completion['values']);
    }

    // ── notifications/initialized via streamable HTTP ─────────────────────────

    public function testNotificationsInitializedReturns202(): void
    {
        // Notifications have no id field — they return 202 with empty body
        $body   = json_encode(['jsonrpc' => '2.0', 'method' => 'notifications/initialized']);
        $result = $this->server->handleStreamablePost('', '2025-11-25', '', 'Bearer tok', 'application/json', $body);
        self::assertSame(202, $result['status']);
    }

    // ── session token stored during initialize ────────────────────────────────

    public function testInitializeWithTokenStoresItInSession(): void
    {
        $initResult = $this->post(
            $this->jsonRpc('initialize', [
                'protocolVersion' => '2025-11-25',
                'clientInfo'      => ['name' => 'test', 'version' => '1.0'],
                'token'           => 'stored-session-token',
            ]),
        );
        $sessionId = $initResult['headers']['MCP-Session-Id'] ?? '';
        self::assertNotEmpty($sessionId);

        // Now use session id without bearer header — should use stored token
        $this->http->addResponse('{"id":1}', 201);
        $result = $this->server->handleStreamablePost(
            $sessionId,
            '2025-11-25',
            '',
            '',  // no bearer header
            'application/json',
            json_encode(['jsonrpc' => '2.0', 'method' => 'tools/call', 'params' => ['name' => 'createMember', 'arguments' => ['join_date' => '2025-01-01']], 'id' => 2]),
        );
        $data = json_decode($result['body'], true);
        // Should succeed using the stored token
        self::assertSame(200, $result['status']);
    }

    // ── getToolNames / getResourceNames / getPromptCount ─────────────────────

    public function testGetToolNamesReturnsArray(): void
    {
        $names = $this->server->getToolNames();
        self::assertIsArray($names);
        self::assertNotEmpty($names);
        self::assertContains('createMember', $names);
    }

    public function testGetResourceNamesReturnsArray(): void
    {
        $names = $this->server->getResourceNames();
        self::assertIsArray($names);
        self::assertNotEmpty($names);
        self::assertContains('listMembers', $names);
    }

    public function testGetPromptCountReturnsPositiveInt(): void
    {
        self::assertGreaterThan(0, $this->server->getPromptCount());
    }
}
