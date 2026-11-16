<?php

declare(strict_types=1);

namespace App\Domain\Message;

use App\Domain\Message\MessageNotFoundException;

interface MessageRepository
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
     * @throws MessageNotFoundException
     */
    public function findIdMessage(int $id): array;

    /**
     * @return String[]
     */
    public function addMessage(array $providedData): array;

    /**
     * @return bool
     */
    public function removeMessage(int $id): bool;

    public function page(array $query): array;
}
