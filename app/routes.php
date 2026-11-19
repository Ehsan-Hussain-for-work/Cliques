<?php

declare(strict_types=1);

use App\Application\Middleware\AuthMiddleware;
use App\Application\Actions\Auth\LoginAction;
use App\Application\Actions\Auth\LogoutAction;
use App\Application\Actions\Auth\ViewAuthUserAction;
use App\Application\Actions\User\RemoveUserAction;
use App\Application\Actions\User\AddUserAction;
use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use App\Application\Actions\Group\RemoveGroupAction;
use App\Application\Actions\Group\AddGroupAction;
use App\Application\Actions\Group\ListGroupsAction;
use App\Application\Actions\Group\ViewGroupAction;
use App\Application\Actions\Channel\RemoveChannelAction;
use App\Application\Actions\Channel\AddChannelAction;
use App\Application\Actions\Channel\ListChannelsAction;
use App\Application\Actions\Channel\ViewChannelAction;
use App\Application\Actions\Message\RemoveMessageAction;
use App\Application\Actions\Message\AddMessageAction;
use App\Application\Actions\Message\ListMessagesAction;
use App\Application\Actions\Message\ViewMessageAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Application\Middleware\CollectionQueryMiddleware as CollectionQ;
use App\Application\Support\UserCollectionQuery;
use App\Application\Support\GroupCollectionQuery;
use App\Application\Support\ChannelCollectionQuery;
use App\Application\Support\MessageCollectionQuery;
use App\Application\Actions\User\UpdateUserAction;

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

    $app->post('/auth/login', LoginAction::class);
    $app->group('/auth', function (Group $group) {
        $group->post('/logout', LogoutAction::class);
        $group->get('/me', ViewAuthUserAction::class);
    })->add(AuthMiddleware::class);

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class)
            ->add(new CollectionQ(UserCollectionQuery::class));
        $group->get('/{id}', ViewUserAction::class);
        $group->post('', AddUserAction::class);
        $group->put('/{id}', UpdateUserAction::class);
        $group->delete('/{id}', RemoveUserAction::class);
    });

    $app->group('/groups', function (Group $group) {
        $group->get('', ListGroupsAction::class)
            ->add(new CollectionQ(GroupCollectionQuery::class));
        $group->get('/{id}', ViewGroupAction::class);
        $group->post('', AddGroupAction::class);
        $group->delete('/{id}', RemoveGroupAction::class);
    });

    $app->group('/channels', function (Group $group) {
        $group->get('', ListChannelsAction::class)
            ->add(new CollectionQ(ChannelCollectionQuery::class));
        $group->get('/{id}', ViewChannelAction::class);
        $group->post('', AddChannelAction::class);
        $group->delete('/{id}', RemoveChannelAction::class);
    });

    $app->group('/messages', function (Group $group) {
        $group->get('', ListMessagesAction::class)
            ->add(new CollectionQ(MessageCollectionQuery::class));
        $group->get('/{id}', ViewMessageAction::class);
        $group->post('', AddMessageAction::class);
        $group->delete('/{id}', RemoveMessageAction::class);
    });
};
