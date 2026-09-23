"use strict";

// Debounce helper — stops the SKU-combination AJAX call (which serializes and POSTs
// the entire product form) from firing on every single keystroke, e.g. while typing
// the unit price. Without this, typing a 4-digit price fired 4 full-form requests to
// the server in a row.
function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function generateRandomString(length) {
    let result = "";
    let characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    let charactersLength = characters.length;
    for (let i = 0; i < length; i++) {
        result += characters.charAt(
            Math.floor(Math.random() * charactersLength)
        );
    }
    return result;
}

document.addEventListener("keyup", function(e) {
    if (e.target.id === "generate-sku-code") {
        generateSKUPlaceHolder();
    }
});

$("#product_type").on("change", function() {
    getProductTypeFunctionality();
});

$(document).on("change", 'input[name="listing_type"]', function() {
    getProductTypeFunctionality();
});

$("#digital-product-type-input").on("change", function() {
    getUpdateDigitalVariationFunctionality();
});

$("#product-color-switcher").on("click", function() {
    elementProductColorSwitcherByIDFunctionality();
    colorWiseImageFunctionality($("#colors-selector-input"));
});

document.addEventListener("click", function(e) {
    if (e.target.classList.contains("action-onclick-generate-number")) {
        let getElement = e.target.dataset.input;
        document.querySelector(getElement).value = generateRandomString(6);
        generateSKUPlaceHolder();
        $(getElement).trigger("blur");
    }

    if (e.target.classList.contains("product-add-requirements-check")) {
        getProductAddRequirementsCheck();
    }
});

document.addEventListener("change", function(e) {
    if (e.target.classList.contains("action-add-more-image")) {
        let parentDiv = e.target.closest("div");
        let deleteFileInput = parentDiv.querySelector(".delete_file_input");

        if (deleteFileInput) {
            deleteFileInput.classList.remove("d-none");
            deleteFileInput.style.display = "block";
        }
        addMoreImage(e.target, e.target.dataset.targetSection);
    }
});

document.addEventListener("change", function(e) {
    if (e.target.classList.contains("action-get-request-onchange")) {
        let getUrlPrefix = e.target.dataset.urlPrefix + e.target.value;
        let id = e.target.dataset.elementId;
        let getElementType = e.target.dataset.elementType;
        getRequestFunctionality(getUrlPrefix, id, getElementType);
    }
});

$("#product-choice-attributes").on("change", function() {
    $("#sku_combination")
        .empty()
        .html("");
    $("#customer-choice-options-container")
        .empty()
        .html("");
    $.each($("#product-choice-attributes option:selected"), function() {
        addMoreCustomerChoiceOption($(this).val(), $(this).text());
    });
    getUpdateSKUFunctionality();
});

function generateSKUPlaceHolder() {
    let exampleText = document.getElementById("get-example-text").dataset
        .example;
    let codeValue = document.querySelector("input[name='code']").value;
    let newPlaceholderValue = `${exampleText} : ${codeValue}-MCU-47-V593-M`;

    document.querySelectorAll(".store-keeping-unit").forEach(function(element) {
        element.setAttribute("placeholder", newPlaceholderValue);
    });
}

document
    .querySelectorAll('input[name="colors_active"]')
    .forEach(function(input) {
        input.addEventListener("change", function() {
            const isChecked = Array.from(
                document.querySelectorAll('input[name="colors_active"]')
            ).some(el => el.checked);
            document.getElementById(
                "colors-selector-input"
            ).disabled = !isChecked;
        });
    });

function addMoreCustomerChoiceOption(index, name) {
    let nameSplit = name.split(" ").join("");
    let genHtml = `<div class="col-md-6"><div class="form-group">
                <input type="hidden" name="choice_no[]" value="${index}">
                    <label class="form-label">${nameSplit}</label>
                    <input type="text" name="choice[]" value="${nameSplit}" hidden>
                    <div class="">
                        <input type="text" class="form-control" name="choice_options_${index}[]"
                        placeholder="${$("#message-enter-choice-values").data(
                            "text"
                        )}" data-role="tagsinput" onchange="getUpdateSKUFunctionality()">
                    </div>
                </div>
        </div>`;
    document
        .getElementById("customer-choice-options-container")
        .insertAdjacentHTML("beforeend", genHtml);

    document
        .querySelectorAll(
            "input[data-role=tagsinput], select[multiple][data-role=tagsinput]"
        )
        .forEach(function(input) {
            $(input).tagsinput();
        });
}

