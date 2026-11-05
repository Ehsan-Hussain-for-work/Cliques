<?php

declare(strict_types=1);

namespace App\Application\Actions\Group;

use Psr\Http\Message\ResponseInterface as Response;

class ListGroupsAction extends GroupAction
{
    // /**
    //  * {@inheritdoc}
    //  */
    // protected function action(): Response
    // {
    //     $data = $this->getURLData();

    //     $page = isset($data['page']) ? (int)$data['page'] : 1;

    //     $limit = 5;

    //     if ($page < 1) {
    //         $page = 1;
    //     }

    //     $offset = ($page - 1) * $limit;

    //     $allGroups = $this->groupRepository->countAll();

    //     $allPages = (int) ceil($allGroups / $limit);

    //     if ($allPages > 0 && $page > $allPages) {
    //         $page = $allPages;
    //         $offset = ($page - 1) * $limit;
    //     }

    //     $groups = $this->groupRepository->findAll($limit, $offset);

    //     return $this->respondWithData([
    //         'metadata' => [
    //             'total_items' => $allGroups,
    //             'total_pages' => $allPages,
    //             'current_page' => $page,
    //             'per_page' => $limit
    //         ],
    //         'data' => $groups
    //     ]);
    // }
    protected function action(): Response
    {
        $query = $this->request->getAttribute('collectionQuery');
        $responseData = $this->groupRepository->page($query);
        return $this->respondWithData($responseData);
    }
}
