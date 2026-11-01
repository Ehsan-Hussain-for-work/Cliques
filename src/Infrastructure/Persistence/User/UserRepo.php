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

        if (!$users) {
            throw new UserNotFoundException();
        } else {
            return $users;
        }
    }

    public function findUserOfId(int $id): array
    {
        $query = 'SELECT id, username, user_email, creation_date, user_birthday, warning_status FROM users WHERE id = :id';

        $sql = $this->db->query($query);
        $sql->execute(['id' => $id]);
        $user = $sql->fetch();

        if (!$user) {
            throw new UserNotFoundException();
        } else {
            return $user;
        }
    }
}
