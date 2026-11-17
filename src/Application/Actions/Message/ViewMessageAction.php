<?php

declare(strict_types=1);

namespace App\Application\Actions\Message;

use Psr\Http\Message\ResponseInterface as Response;

class ViewMessageAction extends MessageAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $messageId = (int) $this->resolveArg('id');
        $message = $this->messageRepository->findIdMessage($messageId);

        $this->logger->info("Message of id `{$messageId}` was viewed.");

        return $this->respondWithData($message);
    }
}
