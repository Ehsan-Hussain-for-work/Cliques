<?php

declare(strict_types=1);

namespace Tests\Application\Actions\User;

use App\Domain\User\UserRepository;
use DI\Container;
use Prophecy\Argument;
use Tests\TestCase;

class ListUserActionTest extends TestCase
{
    public function testAction(): void
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $envelope = [
            'items' => [
                [
                    'id' => 1,
                    'username' => 'bill.gates',
                ],
            ],
            'page' => [
                'number' => 1,
                'perPage' => 5,
                'returned' => 1,
                'totalItems' => 1,
                'totalPages' => 1,
            ],
            'links' => [
                'self' => '/users?page=1&limit=5',
                'next' => null,
                'prev' => null,
            ],
        ];

        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->page(Argument::type('array'))
            ->willReturn($envelope)
            ->shouldBeCalledOnce();

        $container->set(UserRepository::class, $userRepositoryProphecy->reveal());

        $request = $this->createRequest('GET', '/users');
        $response = $app->handle($request);

        $payload = json_decode((string) $response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($envelope, $payload);
        $this->assertArrayHasKey('items', $payload);
        $this->assertArrayHasKey('page', $payload);
        $this->assertArrayHasKey('links', $payload);
    }
}
