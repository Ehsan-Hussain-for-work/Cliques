<?php

declare(strict_types=1);

namespace App\Application\Actions\Channel;

use Psr\Http\Message\ResponseInterface as Response;

class ViewChannelAction extends ChannelAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $channelId = (int) $this->resolveArg('id');
        $channel = $this->channelRepository->findIdChannel($channelId);

        $this->logger->info("Channel of id `{$channelId}` was viewed.");

        return $this->respondWithData($channel);
    }
}
