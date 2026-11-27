<?php

declare(strict_types=1);

namespace App\Application\Actions\Group;

use App\Application\Actions\Group\GroupAction;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException as BadReq;

class AddGroupAction extends GroupAction
{
    protected function action(): Response
    {
        $addRequest = $this->getFormData();

        if (empty($addRequest) || !\is_array($addRequest)) {
            throw new BadReq($this->request, 'Invalid or missing request body. Please send application/json.');
        }

        $addRequest['adult_gate'] = filter_var($addRequest['adult_gate'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        $group = $this->groupRepository->addGroup($addRequest);

        return $this->respondWithData($group, 201);
    }
}
