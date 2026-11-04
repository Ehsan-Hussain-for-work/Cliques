<?php

declare(strict_types=1);

use App\Application\Actions\User\RemoveUserAction;
use App\Application\Actions\User\AddUserAction;
use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use App\Application\Actions\Group\RemoveGroupAction;
use App\Application\Actions\Group\AddGroupAction;
use App\Application\Actions\Group\ListGroupsAction;
use App\Application\Actions\Group\ViewGroupAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Application\Middleware\CollectionQueryMiddleware as CollectionQ;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $indexPath = __DIR__ . '/../public/index.html';
        if (file_exists($indexPath)) {
            $htmlContent = file_get_contents($indexPath);
            $response->getBody()->write($htmlContent);
            return $response->withHeader('Content-Type', 'text/html');
        }
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class)
            ->add(CollectionQ::class);
        $group->get('/{id}', ViewUserAction::class);
        $group->post('', AddUserAction::class);
        $group->delete('/{id}', RemoveUserAction::class);
    });

    $app->group('/groups', function (Group $group) {
        $group->get('', ListGroupsAction::class);
        $group->get('/{id}', ViewGroupAction::class);
        $group->post('', AddGroupAction::class);
        $group->delete('/{id}', RemoveGroupAction::class);
    });
};
