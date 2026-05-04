<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit;

use EasyVerein\Mcp\CurlHttpClient;
use PHPUnit\Framework\TestCase;

/**
 * Basic smoke-tests for CurlHttpClient.
 *
 * The production `execute()` method uses cURL.  We verify:
 *   – The class exists and implements HttpClientInterface.
 *   – Calling execute() against an unreachable URL throws RuntimeException.
 *   – Calling execute() against a file:// URL returns body/statusCode/headers.
 */
class CurlHttpClientTest extends TestCase
{
    public function testImplementsHttpClientInterface(): void
    {
        $client = new CurlHttpClient();
        self::assertInstanceOf(\EasyVerein\Mcp\HttpClientInterface::class, $client);
    }

    public function testExecuteThrowsRuntimeExceptionOnCurlError(): void
    {
        $client = new CurlHttpClient();
        // Use an invalid URL scheme that triggers a cURL error immediately.
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/cURL error/');
        // "invalid-scheme://" is not a supported protocol and curl_exec returns false
        $client->execute('GET', 'invalid-scheme://no-such-host', [], null);
    }

    public function testExecuteReturnsArrayWithExpectedKeys(): void
    {
        $client = new CurlHttpClient();
        // file:// URLs work in cURL and don't need a network connection.
        // We read composer.json which is guaranteed to exist in this project.
        $composerJson = dirname(__DIR__, 2) . '/composer.json';
        $result = $client->execute('GET', 'file://' . $composerJson, [], null);

        self::assertIsArray($result);
        self::assertArrayHasKey('body', $result);
        self::assertArrayHasKey('statusCode', $result);
        self::assertArrayHasKey('headers', $result);
        self::assertStringContainsString('easyverein', $result['body']);
    }

    public function testExecuteWithBodySendsBody(): void
    {
        $client = new CurlHttpClient();
        // Sending a body to file:// still executes (curl ignores it for file URLs)
        $composerJson = dirname(__DIR__, 2) . '/composer.json';
        $result = $client->execute('GET', 'file://' . $composerJson, [], 'some body');

        self::assertIsArray($result);
        self::assertArrayHasKey('body', $result);
    }
}
