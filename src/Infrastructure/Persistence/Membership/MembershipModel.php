<?php

namespace App\Infrastructure\Persistence\Membership;

use App\Domain\Membership\MembershipRepository;

use App\Domain\Channel\ChannelNotFoundException;
use App\Domain\Group\GroupNotFoundException;
use PDO;
use App\Application\Support\MembershipCollectionQuery;

class MembershipModel implements MembershipRepository
{
    private PDO $db;

    public function __construct(PDO $database)
    {
        $this->db = $database;
    }

    public function joinGroup(int $userId, int $groupId): void
    {

        // Check whether the group exists and whether it is adult-gated.
        $query = 'SELECT adult_gate
        FROM groups
        WHERE id = :group_id';

        $sql = $this->db->prepare($query);

        $sql->execute([
            'group_id' => $groupId
        ]);

        $group = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$group) {
            throw new GroupNotFoundException();
        }

        // Check age if the group is adult-gated.
        if ((int) $group['adult_gate'] === 1) {

            $query = 'SELECT user_birthday <=
            DATE_SUB(CURDATE(), INTERVAL 18 YEAR)
            FROM users
            WHERE id = :user_id';

            $sql = $this->db->prepare($query);

            $sql->execute([
                'user_id' => $userId
            ]);

            if (!(bool) $sql->fetchColumn()) {
                throw new \DomainException(
                    'Only 18-year-olds or older can join the group.'
                );
            }
        }

        // Create membership.
        $query = 'INSERT INTO memberships
        (user_id, group_id, channel_id, mod_status)
        VALUES
        (:user_id, :group_id, NULL, 0)';

        $sql = $this->db->prepare($query);

        $sql->execute([
            'user_id' => $userId,
            'group_id' => $groupId
        ]);
    }

    public function joinChannel(int $userId, int $groupId): void
    {

        // Check whether the group exists and whether it is adult-gated.
        $query = 'SELECT id, group_id, adult_gate
        FROM channels
        WHERE id = :channel_id';

        $sql = $this->db->prepare($query);

        $sql->execute([
            'group_id' => $groupId
        ]);

        $channel = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$channel) {
            throw new ChannelNotFoundException();
        }

        // Check age if the group is adult-gated.
        if ((int) $channel['adult_gate'] === 1) {

            $query = 'SELECT user_birthday <=
            DATE_SUB(CURDATE(), INTERVAL 18 YEAR)
            FROM users
            WHERE id = :user_id';

            $sql = $this->db->prepare($query);

            $sql->execute([
                'user_id' => $userId
            ]);

            if (!(bool) $sql->fetchColumn()) {
                throw new \DomainException(
                    'Only 18-year-olds or older can join the channel.'
                );
            }
        }

        // Create membership.
        $query = 'INSERT INTO memberships
        (user_id, group_id, channel_id, mod_status)
        VALUES
        (:user_id, :group_id, :channel_id, 0)';

        $sql = $this->db->prepare($query);

        $sql->execute([
            'user_id' => $userId,
            'group_id' => $groupId
        ]);
    }
}
