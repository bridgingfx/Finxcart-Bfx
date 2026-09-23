
function initSimpleDigitalFileInput() {
    var input = document.getElementById('inputGroupFile01');
    if (!input) return;
    input.addEventListener('change', function () {
        var label = document.querySelector('label[for="inputGroupFile01"]');
        if (label) {
            label.textContent = this.files.length ? this.files[0].name : 'Choose file';
        }
    });
}

function renderProductAjaxSetup() {
    $.ajaxSetup({
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
    });
}

function getUpdateDigitalVariationFunctionality() {
    renderProductAjaxSetup();

    $.ajax({
        type: "POST",
        url: $("#route-admin-products-digital-variation-combination").data("url"),
        data: $("#product_form").serialize(),
        success: function (data) {
            $("#digital-product-variation-section").html(data.view);
            ProductVariationFileUploadFunctionality();
            deleteDigitalVariationFileFunctionality();
            initSimpleDigitalFileInput();
            reinitializeTooltips();
        },
    });
}

function deleteDigitalVariationFileFunctionality() {
    $(".digital-variation-file-delete-button").on("click", function () {
        let variantKey = $(this).data("variant");
        let productId = $(this).data("product");

        renderProductAjaxSetup();

        $.ajax({
            type: "POST",
            url: $("#route-admin-products-digital-variation-file-delete").data(
                "url"
            ),
            data: {
                product_id: productId,
                variant_key: variantKey,
            },
            success: function (response) {
                getUpdateDigitalVariationFunctionality();
                if (response.status === 1) {
                    toastMagic.success(response.message)
                } else {
                    toastMagic.error(response.message)
                }
            },
        });
    });
}

function getUpdateSKUFunctionality() {
    renderProductAjaxSetup();

    $.ajax({
        type: "POST",
        url: $("#route-admin-products-sku-combination").data("url"),
        data: $("#product_form").serialize(),
        success: function (data) {
            $("#sku_combination").html(data.view);
            updateProductQuantity();
            updateProductQuantityByKeyUp();
            let productType = $("#product_type").val();
            if (productType && productType.toString() === "physical") {
                if (data.length > 1) {
                    $("#quantity").hide();
                } else {
                    $("#quantity").show();
                }
            }
            generateSKUPlaceHolder();
            removeSymbol();
        },
    });
}

let productAddUpdateMessages = $('#product-add-update-messages');

// The product form has no type="submit" button — all real submission goes
// through the AJAX handler via the .product-add-requirements-check button.
// But pressing Enter in any text field still triggers the browser's implicit
// native form submission, which bypasses that handler entirely and dumps the
// server's raw JSON response as a full-page navigation instead of a styled
// inline error. Block that native path outright.
$(document).on("submit", "#product_form", function (e) {
    e.preventDefault();
});

function getProductAddRequirementsCheck() {
    if (typeof FormValidators !== "undefined") {
        let isValid = FormValidators.autoValidateForm("#product_form");
        // HTML min="0" alone can't express "greater than zero" / "at least 1" —
        // these business rules need an explicit check on top of the generic pass.
        if ($('input[name="unit_price"]').is(':visible')) {
            if (!FormValidators.positiveNumber('input[name="unit_price"]', 'Unit price must be greater than 0')) isValid = false;
        }
        if ($('#minimum_order_qty').is(':visible') && $('#minimum_order_qty').val()) {
            if (!FormValidators.numberRange('#minimum_order_qty', 1, Infinity, 'Minimum order quantity must be at least 1')) isValid = false;
        }
        // The default-language description textarea is Quill-backed and kept
        // display:none (Quill renders into a sibling .quill-editor div instead),
        // so it's invisible to the generic pass and its `required` HTML attribute
        // would never fire anyway — check its synced value directly instead.
        let $defaultDescription = $('.product-description-default-language');
        if ($defaultDescription.length && !$defaultDescription.val().replace(/<[^>]*>/g, '').trim()) {
            showFieldError('.product-description-default-language-editor', 'Product description is required');
            isValid = false;
        } else if ($defaultDescription.length) {
            clearFieldError('.product-description-default-language-editor');
        }
        if (!isValid) {
            FormValidators.focusFirstError('Please fix the highlighted fields');
            return;
        }
    }

    Swal.fire({
        title: productAddUpdateMessages?.data('are-you-sure'),
        text: productAddUpdateMessages?.data('want-to-add'),
        icon: "warning",
        showCancelButton: true,
        cancelButtonColor: "#d33",
        confirmButtonColor: "#377dff",
        confirmButtonText: productAddUpdateMessages?.data('yes-word'),
        cancelButtonText: productAddUpdateMessages?.data('no-word'),
        reverseButtons: true,
    }).then((result) => {
        if (result.value) {
            let discountValue = parseFloat($("#discount").val());
            let submitStatus = 1;

            if (submitStatus === 1) {
                let formData = new FormData(
                    document.getElementById("product_form")
                );

                renderProductAjaxSetup();
                $.ajax({
                    type: "POST",
                    method: "POST",
                    url: $("#product_form").attr("action"),
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $("#loading").fadeIn();
                    },
                    success: function (data) {
                        if (data.errors) {
                            for (let i = 0; i < data.errors.length; i++) {
                                setTimeout(() => {
                                    toastMagic.error(data.errors[i].message);
                                }, i * 500);
                            }
                        } else {
                            // The server saves the product on this same request now and hands
                            // back where to go next, instead of us re-submitting the same
                            // multipart form a second time — that used to upload every
                            // image/video/file twice over the wire.
                            toastMagic.success($("#message-product-added-successfully").data("text"));
                            window.location.href = data.redirect_url || $("#product_form").attr("action");
                        }
                    },
                    complete: function () {
                        $("#loading").fadeOut();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $("#loading").fadeOut();
                        let errors = jqXHR.responseJSON && jqXHR.responseJSON.errors;
                        if (jqXHR.status === 422 && Array.isArray(errors)) {
                            errors.forEach(function (err) {
                                toastMagic.error(err.message);
                            });
                        } else if (jqXHR.status === 422 && errors && typeof errors === 'object') {
                            Object.keys(errors).forEach(function (key) {
                                toastMagic.error(errors[key][0]);
                            });
                        } else {
                            toastMagic.error('An unexpected error occurred. Please try again.');
                        }
                    },
                });
            }
        }
    });
}

$(".delete_preview_file_input").on("click", function () {
    let parentDiv = $(this).parent().parent();
    parentDiv.find('input[type="file"]').val("");
    parentDiv.find(".image-uploader__title").html($(".image-uploader__title").data("default"));
    $(this).removeClass("delete_preview_file_input");

    let formData = new FormData(document.getElementById("product_form"));
    renderProductAjaxSetup();

    $.post({
        url: $(this).data("route"),
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            if (response.errors) {
                for (let i = 0; i < response.errors.length; i++) {
                    setTimeout(() => {
                        toastMagic.error(response.errors[i].message);
                    }, i * 500);
                }
            } else {
                toastMagic.success(response.message);
                parentDiv
                    .find(".image-uploader__title")
                    .html($(".image-uploader__title").data("default"));
            }
        },
    });
});
