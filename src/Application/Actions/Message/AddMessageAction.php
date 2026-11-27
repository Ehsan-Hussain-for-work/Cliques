<?php

declare(strict_types=1);

namespace App\Application\Actions\Message;

use App\Application\Actions\Message\MessageAction;
use App\Application\Services\TextModerationClient;
use App\Application\Services\TextModerationException;
use App\Domain\Channel\ChannelRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException as BadReq;
use Slim\Exception\HttpException;

class AddMessageAction extends MessageAction
{
    private ChannelRepository $channelRepository;
    private TextModerationClient $textModerationClient;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \App\Domain\Message\MessageRepository $messageRepository,
        ChannelRepository $channelRepository,
        TextModerationClient $textModerationClient
    ) {
        parent::__construct($logger, $messageRepository);
        $this->channelRepository = $channelRepository;
        $this->textModerationClient = $textModerationClient;
    }

    protected function action(): Response
    {
        $addRequest = $this->getFormData();

        // 1. Get authenticated user's information
        $authUser = $this->request->getAttribute('authUser');
        $addRequest['sent_time'] = gmdate('Y-m-d H:i:s', time());
        $addRequest['sender_id'] = (int) $authUser['id'];
        $addRequest['edited'] = 0;
        if (empty($addRequest) || !\is_array($addRequest)) {
            throw new BadReq($this->request, 'Invalid or missing request body. Please send application/json.');
        }

        if (
            !isset($addRequest['body'], $addRequest['channel_id'])
            || !is_string($addRequest['body'])
            || trim($addRequest['body']) === ''
        ) {
            throw new BadReq($this->request, 'body and channel_id are required.');
        }

        $body = $addRequest['body'];
        if (strlen($body) > 1000) {
            throw new BadReq($this->request, 'Message text exceeds the 1000 character provider limit.');
        }

        $channelId = (int) $addRequest['channel_id'];
        $channel = $this->channelRepository->findIdChannel($channelId);
        $adultGate = (bool) (int) ($channel['adult_gate'] ?? 0);

        $originalBody = $body;
        $censoredBody = $body;
        $moderationStatus = 'bypassed';

        if (!$adultGate) {
            try {
                $result = $this->textModerationClient->filter($body);
                $originalBody = $result->original;
                $censoredBody = $result->censored;
                $moderationStatus = $result->hasProfanity ? 'filtered' : 'clean';
            } catch (TextModerationException $e) {
                throw new HttpException(
                    $this->request,
                    $e->getMessage(),
                    $e->getStatusCode(),
                    $e
                );
            }
        }

        $addRequest['body'] = $adultGate || $moderationStatus === 'clean'
            ? $originalBody
            : $censoredBody;
        $addRequest['original_body'] = $originalBody;
        $addRequest['censored_body'] = $censoredBody;
        $addRequest['moderation_status'] = $moderationStatus;

        $message = $this->messageRepository->addMessage($addRequest);

        return $this->respondWithData($message, 201);
    }
}
