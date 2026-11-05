<?php

declare(strict_types=1);

namespace App\Application\Actions\Group;

use App\Application\Actions\Action;
use App\Domain\Group\GroupRepository;
use Psr\Log\LoggerInterface;

abstract class GroupAction extends Action
{
    protected GroupRepository $groupRepository;

    public function __construct(LoggerInterface $logger, GroupRepository $groupRepository)
    {
        parent::__construct($logger);
        $this->groupRepository = $groupRepository;
    }
}
