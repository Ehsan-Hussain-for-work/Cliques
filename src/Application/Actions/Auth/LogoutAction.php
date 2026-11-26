<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;

class LogoutAction extends AuthAction
{
    protected function action(): Response
    {
        $tokenHash = $this->request->getAttribute(
            'authTokenHash'
        );

        $this->authRepo->revokeToken($tokenHash);
        $this->logger->info('User successfully logged out.');

        return $this->respondWithData([
            'message' => 'Successfully logged out.'
        ]);
    }
}
