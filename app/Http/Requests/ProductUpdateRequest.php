<?php

namespace App\Http\Requests;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Traits\CalculatorTrait;
use App\Traits\ResponseHandler;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductUpdateRequest extends FormRequest
{
    use CalculatorTrait, ResponseHandler;

    protected $stopOnFirstFailure = true;

    public function __construct(
        private readonly ProductRepositoryInterface $productRepo
    )
    {
    }

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
            'digital_product_type' => 'required_if:product_type,==,digital',
            'delivery_mode' => 'nullable|in:auto,manual',
            'preview_file' => 'nullable|file|mimes:pdf,mp4,mp3|max:10240',
            'digital_file_ready' => 'nullable|mimes:jpg,jpeg,png,gif,zip,pdf|max:51200',
            'digital_files.*' => 'nullable|mimes:jpg,jpeg,png,gif,zip,pdf|max:51200',
            'unit' => 'required_if:product_type,==,physical',
            'current_stock' => 'required_if:product_type,==,physical|numeric|min:0',
            'listing_type' => $this['product_type'] === 'physical' ? 'nullable|in:product' : 'nullable|in:product,event,broker',
            'tax' => $isEventOrBroker ? 'nullable|numeric|min:0|regex:/^\d+(\.\d+)?$/' : 'required|numeric|min:0|regex:/^\d+(\.\d+)?$/',
            'tax_model' => $isEventOrBroker ? 'nullable' : 'required',
            'unit_price' => $isEventOrBroker ? 'nullable|numeric|min:0|regex:/^\d+(\.\d+)?$/' : ($notSellable ? 'required|numeric|min:0|regex:/^\d+(\.\d+)?$/' : 'required|numeric|gt:0|regex:/^\d+(\.\d+)?$/'),
            'discount' => $isEventOrBroker ? 'nullable' : 'required|numeric|gt:-1|regex:/^\d+(\.\d+)?$/',
            'shipping_cost' => 'required_if:product_type,==,physical|numeric|gt:-1|regex:/^\d+(\.\d+)?$/',
            'minimum_order_qty' => ($isEventOrBroker || $this['product_type'] !== 'physical') ? 'nullable|numeric|min:1|regex:/^\d+(\.\d+)?$/' : 'required|numeric|min:1|regex:/^\d+(\.\d+)?$/',
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
            'code' => [
                'required',
                'regex:/^[a-zA-Z0-9]+$/',
                'min:6',
                'max:20',
                Rule::unique('products', 'code')->ignore($this->route('id'), 'id'),
            ],
        ];

        if (!auth('admin')->check()) {
            $rules['vendor_tier_id'] = [
                'required',
                'integer',
                'regex:/^\d+$/',
                // Ensure the tier exists, belongs to the seller, and is active/trial
                Rule::exists('vendor_tiers', 'id')->where(function ($query) {
                    $query->where('seller_id', auth('seller')->id())
                          ->whereIn('status', ['active', 'trial'])
                          ->where('end_date', '>=', now());
                }),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required!',
            'categories.required' => 'category  is required!',
            'categories.min' => 'category  is required!',
            'unit.required_if' => 'Unit  is required!',
            'code.max' => translate('please_ensure_your_code_does_not_exceed_20_characters'),
            'code.min' => translate('code_with_a_minimum_length_requirement_of_6_characters'),
            'minimum_order_qty.required' => 'Minimum order quantity is required!',
            'minimum_order_qty.min' => 'Minimum order quantity must be positive!',
            'preview_file.mimes' => 'Only PDF, MP4, MP3 files are allowed for Product Preview File',
            'preview_file.max' => 'File size exceeds the maximum limit of 10MB!',
            'digital_file_ready.mimes' => 'Ready product upload must be a file of type: pdf, zip, jpg, jpeg, png, gif.',
            'digital_file_ready.max' => 'Ready product file size exceeds the maximum limit of 50MB!',
            'digital_files.*.mimes' => 'Ready product upload must be a file of type: pdf, zip, jpg, jpeg, png, gif.',
            'digital_product_type.required_if' => 'Digital product type is required!',
            'shipping_cost.required_if' => 'Shipping Cost is required!',
            'current_stock.required_if' => 'Current stock quantity is required!',
            'listing_type.in' => 'Listing type must be Product for physical products!',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {

                $description = $this->input('description');

                if (is_array($description)) {
                    $first = reset($description);
                    $cleanedDescription = is_string($first) ? trim(strip_tags($first)) : null;
                } else {
                    $cleanedDescription = is_string($description) ? trim(strip_tags($description)) : null;
                }

                if (empty($cleanedDescription)) {
                    $validator->errors()->add(
                        'description', translate('Product_description_is_required') . '!'
                    );
                }


                if ($this['tax'] < 0) {
                    $validator->errors()->add(
                        'tax', translate('tax_can_not_be_less_than_zero') . '!'
                    );
                }

                $product = $this->productRepo->getFirstWhere(params: ['id' => $this->route('id')], relations: ['digitalVariation']);
                $productImages = json_decode($product['images']);

                // Images are required only if the product has no gallery images AND no thumbnail
                if (!$this->has('colors_active') && !$this->file('images') && empty($productImages) && empty($product->thumbnail)) {
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

                if (!$notSellable && $this['product_type'] == 'physical' && $this['unit_price'] <= $this->getDiscountAmount(price: $this['unit_price'], discount: $this['discount'], discountType: $this['discount_type'])) {
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

                if ($this->has('colors_active') && $this->has('colors') && count($this['colors']) > 0) {
                    $databaseColorImages = $product['color_image'] ? json_decode($product['color_image'], true) : [];

                    $databaseColorImages =collect($databaseColorImages)
                        ->filter(fn($item) => !is_null($item['color']))
                        ->unique('color')
                        ->values()->toArray();

                    if (!$databaseColorImages) {
                        foreach ($productImages as $image) {
                            $databaseColorImages[] = ['color' => null, 'image_name' => $image];
                        }
                    }
                    $databaseColorImagesFinal = [];
                    if ($databaseColorImages) {
                        foreach ($databaseColorImages as $colorImage) {
                            if ($colorImage['color']) {
                                $databaseColorImagesFinal[] = $colorImage['color'];
                            }
                        }
                    }
                    $inputColors = [];
                    foreach ($this['colors'] as $color) {
                        $inputColors[] = str_replace('#', '', $color);
                    }
                    $differentColor = array_diff($databaseColorImagesFinal, $inputColors);
                    $colorImageRequired = [];
                    if ($databaseColorImages) {
                        foreach ($databaseColorImages as $colorImage) {
                            if ($colorImage['color'] != null && !in_array($colorImage['color'], $differentColor)) {
                                $colorImageRequired[] = [
                                    'color' => $colorImage['color'],
                                    'image_name' => $colorImage['image_name'],
                                ];
                            }
                        }
                    }

                    foreach ($inputColors as $color) {
                        if (!in_array($color, $databaseColorImagesFinal)) {
                            $colorImageIndex = 'color_image_' . $color;
                            if ($this->file($colorImageIndex)) {
                                $colorImageRequired[] = ['color' => $color, 'image_name' => rand(11111, 99999)];
                            }
                        }
                    }

                    if (count($colorImageRequired) != count($this['colors'])) {
                        $validator->errors()->add(
                            'images', translate('Color_images_is_required')
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
                    $isExistingReadyProduct = $product['product_type'] == 'digital'
                        && $product['digital_product_type'] == 'ready_product';
                    $allDigitalVariation = $product->digitalVariation->pluck('variant_key')->toArray();

                    $digitalProductVariationCount = 0;
                    $fileTypeOptions = [];
                    $digitalVariationCombinations = [];
                    if ($this['extensions_type'] && count($this['extensions_type']) > 0) {
                        foreach ($this['extensions_type'] as $type) {
                            $name = 'extensions_options_' . $type;
                            $my_str = implode('|', $this[$name]);
                            $fileTypeOptions[$type] = explode(',', $my_str);
                        }

                        foreach ($fileTypeOptions as $arrayKey => $array) {
                            foreach ($array as $key => $value) {
                                if ($value) {
                                    $digitalVariationCombinations[] = trim($arrayKey.'-'.preg_replace('/\s+/', '-', $value));
                                    $digitalProductVariationCount++;
                                }
                            }
                        }
                        $differenceFromDB = array_diff($allDigitalVariation, $digitalVariationCombinations);
                        $differenceFromRequest = array_diff($digitalVariationCombinations, $allDigitalVariation);
                        $newCombinations = array_merge($differenceFromDB, $differenceFromRequest);

                        if ($this['digital_product_type'] == 'ready_product') {
                            foreach ($newCombinations as $newCombination) {
                                if (in_array($newCombination, $digitalVariationCombinations) && empty($this['digital_files'][str_replace('-', '_', $newCombination)])) {
                                    $validator->errors()->add(
                                        'files', translate('Digital_files_are_required_for') . ' ' . str_replace(' ', '-', ucwords(str_replace('-', ' ', $newCombination)))
                                    );
                                }
                            }
                        }

                        if ($digitalProductVariationCount == 0) {
                            $validator->errors()->add(
                                'variation_error', translate('Digital_Product_variations_are_required') . '!'
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
                    }

                    if ($this['digital_product_type'] == 'ready_product') {
                        foreach ($product->digitalVariation as $variationItem) {
                            if ((empty($variationItem['file'])) && in_array($variationItem['variant_key'], $digitalVariationCombinations) && empty($this['digital_files'][str_replace('-', '_', $variationItem['variant_key'])])) {
                                $validator->errors()->add(
                                    'files', translate('Digital_files_are_required_for') . ' ' . str_replace(' ', '-', ucwords(str_replace('-', ' ', $variationItem['variant_key'])))
                                );
                            }
                        }

                        // Legacy ready-products may not have a stored file. Allow metadata
                        // updates, but still require a file when switching into ready-product.
                        if (!$isExistingReadyProduct
                            && empty($product['digital_file_ready'])
                            && !$this->hasFile('digital_file_ready')
                            && !$this->hasFile('digital_files')
                        ) {
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
