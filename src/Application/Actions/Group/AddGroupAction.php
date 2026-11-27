<?php

declare(strict_types=1);

namespace App\Application\Actions\Group;

use App\Application\Actions\Group\GroupAction;
use Psr\Http\Message\ResponseInterface as Response;

class AddGroupAction extends GroupAction
{
    protected function action(): Response
    {
        // Get authenticated user.
        $authUser = $this->request->getAttribute('authUser');
        $creatorId = (int) $authUser['id'];

        $addRequest = (array) $this->getFormData();

        $addRequest['adult_gate'] = filter_var(
            $addRequest['adult_gate'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        ) ? 1 : 0;

        $group = $this->groupRepository->addGroup(
            $creatorId,
            $addRequest
        );

        return $this->respondWithData($group, 201);
    }
}
