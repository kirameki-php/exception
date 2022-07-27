<?php declare(strict_types=1);

namespace Kirameki\Exception;

use Closure;
use Kirameki\Exception\Reporters\Reporter;
use Throwable;
use function error_get_last;
use function register_shutdown_function;
use function set_error_handler;
use function set_exception_handler;
use const E_DEPRECATED;
use const E_USER_DEPRECATED;

class ExceptionHandler
{
    /**
     * @param Reporter|Closure(): Reporter|null $reporter
     * @param Reporter|Closure(): Reporter|null $deprecationReporter
     */
    public function __construct(
        protected Reporter|Closure|null $reporter = null,
        protected Reporter|Closure|null $deprecationReporter = null,
    )
    {
        error_reporting(-1);
        $this->setExceptionHandling();
        $this->setErrorHandling();
        $this->setFatalHandling();
    }

    /**
     * @param Reporter|Closure(): Reporter $reporter
     * @return void
     */
    public function setDeprecationReporter(Reporter|Closure $reporter): void
    {
        $this->deprecationReporter = $reporter;
    }

    /**
     * @return void
     */
    public function removeDeprecationReporter(): void
    {
        $this->deprecationReporter = null;
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
    protected function setFatalHandling(): void
    {
        register_shutdown_function(function() {
            if($err = error_get_last()) {
                $exception = new ErrorException($err['message'], 0, $err['type'], $err['file'], $err['line']);
                $this->handleException($exception);
            }
        });
    }

    /**
     * @return Reporter|null
     */
    protected function resolveReporter(): ?Reporter
    {
        if ($this->reporter instanceof Closure) {
            $this->reporter = ($this->reporter)();
        }
        return $this->reporter;
    }

    /**
     * @return Reporter|null
     */
    protected function resolveDeprecationReporter(): ?Reporter
    {
        if ($this->deprecationReporter instanceof Closure) {
            $this->deprecationReporter = ($this->deprecationReporter)();
        }
        return $this->deprecationReporter;
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    protected function handleException(Throwable $exception): void
    {
        $reporter = $this->resolveReporter();

        if ($reporter !== null) {
            try {
                $reporter->report($exception);
            }
            catch (Throwable $innerException) {
                $this->fallback($innerException);
            }
        } else {
            $this->fallback($exception);
        }
    }

    /**
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     */
    protected function handleError(int $severity, string $message, string $file, int $line): bool
    {
        $error = new ErrorException($message, 0, $severity, $file, $line);

        if (in_array($severity, [E_DEPRECATED, E_USER_DEPRECATED])) {
            if ($reporter = $this->resolveDeprecationReporter()) {
                $reporter->report($error);
                return true;
            }
        }

        throw $error;
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    protected function fallback(Throwable $exception): void
    {
        throw $exception;
    }
}
