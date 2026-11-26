<?php

namespace App\Application\Actions\User;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;

class UpdateUserAction extends UserAction
{
    protected function action(): Response
    {
        // 1. Get auth user info
        $authUser = $this->request->getAttribute('authUser');

        $authUserId = (int) $authUser['id'];

        // 2. Get ID from route
        $requestUserId = (int) $this->resolveArg('id');

        // 3. Authorization check
        if ($authUserId !== $requestUserId) {
            throw new HttpForbiddenException(
                $this->request,
                'Only the owner of the profile can edit it.'
            );
        }

        // 2. Get JSON body
        $data = (array) $this->request->getParsedBody();

        // 3. Quick Validation
        if (empty($data['username']) || empty($data['user_email'])) {
            throw new HttpBadRequestException($this->request, 'username and user_email are required.');
        }

        // 4. Update and Return
        $user = $this->userRepository->updateUser(
            $authUserId,
            $data
        );

        return $this->respondWithData($user, 200);
    }
}
