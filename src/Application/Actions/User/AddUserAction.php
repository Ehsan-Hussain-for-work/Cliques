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
        $addRequest = $this->getFormData();

        if (empty($addRequest) || !is_array($addRequest)) {
            throw new BadReq($this->request, 'Invalid or missing request body. Please send application/json.');
        }

        $addRequest['password_hash'] = password_hash($addRequest['password'], PASSWORD_DEFAULT);

        $addRequest['warning_status'] = filter_var($addRequest['warning_status'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        $user = $this->userRepository->addUser($addRequest);

        return $this->respondWithData($user, 201);
    }
}
