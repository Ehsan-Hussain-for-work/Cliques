<?php

namespace App\Application\Actions\Group;

use Psr\Http\Message\ResponseInterface as Response;

class RemoveGroupAction extends GroupAction
{

    protected function action(): Response
    {
        $groupId = (int) $this->args['id'];

        $deletion = $this->groupRepository->removeGroup($groupId);

        if ($deletion) {
            return $this->respondWithData(['message' => "Group $groupId deleted."]);
        } else {
            //error message goes here. Temp value here for now.  
            return $this->respondWithData(['message' => "No Group deleted."]);
        }
    }
}
