<?php declare(strict_types=1);

namespace Tests\Kirameki\Exception;

use Kirameki\Exception\ErrorException;
use Monolog\Level;
use RuntimeException;
use const E_ERROR;
use const E_USER_DEPRECATED;
use const E_USER_ERROR;

class ExceptionHandlerTest extends TestCase
{
    protected function runScript(string $file): mixed
    {
        $output = shell_exec("php tests/src/Support/{$file}.php 2>/dev/null");
        assert(is_string($output));
        return json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_exception_handling(): void
    {
        $output = $this->runScript('exception');

        self::assertSame('test exception', $output['message']);
        self::assertSame(Level::Error->value, $output['level']);
        self::assertSame('testing channel', $output['channel']);
        self::assertSame([
            'exception' => [
                'class' => RuntimeException::class,
                'message' => $output['message'],
                'code' => 0,
                'file' => '/app/tests/src/Support/exception.php:5',
            ],
        ], $output['context']);
    }

    public function test_error_handling(): void
    {
        $output = $this->runScript('error');

        self::assertSame('test error', $output['message']);
        self::assertSame(Level::Error->value, $output['level']);
        self::assertSame('testing channel', $output['channel']);
        self::assertSame([
            'exception' => [
                'class' => ErrorException::class,
                'message' => $output['message'],
                'code' => 0,
                'file' => '/app/tests/src/Support/error.php:5',
                'severity' => E_USER_ERROR,
            ],
        ], $output['context']);

        $file = '/tmp/error.txt';
        self::assertFileDoesNotExist($file);
    }

    public function test_fatal_handling(): void
    {
        $output = $this->runScript('fatal');

        self::assertSame('Allowed memory size of 10485760 bytes exhausted (tried to allocate 15000032 bytes)', $output['message']);
        self::assertSame(Level::Error->value, $output['level']);
        self::assertSame('testing channel', $output['channel']);
        self::assertSame([
            'exception' => [
                'class' => ErrorException::class,
                'message' => $output['message'],
                'code' => 0,
                'file' => '/app/tests/src/Support/fatal.php:7',
                'severity' => E_ERROR,
            ],
        ], $output['context']);

        $file = '/tmp/fatal.txt';
        self::assertFileDoesNotExist($file);
    }

    public function test_deprecation(): void
    {
        $output = $this->runScript('deprecation');

        self::assertSame('test deprecation', $output['message']);
        self::assertSame(Level::Error->value, $output['level']);
        self::assertSame('testing channel', $output['channel']);
        self::assertSame([
            'exception' => [
                'class' => ErrorException::class,
                'message' => $output['message'],
                'code' => 0,
                'file' => '/app/tests/src/Support/deprecation.php:5',
                'severity' => E_USER_DEPRECATED,
            ],
        ], $output['context']);

        $file = '/tmp/deprecation.txt';
        self::assertFileDoesNotExist($file);
    }

    public function test_deprecation_custom(): void
    {
        $output = $this->runScript('deprecation_custom');

        self::assertSame('test deprecation', $output['message']);
        self::assertSame(Level::Error->value, $output['level']);
        self::assertSame('deprecated channel', $output['channel']);
        self::assertSame([
            'exception' => [
                'class' => ErrorException::class,
                'message' => $output['message'],
                'code' => 0,
                'file' => '/app/tests/src/Support/deprecation_custom.php:17',
                'severity' => E_USER_DEPRECATED,
            ],
        ], $output['context']);

        $file = '/tmp/deprecation_custom.txt';
        self::assertFileExists($file);
        unlink($file);
    }

    public function test_fallback(): void
    {
        $output = shell_exec("php tests/src/Support/fallback.php 2>&1");
        assert(is_string($output));
        $expected = 'Fatal error:  Uncaught ' . ErrorException::class . ': Uncaught RuntimeException: fallback';
        self::assertStringContainsString($expected, $output);
    }
}
