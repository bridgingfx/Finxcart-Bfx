"use strict";

// Debounce helper — used to stop the SKU-combination AJAX call (which serializes and
// POSTs the entire product form) from firing on every single keystroke, e.g. while
// typing the unit price. Without this, typing a 4-digit price fired 4 full-form
// requests to the server in a row, which is a real contributor to "slow while
// submitting a product" complaints even though each individual request is fast.
function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Global Variable Definitions
let elementProductTypeByID = $("#product_type");
let elementAdditionalImageColumn = $(".additional_image_column");
let elementCustomUploadInputFileByID = $(".custom-upload-input-file");
let elementDigitalProductTypeByID = $("#digital_product_type");
let elementProductColorSwitcherByID = $("#product-color-switcher");
let elementImagePathOfProductUploadIconByID = $("#image-path-of-product-upload-icon").data("path");
let messageEnterChoiceValues = $("#message-enter-choice-values").data("text");
let messageUploadImage = $("#message-upload-image").data("text");
let messageFileSizeTooBig = $("#message-file-size-too-big").data("text");
let messagePleaseOnlyInputPNGOrJPG = $("#message-please-only-input-png-or-jpg").data("text");
let messageAreYouSure = $("#message-are-you-sure").data("text");
let messageYesWord = $("#message-yes-word").data("text");
let messageNoWord = $("#message-no-word").data("text");
let messageWantAddOrUpdateThisProduct = $("#message-want-to-add-or-update-this-product").data("text");
let getSystemCurrencyCode = $("#system-currency-code").data("value");

$(document).on("ready", function() {
    // Initialize Summernote
    $(".summernote").summernote({
        height: 150,
        toolbar: [
            ["style", ["bold", "italic", "underline", "clear"]],
            ["font", ["strikethrough", "superscript", "subscript"]],
            ["fontsize", ["fontsize"]],
            ["color", ["color"]],
            ["para", ["ul", "ol", "paragraph"]],
            ["height", ["height"]]
        ],
        callbacks: {
            onChange: function(contents, $editable) {
                $(this).val(contents); 
                if ($(this).hasClass("product-description-default-language")) {
                    var textWithoutTagsAndEntities = contents.replace(/<[^>]+>|&[^;]+;/g, "");
                    var maxLength = 160;
                    if (textWithoutTagsAndEntities.length > maxLength) {
                        textWithoutTagsAndEntities = textWithoutTagsAndEntities.substring(0, maxLength);
                    }
                    $("#meta_description").val(textWithoutTagsAndEntities);
                }
            }
        }
    });

    syncListingTypeSectionVisibility();
    getProductTypeFunctionality();
    getDigitalProductTypeFunctionality();

    if ($("#product-color-switcher").prop("checked")) {
        $("#color-wise-image-area").show();
        colorWiseImageFunctionality($("#colors-selector"));
    } else {
        $("#color-wise-image-area").hide();
    }

    $(".color-var-select").select2({
        templateResult: colorCodeSelect,
        templateSelection: colorCodeSelect,
        escapeMarkup: function(m) { return m; }
    });

    function colorCodeSelect(state) {
        let colorCode = $(state.element).val();
        if (!colorCode) return state.text;
        return "<span class='color-preview' style='background-color:" + colorCode + ";'></span>" + state.text;
    }
});

// Thumbnail/gallery/color-wise product photos are routinely multi-megabyte
// camera images with no client-side resize anywhere in this form — every one of
// them gets uploaded at full size, then re-encoded to webp server-side (GD encode
// alone runs ~130-180ms per image once warm), so a product with a thumbnail + a
// handful of gallery/color images can add several seconds to a single submit.
// Downscaling oversized images in the browser before upload cuts both the upload
// time and the server-side encode cost, since the source pixels are already
// smaller. resizeImageFile/resizeFormDataImages come from shared/image-resize.js
// (must be loaded before this file) — kept in one place so this and any other
// upload-heavy form (e.g. admin vendor create) share the same fix.
function resizeProductFormDataImages(formData) {
    return resizeFormDataImages(formData);
}

// MAIN BUTTON CLICK HANDLER
let isProductSubmitInFlight = false;

