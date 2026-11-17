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
        $query = $this->request->getAttribute('collectionQuery');

        $responseData = $this->channelRepository->page($query);

        return $this->respondWithData($responseData);
    }
}
