<?php

declare(strict_types=1);

namespace App\Domain\Auth;

interface AuthRepository
{
    //Find user by email.
    public function findEmailUser(string $email): ?array;

    //Store new auth token.
    public function createToken(int $userId, string $tokenHash, string $expirationDate): void;

    //Find user with token.
    public function findTokenUser(string $tokenHash): ?array;

    //invalidates auth token.
    public function revokeToken(string $tokenHash): bool;
}
