<?php

namespace App\Http\Requests\Vendor;

use App\Enums\SessionKey;
use App\Traits\CalculatorTrait;
use App\Traits\RecaptchaTrait;
use App\Traits\ResponseHandler;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Validator;

class VendorAddRequest extends FormRequest
{
    use RecaptchaTrait;
    use CalculatorTrait, ResponseHandler;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        // This same request class backs Vendor self-registration, Freelancer
        // self-registration (App\Http\Controllers\Vendor\Auth\RegisterController
        // and App\Http\Controllers\Freelancer\Auth\RegisterController), AND admin
        // -created vendors (App\Http\Controllers\Admin\Vendor\VendorController) —
        // three different forms with different field names, so rules branch by
        // which controller is currently handling the request.
        $controller = (string) $this->route()?->getAction('controller');
        $isFreelancer = str_contains($controller, 'Freelancer\\Auth\\RegisterController');
        $isAdminCreated = str_contains($controller, 'Admin\\Vendor\\VendorController');

        if ($isAdminCreated) {
            $isCompany = $this->input('seller_type') === 'company';
            $isIndividual = $this->input('seller_type') === 'individual';

            // Admin-created vendors submit seller_type as 'company' or 'individual'
            // (never NULL), matching the sellers_email_seller_type_unique DB index —
            // scope the uniqueness check to that same seller_type, not whereNull()
            // (which is only correct for self-registration rows).
            $scopeBySellerType = fn ($query) => $query->where('seller_type', $this->input('seller_type'));

            return [
                'seller_type' => 'required|in:company,individual',
                'f_name' => 'required|string|max:255',
                'l_name' => 'required|string|max:255',
                'phone' => [
                    'required',
                    'max:20',
                    \Illuminate\Validation\Rule::unique('sellers')->where($scopeBySellerType),
                ],
                'email' => [
                    'required',
                    'email',
                    \Illuminate\Validation\Rule::unique('sellers')->where($scopeBySellerType),
                ],
                // Laravel's built-in "confirmed" rule expects a *_confirmation field
                // name; this form calls it confirm_password, so match manually via "same".
                'password' => 'required|min:8|same:confirm_password',
                'confirm_password' => 'required',
                // Both the company and individual sections have their own file
                // input named "image" (only one is enabled/submitted at a time via
                // JS toggling the "disabled" attribute), so it's valid either way.
                'image' => 'nullable|image|max:5120',
                'logo' => 'nullable|image|max:5120',
                'banner' => 'nullable|image|max:5120',
                'bottom_banner' => 'nullable|image|max:5120',
                'company_website' => $isCompany ? 'nullable|url|max:255' : 'nullable',
                'company_no' => $isCompany ? 'nullable|string|max:100' : 'nullable',
                'company_license' => 'nullable|mimes:pdf,jpg,jpeg,png,webp|max:10240',
                'company_registered_country' => 'nullable|string|max:100',
                'shop_name' => $isCompany ? 'required|string|max:255' : 'nullable|string|max:255',
                'shop_address' => 'nullable|string|max:500',
                'personal_id_document' => $isIndividual ? 'required|mimes:pdf,jpg,jpeg,png,webp|max:10240' : 'nullable|mimes:pdf,jpg,jpeg,png,webp|max:10240',
                'sell_description' => 'nullable|string|max:1000',
            ];
        }

        // Vendor registration stores seller_type as NULL (not a literal 'vendor'
        // string) — "= NULL" never matches in SQL, so the NULL case needs whereNull.
        $scopeBySellerType = $isFreelancer
            ? fn ($query) => $query->where('seller_type', 'freelancer')
            : fn ($query) => $query->whereNull('seller_type');

        return [
            'vendor_name' => 'required|string|max:255',
            'phone' => [
                'required',
                'max:20',
                \Illuminate\Validation\Rule::unique('sellers')->where($scopeBySellerType),
            ],
            'email' => [
                'required',
                \Illuminate\Validation\Rule::unique('sellers')->where($scopeBySellerType),
            ],
            'password' => 'required|min:8|confirmed',
            'agree_to_terms' => 'required|in:1',
        ];
    }

    public function messages(): array
    {
        return [
            'vendor_name.required' => 'Please enter your name.',
            'f_name.required' => translate('The_first_name_field_is_required'),
            'l_name.required' => translate('The_last_name_field_is_required'),
            'seller_type.required' => translate('Please_select_a_seller_type'),
            'phone.required' => translate('The_phone_field_is_required'),
            'phone.unique' => translate('The_phone_number_has_already_been_taken'),
            'phone.max' => translate('please_ensure_your_phone_number_is_valid_and_does_not_exceed_20_characters'),
            'email.required' => translate('The_email_field_is_required'),
            'email.unique' => 'This email is already registered. Please login instead.',
            'password.required' => translate('The_password_field_is_required'),
            'password.min' => translate('The_password_must_be_at_least_8_characters'),
            'password.confirmed' => translate('The_password_and_confirm_password_must_match'),
            'agree_to_terms.required' => translate('Please_read_and_agree_to_Terms_&_Conditions'),
            'agree_to_terms.in' => translate('Please_read_and_agree_to_Terms_&_Conditions'),
            'shop_name.required' => translate('The_shop_name_field_is_required'),
            'personal_id_document.required' => translate('Please_upload_a_personal_ID_or_passport_document'),
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $recaptcha = getWebConfig(name: 'recaptcha');
                    // if (isset($recaptcha) && $recaptcha['status'] == 1) {
                    //     if (!$this['g-recaptcha-response'] || !$this->isGoogleRecaptchaValid($this['g-recaptcha-response'])) {
                    //         $validator->errors()->add(
                    //             'recaptcha', translate('ReCAPTCHA_Failed') . '!'
                    //         );
                    //     }
                    // } else if ($recaptcha['status'] != 1 && strtolower($this['default_recaptcha_id_seller_regi']) != strtolower(Session(SessionKey::VENDOR_RECAPTCHA_KEY))) {
                    //     $validator->errors()->add(
                    //         'g-recaptcha-response', translate('ReCAPTCHA_Failed') . '!'
                    //     );
                    // } else if ($recaptcha['status'] != 1 && strtolower($this['default_recaptcha_id_seller_regi']) == strtolower(Session(SessionKey::VENDOR_RECAPTCHA_KEY))) {
                    //     Session::forget(SessionKey::VENDOR_RECAPTCHA_KEY);
                    // }

                    $numericPhoneValue = preg_replace('/[^0-9]/', '', $this['phone']);
                    $numericLength = strlen($numericPhoneValue);
                    if ($numericLength < 4) {
                        $validator->errors()->add(
                            'phone.min', translate('The_phone_number_must_be_at_least_4_characters')
                        );
                    }

                    if ($numericLength > 20) {
                        $validator->errors()->add(
                            'phone.max', translate('The_phone_number_may_not_be_greater_than_20_characters')
                        );
                    }
                }
            ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->expectsJson() || $this->ajax()) {
            throw new HttpResponseException(
                response()->json(['errors' => $validator->errors()->toArray()], 422)
            );
        }

        throw new HttpResponseException(
            redirect()->back()->withErrors($validator)->withInput()
        );
    }
}
