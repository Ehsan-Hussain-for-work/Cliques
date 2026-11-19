<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SessionMiddleware implements Middleware
{
    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        //TODO: Retrieve the Authorization header. (Authorization: Bearer YOUR_TOKEN)

        //TODO: Extract the token.

        //TODO: Hash the token. ($tokenHash = hash('sha256', $token);)

        //TODO: Check the database.

        //TODO: Retrieve the associated user.

        /* //TODO: Attach the user to the request. ($request = $request->withAttribute('authenticatedUser',$user);) */

        //TODO: Continue processing the request. (return $handler->handle($request); or "401 Unauthorized")

        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            session_start();
            $request = $request->withAttribute('session', $_SESSION);
        }

        return $handler->handle($request);
    }
}
