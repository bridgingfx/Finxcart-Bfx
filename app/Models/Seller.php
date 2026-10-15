<?php

namespace App\Models;

use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use App\Models\VendorVerification;

/**
 * @property int $id
 * @property string $f_name
 * @property string $l_name
 * @property string $country_code
 * @property string $phone
 * @property string $image
 * @property string $email
 * @property string $password
 * @property string $status
 * @property string $account_status
 * @property string $bank_name
 * @property string $branch
 * @property string $account_no
 * @property string $holder_name
 * @property string $auth_token
 * @property float $sales_commission_percentage
 * @property float $gst
 * @property string $cm_firebase_token
 * @property string $pos_status
 * @property float $minimum_order_amount
 * @property string $free_delivery_status
 * @property float $free_delivery_over_amount
 * @property string $app_language
 */
class Seller extends Authenticatable
{
    use Notifiable, StorageTrait;

    protected $fillable = [
        'f_name',
        'l_name',
        'country_code',
        'phone',
        'email',
        'free_delivery_over_amount',
        'image',
        'password',
        'status',
        'account_status',
        'bank_name',
        'branch',
        'branch_code',
        'account_no',
        'holder_name',
        'swift_code',
        'iban',
        'account_type',
        'bank_country',
        'bank_address',
        'currency_preference',
        'sales_commission_percentage',
        'gst',
        'cm_firebase_token',
        'pos_status',
        'minimum_order_amount',
        'free_delivery_status',
        'app_language',
        'seller_type',
        'kyc_status',
        'kyc_notification_seen',
        'first_login_after_approval',
        'terms_agreed_at',
        'terms_agreed_ip',
        'freelancer_rating_avg',
        'freelancer_rating_count',
        'freelancer_jobs_completed',
    ];

    protected $casts = [
        'id' => 'integer',
        'f_name' => 'string',
        'l_name' => 'string',
        'country_code' => 'string',
        'orders_count' => 'integer',
        'product_count' => 'integer',
        'pos_status' => 'integer',
        'freelancer_rating_avg' => 'decimal:2',
        'freelancer_rating_count' => 'integer',
        'freelancer_jobs_completed' => 'integer',
    ];

    public function scopeApproved($query)
    {
        return $query->where(['status' => 'approved']);
    }

    public function freelancerLevelSlug(): string
    {
        $ratingAvg = (float) ($this->freelancer_rating_avg ?? 0);
        $jobsCompleted = (int) ($this->freelancer_jobs_completed ?? 0);

        return match (true) {
            $jobsCompleted >= 50 && $ratingAvg >= 4.8 => 'top_rated',
            $jobsCompleted >= 10 && $ratingAvg >= 4.5 => 'level_2',
            default => 'level_1',
        };
    }

    public function freelancerLevelLabel(): string
    {
        return translate($this->freelancerLevelSlug());
    }

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class, 'seller_id');
    }

    public function vendorVerification(): HasOne
    {
        return $this->hasOne(VendorVerification::class, 'seller_id');
    }

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class, 'seller_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function product(): HasMany
    {
        return $this->hasMany(Product::class, 'user_id')->where(['added_by' => 'seller']);
    }

    public function productReviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, Product::class, 'user_id', 'product_id')
            ->where('products.added_by', 'seller')
            ->where('reviews.status', 1);
    }

    public function positiveProductReviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, Product::class, 'user_id', 'product_id')
            ->where('products.added_by', 'seller')
            ->where('reviews.status', 1)
            ->where('reviews.rating', '>=', 4);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(SellerWallet::class);
    }

    public function banks(): HasMany
    {
        return $this->hasMany(SellerBank::class, 'seller_id');
    }

    public function activeBank(): HasOne
    {
        return $this->hasOne(SellerBank::class, 'seller_id')->where('is_active', true);
    }

    public function freelancerContracts(): HasMany
    {
        return $this->hasMany(FreelancerContract::class, 'seller_id');
    }

    public function freelancerServices(): HasMany
    {
        return $this->hasMany(FreelancerService::class, 'seller_id');
    }

    public function coupon(): HasMany
    {
        return $this->hasMany(Coupon::class, 'seller_id')
            ->where(['coupon_bearer' => 'seller', 'status' => 1])
            ->whereDate('start_date', '<=', date('Y-m-d'))
            ->whereDate('expire_date', '>=', date('Y-m-d'));
    }

    public function getImageFullUrlAttribute(): array
    {
        if ($this->id == 0) {
            return getWebConfig(name: 'company_fav_icon');
        }
        $value = $this->image;
        if (count($this->storage) > 0) {
            $storage = $this->storage->where('key', 'image')->first();
        }
        return $this->storageLink('seller', $value, $storage['value'] ?? 'public');
    }

    protected $appends = ['image_full_url'];

    protected static function boot(): void
    {
        parent::boot();
        static::saved(function ($model) {
            if ($model->isDirty('image')) {
                $storage = config('filesystems.disks.default') ?? 'public';
                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'image',
                ], [
                    'value' => $storage,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            cacheRemoveByType(type: 'sellers');
        });

        static::deleted(function ($model) {
            cacheRemoveByType(type: 'sellers');
        });
    }

}
