<?php

namespace App\Application\Middleware;

use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Application\Exceptions\HttpNotAcceptableException;
use App\Application\Services\FormatSelector;

class ContentNegotiationMiddleware implements Middleware
{
    private FormatSelector $selector;

    public function __construct(FormatSelector $selector)
    {
        $this->selector = $selector;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // Note: For this project's scope, supported formats are hardcoded inline.
        // move to a config class or enum if the business logic is liable to change
        $accept = $request->hasHeader('Accept') ? $request->getHeaderLine('Accept') : null;
        // die('Middleware ran! Accept header was: ' . $accept);
        $supported = ['application/json', 'application/xml', 'application/yaml'];

        $media = $this->selector->select($accept, $supported);

        if ($media === null) {
            // This triggers your 406 Error Handler
            throw new HttpNotAcceptableException($request, 'No acceptable success format.');
        }

        // Pass the chosen format downstream to the Action
        $request = $request->withAttribute('responseFormat', $media);

        return $handler->handle($request);
    }
}