let debouncedGetUpdateSKUFunctionality = debounce(function() {
    getUpdateSKUFunctionality();
}, 400);

document
    .querySelector('input[name="unit_price"]')
    .addEventListener("keyup", function() {
        let productType = document.getElementById("product_type").value;
        if (productType && productType.toString() === "physical") {
            debouncedGetUpdateSKUFunctionality();
        }

        setTimeout(function() {
            document
                .querySelectorAll(".variation-price-input")
                .forEach(function(element) {
                    element.value = this.value;
                });
        }, 500);
    });

$("#colors-selector-input").on("change", function() {
    let elementProductColorSwitcherByID = $("#product-color-switcher");
    if (
        elementProductColorSwitcherByID &&
        elementProductColorSwitcherByID.prop("checked")
    ) {
        colorWiseImageFunctionality($("#colors-selector-input"));
        $("#color-wise-image-area").show();
    } else {
        $("#color-wise-image-area").hide();
    }
    getUpdateSKUFunctionality();

    try {
        initFileUpload();
    } catch (e) {
        console.error(e);
    }
});

document.querySelectorAll(".product-discount-type").forEach(function(select) {
    select.addEventListener("change", function() {
        const symbolElement = document.querySelector(".discount-amount-symbol");
        const currency = symbolElement.dataset.currency;
        const percent = symbolElement.dataset.percent;

        if (this.value.toString() === "flat") {
            symbolElement.innerHTML = `(${currency})`;
        } else {
            symbolElement.innerHTML = `(${percent})`;
        }
    });
});

function getProductTypeFunctionality() {
    let listingType = $('input[name="listing_type"]:checked').val();

    if (listingType === "event" || listingType === "broker") {
        $("#product_type").prop("disabled", true);
        $("#product_type_override").prop("disabled", false).val(listingType);

        $(".show-for-product-radio").addClass("d-none").hide();
        $(".show-for-physical-product").hide();
        $(".show-for-digital-product").hide();

        $(".show-for-event-or-broker-product").removeClass("d-none").show();
        $(".show-for-event-product").toggleClass("d-none", listingType !== "event").toggle(listingType === "event");
        $(".show-for-broker-product").toggleClass("d-none", listingType !== "broker").toggle(listingType === "broker");

        $("#brochure_event").prop("disabled", listingType !== "event");
        $("#brochure_broker").prop("disabled", listingType !== "broker");
        return;
    }

    $("#product_type").prop("disabled", false);
    $("#product_type_override").prop("disabled", true).val("");
    $(".show-for-product-radio").removeClass("d-none").show();
    $(".show-for-event-or-broker-product").addClass("d-none").hide();
    $(".show-for-event-product").addClass("d-none").hide();
    $(".show-for-broker-product").addClass("d-none").hide();
    $("#brochure_event").prop("disabled", true);
    $("#brochure_broker").prop("disabled", true);

    let productType = $("#product_type").val();
    if (productType && productType.toString() === "physical") {
        elementProductColorSwitcherByIDFunctionality("reset");
        $(".show-for-physical-product").show();
        $(".show-for-digital-product").hide();

        $("#digital_file_ready").val("");
    } else if (productType && productType.toString() === "digital") {
        elementProductColorSwitcherByIDFunctionality("reset");
        $(".show-for-physical-product").hide();
        $(".show-for-digital-product").show();

        $("#product-color-switcher").prop("checked", false);
        $("#color-wise-image-section")
            .empty()
            .html("");
    }

    try {
        if (productType && productType.toString() === "physical") {
            $("#digital-product-variation-section")
                .empty()
                .html();
            $(
                "#digital-product-type-choice-section .extension-choice-section"
            ).remove();
        }
    } catch (e) {}

    // Defensive re-sync regardless of what triggered this function, so Listing Type
    // never ends up visible while Physical is selected (or vice versa).
    syncListingTypeSectionVisibility();
}

