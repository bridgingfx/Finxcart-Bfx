<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class FreelancerHireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('customer')->check();
    }

    public function rules(): array
    {
        return [
            'scope' => 'required|string|min:20|max:5000',
            'quote_request_id' => 'nullable|integer|regex:/^\d+$/|exists:freelancer_quote_requests,id',
            'package' => 'nullable|string|in:basic,standard,premium',
        ];
    }
}

