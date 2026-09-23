<?php

namespace App\Http\Requests\Freelancer;

use App\Models\FreelancerPortfolioItem;
use Illuminate\Validation\Validator;

class FreelancerPortfolioUpdateRequest extends FreelancerPortfolioAddRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'is_active' => 'nullable|boolean',
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ((bool) $this->input('is_active', true)) {
                return;
            }

            $itemId = (int) $this->route('id');
            $sellerId = auth('freelancer')->id();
            $activeItem = FreelancerPortfolioItem::where('seller_id', $sellerId)
                ->where('is_active', true)
                ->first();

            if ($activeItem && (int) $activeItem->id === $itemId) {
                $validator->errors()->add('is_active', translate('please_activate_another_portfolio_item_before_deactivating_this_one'));
            }
        });
    }
}