$(document).ready(function() {
    // The product form has no type="submit" button — all real submission goes
    // through the AJAX handler below via the .product-add-requirements-check
    // button. But pressing Enter in any text field still triggers the browser's
    // implicit native form submission, which bypasses that handler entirely and
    // dumps the server's raw JSON response as a full-page navigation instead of
    // a styled inline error. Block that native path outright.
    $("#product_form").on("submit", function(e) {
        e.preventDefault();
    });

    $(".product-add-requirements-check").on("click", function(e) {
        console.log("Update Button Clicked");
        e.preventDefault();
        // Guards against double-submit (double-click, or clicking again while the
        // confirm dialog/AJAX is still in flight) — without this, two identical
        // requests can go out with the same generated product code; the first
        // succeeds and the second then fails validation with "code already taken",
        // which looks like a bug even though the underlying uniqueness check is correct.
        if (isProductSubmitInFlight) {
            return;
        }
        getProductAddRequirementsCheck();
    });
});

function getProductAddRequirementsCheck() {
    // 1. Sync Summernote
    $('.summernote').each(function(){
        $(this).val($(this).summernote('code'));
    });

    // 2. Run Validation
    console.log("Running Validation...");
    if (!validateAllFields()) {
        console.log("Validation Failed");
        let firstError = $('.is-invalid').first();
        if (firstError.length) {
            $('html, body').animate({ scrollTop: firstError.offset().top - 100 }, 500);
        }
        // Field-level errors can land inside a hidden language tab or a collapsed
        // section, so scrolling alone isn't always visible feedback — toast so the
        // vendor always sees *something* explaining why the submit didn't go through.
        let firstErrorMessage = $('.validation-error').first().text().trim();
        toastMagic.error(firstErrorMessage || 'Please fix the highlighted fields before submitting.');
        return;
    }
    console.log("Validation Passed");

    // 3. Prepare Messages
    let isUpdate = $("#product_form").attr("action").includes("update");
    let confirmTitle = isUpdate ? "Are you sure you want to update?" : messageAreYouSure;
    let confirmText = isUpdate ? "This will update the existing product details." : messageWantAddOrUpdateThisProduct;
    let successText = isUpdate ? "Product updated successfully!" : $("#message-product-added-successfully").data("text");

    // 4. Show Confirmation
    Swal.fire({
        title: confirmTitle,
        text: confirmText,
        type: "warning",
        showCancelButton: true,
        cancelButtonColor: "#dd3333",
        confirmButtonColor: "#377dff",
        cancelButtonText: messageNoWord,
        confirmButtonText: messageYesWord,
        reverseButtons: true
    }).then(result => {
        if (result.value) {
            if (isProductSubmitInFlight) {
                return;
            }
            isProductSubmitInFlight = true;
            // Safety net: if image resizing or the request hangs indefinitely for
            // any reason, don't leave double-submit protection stuck on forever.
            setTimeout(function () { isProductSubmitInFlight = false; }, 120000);
            console.log("User Confirmed. Sending AJAX...");

            let formData = new FormData(document.getElementById("product_form"));

            // --- FIX FOR "vendor tier id must be integer" ---
            // If the hidden value is "admin", we remove it from the payload
            // so the server receives null/nothing instead of the string "admin"
            if (formData.get("vendor_tier_id") === "admin") {
                formData.delete("vendor_tier_id");
            }

            $.ajaxSetup({
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") }
            });

            // #loading is the shared panel-wide spinner and has no text slot of its own;
            // append a one-off notice for the duration of this submit so vendors don't
            // refresh mid-upload (large images can take a while to resize + upload).
            let $loadingNotice = $('<div class="text-center text-white mt-2 fw-bold">' +
                ($("#message-do-not-refresh").data("text") || "Please wait, do not refresh or close this page...") +
                '</div>').attr("id", "product-submit-loading-notice");
            $("#loading").append($loadingNotice);
            $("#loading").fadeIn();

            resizeProductFormDataImages(formData).then(function (resizedFormData) {
                $.post({
                    url: $("#product_form").attr("action"),
                    data: resizedFormData,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        console.log("AJAX Success:", data);
                        // The server now saves the product on this same request (see
                        // ProductController::add) and hands back where to go next, instead
                        // of us re-submitting the same multipart form a second time — that
                        // used to upload every image/video/file twice over the wire.
                        sessionStorage.setItem('pending_product_stage', Date.now().toString());
                        isProductSubmitInFlight = false;
                        window.location.href = data.redirect_url || $("#product_form").attr("action");
                    },
                    complete: function() {
                        $("#loading").fadeOut();
                        $("#product-submit-loading-notice").remove();
                        isProductSubmitInFlight = false;
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("AJAX Error:", jqXHR.responseJSON);
                        $("#loading").fadeOut();

                        if (jqXHR.status === 422) {
                            let errors = jqXHR.responseJSON.errors;
                            let errorArray = [];
                            if (Array.isArray(errors)) {
                                errorArray = errors;
                            } else if (typeof errors === 'object') {
                                Object.keys(errors).forEach(function(key) {
                                    errorArray.push({ error_code: key, message: errors[key][0] });
                                });
                            } else {
                                toastMagic.error('An unknown validation error occurred.');
                            }
                            displayTierValidationErrors(errorArray);
                        } else {
                            toastMagic.error('An unexpected server error occurred. Please try again.');
                        }
                    }
                });
            }).catch(function (err) {
                console.error("Image resize failed, submitting original files:", err);
                $.post({
                    url: $("#product_form").attr("action"),
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        sessionStorage.setItem('pending_product_stage', Date.now().toString());
                        isProductSubmitInFlight = false;
                        window.location.href = data.redirect_url || $("#product_form").attr("action");
                    },
                    complete: function() {
                        $("#loading").fadeOut();
                        $("#product-submit-loading-notice").remove();
                        isProductSubmitInFlight = false;
                    },
                    error: function(jqXHR) {
                        $("#loading").fadeOut();
                        toastMagic.error('An unexpected server error occurred. Please try again.');
                    }
                });
            });
        }
    });
}

