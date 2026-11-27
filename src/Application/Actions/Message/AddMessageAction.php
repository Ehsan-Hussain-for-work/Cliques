<?php

declare(strict_types=1);

namespace App\Application\Actions\Message;

use App\Application\Actions\Message\MessageAction;

use Psr\Http\Message\ResponseInterface as Response;

class AddMessageAction extends MessageAction
{
    protected function action(): Response
    {
        $addRequest = $this->getFormData();

        // 1. Get authenticated user's information
        $authUser = $this->request->getAttribute('authUser');
        $addRequest['sent_time'] = gmdate('Y-m-d H:i:s', time());
        $addRequest['sender_id'] = (int) $authUser['id'];
        $addRequest['edited'] = 0;

        $message = $this->messageRepository->addMessage($addRequest);

        return $this->respondWithData($message, 201);
    }
}
