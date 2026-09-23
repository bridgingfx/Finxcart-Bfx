"use strict";

/**
 * Product Add/Update Validation Service
 * This file contains client-side validation logic for product add/update forms
 */

// ==================== ERROR DISPLAY FUNCTIONS ====================
// showFieldError / clearFieldError now live in form-validation-core.js
// (loaded globally by layouts.vendor.app before this file) so every Vendor
// form shares one implementation instead of each script defining its own.
// This file additionally handles select2/summernote/zip-uploader placement,
// which the shared core's simpler version doesn't need to know about, so
// keep a local override with those extra cases.
function showFieldError(fieldElement, errorMessage) {
    let $field = $(fieldElement);
    let $formGroup = $field.closest('.form-group');

    // Remove existing error
    $formGroup.find('.text-danger.validation-error').remove();
    $field.removeClass('is-invalid');

    if (errorMessage) {
        // Add error styling
        $field.addClass('is-invalid');

        // Add error message
        let errorHtml = `<span class="text-danger validation-error d-block mt-1" style="font-size: 12px;">${errorMessage}</span>`;

        // For select2 fields, add error after the select2 container
        if ($field.hasClass('js-select2-custom') || $field.hasClass('select2-hidden-accessible')) {
            let $select2Container = $field.next('.select2-container');
            if ($select2Container.length) {
                $select2Container.after(errorHtml);
            } else {
                $field.after(errorHtml);
            }
        } else if ($field.hasClass('summernote')) {
            // For summernote, add error after the note-editor
            let $noteEditor = $field.next('.note-editor');
            if ($noteEditor.length) {
                $noteEditor.after(errorHtml);
            } else {
                $field.after(errorHtml);
            }
        } else if ($field.attr('type') === 'file') {
            // For file inputs, add error after the upload wrapper
            let $uploadDiv = $field.closest('.custom_upload_input');
            if ($uploadDiv.length) {
                $uploadDiv.after(errorHtml);
            } else {
                let $zipUploader = $field.closest('.image-uploader');
                if ($zipUploader.length) {
                    $zipUploader.after(errorHtml);
                } else {
                    $field.after(errorHtml);
                }
            }
        } else {
            $field.after(errorHtml);
        }
    }
}

/**
 * Clear validation error message for a field
 * @param {string|jQuery} fieldElement - Field selector or jQuery object
 */
function clearFieldError(fieldElement) {
    let $field = $(fieldElement);
    let $formGroup = $field.closest('.form-group');

    $formGroup.find('.text-danger.validation-error').remove();
    $field.removeClass('is-invalid');

    // Also clear file input errors
    if ($field.attr('type') === 'file') {
        let $uploadDiv = $field.closest('.custom_upload_input');
        if ($uploadDiv.length) {
            $uploadDiv.parent().find('.text-danger.validation-error').remove();
        } else {
            let $zipUploader = $field.closest('.image-uploader');
            if ($zipUploader.length) {
                $zipUploader.parent().find('.text-danger.validation-error').remove();
            }
        }
    }
}

// ==================== BASIC FIELD VALIDATION FUNCTIONS ====================

function validateProductName() {
    let productName = $('.product-title-default-language').val();
    if (!productName || productName.trim() === '') {
        showFieldError('.product-title-default-language', 'Product name is required');
        return false;
    }
    clearFieldError('.product-title-default-language');
    return true;
}

function validateProductDescription() {
    // Validation for description length is now handled by VendorTierService
    // We just clear errors here.
    clearFieldError('.product-description-default-language');
    return true;
}

function validateProductType() {
    let $typeInputs = $('.product-type-checkbox');
    if ($typeInputs.length === 0) return true;

    if ($typeInputs.filter(':checked').length === 0) {
        showFieldError('.product-type-checkbox:first', 'Please select a product type');
        return false;
    }
    clearFieldError('.product-type-checkbox:first');
    return true;
}

function validateCategory() {
    // Product category is now assigned via the category-row repeater
    // (#category-rows-wrapper), which mirrors its selections into
    // hidden input.category-hidden-input[name="categories[]"] fields.
    let $wrapper = $('#category-rows-wrapper');

    // If the repeater isn't on this page, skip.
    if ($wrapper.length === 0) return true;

    let categoryIds = $('input.category-hidden-input[name="categories[]"]');
    if (categoryIds.length === 0) {
        showFieldError('#category-rows-wrapper', 'Please select at least one category');
        return false;
    }
    clearFieldError('#category-rows-wrapper');
    return true;
}