function getProductTypeFunctionality() {
    let listingType = $('input[name="listing_type"]:checked').val();

    if (listingType === "event" || listingType === "broker") {
        elementProductTypeByID.prop("disabled", true);
        $("#product_type_override").prop("disabled", false).val(listingType);

        $(".show-for-product-radio").addClass("d-none").hide();
        $(".physical_product_show").hide();
        $(".digital-product-sections-show").hide();
        $(".digitalProductVariationSetupSection").hide();
        $("#digital_product_type_show").hide();

        $(".show-for-event-or-broker-product").removeClass("d-none").show();
        $(".show-for-event-product").toggleClass("d-none", listingType !== "event").toggle(listingType === "event");
        $(".show-for-broker-product").toggleClass("d-none", listingType !== "broker").toggle(listingType === "broker");

        $("#brochure_event").prop("disabled", listingType !== "event");
        $("#brochure_broker").prop("disabled", listingType !== "broker");
        return;
    }

    elementProductTypeByID.prop("disabled", false);
    $("#product_type_override").prop("disabled", true).val("");
    $(".show-for-product-radio").removeClass("d-none").show();
    $(".show-for-event-or-broker-product").addClass("d-none").hide();
    $(".show-for-event-product").addClass("d-none").hide();
    $(".show-for-broker-product").addClass("d-none").hide();
    $("#brochure_event").prop("disabled", true);
    $("#brochure_broker").prop("disabled", true);

    let productType = elementProductTypeByID.val();
    if (productType && productType.toString() === "physical") {
        $("#digital_product_type_show").hide();
        $(".physical_product_show").show();
        elementDigitalProductTypeByID.val($("#digital_product_type option:first").val());
        $("#digital_file_ready").val("");
        $(".digital-product-sections-show").hide();
        $(".digitalProductVariationSetupSection").hide();
        elementProductColorSwitcherByIDFunctionality("reset");
    } else if (productType && (productType.toString() === "digital" || productType.toString() === "recurring" || productType.toString() === "subscription")) {
        $("#digital_product_type_show").show();
        $(".physical_product_show").hide();
        if(productType.toString() !== "digital") $(".physical_product_show").show(); 
        
        $(".digital-product-sections-show").show();
        $(".digitalProductVariationSetupSection").show();
        elementProductColorSwitcherByID.prop("checked", false);
        $("#color-wise-image-section").empty().html("");
        elementProductColorSwitcherByIDFunctionality("reset");
    }
    try {
        if (productType && productType.toString() === "physical") {
            $("#digital-product-variation-section").empty().html();
            $("#digital-product-type-choice-section .extension-choice-section").remove();
        }
    } catch (e) { console.log(e); }

    // Defensive re-sync regardless of what triggered this function, so Listing Type
    // never ends up visible while Physical is selected (or vice versa).
    syncListingTypeSectionVisibility();
}

