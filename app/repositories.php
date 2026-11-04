<?php

declare(strict_types=1);

use App\Domain\User\UserRepository;
use App\Domain\Group\GroupRepository;
use App\Domain\Channel\ChannelRepository;
use App\Infrastructure\Persistence\User\UserRepo;
use App\Infrastructure\Persistence\Group\GroupRepo;
use App\Infrastructure\Persistence\Channel\ChannelRepo;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // Here we map our UserRepository interface to its implementation
    $containerBuilder->addDefinitions([
        UserRepository::class => \DI\autowire(UserRepo::class),
        GroupRepository::class => \DI\autowire(GroupRepo::class),
        ChannelRepository::class => \DI\autowire(ChannelRepo::class),
    ]);
};
