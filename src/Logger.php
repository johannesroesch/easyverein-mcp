<?php

declare(strict_types=1);

namespace EasyVerein\Mcp;

/**
 * Structured JSON logger (NDJSON → stderr via error_log).
 *
 * Log levels (ascending severity): DEBUG < INFO < WARNING < ERROR
 * Configure via LOG_LEVEL env variable (default: INFO).
 *
 * What is intentionally never logged:
 *   - Full API tokens        → only a 5-char hint is used
 *   - Session IDs            → security-sensitive
 *   - Request / response bodies → may contain PII (member data, addresses)
 *   - Tool arguments         → may contain user-supplied PII
 *   - API response payloads  → may contain member data, financials, etc.
 */
class Logger
{
    public const DEBUG   = 'DEBUG';
    public const INFO    = 'INFO';
    public const WARNING = 'WARNING';
    public const ERROR   = 'ERROR';

    private const SEVERITY = [
        self::DEBUG   => 0,
        self::INFO    => 1,
        self::WARNING => 2,
        self::ERROR   => 3,
    ];

    private static int    $minLevel  = 1;   // INFO
    private static string $requestId  = '';
    private static string $transport  = '';
    private static string $sessionTag = '';

    public static function init(): void
    {
        $name          = strtoupper($_ENV['LOG_LEVEL'] ?? 'INFO');
        self::$minLevel = self::SEVERITY[$name] ?? 1;
    }

    public static function setMinLevel(string $level): void
    {
        self::$minLevel = self::SEVERITY[strtoupper($level)] ?? self::$minLevel;
    }

    /** Derives a short, non-reversible tag from a session ID for log correlation. */
    public static function setSession(string $sessionId): void
    {
        self::$sessionTag = $sessionId !== '' ? substr(md5($sessionId), 0, 6) : '';
    }

    /** Call once per request to correlate log lines from the same request. */
    public static function newRequest(string $transport = ''): string
    {
        self::$requestId = substr(bin2hex(random_bytes(4)), 0, 8);
        self::$transport = $transport;
        return self::$requestId;
    }

    public static function debug(string $message, array $context = []): void
    {
        self::write(self::DEBUG, $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write(self::INFO, $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write(self::WARNING, $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write(self::ERROR, $message, $context);
    }

    /**
     * Returns a safe loggable hint for a token.
     * Never pass the full token to log context.
     */
    public static function tokenHint(string $token): ?string
    {
        return $token !== '' ? substr($token, 0, 5) . '...' : null;
    }

    /** Measures wall-clock ms since a microtime(true) start. */
    public static function ms(float $start): int
    {
        return (int) round((microtime(true) - $start) * 1000);
    }

    // -------------------------------------------------------------------------

    private static function write(string $level, string $message, array $context): void
    {
        if ((self::SEVERITY[$level] ?? 0) < self::$minLevel) {
            return;
        }

        $entry = ['ts' => date('c'), 'level' => $level, 'msg' => $message];

        if (self::$requestId !== '') {
            $entry['req'] = self::$requestId;
        }

        if (self::$transport !== '') {
            $entry['transport'] = self::$transport;
        }

        if (self::$sessionTag !== '') {
            $entry['sess'] = self::$sessionTag;
        }

        foreach ($context as $key => $value) {
            $entry[$key] = $value;
        }

        error_log('[easyverein-mcp] ' . json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
