<?php

namespace App\Repositories;

use App\Contracts\Repositories\CategoryShippingCostRepositoryInterface;
use App\Models\CategoryShippingCost;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryShippingCostRepository implements CategoryShippingCostRepositoryInterface
{
    public function __construct(
        private readonly CategoryShippingCost    $categoryShippingCost
    )
    {
    }
    public function add(array $data): string|object
    {
        return $this->categoryShippingCost->newInstance()->create($data);
    }

    /**
     * Bulk-insert multiple rows in a single query. Used by the admin shipping-method
     * index page to backfill missing category_shipping_costs rows without looping
     * one INSERT per category on every page load.
     * @param array $data array of row arrays (each missing created_at/updated_at will get them filled in)
     */
    public function insert(array $data): bool
    {
        if (empty($data)) {
            return true;
        }
        $now = now();
        $data = array_map(function ($row) use ($now) {
            $row['created_at'] = $row['created_at'] ?? $now;
            $row['updated_at'] = $row['updated_at'] ?? $now;
            return $row;
        }, $data);
        return $this->categoryShippingCost->newInstance()->newQuery()->insert($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->categoryShippingCost->with($relations)->where($params)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        // TODO: Implement getList() method.
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
       return $this->categoryShippingCost->with($relations)
           ->when(isset($searchValue), function ($query) use ($searchValue) {
               return $query->whereHas('category', function ($query) use ($searchValue){
                   $query->where('name', 'like', "%$searchValue%");
               });
           })
           ->where($filters)->get();
    }

    public function update(string $id, array $data): bool
    {
        return $this->categoryShippingCost->find($id)->update($data);
    }

    public function updateOrInsert(array $params, array $data): bool
    {
        $this->categoryShippingCost->updateOrInsert($params, $data);
        return true;
    }

    public function delete(array $params): bool
    {
        // TODO: Implement delete() method.
    }
}
