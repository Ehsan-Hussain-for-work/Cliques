<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\Action;
use App\Domain\Auth\AuthRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class LogoutAction extends Action
{
    private AuthRepository $authRepo;

    public function __construct(
        LoggerInterface $logger,
        AuthRepository $authRepo
    ) {
        parent::__construct($logger);

        $this->authRepo = $authRepo;
    }

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
