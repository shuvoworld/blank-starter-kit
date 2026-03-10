import './bootstrap.js';
import Alpine from 'alpinejs';
import { showAlertModal } from './ui/alert-modal.js';
import { initAjaxForms } from './ui/ajax-form.js';

// expose globally for blade scripts
window.Alpine         = Alpine;
window.showAlertModal = showAlertModal;

Alpine.start();

// Initialize OverlayScrollbars on sidebar
const sidebarWrapper = document.querySelector('.sidebar-wrapper');
if (sidebarWrapper && window.innerWidth > 992) {
    OverlayScrollbars(sidebarWrapper, {
        scrollbars: {
            theme: 'os-theme-light',
            autoHide: 'leave',
            clickScroll: true,
        },
    });
}

// Initialize Select2
document.querySelectorAll('select[data-toggle="select2"]').forEach(function (el) {
    jQuery(el).select2({
        theme: 'bootstrap-5',
        width: '100%',
        allowClear: el.dataset.allowClear === 'true',
    });
});

// Initialize AJAX forms
initAjaxForms();

// Flush onAppReady queue — runs all blade callbacks registered before app.js loaded
window.appReady = true;
window._appReadyQueue.forEach(fn => fn());
window._appReadyQueue = [];
window.dispatchEvent(new Event('app:ready'));