<?php

namespace App\Http\Requests\Freelancer;

use Illuminate\Foundation\Http\FormRequest;

class FreelancerPortfolioAddRequest extends FormRequest
{
    public function authorize(): bool
    {
        $seller = auth('freelancer')->user();

        return auth('freelancer')->check() && $seller?->seller_type === 'freelancer' && $seller->status === 'approved';
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tags' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'project_url' => 'nullable|url|max:255',
            'completed_at' => 'nullable|date',
            'gallery' => 'nullable|array',
            'gallery.*.id' => 'nullable|integer|regex:/^\d+$/',
            'gallery.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'gallery.*.url' => 'nullable|url|max:255',
        ];
    }
}
