<?php

namespace App\Application\Actions\Group;

use Psr\Http\Message\ResponseInterface as Response;

class RemoveGroupAction extends GroupAction
{

    protected function action(): Response
    {
        $authUser = $this->request->getAttribute('authUser');
        $userId = (int) $authUser['id'];

        $groupId = (int) $this->resolveArg('id');

        // Check moderator permissions.
        if (!$this->groupRepository->isMod($userId, $groupId)) {

            throw new \Slim\Exception\HttpForbiddenException(
                $this->request,
                'You must be a group moderator to delete the group.'
            );
        }

        $deletion = $this->groupRepository->removeGroup($groupId);

        if ($deletion) {
            return $this->respondWithData([
                'message' => "Group $groupId deleted."
            ]);
        }

        return $this->respondWithData([
            'message' => 'No group deleted.'
        ], 404);
    }
}
