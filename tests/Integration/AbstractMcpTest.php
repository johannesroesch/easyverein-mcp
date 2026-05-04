<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Integration;

use EasyVerein\Mcp\ApiClient;
use EasyVerein\Mcp\McpServer;
use EasyVerein\Mcp\Tests\MockHttpClient;
use PHPUnit\Framework\TestCase;

abstract class AbstractMcpTest extends TestCase
{
    protected MockHttpClient $http;
    protected ApiClient      $apiClient;
    protected McpServer      $server;

    protected function setUp(): void
    {
        $this->http      = new MockHttpClient();
        $this->apiClient = new ApiClient('https://easyverein.com/api/v3.0', $this->http);
        $this->server    = new McpServer($this->apiClient);
        $_SESSION        = [];
    }

    protected function post(string $body, string $authHeader = 'Bearer test-token', string $sessionId = '', string $protocolVersion = '2025-11-25'): array
    {
        $result = $this->server->handleStreamablePost($sessionId, $protocolVersion, '', $authHeader, 'application/json', $body);
        return [
            'status'  => $result['status'],
            'headers' => $result['headers'],
            'data'    => json_decode($result['body'], true),
        ];
    }

    protected function jsonRpc(string $method, array $params = [], mixed $id = 1): string
    {
        return json_encode(['jsonrpc' => '2.0', 'method' => $method, 'params' => $params, 'id' => $id]);
    }

    protected function initialize(string $protocolVersion = '2025-11-25'): array
    {
        return $this->post(
            $this->jsonRpc('initialize', ['protocolVersion' => $protocolVersion, 'clientInfo' => ['name' => 'test', 'version' => '1.0']]),
            'Bearer test-token',
            '',
            $protocolVersion,
        );
    }
}
