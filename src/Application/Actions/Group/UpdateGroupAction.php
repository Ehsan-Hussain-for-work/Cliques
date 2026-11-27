<?php

declare(strict_types=1);

namespace App\Application\Actions\Group;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;

class UpdateGroupAction extends GroupAction
{
    protected function action(): Response
    {
        $authUser = $this->request->getAttribute('authUser');
        $userId = (int) $authUser['id'];

        $groupId = (int) $this->resolveArg('id');

        if (!$this->groupRepository->isMod($userId, $groupId)) {
            throw new HttpForbiddenException(
                $this->request,
                'You must be a group moderator to edit the group.'
            );
        }

        $data = (array) $this->getFormData();

        $name = trim($data['name'] ?? '');
        $data['adult_gate'] = filter_var(
            $data['adult_gate'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        ) ? 1 : 0;

        if ($name === '') {
            throw new HttpBadRequestException(
                $this->request,
                'A group name is required.'
            );
        }

        $group = $this->groupRepository->updateGroup($groupId, $data);

        return $this->respondWithData($group);
    }
}
