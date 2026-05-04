<?php

declare(strict_types=1);

use EasyVerein\Mcp\ApiClient;
use EasyVerein\Mcp\ExitException;
use EasyVerein\Mcp\McpServer;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

\EasyVerein\Mcp\Logger::init();

$baseUrl = $_ENV['EASYVEREIN_BASE_URL'] ?? 'https://easyverein.com/api/v3.0';

$client = new ApiClient($baseUrl);
$mcp    = new McpServer($client);

try {
    $mcp->handleStdio();
} catch (ExitException) {
    exit(0);
}
