<?php

declare(strict_types=1);

namespace App\Application\Actions\Channel;

use Psr\Http\Message\ResponseInterface as Response;

class ListChannelsAction extends ChannelAction
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

        $allChannels = $this->channelRepository->countAll();

        $allPages = (int) ceil($allChannels / $limit);

        if ($allPages > 0 && $page > $allPages) {
            $page = $allPages;
            $offset = ($page - 1) * $limit;
        }

        $channels = $this->channelRepository->findAll($limit, $offset);

        return $this->respondWithData([
            'metadata' => [
                'total_items' => $allChannels,
                'total_pages' => $allPages,
                'current_page' => $page,
                'per_page' => $limit
            ],
            'data' => $channels
        ]);
    }
}
