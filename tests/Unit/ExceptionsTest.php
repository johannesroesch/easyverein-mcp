<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit;

use EasyVerein\Mcp\AuthException;
use EasyVerein\Mcp\ExitException;
use EasyVerein\Mcp\RateLimitException;
use PHPUnit\Framework\TestCase;

class ExceptionsTest extends TestCase
{
    public function testAuthExceptionIsRuntimeException(): void
    {
        $e = new AuthException('Access denied', 403);
        self::assertInstanceOf(\RuntimeException::class, $e);
        self::assertSame('Access denied', $e->getMessage());
        self::assertSame(403, $e->getCode());
    }

    public function testAuthExceptionMessagePassedThrough(): void
    {
        $e = new AuthException('Token expired');
        self::assertSame('Token expired', $e->getMessage());
    }

    public function testExitExceptionIsRuntimeException(): void
    {
        $e = new ExitException();
        self::assertInstanceOf(\RuntimeException::class, $e);
    }

    public function testRateLimitExceptionIsRuntimeException(): void
    {
        $e = new RateLimitException(30);
        self::assertInstanceOf(\RuntimeException::class, $e);
    }

    public function testRateLimitExceptionGetRetryAfter(): void
    {
        $e = new RateLimitException(42);
        self::assertSame(42, $e->getRetryAfter());
    }

    public function testRateLimitExceptionMessageContainsSeconds(): void
    {
        $e = new RateLimitException(15);
        self::assertStringContainsString('15', $e->getMessage());
    }

    public function testRateLimitExceptionWithBodyIgnoresBody(): void
    {
        $e = new RateLimitException(5, '{"detail":"throttled"}');
        self::assertSame(5, $e->getRetryAfter());
    }
}
