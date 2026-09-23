"use strict";

const adminVendorSellerTypeInput = $("#admin-vendor-seller-type");
const adminVendorSellerTypeChooser = $("#admin-vendor-seller-type-chooser");
const adminVendorCompanyFields = $("#admin-vendor-company-fields");
const adminVendorIndividualFields = $("#admin-vendor-individual-fields");
const adminVendorShopDetailsFields = $("#admin-vendor-shop-details-fields");
const adminVendorIndividualDocumentsFields = $("#admin-vendor-individual-documents-fields");
const adminVendorImageCompanyField = $("#admin-vendor-image-company-field");
const adminVendorShopSectionTitleCompany = $("#admin-vendor-shop-section-title-company");
const adminVendorShopSectionTitleIndividual = $("#admin-vendor-shop-section-title-individual");
const adminVendorLogoLabelCompany = $("#admin-vendor-logo-label-company");
const adminVendorLogoLabelIndividual = $("#admin-vendor-logo-label-individual");
const adminVendorBannerLabelCompany = $("#admin-vendor-banner-label-company");
const adminVendorBannerLabelIndividual = $("#admin-vendor-banner-label-individual");
const adminVendorTypeButtons = $(".vendor-seller-type-btn");

const setAdminVendorSectionRequiredState = (section, required) => {
    section.find("input, textarea, select").each(function () {
        if (this.name && this.name !== "seller_type") {
            $(this).prop("required", required);
        }
    });
};

const setAdminVendorTypeActiveState = (type) => {
    adminVendorTypeButtons.each(function () {
        const isActive = $(this).data("type") === type;
        $(this)
            .toggleClass("btn-primary", isActive)
            .toggleClass("btn-outline-primary", !isActive);
    });
};

const setAdminVendorShopDetailsState = (type) => {
    const isCompany = type === "company";
    const isIndividual = type === "individual";

    adminVendorShopDetailsFields.toggleClass("d-none", !isCompany);
    adminVendorShopDetailsFields.find("input, textarea").each(function () {
        $(this).prop("required", isCompany).prop("disabled", !isCompany);
    });

    adminVendorImageCompanyField.toggleClass("d-none", !isCompany);
    adminVendorImageCompanyField.find("input").each(function () {
        $(this).prop("disabled", !isCompany);
    });

    adminVendorIndividualDocumentsFields.toggleClass("d-none", !isIndividual);
    adminVendorIndividualDocumentsFields.find("input").each(function () {
        $(this).prop("disabled", !isIndividual);
    });

    adminVendorShopSectionTitleCompany.toggleClass("d-none", !isCompany);
    adminVendorShopSectionTitleIndividual.toggleClass("d-none", !isIndividual);
    adminVendorLogoLabelCompany.toggleClass("d-none", !isCompany);
    adminVendorLogoLabelIndividual.toggleClass("d-none", !isIndividual);
    adminVendorBannerLabelCompany.toggleClass("d-none", !isCompany);
    adminVendorBannerLabelIndividual.toggleClass("d-none", !isIndividual);
};

const showAdminVendorType = (type) => {
    adminVendorSellerTypeInput.val(type);
    adminVendorSellerTypeChooser.addClass("d-none");
    adminVendorCompanyFields.toggleClass("d-none", type !== "company");
    adminVendorIndividualFields.toggleClass("d-none", type !== "individual");

    setAdminVendorSectionRequiredState(adminVendorCompanyFields, type === "company");
    setAdminVendorSectionRequiredState(adminVendorIndividualFields, type === "individual");
    setAdminVendorShopDetailsState(type);
    setAdminVendorTypeActiveState(type);
};

const resetAdminVendorTypeSelection = () => {
    adminVendorSellerTypeInput.val("");
    adminVendorSellerTypeChooser.removeClass("d-none");
    adminVendorCompanyFields.addClass("d-none");
    adminVendorIndividualFields.addClass("d-none");
    setAdminVendorSectionRequiredState(adminVendorCompanyFields, false);
    setAdminVendorSectionRequiredState(adminVendorIndividualFields, false);
    setAdminVendorShopDetailsState("");
    adminVendorTypeButtons.removeClass("btn-primary").addClass("btn-outline-primary");
};

