<?php

declare(strict_types=1);

namespace EasyVerein\Mcp;

class RateLimitException extends \RuntimeException
{
    public function __construct(private readonly int $retryAfter)
    {
        parent::__construct('EasyVerein API: Rate limit erreicht. Bitte ' . $retryAfter . 's warten.');
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
