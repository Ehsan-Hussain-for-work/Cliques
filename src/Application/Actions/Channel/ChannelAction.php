<?php

declare(strict_types=1);

namespace App\Application\Actions\Channel;

use App\Application\Actions\Action;
use App\Domain\Channel\ChannelRepository;
use Psr\Log\LoggerInterface;

abstract class ChannelAction extends Action
{
    protected ChannelRepository $channelRepository;

    public function __construct(LoggerInterface $logger, ChannelRepository $channelRepository)
    {
        parent::__construct($logger);
        $this->channelRepository = $channelRepository;
    }
}
