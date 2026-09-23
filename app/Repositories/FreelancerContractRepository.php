<?php

namespace App\Repositories;

use App\Contracts\Repositories\FreelancerContractRepositoryInterface;
use App\Models\FreelancerContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class FreelancerContractRepository implements FreelancerContractRepositoryInterface
{
    public function __construct(
        private readonly FreelancerContract $contract,
    ) {
    }

    public function add(array $data): string|object
    {
        return $this->contract->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->contract->with($relations)->where($params)->first();
    }

    public function getByCustomerId(int $customerId, array $relations = []): Collection
    {
        return $this->contract->with($relations)
            ->where('customer_id', $customerId)
            ->latest()
            ->get();
    }

    public function getBySellerId(int $sellerId, array $relations = []): Collection
    {
        return $this->contract->with($relations)
            ->where('seller_id', $sellerId)
            ->latest()
            ->get();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->contract->with($relations)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                foreach ($orderBy as $column => $direction) {
                    $query->orderBy($column, $direction);
                }
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->contract->with($relations)
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->where('scope', 'like', "%$searchValue%");
            })
            ->when(isset($filters['status']) && $filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['cancellation_status']) && $filters['cancellation_status'] !== '', fn ($query) => $query->where('cancellation_status', $filters['cancellation_status']))
            ->when(isset($filters['customer_id']), fn ($query) => $query->where('customer_id', $filters['customer_id']))
            ->when(isset($filters['seller_id']), fn ($query) => $query->where('seller_id', $filters['seller_id']))
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                foreach ($orderBy as $column => $direction) {
                    $query->orderBy($column, $direction);
                }
            }, fn ($query) => $query->latest());

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        return (bool)$this->contract->find($id)?->update($data);
    }

    public function delete(array $params): bool
    {
        return (bool)$this->contract->where($params)->delete();
    }
}
