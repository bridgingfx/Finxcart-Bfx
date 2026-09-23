<?php

namespace App\Http\Requests\Freelancer;

use App\Enums\Freelancer\DeliveryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FreelancerDeliveryStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('freelancer')->check();
    }

    public function rules(): array
    {
        return [
            'delivery_status' => ['required', Rule::in(array_map(fn ($case) => $case->value, DeliveryStatus::cases()))],
        ];
    }
}
