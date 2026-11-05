<?php

declare(strict_types=1);

namespace App\Domain\Channel;

use App\Domain\Channel\ChannelNotFoundException;

interface ChannelRepository
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
     * @throws ChannelNotFoundException
     */
    public function findIdChannel(int $id): array;

    /**
     * @return String[]
     */
    public function addChannel(array $providedData): array;

    /**
     * @return bool
     */
    public function removeChannel(int $id): bool;
}
