<?php declare(strict_types=1);

use Kirameki\Exception\ExceptionHandler;
use Kirameki\Exception\Reporters\LogReporter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/** @var ExceptionHandler $handler */
$handler = require 'setup.php';

$logger = new Logger('deprecated channel', [
    (new StreamHandler('php://stdout'))->setFormatter(new JsonFormatter()),
]);

$handler->setDeprecationReporter(new LogReporter($logger));

trigger_error('test deprecation', E_USER_DEPRECATED);

file_put_contents('/tmp/deprecation_custom.txt', '1');
