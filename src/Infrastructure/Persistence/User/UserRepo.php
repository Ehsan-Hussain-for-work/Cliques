<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use Override;
use PDO;

class UserRepo implements UserRepository
{
    private PDO $db;

    public function __construct(PDO $database)
    {
        $this->db = $database;
    }

    public function countAll(): int
    {
        $query = 'SELECT COUNT(*) FROM users';

        $sql = $this->db->query($query);

        $userNum = (int) $sql->fetchColumn();

        return $userNum;
    }

    public function findAll(int $limit, int $offset): array
    {
        $query = 'SELECT `id`, `username`, `user_email`, `creation_date`, `user_birthday`, `warning_status` 
        FROM users 
        ORDER BY id ASC 
        LIMIT :limit OFFSET :offset';

        $sql = $this->db->prepare($query);

        $sql->bindValue(':limit', $limit, PDO::PARAM_INT);
        $sql->bindValue(':offset', $offset, PDO::PARAM_INT);

        $sql->execute();
        $users = $sql->fetchAll(PDO::FETCH_ASSOC);

        return $users;
    }

    public function findIdUser(int $id): array
    {
        $query = 'SELECT id, username, user_email, creation_date, user_birthday, warning_status 
        FROM users 
        WHERE id = :id';

        $sql = $this->db->prepare($query);
        $sql->execute(['id' => $id]);
        $user = $sql->fetch();

        if (!$user) {
            throw new UserNotFoundException();
        } else {
            return $user;
        }
    }

    public function addUser(array $providedData): array
    {
        $query = 'INSERT INTO users 
        (username, user_email, user_birthday, password_hash, warning_status) 
        VALUES 
        (:username, :user_email, :user_birthday, :password_hash, :warning_status)';

        $sql = $this->db->prepare($query);
        $sql->execute([
            'username' => $providedData['username'],
            'user_email' => $providedData['user_email'],
            'user_birthday' => $providedData['user_birthday'],
            'password_hash' => $providedData['password_hash'],
            'warning_status' => $providedData['warning_status']
        ]);

        $createdId = (int) $this->db->lastInsertId();
        return $this->findIdUser($createdId);
    }

    #[Override]
    public function removeUser(int $id): bool
    {
        $query = 'DELETE FROM users WHERE id = :id';

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
