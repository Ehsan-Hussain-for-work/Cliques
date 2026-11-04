<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Group;

use App\Domain\Group\GroupNotFoundException;
use App\Domain\Group\GroupRepository;
use Override;
use PDO;

class GroupRepo implements GroupRepository
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
}
