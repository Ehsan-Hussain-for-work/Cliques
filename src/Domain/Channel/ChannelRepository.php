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
    public function addChannel(
        int $creatorId,
        array $providedData
    ): array;

    /**
     * @return bool
     */
    public function removeChannel(int $id): bool;

    public function isMod(
        int $userId,
        int $channelId
    ): bool;

    public function updateChannel(
        int $channelId,
        array $values
    ): array;

    public function joinChannel(
        int $userId,
        int $channelId
    ): bool;

    /**
     * @return array
     */
    public function page(array $query): array;
}
