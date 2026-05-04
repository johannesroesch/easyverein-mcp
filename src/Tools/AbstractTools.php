<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tools;

use EasyVerein\Mcp\ApiClient;

abstract class AbstractTools
{
    public function __construct(protected readonly ApiClient $client) {}

    protected function bodyFrom(array $p, array $fields, array $urlFields = []): string
    {
        $body = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $p)) {
                $value = $p[$field];
                if (isset($urlFields[$field]) && is_int($value)) {
                    $value = $this->client->urlRef($urlFields[$field], $value);
                }
                $body[$field] = $value;
            }
        }
        return json_encode($body);
    }

    protected function pagination(array $p): array
    {
        $q = [];
        if (isset($p['limit']))  $q['limit']  = (int) $p['limit'];
        if (isset($p['page']))   $q['page']   = (int) $p['page'];
        return $q;
    }

    protected function optional(array $p, string $key): array
    {
        return isset($p[$key]) ? [$key => $p[$key]] : [];
    }

    protected function deleted(string $token, string $path, string $label): string
    {
        $this->client->delete($token, $path);
        return json_encode(['message' => "$label deleted."]);
    }
}
