<?php

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class RequestIdMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $next): Response
    {
        // 1. Generate the ID
        $id = bin2hex(random_bytes(16));

        // 2. Attach to request
        $request = $request->withAttribute('requestId', $id);

        // 3. Pass to next handler
        $response = $next->handle($request);

        // 4. Add to response header
        return $response->withHeader('X-Request-ID', $id);
    }
}
