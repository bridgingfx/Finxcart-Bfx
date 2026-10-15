<?php

namespace App\Repositories;

use App\Contracts\Repositories\SellerBankRepositoryInterface;
use App\Models\SellerBank;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SellerBankRepository implements SellerBankRepositoryInterface
{
    public function __construct(private readonly SellerBank $sellerBank)
    {
    }

    public function add(array $data): string|object
    {
        return $this->sellerBank->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->sellerBank->with($relations)->where($params)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->sellerBank->with($relations)->when(!empty($orderBy), function ($query) use ($orderBy) {
            $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
        });
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->sellerBank->where($filters)->with($relations)
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->where('bank_name', 'LIKE', "%$searchValue%");
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        $this->sellerBank->where(['id' => $id])->update($data);
        return true;
    }

    public function delete(array $params): bool
    {
        $this->sellerBank->where($params)->delete();
        return true;
    }

    public function setActive(int $sellerId, int $bankId): bool
    {
        return DB::transaction(function () use ($sellerId, $bankId) {
            $target = $this->sellerBank->where(['id' => $bankId, 'seller_id' => $sellerId])->first();
            if (!$target) {
                return false;
            }

            $this->sellerBank->where('seller_id', $sellerId)->update(['is_active' => false]);
            $target->is_active = true;
            $target->save();

            return true;
        });
    }
}
