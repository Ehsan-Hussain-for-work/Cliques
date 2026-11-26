<?php

namespace App\Application\Actions\Message;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpForbiddenException;
use DateTime;
use DateTimeZone;

class UpdateMessageAction extends MessageAction
{
    protected function action(): Response
    {
        // 1. Get auth user info
        $authUser = $this->request->getAttribute('authUser');
        $authUserId = (int) $authUser['id'];

        //2. Get message info
        $messageId = (int) $this->args['id'];
        $message = $this->messageRepository->findIdMessage($messageId);
        $senderId = (int) $message['sender_id'];

        // 3. Get editing timeframe
        $validEditTime = new DateTime($message['sent_time'], new DateTimeZone('UTC'));
        $validEditTime->modify('+30 minutes');
        $currentTime = new DateTime('now', new DateTimeZone('UTC'));


        // 4. Authorization check
        if ($messageId !== $senderId) {
            throw new HttpForbiddenException(
                $this->request,
                'Only the one who sent the message can edit it.'
            );
        }

        // 5. valid time check
        if ($currentTime > $validEditTime) {
            throw new HttpForbiddenException(
                $this->request,
                'Messages can only be edited within the 30 minutes it was sent.'
            );
        }

        // 6. Get new JSON info
        $data = (array) $this->request->getParsedBody();

        $message = $this->messageRepository->updateMessage($messageId, $data["body"]);

        return $this->respondWithData($message, 200);
    }
}
