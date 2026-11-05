<?php

declare(strict_types=1);

namespace App\Domain\Channel;

use App\Domain\DomainException\DomainRecordNotFoundException;

class ChannelNotFoundException extends DomainRecordNotFoundException
{
    public $message = 'The channel you requested does not exist.';
}
