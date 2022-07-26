<?php declare(strict_types=1);

namespace Kirameki\Exception\Reporters;

use Closure;
use Throwable;

class CallbackReporter implements Reporter
{
    /**
     * @param Closure(Throwable): mixed $callback
     */
    public function __construct(
        protected Closure $callback,
    )
    {
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        ($this->callback)($exception);
    }
}
