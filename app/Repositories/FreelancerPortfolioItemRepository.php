<?php

namespace App\Repositories;

use App\Contracts\Repositories\FreelancerPortfolioItemRepositoryInterface;
use App\Models\FreelancerPortfolioItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class FreelancerPortfolioItemRepository implements FreelancerPortfolioItemRepositoryInterface
{
    public function __construct(
        private readonly FreelancerPortfolioItem $freelancerPortfolioItem,
    ) {
    }

    public function add(array $data): string|object
    {
        return $this->freelancerPortfolioItem->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->freelancerPortfolioItem->with($relations)->where($params)->first();
    }

    public function getBySellerId(string|int $sellerId, array $relations = []): Collection
    {
        return $this->freelancerPortfolioItem->with($relations)
            ->where('seller_id', $sellerId)
            ->orderBy('priority')
            ->get();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->freelancerPortfolioItem->with($relations)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                foreach ($orderBy as $column => $direction) {
                    $query->orderBy($column, $direction);
                }
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->freelancerPortfolioItem->with($relations)
            ->when($searchValue, fn ($query) => $query->where('title', 'like', "%$searchValue%"))
            ->when(isset($filters['freelancer_profile_id']), fn ($query) => $query->where('freelancer_profile_id', $filters['freelancer_profile_id']))
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                foreach ($orderBy as $column => $direction) {
                    $query->orderBy($column, $direction);
                }
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        return (bool)$this->freelancerPortfolioItem->find($id)?->update($data);
    }

    public function delete(array $params): bool
    {
        return (bool)$this->freelancerPortfolioItem->where($params)->delete();
    }
}
