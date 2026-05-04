<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit\Tools;

use EasyVerein\Mcp\ApiClient;
use EasyVerein\Mcp\Tests\MockHttpClient;
use PHPUnit\Framework\TestCase;

abstract class AbstractToolsTest extends TestCase
{
    protected MockHttpClient $http;
    protected ApiClient      $apiClient;

    protected function setUp(): void
    {
        $this->http      = new MockHttpClient();
        $this->apiClient = new ApiClient('https://easyverein.com/api/v3.0', $this->http);
    }

    protected function addOk(string $body = '{"id":1}', int $status = 200): void
    {
        $this->http->addResponse($body, $status);
    }

    protected function addDeleted(): void
    {
        $this->http->addResponse('', 204);
    }

    protected function lastCall(): array
    {
        return $this->http->getLastCall();
    }

    protected function assertGetTo(string $pathPrefix): void
    {
        $call = $this->lastCall();
        self::assertSame('GET', $call['method']);
        self::assertStringContainsString($pathPrefix, $call['url']);
    }

    protected function assertPostTo(string $pathPrefix): void
    {
        $call = $this->lastCall();
        self::assertSame('POST', $call['method']);
        self::assertStringContainsString($pathPrefix, $call['url']);
    }

    protected function assertPatchTo(string $pathPrefix): void
    {
        $call = $this->lastCall();
        self::assertSame('PATCH', $call['method']);
        self::assertStringContainsString($pathPrefix, $call['url']);
    }

    protected function assertDeleteTo(string $pathPrefix): void
    {
        $call = $this->lastCall();
        self::assertSame('DELETE', $call['method']);
        self::assertStringContainsString($pathPrefix, $call['url']);
    }

    protected function assertBodyContains(string $key, mixed $value): void
    {
        $body = json_decode($this->lastCall()['body'] ?? '{}', true);
        self::assertArrayHasKey($key, $body);
        self::assertSame($value, $body[$key]);
    }

    protected function assertBodyNotContains(string $key): void
    {
        $body = json_decode($this->lastCall()['body'] ?? '{}', true);
        self::assertArrayNotHasKey($key, $body);
    }

    protected function assertDeletedMessage(string $result): void
    {
        $data = json_decode($result, true);
        self::assertArrayHasKey('message', $data);
        self::assertStringContainsString('deleted', $data['message']);
    }
}
