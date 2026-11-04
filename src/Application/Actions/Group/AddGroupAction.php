<?php

declare(strict_types=1);

namespace App\Application\Actions\Group;

use App\Application\Actions\Group\GroupAction;

use Psr\Http\Message\ResponseInterface as Response;

class AddGroupAction extends GroupAction
{
    protected function action(): Response
    {
        $addRequest = $this->getFormData();

        //error handle here

        $addRequest['adult_gate'] = filter_var($addRequest['adult_gate'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        $group = $this->groupRepository->addGroup($addRequest);

        return $this->respondWithData($group, 201);
    }
}
