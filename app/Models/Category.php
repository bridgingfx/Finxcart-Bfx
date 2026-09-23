<?php

namespace App\Models;

use App\Traits\CacheManagerTrait;
use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $icon
 * @property int $parent_id
 * @property int $position
 * @property int $home_status
 * @property int $priority
 */
class Category extends Model
{
    use StorageTrait, CacheManagerTrait;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'icon_storage_type',
        'parent_id',
        'position',
        'home_status',
        'priority',
    ];

    protected $casts = [
        'name' => 'string',
        'slug' => 'string',
        'icon' => 'string',
        'icon_storage_type' => 'string',
        'parent_id' => 'integer',
        'position' => 'integer',
        'home_status' => 'integer',
        'priority' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id')->orderBy('priority', 'desc');
    }

    public function childes(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('priority', 'asc');
    }

    public function product(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    // Old Relation: sub_category_product
    public function subCategoryProduct(): HasMany
    {
        return $this->hasMany(Product::class, 'sub_category_id', 'id');
    }

    // Old Relation: sub_sub_category_product
    public function subSubCategoryProduct(): HasMany
    {
        return $this->hasMany(Product::class, 'sub_sub_category_id', 'id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function getNameAttribute($name): string|null
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/vendor') || strpos(url()->current(), '/seller')) {
            return $name;
        }

        return $this->translations[0]->value ?? $name;
    }

    public function getDefaultNameAttribute(): string|null
    {
        return $this->translations[0]->value ?? $this->name;
    }


    public function scopePriority($query): mixed
    {
        return $query->orderBy('priority', 'asc');
    }

    public function getIconFullUrlAttribute():array
    {
        $value = $this->icon;
        $image = $this->storageLink($this->getImageDirectoryByPosition(), $value, $this->icon_storage_type ?? 'public');

        if (($image['status'] ?? 404) !== 200 && (int)$this->position !== 0) {
            return $this->storageLink('category', $value, $this->icon_storage_type ?? 'public');
        }

        return $image;
    }
    protected $appends = ['icon_full_url'];

    protected static function booted(): void
    {
        static::deleting(function (self $category) {
            $children = $category->relationLoaded('childes')
                ? $category->childes
                : $category->childes()->withoutGlobalScopes()->get();

            foreach ($children as $child) {
                $child->delete();
            }

            $affectedProducts = $category->products()->get();
            $category->products()->detach();

            foreach ($affectedProducts as $product) {
                $remainingCategories = $product->categories()->get();

                if ($remainingCategories->isEmpty()) {
                    $product->delete();
                    continue;
                }

                $legacyCategoryColumns = \App\Services\ProductService::resolveLegacyCategoryColumns($remainingCategories);

                $product->update($legacyCategoryColumns + [
                    'category_ids' => json_encode(\App\Services\ProductService::getCategoriesArray($legacyCategoryColumns)),
                ]);
            }

            // Safety net for any legacy rows never migrated into the category_product pivot.
            Product::query()
                ->where('category_id', $category->id)
                ->orWhere('sub_category_id', $category->id)
                ->orWhere('sub_sub_category_id', $category->id)
                ->delete();
        });

        static::saved(function ($model) {
            cacheRemoveByType(type: 'categories');
        });

        static::deleted(function ($model) {
            Translation::query()
                ->where('translationable_type', self::class)
                ->where('translationable_id', $model->id)
                ->delete();

            cacheRemoveByType(type: 'categories');
        });

        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                if (strpos(url()->current(), '/api')) {
                    return $query->where('locale', App::getLocale());
                } else {
                    return $query->where('locale', getDefaultLanguage());
                }
            }]);
        });
    }

    private function getImageDirectoryByPosition(): string
    {
        return match ((int)$this->position) {
            1 => 'sub-categories',
            2 => 'sub-sub-categories',
            default => 'category',
        };
    }
}
