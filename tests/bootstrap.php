<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// Ensure session functions are available without actually starting one
if (!isset($_SESSION)) {
    $_SESSION = [];
}