function getDigitalProductTypeFunctionality() {
    getUpdateDigitalVariationFunctionality();
}

// Listing Type (Product/Event/Broker) only makes sense for digital listings — a
// physical stock item is never an "event" or "broker" listing. The "Listing Type"
// section itself always stays visible; for a legacy Physical product being edited,
// only the "Product" option stays visible/selected and Event/Broker are hidden
// (not the whole section). New products are always Digital, so this only matters
// when editing pre-existing physical stock.
function syncListingTypeSectionVisibility() {
    let selectedType = elementProductTypeByID.val() || "digital";
    let isPhysical = selectedType === "physical";
    let $eventBrokerOptions = $("#listing_type_event, #listing_type_broker").closest(".form-check");

    if (isPhysical) {
        $(".listing-type-input").prop("checked", false);
        $("#listing_type_product").prop("checked", true);
        $eventBrokerOptions.hide()[0] && $eventBrokerOptions.each(function () { this.style.display = "none"; });
    } else {
        $eventBrokerOptions.show()[0] && $eventBrokerOptions.each(function () { this.style.display = ""; });
    }
}

elementProductTypeByID.on("change", () => getProductTypeFunctionality());
elementDigitalProductTypeByID.on("change", () => getDigitalProductTypeFunctionality());
$(document).on("change", 'input[name="listing_type"]', () => getProductTypeFunctionality());

elementProductColorSwitcherByID.on("click", function() {
    elementProductColorSwitcherByIDFunctionality();
});

let pageLoadFirstTime = true;
function elementProductColorSwitcherByIDFunctionality(action = null) {
    if (elementProductColorSwitcherByID.prop("checked")) {
        $(".color_image_column").removeClass("d-none");
        elementAdditionalImageColumn.removeClass("col-md-9").addClass("col-md-12");
        $("#color-wise-image-area").show();
        $("#additional_Image_Section .col-md-4").addClass("col-lg-2");
    } else {
        let colors = $("#colors-selector");
        let choiceAttributes = $("#choice_attributes");

        colors.val(null).trigger("change");
        if (pageLoadFirstTime === false && action === "reset") {
            choiceAttributes.val(null).trigger("change");
            pageLoadFirstTime = false;
        }

        $(".color_image_column").addClass("d-none");
        elementAdditionalImageColumn.addClass("col-md-9").removeClass("col-md-12");
        $("#color-wise-image-area").hide();
        $("#additional_Image_Section .col-md-4").removeClass("col-lg-2");
    }

    if (!$('input[name="colors_active"]').is(":checked")) {
        $("#colors-selector").prop("disabled", true);
    } else {
        $("#colors-selector").prop("disabled", false);
    }
}

$(document).on("ready", function() {
    if (elementProductColorSwitcherByID.prop("checked")) {
        $(".color_image_column").removeClass("d-none");
        elementAdditionalImageColumn.removeClass("col-md-9").addClass("col-md-12");
        $("#additional_Image_Section .col-md-4").addClass("col-lg-2");
    } else {
        $(".color_image_column").addClass("d-none");
        elementAdditionalImageColumn.addClass("col-md-9").removeClass("col-md-12");
        $("#additional_Image_Section .col-md-4").removeClass("col-lg-2");
    }
});

$('input[name="colors_active"]').on("change", function() {
    if (!$('input[name="colors_active"]').is(":checked")) {
        $("#colors-selector").prop("disabled", true);
    } else {
        $("#colors-selector").prop("disabled", false);
    }
});

$("#choice_attributes").on("change", function() {
    $("#sku_combination").empty().html("");
    $("#customer_choice_options").empty().html("");
    $.each($("#choice_attributes option:selected"), function() {
        addMoreCustomerChoiceOption($(this).val(), $(this).text());
    });
    getUpdateSKUFunctionality();
});

