<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Cart;
use App\Models\DealOfTheDay;
use App\Models\FlashDealProduct;
use App\Models\Product;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\Wishlist;
use App\Traits\CacheManagerTrait;
use App\Traits\ProductTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
    use ProductTrait, CacheManagerTrait;

    public function __construct(
        private readonly Product          $product,
        private readonly Translation      $translation,
        private readonly Tag              $tag,
        private readonly Cart             $cart,
        private readonly Wishlist         $wishlist,
        private readonly FlashDealProduct $flashDealProduct,
        private readonly DealOfTheDay     $dealOfTheDay,
    )
    {
    }

    public function addRelatedTags(object $request, object $product): void
    {
        $tagIds = [];
        if ($request->tags != null) {
            $tagNames = collect(explode(",", $request->tags))
                ->map(fn ($value) => trim($value))
                ->filter()
                ->unique()
                ->values();

            if ($tagNames->isNotEmpty()) {
                // Batched: one SELECT for existing tags, one bulk INSERT for the rest,
                // instead of a firstOrNew()+save() pair (2 queries) per tag.
                $existingTags = $this->tag->whereIn('tag', $tagNames)->get()->keyBy('tag');
                $newTagNames = $tagNames->diff($existingTags->keys());

                if ($newTagNames->isNotEmpty()) {
                    $now = now();
                    $this->tag->insert(
                        $newTagNames->map(fn ($name) => [
                            'tag' => $name,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ])->all()
                    );
                    // insert() bypasses Eloquent events, so the model's saved-event
                    // cache invalidation (Tag::boot()) never fires — do it here instead.
                    cacheRemoveByType(type: 'tags');
                    $existingTags = $this->tag->whereIn('tag', $tagNames)->get()->keyBy('tag');
                }

                $tagIds = $existingTags->pluck('id')->all();
            }
        }
        $product->tags()->sync($tagIds);
    }

    public function addRelatedCategories(object $request, object $product): void
    {
        $categoryIds = $request['categories'] ?? [];
        $product->categories()->sync($categoryIds);
    }

    public function add(array $data): string|object
    {
        cacheRemoveByType(type: 'products');
        return $this->product->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->product->where($params)->with($relations)->first();
    }

    public function getFirstWhereWithCount(array $params, array $withCount = [], array $relations = []): ?Model
    {
        return $this->product->with($relations)->where($params)->withCount($withCount)->first();
    }

    public function getFirstWhereWithoutGlobalScope(array $params, array $relations = []): ?Model
    {
        return $this->product->withoutGlobalScopes()->where($params)->with($relations)->first();
    }

    public function getFirstWhereActive(array $params, array $relations = []): ?Model
    {
        return $this->product->active()->where($params)->with($relations)->first();
    }

    public function getWebFirstWhereActive(array $params, array $relations = [], array $withCount = []): ?Model
    {
        return $this->product->active()
            ->when(isset($relations['reviews']), function ($query) use ($relations) {
                return $query->with($relations['reviews']);
            })
            ->when(isset($relations['seller.shop']), function ($query) use ($relations) {
                return $query->with($relations['seller.shop']);
            })
            ->when(isset($relations['wishList']), function ($query) use ($relations, $params) {
                return $query->with([$relations['wishList'] => function ($query) use ($params) {
                    return $query->when(isset($params['customer_id']), function ($query) use ($params) {
                        return $query->where('customer_id', $params['customer_id']);
                    });
                }]);
            })
            ->when(isset($relations['compareList']), function ($query) use ($relations, $params) {
                return $query->with([$relations['compareList'] => function ($query) use ($params) {
                    return $query->when(isset($params['customer_id']), function ($query) use ($params) {
                        return $query->where('user_id', $params['customer_id']);
                    });
                }]);
            })
            ->when(isset($relations['digitalProductAuthors']), function ($query) use ($relations) {
                return $query->with($relations['digitalProductAuthors'], function ($query) {
                    return $query->with('author');
                });
            })
            ->when(isset($relations['digitalProductPublishingHouse']), function ($query) use ($relations) {
                return $query->with($relations['digitalProductPublishingHouse'], function ($query) {
                    return $query->with('publishingHouse');
                });
            })
            ->when(isset($relations['clearanceSale']), function ($query) use ($relations) {
                return $query->with(['clearanceSale' => function($query) {
                    return $query->active();
                }]);
            })
            ->when(isset($relations['subCategory']), function ($query) use ($relations) {
                return $query->with($relations['subCategory']);
            })
            ->when(isset($params['id']), function ($query) use ($params) {
                return $query->where('id', $params['id']);
            })
            ->when(isset($params['slug']), function ($query) use ($params) {
                return $query->where('slug', $params['slug']);
            })
            ->when(isset($withCount['orderDetails']), function ($query) use ($withCount) {
                return $query->withCount($withCount['orderDetails']);
            })
            ->when(isset($withCount['wishList']), function ($query) use ($withCount) {
                return $query->withCount($withCount['wishList']);
            })
            ->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        // TODO: Implement getList() method.
    }

    /**
     * Product names are stored per-locale in `translations`, not on `products.name` directly
     * for non-default locales — so a plain `name LIKE` search misses translated matches.
     * Shared by every list method below that supports searching by name.
     */
    private function getProductIdsMatchingTranslatedName(string $searchValue): array
    {
        return $this->translation->where('translationable_type', 'App\Models\Product')
            ->where('key', 'name')
            ->where('value', 'like', "%{$searchValue}%")
            ->pluck('translationable_id')
            ->toArray();
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null, array $select = []): Collection|LengthAwarePaginator
    {
        $query = $this->product->with($relations)
            // Only applied when the caller explicitly passes a column list (e.g. the
            // list-page controllers) — every other caller keeps SELECT * unchanged,
            // since this method is shared by ~20 call sites needing different columns
            // (gallery view needs colors/choice_options, exports need more, etc).
            ->when(!empty($select), function ($query) use ($select) {
                return $query->select($select);
            })
            ->when(isset($filters['added_by']) && $this->isAddedByInHouse(addedBy: $filters['added_by']), function ($query) {
            return $query->where(['added_by' => 'admin']);
        })->when(isset($filters['added_by']) && !$this->isAddedByInHouse($filters['added_by']), function ($query) use ($filters) {
            return $query->where(['added_by' => 'seller'])
                ->when(isset($filters['request_status']) && $filters['request_status'] != 'all', function ($query) use ($filters) {
                    $query->where(['request_status' => $filters['request_status']]);
                })
                ->when(isset($filters['seller_id'])&& $filters['seller_id']!='all', function ($query) use ($filters) {
                    return $query->where(['user_id' => $filters['seller_id']]);
                });
        })->when($searchValue, function ($query) use ($filters, $searchValue) {
            $productIds = $this->getProductIdsMatchingTranslatedName($searchValue);

            $codeSearch = $filters['code'] ?? $searchValue;

            return $query->where(function ($query) use ($searchValue, $productIds, $codeSearch) {
                $query->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('code', 'like', "%{$codeSearch}%");

                if (!empty($productIds)) {
                    $query->orWhereIn('id', $productIds);
                }
                });
        })->when(isset($filters['product_search_type']) && $filters['product_search_type'] == 'product_gallery', function ($query) use ($filters) {
            return $query->when(isset($filters['request_status']) && $filters['request_status'] != 'all', function ($query) use ($filters) {
                    $query->where(['request_status' => $filters['request_status']]);
                });
        })->when(isset($filters['brand_id']) && $filters['brand_id'] != 'all', function ($query) use ($filters) {
            return $query->where(['brand_id' => $filters['brand_id']]);
        })->when(isset($filters['category_id']) && !empty($filters['category_id']) && $filters['category_id'] != 'all', function ($query) use ($filters) {
            return $query->where(['category_id' => $filters['category_id']]);
        })->when(isset($filters['sub_category_id']) && !empty($filters['sub_category_id']) && $filters['sub_category_id'] != 'all', function ($query) use ($filters) {
            return $query->where(['sub_category_id' => $filters['sub_category_id']]);
        })->when(isset($filters['sub_sub_category_id']) && !empty($filters['sub_sub_category_id']) && $filters['sub_sub_category_id'] != 'all', function ($query) use ($filters) {
            return $query->where(['sub_sub_category_id' => $filters['sub_sub_category_id']]);
        })->when(isset($filters['is_shipping_cost_updated']), function ($query) use ($filters) {
            return $query->where(['is_shipping_cost_updated' => $filters['is_shipping_cost_updated']]);
        })->when(isset($filters['status']), function ($query) use ($filters) {
            return $query->where(['status' => $filters['status']]);
        })->when(isset($filters['code']), function ($query) use ($filters) {
            return $query->where(['code' => $filters['code']]);
        })->when(isset($filters['productIds']), function ($query) use ($filters) {
            return $query->whereIn('id' , $filters['productIds']);
        })->when(!empty($orderBy), function ($query) use ($orderBy) {
            $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
        });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function getListWithScope(array $orderBy = [], string $searchValue = null, string $scope = null, array $filters = [], array $whereIn = [], array $whereNotIn = [], array $relations = [], array $withCount = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->product->with($relations)
            ->when(isset($withCount['reviews']), function ($query) use ($withCount) {
                return $query->withCount($withCount['reviews']);
            })
            ->when(isset($scope) && $scope == 'active', function ($query) {
                return $query->active();
            })
            ->when($searchValue, function ($query) use ($searchValue) {
                $product_ids = $this->getProductIdsMatchingTranslatedName($searchValue);

                return $query->where(function ($query) use ($searchValue, $product_ids) {
                    return $query->where('name', 'like', "%{$searchValue}%")
                        ->orWhereIn('id', $product_ids);
                });
            })
            ->when(isset($filters['search_from']) && $filters['search_from'] == 'pos', function ($query) use ($filters) {
                // SEC-01 FIX: bind the keyword as a parameter instead of interpolating
                // it into raw SQL, eliminating the SQL-injection vector in ORDER BY.
                $searchKeyword = preg_replace('/\s\s+/', ' ', $filters['keywords']);
                return $query->where(function ($query) use ($filters) {
                    return $query->where('code', 'like', "%{$filters['keywords']}%")
                        ->orWhere('name', 'like', "%{$filters['keywords']}%");
                })
                ->orderByRaw("CASE WHEN name LIKE ? THEN 1 ELSE 2 END, LOCATE(?, name), name", ["%{$searchKeyword}%", $searchKeyword]);
            })
            ->when(isset($filters['added_by']) && $this->isAddedByInHouse(addedBy: $filters['added_by']), function ($query) {
                return $query->where(['added_by' => 'admin']);
            })
            ->when(isset($filters['added_by']) && !$this->isAddedByInHouse($filters['added_by']), function ($query) use ($filters) {
                return $query->where(['added_by' => 'seller'])
                    ->when(isset($filters['request_status']), function ($query) use ($filters) {
                        $query->where(['request_status' => $filters['request_status']]);
                    })
                    ->when(isset($filters['seller_id']), function ($query) use ($filters) {
                        return $query->where(['user_id' => $filters['seller_id']]);
                    });
            })
            ->when(isset($filters['brand_id']), function ($query) use ($filters) {
                return $query->where(['brand_id' => $filters['brand_id']]);
            })->when(isset($filters['category_id']), function ($query) use ($filters) {
                return $query->where(['category_id' => $filters['category_id']]);
            })->when(isset($filters['sub_category_id']), function ($query) use ($filters) {
                return $query->where(['sub_category_id' => $filters['sub_category_id']]);
            })->when(isset($filters['status']), function ($query) use ($filters) {
                return $query->where(['status' => $filters['status']]);
            })->when(isset($whereIn), function ($query) use ($whereIn) {
                foreach ($whereIn as $key => $whereInIndex) {
                    return $query->whereIn($key, $whereInIndex);
                }
            })
            ->when(isset($filters['sub_sub_category_id']), function ($query) use ($filters) {
                return $query->where(['sub_sub_category_id' => $filters['sub_sub_category_id']]);
            })->when($whereNotIn, function ($query) use ($whereNotIn) {
                foreach ($whereNotIn as $key => $whereNotInIndex) {
                    $query->whereNotIn($key, $whereNotInIndex);
                }
            })->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function getWebListWithScope(array $orderBy = [], string $searchValue = null, string $scope = null, array $filters = [], array $whereHas = [], array $whereIn = [], array $whereNotIn = [], array $relations = [], array $withCount = [], array $withSum = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->product
            ->when(isset($scope) && $scope == 'active', function ($query) {
                return $query->active();
            })
            ->when(isset($filters['added_by']) && $this->isAddedByInHouse(addedBy: $filters['added_by']), function ($query) {
                return $query->where(['added_by' => 'admin']);
            })->when(isset($filters['added_by']) && !$this->isAddedByInHouse($filters['added_by']), function ($query) use ($filters) {
                return $query->where(['added_by' => 'seller']);
            })
            ->when(isset($relations['reviews']), function ($query) use ($relations) {
                return $query->with($relations['reviews'], function ($query) use($relations) {
                    return $query->active();
                });
            })
            ->when(isset($relations['seller.shop']), function ($query) use ($relations) {
                return $query->with($relations['seller.shop']);
            })
            ->when(isset($relations['clearanceSale']), function ($query) use ($relations) {
                return $query->with([$relations['clearanceSale'] => function ($query) {
                    return $query->active();
                }]);
            })
            ->when(isset($relations['flashDealProducts.flashDeal']), function ($query) use ($relations) {
                return $query->with($relations['flashDealProducts.flashDeal']);
            })
            ->when(isset($relations['wishList']), function ($query) use ($relations, $filters) {
                return $query->with([$relations['wishList'] => function ($query) use ($filters) {
                    return $query->when(isset($filters['customer_id']), function ($query) use ($filters) {
                        return $query->where('customer_id', $filters['customer_id']);
                    });
                }]);
            })
            ->when(isset($relations['compareList']), function ($query) use ($relations, $filters) {
                return $query->with([$relations['compareList'] => function ($query) use ($filters) {
                    return $query->when(isset($filters['customer_id']), function ($query) use ($filters) {
                        return $query->where('user_id', $filters['customer_id']);
                    });
                }]);
            })
            ->when(isset($whereHas['reviews']), function ($query) use ($whereHas) {
                return $query;
            })
            ->when(isset($withCount['reviews']), function ($query) use ($withCount) {
                return $query->withCount([$withCount['reviews'] => function ($query) {
                    return $query->active();
                }]);
            })
            ->when($withSum, function ($query) use ($withSum) {
                foreach ($withSum as $sum) {
                    return $query->withSum($sum['relation'], $sum['column'], function ($query) use ($sum) {
                        $query->where($sum['whereColumn'], $sum['whereValue']);
                    });
                }
                return $query->withSum($withSum['orderDetails']);
            })
            ->when(isset($withSum['qty']), function ($query) use ($withSum) {
                return $query->withSum($withSum['qty']);
            })
            ->when($searchValue, function ($query) use ($searchValue) {
                $product_ids = $this->getProductIdsMatchingTranslatedName($searchValue);
                return $query->where('name', 'like', "%{$searchValue}%")->orWhereIn('id', $product_ids);
            })->when(isset($filters['seller_id']), function ($query) use ($filters) {
                return $query->where('user_id', $filters['seller_id']);
            })->when(isset($filters['brand_id']), function ($query) use ($filters) {
                return $query->where(['brand_id' => $filters['brand_id']]);
            })->when(isset($filters['category_id']), function ($query) use ($filters) {
                return $query->where(['category_id' => $filters['category_id']]);
            })->when(isset($filters['sub_category_id']), function ($query) use ($filters) {
                return $query->where(['sub_category_id' => $filters['sub_category_id']]);
            })->when(isset($whereIn), function ($query) use ($whereIn) {
                foreach ($whereIn as $key => $whereInIndex) {
                    return $query->whereIn($key, $whereInIndex);
                }
            })->when(isset($filters['sub_sub_category_id']), function ($query) use ($filters) {
                return $query->where(['sub_sub_category_id' => $filters['sub_sub_category_id']]);
            })->when($whereNotIn, function ($query) use ($whereNotIn) {
                foreach ($whereNotIn as $key => $whereNotInIndex) {
                    $query->whereNotIn($key, $whereNotInIndex);
                }
            })->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        cacheRemoveByType(type: 'products');
        return $this->product->find($id)->update($data);
    }

    public function updateByParams(array $params, array $data): bool
    {
        cacheRemoveByType(type: 'products');
        return $this->product->where($params)->update($data);
    }

    public function getDistinctBrandIds(string $addedBy): array
    {
        return $this->product->when($this->isAddedByInHouse(addedBy: $addedBy), function ($query) {
                return $query->where(['added_by' => 'admin']);
            })->when(!$this->isAddedByInHouse($addedBy), function ($query) {
                return $query->where(['added_by' => 'seller']);
            })
            ->whereNotNull('brand_id')
            ->where('brand_id', '!=', 0)
            ->distinct()
            ->pluck('brand_id')
            ->toArray();
    }

    public function getListWhereNotIn(array $filters = [], array $whereNotIn = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        return $this->product->when($whereNotIn, function ($query) use ($whereNotIn) {
            foreach ($whereNotIn as $key => $whereNotInIndex) {
                $query->whereNotIn($key, $whereNotInIndex);
            }
        })->when(isset($filters['user_id']), function ($query) use ($filters) {
            return $query->where(['user_id' => $filters['user_id']]);
        })->when(isset($filters['added_by']), function ($query) use ($filters) {
            return $query->where(['added_by' => $filters['added_by']]);
        })->get();
    }

    public function getTopRatedList(array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        // Only the reviews_count/ratings_average aggregates are ever displayed by callers —
        // no need to also eager-load the full reviews collection.
        $query = $this->product->with($relations)->where($filters)
            ->withCount(['reviews' => function ($query){
                return $query->whereNull('delivery_man_id');
            }])
            ->withAvg('rating as ratings_average', 'rating')
            ->orderByDesc('reviews_count');

        return $dataLimit === 'all' ? $query->get() : $query->limit($dataLimit)->get();
    }

    public function getTopSellList(array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->product->with($relations)
            ->when(isset($filters['added_by']) && $this->isAddedByInHouse(addedBy: $filters['added_by']), function ($query) {
                return $query->where(['added_by' => 'admin']);
            })->when(isset($filters['added_by']) && !$this->isAddedByInHouse($filters['added_by']), function ($query) use ($filters) {
                return $query->where(['added_by' => 'seller', 'request_status' => $filters['request_status']]);
            })->when(isset($filters['seller_id']), function ($query) use ($filters) {
                return $query->where('user_id', $filters['seller_id']);
            })
            ->when(isset($filters['request_status']), function ($query) use ($filters) {
                return $query->where('request_status', $filters['request_status']);
            })
            ->whereHas('orderDetails', function ($query) {
                $query->where(['delivery_status' => 'delivered']);
            })
            ->withCount('orderDetails')
            ->orderByDesc('order_details_count');

        return $dataLimit === 'all' ? $query->get() : $query->limit($dataLimit)->get();
    }

    public function delete(array $params): bool
    {
        cacheRemoveByType(type: 'products');
        return $this->product->where($params)->delete();
    }

    public function getStockLimitListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $withCount = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $stockLimit = getWebConfig(name: 'stock_limit');
        $query = $this->product->with($relations)
            ->withCount($withCount)
            ->when($this->isAddedByInHouse(addedBy: $filters['added_by']), function ($query) {
                return $query->where(['added_by' => 'admin']);
            })
            ->when(!$this->isAddedByInHouse($filters['added_by']), function ($query) use ($filters) {
                return $query->where(['added_by' => 'seller', 'product_type' => 'physical'])
                    ->when(isset($filters['request_status']), function ($query) use ($filters) {
                        return $query->where(['request_status' => $filters['request_status']]);
                    })
                    ->when(isset($filters['seller_id']), function ($query) use ($filters) {
                        return $query->where(['user_id' => $filters['seller_id']]);
                    });
            })
            ->when(isset($filters['product_type']), function ($query) use ($filters) {
                return $query->where(['product_type' => $filters['product_type']]);
            })
            ->when($searchValue, function ($query) use ($searchValue) {
                $product_ids = $this->getProductIdsMatchingTranslatedName($searchValue);

                return $query->where('name', 'like', "%{$searchValue}%")->orWhereIn('id', $product_ids);
            })
            ->when($stockLimit <= 0, function ($query) {
                return $query->where('current_stock', 0);
            })
            ->when($stockLimit > 0, function ($query) use ($stockLimit) {
                return $query->where('current_stock', '<', $stockLimit);
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                return $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function getProductIds(array $filters = []): \Illuminate\Support\Collection|array
    {
        return $this->product->when(isset($filters['added_by']), function ($query) use ($filters) {
            return $query->where('added_by', $filters['added_by']);
        })->when(isset($filters['user_id']), function ($query) use ($filters) {
            return $query->where('user_id', $filters['user_id']);
        })->pluck('id');

    }

    public function addArray(array $data): bool
    {
        cacheRemoveByType(type: 'products');
        return DB::table('products')->insert($data);
    }
}
