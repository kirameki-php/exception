<?php declare(strict_types=1);

namespace Tests\Kirameki\Exception;

use Kirameki\Exception\ErrorException;
use const E_ERROR;

class ErrorExceptionTest extends TestCase
{
    public function test_jsonSerialize(): void
    {
        $exception = new ErrorException('test report');

        self::assertSame([
            'class' => 'Kirameki\Exception\ErrorException',
            'message' => 'test report',
            'code' => 0,
            'file' => '/app/tests/src/ErrorExceptionTest.php:12',
            'severity' => E_ERROR,
        ], $exception->jsonSerialize());
    }
}
