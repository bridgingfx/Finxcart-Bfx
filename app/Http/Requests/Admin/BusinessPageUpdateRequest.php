<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BusinessPageUpdateRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            // Keep Quill's HTML so paragraphs, headings, and lists render correctly.
            'description' => trim((string)$this->input('description')),
        ]);
    }

    public function rules(): array
    {
        if (in_array(Str::slug($this['title']), ['terms-and-conditions', 'about-us', 'privacy-policy'])) {
            $this->merge(['status' => 1]);
        }

        return [
            'title' => [
                'required', 'string', Rule::unique('business_pages', 'title')->ignore($this['id']),
            ],
            'description' => 'required|string',
            'slug' => [
                'string', Rule::unique('business_pages', 'slug')->ignore($this['id']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => translate('title_is_required'),
            'description.required' => translate('description_is_required'),
            'slug.unique' => translate('slug_must_be_unique'),
        ];
    }

}
