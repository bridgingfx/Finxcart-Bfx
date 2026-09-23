"use strict";

/**
 * Shared client-side form validation helpers for the Vendor panel.
 * Reuses the error-display convention already established by
 * product-add-update-validation.js (text-danger span injected next to the
 * field, is-invalid class on the field) so new validation looks identical
 * to the existing one instead of introducing a second style.
 */

function showFieldError(fieldElement, errorMessage) {
    let $field = $(fieldElement);
    let $formGroup = $field.closest('.form-group');
    let $scope = $formGroup.length ? $formGroup : $field.parent();

    $scope.find('.text-danger.validation-error').remove();
    $field.removeClass('is-invalid');

    if (errorMessage) {
        $field.addClass('is-invalid');

        let errorHtml = `<span class="text-danger validation-error d-block mt-1" style="font-size: 12px;">${errorMessage}</span>`;

        if ($field.hasClass('js-select2-custom') || $field.hasClass('select2-hidden-accessible')) {
            let $select2Container = $field.next('.select2-container');
            if ($select2Container.length) {
                $select2Container.after(errorHtml);
            } else {
                $field.after(errorHtml);
            }
        } else if ($field.attr('type') === 'file') {
            let $uploadDiv = $field.closest('.custom_upload_input');
            if ($uploadDiv.length) {
                $uploadDiv.after(errorHtml);
            } else {
                $field.after(errorHtml);
            }
        } else {
            $field.after(errorHtml);
        }
    }
}

function clearFieldError(fieldElement) {
    let $field = $(fieldElement);
    let $formGroup = $field.closest('.form-group');
    let $scope = $formGroup.length ? $formGroup : $field.parent();

    $scope.find('.text-danger.validation-error').remove();
    $field.removeClass('is-invalid');

    if ($field.attr('type') === 'file') {
        let $uploadDiv = $field.closest('.custom_upload_input');
        if ($uploadDiv.length) {
            $uploadDiv.parent().find('.text-danger.validation-error').remove();
        }
    }
}

