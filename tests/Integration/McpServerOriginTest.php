<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Integration;

class McpServerOriginTest extends AbstractMcpTest
{
    private function postWithOrigin(string $origin): array
    {
        $result = $this->server->handleStreamablePost(
            '',
            '2025-11-25',
            $origin,
            'Bearer tok',
            'application/json',
            json_encode(['jsonrpc' => '2.0', 'method' => 'ping', 'id' => 1]),
        );
        return $result;
    }

    public function testLocalhostAllowed(): void
    {
        $result = $this->postWithOrigin('http://localhost:3000');
        self::assertNotSame(403, $result['status']);
    }

    public function testLoopbackIpAllowed(): void
    {
        $result = $this->postWithOrigin('http://127.0.0.1:8080');
        self::assertNotSame(403, $result['status']);
    }

    public function testIpv6LoopbackBracketsBlocked(): void
    {
        // parse_url returns "[::1]" with brackets, which is not in ALLOWED_ORIGINS
        // This documents the current behavior — see McpServer::validateOrigin
        $result = $this->postWithOrigin('http://[::1]:8080');
        self::assertSame(403, $result['status']);
    }

    public function testExternalDomainBlocked(): void
    {
        $result = $this->postWithOrigin('https://evil.example.com');
        self::assertSame(403, $result['status']);
    }

    public function testEmptyOriginAllowed(): void
    {
        $result = $this->postWithOrigin('');
        self::assertNotSame(403, $result['status']);
    }

    public function testUnsupportedProtocolVersionReturns400(): void
    {
        $result = $this->server->handleStreamablePost(
            '',
            '2024-01-01',
            '',
            'Bearer tok',
            'application/json',
            json_encode(['jsonrpc' => '2.0', 'method' => 'tools/list', 'id' => 1]),
        );
        self::assertSame(400, $result['status']);
    }
}
