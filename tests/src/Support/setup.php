<?php declare(strict_types=1);

use Kirameki\Exception\ExceptionHandler;
use Kirameki\Exception\Reporters\LogReporter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require 'vendor/autoload.php';

$logger = new Logger('testing channel', [
    (new StreamHandler('php://stdout'))->setFormatter(new JsonFormatter()),
]);

return new ExceptionHandler([
    'default' => new LogReporter($logger),
]);
