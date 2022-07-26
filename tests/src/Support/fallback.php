<?php declare(strict_types=1);

use Kirameki\Exception\ExceptionHandler;

require 'vendor/autoload.php';

ini_set('error_log', 'php://stderr');

$handler = new ExceptionHandler();

throw new RuntimeException('fallback');
