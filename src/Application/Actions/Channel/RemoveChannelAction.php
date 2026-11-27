<?php

namespace App\Application\Actions\Channel;

use Psr\Http\Message\ResponseInterface as Response;

class RemoveChannelAction extends ChannelAction
{

    protected function action(): Response
    {
        $authUser = $this->request->getAttribute('authUser');
        $userId = (int) $authUser['id'];

        $channelId = (int) $this->resolveArg('id');

        if (!$this->channelRepository->isMod($userId, $channelId)) {
            throw new \Slim\Exception\HttpForbiddenException(
                $this->request,
                'Only channel moderators can delete the channel.'
            );
        }

        $deletion = $this->channelRepository->removeChannel($channelId);

        if ($deletion) {
            return $this->respondWithData(['message' => "Channel $channelId deleted."]);
        } else {
            return $this->respondWithData([
                'message' => 'No channel deleted.'
            ], 404);
        }
    }
}
