import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Import jQuery - must be first and global before DataTables
import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

// Import Bootstrap JS
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Import Select2 and attach to jQuery
import select2 from 'select2';
select2(window, jQuery);

// Import DataTables - must import the base package first
import 'datatables.net';
import 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';

// DataTables is now available on jQuery automatically

// Import OverlayScrollbars
import { OverlayScrollbars } from 'overlayscrollbars';
window.OverlayScrollbars = OverlayScrollbars;

// Import AdminLTE
import 'admin-lte';

// Initialize OverlayScrollbars on sidebar
document.addEventListener('DOMContentLoaded', function () {
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
});
