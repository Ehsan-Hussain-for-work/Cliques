<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Channel;

use App\Domain\Channel\ChannelNotFoundException;
use App\Domain\Channel\ChannelRepository;
use Override;
use PDO;
use App\Application\Support\ChannelCollectionQuery;

class ChannelModel implements ChannelRepository
{
    private PDO $db;

    public function __construct(PDO $database)
    {
        $this->db = $database;
    }

    public function countAll(): int
    {
        $query = 'SELECT COUNT(*) FROM channels';

        $sql = $this->db->query($query);

        $channelNum = (int) $sql->fetchColumn();

        return $channelNum;
    }

    public function findAll(int $limit, int $offset): array
    {
        $query = 'SELECT * 
        FROM channels 
        ORDER BY id ASC 
        LIMIT :limit OFFSET :offset';

        $sql = $this->db->prepare($query);

        $sql->bindValue(':limit', $limit, PDO::PARAM_INT);
        $sql->bindValue(':offset', $offset, PDO::PARAM_INT);

        $sql->execute();
        $channels = $sql->fetchAll(PDO::FETCH_ASSOC);

        return $channels;
    }

    public function findIdChannel(int $id): array
    {
        $query = 'SELECT id, `name`, group_id, adult_gate 
        FROM channels 
        WHERE id = :id';

        $sql = $this->db->prepare($query);
        $sql->execute(['id' => $id]);
        $channel = $sql->fetch();

        if (!$channel) {
            throw new ChannelNotFoundException();
        } else {
            return $channel;
        }
    }

    public function addChannel(int $creatorId, array $values): array
    {

        try {

            $this->db->beginTransaction();

            // Create the channel.
            $query = 'INSERT INTO channels
            (`name`, group_id, adult_gate)
            VALUES
            (:name, :group_id, :adult_gate)';

            $sql = $this->db->prepare($query);

            $sql->execute([
                'name' => $values['name'],
                'group_id' => $values['group_id'],
                'adult_gate' => $values['adult_gate']
            ]);

            $channelId = (int) $this->db->lastInsertId();

            // Automatically make creator a channel moderator.
            $query = 'INSERT INTO memberships
            (user_id, group_id, channel_id, mod_status)
            VALUES
            (:user_id, :group_id, :channel_id, 1)';

            $sql = $this->db->prepare($query);

            $sql->execute([
                'user_id' => $creatorId,
                'group_id' => $values['group_id'],
                'channel_id' => $channelId
            ]);

            $this->db->commit();

            return $this->findIdChannel($channelId);
        } catch (\Throwable $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function removeChannel(int $id): bool
    {
        $query = 'DELETE FROM channels WHERE id = :id';

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

    public function isMod(int $userId, int $channelId): bool
    {
        $query = 'SELECT 1
        FROM memberships
        WHERE user_id = :user_id
        AND channel_id = :channel_id
        AND mod_status = 1
        AND leave_date IS NULL
        LIMIT 1';

        $sql = $this->db->prepare($query);

        $sql->execute([
            'user_id' => $userId,
            'channel_id' => $channelId
        ]);

        return $sql->fetchColumn() !== false;
    }

    public function updateChannel(int $channelId, array $values): array
    {
        $channel = $this->findIdChannel($channelId);

        $query = 'UPDATE channels
        SET name = :name, adult_gate = :adult_gate
        WHERE id = :id';

        $sql = $this->db->prepare($query);

        $sql->execute([
            'id' => $channelId,
            'name' => $values['name'] ?? $channel['name'],
            'adult_gate' => $values['adult_gate'] ?? $channel['adult_gate']
        ]);

        return $this->findIdChannel($channelId);
    }

    public function joinChannel(
        int $userId,
        int $channelId
    ): bool {

        // Retrieve the channel and its parent group's age restrictions.
        $query = 'SELECT
                c.group_id,
                c.adult_gate AS channel_adult_gate,
                g.adult_gate AS group_adult_gate,
                (
                    SELECT user_birthday <=
                        DATE_SUB(CURDATE(), INTERVAL 18 YEAR)
                    FROM users
                    WHERE id = :user_id
                ) AS is_adult
              FROM channels c
              INNER JOIN groups g ON c.group_id = g.id
              WHERE c.id = :channel_id';

        $sql = $this->db->prepare($query);

        $sql->execute([
            'user_id' => $userId,
            'channel_id' => $channelId
        ]);

        $channel = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$channel) {
            throw new ChannelNotFoundException();
        }

        // Check both the channel's and parent group's adult_gate.
        $adultGate =
            (int) $channel['channel_adult_gate'] === 1 ||
            (int) $channel['group_adult_gate'] === 1;

        if ($adultGate && !(bool) $channel['is_adult']) {
            return false;
        }

        // Create or reactivate channel membership.
        $query = 'INSERT INTO memberships
                (user_id, group_id, channel_id, mod_status)
              VALUES
                (:user_id, :group_id, :channel_id, 0)
              ON DUPLICATE KEY UPDATE
                join_date = UTC_TIMESTAMP(),
                leave_date = NULL';

        $sql = $this->db->prepare($query);

        $sql->execute([
            'user_id' => $userId,
            'group_id' => $channel['group_id'],
            'channel_id' => $channelId
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
        $plan = ChannelCollectionQuery::plan($query);
        $total = (int) $this->execute($plan['countSql'], $plan['filters'])->fetchColumn();
        $rows = $this->execute($plan['sql'], $plan['params'])->fetchAll(PDO::FETCH_ASSOC);

        $items = array_map(static fn(array $r): array => [
            'id' => (int) $r['id'],
            'name' => $r['name'],
            'group_id' => (int) $r['group_id'],
            'adult_gate' => (bool) $r['adult_gate']
        ], $rows);

        return ChannelCollectionQuery::envelope($items, $query, $total);
    }
}
