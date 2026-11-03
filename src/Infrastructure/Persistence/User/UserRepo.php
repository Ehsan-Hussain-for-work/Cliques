<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use PDO;

class UserRepo implements UserRepository
{
    private PDO $db;

    public function __construct(PDO $database)
    {
        $this->db = $database;
    }

    public function findAll(): array
    {
        $query = 'SELECT * FROM users';

        $sql = $this->db->query($query);

        $users = $sql->fetchAll();

        return $users;
    }

    public function findUserOfId(int $id): array
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
        return $this->findUserOfId($createdId);
    }
}
