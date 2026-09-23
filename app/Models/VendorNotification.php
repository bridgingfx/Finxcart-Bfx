<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'title',
        'message',
        'link',
        'read_at',
        'reference_id', // <--- Add this
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function getLinkAttribute($value): ?string
    {
        return $this->normalizeLink($value);
    }

    public function setLinkAttribute($value): void
    {
        $this->attributes['link'] = $this->normalizeLink($value);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    private function normalizeLink($value): ?string
    {
        if (is_object($value)) {
            $value = (array) $value;
        }

        if (is_array($value)) {
            $value = $value['url'] ?? $value['link'] ?? $value['href'] ?? null;
        }

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '' || str_contains($value, '[object Object]')) {
            return null;
        }

        $decodedValue = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedValue)) {
            return $this->normalizeLink($decodedValue);
        }

        return $value;
    }
}
