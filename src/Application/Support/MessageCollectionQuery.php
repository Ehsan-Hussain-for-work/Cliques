<?php

declare(strict_types=1);

namespace App\Application\Support;

use Override;

final class MessageCollectionQuery extends CollectionQuery
{
    protected const FIELDS = [
        'id' => 'id',
        'body' => 'body',
        'sent_time' => 'sent_time',
        'sender_id' => 'sender_id',
        'channel_id' => 'channel_id',
        'edited' => 'edited'
    ];

    #[Override]
    protected static function getTableName(): string
    {
        return 'messages';
    }

    #[Override]
    protected static function getSelectColumns(): string
    {
        return 'id, body, sent_time, sender_id, channel_id, edited';
    }

    #[Override]
    protected static function getBasePath(): string
    {
        return '/messages';
    }

    protected static function getAllowedFilters(): array
    {
        return ['sent_time', 'sender_id', 'channel_id'];
    }

    protected static function applyFilters(array $input, array &$q): void
    {
        if (isset($input['sent_time'])) {
            $q['sent_time'] = $input['sent_time'];
        }
        if (isset($input['sender_id'])) {
            $q['sender_id'] = $input['sender_id'];
        }
        if (isset($input['channel_id'])) {
            $q['channel_id'] = $input['channel_id'];
        }
    }
    protected static function buildFilters(array $q, array &$where, array &$params): void
    {
        if (isset($q['sent_time'])) {
            $where[] = 'sent_time = :sent_time';
            $params['sent_time'] = $q['sent_time'];
        }
        if (isset($q['sender_id'])) {
            $where[] = 'sender_id = :sender_id';
            $params['sender_id'] = $q['sender_id'];
        }
        if (isset($q['channel_id'])) {
            $where[] = 'channel_id = :channel_id';
            $params['channel_id'] = $q['channel_id'];
        }
    }
}
