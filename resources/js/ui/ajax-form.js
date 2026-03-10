import { showAlertModal } from './alert-modal.js';

function clearFormErrors(form) {
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback.d-block').forEach(el => el.remove());
}

function handleValidationErrors(form, errors) {
    let generalErrors = [];
    let fieldErrors   = [];

    Object.entries(errors).forEach(([field, messages]) => {
        const message = Array.isArray(messages) ? messages[0] : messages;

        if (field === 'general') {
            generalErrors.push(message);
            return;
        }

        const input = form.querySelector(`[name="${field}"]`);
        if (input) {
            input.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback d-block';
            feedback.textContent = message;
            input.closest('.mb-3, .col-md-3, .col-md-4, .col-md-6, .col-md-12')
                ?.appendChild(feedback);
            fieldErrors.push(message);
        } else {
            generalErrors.push(message);
        }
    });

    const allErrors = [...generalErrors, ...fieldErrors];
    if (allErrors.length) {
        const list = allErrors.map(e => `<li>${e}</li>`).join('');
        showAlertModal(
            'Please fix the following errors',
            `<ul class="mb-0">${list}</ul>`,
            'danger'
        );
    }
}

export function initAjaxForms() {
    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            handleAjaxForm(this);
        });
    });
}

function handleAjaxForm(form) {
    const submitBtn    = form.querySelector('[type="submit"]');
    const originalHtml = submitBtn.innerHTML;

    submitBtn.disabled  = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

    clearFormErrors(form);

    fetch(form.action, {
        method: form.method,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json', // ← tells Laravel to return JSON errors
        },
        body: new FormData(form),
    })
        .then(response => response.json().then(data => ({ status: response.status, data })))
        .then(({ status, data }) => {
            if (data.success) {
                showAlertModal('Success', data.message, 'success', function () {
                    if (data.redirect) window.location.href = data.redirect;
                });
            } else if (status === 422) {
                handleValidationErrors(form, data.errors);
            } else {
                showAlertModal('Error', data.message ?? 'Something went wrong.', 'danger');
            }
        })
        .catch(() => {
            showAlertModal('Error', 'An unexpected error occurred. Please try again.', 'danger');
        })
        .finally(() => {
            submitBtn.disabled  = false;
            submitBtn.innerHTML = originalHtml;
        });
}