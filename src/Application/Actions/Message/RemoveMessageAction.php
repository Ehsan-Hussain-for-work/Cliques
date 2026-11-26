<?php

namespace App\Application\Actions\Message;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpForbiddenException;

class RemoveMessageAction extends MessageAction
{

    protected function action(): Response
    {
        // 1. Get authenticated user's info
        $authUser = $this->request->getAttribute('authUser');
        $authUserId = (int) $authUser['id'];

        //2. Get message info
        $messageId = (int) $this->args['id'];
        $message = $this->messageRepository->findIdMessage($messageId);
        $senderId = (int) $message['sender_id'];

        // 3. Authorization check
        if ($authUserId !== $senderId) {
            throw new HttpForbiddenException(
                $this->request,
                'Only the one who sent the message can delete it.'
            );
        }



        $deletion = $this->messageRepository->removeMessage($messageId);

        if ($deletion) {
            return $this->respondWithData(['message' => "Message $messageId deleted."]);
        } else {
            //error message goes here. Temp value here for now.  
            return $this->respondWithData(['message' => "No Message deleted."]);
        }
    }
}
