<?php

declare(strict_types=1);

namespace App\Application\Services;

interface TextModerationClient
{
    public function filter(string $text): TextModerationResult;
}
