<?php

declare(strict_types=1);

namespace App\Application\Actions\Message;

use App\Application\Actions\Action;
use App\Domain\Message\MessageRepository;
use Psr\Log\LoggerInterface;

abstract class MessageAction extends Action
{
    protected MessageRepository $messageRepository;

    public function __construct(LoggerInterface $logger, MessageRepository $messageRepository)
    {
        parent::__construct($logger);
        $this->messageRepository = $messageRepository;
    }
}
