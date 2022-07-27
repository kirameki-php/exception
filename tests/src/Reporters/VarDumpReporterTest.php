<?php declare(strict_types=1);

namespace Tests\Kirameki\Exception\Reporters;

use Kirameki\Exception\Reporters\LogReporter;
use Kirameki\Exception\Reporters\VarDumpReporter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use RuntimeException;
use Symfony\Component\VarDumper\VarDumper;
use Tests\Kirameki\Exception\TestCase;

class VarDumpReporterTest extends TestCase
{
    public function test_report(): void
    {
        $handled = null;

        VarDumper::setHandler(function (RuntimeException $var) use (&$handled) {
            $handled = $var;
        });

        $exception = new RuntimeException('<test report>');

        $reporter = new VarDumpReporter();
        $reporter->report($exception);

        self::assertSame($exception, $handled);
    }
}
