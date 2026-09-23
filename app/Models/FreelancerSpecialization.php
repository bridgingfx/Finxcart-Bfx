<?php

namespace App\Models;

use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;

class FreelancerSpecialization extends Model
{
    use StorageTrait;

    protected $fillable = [
        'seller_id',
        'freelancer_category_id',
        'name',
        'slug',
        'description',
        'image',
        'image_storage_type',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'seller_id' => 'integer',
        'freelancer_category_id' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'description' => 'string',
        'image' => 'string',
        'image_storage_type' => 'string',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['image_full_url'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(FreelancerCategory::class, 'freelancer_category_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function storage(): MorphMany
    {
        return $this->morphMany(Storage::class, 'data');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', 1);
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

    public function getImageFullUrlAttribute(): array
    {
        return $this->storageLink('freelancer-specialization', $this->image, $this->image_storage_type ?? 'public');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                if (strpos(url()->current(), '/api')) {
                    return $query->where('locale', App::getLocale());
                }

                return $query->where('locale', getDefaultLanguage());
            }]);
        });
    }
}
