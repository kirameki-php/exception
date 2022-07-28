<?php declare(strict_types=1);

namespace Tests\Kirameki\Exception\Reporters;

use Kirameki\Exception\Reporters\LogReporter;
use Kirameki\Exception\Reporters\MultiReporter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use RuntimeException;
use Tests\Kirameki\Exception\TestCase;

class MultiReporterTest extends TestCase
{
    public function test_report(): void
    {
        $tmpFile1 = tmpfile();
        $tmpFile2 = tmpfile();

        assert(is_resource($tmpFile1));
        assert(is_resource($tmpFile2));

        $reporter = new MultiReporter([
            new LogReporter(new Logger('a', [new StreamHandler($tmpFile1)])),
            new LogReporter(new Logger('b', [new StreamHandler($tmpFile2)])),
        ]);
        $reporter->report(new RuntimeException('<test report>'));

        $contents1 = file_get_contents(stream_get_meta_data($tmpFile1)['uri']);
        $contents2 = file_get_contents(stream_get_meta_data($tmpFile2)['uri']);

        self::assertStringContainsString('<test report>', $contents1 ?: '');
        self::assertStringContainsString('<test report>', $contents2 ?: '');
    }
}
