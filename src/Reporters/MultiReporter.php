<?php declare(strict_types=1);

namespace Kirameki\Exception\Reporters;

use Closure;
use Throwable;

class MultiReporter implements Reporter
{
    /**
     * @param array<Reporter|Closure(): Reporter> $reporters
     */
    public function __construct(protected array $reporters)
    {
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        foreach ($this->reporters as $index => $reporter) {
            if ($reporter instanceof Closure) {
                $reporter = $this->reporters[$index] = $reporter();
            }
            $reporter->report($exception);
        }
    }
}
