<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use \Slim\Exception\HttpUnauthorizedException;

class LoginAction extends AuthAction
{
    protected function action(): Response
    {
        $data = (array) $this->getFormData();

        //Validates required credentials.
        $email = trim($data['user_email'] ?? '');
        $password = $data['password'] ?? '';

        if ($email === '' || !is_string($password) || $password === '') {
            throw new HttpBadRequestException(
                $this->request,
                'An Email and a password are required.'
            );
        }

        $user = $this->authRepo->findEmailUser($email);

        //Verifies the password.
        if (
            $user === null ||
            !password_verify($password, $user['password_hash'])
        ) {
            throw new HttpUnauthorizedException(
                $this->request,
                'Invalid email or password.'
            );
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $expirationDate = gmdate(
            'Y-m-d H:i:s',
            time() + 3600
        );

        $this->authRepo->createToken(
            (int) $user['id'],
            $tokenHash,
            $expirationDate
        );

        $this->logger->info(
            "User {$user['id']} successfully authenticated."
        );

        return $this->respondWithData([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expiration_date' => 3600
        ]);
    }
}
