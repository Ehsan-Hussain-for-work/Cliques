<?php

namespace App\Application\Services;

use Negotiation\Negotiator;
use Negotiation\Accept;

interface SuccessFormatSelector
{
    public function select(?string $accept, array $supported): ?string;
}

class FormatSelector implements SuccessFormatSelector
{
    public function select(?string $accept, array $supported): ?string
    {
        if ($accept === null || trim($accept) === '') {
            return 'application/json';
        }
        $negotiator = new Negotiator();
        try {
            $mediaType = $negotiator->getBest($accept, $supported);
        } catch (\Exception $e) {
            return null;
        }
        if ($mediaType instanceof Accept) {
            return $mediaType->getType();
        } else return $mediaType;
    }
}
