<?php

declare(strict_types=1);

namespace App\Application\Services;

final class TextModerationResult
{
    public string $original;
    public string $censored;
    public bool $hasProfanity;

    public function __construct(string $original, string $censored, bool $hasProfanity)
    {
        $this->original = $original;
        $this->censored = $censored;
        $this->hasProfanity = $hasProfanity;
    }
}
