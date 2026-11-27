// PDF/Excel export runtime for DataTables reports. Kept out of the global
// admin bundle so ordinary admin pages do not download PDF fonts and JSZip.
import jszip from 'jszip';
import pdfMake from 'pdfmake/build/pdfmake';
import vfsFontsUrl from 'pdfmake/build/vfs_fonts.js?url';

import $ from './admin-jquery';

window.jszip = jszip;
window.JSZip = jszip;
window.pdfMake = pdfMake;

// vfs_fonts.js is a classic script that assigns to `this.pdfMake`. Loaded as
// an ES module its top-level `this` is undefined, so inject it as a normal
// script pointing at the Vite-emitted local asset.
const vfsFontsScript = document.createElement('script');
vfsFontsScript.src = vfsFontsUrl;
vfsFontsScript.async = false;
document.head.appendChild(vfsFontsScript);

if ($.fn.dataTable && $.fn.dataTable.Buttons) {
    $.fn.dataTable.Buttons.jszip(jszip);
    $.fn.dataTable.Buttons.pdfMake(pdfMake);
}
