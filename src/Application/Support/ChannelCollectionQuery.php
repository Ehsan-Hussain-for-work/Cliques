<?php

declare(strict_types=1);

namespace App\Application\Support;

use Override;

final class ChannelCollectionQuery extends CollectionQuery
{
    protected const FIELDS = [
        'id' => 'id',
        'name' => 'name',
        'group_id' => 'group_id',
        'adult_gate' => 'adult_gate'
    ];

    #[Override]
    protected static function getTableName(): string
    {
        return 'channels';
    }

    #[Override]
    protected static function getSelectColumns(): string
    {
        return 'id, name, group_id, adult_gate';
    }

    #[Override]
    protected static function getBasePath(): string
    {
        return '/channels';
    }

    protected static function getAllowedFilters(): array
    {
        return ['name', 'group_id', 'adult_gate'];
    }

    protected static function applyFilters(array $input, array &$q): void
    {
        if (isset($input['name'])) {
            $q['name'] = $input['name'];
        }
        if (isset($input['group_id'])) {
            $q['group_id'] = $input['group_id'];
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
        if (isset($q['group_id'])) {
            $where[] = 'group_id = :group_id';
            $params['group_id'] = $q['group_id'];
        }
        if (isset($q['adult_gate'])) {
            $where[] = 'adult_gate = :adult_gate';
            $params['adult_gate'] = $q['adult_gate'];
        }
    }
}