$("#colors-selector").on("change", function() {
    getUpdateSKUFunctionality();
    if (elementProductColorSwitcherByID.prop("checked")) {
        colorWiseImageFunctionality($("#colors-selector"));
        $("#color-wise-image-area").show();
    } else {
        $("#color-wise-image-area").hide();
    }
});

let debouncedGetUpdateSKUFunctionality = debounce(getUpdateSKUFunctionality, 400);

$('input[name="unit_price"]').on("keyup", function() {
    let productType = elementProductTypeByID.val();
    if (productType && productType.toString() === "physical") {
        debouncedGetUpdateSKUFunctionality();
    }
    setTimeout(() => {
        $(".variation-price-input").val($(this).val());
    }, 500);
});

function getUpdateSKUFunctionality() {
    $.ajaxSetup({
        headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") }
    });

    $.ajax({
        type: "POST",
        url: $("#route-vendor-products-sku-combination").data("url"),
        data: $("#product_form").serialize(),
        success: function(data) {
            $("#sku_combination").html(data.view);
            updateProductQuantity();
            updateProductQuantityByKeyUp();
            let productType = elementProductTypeByID.val();
            if (productType && productType.toString() === "physical") {
                if (data.length > 1) {
                    $("#quantity").hide();
                } else {
                    $("#quantity").show();
                }
            }
            generateSKUPlaceHolder();
            removeSymbol();
        }
    });
}

$("#discount_type").on("change", function() {
    if ($(this).val().toString() === "flat") {
        $(".discount_amount_symbol").html(`(` + getSystemCurrencyCode + `)`).fadeIn();
    } else {
        $(".discount_amount_symbol").html("(%)").fadeIn();
    }
});

$(".action-add-more-image").on("change", function() {
    let parentDiv = $(this).closest("div");
    parentDiv.find(".delete_file_input").removeClass("d-none").fadeIn();
    addMoreImage(this, $(this).data("target-section"));
});

function addMoreImage(thisData, targetSection) {
    let $fileInputs = $(targetSection + " input[type='file']");
    let nonEmptyCount = 0;
    $fileInputs.each(function() {
        if (parseFloat($(this).prop("files").length) === 0) {
            nonEmptyCount++;
        }
    });

    uploadColorImage(thisData);

    if (nonEmptyCount === 0) {
        let datasetIndex = thisData.dataset.index + 1;
        let newHtmlData = `<div class="col-sm-12 col-md-4">
            <div class="custom_upload_input position-relative border-dashed-2 aspect-1">
                <input type="file" name="${thisData.name}" class="custom-upload-input-file action-add-more-image" data-index="${datasetIndex}" data-imgpreview="additional_Image_${datasetIndex}"
                    accept=".jpg, .webp, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" data-target-section="${targetSection}">
                <span class="delete_file_input delete_file_input_section btn btn-outline-danger btn-sm square-btn d-none">
                    <i class="fi fi-rr-trash"></i>
                </span>
                <div class="img_area_with_preview position-absolute z-index-2 border-0">
                    <img alt="" id="additional_Image_${datasetIndex}" class="h-auto aspect-1 bg-white d-none" src="img">
                </div>
                <div class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                    <div class="d-flex flex-column justify-content-center align-items-center">
                        <img src="` + elementImagePathOfProductUploadIconByID + `" class="w-50" alt="">
                        <h3 class="text-muted">` + messageUploadImage + `</h3>
                    </div>
                </div>
            </div>
        </div>`;
        $(targetSection).append(newHtmlData);
    }

    elementCustomUploadInputFileByID.on("change", function() {
        if (parseFloat($(this).prop("files").length) !== 0) {
            let parentDiv = $(this).closest("div");
            parentDiv.find(".delete_file_input").fadeIn();
        }
    });

    $(".delete_file_input_section").click(function() {
        $(this).closest("div").parent().remove();
    });

    if (elementProductColorSwitcherByID.prop("checked")) {
        $("#additional_Image_Section .col-md-4").addClass("col-lg-2");
    } else {
        $("#additional_Image_Section .col-md-4").removeClass("col-lg-2");
    }

    $(".action-add-more-image").on("change", function() {
        let parentDiv = $(this).closest("div");
        parentDiv.find(".delete_file_input").removeClass("d-none").fadeIn();
        addMoreImage(this, $(this).data("target-section"));
    });

    $(".onerror-add-class-d-none").on("error", function() {
        $(this).addClass("d-none");
    });

    onErrorImage();
}

