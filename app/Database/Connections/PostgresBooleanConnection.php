<?php

declare(strict_types=1);

namespace App\Database\Connections;

use DateTimeInterface;
use Illuminate\Database\PostgresConnection;

class PostgresBooleanConnection extends PostgresConnection
{
    /**
     * Bind PHP booleans as string literals instead of integers.
     *
     * The framework's base prepareBindings casts booleans to (int), which
     * MySQL and SQLite happily store into boolean columns but PostgreSQL
     * strict typing rejects with "column is of type boolean but expression
     * is of type integer". Postgres infers parameter types from the target
     * column, so the 'true'/'false' strings bind cleanly and no SQL has to
     * change anywhere in the application.
     */
    public function prepareBindings(array $bindings): array
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = $value ? 'true' : 'false';
            }
        }

        return $bindings;
    }
}
