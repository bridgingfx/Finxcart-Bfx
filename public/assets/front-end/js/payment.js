"use strict";

setTimeout(function () {
    $(".stripe-button-el").hide();
    $(".razorpay-payment-button").hide();
}, 10);

$(function () {
    // Start disabled only when nothing is pre-selected (e.g. a default gateway
    // marked `checked` server-side in the blade template) — otherwise Proceed
    // stays visually enabled-looking but is actually blocked (a.btn.disabled
    // sets pointer-events: none), with no click ever firing the "change"
    // handler below that would turn it back on.
    if ($('input[type="radio"]:checked').length === 0) {
        $(".proceed_to_next_button").addClass("disabled");
    } else {
        $(".proceed_to_next_button").removeClass("disabled");
    }
});

const radioButtons = document.querySelectorAll('input[type="radio"]');
radioButtons.forEach((radioButton) => {
    radioButton.addEventListener("change", function () {
        if (this.checked) {
            $(".proceed_to_next_button").removeClass("disabled");

            radioButtons.forEach((otherRadioButton) => {
                if (otherRadioButton !== this) {
                    otherRadioButton.checked = false;
                }
            });
            this.setAttribute("checked", "true");
            const field_id = this.id;
            if (field_id == "pay_offline") {
                $(".pay_offline_card").removeClass("d-none");
                $(".proceed_to_next_button").addClass("disabled");
            } else {
                $(".pay_offline_card").addClass("d-none");
                $(".proceed_to_next_button").removeClass("disabled");
            }
        } else {
        }
    });
});

function checkoutFromPayment() {
    let checkoutButton = $(".action-checkout-function");
    if (checkoutButton.hasClass("disabled") || checkoutButton.attr("disabled")) {
        return;
    }
    let checked_button_id = $('input[type="radio"]:checked').attr("id");
    let form = checked_button_id ? $("#" + checked_button_id + "_form") : $();
    if (form.length === 0) {
        // Nothing selected / matching form missing — nothing to submit, so
        // leave the button clickable instead of disabling it with no way back.
        return;
    }
    checkoutButton.attr("disabled", true).addClass("disabled");
    form.submit();
}

const buttons = document.querySelectorAll(".offline_payment_button");
const selectElement = document.getElementById("pay_offline_method");
buttons.forEach((button) => {
    button.addEventListener("click", function () {
        const buttonId = this.id;
        pay_offline_method_field(buttonId);
        selectElement.value = buttonId;
    });
});

$("#pay_offline_method").on("change", function () {
    pay_offline_method_field(this.value);
});

function pay_offline_method_field(method_id) {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
    $.ajax({
        url:
            $("#route-pay-offline-method-list").data("url") +
            "?method_id=" +
            method_id,
        data: {},
        processData: false,
        contentType: false,
        type: "get",
        success: function (response) {
            $("#payment_method_field").html(response.methodHtml);
            $("#selectPaymentMethod").modal().show();
        },
        error: function () {
            toastr.error('Something went wrong. Please try again.');
        },
    });
}

$("#bring_change_amount").on("shown.bs.collapse", function () {
    $("#bring_change_amount_btn").text($(this).data("less"));
});

$("#bring_change_amount").on("hidden.bs.collapse", function () {
    $("#bring_change_amount_btn").text($(this).data("more"));
});

$(document).ready(function () {
    $("input").on("change", function () {
        bringChangeAmountSectionRender();
    });

    function bringChangeAmountSectionRender() {
        if ($("#cash_on_delivery").prop("checked")) {
            $(".bring_change_amount_section").slideDown();
        } else {
            $(".bring_change_amount_section").slideUp();
        }
    }

    $("#bring_change_amount_input").on("keyup keypress change", function () {
        $("#bring_change_amount_value").val($(this).val());
    });
});

//
