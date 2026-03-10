import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// jQuery — must be global before DataTables and Select2
import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

// Bootstrap JS
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Select2
import select2 from 'select2';
select2(window, jQuery);

// DataTables
import 'datatables.net';
import 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';

// OverlayScrollbars
import { OverlayScrollbars } from 'overlayscrollbars';
window.OverlayScrollbars = OverlayScrollbars;

// AdminLTE
import 'admin-lte';