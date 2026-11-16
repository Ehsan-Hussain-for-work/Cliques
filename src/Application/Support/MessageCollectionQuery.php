<?php

declare(strict_types=1);

namespace App\Application\Support;

use Override;

final class GroupCollectionQuery extends CollectionQuery
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
        return ['name', 'adult_gate']; //TODO this line and going down
    }

    protected static function applyFilters(array $input, array &$q): void
    {
        if (isset($input['name'])) {
            $q['name'] = $input['name'];
        }
        if (isset($input['adult_gate'])) {
            $q['adult_gate'] = $input['adult_gate'];
        }
    }
    protected static function buildFilters(array $q, array &$where, array &$params): void
    {
        if (isset($q['name'])) {
            $where[] = 'name = :name';
            $params['name'] = $q['name'];
        }
        if (isset($q['adult_gate'])) {
            $where[] = 'adult_gate = :adult_gate';
            $params['adult_gate'] = $q['adult_gate'];
        }
    }
}
