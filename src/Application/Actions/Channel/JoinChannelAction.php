<?php

declare(strict_types=1);

namespace App\Application\Actions\Channel;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpForbiddenException;

class JoinChannelAction extends ChannelAction
{
    protected function action(): Response
    {
        // Get the channel ID from the URL.
        $channelId = (int) $this->resolveArg('id');

        // Get the authenticated user.
        $authUser = $this->request->getAttribute('authUser');

        $userId = (int) $authUser['id'];

        // Attempt to join the channel.
        $joined = $this->channelRepository->joinChannel(
            $userId,
            $channelId
        );

        if (!$joined) {
            throw new HttpForbiddenException(
                $this->request,
                'You must be at least 18 years old to join this channel.'
            );
        }

        return $this->respondWithData([
            'message' => 'Successfully joined channel.',
            'channel_id' => $channelId
        ], 200);
    }
}
