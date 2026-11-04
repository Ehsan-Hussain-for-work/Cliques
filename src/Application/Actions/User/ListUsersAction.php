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
        $data = $this->getURLData();

        $page = isset($data['page']) ? (int)$data['page'] : 1;

        $limit = 5;

        if ($page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;

        $allUsers = $this->userRepository->countAll();

        $allPages = (int) ceil($allUsers / $limit);

        if ($allPages > 0 && $page > $allPages) {
            $page = $allPages;
            $offset = ($page - 1) * $limit;
        }

        $users = $this->userRepository->findAll($limit, $offset);

        return $this->respondWithData([
            'metadata' => [
                'total_items' => $allUsers,
                'total_pages' => $allPages,
                'current_page' => $page,
                'per_page' => $limit
            ],
            'data' => $users
        ]);
    }
}
