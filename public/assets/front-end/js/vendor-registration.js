"use strict";
$(document).ready(function () {
    $("#vendor-apply-submit").on("click", function () {
        let email = $("#email").val();
        let phone = $(".phone-input-with-country-picker").val();
        let password = $("#password").val();
        let confirmPassword = $("#confirm_password").val();
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        let getErrorMessages = $("#proceed-to-next-validation-message");
        if (email === "") {
            $(".mail-error").html(getErrorMessages.data("mail-error"));
            return;
        } else {
            $(".mail-error").html("");
        }
        if (!emailPattern.test(email)) {
            $(".mail-error").html(getErrorMessages.data("valid-mail"));
            return;
        } else {
            $(".mail-error").html("");
        }
        if (phone === "") {
            $(".phone-error").html(getErrorMessages.data("phone-error"));
            return;
        } else {
            $(".phone-error").html("");
        }
        if (password === "") {
            $(".password-error").html(getErrorMessages.data("enter-password"));
            return;
        } else {
            $(".password-error").html("");
        }
        if (confirmPassword === "") {
            $(".confirm-password-error").html(
                getErrorMessages.data("enter-confirm-password"),
            );
            return;
        } else {
            $(".confirm-password-error").html("");
        }
        if (password.trim() !== confirmPassword.trim()) {
            $(".confirm-password-error").html(
                getErrorMessages.data("password-not-match"),
            );
            return;
        } else {
            $(".confirm-password-error").html("");
        }
        submitRegistration();
    });
});

function submitRegistration() {
    let getText = $("#get-confirm-and-cancel-button-text");
    const getFormId = "seller-registration";
    Swal.fire({
        title: getText.data("sure"),
        text: getText.data("message"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: getText.data("cancel"),
        confirmButtonText: getText.data("confirm"),
        reverseButtons: true,
    }).then((result) => {
        if (result.value) {
            if (!$("#terms-checkbox").is(":checked")) {
                toastr.error("Please accept the terms and conditions.");
                return;
            }
            let formData = new FormData(document.getElementById(getFormId));
            $.ajaxSetup({
                headers: {
                    "X-XSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },
            });
            $.ajax({
                type: "POST",
                url: $("#" + getFormId).attr("action"),
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $("#loading").removeClass("d--none").addClass("d-grid");
                },
                success: function (data) {
                    if (data.errors) {
                        for (
                            let index = 0;
                            index < data.errors.length;
                            index++
                        ) {
                            toastr.error(data.errors[index].message, {
                                CloseButton: true,
                                ProgressBar: true,
                            });
                        }
                    } else if (data.error) {
                        toastr.error(data.error, {
                            CloseButton: true,
                            ProgressBar: true,
                        });
                    } else {
                        if (data.message) {
                            $("#vendor-registration-success-message").text(
                                data.message,
                            );
                        }
                        $(".registration-success-modal").modal("show");
                        setTimeout(function () {
                            location.href =
                                data.redirectRoute + "?registered=1";
                        }, 4000);
                    }
                },
                error: function (xhr) {
                    try {
                        var response = xhr.responseJSON || JSON.parse(xhr.responseText || "{}");

                        if (Array.isArray(response.errors) && response.errors.length) {
                            response.errors.forEach(function (err) {
                                if (err && err.message) {
                                    toastr.error(err.message, {
                                        CloseButton: true,
                                        ProgressBar: true,
                                    });
                                }
                            });
                            return;
                        }

                        if (response.message) {
                            toastr.error(response.message, {
                                CloseButton: true,
                                ProgressBar: true,
                            });
                            return;
                        }

                        toastr.error("Something went wrong. Please try again.", {
                            CloseButton: true,
                            ProgressBar: true,
                        });
                    } catch (e) {
                        toastr.error("Something went wrong. Please try again.", {
                            CloseButton: true,
                            ProgressBar: true,
                        });
                    }
                },

                complete: function () {
                    $("#loading").removeClass("d-grid").addClass("d--none");
                },
            });
        }
    });
}
$("#terms-checkbox").on("click", function () {
    if ($(this).is(":checked")) {
        $("#vendor-apply-submit").removeAttr("disabled");
    } else {
        $("#vendor-apply-submit").attr("disabled", "disabled");
    }
});
