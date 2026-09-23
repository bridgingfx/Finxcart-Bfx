<?php

namespace App\Http\Requests;

use App\Traits\CalculatorTrait;
use App\Traits\ResponseHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class ProductAddRequest extends Request
{
    use CalculatorTrait, ResponseHandler;

    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $isEventOrBroker = in_array($this['product_type'], ['event', 'broker']);
        $notSellable = $isEventOrBroker;

        $rules = [
            'name' => 'required',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
            'product_type' => 'required|in:physical,digital,event,broker',
            'digital_product_type' => 'required_if' . ':' . 'product_type' . ',==,' . 'digital',
            'delivery_mode' => 'nullable|in:auto,manual',
            'preview_file' => 'nullable|file|mimes:pdf,mp4,mp3|max:10240',
            'digital_file_ready' => [
                Rule::requiredIf(fn () => $this['product_type'] == 'digital' && $this['digital_product_type'] == 'ready_product'),
                'mimes:jpg,jpeg,png,gif,zip,pdf',
                'max:51200',
            ],
            'digital_files.*' => 'nullable|mimes:jpg,jpeg,png,gif,zip,pdf|max:51200',
            'unit' => 'required_if' . ':' . 'product_type' . ',==,' . 'physical',
            'current_stock' => 'required_if' . ':' . 'product_type' . ',==,' . 'physical' . '|' . 'numeric' . '|' . 'min' . ':0',
            'listing_type' => $this['product_type'] === 'physical' ? 'nullable|in:product' : 'nullable|in:product,event,broker',
            'tax' => $isEventOrBroker ? 'nullable|numeric|min:0|regex:/^\d+(\.\d+)?$/' : 'required|numeric|min:0|regex:/^\d+(\.\d+)?$/',
            'tax_model' => $isEventOrBroker ? 'nullable' : 'required',
            'unit_price' => $isEventOrBroker ? 'nullable|numeric|min:0|regex:/^\d+(\.\d+)?$/' : ($notSellable ? 'required|numeric|min:0|regex:/^\d+(\.\d+)?$/' : 'required|numeric|gt:0|regex:/^\d+(\.\d+)?$/'),
            'discount' => $isEventOrBroker ? 'nullable' : ('required' . '|' . 'numeric' . '|' . 'gt' . ':-1' . '|' . 'regex:/^\d+(\.\d+)?$/'),
            'shipping_cost' => 'required_if' . ':' . 'product_type' . ',==,' . 'physical' . '|' . 'numeric' . '|' . 'gt' . ':-1' . '|' . 'regex:/^\d+(\.\d+)?$/',
            'code' => 'required' . '|' . 'regex:/^[a-zA-Z0-9]+$/' . '|' . 'min' . ':6|' . 'max' . ':20|' . 'unique' . ':products',
            'minimum_order_qty' => ($isEventOrBroker || $this['product_type'] !== 'physical') ? 'nullable|numeric|min:1|regex:/^\d+(\.\d+)?$/' : ('required' . '|' . 'numeric' . '|' . 'min' . ':1' . '|' . 'regex:/^\d+(\.\d+)?$/'),
            'brochure' => 'nullable|mimes:pdf,doc,docx,zip,jpg,jpeg,png|max:20480',
            'book_tickets_url' => 'nullable|string|max:500',
            'view_floorplan_url' => 'nullable|string|max:500',
            'sponsor_exhibit_url' => 'nullable|string|max:500',
            'event_days' => 'nullable|string|max:50',
            'event_industry_brands' => 'nullable|string|max:50',
            'event_speakers_count' => 'nullable|string|max:50',
            'event_audience' => 'nullable|string|max:50',
            'open_account_url' => $this['product_type'] == 'broker' ? 'required|string|max:500' : 'nullable|string|max:500',
            'contact_broker_url' => $this['product_type'] == 'broker' ? 'required|string|max:500' : 'nullable|string|max:500',
            // --- ADDED TIER VALIDATION ---

        ];
        if (!isset($this['existing_thumbnail'])) {
            $rules['image'] = 'required';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'image' . '.' . 'required' => translate('product_thumbnail_is_required!'),
            'categories' . '.' . 'required' => translate('category_is_required!'),
            'categories' . '.' . 'min' => translate('category_is_required!'),
            'unit' . '.' . 'required_if' => translate('unit_is_required!'),
            'code.max' => translate('please_ensure_your_code_does_not_exceed_20_characters'),
            'code.min' => translate('code_with_a_minimum_length_requirement_of_6_characters'),
            'minimum_order_qty' . '.' . 'required' => translate('minimum_order_quantity_is_required!'),
            'minimum_order_qty' . '.' . 'min' => translate('minimum_order_quantity_must_be_positive!'),
            'preview_file' . '.' . 'mimes' => 'Only PDF, MP4, MP3 files are allowed for Product Preview File',
            'preview_file' . '.' . 'max' => 'File size exceeds the maximum limit of 10MB!',
            'digital_file_ready.required_if' => translate('ready_product_upload_is_required!'),
            'digital_file_ready.required' => translate('ready_product_upload_is_required!'),
            'digital_file_ready.mimes' => translate('ready_product_upload_must_be_a_file_of_type') . ': pdf, zip, jpg, jpeg, png, gif.',
            'digital_file_ready.max' => translate('ready_product_file_size_exceeds_the_maximum_limit') . ' (50MB)!',
            'digital_files.*.mimes' => translate('ready_product_upload_must_be_a_file_of_type') . ': pdf, zip, jpg, jpeg, png, gif.',
            'digital_product_type' . '.' . 'required_if' => translate('digital_product_type_is_required!'),
            'shipping_cost' . '.' . 'required_if' => translate('shipping_cost_is_required!'),
            'current_stock' . '.' . 'required_if' => translate('current_stock_quantity_is_required!'),
            'listing_type' . '.' . 'in' => translate('listing_type_must_be_product_for_physical_products!'),
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this['tax'] < 0) {
                    $validator->errors()->add(
                        'tax', translate('tax_can_not_be_less_than_zero') . '!'
                    );
                }

                if (!$this->has('colors_active') && !$this->file('images') && !$this->has('existing_images')) {
                    $validator->errors()->add(
                        'images', translate('product_images_is_required') . '!'
                    );
                }

                if (getWebConfig(name: 'product_brand') && empty($this->brand_id) && $this['product_type'] == 'physical') {
                    $validator->errors()->add(
                        'brand_id', translate('brand_is_required') . '!'
                    );
                }

                $isEvent = $this['product_type'] === 'event';
                $isBroker = $this['product_type'] === 'broker';
                $notSellable = $isEvent || $isBroker;

                if ($notSellable) {
                    if (empty($this['external_url'])) {
                        $validator->errors()->add(
                            'external_url', translate('external_website_url_is_required') . '!'
                        );
                    } elseif (!filter_var($this['external_url'], FILTER_VALIDATE_URL)) {
                        $validator->errors()->add(
                            'external_url', translate('external_website_url_must_be_a_valid_url') . '!'
                        );
                    }
                }

                if ($isBroker) {
                    foreach (['open_account_url', 'contact_broker_url'] as $requiredBrokerField) {
                        if (empty($this[$requiredBrokerField])) {
                            $validator->errors()->add(
                                $requiredBrokerField, translate($requiredBrokerField . '_is_required') . '!'
                            );
                        } elseif (!filter_var($this[$requiredBrokerField], FILTER_VALIDATE_URL)) {
                            $validator->errors()->add(
                                $requiredBrokerField, translate($requiredBrokerField . '_must_be_a_valid_url') . '!'
                            );
                        }
                    }
                }

                foreach (['book_tickets_url', 'view_floorplan_url', 'sponsor_exhibit_url'] as $optionalUrlField) {
                    if (!empty($this[$optionalUrlField]) && !filter_var($this[$optionalUrlField], FILTER_VALIDATE_URL)) {
                        $validator->errors()->add(
                            $optionalUrlField, translate($optionalUrlField . '_must_be_a_valid_url') . '!'
                        );
                    }
                }

                if (!$notSellable && $this['product_type'] == 'physical' && $this['unit_price'] <= $this->getDiscountAmount(price: $this['unit_price'] ?? 0, discount: $this['discount'], discountType: $this['discount_type'])) {
                    $validator->errors()->add(
                        'unit_price', translate('discount_can_not_be_more_or_equal_to_the_price') . '!'
                    );
                }

                $enIndex = is_array($this['lang']) ? array_search('en', $this['lang']) : false;
                if ($enIndex === false || !isset($this['name'][$enIndex]) || is_null($this['name'][$enIndex])) {
                    $validator->errors()->add(
                        'name', translate('name_field_is_required') . '!'
                    );
                }

                $productImagesCount = 0;
                if ($this->has('colors_active') && $this->has('colors') && count($this['colors']) > 0) {
                    foreach ($this['colors'] as $color) {
                        $color_ = str_replace('#', '', $color);
                        $image = 'color_image_' . $color_;
                        if ($this->file($image)) {
                            $productImagesCount++;
                        } else if ($this->has($image)) {
                            $productImagesCount++;
                        }

                    }
                    if ($productImagesCount != count($this['colors'])) {
                        $validator->errors()->add(
                            'images', translate('color_images_is_required') . '!'
                        );
                    }
                }

                if ($this['product_type'] == 'physical' && ($this->has('colors') || ($this->has('choice_attributes') && count($this['choice_attributes']) > 0))) {
                    foreach ($this->all() as $requestKey => $requestValue) {
                        if (str_contains($requestKey, 'sku_')) {
                            if (empty($this[$requestKey])) {
                                $validator->errors()->add(
                                    'sku_error', translate('Variation_SKU_are_required') . '!'
                                );
                            }
                        }

                        if (str_contains($requestKey, 'price_')) {
                            if (empty($this[$requestKey]) || $this[$requestKey] < 0) {
                                $validator->errors()->add(
                                    'variation_price', translate('Variation_price_are_required') . '!'
                                );
                            } else if ($this[$requestKey] <= $this->getDiscountAmount(price: $this[$requestKey] ?? 0, discount: $this['discount'], discountType: $this['discount_type'])) {
                                $validator->errors()->add(
                                    'variation_price', translate('discount_can_not_be_more_or_equal_to_the_variation_price') . '!'
                                );
                            }
                        }
                    }
                }

                if ($this['product_type'] == 'digital') {
                    $digitalProductVariationCount = 0;
                    if ($this['extensions_type'] && count($this['extensions_type']) > 0) {
                        $options = [];
                        foreach ($this['extensions_type'] as $type) {
                            $name = 'extensions_options_' . $type;
                            $my_str = implode('|', $this[$name]);
                            $options[$type] = explode(',', $my_str);
                        }

                        foreach ($options as $arrayKey => $array) {
                            foreach ($array as $key => $value) {
                                if ($value) {
                                    $digitalProductVariationCount++;
                                }
                            }
                        }

                        if ($digitalProductVariationCount == 0) {
                            $validator->errors()->add(
                                'variation_error', translate('Digital_Product_variations_are_required') . '!'
                            );
                        }

                        if ($this['digital_product_type'] == 'ready_product' && empty($this['digital_files'])) {
                            $validator->errors()->add(
                                'files', translate('Digital_files_are_required') . '!'
                            );
                        }

                        if ($this['digital_files'] && $digitalProductVariationCount != count($this['digital_files'])) {
                            $validator->errors()->add(
                                'files', translate('Digital_files_are_required') . '!'
                            );
                        }

                        if ($this->has('digital_product_sku') && empty($this['digital_product_sku'])) {
                            $validator->errors()->add(
                                'sku_error', translate('Digital_SKU_are_required') . '!'
                            );
                        } elseif ($this->has('digital_product_sku') && !empty($this['digital_product_sku'])) {
                            foreach ($this['digital_product_sku'] as $digitalSKU) {
                                if (empty($digitalSKU)) {
                                    $validator->errors()->add(
                                        'sku_error', translate('Digital_SKU_are_required') . '!'
                                    );
                                }
                            }
                        }

                    } else {
                        if ($this['digital_product_type'] == 'ready_product' && empty($this['digital_file_ready'])) {
                            $validator->errors()->add(
                                'files', translate('Digital_files_are_required') . '!'
                            );
                        }
                    }

                    if ($this->has('digital_product_price') && !empty($this['digital_product_price'])) {
                        foreach ($this['digital_product_price'] as $digitalPrice) {
                            if (empty($digitalPrice) || $digitalPrice < 0) {
                                $validator->errors()->add(
                                    'variation_price', translate('Digital_variation_price_are_required') . '!'
                                );
                            } else if ($digitalPrice <= $this->getDiscountAmount(price: $digitalPrice, discount: $this['discount'], discountType: $this['discount_type'])) {
                                $validator->errors()->add(
                                    'variation_price', translate('discount_can_not_be_more_or_equal_to_the_digital_variation_price') . '!'
                                );
                            }
                        }
                    }
                }
            }
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $this->errorProcessor($validator)], 422));
    }
}
