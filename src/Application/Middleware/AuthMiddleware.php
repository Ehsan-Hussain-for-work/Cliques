<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Domain\Auth\AuthRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpUnauthorizedException;

class AuthMiddleware implements Middleware
{
    private AuthRepository $authRepo;

    public function __construct(
        AuthRepository $authRepo
    ) {
        $this->authRepo = $authRepo;
    }

    public function process(
        Request $request,
        RequestHandler $handler
    ): Response {
        $authorization = $request->getHeaderLine('Authorization');

        //Checks for token.
        if (!preg_match('/^Bearer\s+(\S+)$/i', $authorization, $matches)) {
            throw new HttpUnauthorizedException(
                $request,
                'Missing or invalid authentication token.'
            );
        }

        $token = $matches[1];
        $tokenHash = hash('sha256', $token);
        $user = $this->authRepo->findTokenUser($tokenHash);

        if ($user === null) {
            throw new HttpUnauthorizedException(
                $request,
                'Invalid or expired authentication token.'
            );
        }

        //Request belongs to user
        $request = $request->withAttribute(
            'authUser',
            $user
        );

        //For LogoutAction to revoke.
        $request = $request->withAttribute(
            'authTokenHash',
            $tokenHash
        );

        return $handler->handle($request);
    }
}
