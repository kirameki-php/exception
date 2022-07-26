<?php declare(strict_types=1);

namespace Kirameki\Exception;

use Closure;
use Kirameki\Exception\Reporters\Reporter;
use Throwable;
use function error_get_last;
use function error_log;
use function register_shutdown_function;
use function set_error_handler;
use function set_exception_handler;
use const E_DEPRECATED;
use const E_USER_DEPRECATED;

class ExceptionHandler
{
    /**
     * @var array<string, Reporter|Closure(): Reporter>
     */
    protected array $reporters;

    /**
     * @var Reporter|Closure(): Reporter|null
     */
    protected Reporter|Closure|null $deprecationReporter = null;

    /**
     * @param array<string, Reporter|Closure(): Reporter> $reporters
     */
    public function __construct(array $reporters = [])
    {
        error_reporting(-1);
        $this->setExceptionHandling();
        $this->setErrorHandling();
        $this->setFatalHandling();
        $this->reporters = $reporters;
    }

    /**
     * @param string $name
     * @param Closure(): Reporter $reporter
     * @return void
     */
    public function setReporter(string $name, Closure $reporter): void
    {
        $this->reporters[$name] = $reporter;
    }

    /**
     * @param string $name
     * @return void
     */
    public function removeReporter(string $name): void
    {
        unset($this->reporters[$name]);
    }

    /**
     * @param Reporter|Closure(): Reporter|null $reporter
     * @return void
     */
    public function setDeprecationReporter(Reporter|Closure|null $reporter): void
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
     * @param Throwable $exception
     * @return void
     */
    protected function handleException(Throwable $exception): void
    {
        if (count($this->reporters) === 0) {
            $this->fallback($exception);
            return;
        }

        try {
            foreach ($this->reporters as $name => $reporter) {
                if ($reporter instanceof Closure) {
                    $reporter = $this->reporters[$name] = $reporter();
                }
                $reporter->report($exception);
            }
        }
        catch (Throwable $innerException) {
            $this->fallback($innerException);
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

        return match($severity) {
            E_DEPRECATED,
            E_USER_DEPRECATED => $this->handleDeprecations($error),
            default => throw $error,
        };
    }

    /**
     * @param ErrorException $error
     * @return bool
     */
    protected function handleDeprecations(ErrorException $error): bool
    {
        $reporter = $this->deprecationReporter;

        // If no reporter for deprecation is set, throw and treat it as normal exception.
        if ($reporter === null) {
            throw $error;
        }

        if ($reporter instanceof Closure) {
            $this->deprecationReporter = $reporter = $reporter();
        }

        $reporter->report($error);

        return true;
    }
}
