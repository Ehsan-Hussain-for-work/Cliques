<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\User\UserNotFoundException;

interface UserRepository
{
    /**
     * @return String[]
     */
    public function findAll(): array;

    /**
     * @param int $id
     * @return String[]
     * @throws UserNotFoundException
     */
    public function findUserOfId(int $id): array;
}