// Listing Type (Product/Event/Broker) only makes sense for digital listings — a
// physical stock item is never an "event" or "broker" listing. The "Listing Type"
// section itself always stays visible; for a legacy Physical product being edited,
// only the "Product" option stays visible/selected and Event/Broker are hidden
// (not the whole section). New products are always Digital, so this only matters
// when editing pre-existing physical stock.
function syncListingTypeSectionVisibility() {
    let selectedType = $("#product_type").val() || "digital";
    let isPhysical = selectedType === "physical";
    let $eventBrokerOptions = $("#listing_type_event, #listing_type_broker").closest(".form-check");

    if (isPhysical) {
        $(".listing-type-input").prop("checked", false);
        $("#listing_type_product").prop("checked", true);
        $eventBrokerOptions.hide()[0] && $eventBrokerOptions.each(function() { this.style.display = "none"; });
    } else {
        $eventBrokerOptions.show()[0] && $eventBrokerOptions.each(function() { this.style.display = ""; });
    }
}

function ProductVariationFileUploadFunctionality() {
    document
        .querySelectorAll('.variation-upload-item input[type="file"]')
        .forEach(function(input) {
            input.addEventListener("change", function() {
                const file = this.files[0];
                if (file) {
                    let variationUploadItem = this.closest(
                        ".variation-upload-item"
                    );
                    variationUploadItem
                        .querySelector(".variation-upload-file")
                        .classList.add("collapse");
                    variationUploadItem
                        .querySelector(".uploading-item")
                        .classList.remove("collapse");

                    const timer = setTimeout(() => {
                        variationUploadItem
                            .querySelector(".uploading-item")
                            .classList.add("collapse");
                        variationUploadItem
                            .querySelector(".uploaded-item")
                            .classList.remove("collapse");
                        variationUploadItem.querySelector(
                            ".uploaded-item .file-name"
                        ).textContent = file.name;
                    }, 500);

                    return () => clearTimeout(timer);
                }
            });
        });

    document.querySelectorAll(".cancel-upload").forEach(function(button) {
        button.addEventListener("click", function() {
            let variationUploadItem = this.closest(".variation-upload-item");
            variationUploadItem
                .querySelector(".variation-upload-file")
                .classList.remove("collapse");
            variationUploadItem
                .querySelector(".uploading-item")
                .classList.add("collapse");
            variationUploadItem
                .querySelector(".uploaded-item")
                .classList.add("collapse");
            variationUploadItem.querySelector('input[type="file"]').value = "";
        });
    });
}

$("#digital-product-type-select").on("change", function() {
    $(
        "#digital-product-type-choice-section .extension-choice-section"
    ).remove();
    $("#digital-product-variation-section")
        .empty()
        .html();
    $.each($("#digital-product-type-select option:selected"), function() {
        addMoreDigitalProductChoiceOption($(this).val(), $(this).text());
    });
    getUpdateDigitalVariationFunctionality();
});

function addMoreDigitalProductChoiceOption(index, name) {
    let nameSplit = name.split(" ").join("");
    let ExtensionText = $("#get-extension-text-message").data("text");
    let genHtml =
        `<div class="col-sm-6 col-md-4 col-xxl-3 extension-choice-section">
                <div class="form-group">
                    <input type="hidden" name="extensions_type[]" value="${index}">
                    <label class="form-label">${nameSplit} ${ExtensionText}</label>
                    <input type="text" name="extensions[]" value="${nameSplit}" hidden>
                    <div class="">
                        <input type="text" class="form-control" name="extensions_options_${index}[]"
                        placeholder="` +
        $("#message-enter-choice-values").data("text") +
        `" data-role="tagsinput" onchange="getUpdateDigitalVariationFunctionality()">
                    </div>
                </div>
        </div>`;
    $("#digital-product-type-choice-section").append(genHtml);
    $(
        "input[data-role=tagsinput], select[multiple][data-role=tagsinput]"
    ).tagsinput();
}

$(".product-title-default-language").on("change keyup keypress", function() {
    $("#meta_title").val($(this).val());
    getUpdateSKUFunctionality();
});
