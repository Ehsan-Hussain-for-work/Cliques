<?php

namespace App\Application\Actions\User;

use App\Application\Actions\Action;
use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class UpdateUserAction extends Action
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository,)
    {
        $this->userRepository = $userRepository;
    }

    protected function action(): Response
    {
        // 1. Get ID from route
        $id = (int) $this->resolveArg('id');

        // 2. Get JSON body
        $data = (array) $this->request->getParsedBody();

        // 3. Quick Validation
        if (empty($data['username']) || empty($data['user_email'])) {
            throw new HttpBadRequestException($this->request, 'username and user_email are required.');
        }

        // 4. Update and Return
        $user = $this->userRepository->updateUser($id, $data);

        return $this->respondWithData($user, 200);
    }
}
