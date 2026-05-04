<?php

declare(strict_types=1);

namespace EasyVerein\Mcp;

interface HttpClientInterface
{
    /**
     * @return array{body: string, statusCode: int, headers: array<string, string>}
     */
    public function execute(string $method, string $url, array $headers, ?string $body): array;
}
