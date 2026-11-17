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

        //error handle here

        $addRequest['adult_gate'] = filter_var($addRequest['adult_gate'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        $message = $this->messageRepository->addMessage($addRequest);

        return $this->respondWithData($message, 201);
    }
}
