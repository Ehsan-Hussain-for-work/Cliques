<?php

declare(strict_types=1);

use App\Application\Middleware\SessionMiddleware;
use Slim\App;
use App\Application\Middleware\ContentNegotiationMiddleware;

return function (App $app) {
    $app->add(SessionMiddleware::class);
    $app->addRoutingMiddleware();
    $app->add(ContentNegotiationMiddleware::class);
    $app->add(\App\Application\Middleware\LoggingMiddleware::class);
    $app->add(\App\Application\Middleware\RequestIdMiddleware::class);
    $errorMiddleware = $app->addErrorMiddleware(true, true, true);
    $errorMiddleware->setDefaultErrorHandler(
        new \App\Application\Handlers\HttpErrorHandler(
            $app->getCallableResolver(),
            $app->getResponseFactory()
        )
    );
};
