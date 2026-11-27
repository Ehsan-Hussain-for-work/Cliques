<?php

declare(strict_types=1);

namespace App\Infrastructure\External;

use App\Application\Services\TextModerationClient;
use App\Application\Services\TextModerationException;
use App\Application\Services\TextModerationResult;

final class ApiNinjasProfanityClient implements TextModerationClient
{
    private const ENDPOINT = 'https://api.api-ninjas.com/v1/profanityfilter';

    private string $apiKey;
    private int $timeoutSeconds;

    public function __construct(?string $apiKey = null, int $timeoutSeconds = 2)
    {
        $this->apiKey = $apiKey ?? (string) getenv('API_NINJA_API_KEY');
        $this->timeoutSeconds = $timeoutSeconds;
    }

    public function filter(string $text): TextModerationResult
    {
        if ($this->apiKey === '' || $this->apiKey === 'YOUR_API_NINJA_KEY') {
            throw new TextModerationException('The profanity provider is not configured.', 503);
        }

        $url = self::ENDPOINT . '?text=' . urlencode($text);
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Accept: application/json\r\nX-Api-Key: " . $this->apiKey . "\r\n",
                'timeout' => $this->timeoutSeconds,
                'ignore_errors' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        if ($body === false) {
            throw new TextModerationException('The profanity provider could not be reached.', 503);
        }

        $statusCode = $this->responseStatusCode($http_response_header ?? []);
        if ($statusCode === 429) {
            throw new TextModerationException('The profanity provider quota was exceeded.', 429);
        }
        if ($statusCode >= 500) {
            throw new TextModerationException('The profanity provider is unavailable.', 503);
        }
        if ($statusCode >= 400) {
            throw new TextModerationException('The profanity provider rejected the request.', 502);
        }

        $payload = json_decode($body, true);
        if (
            !is_array($payload)
            || !array_key_exists('original', $payload)
            || !array_key_exists('censored', $payload)
            || !array_key_exists('has_profanity', $payload)
            || !is_string($payload['original'])
            || !is_string($payload['censored'])
            || !is_bool($payload['has_profanity'])
        ) {
            throw new TextModerationException('The profanity provider returned an invalid response.', 502);
        }

        return new TextModerationResult(
            $payload['original'],
            $payload['censored'],
            $payload['has_profanity']
        );
    }

    private function responseStatusCode(array $headers): int
    {
        foreach ($headers as $header) {
            if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $matches) === 1) {
                return (int) $matches[1];
            }
        }

        return 0;
    }
}
