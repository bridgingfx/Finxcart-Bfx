<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use App\Traits\ResponseHandler;

class MailBrevoUpdateRequest extends Request
{
    use ResponseHandler;

    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "name" => 'required',
            "driver" => 'required',
            "email" => 'required',
            "password" => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => translate('the_name_field_is_required!'),
            'driver.required' => translate('the_driver_field_is_required!'),
            'email.required' => translate('the_sender_ID_field_is_required!'),
            'password.required' => translate('the_api_key_field_is_required!'),
        ];
    }
}
