<?php

namespace App\Repositories;

use App\Contracts\Repositories\FreelancerServiceRepositoryInterface;
use App\Models\FreelancerService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class FreelancerServiceRepository implements FreelancerServiceRepositoryInterface
{
    public function __construct(
        private readonly FreelancerService $freelancerService,
    ) {
    }

    public function add(array $data): string|object
    {
        return $this->freelancerService->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->freelancerService->with($relations)->where($params)->first();
    }

    public function getBySellerId(string|int $sellerId, array $relations = []): Collection
    {
        return $this->freelancerService->with($relations)
            ->where('seller_id', $sellerId)
            ->orderBy('priority')
            ->get();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->freelancerService->with($relations)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                foreach ($orderBy as $column => $direction) {
                    $query->orderBy($column, $direction);
                }
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->freelancerService->with($relations)
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->where('title', 'like', "%$searchValue%");
            })
            ->when(isset($filters['freelancer_profile_id']), fn ($query) => $query->where('freelancer_profile_id', $filters['freelancer_profile_id']))
            ->when(isset($filters['freelancer_category_id']) && $filters['freelancer_category_id'] !== '', fn ($query) => $query->where('freelancer_category_id', $filters['freelancer_category_id']))
            ->when(isset($filters['freelancer_specialization_id']) && $filters['freelancer_specialization_id'] !== '', fn ($query) => $query->where('freelancer_specialization_id', $filters['freelancer_specialization_id']))
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', fn ($query) => $query->where('is_active', $filters['is_active']))
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
        return (bool)$this->freelancerService->find($id)?->update($data);
    }

    public function updateActiveStatus(string|int $id, int $status): bool
    {
        return (bool)$this->freelancerService->find($id)?->update(['is_active' => $status]);
    }

    public function delete(array $params): bool
    {
        return (bool)$this->freelancerService->where($params)->delete();
    }
}
