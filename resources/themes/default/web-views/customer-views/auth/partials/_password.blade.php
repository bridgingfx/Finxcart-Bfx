<div class="form-group">
    <label class="form-label font-semibold">{{translate('password')}}
        <span class="input-required-icon text-danger fs-12 password-error">*</span>
    </label>
    <div class="password-toggle rtl" style="position: relative;">
        <input class="form-control text-align-direction auth-password-input" name="password" type="password" id="password"
            placeholder="{{ translate('minimum_8_characters_long') }}" required
            style="padding-right: 2.5rem;">
        <label class="password-toggle-btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); margin: 0; cursor: pointer;">
        <input class="custom-control-input" type="checkbox">
        <i class="tio-hidden password-toggle-indicator"></i>
        <span class="sr-only">{{ translate('show_password') }}</span>
        </label>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtns = document.querySelectorAll('.password-toggle-btn input[type="checkbox"]');

        toggleBtns.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const parent = checkbox.closest('.password-toggle');
                const passwordInput = parent.querySelector('input[type="password"], input[type="text"]');
                const icon = parent.querySelector('.password-toggle-indicator');

                if (checkbox.checked) {
                    passwordInput.type = 'text';
                    icon.classList.remove('tio-hidden');
                    icon.classList.add('tio-visible');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('tio-visible');
                    icon.classList.add('tio-hidden');
                }
            });
        });
    });
</script>
