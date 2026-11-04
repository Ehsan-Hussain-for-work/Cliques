<?php

namespace App\Application\Actions\Channel;

use Psr\Http\Message\ResponseInterface as Response;

class RemoveChannelAction extends ChannelAction
{

    protected function action(): Response
    {
        $channelId = (int) $this->args['id'];

        $deletion = $this->channelRepository->removeChannel($channelId);

        if ($deletion) {
            return $this->respondWithData(['message' => "Channel $channelId deleted."]);
        } else {
            //error message goes here. Temp value here for now.  
            return $this->respondWithData(['message' => "No Channel deleted."]);
        }
    }
}
