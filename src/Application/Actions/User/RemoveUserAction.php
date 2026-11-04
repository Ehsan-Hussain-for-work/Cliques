<?php

namespace App\Application\Actions\User;

use Psr\Http\Message\ResponseInterface as Response;

class RemoveUserAction extends UserAction
{

    protected function action(): Response
    {
        $userId = (int) $this->args['id'];

        $deletion = $this->userRepository->removeUser($userId);

        if ($deletion) {
            return $this->respondWithData(['message' => "User $userId deleted."]);
        } else {
            //error message goes here. Temp value here for now.  
            return $this->respondWithData(['message' => "No User deleted."]);
        }
    }
}
