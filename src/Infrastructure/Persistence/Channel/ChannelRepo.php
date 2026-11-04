<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Channel;

use App\Domain\Channel\ChannelNotFoundException;
use App\Domain\Channel\ChannelRepository;
use Override;
use PDO;

class ChannelRepo implements ChannelRepository
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

    public function addChannel(array $providedData): array
    {
        $query = 'INSERT INTO channels 
        (`name`, group_id, adult_gate) 
        VALUES 
        (:name, :group_id, :adult_gate)';

        $sql = $this->db->prepare($query);
        $sql->execute([
            'name' => $providedData['name'],
            'group_id' => $providedData['group_id'],
            'adult_gate' => $providedData['adult_gate']
        ]);

        $createdId = (int) $this->db->lastInsertId();
        return $this->findIdChannel($createdId);
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
}
