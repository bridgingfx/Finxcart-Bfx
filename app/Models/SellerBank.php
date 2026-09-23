<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class SellerBank
 *
 * @property int $id
 * @property int $seller_id
 * @property string $bank_name
 * @property string $holder_name
 * @property string $account_no
 * @property string|null $branch
 * @property string|null $swift_code
 * @property string|null $ifsc_code
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class SellerBank extends Model
{
    protected $fillable = [
        'seller_id',
        'bank_name',
        'holder_name',
        'account_no',
        'branch',
        'swift_code',
        'ifsc_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }
}
