<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Application\Actions\User\UserAction;

use Psr\Http\Message\ResponseInterface as Response;

class AddUserAction extends UserAction
{
    protected function action(): Response
    {
        $addRequest = $this->request->getParsedBody();

        //error handle here

        $addRequest['password_hash'] = password_hash($addRequest['password'], PASSWORD_DEFAULT);

        $addRequest['warning_status'] = filter_var($addRequest['warning_status'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        $user = $this->userRepository->addUser($addRequest);

        return $this->respondWithData($user, 201);
    }
}
