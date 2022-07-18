<?php declare(strict_types=1);

namespace Kirameki\Exception\Reporters;

use Symfony\Component\VarDumper\VarDumper;
use Throwable;

class VarDumpReporter implements Reporter
{
    /**
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        VarDumper::dump($exception);
    }
}
