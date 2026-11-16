<?php

declare(strict_types=1);

namespace App\Domain\Message;

use App\Domain\DomainException\DomainRecordNotFoundException;

class MessageNotFoundException extends DomainRecordNotFoundException
{
    public $message = 'The message you requested does not exist.';
}
