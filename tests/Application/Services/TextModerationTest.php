<?php

declare(strict_types=1);

namespace Tests\Application\Services;

use App\Application\Services\TextModerationClient;
use App\Application\Services\TextModerationException;
use App\Application\Services\TextModerationResult;
use App\Domain\Channel\ChannelRepository;
use App\Domain\Message\MessageRepository;
use App\Infrastructure\External\ApiNinjasProfanityClient;
use DI\Container;
use Prophecy\Argument;
use Tests\TestCase;

final class StubTextModerationClient implements TextModerationClient
{
    private ?TextModerationResult $result;
    private ?TextModerationException $exception;

    public function __construct(?TextModerationResult $result = null, ?TextModerationException $exception = null)
    {
        $this->result = $result;
        $this->exception = $exception;
    }

    public function filter(string $text): TextModerationResult
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }

        return $this->result ?? new TextModerationResult($text, $text, false);
    }
}

class TextModerationTest extends TestCase
{
    public function testNormalizedModerationResult(): void
    {
        $result = new TextModerationResult('damn it!', '**** it!', true);

        $this->assertSame('damn it!', $result->original);
        $this->assertSame('**** it!', $result->censored);
        $this->assertTrue($result->hasProfanity);
    }

    public function testMissingApiKeyFailsClosed(): void
    {
        $client = new ApiNinjasProfanityClient('YOUR_API_NINJA_KEY', 2);

        $this->expectException(TextModerationException::class);
        $this->expectExceptionCode(503);

        $client->filter('test message');
    }

    public function testCleanMessageIsPersistedUnchanged(): void
    {
        $context = $this->createMessageContext(false, new StubTextModerationClient(
            new TextModerationResult('hello world', 'hello world', false)
        ), 'hello world');

        $this->assertSame(201, $context['response']->getStatusCode());
        $this->assertSame('clean', $context['payload']['moderation_status']);
        $this->assertSame('hello world', $context['payload']['body']);
        $this->assertSame('hello world', $context['payload']['original_body']);
        $this->assertSame('hello world', $context['payload']['censored_body']);
    }

    public function testProfaneMessageStoresOriginalAndCensoredText(): void
    {
        $context = $this->createMessageContext(false, new StubTextModerationClient(
            new TextModerationResult('damn it!', '**** it!', true)
        ), 'damn it!');

        $this->assertSame(201, $context['response']->getStatusCode());
        $this->assertSame('filtered', $context['payload']['moderation_status']);
        $this->assertSame('**** it!', $context['payload']['body']);
        $this->assertSame('damn it!', $context['payload']['original_body']);
        $this->assertSame('**** it!', $context['payload']['censored_body']);
    }

    public function testAdultChannelBypassesProvider(): void
    {
        $client = new StubTextModerationClient();
        $context = $this->createMessageContext(true, $client, 'damn it!');

        $this->assertSame(201, $context['response']->getStatusCode());
        $this->assertSame('bypassed', $context['payload']['moderation_status']);
        $this->assertSame('damn it!', $context['payload']['body']);
    }

    public function testOversizedMessageIsRejectedBeforeModeration(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();

        $channelRepository = $this->prophesize(ChannelRepository::class);
        $moderation = $this->prophesize(TextModerationClient::class);
        $moderation->filter(Argument::any())->shouldNotBeCalled();
        $messageRepository = $this->prophesize(MessageRepository::class);
        $messageRepository->addMessage(Argument::any())->shouldNotBeCalled();

        $container->set(ChannelRepository::class, $channelRepository->reveal());
        $container->set(TextModerationClient::class, $moderation->reveal());
        $container->set(MessageRepository::class, $messageRepository->reveal());

        $request = $this->createRequest(
            'POST',
            '/messages',
            ['HTTP_ACCEPT' => 'application/json', 'HTTP_CONTENT_TYPE' => 'application/json']
        )->withParsedBody([
            'body' => str_repeat('x', 1001),
            'channel_id' => 1,
            'sender_id' => 1,
            'sent_time' => '2026-11-26 12:00:00',
            'edited' => 0,
        ]);

        $response = $app->handle($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    /** @dataProvider providerFailureCases */
    public function testProviderFailureDoesNotPersist(int $statusCode, string $message): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();

        $channelRepository = $this->prophesize(ChannelRepository::class);
        $channelRepository->findIdChannel(1)->willReturn(['id' => 1, 'adult_gate' => 0]);

        $moderation = $this->prophesize(TextModerationClient::class);
        $moderation->filter('hello')->willThrow(new TextModerationException($message, $statusCode));

        $messageRepository = $this->prophesize(MessageRepository::class);
        $messageRepository->addMessage(Argument::any())->shouldNotBeCalled();

        $container->set(ChannelRepository::class, $channelRepository->reveal());
        $container->set(TextModerationClient::class, $moderation->reveal());
        $container->set(MessageRepository::class, $messageRepository->reveal());

        $request = $this->createRequest(
            'POST',
            '/messages',
            ['HTTP_ACCEPT' => 'application/json', 'HTTP_CONTENT_TYPE' => 'application/json']
        )->withParsedBody([
            'body' => 'hello',
            'channel_id' => 1,
            'sender_id' => 1,
            'sent_time' => '2026-11-26 12:00:00',
            'edited' => 0,
        ]);

        $response = $app->handle($request);

        $this->assertSame($statusCode, $response->getStatusCode());
    }

    public function providerFailureCases(): array
    {
        return [
            'quota' => [429, 'The profanity provider quota was exceeded.'],
            'malformed response' => [502, 'The profanity provider returned an invalid response.'],
            'unavailable' => [503, 'The profanity provider is unavailable.'],
        ];
    }

    private function createMessageContext(bool $adultGate, TextModerationClient $client, string $body): array
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();

        $channelRepository = $this->prophesize(ChannelRepository::class);
        $channelRepository->findIdChannel(1)->willReturn([
            'id' => 1,
            'adult_gate' => $adultGate ? 1 : 0,
        ]);

        $messageRepository = $this->prophesize(MessageRepository::class);
        $messageRepository->addMessage(Argument::that(static function (array $data): bool {
            return isset($data['original_body'], $data['censored_body'], $data['moderation_status']);
        }))->will(function (array $args) {
            return $args[0];
        })->shouldBeCalledOnce();

        $container->set(ChannelRepository::class, $channelRepository->reveal());
        $container->set(TextModerationClient::class, $client);
        $container->set(MessageRepository::class, $messageRepository->reveal());

        $request = $this->createRequest(
            'POST',
            '/messages',
            ['HTTP_ACCEPT' => 'application/json', 'HTTP_CONTENT_TYPE' => 'application/json']
        )->withParsedBody([
            'body' => $body,
            'channel_id' => 1,
            'sender_id' => 1,
            'sent_time' => '2026-11-26 12:00:00',
            'edited' => 0,
        ]);

        $response = $app->handle($request);
        $payload = json_decode((string) $response->getBody(), true);

        return [
            'response' => $response,
            'payload' => $payload,
        ];
    }
}
