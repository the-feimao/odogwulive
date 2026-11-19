import _ from 'lodash';
import $ from 'jquery';
import axios from 'axios';

window._ = _;
window.$ = window.jQuery = $;

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Bootstrap 4's JavaScript plugins expect jQuery and Popper on window.
import('popper.js').then((popper) => {
    window.Popper = popper.default;

    return import('bootstrap');
}).catch(() => {
    // The Bootstrap plugins are optional; the layout still works without them.
});
