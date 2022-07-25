<?php declare(strict_types=1);

use Kirameki\Exception\ExceptionHandler;
use Kirameki\Exception\Reporters\LogReporter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require 'vendor/autoload.php';

$logHandler = new StreamHandler('php://stdout');
$logHandler->setFormatter(new JsonFormatter());
$logger = new Logger('testing channel', [$logHandler]);

$handler = new ExceptionHandler();
$handler->setReporter('default', fn() => new LogReporter($logger));

$path = $argv[1];

require $path . '.php';
