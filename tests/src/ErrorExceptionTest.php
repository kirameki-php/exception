<?php declare(strict_types=1);

namespace Tests\Kirameki\Exception;

use Kirameki\Exception\ErrorException;
use Monolog\Level;
use RuntimeException;
use const E_ERROR;
use const E_USER_DEPRECATED;
use const E_USER_ERROR;

class ErrorExceptionTest extends TestCase
{
    public function test_jsonSerialize(): void
    {
        $exception = new ErrorException('test report');

        self::assertSame([
            'class' => 'Kirameki\Exception\ErrorException',
            'message' => 'test report',
            'code' => 0,
            'file' => '/app/tests/src/ErrorExceptionTest.php:16',
            'severity' => E_ERROR,
        ], $exception->jsonSerialize());
    }
}
