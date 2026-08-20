<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class EloquentRepository implements RepositoryInterface
{
    public function __construct(protected Model $model) {}

    public function find(int $id): ?Model
    {
        return $this->model->newQuery()->find($id);
    }

    public function all(): Collection
    {
        return $this->model->newQuery()->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()->paginate($perPage);
    }

    public function create(array $attributes): Model
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function update(int $id, array $attributes): ?Model
    {
        $record = $this->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($attributes);

        return $record->fresh();
    }

    public function delete(int $id): bool
    {
        $record = $this->find($id);

        if ($record === null) {
            return false;
        }

        return (bool) $record->delete();
    }
}
