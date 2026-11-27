<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Group;

use App\Domain\Group\GroupNotFoundException;
use App\Domain\Group\GroupRepository;
use Override;
use PDO;
use App\Application\Support\GroupCollectionQuery;

class GroupModel implements GroupRepository
{
    private PDO $db;

    public function __construct(PDO $database)
    {
        $this->db = $database;
    }

    public function countAll(): int
    {
        $query = 'SELECT COUNT(*) FROM groups';

        $sql = $this->db->query($query);

        $groupNum = (int) $sql->fetchColumn();

        return $groupNum;
    }

    public function findAll(int $limit, int $offset): array
    {
        $query = 'SELECT * 
        FROM groups 
        ORDER BY id ASC 
        LIMIT :limit OFFSET :offset';

        $sql = $this->db->prepare($query);

        $sql->bindValue(':limit', $limit, PDO::PARAM_INT);
        $sql->bindValue(':offset', $offset, PDO::PARAM_INT);

        $sql->execute();
        $groups = $sql->fetchAll(PDO::FETCH_ASSOC);

        return $groups;
    }

    public function findIdGroup(int $id): array
    {
        $query = 'SELECT id, `name`, creation_date, adult_gate 
        FROM groups 
        WHERE id = :id';

        $sql = $this->db->prepare($query);
        $sql->execute(['id' => $id]);
        $group = $sql->fetch();

        if (!$group) {
            throw new GroupNotFoundException();
        } else {
            return $group;
        }
    }

    public function addGroup(int $creatorId, array $values): array
    {

        try {

            $this->db->beginTransaction();

            $query = 'INSERT INTO groups
            (`name`, creation_date, adult_gate)
            VALUES
            (:name, UTC_TIMESTAMP(), :adult_gate)';

            $sql = $this->db->prepare($query);

            $sql->execute([
                'name' => $values['name'],
                'adult_gate' => $values['adult_gate']
            ]);

            $groupId = (int) $this->db->lastInsertId();

            // make creator = moderator
            $query = 'INSERT INTO memberships
            (user_id, group_id, channel_id, mod_status)
            VALUES
            (:user_id, :group_id, NULL, 1)';

            $sql = $this->db->prepare($query);

            $sql->execute([
                'user_id' => $creatorId,
                'group_id' => $groupId
            ]);

            $this->db->commit();

            return $this->findIdGroup($groupId);
        } catch (\Throwable $e) {

            //undoes in case of Groups created, but owners moderation fails
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function removeGroup(int $id): bool
    {
        $query = 'DELETE FROM groups WHERE id = :id';

        $sql = $this->db->prepare($query);

        $sql->execute([
            'id' => $id
        ]);

        if ($sql->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function isMod(
        int $userId,
        int $groupId
    ): bool {

        $query = 'SELECT 1
        FROM memberships
        WHERE user_id = :user_id
        AND group_id = :group_id
        AND channel_id IS NULL
        AND mod_status = 1
        AND leave_date IS NULL
        LIMIT 1';

        $sql = $this->db->prepare($query);

        $sql->execute([
            'user_id' => $userId,
            'group_id' => $groupId
        ]);

        return $sql->fetchColumn() !== false;
    }

    public function updateGroup(int $groupId, array $values): array
    {
        $group = $this->findIdGroup($groupId);

        $query = 'UPDATE groups
        SET name = :name, adult_gate = :adult_gate
        WHERE id = :id';

        $sql = $this->db->prepare($query);

        $sql->execute([
            'id' => $groupId,
            'name' => $values['name'] ?? $group['name'],
            'adult_gate' => $values['adult_gate'] ?? $group['adult_gate']
        ]);

        return $this->findIdGroup($groupId);
    }

    public function joinGroup(
        int $userId,
        int $groupId
    ): bool {

        // Get the group's adult_gate setting and check the user's age.
        $query = 'SELECT
                g.adult_gate,
                (
                    SELECT user_birthday <=
                        DATE_SUB(CURDATE(), INTERVAL 18 YEAR)
                    FROM users
                    WHERE id = :user_id
                ) AS is_adult
              FROM groups g
              WHERE g.id = :group_id';

        $sql = $this->db->prepare($query);

        $sql->execute([
            'user_id' => $userId,
            'group_id' => $groupId
        ]);

        $group = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$group) {
            throw new GroupNotFoundException();
        }

        // Reject users who do not meet the age requirement.
        if (
            (int) $group['adult_gate'] === 1 &&
            !(bool) $group['is_adult']
        ) {
            return false;
        }

        // Create or reactivate the membership.
        $query = 'INSERT INTO memberships
                (user_id, group_id, channel_id, mod_status)
              VALUES
                (:user_id, :group_id, NULL, 0)
              ON DUPLICATE KEY UPDATE
                join_date = UTC_TIMESTAMP(),
                leave_date = NULL';

        $sql = $this->db->prepare($query);

        $sql->execute([
            'user_id' => $userId,
            'group_id' => $groupId
        ]);

        return true;
    }

    private function execute(string $sql, array $params): \PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        foreach ($params as $name => $value) {
            $stmt->bindValue(
                ':' . $name,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
        $stmt->execute();
        return $stmt;
    }

    public function page(array $query): array
    {
        $plan = GroupCollectionQuery::plan($query);
        $total = (int) $this->execute($plan['countSql'], $plan['filters'])->fetchColumn();
        $rows = $this->execute($plan['sql'], $plan['params'])->fetchAll(PDO::FETCH_ASSOC);

        $items = array_map(static fn(array $r): array => [
            'id' => (int) $r['id'],
            'name' => $r['name'],
            'creation_date' => $r['creation_date'],
            'adult_gate' => (bool) $r['adult_gate']
        ], $rows);

        return GroupCollectionQuery::envelope($items, $query, $total);
    }
}
