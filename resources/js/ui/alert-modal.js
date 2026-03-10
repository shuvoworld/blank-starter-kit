// resources/js/ui/alert-modal.js

export function showAlertModal(title, message, type = 'danger', onClose = null) {
    const colorMap = {
        danger:  { header: 'bg-danger text-white',  btn: 'btn-close-white' },
        success: { header: 'bg-success text-white', btn: 'btn-close-white' },
        warning: { header: 'bg-warning text-dark',  btn: '' },
        info:    { header: 'bg-info text-dark',     btn: '' },
    };

    const { header, btn } = colorMap[type] ?? colorMap.danger;
    const modalEl         = document.getElementById('alertModal');

    if (!modalEl) {
        console.warn('alertModal element not found in DOM');
        return;
    }

    const modalHeader = document.getElementById('alertModalHeader');
    const closeBtn    = modalHeader.querySelector('.btn-close');

    document.getElementById('alertModalTitle').textContent = title;
    document.getElementById('alertModalBody').innerHTML    = message;
    modalHeader.className = `modal-header ${header}`;
    closeBtn.className    = `btn-close ${btn}`;

    const modal = new window.bootstrap.Modal(modalEl);
    modal.show();

    if (onClose) {
        modalEl.addEventListener('hidden.bs.modal', onClose, { once: true });
    }
}