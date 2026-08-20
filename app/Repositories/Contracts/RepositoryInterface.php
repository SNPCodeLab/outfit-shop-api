<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Optional data-access contract.
 *
 * Domain logic stays in App\Services. Repositories isolate Eloquent queries
 * when the same lookups are reused across services or become hard to test.
 */
interface RepositoryInterface
{
    public function find(int $id): ?Model;

    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Model;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(int $id, array $attributes): ?Model;

    public function delete(int $id): bool;
}