function validateSubCategory() {
    let $subSelect = $('#sub-category-select');
    
    // If the element doesn't exist (maybe removed dynamically), skip
    if ($subSelect.length === 0) return true;

    let subCategoryId = $subSelect.val();
    
    // Check for empty, null, or "0". 
    // We check if options exist > 1 to ensure there are actually subcategories to choose from
    if ($subSelect.find('option').length > 1) {
        if (!subCategoryId || subCategoryId === '' || subCategoryId === 'null' || subCategoryId === '0') {
            showFieldError('#sub-category-select', 'Please select a sub-category');
            return false;
        }
    }
    
    clearFieldError('#sub-category-select');
    return true;
}

function validateBrand() {
    let $brandSelect = $('select[name="brand_id"]');
    // Only validate if the brand select is visible and required
    if ($brandSelect.closest('.physical_product_show').is(':visible') && $brandSelect.prop('required')) {
        let brandId = $brandSelect.val();
        if (!brandId || brandId === '' || brandId === 'null' || brandId === '0') {
            showFieldError('select[name="brand_id"]', 'Please select a brand');
            return false;
        }
    }
    clearFieldError('select[name="brand_id"]');
    return true;
}

function validateVendorTier() {
    let hiddenTier = $('input[name="vendor_tier_id"]');
    if (hiddenTier.length) {
        let hiddenTierValue = hiddenTier.val();
        if (!hiddenTierValue || hiddenTierValue === '') {
            return true;
        }
        if (hiddenTierValue === '0') {
            return true;
        }
    }

    let tierSelect = $('select[name="vendor_tier_id"]');
    // Check if it's a dropdown (on edit page)
    if (tierSelect.length && tierSelect.prop('required')) {
        if (!tierSelect.val() || tierSelect.val() === "" || tierSelect.val() === "null") {
            showFieldError(tierSelect, 'Please select an available tier plan');
            return false;
        }
        clearFieldError(tierSelect);
    }

    // Check for the hidden input (on add page)
    return true;
}


function validateProductSKU() {
    let sku = $('input[name="code"]').val();
    if (!sku || sku.trim() === '') {
        showFieldError('input[name="code"]', 'Product SKU is required');
        return false;
    }
    if (sku.length < 6) {
        showFieldError('input[name="code"]', 'Product SKU must be at least 6 characters');
        return false;
    }
    clearFieldError('input[name="code"]');
    return true;
}

function validateUnitPrice() {
    if (!$('input[name="unit_price"]').is(':visible')) {
        clearFieldError('input[name="unit_price"]');
        return true;
    }

    let unitPrice = $('input[name="unit_price"]').val();

    if (!unitPrice || unitPrice.trim() === '') {
        showFieldError('input[name="unit_price"]', 'Unit price is required');
        return false;
    }

    let priceValue = parseFloat(unitPrice);

    if (isNaN(priceValue)) {
        showFieldError('input[name="unit_price"]', 'Unit price must be a valid number');
        return false;
    }

    if (priceValue <= 0) {
        showFieldError('input[name="unit_price"]', 'Unit price must be greater than 0');
        return false;
    }

    // Client-side tier price validation
    if (!validateTierPrice(priceValue)) {
        return false;
    }

    clearFieldError('input[name="unit_price"]');
    return true;
}

function validateMinimumOrderQty() {
    if (!$('input[name="minimum_order_qty"]').is(':visible')) {
        clearFieldError('input[name="minimum_order_qty"]');
        return true;
    }

    let minQty = $('input[name="minimum_order_qty"]').val();
    if (!minQty || minQty.trim() === '') {
        showFieldError('input[name="minimum_order_qty"]', 'Minimum order quantity is required');
        return false;
    }
    if (parseFloat(minQty) < 1) {
        showFieldError('input[name="minimum_order_qty"]', 'Minimum order quantity must be at least 1');
        return false;
    }
    clearFieldError('input[name="minimum_order_qty"]');
    return true;
}

function validateCurrentStock() {
    let productType = $('#product_type').val();
    if (productType === 'physical') {
        let $stockInput = $('input[name="current_stock"]');
        if ($stockInput.closest('.physical_product_show').is(':visible')) {
            let currentStock = $stockInput.val();
            if (!currentStock || currentStock.trim() === '') {
                showFieldError($stockInput, 'Current stock quantity is required');
                return false;
            }
            if (parseFloat(currentStock) < 0) {
                showFieldError($stockInput, 'Current stock cannot be negative');
                return false;
            }
        }
    }
    clearFieldError('input[name="current_stock"]');
    return true;
}

