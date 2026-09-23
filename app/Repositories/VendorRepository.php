<?php

namespace App\Repositories;

use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Models\DeliveryZipCode;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class VendorRepository implements VendorRepositoryInterface
{
    public function __construct(
        private readonly Seller $vendor,
    )
    {
    }

    public function getByStatusExcept(string $status, array $relations = [], int|string $paginateBy = DEFAULT_DATA_LIMIT, array $select = []): Collection|array|LengthAwarePaginator
    {
        $query = $this->vendor->with($relations)
            ->when(!empty($select), function ($query) use ($select) {
                $query->select($select);
            })
            ->whereNotIn('status', [$status]);

        return $paginateBy === 'all' ? $query->get() : $query->paginate($paginateBy);
    }


    public function add(array $data): string|object
    {
        return $this->vendor->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->vendor->with($relations)
            ->when(isset($params['identity']),function ($query) use ($params){
                return $query->where(function ($q) use ($params) {
                    $q->where(['email' => $params['identity']])
                        ->orWhere(['phone' => $params['identity']]);
                });
            })
            // Vendor and Freelancer accounts can share an email/phone (they're
            // separate account types living in the same `sellers` table, scoped by
            // seller_type), so an identity lookup alone is ambiguous. Callers that
            // know which panel they're in (login/password-reset) must pass this so
            // the right account is returned instead of an arbitrary match.
            ->when(array_key_exists('seller_type', $params),function ($query) use ($params){
                return is_null($params['seller_type'])
                    ? $query->whereNull('seller_type')
                    : $query->where('seller_type', $params['seller_type']);
            })
            // Vendor login/panel accounts are every seller_type except 'freelancer'
            // (NULL for self-registered, 'company'/'individual' for admin-created) —
            // this excludes a same-email Freelancer row without needing an exact match.
            ->when(isset($params['excludeSellerType']),function ($query) use ($params){
                return $query->where(function ($q) use ($params) {
                    $q->whereNull('seller_type')->orWhere('seller_type', '!=', $params['excludeSellerType']);
                });
            })
            ->when(isset($params['id']),function ($query) use ($params){
                return $query->where(['id' => $params['id']]);
            })
            ->when(isset($params['withCount']),function ($query)use($params){
                return $query->withCount($params['withCount']);
            })
            ->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->vendor->with($relations)->when(!empty($orderBy), function ($query) use ($orderBy) {
            $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
        });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy=[], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null, array $select = []):  Collection|LengthAwarePaginator
    {
        // 'product' / 'orders' / 'orders_filtered' are count-only markers here — callers that
        // only need a count (not the full collection) should use these instead of eager-loading
        // every related row just to call ->count() on it in PHP.
        $countOnlyRelations = ['product', 'orders', 'orders_filtered'];
        $eagerLoadRelations = array_values(array_diff($relations, $countOnlyRelations));

        $excludeSellerType = $filters['exclude_seller_type'] ?? null;
        unset($filters['exclude_seller_type']);

        $query = $this->vendor->with($eagerLoadRelations)
            ->when(!empty($select), function ($query) use ($select) {
                $query->select($select);
            })
            ->where($filters)
            ->when($excludeSellerType, function ($query) use ($excludeSellerType) {
                $query->where('seller_type', '!=', $excludeSellerType);
            })
            ->when($searchValue, function ($query) use ($searchValue) {
                $searchTerms = explode(' ', $searchValue);
                $query->where(function ($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $query->orWhere('f_name', 'like', "%$term%")
                            ->orWhere('l_name', 'like', "%$term%")
                            ->orWhere('phone', 'like', "%$term%")
                            ->orWhere('email', 'like', "%$term%")
                            ->orWhereHas('shop', function ($query) use ($term) {
                                $query->where('name', 'like', "%$term%");
                            });
                    }
                });
            })
            ->when(in_array('product', $relations, true), function ($query) {
                $query->withCount('product');
            })
            ->when(in_array('orders', $relations, true), function ($query) {
                $query->withCount('orders');
            })
            ->when(in_array('orders_filtered', $relations, true), function ($query) {
                $query->withCount(['orders as orders_filtered_count' => function ($q) {
                    $q->where('seller_is', 'seller')->where('order_type', 'default_type');
                }]);
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
            });

        $filters += ['searchValue' =>$searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        return $this->vendor->find($id)->update($data);
    }

    public function delete(array $params): bool
    {
        $this->vendor->where($params)->delete();
        return true;
    }
}
