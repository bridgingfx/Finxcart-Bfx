<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class FreelancerContractReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('customer')->check() || auth('freelancer')->check();
    }

    public function rules(): array
    {
        return [
            'rating' => 'required|integer|min:1|max:5|regex:/^\d+$/',
            'body' => 'nullable|string|max:2000',
        ];
    }
}
