<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface extends RepositoryInterface
{

    /**
     * @param array $params
     * @param array $filters
     * @param array|string|null $relations
     * @param int|string $dataLimit
     * @param int|null $offset
     * @return Collection|LengthAwarePaginator
     */
    public function getListWhereBetween(array $params = [], array $filters = [], array|string $relations = null, int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator;

    /**
     * Same filters as getListWhereBetween(), but returns a DB-level COUNT instead of
     * hydrating every matching row just to count them in PHP.
     * @param array $params
     * @param array $filters
     * @param array|string|null $relations
     * @return int
     */
    public function countWhereBetween(array $params = [], array $filters = [], array|string $relations = null): int;

}
