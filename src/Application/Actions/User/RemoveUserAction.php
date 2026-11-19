<?php

namespace App\Application\Actions\User;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpForbiddenException;

class RemoveUserAction extends UserAction
{

    protected function action(): Response
    {
        // 1. Get authenticated user's information
        $authUser = $this->request->getAttribute('authUser');

        $authUserId = (int) $authUser['id'];

        // 2. Get requested profile ID
        $requestUserId = (int) $this->resolveArg('id');

        // 3. Authorization check
        if ($authUserId !== $requestUserId) {
            throw new HttpForbiddenException(
                $this->request,
                'Only the owner of the profile can delete it.'
            );
        }

        $deletion = $this->userRepository->removeUser($authUserId);

        if ($deletion) {
            return $this->respondWithData(['message' => "User $requestUserId deleted."]);
        } else {
            return $this->respondWithData(['message' => "No User deleted."]);
        }
    }
}
