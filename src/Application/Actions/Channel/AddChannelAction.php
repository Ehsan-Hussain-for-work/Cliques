<?php

declare(strict_types=1);

namespace App\Application\Actions\Channel;

use App\Application\Actions\Channel\ChannelAction;

use Psr\Http\Message\ResponseInterface as Response;

class AddChannelAction extends ChannelAction
{
    protected function action(): Response
    {
        $addRequest = $this->getFormData();

        //error handle here

        $addRequest['adult_gate'] = filter_var($addRequest['adult_gate'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        $channel = $this->channelRepository->addChannel($addRequest);

        return $this->respondWithData($channel, 201);
    }
}
