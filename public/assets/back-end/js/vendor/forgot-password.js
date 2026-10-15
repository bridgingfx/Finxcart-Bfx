"use strict";
$(".js-example-responsive").select2({
    width: 'resolve'
});
$(document).on('ready', function () {
    $('.js-toggle-password').each(function () {
        new HSTogglePassword(this).init()
    });
    $('.js-validate').each(function () {
        $.HSCore.components.HSValidation.init($(this));
    });
});

$(document).on('submit', 'form.forget-password-form', function (event) {
    event.preventDefault();

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

    const form = $(this);
    const button = form.find('[type="submit"]').first();

    $.ajax({
        type: 'POST',
        url: form.attr('action'),
        data: form.serialize(),
        beforeSend: function () {
            $('#loading').fadeIn();
        },
        success: function (data) {
            if (data.errors) {
                for (let index = 0; index < data.errors.length; index++) {
                    setTimeout(() => {
                        if (typeof window.toastMagic !== 'undefined') {
                            window.toastMagic.error(data.errors[index].message);
                        } else if (typeof window.toastr !== 'undefined') {
                            window.toastr.error(data.errors[index].message);
                        }
                    }, index * 500);
                }
            } else if (data.redirect) {
                if (typeof window.toastr !== 'undefined') {
                    window.toastr.error(data.error);
                } else if (typeof window.toastMagic !== 'undefined') {
                    window.toastMagic.error(data.error);
                }
                setTimeout(function () {
                    location.href = data.redirectRoute;
                }, 3000);
            } else if(data.error){
                if (typeof window.toastMagic !== 'undefined') {
                    window.toastMagic.error(data.error);
                } else if (typeof window.toastr !== 'undefined') {
                    window.toastr.error(data.error);
                }
            }
            else if(data.verificationBy === 'mail'){
                const modalElement = document.getElementById('email-reset-success-modal');
                if (modalElement) {
                    if (window.bootstrap && window.bootstrap.Modal) {
                        const modal = new window.bootstrap.Modal(modalElement, {
                            backdrop: 'static',
                            keyboard: false
                        });
                        modal.show();
                    } else if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
                        window.jQuery(modalElement).modal({
                            backdrop: 'static',
                            keyboard: false
                        });
                        window.jQuery(modalElement).modal('show');
                    }
                }
                button.attr('disabled', true);
            }else if(data.verificationBy === 'phone' || data.passwordUpdate){
                location.href=data.redirectRoute;
                if (typeof window.toastMagic !== 'undefined') {
                    window.toastMagic.success(data.success);
                } else if (typeof window.toastr !== 'undefined') {
                    window.toastr.success(data.success);
                }
            }
        },
        complete: function () {
            $('#loading').fadeOut();
        },
    })
});
var backgroundImage = $("[data-bg-img]");
backgroundImage.css("background-image", function () {
    return 'url("' + $(this).data("bg-img") + '")';
}).removeAttr("data-bg-img").addClass("bg-img");
$('.password-check').on('keyup keypress change click', function () {
    let password = $(this).val();
    let passwordError = $('.password-error');
    let passwordErrorMessage = $('#password-error-message');
    switch (true) {
        case password.length < 8:
            passwordError.html(passwordErrorMessage.data('max-character')).removeClass('d-none');
            break;
        case !(/[a-z]/.test(password)):
            passwordError.html(passwordErrorMessage.data('lowercase-character')).removeClass('d-none');
            break;
        case !(/[A-Z]/.test(password)):
            passwordError.html(passwordErrorMessage.data('uppercase-character')).removeClass('d-none');
            break;
        case !(/\d/.test(password)):
            passwordError.html(passwordErrorMessage.data('number')).removeClass('d-none');
            break;
        case !(/[@.#$!%*?&]/.test(password)):
            passwordError.html(passwordErrorMessage.data('symbol')).removeClass('d-none');
            break;
        default:
            passwordError.addClass('d-none').empty();
    }
});



