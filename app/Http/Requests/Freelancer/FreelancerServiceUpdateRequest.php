<?php

namespace App\Http\Requests\Freelancer;

use App\Models\FreelancerCategory;
use App\Models\FreelancerSpecialization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class FreelancerServiceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('freelancer')->check() && auth('freelancer')->user()?->seller_type === 'freelancer';
    }

    public function rules(): array
    {
        return [
            'freelancer_category_id' => 'required|exists:freelancer_categories,id',
            'freelancer_specialization_id' => 'required|exists:freelancer_specializations,id',
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*.id' => 'nullable|integer|regex:/^\d+$/',
            'images.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'packages' => 'nullable|array',
            'packages.*.is_enabled' => 'nullable|boolean',
            'packages.*.title' => 'nullable|string|max:100',
            'packages.*.description' => 'nullable|string|max:1000',
            'packages.*.price' => 'nullable|numeric|min:0|regex:/^\d+(\.\d+)?$/',
            'packages.*.delivery_time_days' => 'nullable|integer|min:1|max:365|regex:/^\d+$/',
            'packages.*.revisions' => 'nullable|integer|min:0|max:100|regex:/^\d+$/',
            'packages.*.features' => 'nullable|array',
            'packages.*.features.*.label' => 'nullable|string|max:100',
            'packages.*.features.*.included' => 'nullable|boolean',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $sellerId = auth('freelancer')->id();

            $hasSubmittedImage = collect($this->input('images', []))->contains(function ($row, $index) {
                return !empty($row['id']) || $this->hasFile("images.$index.image");
            });

            if (!$hasSubmittedImage) {
                $validator->errors()->add('images', translate('at_least_one_service_image_is_required'));
            }

            $categoryVisible = FreelancerCategory::withoutGlobalScope('translate')
                ->where('id', $this->input('freelancer_category_id'))
                ->where(function ($query) use ($sellerId) {
                    $query->whereNull('seller_id')->orWhere('seller_id', $sellerId);
                })
                ->exists();

            if (!$categoryVisible) {
                $validator->errors()->add('freelancer_category_id', translate('selected_freelancer_category_is_invalid'));
            }

            $belongs = FreelancerSpecialization::withoutGlobalScope('translate')
                ->where('id', $this->input('freelancer_specialization_id'))
                ->where('freelancer_category_id', $this->input('freelancer_category_id'))
                ->where(function ($query) use ($sellerId) {
                    $query->whereNull('seller_id')->orWhere('seller_id', $sellerId);
                })
                ->exists();

            if (!$belongs) {
                $validator->errors()->add('freelancer_specialization_id', translate('selected_specialization_does_not_belong_to_category'));
            }
        });
    }
}