function validateTax() {
    if (!$('input[name="tax"]').is(':visible')) {
        clearFieldError('input[name="tax"]');
        return true;
    }

    let tax = $('input[name="tax"]').val();
    if (!tax || tax.trim() === '') {
        showFieldError('input[name="tax"]', 'Tax amount is required');
        return false;
    }
    if (parseFloat(tax) < 0) {
        showFieldError('input[name="tax"]', 'Tax amount cannot be negative');
        return false;
    }
    clearFieldError('input[name="tax"]');
    return true;
}

function validateShippingCost() {
    let productType = $('#product_type').val();
    if (productType === 'physical') {
        let $shippingInput = $('input[name="shipping_cost"]');
        if ($shippingInput.closest('.physical_product_show').is(':visible')) {
            let shippingCost = $shippingInput.val();
            if (!shippingCost || shippingCost.trim() === '') {
                showFieldError($shippingInput, 'Shipping cost is required');
                return false;
            }
            if (parseFloat(shippingCost) < 0) {
                showFieldError($shippingInput, 'Shipping cost cannot be negative');
                return false;
            }
        }
    }
    clearFieldError('input[name="shipping_cost"]');
    return true;
}

function validateDiscount() {
    if (!$('input[name="discount"]').is(':visible')) {
        clearFieldError('input[name="discount"]');
        return true;
    }

    let discount = $('input[name="discount"]').val();
    let discountType = $('#discount_type').val();
    let unitPrice = $('input[name="unit_price"]').val();

    if (discount && discount.trim() !== '') {
        let discountValue = parseFloat(discount);

        if (discountValue < 0) {
            showFieldError('input[name="discount"]', 'Discount cannot be negative');
            return false;
        }

        if (discountType === 'percent' && discountValue > 100) {
            showFieldError('input[name="discount"]', 'Discount percentage cannot exceed 100%');
            return false;
        }

        if (discountType === 'flat' && unitPrice && parseFloat(unitPrice) > 0) {
            if (discountValue > parseFloat(unitPrice)) {
                showFieldError('input[name="discount"]', 'Discount amount cannot exceed unit price');
                return false;
            }
        }
    }

    clearFieldError('input[name="discount"]');
    return true;
}

function validatePreviewFile() {
    const previewInput = $('input[name="preview_file"]');
    if (!previewInput.length) {
        return true;
    }

    const file = previewInput[0].files && previewInput[0].files[0];
    if (!file) {
        clearFieldError(previewInput);
        return true;
    }

    const allowedExtensions = ['pdf', 'mp4', 'mp3'];
    const allowedMimeTypes = ['application/pdf', 'video/mp4', 'audio/mpeg', 'audio/mp3'];
    const extension = (file.name.split('.').pop() || '').toLowerCase();
    const mimeType = (file.type || '').toLowerCase();

    if (!allowedExtensions.includes(extension) || (mimeType && !allowedMimeTypes.includes(mimeType))) {
        showFieldError(previewInput, 'Only PDF, MP4, MP3 files are allowed for Product Preview File');
        return false;
    }

    clearFieldError(previewInput);
    return true;
}

function validateProductThumbnail() {
    let thumbnailInput = $('input[name="image"]');
    let hasFile = thumbnailInput[0].files && thumbnailInput[0].files.length > 0;

    let hasPreview = $('#pre_img_viewer').attr('src') && !$('#pre_img_viewer').hasClass('d-none') && $('#pre_img_viewer').attr('src') !== '';

    if (!hasFile && !hasPreview) {
        showFieldError('input[name="image"]', 'Product thumbnail image is required');
        return false;
    }
    clearFieldError('input[name="image"]');
    return true;
}

// ==================== TIER PLAN VALIDATION FUNCTIONS ====================

