<?php

namespace App\Http\Requests\Freelancer;

use Illuminate\Foundation\Http\FormRequest;

class FreelancerMilestoneSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('freelancer')->check();
    }

    public function rules(): array
    {
        return [
            'note' => 'nullable|string|max:5000|required_without:attachments',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|max:10240',
        ];
    }
}
