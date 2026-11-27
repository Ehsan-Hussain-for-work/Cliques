<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Application\Actions\User\UserAction;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException as BadReq;

class AddUserAction extends UserAction
{
    protected function action(): Response
    {
        $addRequest = (array) $this->getFormData();

        if (empty($addRequest) || !is_array($addRequest)) {
            throw new BadReq($this->request, 'Invalid or missing request body. Please send application/json.');
        }
        $addRequest['password_hash'] = password_hash(
            $addRequest['password'],
            PASSWORD_DEFAULT
        );

        $addRequest['warning_status'] = 0;

        $addRequest['user_birthday'] = $addRequest['user_birthday'] ?? null;

        unset($addRequest['password']);

        $user = $this->userRepository->addUser($addRequest);

        return $this->respondWithData($user, 201);
    }
}
