<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use Psr\Http\Message\ResponseInterface as Response;

class ListUsersAction extends UserAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $query = $this->request->getAttribute('collectionQuery');
        $responseData = $this->userRepository->page($query);
        return $this->respondWithData($responseData);
    }
}
