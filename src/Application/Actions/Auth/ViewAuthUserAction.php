<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;

class ViewAuthUserAction extends AuthAction
{
    protected function action(): Response
    {
        $user = $this->request->getAttribute('authUser');
        return $this->respondWithData($user);
    }
}
