<?php

namespace App\Application\Middleware;

use App\Application\Support\UserCollectionQuery;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Exception\HttpBadRequestException as BadRequest;


final class CollectionQueryMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $next): Response
    {
        $id = bin2hex(random_bytes(8));
        $request = $request->withAttribute('requestId', $id);
        try {
            $rawQuery = $request->getUri()->getQuery();
            $query = UserCollectionQuery::parse($rawQuery);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequest($request, $e->getMessage());
        }

        $request = $request->withAttribute('collectionQuery', $query);
        $response = $next->handle($request);
        return $response->withHeader('X-Requested-ID', $id);
    }
}
