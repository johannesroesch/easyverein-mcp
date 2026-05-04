<?php

declare(strict_types=1);

namespace EasyVerein\Mcp;

class CurlHttpClient implements HttpClientInterface
{
    public function execute(string $method, string $url, array $headers, ?string $body): array
    {
        $responseHeaders = [];

        $ch = curl_init($url);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_HEADERFUNCTION => function ($ch, string $line) use (&$responseHeaders): int {
                $trimmed = trim($line);
                if (str_contains($trimmed, ':')) {
                    [$name, $value] = explode(':', $trimmed, 2);
                    $responseHeaders[strtolower(trim($name))] = trim($value);
                }
                return strlen($line);
            },
        ]);

        $response   = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('cURL error: ' . $error);
        }
        curl_close($ch);

        return [
            'body'       => (string) $response,
            'statusCode' => $statusCode,
            'headers'    => $responseHeaders,
        ];
    }
}
