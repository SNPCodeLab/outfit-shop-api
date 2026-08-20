<?php

declare(strict_types=1);

namespace App\Models\Concerns;

/**
 * Neon runs PgBouncer with PDO::ATTR_EMULATE_PREPARES = true.
 * This causes PHP boolean true/false to be sent as integers (1/0),
 * which PostgreSQL rejects for boolean columns with:
 *   SQLSTATE[42804]: column "x" is of type boolean but expression is of type integer
 *
 * This trait overrides setAttribute() to convert boolean casts to the string
 * literals 'true'/'false' that PostgreSQL accepts regardless of PDO emulation mode.
 */
trait CastsBooleanForPostgres
{
    public function setAttribute($key, $value): mixed
    {
        if (isset($this->casts[$key]) && $this->casts[$key] === 'boolean') {
            $this->attributes[$key] = $value ? 'true' : 'false';

            return $this;
        }

        return parent::setAttribute($key, $value);
    }
}
