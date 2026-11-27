<?php

namespace App\Domain\Membership;

interface MembershipRepository
{
    public function joinGroup(int $userId, int $groupId): void;

    public function joinChannel(int $userId, int $channelId): void;
}
