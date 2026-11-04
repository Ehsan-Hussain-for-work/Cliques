<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\User\UserNotFoundException;

interface UserRepository
{
    /**
     * @return int
     */
    public function countAll(): int;

    /**
     * @return String[]
     */
    public function findAll(int $limit, int $offset): array;

    /**
     * @param int $id
     * @return String[]
     * @throws UserNotFoundException
     */
    public function findIdUser(int $id): array;

    /**
     * @return String[]
     */
    public function addUser(array $providedData): array;

    /**
     * @return bool
     */
    public function removeUser(int $id): bool;
}