const FormValidators = {
    EMAIL_PATTERN: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    // Loose international-friendly phone check: digits, spaces, +, -, () — 4-20 chars.
    PHONE_PATTERN: /^[+]?[0-9\s\-().]{4,20}$/,
    URL_PATTERN: /^(https?:\/\/)[^\s$.?#].[^\s]*$/i,

    required(fieldElement, message) {
        let $field = $(fieldElement);
        let value = $field.val();
        if (value === null || value === undefined || String(value).trim() === '') {
            showFieldError($field, message || 'This field is required');
            return false;
        }
        clearFieldError($field);
        return true;
    },

    email(fieldElement, message) {
        let $field = $(fieldElement);
        let value = ($field.val() || '').trim();
        if (value === '') return true; // pair with .required() for "required + valid" fields
        if (!FormValidators.EMAIL_PATTERN.test(value)) {
            showFieldError($field, message || 'Please enter a valid email address');
            return false;
        }
        clearFieldError($field);
        return true;
    },

    phone(fieldElement, message) {
        let $field = $(fieldElement);
        let value = ($field.val() || '').trim();
        if (value === '') return true;
        if (!FormValidators.PHONE_PATTERN.test(value)) {
            showFieldError($field, message || 'Please enter a valid phone number');
            return false;
        }
        clearFieldError($field);
        return true;
    },

    url(fieldElement, message) {
        let $field = $(fieldElement);
        let value = ($field.val() || '').trim();
        if (value === '') return true;
        if (!FormValidators.URL_PATTERN.test(value)) {
            showFieldError($field, message || 'Please enter a valid URL (starting with http:// or https://)');
            return false;
        }
        clearFieldError($field);
        return true;
    },

    minLength(fieldElement, min, message) {
        let $field = $(fieldElement);
        let value = ($field.val() || '').trim();
        if (value === '') return true;
        if (value.length < min) {
            showFieldError($field, message || `Must be at least ${min} characters`);
            return false;
        }
        clearFieldError($field);
        return true;
    },

    positiveNumber(fieldElement, message) {
        let $field = $(fieldElement);
        let value = $field.val();
        if (value === null || value === undefined || String(value).trim() === '') return true;
        let num = parseFloat(value);
        if (isNaN(num) || num <= 0) {
            showFieldError($field, message || 'Must be a number greater than 0');
            return false;
        }
        clearFieldError($field);
        return true;
    },

    nonNegativeNumber(fieldElement, message) {
        let $field = $(fieldElement);
        let value = $field.val();
        if (value === null || value === undefined || String(value).trim() === '') return true;
        let num = parseFloat(value);
        if (isNaN(num) || num < 0) {
            showFieldError($field, message || 'Must be a number of 0 or more');
            return false;
        }
        clearFieldError($field);
        return true;
    },

    numberRange(fieldElement, min, max, message) {
        let $field = $(fieldElement);
        let value = $field.val();
        if (value === null || value === undefined || String(value).trim() === '') return true;
        let num = parseFloat(value);
        if (isNaN(num) || num < min || num > max) {
            showFieldError($field, message || `Must be between ${min} and ${max}`);
            return false;
        }
        clearFieldError($field);
        return true;
    },

    minValue(fieldElement, min, message) {
        let $field = $(fieldElement);
        let value = $field.val();
        if (value === null || value === undefined || String(value).trim() === '') return true;
        let num = parseFloat(value);
        if (isNaN(num) || num < min) {
            showFieldError($field, message || `Must be at least ${min}`);
            return false;
        }
        clearFieldError($field);
        return true;
    },

    maxValue(fieldElement, max, message) {
        let $field = $(fieldElement);
        let value = $field.val();
        if (value === null || value === undefined || String(value).trim() === '') return true;
        let num = parseFloat(value);
        if (isNaN(num) || num > max) {
            showFieldError($field, message || `Must not exceed ${max}`);
            return false;
        }
        clearFieldError($field);
        return true;
    },

    // Confirm-password match. Reads both fields; anchors the error to the confirm field.
    passwordsMatch(passwordField, confirmField, message) {
        let $password = $(passwordField);
        let $confirm = $(confirmField);
        let confirmValue = $confirm.val() || '';
        if (confirmValue === '') return true; // .required() covers emptiness separately
        if ($password.val() !== confirmValue) {
            showFieldError($confirm, message || 'Passwords do not match');
            return false;
        }
        clearFieldError($confirm);
        return true;
    },

    // File input type/size check. allowedExtensions e.g. ['jpg','jpeg','png']. maxSizeMB optional.
    file(fieldElement, allowedExtensions, maxSizeMB, message) {
        let $field = $(fieldElement);
        let input = $field[0];
        let file = input && input.files && input.files[0];
        if (!file) return true; // presence is a separate .required() concern

        let extension = (file.name.split('.').pop() || '').toLowerCase();
        if (allowedExtensions && allowedExtensions.length && !allowedExtensions.includes(extension)) {
            showFieldError($field, message || `Only ${allowedExtensions.join(', ')} files are allowed`);
            return false;
        }

        if (maxSizeMB && file.size > maxSizeMB * 1024 * 1024) {
            showFieldError($field, `File size must not exceed ${maxSizeMB}MB`);
            return false;
        }

        clearFieldError($field);
        return true;
    },

    // Scrolls to and focuses the first invalid field after a failed validateAll(), and
    // toasts a summary — matches the product-form reference's "always surface something"
    // behavior so a blocked submit never looks like it silently did nothing.
    focusFirstError(summaryMessage) {
        let $firstError = $('.is-invalid').first();
        if ($firstError.length) {
            $('html, body').animate({ scrollTop: $firstError.offset().top - 100 }, 400);
            $firstError.trigger('focus');
        }
        if (summaryMessage && typeof toastMagic !== 'undefined') {
            toastMagic.error(summaryMessage);
        }
    },

    // Field types this engine never checks itself (file/submit/button handled
    // elsewhere or not at all; checkbox/radio handled as groups separately).
    _isSkippableType(type) {
        return type === 'file' || type === 'submit' || type === 'button' || type === 'checkbox' || type === 'radio';
    },

    _isEnhancedSelect($field) {
        return $field.hasClass('select2-hidden-accessible')
            || $field.next('.select2-container').length > 0
            || $field.hasClass('js-select2-custom');
    },

    /**
     * Validates one input/select/textarea against whatever its own markup
     * declares (required, type=email/tel/url/number min-max, minlength,
     * pattern) and renders/clears its inline error immediately. This is the
     * single source of truth for both live (blur/change) checks and the
     * full-form submit-time sweep below, so a field behaves identically
     * whether the user tabs away from it or hits submit.
     * Returns true if the field is valid (or was skipped/not applicable).
     */
    validateSingleField(fieldElement) {
        let $field = $(fieldElement);
        if (!$field.length) return true;
        let tag = $field[0].tagName.toLowerCase();
        let type = tag === 'select' ? 'select' : tag === 'textarea' ? 'textarea' : ($field.attr('type') || 'text').toLowerCase();

        if (type === 'hidden' && !$field.prop('required')) return true;
        if (FormValidators._isSkippableType(type)) return true;
        if ($field.prop('disabled') || $field.prop('readonly')) return true;
        let isEnhancedSelect = FormValidators._isEnhancedSelect($field);
        if (type !== 'hidden' && !isEnhancedSelect && !$field.is(':visible')) return true;

        let required = $field.prop('required');
        let value = ($field.val() || '').toString();

        if (required && value.trim() === '') {
            showFieldError($field, 'This field is required');
            return false;
        }

        if (value.trim() === '') {
            clearFieldError($field);
            return true;
        }

        if (type === 'email' && !FormValidators.EMAIL_PATTERN.test(value)) {
            showFieldError($field, 'Please enter a valid email address');
            return false;
        }

        if (type === 'tel' && !FormValidators.PHONE_PATTERN.test(value)) {
            showFieldError($field, 'Please enter a valid phone number');
            return false;
        }

        if (type === 'url' && !FormValidators.URL_PATTERN.test(value)) {
            showFieldError($field, 'Please enter a valid URL');
            return false;
        }

        if (type === 'number') {
            let num = parseFloat(value);
            let min = $field.attr('min');
            let max = $field.attr('max');
            if (isNaN(num)) {
                showFieldError($field, 'Must be a valid number');
                return false;
            }
            if (min !== undefined && min !== false && num < parseFloat(min)) {
                showFieldError($field, `Must be at least ${min}`);
                return false;
            }
            if (max !== undefined && max !== false && num > parseFloat(max)) {
                showFieldError($field, `Must not exceed ${max}`);
                return false;
            }
        }

        let minLength = $field.attr('minlength');
        if (minLength && value.length < parseInt(minLength, 10)) {
            showFieldError($field, `Must be at least ${minLength} characters`);
            return false;
        }

        let pattern = $field.attr('pattern');
        if (pattern) {
            try {
                if (!(new RegExp('^(?:' + pattern + ')$')).test(value)) {
                    showFieldError($field, $field.attr('title') || 'Please match the requested format');
                    return false;
                }
            } catch (e) { /* invalid pattern attr — skip rather than false-fail */ }
        }

        // Live confirm-password check: if this field IS the confirm field,
        // or the user is editing the password field itself, re-check the pair.
        let name = $field.attr('name') || '';
        if (name === 'confirm_password' || name === 'password_confirmation') {
            let $form = $field.closest('form');
            let $password = $form.find('input[name="password"], .password-check').first();
            if ($password.length && value !== '') {
                if ($password.val() !== value) {
                    showFieldError($field, 'Passwords do not match');
                    return false;
                }
            }
        }
        if (name === 'password' || $field.hasClass('password-check')) {
            let $form = $field.closest('form');
            let $confirm = $form.find('input[name="confirm_password"], input[name="password_confirmation"]').first();
            if ($confirm.length && $confirm.val()) {
                if ($confirm.val() !== value) {
                    showFieldError($confirm, 'Passwords do not match');
                } else {
                    clearFieldError($confirm);
                }
            }
        }

        clearFieldError($field);
        return true;
    },

    /**
     * Generic attribute-driven validator: walks every visible input/select/
     * textarea inside the given form and enforces whatever the markup
     * already declares (required, type=email/tel/url/number with min/max,
     * minlength, pattern), plus auto-pairs a `password` field with a
     * `confirm_password`/`password_confirmation` field in the same form for
     * a match check. This covers the large majority of forms without a
     * hand-written per-field validator — call it from a .form-submit
     * handler or a plain `submit` listener and block on `false`.
     *
     * Deliberately skips fields marked readonly/disabled/hidden and file
     * inputs (file type/size rules vary too much per form to guess safely;
     * validate those explicitly with FormValidators.file() instead).
     */
    autoValidateForm(formSelector) {
        let $form = $(formSelector);
        if (!$form.length) return true;

        let isValid = true;
        let passwordField = null;
        let confirmField = null;

        $form.find('input, select, textarea').each(function () {
            let $field = $(this);
            let name = $field.attr('name') || '';
            if (name === 'password' || $field.hasClass('password-check')) passwordField = $field;
            if (name === 'confirm_password' || name === 'password_confirmation') confirmField = $field;

            if (!FormValidators.validateSingleField($field)) {
                isValid = false;
            }
        });

        if (passwordField && confirmField && confirmField.val()) {
            if (!FormValidators.passwordsMatch(passwordField, confirmField, 'Passwords do not match')) {
                isValid = false;
            }
        }

        // Required radio groups / checkboxes: a single radio input being
        // `required` only matters at the group level (browsers themselves
        // treat `required` on any one radio as "at least one in this name
        // group must be checked"), so check per distinct name instead of
        // per input like the loop above does for text-like fields.
        let checkedGroupNames = {};
        $form.find('input[type="radio"]:visible:not(:disabled)').each(function () {
            let $radio = $(this);
            let name = $radio.attr('name');
            if (!name || !$radio.prop('required')) return;
            checkedGroupNames[name] = checkedGroupNames[name] || $form.find(`input[type="radio"][name="${name}"]:checked`).length > 0;
        });
        Object.keys(checkedGroupNames).forEach(function (name) {
            let $group = $form.find(`input[type="radio"][name="${name}"]`);
            if (!checkedGroupNames[name]) {
                showFieldError($group.first(), 'Please make a selection');
                isValid = false;
            } else {
                clearFieldError($group.first());
            }
        });

        $form.find('input[type="checkbox"]:visible:not(:disabled)[required]').each(function () {
            let $checkbox = $(this);
            if (!$checkbox.prop('checked')) {
                showFieldError($checkbox, 'This must be checked to continue');
                isValid = false;
            } else {
                clearFieldError($checkbox);
            }
        });

        if (!isValid) {
            FormValidators.focusFirstError('Please fix the highlighted fields');
        }

        return isValid;
    },
};
