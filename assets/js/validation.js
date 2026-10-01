/* ============================================================
   AGRICONNECT — Form Validation (reusable, vanilla)
   Works via HTML5 data-* attributes.
   ============================================================ */

(function (window, document) {
    'use strict';

    const Agri = window.Agri = window.Agri || {};

    // ============================================================
    // 1. VALIDATORS
    // ============================================================
    Agri.validators = {
        required: (v) => v !== null && v !== undefined && String(v).trim() !== '',
        email: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(v).trim()),
        phone: (v) => /^(\+?234|0)[789][01]\d{8}$/.test(String(v).replace(/\s+/g, '')),
        url: (v) => {
            try { new URL(v); return true; } catch { return false; }
        },
        number: (v) => v !== '' && !isNaN(parseFloat(v)),
        integer: (v) => /^-?\d+$/.test(String(v)),
        minLength: (v, min) => String(v).trim().length >= parseInt(min, 10),
        maxLength: (v, max) => String(v).trim().length <= parseInt(max, 10),
        min: (v, min) => parseFloat(v) >= parseFloat(min),
        max: (v, max) => parseFloat(v) <= parseFloat(max),
        match: (v, otherId) => {
            const other = document.getElementById(otherId);
            return other && v === other.value;
        }
    };

    // ============================================================
    // 2. VALIDATE A SINGLE FIELD
    // Returns { valid: bool, message: string }
    // ============================================================
    Agri.validateField = function (field) {
        const value = field.value;
        const errors = [];

        // Required
        if (field.hasAttribute('required') || field.dataset.required === 'true') {
            if (!Agri.validators.required(value)) {
                errors.push(field.dataset.errorRequired || 'This field is required.');
            }
        }

        // Skip other validations if empty and not required
        if (!Agri.validators.required(value)) {
            return { valid: errors.length === 0, message: errors[0] || '' };
        }

        // Type-specific
        const type = field.dataset.validate || field.type;

        if (type === 'email' && !Agri.validators.email(value)) {
            errors.push(field.dataset.errorEmail || 'Please enter a valid email address.');
        }

        if (type === 'phone' || field.dataset.validate === 'phone') {
            if (!Agri.validators.phone(value)) {
                errors.push(field.dataset.errorPhone || 'Enter a valid Nigerian phone number (e.g. 08012345678).');
            }
        }

        if (type === 'number' && !Agri.validators.number(value)) {
            errors.push(field.dataset.errorNumber || 'Please enter a valid number.');
        }

        if (field.dataset.minLength && !Agri.validators.minLength(value, field.dataset.minLength)) {
            errors.push(field.dataset.errorMinlength || `Must be at least ${field.dataset.minLength} characters.`);
        }

        if (field.dataset.maxLength && !Agri.validators.maxLength(value, field.dataset.maxLength)) {
            errors.push(field.dataset.errorMaxlength || `Must be at most ${field.dataset.maxLength} characters.`);
        }

        if (field.dataset.match && !Agri.validators.match(value, field.dataset.match)) {
            errors.push(field.dataset.errorMatch || 'Values do not match.');
        }

        if (field.dataset.min !== undefined && !Agri.validators.min(value, field.dataset.min)) {
            errors.push(field.dataset.errorMin || `Value must be at least ${field.dataset.min}.`);
        }

        if (field.dataset.max !== undefined && !Agri.validators.max(value, field.dataset.max)) {
            errors.push(field.dataset.errorMax || `Value must be at most ${field.dataset.max}.`);
        }

        return { valid: errors.length === 0, message: errors[0] || '' };
    };

    // ============================================================
    // 3. SHOW ERROR UNDER FIELD
    // ============================================================
    Agri.showFieldError = function (field, message) {
        field.classList.add('is-error');
        field.setAttribute('aria-invalid', 'true');

        const group = field.closest('.agri-form-group') || field.parentElement;
        if (!group) return;

        let errorEl = group.querySelector('.agri-form-error[data-for="' + field.name + '"]');
        if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.className = 'agri-form-error';
            errorEl.dataset.for = field.name || '';
            group.appendChild(errorEl);
        }
        errorEl.textContent = message;
        errorEl.style.display = message ? '' : 'none';
    };

    Agri.clearFieldError = function (field) {
        field.classList.remove('is-error');
        field.removeAttribute('aria-invalid');

        const group = field.closest('.agri-form-group') || field.parentElement;
        if (!group) return;

        const errorEl = group.querySelector('.agri-form-error[data-for="' + field.name + '"]');
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.style.display = 'none';
        }
    };

    // ============================================================
    // 4. VALIDATE ENTIRE FORM
    // ============================================================
    Agri.validateForm = function (form) {
        const fields = form.querySelectorAll('input:not([type="submit"]):not([type="button"]):not([type="hidden"]), select, textarea');
        let valid = true;
        let firstInvalid = null;

        fields.forEach(field => {
            if (field.disabled || field.readOnly) return;

            const result = Agri.validateField(field);
            if (!result.valid) {
                valid = false;
                Agri.showFieldError(field, result.message);
                if (!firstInvalid) firstInvalid = field;
            } else {
                Agri.clearFieldError(field);
            }
        });

        if (firstInvalid) {
            firstInvalid.focus();
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return valid;
    };

    // ============================================================
    // 5. AUTO-INIT ON FORMS WITH [data-validate-form]
    // ============================================================
    Agri.initForms = function () {
        document.querySelectorAll('form[data-validate-form]').forEach(form => {
            // Validate on submit
            form.addEventListener('submit', function (e) {
                if (!Agri.validateForm(form)) {
                    e.preventDefault();
                    Agri.toast('Please fix the errors below.', 'danger');
                    return false;
                }
            });

            // Live validate on blur
            form.querySelectorAll('input, select, textarea').forEach(field => {
                field.addEventListener('blur', () => {
                    const result = Agri.validateField(field);
                    if (result.valid) {
                        Agri.clearFieldError(field);
                    } else if (field.value !== '') {
                        // Only show errors if field has been touched
                        Agri.showFieldError(field, result.message);
                    }
                });

                field.addEventListener('input', () => {
                    if (field.classList.contains('is-error')) {
                        const result = Agri.validateField(field);
                        if (result.valid) Agri.clearFieldError(field);
                    }
                });
            });
        });
    };

    // ============================================================
    // 6. AUTO-FORMAT PHONE INPUT
    // Usage: <input type="tel" data-format-phone>
    // ============================================================
    Agri.initPhoneFormatting = function () {
        document.querySelectorAll('[data-format-phone]').forEach(input => {
            input.addEventListener('input', function () {
                let v = this.value.replace(/[^\d+]/g, '');
                // Convert +234 to 0
                if (v.startsWith('+234')) v = '0' + v.slice(4);
                if (v.startsWith('234') && v.length > 3) v = '0' + v.slice(3);
                this.value = v.slice(0, 11);
            });
        });
    };

    // ============================================================
    // 7. AUTO-NUMERIC (only digits)
    // Usage: <input data-format-number>
    // ============================================================
    Agri.initNumberFormatting = function () {
        document.querySelectorAll('[data-format-number]').forEach(input => {
            input.addEventListener('input', function () {
                this.value = this.value.replace(/[^\d.-]/g, '');
            });
        });
    };

    // ============================================================
    // BOOTSTRAP
    // ============================================================
    Agri.ready(function () {
        Agri.initForms();
        Agri.initPhoneFormatting();
        Agri.initNumberFormatting();
    });

})(window, document);