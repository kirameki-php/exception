<?php declare(strict_types=1);

namespace Tests\Kirameki\Exception\Reporters;

use Kirameki\Exception\Reporters\LogReporter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use RuntimeException;
use Tests\Kirameki\Exception\TestCase;

class LogReporterTest extends TestCase
{
    public function test_report(): void
    {
        $tmpFile = tmpfile();
        assert(is_resource($tmpFile));

        $logger = new Logger('test', [new StreamHandler($tmpFile)]);
        $reporter = new LogReporter($logger);
        $reporter->report(new RuntimeException('<test report>'));

        $contents = file_get_contents(stream_get_meta_data($tmpFile)['uri']);

        self::assertStringContainsString('<test report>', $contents ?: '');
    }
}
