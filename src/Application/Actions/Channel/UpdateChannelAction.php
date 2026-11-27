<?php

declare(strict_types=1);

namespace App\Application\Actions\Channel;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;

class UpdateChannelAction extends ChannelAction
{
    protected function action(): Response
    {
        // Get channel ID.
        $channelId = (int) $this->resolveArg('id');

        // Get authenticated user.
        $authUser = $this->request->getAttribute('authUser');

        $userId = (int) $authUser['id'];

        // Make sure the channel exists.
        $this->channelRepository->findIdChannel($channelId);

        // Check moderator permissions.
        if (!$this->channelRepository->isMod(
            $userId,
            $channelId
        )) {
            throw new HttpForbiddenException(
                $this->request,
                'Only a channel moderator can edit the channel.'
            );
        }

        // Retrieve submitted data.
        $data = (array) $this->getFormData();

        // Validate the new name.
        $name = trim($data['name'] ?? '');
        $data['adult_gate'] = filter_var(
            $data['adult_gate'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        ) ? 1 : 0;

        if ($name === '') {
            throw new HttpBadRequestException(
                $this->request,
                'A channel name is required.'
            );
        }

        // Update channel name.
        $channel = $this->channelRepository->updateChannel(
            $channelId,
            $data
        );

        return $this->respondWithData($channel, 200);
    }
}
