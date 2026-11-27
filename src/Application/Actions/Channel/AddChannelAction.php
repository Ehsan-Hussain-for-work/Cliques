<?php

declare(strict_types=1);

namespace App\Application\Actions\Channel;

use App\Application\Actions\Channel\ChannelAction;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException as BadReq;

class AddChannelAction extends ChannelAction
{
    protected function action(): Response
    {
        $authUser = $this->request->getAttribute('authUser');

        $creatorId = (int) $authUser['id'];
        if (empty($addRequest) || !\is_array($addRequest)) {
            throw new BadReq($this->request, 'Invalid or missing request body. Please send application/json.');
        }

        $addRequest = (array) $this->getFormData();

        $addRequest['adult_gate'] = filter_var(
            $addRequest['adult_gate'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        ) ? 1 : 0;

        $channel = $this->channelRepository->addChannel(
            $creatorId,
            $addRequest
        );

        return $this->respondWithData($channel, 201);
    }
}
