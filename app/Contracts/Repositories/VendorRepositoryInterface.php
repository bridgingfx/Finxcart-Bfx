<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface VendorRepositoryInterface extends RepositoryInterface
{
    /**
     * @param string $status
     * @param array $relations
     * @param int|string $paginateBy
     * @param array $select
     * @return Collection|array|LengthAwarePaginator
     */
    public function getByStatusExcept(string $status, array $relations = [], int|string $paginateBy = DEFAULT_DATA_LIMIT, array $select = []): Collection|array|LengthAwarePaginator;
}
