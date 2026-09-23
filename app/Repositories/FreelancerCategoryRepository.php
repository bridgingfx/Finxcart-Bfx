<?php

namespace App\Repositories;

use App\Contracts\Repositories\FreelancerCategoryRepositoryInterface;
use App\Models\FreelancerCategory;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class FreelancerCategoryRepository implements FreelancerCategoryRepositoryInterface
{
    public function __construct(
        private readonly FreelancerCategory $freelancerCategory,
        private readonly Translation $translation,
    ) {
    }

    public function add(array $data): string|object
    {
        return $this->freelancerCategory->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->freelancerCategory
            ->withoutGlobalScope('translate')
            ->with($relations)
            ->where($params)
            ->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->freelancerCategory->with($relations)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                foreach ($orderBy as $column => $direction) {
                    $query->orderBy($column, $direction);
                }
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $visibleToSellerId = $filters['visible_to_seller_id'] ?? null;
        unset($filters['visible_to_seller_id']);

        $query = $this->freelancerCategory
            ->with($relations)
            ->where($filters)
            ->when($visibleToSellerId, function ($query) use ($visibleToSellerId) {
                $query->where(function ($query) use ($visibleToSellerId) {
                    $query->whereNull('seller_id')->orWhere('seller_id', $visibleToSellerId);
                });
            })
            ->when($searchValue, function ($query) use ($searchValue) {
                $translationIds = $this->translation
                    ->where('translationable_type', FreelancerCategory::class)
                    ->where('key', 'name')
                    ->where('value', 'like', "%$searchValue%")
                    ->pluck('translationable_id');

                $query->where(function ($query) use ($searchValue, $translationIds) {
                    $query->where('name', 'like', "%$searchValue%")
                        ->orWhereIn('id', $translationIds);
                });
            })
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
        return (bool)$this->freelancerCategory->withoutGlobalScope('translate')->find($id)?->update($data);
    }

    public function updateStatus(string|int $id, int $status): bool
    {
        return (bool)$this->freelancerCategory->withoutGlobalScope('translate')->find($id)?->update([
            'is_active' => $status,
        ]);
    }

    public function hasChildren(string|int $id): bool
    {
        return $this->freelancerCategory->withoutGlobalScope('translate')->where('parent_id', $id)->exists();
    }

    public function delete(array $params): bool
    {
        return (bool)$this->freelancerCategory->withoutGlobalScope('translate')->where($params)->delete();
    }
}
