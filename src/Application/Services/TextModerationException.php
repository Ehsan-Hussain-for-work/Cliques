<?php

declare(strict_types=1);

namespace App\Application\Services;

final class TextModerationException extends \RuntimeException
{
    public function __construct(string $message, int $statusCode, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->getCode();
    }
}
