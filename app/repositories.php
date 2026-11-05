<?php

declare(strict_types=1);

use App\Domain\User\UserRepository;
use App\Domain\Group\GroupRepository;
use App\Domain\Channel\ChannelRepository;
use App\Infrastructure\Persistence\User\UserModel;
use App\Infrastructure\Persistence\Group\GroupModel;
use App\Infrastructure\Persistence\Channel\ChannelModel;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // Here we map our UserRepository interface to its implementation
    $containerBuilder->addDefinitions([
        UserRepository::class => \DI\autowire(UserModel::class),
        GroupRepository::class => \DI\autowire(GroupModel::class),
        ChannelRepository::class => \DI\autowire(ChannelModel::class),
    ]);
};