if (adminVendorSellerTypeInput.length) {
    const initialAdminVendorType = adminVendorSellerTypeInput.val();

    if (initialAdminVendorType === "company" || initialAdminVendorType === "individual") {
        showAdminVendorType(initialAdminVendorType);
    } else {
        resetAdminVendorTypeSelection();
    }
}

adminVendorTypeButtons.on("click", function () {
    const selectedType = $(this).data("type");
    if (selectedType === "company" || selectedType === "individual") {
        showAdminVendorType(selectedType);
    }
});

$('.reset-button').on('click', function () {
    let placeholderImg = $("#placeholderImg").data('img');
    $('#viewer').attr('src', placeholderImg);
    $('#viewerBanner').attr('src', placeholderImg);
    $('#viewerBottomBanner').attr('src', placeholderImg);
    $('#viewerLogo').attr('src', placeholderImg);
    $('#viewerIndividualImage').attr('src', placeholderImg);
    $('#viewerIndividualPersonalId').attr('src', placeholderImg);
    $('.spartan_remove_row').click();
})

$("#add-vendor-form").on("reset", function () {
    setTimeout(() => {
        resetAdminVendorTypeSelection();
    }, 0);
});

$('#exampleInputPassword ,#exampleRepeatPassword').on('keyup', function () {
    let pass = $("#exampleInputPassword").val();
    let passRepeat = $("#exampleRepeatPassword").val();
    if (pass === passRepeat) {
        $('.pass').hide();
    } else {
        $('.pass').show();
    }
});

$('#apply').on('click', function () {
    let image = $("#image-set").val();
    if (image === null) {
        $('.image').show();
        return false;
    }
    let pass = $("#exampleInputPassword").val();
    let passRepeat = $("#exampleRepeatPassword").val();
    if (pass !== passRepeat) {
        $('.pass').show();
        return false;
    }
});

$("#add-vendor-form").on("submit", function (event) {
    event.preventDefault();

    if (typeof FormValidators !== "undefined" && !FormValidators.autoValidateForm(this)) {
        return;
    }

    let getText = $("#get-confirm-and-cancel-button-text");
    let targetUrl = $(this).data("redirect-route");

    Swal.fire({
        title: getText.data("sure"),
        text: $(this).data("message"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: getText.data("cancel"),
        confirmButtonText: getText.data("confirm"),
        reverseButtons: true,
    }).then((result) => {
        if (result.value) {
            let formData = new FormData(document.getElementById('add-vendor-form'));
            $.ajaxSetup({
                headers: {
                    "X-XSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
            });

            $("#loading").fadeIn();

            // This form can carry up to 6 file uploads (license/ID document, vendor
            // image, shop logo/banner/bottom banner) with no size limit — raw camera
            // photos here were the main cause of "too much loading" on submit.
            // Downscaling oversized images in the browser first (same fix already
            // used on the product form) cuts both the upload and server encode time.
            let postFormData = function (dataToSend) {
                $.post({
                    url: $("#add-vendor-form").attr("action"),
                    data: dataToSend,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        if (data.errors) {
                            for (let index = 0; index < data.errors.length; index++) {
                                setTimeout(() => {
                                    toastMagic.error(data.errors[index].message);
                                }, index * 500);
                            }
                        } else if (data.error) {
                            toastMagic.error(data.error);
                        } else {
                            toastMagic.success(data.message);
                            if (targetUrl) {
                                location.href = targetUrl;
                            } else {
                                location.reload();
                            }
                        }
                    },
                    complete: function () {
                        $("#loading").fadeOut();
                    },
                });
            };

            if (typeof resizeFormDataImages === 'function') {
                resizeFormDataImages(formData)
                    .then(postFormData)
                    .catch(function (err) {
                        console.error("Image resize failed, submitting original files:", err);
                        postFormData(formData);
                    });
            } else {
                postFormData(formData);
            }
        }
    });
});
