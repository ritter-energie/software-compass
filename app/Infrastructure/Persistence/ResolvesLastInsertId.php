<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use Tempest\Database\Database;
use Tempest\Database\PrimaryKey;

use function Tempest\get;

/**
 * Works around a quirk of Tempest's query builder: `query('table_name')->insert(...)->execute()`
 * only returns a {@see PrimaryKey} when the query builder knows the primary
 * key *column name* up front, which requires a reflected model class.
 *
 * Since these repositories intentionally use plain table-name queries (see
 * query builder-based repositories instead of model classes, `execute()` always
 * returns `null` after an insert. We work around this by reading back
 * MariaDB's `LAST_INSERT_ID()` directly from the database connection.
 */
trait ResolvesLastInsertId
{
    private function lastInsertId(): int
    {
        $id = get(Database::class)->getLastInsertId();

        return $id instanceof PrimaryKey ? (int) $id->value : (int) $id;
    }
}

