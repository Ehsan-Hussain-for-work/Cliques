<?php

declare(strict_types=1);

namespace App\Application\Actions\Group;

use Psr\Http\Message\ResponseInterface as Response;

class ViewGroupAction extends GroupAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $groupId = (int) $this->resolveArg('id');
        $group = $this->groupRepository->findIdGroup($groupId);

        $this->logger->info("Group of id `{$groupId}` was viewed.");

        return $this->respondWithData($group);
    }
}
