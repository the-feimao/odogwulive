// Bundled replacements for the CDN libraries used by the legacy admin scripts.
import $ from './admin-jquery';

import 'bootstrap/dist/js/bootstrap.bundle.min.js';

import 'datatables.net-bs4';
import 'datatables.net-buttons-bs4';
import 'datatables.net-buttons/js/buttons.html5.js';
import 'datatables.net-buttons/js/buttons.print.js';
import 'datatables.net-select-bs4';

import jszip from 'jszip';
import pdfMake from 'pdfmake/build/pdfmake';
import 'pdfmake/build/vfs_fonts';

import 'select2';
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

window.jszip = jszip;
window.JSZip = jszip;
window.pdfMake = pdfMake;
window.flatpickr = flatpickr;
window.Chart = Chart;
window.Swal = Swal;
window.moment = moment;

if ($.fn.dataTable && $.fn.dataTable.Buttons) {
    $.fn.dataTable.Buttons.jszip = jszip;
    $.fn.dataTable.Buttons.pdfMake = pdfMake;
}
