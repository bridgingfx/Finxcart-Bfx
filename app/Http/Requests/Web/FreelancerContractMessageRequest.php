<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class FreelancerContractMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('customer')->check() || auth('freelancer')->check();
    }

    public function rules(): array
    {
        return [
            'body' => 'nullable|string|max:5000|required_without:attachments',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240',
        ];
    }
}
