<?php declare(strict_types=1);

namespace Kirameki\Exception;

use Closure;
use ErrorException;
use Kirameki\Exception\Reporters\Reporter;
use Throwable;
use function error_get_last;
use function error_log;
use function register_shutdown_function;
use function set_error_handler;
use function set_exception_handler;

class ExceptionHandler
{
    /**
     * @var array<class-string<Reporter>, Reporter|Closure>
     */
    protected array $reporters;

    /**
     * @var Reporter|Closure(int, string, string, int): Reporter|null
     */
    protected Reporter|Closure|null $deprecationReporter = null;

    public function __construct()
    {
        $this->setErrorHandling();
        $this->setExceptionHandling();
        $this->setFatalHandling();
        $this->reporters = [];
    }

    /**
     * @param class-string<Reporter> $name
     * @param Closure(): Reporter $reporter
     * @return void
     */
    public function setReporter(string $name, Closure $reporter): void
    {
        $this->reporters[$name] = $reporter;
    }

    /**
     * @param class-string<Reporter> $name
     * @return void
     */
    public function removeReporter(string $name): void
    {
        unset($this->reporters[$name]);
    }

    /**
     * @param Closure(int, string, string, int): Reporter|null $reporter
     * @return void
     */
    public function setDeprecationReporter(?Closure $reporter): void
    {
        $this->deprecationReporter = $reporter;
    }

    /**
     * @return array<string, mixed>
     */
    protected function context(): array
    {
        return [];
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    protected function fallback(Throwable $exception): void
    {
        /** @noinspection ForgottenDebugOutputInspection */
        error_log((string) $exception);
    }

    /**
     * @throws ErrorException
     * @return void
     */
    protected function setErrorHandling(): void
    {
        set_error_handler(function(int $severity, string $message, string $file, int $line) {
            return $this->handleError($severity, $message, $file, $line);
        });
    }

    /**
     * @return void
     */
    protected function setExceptionHandling(): void
    {
        set_exception_handler(function (Throwable $throwable) {
            $this->handleException($throwable);
        });
    }

    /**
     * @return void
     */
    protected function setFatalHandling(): void
    {
        register_shutdown_function(function() {
            if($err = error_get_last()) {
                $this->handleError($err['type'], $err['message'], $err['file'], $err['line']);
            }
        });
    }

    /**
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     * @throws ErrorException
     */
    protected function handleError(int $severity, string $message, string $file, int $line): bool
    {
        return match($severity) {
            E_DEPRECATED,
            E_USER_DEPRECATED => $this->handleDeprecations($severity, $message, $file, $line),
            default => throw new ErrorException($message, 0, $severity, $file, $line),
        };
    }

    /**
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     * @throws ErrorException
     */
    protected function handleDeprecations(int $severity, string $message, string $file, int $line): bool
    {
        $error = new ErrorException($message, 0, $severity, $file, $line);

        $reporter = $this->deprecationReporter;

        if ($reporter === null) {
            $this->handleException($error);
            return true;
        }

        if ($reporter instanceof Closure) {
            $this->deprecationReporter = $reporter = $reporter($severity, $message, $file, $line);
        }

        $reporter->report($error);

        return true;
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    protected function handleException(Throwable $exception): void
    {
        try {
            foreach ($this->reporters as $name => $processor) {
                if ($processor instanceof Closure) {
                    $processor = $this->reporters[$name] = $processor();
                }
                $processor->report($exception);
            }
        }
        catch (Throwable $innerException) {
            $this->fallback($innerException);
        }
    }

}
