<?php

namespace App\Http\Requests\Freelancer;

use App\Models\FreelancerCategory;
use App\Models\FreelancerPortfolioItem;
use App\Models\FreelancerSpecialization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class FreelancerServiceCreateRequest extends FormRequest
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
            'images' => 'required|array|min:1',
            'images.*.image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
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

            $hasActivePortfolio = FreelancerPortfolioItem::where('seller_id', $sellerId)
                ->where('is_active', true)
                ->exists();

            if (!$hasActivePortfolio) {
                $validator->errors()->add('portfolio', translate('please_create_and_activate_at_least_one_portfolio_item_before_creating_a_service'));
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