function validateTierPrice(unitPrice) {
    let hiddenTier = $('input[name="vendor_tier_id"]');
    if (hiddenTier.length) {
        let hiddenTierValue = hiddenTier.val();
        if (!hiddenTierValue || hiddenTierValue === '') {
            return true;
        }
        if (hiddenTierValue === '0') {
            return true;
        }
    }

    let tierPlanName = $("#tier-plan-name").data("value");
    let tierPriceMinValue = $("#tier-price-min").data("value");
    let tierPriceMaxValue = $("#tier-price-max").data("value");

    let tierPriceMin = (tierPriceMinValue !== "" && tierPriceMinValue !== null) ? parseFloat(tierPriceMinValue) : null;
    let tierPriceMax = (tierPriceMaxValue !== "" && tierPriceMaxValue !== null) ? parseFloat(tierPriceMaxValue) : null;

    if (!tierPlanName || tierPlanName === "") return true; // No tier, skip validation
    if (isNaN(unitPrice)) return true; // Handled by validateUnitPrice

    if (tierPriceMin !== null && tierPriceMax !== null) {
        if (unitPrice < tierPriceMin || unitPrice > tierPriceMax) {
            showFieldError('input[name="unit_price"]',
                tierPlanName + ' tier: Price must be between $' +
                tierPriceMin.toFixed(2) + ' and $' + tierPriceMax.toFixed(2));
            return false;
        }
    }
    else if (tierPriceMin !== null && tierPriceMax === null) {
        if (unitPrice < tierPriceMin) {
            showFieldError('input[name="unit_price"]',
                tierPlanName + ' tier: Price must be $' + tierPriceMin.toFixed(2) + ' or higher');
            return false;
        }
    }
    else if (tierPriceMin === null && tierPriceMax !== null) {
        if (unitPrice > tierPriceMax) {
            showFieldError('input[name="unit_price"]',
                tierPlanName + ' tier: Price must be under $' + tierPriceMax.toFixed(2));
            return false;
        }
    }

    return true;
}

function validateTierDescription() {
    let hiddenTier = $('input[name="vendor_tier_id"]');
    if (hiddenTier.length) {
        let hiddenTierValue = hiddenTier.val();
        if (!hiddenTierValue || hiddenTierValue === '') {
            return true;
        }
        if (hiddenTierValue === '0') {
            return true;
        }
    }

    let tierPlanName = $("#tier-plan-name").data("value");
    if (!tierPlanName || tierPlanName === "") return true;

    let description = $('.product-description-default-language').summernote('code');
    let plainText = $('<div>').html(description).text().trim();
    let descLength = plainText.length;

    if (tierPlanName === 'Basic' && descLength > 500) {
        showFieldError('.product-description-default-language',
            'Basic tier: Description maximum 500 characters. Current: ' + descLength + ' characters');
        return false;
    }

    clearFieldError('.product-description-default-language');
    return true;
}

function validateTierPlanRestrictions() {
    // BYPASS: Check if user is admin
    let hiddenTier = $('input[name="vendor_tier_id"]');
    if(hiddenTier.length && hiddenTier.val() === '0') {
        return true; 
    }
    
    // This is a master check. We already validate price on blur.
    // We just need to check description here.
    let isValid = true;
    if (!validateTierDescription()) isValid = false;

    // We can re-check price just in case
    let unitPrice = parseFloat($('input[name="unit_price"]').val());
    if (!validateTierPrice(unitPrice)) isValid = false;

    return isValid;
}

// ==================== MAIN VALIDATION FUNCTION ====================

function validateAllFields() {
    let isValid = true;

    console.log("--- Starting Validation Check ---");

    if (!validateProductName()) { console.log("Failed: Product Name"); isValid = false; }
    if (!validateProductType()) { console.log("Failed: Product Type"); isValid = false; }
    if (!validateCategory()) { console.log("Failed: Category"); isValid = false; }
    if (!validateSubCategory()) { console.log("Failed: Sub-Category"); isValid = false; }
    if (!validateBrand()) { console.log("Failed: Brand"); isValid = false; }
    if (!validateVendorTier()) { console.log("Failed: Vendor Tier"); isValid = false; }
    if (!validateProductSKU()) { console.log("Failed: SKU"); isValid = false; }
    if (!validateUnitPrice()) { console.log("Failed: Unit Price"); isValid = false; }
    if (!validateMinimumOrderQty()) { console.log("Failed: Min Order Qty"); isValid = false; }
    if (!validateCurrentStock()) { console.log("Failed: Current Stock"); isValid = false; }
    if (!validateTax()) { console.log("Failed: Tax"); isValid = false; }
    if (!validateShippingCost()) { console.log("Failed: Shipping Cost"); isValid = false; }
    if (!validateDiscount()) { console.log("Failed: Discount"); isValid = false; }
    if (!validatePreviewFile()) { console.log("Failed: Preview File"); isValid = false; }
    if (!validateProductThumbnail()) { console.log("Failed: Thumbnail"); isValid = false; }
    if (!validateTierDescription()) { console.log("Failed: Description"); isValid = false; }

    console.log("--- Validation Result: " + isValid + " ---");
    return isValid;
}

