<?php

// app/Models/SellerContract.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerContract extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'seller_id',
        'tier_name',
        'seller_full_name',
        'seller_entity',
        'agreement_date',
        'signature_image_path',
        'contract_pdf_path',
        'agreed_to_terms',
        'vendor_tier_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'agreement_date' => 'date',
        'agreed_to_terms' => 'boolean',
    ];

    // Optional: Define relationship to the Seller/User model
    // public function seller()
    // {
    //     return $this->belongsTo(User::class, 'seller_id');
    // }
}
