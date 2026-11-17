<?php

namespace App\Application\Actions\Message;

use Psr\Http\Message\ResponseInterface as Response;

class RemoveMessageAction extends MessageAction
{

    protected function action(): Response
    {
        $messageId = (int) $this->args['id'];

        $deletion = $this->messageRepository->removeMessage($messageId);

        if ($deletion) {
            return $this->respondWithData(['message' => "Message $messageId deleted."]);
        } else {
            //error message goes here. Temp value here for now.  
            return $this->respondWithData(['message' => "No Message deleted."]);
        }
    }
}
