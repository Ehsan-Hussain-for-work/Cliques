<?php

declare(strict_types=1);

namespace Tests\Application\Services;

use App\Application\Services\TextModerationException;
use App\Application\Services\TextModerationResult;
use App\Infrastructure\External\ApiNinjasProfanityClient;
use Tests\TestCase;

class TextModerationTest extends TestCase
{
    public function testNormalizedModerationResult(): void
    {
        $result = new TextModerationResult('damn it!', '**** it!', true);

        $this->assertSame('damn it!', $result->original);
        $this->assertSame('**** it!', $result->censored);
        $this->assertTrue($result->hasProfanity);
    }

    public function testMissingApiKeyFailsClosed(): void
    {
        $client = new ApiNinjasProfanityClient('YOUR_API_NINJA_KEY', 2);

        $this->expectException(TextModerationException::class);
        $this->expectExceptionCode(503);

        $client->filter('test message');
    }
}