$(function() {
    $("#coba").spartanMultiImagePicker({
        fieldName: "images[]",
        maxCount: 15,
        rowHeight: "auto",
        groupClassName: "col-6 col-md-4 col-lg-3 col-xl-2",
        maxFileSize: "",
        placeholderImage: {
            image: $("#image-path-of-product-upload-icon-two").data("path"),
            width: "100%"
        },
        dropFileLabel: "Drop Here",
        onExtensionErr: function() { toastMagic.error(messagePleaseOnlyInputPNGOrJPG); },
        onSizeErr: function() { toastMagic.error(messageFileSizeTooBig); }
    });
});

function addMoreCustomerChoiceOption(index, name) {
    let nameSplit = name.split(" ").join("");
    let genHtml = `<div class="col-md-6"><div class="form-group">
        <input type="hidden" name="choice_no[]" value="${index}">
            <label class="title-color">${nameSplit}</label>
            <input type="text" name="choice[]" value="${nameSplit}" hidden>
            <div class="">
                <input type="text" class="form-control" name="choice_options_${index}[]"
                placeholder="` + messageEnterChoiceValues + `" data-role="tagsinput" onchange="getUpdateSKUFunctionality()">
            </div>
        </div></div>`;
    $("#customer_choice_options").append(genHtml);
    $("input[data-role=tagsinput], select[multiple][data-role=tagsinput]").tagsinput();
}

$(".delete_file_input").on("click", function() {
    let $parentDiv = $(this).parent().parent();
    $parentDiv.find('input[type="file"]').val("");
    $parentDiv.find(".img_area_with_preview img").addClass("d-none");
    $(this).removeClass("d-flex").hide();
});

$(".delete_preview_file_input").on("click", function() {
    let parentDiv = $(this).parent().parent();
    parentDiv.find('input[type="file"]').val("");
    parentDiv.find(".image-uploader__title").html($(".image-uploader__title").data("default"));
    $(this).removeClass("delete_preview_file_input");

    let formData = new FormData(document.getElementById("product_form"));
    $.ajaxSetup({
        headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") }
    });
    $.post({
        url: $(this).data("route"),
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response.errors) {
                for (let i = 0; i < response.errors.length; i++) {
                    setTimeout(() => { toastMagic.error(response.errors[i].message); }, i * 500);
                }
            } else {
                toastMagic.success(response.message);
                parentDiv.find(".image-uploader__title").html($(".image-uploader__title").data("default"));
            }
        }
    });
});

$(".onerror-add-class-d-none").on("error", function() {
    $(this).addClass("d-none");
});

function uploadColorImage(thisData = null) {
    if (thisData) {
        document.getElementById(thisData.dataset.imgpreview).setAttribute("src", window.URL.createObjectURL(thisData.files[0]));
        document.getElementById(thisData.dataset.imgpreview).classList.remove("d-none");
        try {
            if (thisData.dataset.imgpreview == "pre_img_viewer" && !$("#meta_image_input").val()) {
                $("#pre_meta_image_viewer").removeClass("d-none");
                $(".pre-meta-image-viewer").attr("src", window.URL.createObjectURL(thisData.files[0]));
            }
        } catch (e) {}
    }
}

$(".action-upload-color-image").on("change", function() {
    uploadColorImage(this);
});

$(".delete_file_input").click(function() {
    let $parentDiv = $(this).closest("div");
    $parentDiv.find('input[type="file"]').val("");
    $parentDiv.find(".img_area_with_preview img").addClass("d-none");
    $(this).hide();
});

elementCustomUploadInputFileByID.on("change", function() {
    if (parseFloat($(this).prop("files").length) !== 0) {
        let $parentDiv = $(this).closest("div");
        $parentDiv.find(".delete_file_input").fadeIn();
    }
});

$(".action-onclick-generate-number").on("click", function() {
    let getElement = $(this).data("input");
    $(getElement).val(generateRandomString(6));
    generateSKUPlaceHolder();
    $(getElement).trigger("blur");
});

