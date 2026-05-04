<?php

declare(strict_types=1);

namespace EasyVerein\Mcp;

class ApiClient
{
    private string              $baseUrl;
    private bool                $tokenRefreshNeeded = false;
    private HttpClientInterface $http;

    public function __construct(string $baseUrl, ?HttpClientInterface $http = null)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->http    = $http ?? new CurlHttpClient();
    }

    public function isTokenRefreshNeeded(): bool
    {
        return $this->tokenRefreshNeeded;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    // Build a hyperlinked URL for a reference field (DRF-style relations).
    // E.g. urlRef('/application-form/', 42) → 'https://easyverein.com/api/v3.0/application-form/42'
    public function urlRef(string $path, int $id): string
    {
        return $this->baseUrl . rtrim($path, '/') . '/' . $id;
    }

    public function get(string $token, string $path, array $params = []): string
    {
        $url = $this->baseUrl . $path;
        if ($params) {
            $url .= '?' . http_build_query($params);
        }
        return $this->request($token, 'GET', $url);
    }

    public function post(string $token, string $path, string $body): string
    {
        return $this->request($token, 'POST', $this->baseUrl . $path, $body);
    }

    public function patch(string $token, string $path, string $body): string
    {
        return $this->request($token, 'PATCH', $this->baseUrl . $path, $body);
    }

    public function delete(string $token, string $path): void
    {
        $this->request($token, 'DELETE', $this->baseUrl . $path);
    }

    private function request(string $token, string $method, string $url, ?string $body = null, bool $retried = false): string
    {
        $this->tokenRefreshNeeded = false;

        $headers = [
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
        ];
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
        }

        $result     = $this->http->execute($method, $url, $headers, $body);
        $response   = $result['body'];
        $statusCode = $result['statusCode'];
        $respHeaders = $result['headers'];

        if (isset($respHeaders['token_refresh_needed'])) {
            $this->tokenRefreshNeeded = strtolower($respHeaders['token_refresh_needed']) === 'true';
        }

        if ($statusCode === 429) {
            $retryAfter = max(1, (int) ($respHeaders['retry-after'] ?? 1));
            if ($retried) {
                throw new RateLimitException($retryAfter);
            }
            Logger::warning('Rate limit hit, retrying', ['retry_after' => $retryAfter, 'url' => $url]);
            sleep(min($retryAfter, 60));
            return $this->request($token, $method, $url, $body, true);
        }

        if ($statusCode === 401 || $statusCode === 403) {
            throw new AuthException('EasyVerein API: Zugriff verweigert (HTTP ' . $statusCode . '). Token ungültig oder abgelaufen.');
        }

        if ($statusCode >= 400) {
            throw new \RuntimeException('EasyVerein API error (HTTP ' . $statusCode . '): ' . $response);
        }

        return $response;
    }
}
