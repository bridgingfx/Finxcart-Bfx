<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorVerification extends Model
{
    protected $fillable = [
        'seller_id',
        'seller_type',
        'company_website',
        'company_no',
        'company_license',
        'company_registered_country',
        'personal_id_document',
        'personal_name',
        'personal_email',
        'personal_contact',
        'sell_description',
        'status',
        'sumsub_applicant_id',
        'sumsub_review_status',
        'payout_kyc_document',
        'payout_kyc_note',
        'payout_kyc_submitted_at',
        'rejection_reason',
        'approval_note',
        'reviewed_by_name',
        'reviewed_at',
    ];

    protected $casts = [
        'seller_id' => 'integer',
        'reviewed_at' => 'datetime',
        'payout_kyc_submitted_at' => 'datetime',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }
}
