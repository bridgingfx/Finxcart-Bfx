"use strict";
$('#customer-verify').on('submit', function (event) {
    event.preventDefault();
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        dataType: "json",
        data: $(this).serialize(),
        beforeSend: function () {
            $("#loading").addClass("d-grid");
        },
        success: function (data) {
            if (data.status === 'success') {
                $('#otp_form_section').addClass('d-none');
                $('#success_message').removeClass('d-none');
                $('#loginModal').modal('show');
                toastr.success(data.message);
            } else {
                toastr.error(data.message);
            }
        },
        complete: function () {
            $("#loading").removeClass("d-grid");
        },
    });
});

$('#resend-otp').click(function () {
    $('input.otp-field').val('');
    let userId = $(this).data('field') === 'identity' ? $('input[name="identity"]').val(): $('input[name="id"]').val();
    let url = $(this).data('route') ;
    if ($(this).data('field') === 'identity') {
        sendAjaxRequest(url,{identity: userId });
    } else {
        sendAjaxRequest(url,{user_id: userId });
    }
});

function sendAjaxRequest(url,responseData)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $.ajax({
        url: url,
        method: 'POST',
        dataType: 'json',
        data: responseData,
        beforeSend: function () {
            $("#loading").addClass("d-grid");
        },
        success: function (data) {
            if (parseInt(data.status) === 1) {
                let newCounter = $('.verifyCounter');
                let seconds = parseInt(data.new_time, 10) || 60;
                let expiresAt = parseInt(data.otp_expires_at, 10) || (Date.now() + seconds * 1000);
                newCounter.data('expires-at', expiresAt);
                function newTick() {
                    let remaining = Math.max(0, Math.ceil((expiresAt - Date.now()) / 1000));
                    let m = Math.floor(remaining / 60);
                    let s = remaining % 60;
                    newCounter.html(m + ":" + (s < 10 ? "0" : "") + String(s));
                    if (remaining > 0) {
                        setTimeout(newTick, 1000);
                        $('.resend-otp-button').attr('disabled', true);
                        $(".resend-otp-custom, .resend_otp_custom").slideDown();
                    } else {
                        $('.resend-otp-button').removeAttr('disabled');
                        newCounter.html("0:00");
                        $(".resend-otp-custom, .resend_otp_custom").slideUp();
                    }
                }
                newTick();
                toastr.success($('#get-resend-otp-text').data('success'));
            } else {
                toastr.error($('#get-resend-otp-text').data('error'));
            }
        },
        complete: function () {
            $("#loading").removeClass("d-grid");
        },
    });
}
