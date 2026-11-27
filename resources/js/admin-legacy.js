// Bundled replacements for the CDN libraries used by the legacy admin scripts.
// Bootstrap's stylesheet must load with the admin bundle: Stisla's navbar and
// grid overrides assume the original Bootstrap 4 base rules.
import 'bootstrap/dist/css/bootstrap.min.css';

import $ from './admin-jquery';

import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// These packages expose CommonJS factories instead of installing themselves as
// a side effect. Call them once with the exact jQuery instance used by the
// legacy public/admin/js scripts. PDF/Excel export runtimes live in
// admin-export.js and are only loaded on report pages. The rich text editor
// lives in admin-editor.js and is only loaded on blog forms.
import select2 from 'select2';
import dataTableBs4 from 'datatables.net-bs4';
import buttonsBs4 from 'datatables.net-buttons-bs4';
import buttonsHtml5 from 'datatables.net-buttons/js/buttons.html5.js';
import buttonsPrint from 'datatables.net-buttons/js/buttons.print.js';
import selectBs4 from 'datatables.net-select-bs4';

import flatpickr from 'flatpickr';
import 'summernote/dist/summernote-bs4.js';
import 'jquery.nicescroll';

import Chart from 'chart.js';
import Swal from 'sweetalert2';
import moment from 'moment';

import '@fortawesome/fontawesome-free/css/all.min.css';
import 'datatables.net-bs4/css/dataTables.bootstrap4.min.css';
import 'datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css';
import 'datatables.net-select-bs4/css/select.bootstrap4.min.css';
import 'select2/dist/css/select2.min.css';
import 'flatpickr/dist/flatpickr.min.css';
import 'summernote/dist/summernote-bs4.min.css';
import '../css/vendor/jvectormap.css';

window.flatpickr = flatpickr;
window.Chart = Chart;
window.Swal = Swal;
window.moment = moment;

select2(window, $);
dataTableBs4(window, $);
buttonsBs4(window, $);
buttonsHtml5(window, $);
buttonsPrint(window, $);
selectBs4(window, $);

// The ESM flatpickr entry does not install the jQuery method that myCustom.js
// calls, so register it explicitly on the shared jQuery instance.
$.fn.flatpickr = function (config) {
    return flatpickr(this, config);
};
