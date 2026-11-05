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

    public function addGroup(array $providedData): array
    {
        $query = 'INSERT INTO groups 
        (`name`, creation_date, adult_gate) 
        VALUES 
        (:name, :creation_date, :adult_gate)';

        $sql = $this->db->prepare($query);
        $sql->execute([
            'name' => $providedData['name'],
            'creation_date' => $providedData['creation_date'],
            'adult_gate' => $providedData['adult_gate']
        ]);

        $createdId = (int) $this->db->lastInsertId();
        return $this->findIdGroup($createdId);
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
