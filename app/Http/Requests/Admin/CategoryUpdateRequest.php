<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $icon
 * @property int $parent_id
 * @property int $position
 * @property int $home_status
 * @property int $priority
 */
class CategoryUpdateRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
            'priority' => 'required|integer|min:0|max:20|regex:/^\d+$/',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,bmp,tif,tiff,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => translate('category_name_is_required'),
            'priority.required' => translate('category_priority_is_required'),
            'image.image' => translate('the_file_must_be_an_image'),
        ];
    }

}
