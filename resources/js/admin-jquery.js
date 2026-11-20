// The legacy admin scripts are plain scripts and expect global jQuery.
// This module must be imported before any jQuery plugin or Bootstrap bundle.
import $ from 'jquery';

window.$ = window.jQuery = $;

export default $;
