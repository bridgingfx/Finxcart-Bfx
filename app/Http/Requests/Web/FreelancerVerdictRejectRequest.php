<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class FreelancerVerdictRejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('customer')->check();
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => 'required|string|min:5|max:2500',
        ];
    }
}
