<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CustomerUpdateRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->route('id')),
            ],
            'phone' => [
                'required',
                'min:4',
                'max:20',
                Rule::unique('users', 'phone')->ignore($this->route('id')),
            ],
        ];
        if ($this['password']) {
            $rules['password'] = 'required|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W)(?!.*\s).{8,}$/|same:confirm_password';
        }
        return $rules;
    }

    public function messages(): array
    {
        return [
            'f_name.required' => translate('first_name_is_required'),
            'l_name.required' => translate('last_name_is_required'),
            'email.required' => translate('email_is_required'),
            'email.email' => translate('email_must_be_valid'),
            'email.unique' => translate('email_already_in_use'),
            'phone.required' => translate('phone_is_required'),
            'phone.unique' => translate('phone_already_in_use'),
            'phone.max' => translate('please_ensure_your_phone_number_is_valid_and_does_not_exceed_20_characters'),
            'phone.min' => translate('phone_number_with_a_minimum_length_requirement_of_4_characters'),
            'password.regex' => translate('The_password_must_be_at_least_8_characters_long_and_contain_at_least_one_uppercase_letter') . ',' . translate('_one_lowercase_letter') . ',' . translate('_one_digit_') . ',' . translate('_one_special_character') . ',' . translate('_and_no_spaces') . '.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                // password's own rule only activates when $this['password'] is
                // filled — without this, filling ONLY confirm_password silently
                // does nothing (no error, no password change), which would leave
                // the admin thinking a password was set when it wasn't.
                if (empty($this['password']) && !empty($this['confirm_password'])) {
                    $validator->errors()->add('password', translate('password_is_required'));
                }
            }
        ];
    }
}
