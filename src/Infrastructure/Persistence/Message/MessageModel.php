<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Message;

use App\Domain\Message\MessageNotFoundException;
use App\Domain\Message\MessageRepository;
use Override;
use PDO;
use App\Application\Support\MessageCollectionQuery;

class MessageModel implements MessageRepository
{
    private PDO $db;

    public function __construct(PDO $database)
    {
        $this->db = $database;
    }

    public function countAll(): int
    {
        $query = 'SELECT COUNT(*) FROM messages';

        $sql = $this->db->query($query);

        $messageNum = (int) $sql->fetchColumn();

        return $messageNum;
    }

    public function findAll(int $limit, int $offset): array
    {
        $query = 'SELECT * 
        FROM messages 
        ORDER BY id ASC 
        LIMIT :limit OFFSET :offset';

        $sql = $this->db->prepare($query);

        $sql->bindValue(':limit', $limit, PDO::PARAM_INT);
        $sql->bindValue(':offset', $offset, PDO::PARAM_INT);

        $sql->execute();
        $messages = $sql->fetchAll(PDO::FETCH_ASSOC);

        return $messages;
    }

    public function findIdMessage(int $id): array
    {
        $query = 'SELECT id, body, sent_time, sender_id, channel_id, edited
        FROM messages 
        WHERE id = :id';

        $sql = $this->db->prepare($query);
        $sql->execute(['id' => $id]);
        $message = $sql->fetch();

        if (!$message) {
            throw new MessageNotFoundException();
        } else {
            return $message;
        }
    }

    public function addMessage(array $providedData): array
    {
        $query = 'INSERT INTO messages 
        (body, sent_time, sender_id, channel_id, edited) 
        VALUES 
        (:body, :sent_time, :sender_id, :channel_id, :edited) ';

        $sql = $this->db->prepare($query);
        $sql->execute([
            'body' => $providedData['body'],
            'sent_time' => $providedData['sent_time'],
            'sender_id' => $providedData['sender_id'],
            'channel_id' => $providedData['channel_id'],
            'edited' => $providedData['edited']
        ]);

        $createdId = (int) $this->db->lastInsertId();
        return $this->findIdMessage($createdId);
    }

    public function removeMessage(int $id): bool
    {
        $query = 'DELETE FROM messages WHERE id = :id';

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
        $plan = MessageCollectionQuery::plan($query);
        $total = (int) $this->execute($plan['countSql'], $plan['filters'])->fetchColumn();
        $rows = $this->execute($plan['sql'], $plan['params'])->fetchAll(PDO::FETCH_ASSOC);

        $items = array_map(static fn(array $r): array => [
            'id' => (int) $r['id'],
            'body' => $r['body'],
            'sent_time' => $r['sent_time'],
            'sender_id' => (int) $r['sender_id'],
            'channel_id' => (int) $r['channel_id'],
            'edited' => (bool) $r['edited']
        ], $rows);

        return MessageCollectionQuery::envelope($items, $query, $total);
    }
}
