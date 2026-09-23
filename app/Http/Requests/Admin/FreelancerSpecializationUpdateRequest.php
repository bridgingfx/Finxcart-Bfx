<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FreelancerSpecializationUpdateRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'freelancer_category_id' => [
                'required',
                Rule::exists('freelancer_categories', 'id')->where('is_active', 1),
            ],
            'name' => 'required|array',
            'name.0' => [
                'required',
                'max:100',
                Rule::unique('freelancer_specializations', 'name')->ignore($this->route('id')),
            ],
            'lang' => 'required|array',
            'description' => 'nullable|array',
            'description.*' => 'nullable|max:500',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,bmp,tif,tiff,webp|max:2048',
            'priority' => 'required|integer|min:0|max:20|regex:/^\d+$/',
            'is_active' => 'nullable|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'freelancer_category_id.required' => translate('freelancer_category_is_required'),
            'freelancer_category_id.exists' => translate('selected_freelancer_category_is_invalid'),
            'name.required' => translate('the_name_field_is_required'),
            'name.0.required' => translate('the_name_field_is_required'),
            'name.0.max' => translate('the_name_must_not_be_greater_than_100_characters'),
            'name.0.unique' => translate('freelancer_specialization_has_already_been_taken'),
            'lang.required' => translate('language_is_required'),
            'image.image' => translate('the_file_must_be_an_image'),
            'priority.required' => translate('category_priority_is_required'),
        ];
    }
}
