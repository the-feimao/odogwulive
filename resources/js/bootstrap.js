import $ from 'jquery';

window.$ = window.jQuery = $;

// Bootstrap 4's JavaScript plugins expect jQuery and Popper on window.
import('popper.js').then((popper) => {
    window.Popper = popper.default;

    return import('bootstrap');
}).catch(() => {
    // The Bootstrap plugins are optional; the layout still works without them.
});
