<?php

declare(strict_types=1);

namespace App\Application\Actions\Group;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpForbiddenException;

class JoinGroupAction extends GroupAction
{
    protected function action(): Response
    {
        // Get the group ID from the URL.
        $groupId = (int) $this->resolveArg('id');

        // Get the authenticated user.
        $authUser = $this->request->getAttribute('authUser');

        $userId = (int) $authUser['id'];

        // Attempt to join the group.
        // The repository checks the adult_gate setting.
        $joined = $this->groupRepository->joinGroup(
            $userId,
            $groupId
        );

        if (!$joined) {
            throw new HttpForbiddenException(
                $this->request,
                'You must be at least 18 years old to join this group.'
            );
        }

        return $this->respondWithData([
            'message' => 'Successfully joined group.',
            'group_id' => $groupId
        ], 200);
    }
}
