<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Integration;

class McpServerToolsTest extends AbstractMcpTest
{
    // ── tools/list ────────────────────────────────────────────────────────────

    public function testToolsListReturnsTools(): void
    {
        $result = $this->post($this->jsonRpc('tools/list'));
        $tools  = $result['data']['result']['tools'];
        self::assertIsArray($tools);
        self::assertNotEmpty($tools);
    }

    public function testToolsListInputSchemaHasRequiredFields(): void
    {
        $result = $this->post($this->jsonRpc('tools/list'));
        foreach ($result['data']['result']['tools'] as $tool) {
            self::assertArrayHasKey('inputSchema', $tool);
            self::assertArrayHasKey('properties', $tool['inputSchema']);
        }
    }

    public function testToolsListAdditionalPropertiesFalse(): void
    {
        $result = $this->post($this->jsonRpc('tools/list'));
        foreach ($result['data']['result']['tools'] as $tool) {
            self::assertFalse($tool['inputSchema']['additionalProperties'] ?? true);
        }
    }

    public function testToolsListHasAnnotations(): void
    {
        $result = $this->post($this->jsonRpc('tools/list'));
        foreach ($result['data']['result']['tools'] as $tool) {
            self::assertArrayHasKey('annotations', $tool);
        }
    }

    // ── tools/call ────────────────────────────────────────────────────────────

    public function testToolsCallSuccessReturnsContentAndNoError(): void
    {
        $this->http->addResponse('{"id":1}', 201);
        $result = $this->post(
            $this->jsonRpc('tools/call', ['name' => 'createMember', 'arguments' => ['token' => 'tok', 'join_date' => '2025-01-01']])
        );
        $toolResult = $result['data']['result'] ?? null;
        self::assertNotNull($toolResult, 'Expected result, got: ' . json_encode($result['data']));
        self::assertArrayHasKey('content', $toolResult);
        self::assertFalse($toolResult['isError']);
    }

    public function testToolsCallUnknownToolReturnsIsError(): void
    {
        $result = $this->post(
            $this->jsonRpc('tools/call', ['name' => 'totallyFakeToolName', 'arguments' => ['token' => 'tok']])
        );
        // Unknown tool → InvalidArgumentException → -32602
        self::assertSame(-32602, $result['data']['error']['code'] ?? null);
    }

    public function testToolsCallApiErrorReturnsIsError(): void
    {
        $this->http->addResponse('Server Error', 500);
        $result = $this->post(
            $this->jsonRpc('tools/call', ['name' => 'createMember', 'arguments' => ['token' => 'tok', 'join_date' => '2025-01-01']])
        );
        $toolResult = $result['data']['result'] ?? null;
        self::assertNotNull($toolResult, json_encode($result['data']));
        self::assertTrue($toolResult['isError']);
    }

    public function testToolsCallAuthExceptionReturnsHttp401(): void
    {
        $this->http->addResponse('Unauthorized', 401);
        $result = $this->post(
            $this->jsonRpc('tools/call', ['name' => 'createMember', 'arguments' => ['token' => 'tok', 'join_date' => '2025-01-01']])
        );
        // AuthException from API → 401 HTTP status
        self::assertSame(401, $result['status']);
    }

    public function testToolCallWithBearerTokenFromHeader(): void
    {
        $this->http->addResponse('{"id":1}', 201);
        $result = $this->post(
            $this->jsonRpc('tools/call', ['name' => 'createMember', 'arguments' => ['join_date' => '2025-01-01']]),
            'Bearer header-token'
        );
        $toolResult = $result['data']['result'] ?? null;
        self::assertNotNull($toolResult, json_encode($result['data']));
        self::assertFalse($toolResult['isError']);
        // Check the token was forwarded
        $headers    = $this->http->getLastCall()['headers'];
        $authHeader = array_values(array_filter($headers, fn ($h) => str_starts_with($h, 'Authorization:')));
        self::assertStringContainsString('header-token', $authHeader[0] ?? '');
    }

    // ── Annotations ───────────────────────────────────────────────────────────

    public function testListToolAnnotationsReadOnly(): void
    {
        $result = $this->post($this->jsonRpc('tools/list'));
        $tools  = $result['data']['result']['tools'];
        // createMember is a mutating tool, NOT readOnly
        $createTool = array_values(array_filter($tools, fn ($t) => $t['name'] === 'createMember'))[0] ?? null;
        self::assertNotNull($createTool);
        self::assertFalse($createTool['annotations']['readOnlyHint']);
    }

    public function testDeleteToolAnnotationsDestructive(): void
    {
        $result = $this->post($this->jsonRpc('tools/list'));
        $tools  = $result['data']['result']['tools'];
        $deleteTool = array_values(array_filter($tools, fn ($t) => $t['name'] === 'deleteMember'))[0] ?? null;
        self::assertNotNull($deleteTool);
        self::assertTrue($deleteTool['annotations']['destructiveHint']);
        self::assertFalse($deleteTool['annotations']['readOnlyHint']);
    }
}
