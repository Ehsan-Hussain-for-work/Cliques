<?php

declare(strict_types=1);

use App\Application\Middleware\SessionMiddleware;
use Slim\App;
use App\Application\Middleware\ContentNegotiationMiddleware;

return function (App $app) {
    $app->add(SessionMiddleware::class);
    $app->addRoutingMiddleware();
    $app->add(ContentNegotiationMiddleware::class);
    $app->addErrorMiddleware(true, true, true);
};