function generateRandomString(length) {
    let result = "";
    let characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    let charactersLength = characters.length;
    for (let i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
}

$("#generate_number").on("keyup", function() {
    generateSKUPlaceHolder();
});

function generateSKUPlaceHolder() {
    let newPlaceholderValue = $("#get-example-text").data("example") + " : " + $("input[name=code]").val() + "-MCU-47-V593-M";
    $(".store-keeping-unit").attr("placeholder", newPlaceholderValue);
}

$(window).on("load", function() {
    generateSKUPlaceHolder();
});

$("#digital-product-type-select").on("change", function() {
    $("#digital-product-type-choice-section .extension-choice-section").remove();
    $("#digital-product-variation-section").empty().html();
    $.each($("#digital-product-type-select option:selected"), function() {
        addMoreDigitalProductChoiceOption($(this).val(), $(this).text());
    });
    getUpdateDigitalVariationFunctionality();
});

function addMoreDigitalProductChoiceOption(index, name) {
    let nameSplit = name.split(" ").join("");
    let genHtml = `<div class="col-sm-6 col-md-4 col-xxl-3 extension-choice-section">
        <div class="form-group">
            <input type="hidden" name="extensions_type[]" value="${index}">
            <label class="title-color">${nameSplit}</label>
            <input type="text" name="extensions[]" value="${nameSplit}" hidden>
            <div class="">
                <input type="text" class="form-control" name="extensions_options_${index}[]"
                placeholder="` + messageEnterChoiceValues + `" data-role="tagsinput" onchange="getUpdateDigitalVariationFunctionality()">
            </div>
        </div></div>`;
    $("#digital-product-type-choice-section").append(genHtml);
    $("input[data-role=tagsinput], select[multiple][data-role=tagsinput]").tagsinput();
}

function getUpdateDigitalVariationFunctionality() {
    $.ajaxSetup({
        headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") }
    });
    $.ajax({
        type: "POST",
        url: $("#route-vendor-products-digital-variation-combination").data("url"),
        data: $("#product_form").serialize(),
        success: function(data) {
            $("#digital-product-variation-section").html(data.view);
            ProductVariationFileUploadFunctionality();
            deleteDigitalVariationFileFunctionality();
        }
    });
}

function deleteDigitalVariationFileFunctionality() {
    $(".digital-variation-file-delete-button").on("click", function() {
        let variantKey = $(this).data("variant");
        let productId = $(this).data("product");
        $.ajaxSetup({
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") }
        });
        $.ajax({
            type: "POST",
            url: $("#route-vendor-products-digital-variation-file-delete").data("url"),
            data: { product_id: productId, variant_key: variantKey },
            success: function(response) {
                getUpdateDigitalVariationFunctionality();
                response.status === 1 ? toastMagic.success(response.message) : toastMagic.error(response.message);
            }
        });
    });
}

function ProductVariationFileUploadFunctionality() {
    $('.variation-upload-item input[type="file"]').each(function() {
        $(this).on("change", function() {
            const file = $(this)[0].files[0];
            if (file) {
                $(this).closest(".variation-upload-item").find(".variation-upload-file").addClass("collapse");
                $(this).closest(".variation-upload-item").find(".uploading-item").removeClass("collapse");
                const timer = setTimeout(() => {
                    $(this).closest(".variation-upload-item").find(".uploading-item").addClass("collapse");
                    $(this).closest(".variation-upload-item").find(".uploaded-item").removeClass("collapse");
                    $(this).closest(".variation-upload-item").find(".uploaded-item .file-name").text(file.name);
                }, 500);
                return () => clearTimeout(timer);
            }
        });
    });
    $(".cancel-upload").on("click", function() {
        $(this).closest(".variation-upload-item").find(".variation-upload-file").removeClass("collapse");
        $(this).closest(".variation-upload-item").find(".uploading-item").addClass("collapse");
        $(this).closest(".variation-upload-item").find(".uploaded-item").addClass("collapse");
        $(this).closest(".variation-upload-item").find('input[type="file"]').val("");
    });
}

$(".product-title-default-language").on("change keyup keypress", function() {
    $("#meta_title").val($(this).val());
});

$(".image-uploader__zip").on("change", function(event) {
    const file = event.target.files[0];
    const target = $(this).closest(".image-uploader").find(".image-uploader__title");
    if (file) {
        if (typeof window.validatePreviewFile === 'function' && !window.validatePreviewFile()) {
            this.value = '';
            target.text("Upload File");
            $(".zip-remove-btn").hide();
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) { target.text(file.name); };
        reader.readAsDataURL(file);
        $(".zip-remove-btn").show();
    } else {
        target.text("Upload File");
        $(".zip-remove-btn").hide();
    }
});

$(".image-uploader .zip-remove-btn").on("click", function(event) {
    $(this).closest(".image-uploader").find(".image-uploader__zip").val(null);
    $(this).closest(".image-uploader").find(".image-uploader__title").text("Upload File");
    $(this).hide();
});

$.fn.select2DynamicDisplay = function() {
    // ... (Your existing Select2 Dynamic Display function code remains unchanged) ...
    function updateDisplay($element) {
        var $rendered = $element.siblings(".select2-container").find(".select2-selection--multiple").find(".select2-selection__rendered");
        var $container = $rendered.parent();
        var containerWidth = $container.width();
        var totalWidth = 0;
        var itemsToShow = [];
        var remainingCount = 0;
        var selectedItems = $element.select2("data");
        var $tempContainer = $("<div>").css({ display: "inline-block", padding: "0 15px", "white-space": "nowrap", visibility: "hidden" }).appendTo($container);
        selectedItems.forEach(function(item) {
            var $tempItem = $("<span>").text(item.text).css({ display: "inline-block", padding: "0 12px", "white-space": "nowrap" }).appendTo($tempContainer);
            var itemWidth = $tempItem.outerWidth(true);
            if (totalWidth + itemWidth <= containerWidth - 40) {
                totalWidth += itemWidth;
                itemsToShow.push(item);
            } else {
                remainingCount = selectedItems.length - itemsToShow.length;
                return false;
            }
        });
        $tempContainer.remove();
        const $searchForm = $rendered.find(".select2-search");
        var html = "";
        itemsToShow.forEach(function(item) {
            html += `<li class="name"><span>${item.text}</span><span class="close-icon" data-id="${item.id}"><i class="tio-clear"></i></span></li>`;
        });
        if (remainingCount > 0) {
            html += `<li class="ms-auto"><div class="more">+${remainingCount}</div></li>`;
        }
        html += $searchForm.prop("outerHTML");
        $rendered.html(html);
        
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
        $(".select2-search input").on("input", debounce(function() {
            const inputValue = $(this).val().toLowerCase();
            const $listItems = $(".select2-results__options li");
            $listItems.each(function() {
                const itemText = $(this).text().toLowerCase();
                $(this).toggle(itemText.includes(inputValue));
            });
        }, 100));
        
        $(".select2-search input").on("keydown", function(e) {
            if (e.which === 13) {
                e.preventDefault();
                const inputValue = $(this).val();
                if (!inputValue || itemsToShow.find(item => item.text === inputValue) || selectedItems.find(item => item.text === inputValue)) {
                    $(this).val("");
                    return null;
                }
                if (inputValue) {
                    $element.append(new Option(inputValue, inputValue, true, true));
                    $element.val([...$element.val(), inputValue]);
                    $(this).val("");
                    $(".multiple-select2").select2DynamicDisplay();
                }
            }
        });
    }
    return this.each(function() {
        var $this = $(this);
        $this.select2({ tags: true });
        $this.on("change", function() { updateDisplay($this); });
        updateDisplay($this);
        $(window).on("resize", function() { updateDisplay($this); });
        $(window).on("load", function() { updateDisplay($this); });
        $(document).on("click", ".select2-selection__rendered .close-icon", function(e) {
            e.stopPropagation();
            var $removeIcon = $(this);
            var itemId = $removeIcon.data("id");
            var $this2 = $removeIcon.closest(".select2").siblings(".multiple-select2");
            $this2.val($this2.val().filter(function(id) { return id != itemId; }));
            $this2.trigger("change");
        });
    });
};
$(".multiple-select2").select2DynamicDisplay();
