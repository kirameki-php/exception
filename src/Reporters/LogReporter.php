<?php declare(strict_types=1);

namespace Kirameki\Exception\Reporters;

use Psr\Log\LoggerInterface;
use Throwable;

class LogReporter implements Reporter
{
    public function __construct(
        protected LoggerInterface $logger,
    )
    {
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        $message = $exception->getMessage();
        $context = ['exception' => $exception];
        $this->logger->error($message, $context);
    }
}
