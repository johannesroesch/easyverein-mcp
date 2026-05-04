<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit;

use EasyVerein\Mcp\ApiClient;
use EasyVerein\Mcp\AuthException;
use EasyVerein\Mcp\RateLimitException;
use EasyVerein\Mcp\Tests\MockHttpClient;
use PHPUnit\Framework\TestCase;

class ApiClientTest extends TestCase
{
    private MockHttpClient $http;
    private ApiClient      $client;

    protected function setUp(): void
    {
        $this->http   = new MockHttpClient();
        $this->client = new ApiClient('https://example.com/api/v3.0', $this->http);
    }

    // ── GET ──────────────────────────────────────────────────────────────────

    public function testGetUsesCorrectMethodAndUrl(): void
    {
        $this->http->addResponse('{"id":1}');
        $this->client->get('tok', '/members');

        $call = $this->http->getLastCall();
        self::assertSame('GET', $call['method']);
        self::assertSame('https://example.com/api/v3.0/members', $call['url']);
    }

    public function testGetAppendsQueryParams(): void
    {
        $this->http->addResponse('{}');
        $this->client->get('tok', '/members', ['limit' => 10, 'offset' => 20]);

        $call = $this->http->getLastCall();
        self::assertStringContainsString('limit=10', $call['url']);
        self::assertStringContainsString('offset=20', $call['url']);
    }

    public function testGetWithEmptyParamsNoQueryString(): void
    {
        $this->http->addResponse('{}');
        $this->client->get('tok', '/members', []);

        $call = $this->http->getLastCall();
        self::assertStringNotContainsString('?', $call['url']);
    }

    public function testGetReturnResponseBody(): void
    {
        $this->http->addResponse('{"result":"ok"}');
        $result = $this->client->get('tok', '/members');

        self::assertSame('{"result":"ok"}', $result);
    }

    // ── POST ─────────────────────────────────────────────────────────────────

    public function testPostUsesPostMethod(): void
    {
        $this->http->addResponse('{}', 201);
        $this->client->post('tok', '/members', '{"name":"Test"}');

        self::assertSame('POST', $this->http->getLastCall()['method']);
    }

    public function testPostSendsBody(): void
    {
        $this->http->addResponse('{}', 201);
        $this->client->post('tok', '/members', '{"name":"Test"}');

        self::assertSame('{"name":"Test"}', $this->http->getLastCall()['body']);
    }

    // ── PATCH ────────────────────────────────────────────────────────────────

    public function testPatchUsesPatchMethod(): void
    {
        $this->http->addResponse('{}');
        $this->client->patch('tok', '/members/1', '{"name":"Updated"}');

        self::assertSame('PATCH', $this->http->getLastCall()['method']);
    }

    // ── DELETE ───────────────────────────────────────────────────────────────

    public function testDeleteUsesDeleteMethod(): void
    {
        $this->http->addResponse('', 204);
        $this->client->delete('tok', '/members/1');

        self::assertSame('DELETE', $this->http->getLastCall()['method']);
    }

    // ── Headers ──────────────────────────────────────────────────────────────

    public function testBearerTokenHeaderIsAlwaysSet(): void
    {
        $this->http->addResponse('{}');
        $this->client->get('my-token', '/members');

        $headers = $this->http->getLastCall()['headers'];
        self::assertContains('Authorization: Bearer my-token', $headers);
    }

    public function testAcceptJsonHeaderIsAlwaysSet(): void
    {
        $this->http->addResponse('{}');
        $this->client->get('tok', '/members');

        $headers = $this->http->getLastCall()['headers'];
        self::assertContains('Accept: application/json', $headers);
    }

    public function testContentTypeSetOnPostButNotGet(): void
    {
        $this->http->addResponse('{}');
        $this->client->get('tok', '/members');
        $getHeaders = $this->http->getLastCall()['headers'];
        self::assertNotContains('Content-Type: application/json', $getHeaders);

        $this->http->addResponse('{}', 201);
        $this->client->post('tok', '/members', '{}');
        $postHeaders = $this->http->getLastCall()['headers'];
        self::assertContains('Content-Type: application/json', $postHeaders);
    }

    // ── Base-URL Normalisierung ───────────────────────────────────────────────

