<?php

declare(strict_types=1);

namespace App\Application\Support;

use Override;

final class GroupCollectionQuery extends CollectionQuery
{
    protected const FIELDS = [
        'id' => 'id',
        'name' => 'name',
        'creation_date' => 'creation_date',
        'adult_gate' => 'adult_gate'
    ];

    #[Override]
    protected static function getTableName(): string
    {
        return 'groups';
    }

    #[Override]
    protected static function getSelectColumns(): string
    {
        return 'id, name, creation_date, adult_gate';
    }

    #[Override]
    protected static function getBasePath(): string
    {
        return '/groups';
    }

    protected static function getAllowedFilters(): array
    {
        return ['name', 'adult_gate'];
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
