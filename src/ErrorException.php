<?php declare(strict_types=1);

namespace Kirameki\Exception;

use ErrorException as Base;
use JsonSerializable;

class ErrorException extends Base implements JsonSerializable
{
    /**
     * @return array{ class: string, message: string, code: int, file: string, severity: int }
     */
    public function jsonSerialize(): array
    {
        return [
            'class' => $this::class,
            'message' => $this->message,
            'code' => $this->code,
            'file' => "{$this->file}:{$this->line}",
            'severity' => $this->severity,
        ];
    }
}
