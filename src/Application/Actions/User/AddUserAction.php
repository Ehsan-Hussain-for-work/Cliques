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

        $user = $this->userRepository->addUser($addRequest);

        return $this->respondWithData($user, 201);
    }
}
