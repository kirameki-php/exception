<?php declare(strict_types=1);

namespace Tests\Kirameki\Exception;

use Kirameki\Exception\ErrorException;
use Monolog\Level;
use RuntimeException;

class ExceptionHandlerTest extends TestCase
{
    protected function runScript(string $file): mixed
    {
        $output = shell_exec("php tests/src/Support/runner.php tests/src/Support/$file");
        assert(is_string($output));
        return json_decode($output, true, JSON_THROW_ON_ERROR);
    }

    public function testException(): void
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
                'file' => '/app/tests/src/Support/exception.php:3',
            ],
        ], $output['context']);
    }

    public function testError(): void
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
                'file' => '/app/tests/src/Support/error.php:3',
                'severity' => E_USER_NOTICE,
            ],
        ], $output['context']);
    }

}
