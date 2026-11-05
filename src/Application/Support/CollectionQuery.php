<?php

declare(strict_types=1);

namespace App\Application\Support;

use InvalidArgumentException;

abstract class CollectionQuery
{
    // Force child classes to define their specific fields and table
    protected const FIELDS = [];
    abstract protected static function getTableName(): string;
    abstract protected static function getSelectColumns(): string;
    abstract protected static function getBasePath(): string;

    protected static function getAllowedFilters(): array
    {
        return [];
    }
    protected static function applyFilters(array $input, array &$q): void {}
    protected static function buildFilters(array $q, array &$where, array &$params): void {}

    public static function parse(string $raw): array
    {
        if (\strlen($raw) > 2048) {
            throw new InvalidArgumentException('Query exceeds 2048 bytes.');
        }
        $allowedParams = array_merge(['page', 'limit', 'sort'], static::getAllowedFilters());
        $input = [];
        foreach ($raw === '' ? [] : explode('&', $raw) as $part) {
            if ($part === '' || preg_match('/%(?![0-9A-Fa-f]{2})/', $part)) {
                throw new InvalidArgumentException('Malformed query encoding.');
            }
            [$key, $value] = array_pad(explode('=', $part, 2), 2, '');
            $key = urldecode($key);
            $value = urldecode($value);

            if (!\in_array($key, $allowedParams, true)) {
                throw new InvalidArgumentException("Unknown query parameter. $key");
            }
            if (\array_key_exists($key, $input)) {
                throw new InvalidArgumentException('Each query parameter may occur once.');
            }
            if ($value === '' || preg_match('//u', $value) !== 1) {
                throw new InvalidArgumentException('Empty or invalid UTF-8 query value.');
            }
            $input[$key] = $value;
        }
        $q = [];
        static::applyFilters($input, $q);
        $q['sort'] = static::sort($input['sort'] ?? 'id');
        $q['page'] = static::integer($input['page'] ?? '1', 1, 10000, 'page');
        $q['limit'] = static::integer($input['limit'] ?? '10', 1, 100, 'limit');
        return $q;
    }

    public static function integer(string $raw, int $min, int $max, string $name): int
    {
        if (!preg_match('/^(0|[1-9][0-9]*)$/D', $raw)) {
            throw new InvalidArgumentException("$name must be a canonical non-negative integer.");
        }
        if (\strlen($raw) > \strlen((string) $max)) {
            throw new InvalidArgumentException("$name is outside the allowed range.");
        }
        $value = (int) $raw;
        if ($value < $min || $value > $max) {
            throw new InvalidArgumentException("$name is outside the allowed range.");
        }
        return $value;
    }

    public static function link(array $q, int $page): string
    {
        $q['page'] = $page;
        return static::getBasePath() . '?' . http_build_query($q, '', '&', PHP_QUERY_RFC3986);
    }


    public static function sort(string $raw): string
    {
        // Change self::FIELDS to static::FIELDS
        $fields = static::FIELDS;
        $tokens = explode(',', $raw);
        if (\count($tokens) > 3) {
            throw new InvalidArgumentException('Use at most three sort keys.');
        }
        $seen = [];
        foreach ($tokens as $token) {
            $field = str_starts_with($token, '-') ? substr($token, 1) : $token;
            if (!isset($fields[$field]) || isset($seen[$field])) {
                throw new InvalidArgumentException('Unknown, empty, or repeated sort key.');
            }
            $seen[$field] = true;
        }
        // Complete a deterministic total order over an unchanged result set.
        if (!isset($seen['id'])) $tokens[] = 'id';
        return implode(',', $tokens);
    }

    public static function plan(array $q): array
    {
        $where = [];
        $params = [];
        static::buildFilters($q, $where, $params);
        $from = ' FROM ' . static::getTableName();
        $predicate = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $select = 'SELECT ' . static::getSelectColumns();

        $sql = $select . $from . $predicate . ' ORDER BY ' . static::orderBy($q['sort'])
            . ' LIMIT :take OFFSET :skip';

        return [
            'sql' => $sql,
            'countSql' => 'SELECT COUNT(*)' . $from . $predicate,
            'filters' => $params,
            'params' => $params + [
                'take' => $q['limit'],
                'skip' => ($q['page'] - 1) * $q['limit'],
            ],
        ];
    }
    public static function envelope(array $items, array $q, int $total): array
    {
        $pages = (int) ceil($total / $q['limit']);
        return [
            'items' => $items,
            'page' => [
                'number' => $q['page'],
                'perPage' => $q['limit'],
                'returned' => \count($items),
                'totalItems' => $total,
                'totalPages' => $pages,
                ...($q['page'] === 10000 && $pages > 10000
                    ? ['traversalLimited' => true] : [])
            ],
            'links' => [
                'self' => static::link($q, $q['page']),
                'next' => $q['page'] < $pages && $q['page'] < 10000
                    ? static::link($q, $q['page'] + 1) : null,
                'prev' => $q['page'] > 1 && $pages > 0
                    ? static::link($q, min($q['page'] - 1, $pages)) : null,
            ],
        ];
    }
    public static function orderBy(string $sort): string
    {
        $pieces = [];
        foreach (explode(',', static::sort($sort)) as $token) {
            $descending = str_starts_with($token, '-');
            $field = $descending ? substr($token, 1) : $token;
            $pieces[] = static::FIELDS[$field] . ($descending ? ' DESC' : ' ASC');
        }
        return implode(', ', $pieces);
    }
}
