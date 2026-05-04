<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit;

use EasyVerein\Mcp\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset to INFO level before each test
        Logger::setMinLevel('INFO');
    }

    // ── init / level ─────────────────────────────────────────────────────────

    public function testInitReadsLogLevelFromEnv(): void
    {
        $_ENV['LOG_LEVEL'] = 'DEBUG';
        Logger::init();
        // DEBUG-Meldungen dürfen bei Level DEBUG ankommen
        $logged = $this->captureErrorLog(fn () => Logger::debug('test msg'));
        self::assertStringContainsString('test msg', $logged);
        unset($_ENV['LOG_LEVEL']);
    }

    public function testInitDefaultsToInfoWhenEnvMissing(): void
    {
        unset($_ENV['LOG_LEVEL']);
        Logger::init();
        // DEBUG gefiltert bei INFO-Level
        $logged = $this->captureErrorLog(fn () => Logger::debug('should be hidden'));
        self::assertSame('', $logged);
    }

    public function testInvalidLevelFallsBackToCurrentLevel(): void
    {
        Logger::setMinLevel('INFO');
        Logger::setMinLevel('NONSENSE'); // invalid → bleibt bei INFO
        $logged = $this->captureErrorLog(fn () => Logger::debug('hidden'));
        self::assertSame('', $logged);
    }

    // ── Filtering ────────────────────────────────────────────────────────────

    public function testDebugFilteredAtInfoLevel(): void
    {
        Logger::setMinLevel('INFO');
        $logged = $this->captureErrorLog(fn () => Logger::debug('debug msg'));
        self::assertSame('', $logged);
    }

    public function testDebugOutputAtDebugLevel(): void
    {
        Logger::setMinLevel('DEBUG');
        $logged = $this->captureErrorLog(fn () => Logger::debug('debug msg'));
        self::assertStringContainsString('debug msg', $logged);
    }

    public function testInfoOutputAtInfoLevel(): void
    {
        Logger::setMinLevel('INFO');
        $logged = $this->captureErrorLog(fn () => Logger::info('info msg'));
        self::assertStringContainsString('info msg', $logged);
    }

    public function testWarningOutputAtInfoLevel(): void
    {
        Logger::setMinLevel('INFO');
        $logged = $this->captureErrorLog(fn () => Logger::warning('warn msg'));
        self::assertStringContainsString('warn msg', $logged);
    }

    public function testErrorOutputAtInfoLevel(): void
    {
        Logger::setMinLevel('INFO');
        $logged = $this->captureErrorLog(fn () => Logger::error('error msg'));
        self::assertStringContainsString('error msg', $logged);
    }

    // ── NDJSON-Format ────────────────────────────────────────────────────────

    public function testOutputIsValidJson(): void
    {
        $logged = $this->captureErrorLog(fn () => Logger::info('json test'));
        $data   = $this->parseLogLine($logged);
        self::assertIsArray($data);
        self::assertNotEmpty($data);
    }

    public function testOutputContainsTsLevelMsgFields(): void
    {
        $logged = $this->captureErrorLog(fn () => Logger::info('fields test'));
        $data   = $this->parseLogLine($logged);
        self::assertArrayHasKey('ts', $data);
        self::assertArrayHasKey('level', $data);
        self::assertArrayHasKey('msg', $data);
    }

    public function testLevelFieldMatchesCalledMethod(): void
    {
        $logged = $this->captureErrorLog(fn () => Logger::warning('level check'));
        $data   = $this->parseLogLine($logged);
        self::assertSame('WARNING', $data['level']);
    }

    public function testContextKeysInOutput(): void
    {
        $logged = $this->captureErrorLog(fn () => Logger::info('ctx test', ['foo' => 'bar', 'num' => 42]));
        $data   = $this->parseLogLine($logged);
        self::assertSame('bar', $data['foo']);
        self::assertSame(42, $data['num']);
    }

    // ── tokenHint ────────────────────────────────────────────────────────────

    public function testTokenHintReturnsFiveCharsAndEllipsis(): void
    {
        $hint = Logger::tokenHint('abcdefghijk');
        self::assertSame('abcde...', $hint);
    }

    public function testTokenHintWithExactlyFiveChars(): void
    {
        $hint = Logger::tokenHint('12345');
        self::assertSame('12345...', $hint);
    }

    public function testTokenHintWithEmptyTokenReturnsNull(): void
    {
        self::assertNull(Logger::tokenHint(''));
    }

    public function testTokenHintWithShortTokenLessThanFive(): void
    {
        $hint = Logger::tokenHint('abc');
        self::assertSame('abc...', $hint);
    }

    // ── newRequest ───────────────────────────────────────────────────────────

    public function testNewRequestReturnsEightCharHex(): void
    {
        $id = Logger::newRequest();
        self::assertMatchesRegularExpression('/^[0-9a-f]{8}$/', $id);
    }

    public function testNewRequestGeneratesUniqueIds(): void
    {
        $ids = [];
        for ($i = 0; $i < 10; $i++) {
            $ids[] = Logger::newRequest();
        }
        self::assertCount(10, array_unique($ids));
    }

    // ── ms ───────────────────────────────────────────────────────────────────

    public function testMsReturnsNonNegativeInteger(): void
    {
        $start = microtime(true) - 0.1; // 100ms ago
        $ms    = Logger::ms($start);
        self::assertGreaterThanOrEqual(90, $ms);
        self::assertLessThan(200, $ms);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function captureErrorLog(callable $fn): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'phplog');
        ini_set('error_log', $tmp);
        $fn();
        $content = file_get_contents($tmp);
        unlink($tmp);
        ini_restore('error_log');
        return trim($content);
    }

    private function parseLogLine(string $line): array
    {
        // error_log writes: "[29-Apr-2026 12:00:00 UTC] [easyverein-mcp] {...}"
        // Find the first "{" to locate the JSON payload
        $pos = strpos($line, '{');
        if ($pos === false) {
            return [];
        }
        return json_decode(substr($line, $pos), true) ?? [];
    }
}
