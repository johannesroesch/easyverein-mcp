<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests;

use EasyVerein\Mcp\HttpClientInterface;

class MockHttpClient implements HttpClientInterface
{
    private array $responses = [];
    private array $calls     = [];

    public function addResponse(string $body, int $statusCode = 200, array $headers = []): void
    {
        $this->responses[] = ['body' => $body, 'statusCode' => $statusCode, 'headers' => $headers];
    }

    public function execute(string $method, string $url, array $headers, ?string $body): array
    {
        $this->calls[] = ['method' => $method, 'url' => $url, 'headers' => $headers, 'body' => $body];

        if (empty($this->responses)) {
            return ['body' => '', 'statusCode' => 200, 'headers' => []];
        }

        return array_shift($this->responses);
    }

    public function getCalls(): array
    {
        return $this->calls;
    }

    public function getLastCall(): array
    {
        return end($this->calls) ?: [];
    }

    public function getCallCount(): int
    {
        return count($this->calls);
    }
}
