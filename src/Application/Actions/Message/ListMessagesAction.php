<?php

declare(strict_types=1);

namespace App\Application\Actions\Message;

use Psr\Http\Message\ResponseInterface as Response;

class ListMessagesAction extends MessageAction
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

    //     $allMessages = $this->messageRepository->countAll();

    //     $allPages = (int) ceil($allMessages / $limit);

    //     if ($allPages > 0 && $page > $allPages) {
    //         $page = $allPages;
    //         $offset = ($page - 1) * $limit;
    //     }

    //     $messages = $this->messageRepository->findAll($limit, $offset);

    //     return $this->respondWithData([
    //         'metadata' => [
    //             'total_items' => $allMessages,
    //             'total_pages' => $allPages,
    //             'current_page' => $page,
    //             'per_page' => $limit
    //         ],
    //         'data' => $messages
    //     ]);
    // }
    protected function action(): Response
    {
        $query = $this->request->getAttribute('collectionQuery');
        $responseData = $this->messageRepository->page($query);
        return $this->respondWithData($responseData);
    }
}