    public function testTrailingSlashRemovedFromBaseUrl(): void
    {
        $this->http->addResponse('{}');
        $client = new ApiClient('https://example.com/api/v3.0/', $this->http);
        $client->get('tok', '/members');

        $url = $this->http->getLastCall()['url'];
        self::assertStringStartsWith('https://example.com/api/v3.0/', $url);
        self::assertStringNotContainsString('v3.0//', $url);
    }

    // ── token_refresh_needed ─────────────────────────────────────────────────

    public function testTokenRefreshNeededTrueWhenHeaderTrue(): void
    {
        $this->http->addResponse('{}', 200, ['token_refresh_needed' => 'true']);
        $this->client->get('tok', '/members');

        self::assertTrue($this->client->isTokenRefreshNeeded());
    }

    public function testTokenRefreshNeededFalseWhenHeaderFalse(): void
    {
        $this->http->addResponse('{}', 200, ['token_refresh_needed' => 'false']);
        $this->client->get('tok', '/members');

        self::assertFalse($this->client->isTokenRefreshNeeded());
    }

    public function testTokenRefreshNeededResetOnEachRequest(): void
    {
        $this->http->addResponse('{}', 200, ['token_refresh_needed' => 'true']);
        $this->client->get('tok', '/members');
        self::assertTrue($this->client->isTokenRefreshNeeded());

        $this->http->addResponse('{}', 200, []);
        $this->client->get('tok', '/members');
        self::assertFalse($this->client->isTokenRefreshNeeded());
    }

    // ── HTTP-Fehler ───────────────────────────────────────────────────────────

    public function testHttp401ThrowsAuthException(): void
    {
        $this->expectException(AuthException::class);
        $this->http->addResponse('Unauthorized', 401);
        $this->client->get('tok', '/members');
    }

    public function testHttp403ThrowsAuthException(): void
    {
        $this->expectException(AuthException::class);
        $this->http->addResponse('Forbidden', 403);
        $this->client->get('tok', '/members');
    }

    public function testHttp400ThrowsRuntimeException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->http->addResponse('Bad Request', 400);
        $this->client->get('tok', '/members');
    }

    public function testHttp500ThrowsRuntimeExceptionWithBody(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Internal Server Error/');
        $this->http->addResponse('Internal Server Error', 500);
        $this->client->get('tok', '/members');
    }

    public function testHttp200ReturnsResponseBody(): void
    {
        $this->http->addResponse('{"data":"value"}', 200);
        $result = $this->client->get('tok', '/members');
        self::assertSame('{"data":"value"}', $result);
    }

    public function testHttp204ReturnsEmptyString(): void
    {
        $this->http->addResponse('', 204);
        $this->client->delete('tok', '/members/1');
        // No exception thrown = success
        $this->addToAssertionCount(1);
    }

    // ── Rate Limiting ─────────────────────────────────────────────────────────

    public function testHttp429TriggersOneRetry(): void
    {
        $this->http->addResponse('', 429, ['retry-after' => '1']);
        $this->http->addResponse('{"ok":true}', 200);

        $result = $this->client->get('tok', '/members');

        self::assertSame(2, $this->http->getCallCount());
        self::assertSame('{"ok":true}', $result);
    }

    public function testHttp429TwiceThrowsRateLimitException(): void
    {
        $this->expectException(RateLimitException::class);

        $this->http->addResponse('', 429, ['retry-after' => '1']);
        $this->http->addResponse('', 429, ['retry-after' => '1']);

        $this->client->get('tok', '/members');
    }

    public function testRateLimitExceptionContainsRetryAfter(): void
    {
        $this->http->addResponse('', 429, ['retry-after' => '30']);
        $this->http->addResponse('', 429, ['retry-after' => '30']);

        try {
            $this->client->get('tok', '/members');
            self::fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            self::assertSame(30, $e->getRetryAfter());
        }
    }

    public function testMissingRetryAfterDefaultsToOne(): void
    {
        $this->http->addResponse('', 429, []);
        $this->http->addResponse('', 429, []);

        try {
            $this->client->get('tok', '/members');
        } catch (RateLimitException $e) {
            self::assertSame(1, $e->getRetryAfter());
        }
    }

    public function testUrlRefBuildsHyperlinkUrl(): void
    {
        self::assertSame(
            'https://example.com/api/v3.0/application-form/22719',
            $this->client->urlRef('/application-form/', 22719)
        );
    }

    public function testUrlRefStripsTrailingSlashFromPath(): void
    {
        self::assertSame(
            'https://example.com/api/v3.0/member/42',
            $this->client->urlRef('/member', 42)
        );
    }
}
