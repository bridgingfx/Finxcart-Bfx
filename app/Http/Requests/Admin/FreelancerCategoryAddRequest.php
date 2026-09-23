<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FreelancerCategoryAddRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|array',
            'name.0' => 'required|max:100|unique:freelancer_categories,name',
            'lang' => 'required|array',
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,bmp,tif,tiff,webp|max:2048',
            'priority' => 'required|integer|min:0|max:20|regex:/^\d+$/',
            'is_active' => 'nullable|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => translate('the_name_field_is_required'),
            'name.0.required' => translate('the_name_field_is_required'),
            'name.0.max' => translate('the_name_must_not_be_greater_than_100_characters'),
            'name.0.unique' => translate('freelancer_category_has_already_been_taken'),
            'lang.required' => translate('language_is_required'),
            'image.required' => translate('category_image_is_required'),
            'image.image' => translate('the_file_must_be_an_image'),
            'priority.required' => translate('category_priority_is_required'),
        ];
    }
}
