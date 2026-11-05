<?php

declare(strict_types=1);

namespace App\Domain\Group;

use App\Domain\Group\GroupNotFoundException;

interface GroupRepository
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
     * @throws GroupNotFoundException
     */
    public function findIdGroup(int $id): array;

    /**
     * @return String[]
     */
    public function addGroup(array $providedData): array;

    /**
     * @return bool
     */
    public function removeGroup(int $id): bool;

    public function page(array $query): array;
}