// ==================== REAL-TIME VALIDATION EVENT HANDLERS ====================

$(document).ready(function() {

    $('.product-title-default-language').on('blur', validateProductName);
    
    $('select[name="category_id"]').on('change', function() {
        validateCategory();
        // Reset Sub-category error when main category changes
        clearFieldError('#sub-category-select');
    });

    $('#sub-category-select').on('change', validateSubCategory);
    
    $('select[name="brand_id"]').on('change', validateBrand);
    $('select[name="vendor_tier_id"]').on('change', validateVendorTier);
    $('input[name="code"]').on('blur', validateProductSKU);
    $('input[name="unit_price"]').on('blur', validateUnitPrice);
    $('input[name="minimum_order_qty"]').on('blur', validateMinimumOrderQty);
    $('input[name="current_stock"]').on('blur', validateCurrentStock);
    $('input[name="tax"]').on('blur', validateTax);
    $('input[name="shipping_cost"]').on('blur', validateShippingCost);
    $('input[name="discount"]').on('blur', validateDiscount);
    $('#discount_type').on('change', validateDiscount);

    $('input, select, textarea').on('focus', function() { clearFieldError(this); });
    $('.summernote').on('summernote.focus', function() { clearFieldError(this); });
    $('input[type="file"]').on('change', function() { clearFieldError(this); });

    $('.product-description-default-language').on('summernote.blur', validateTierDescription);
});

// ==================== BACKEND VALIDATION ERROR DISPLAY ====================

function displayTierValidationErrors(errors) {
    errors.forEach(function(error) {
        let errorCode = error.error_code;
        let message = error.message;

        switch(errorCode) {
            case 'name.0':
                showFieldError('.product-title-default-language', message);
                break;
            case 'price':
            case 'unit_price':
                showFieldError('input[name="unit_price"]', message);
                break;
            case 'vendor_tier_id':
                let tierField = $('select[name="vendor_tier_id"]').length
                                ? 'select[name="vendor_tier_id"]'
                                : 'input[name="vendor_tier_id"]';
                showFieldError(tierField, message);
                break;
            case 'categories':
                showFieldError('#category-rows-wrapper', message);
                break;
            case 'brand_id':
                showFieldError('select[name="brand_id"]', message);
                break;
            case 'code':
                // The SKU is normally a client-generated random string; a collision is
                // rare but the fix is always the same, so regenerate it automatically
                // instead of making the vendor notice and click "Generate Code" again.
                // Re-validate the freshly generated code immediately afterward so its
                // error clears — otherwise the "SKU is required/taken" message from the
                // old value stays on screen even though a new, valid value is now filled in.
                if (typeof generateRandomString === 'function') {
                    $('input[name="code"]').val(generateRandomString(6));
                    if (typeof generateSKUPlaceHolder === 'function') {
                        generateSKUPlaceHolder();
                    }
                    validateProductSKU();
                } else {
                    showFieldError('input[name="code"]', message);
                }
                break;
            case 'minimum_order_qty':
                showFieldError('input[name="minimum_order_qty"]', message);
                break;
            case 'tax':
                showFieldError('input[name="tax"]', message);
                break;
            case 'image':
                showFieldError('input[name="image"]', message);
                break;
            case 'preview_file':
            case 'preview_file.mimes':
            case 'preview_file.max':
                showFieldError('input[name="preview_file"]', message);
                break;
            case 'description':
                showFieldError('.product-description-default-language', message);
                break;
            case 'category':
                showFieldError('#category-rows-wrapper', message);
                break;
            case 'product_type':
                showFieldError('#product_type', message);
                break;
            case 'media':
            case 'images':
            case 'tier_plan':
                // No single form field to anchor these to — toast below is the only signal.
                break;
            default:
                let field = $(`[name="${errorCode}"]`);
                if (field.length) {
                    showFieldError(field, message);
                }
        }

        // Inline field errors can end up inside a hidden language tab, a collapsed
        // section, or scrolled off-screen, so the vendor never saw *any* feedback
        // when a field-specific case fired — only the unmapped/default errors used
        // to toast. Every error now always toasts too, so submitting never looks
        // like it silently did nothing.
        toastMagic.error(message);
    });

    let firstError = $('.is-invalid').first();
    if (firstError.length) {
        $('html, body').animate({
            scrollTop: firstError.offset().top - 100
        }, 500);
    }
}
