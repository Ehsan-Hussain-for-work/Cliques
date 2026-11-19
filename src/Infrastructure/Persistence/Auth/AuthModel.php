<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth;

use App\Domain\Auth\AuthRepository;
use PDO;

class AuthModel implements AuthRepository
{
    private PDO $db;

    public function __construct(PDO $database)
    {
        $this->db = $database;
    }

    public function findEmailUser(string $email): ?array
    {
        $query = 'SELECT
        id, username, user_email, password_hash, warning_status
        FROM users
        WHERE user_email = :email';

        $sql = $this->db->prepare($query);
        $sql->execute([
            'email' => $email
        ]);
        $user = $sql->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function createToken(
        int $userId,
        string $tokenHash,
        string $expirationDate
    ): void {

        $query = 'INSERT INTO user_tokens
        (user_id, token_hash, creation_date, expiration_date)
        VALUES
        (:user_id, :token_hash, UTC_TIMESTAMP(), :expiration_date)';

        $sql = $this->db->prepare($query);
        $sql->execute([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expiration_date' => $expirationDate
        ]);
    }

    public function findTokenUser(string $tokenHash): ?array
    {
        $query = 'SELECT u.id, u.username, u.user_email, u.creation_date, u.user_birthday, u.warning_status
        FROM user_tokens t
        INNER JOIN users u
        ON t.user_id = u.id
        WHERE t.token_hash = :token_hash
        AND t.revoked_date IS NULL
        AND t.expiration_date > UTC_TIMESTAMP()';

        $sql = $this->db->prepare($query);
        $sql->execute([
            'token_hash' => $tokenHash
        ]);
        $user = $sql->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function revokeToken(string $tokenHash): bool
    {
        $query = 'UPDATE user_tokens
        SET revoked_Date = UTC_TIMESTAMP()
        WHERE token_hash = :token_hash
        AND revoked_Date IS NULL';

        $sql = $this->db->prepare($query);
        $sql->execute([
            'token_hash' => $tokenHash
        ]);

        return $sql->rowCount() > 0;
    }
}
