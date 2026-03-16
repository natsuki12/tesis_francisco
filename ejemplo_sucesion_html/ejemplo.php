<!DOCTYPE html>
<html lang=es translate=no class><!--
 Page saved with SingleFile 
 url: https://dgpatrimonios.seniat.gob.ve/sucesion/resumen/calculo/manual 
 saved date: Mon Mar 16 2026 07:28:33 GMT-0400 (hora de Venezuela)
-->
<meta charset=utf-8>
<title>iSeniatV2</title>

<meta http-equiv=Cache-Control content="no-cache, no-store, must-revalidate">
<meta http-equiv=Pragma content=no-cache>
<meta http-equiv=Expires content=0>
<meta http-equiv=Last-Modified content=0>
<meta name=viewport content="width=device-width, initial-scale=1">
<style>
    :root {
        --color-principal-btn: #245b98;
        --color-principal-fuerte: #164193;
        --color-principal-bg: linear-gradient(90deg, var(--color-principal-btn), var(--color-principal-fuerte));
        --accordion-color-ligth: #dce9fc;
        --letra: #fff
    }

    :root {
        --bs-blue: #0d6efd;
        --bs-indigo: #6610f2;
        --bs-purple: #6f42c1;
        --bs-pink: #d63384;
        --bs-red: #dc3545;
        --bs-orange: #fd7e14;
        --bs-yellow: #ffc107;
        --bs-green: #198754;
        --bs-teal: #20c997;
        --bs-cyan: #0dcaf0;
        --bs-black: #000;
        --bs-white: #fff;
        --bs-gray: #6c757d;
        --bs-gray-dark: #343a40;
        --bs-gray-100: #f8f9fa;
        --bs-gray-200: #e9ecef;
        --bs-gray-300: #dee2e6;
        --bs-gray-400: #ced4da;
        --bs-gray-500: #adb5bd;
        --bs-gray-600: #6c757d;
        --bs-gray-700: #495057;
        --bs-gray-800: #343a40;
        --bs-gray-900: #212529;
        --bs-primary: #0d6efd;
        --bs-secondary: #6c757d;
        --bs-success: #198754;
        --bs-info: #0dcaf0;
        --bs-warning: #ffc107;
        --bs-danger: #dc3545;
        --bs-light: #f8f9fa;
        --bs-dark: #212529;
        --bs-primary-rgb: 13, 110, 253;
        --bs-secondary-rgb: 108, 117, 125;
        --bs-success-rgb: 25, 135, 84;
        --bs-info-rgb: 13, 202, 240;
        --bs-warning-rgb: 255, 193, 7;
        --bs-danger-rgb: 220, 53, 69;
        --bs-light-rgb: 248, 249, 250;
        --bs-dark-rgb: 33, 37, 41;
        --bs-primary-text-emphasis: #052c65;
        --bs-secondary-text-emphasis: #2b2f32;
        --bs-success-text-emphasis: #0a3622;
        --bs-info-text-emphasis: #055160;
        --bs-warning-text-emphasis: #664d03;
        --bs-danger-text-emphasis: #58151c;
        --bs-light-text-emphasis: #495057;
        --bs-dark-text-emphasis: #495057;
        --bs-primary-bg-subtle: #cfe2ff;
        --bs-secondary-bg-subtle: #e2e3e5;
        --bs-success-bg-subtle: #d1e7dd;
        --bs-info-bg-subtle: #cff4fc;
        --bs-warning-bg-subtle: #fff3cd;
        --bs-danger-bg-subtle: #f8d7da;
        --bs-light-bg-subtle: #fcfcfd;
        --bs-dark-bg-subtle: #ced4da;
        --bs-primary-border-subtle: #9ec5fe;
        --bs-secondary-border-subtle: #c4c8cb;
        --bs-success-border-subtle: #a3cfbb;
        --bs-info-border-subtle: #9eeaf9;
        --bs-warning-border-subtle: #ffe69c;
        --bs-danger-border-subtle: #f1aeb5;
        --bs-light-border-subtle: #e9ecef;
        --bs-dark-border-subtle: #adb5bd;
        --bs-white-rgb: 255, 255, 255;
        --bs-black-rgb: 0, 0, 0;
        --bs-font-sans-serif: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        --bs-font-monospace: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        --bs-gradient: linear-gradient(180deg, #ffffff26, #fff0);
        --bs-body-font-family: var(--bs-font-sans-serif);
        --bs-body-font-size: 1rem;
        --bs-body-font-weight: 400;
        --bs-body-line-height: 1.5;
        --bs-body-color: #212529;
        --bs-body-color-rgb: 33, 37, 41;
        --bs-body-bg: #fff;
        --bs-body-bg-rgb: 255, 255, 255;
        --bs-emphasis-color: #000;
        --bs-emphasis-color-rgb: 0, 0, 0;
        --bs-secondary-color: #212529bf;
        --bs-secondary-color-rgb: 33, 37, 41;
        --bs-secondary-bg: #e9ecef;
        --bs-secondary-bg-rgb: 233, 236, 239;
        --bs-tertiary-color: #21252980;
        --bs-tertiary-color-rgb: 33, 37, 41;
        --bs-tertiary-bg: #f8f9fa;
        --bs-tertiary-bg-rgb: 248, 249, 250;
        --bs-heading-color: inherit;
        --bs-link-color: #0d6efd;
        --bs-link-color-rgb: 13, 110, 253;
        --bs-link-decoration: underline;
        --bs-link-hover-color: #0a58ca;
        --bs-link-hover-color-rgb: 10, 88, 202;
        --bs-code-color: #d63384;
        --bs-highlight-color: #212529;
        --bs-highlight-bg: #fff3cd;
        --bs-border-width: 1px;
        --bs-border-style: solid;
        --bs-border-color: #dee2e6;
        --bs-border-color-translucent: rgba(0, 0, 0, .175);
        --bs-border-radius: 0.375rem;
        --bs-border-radius-sm: 0.25rem;
        --bs-border-radius-lg: 0.5rem;
        --bs-border-radius-xl: 1rem;
        --bs-border-radius-xxl: 2rem;
        --bs-border-radius-2xl: var(--bs-border-radius-xxl);
        --bs-border-radius-pill: 50rem;
        --bs-box-shadow: 0 0.5rem 1rem #00000026;
        --bs-box-shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, .075);
        --bs-box-shadow-lg: 0 1rem 3rem rgba(0, 0, 0, .175);
        --bs-box-shadow-inset: inset 0 1px 2px rgba(0, 0, 0, .075);
        --bs-focus-ring-width: 0.25rem;
        --bs-focus-ring-opacity: 0.25;
        --bs-focus-ring-color: #0d6efd40;
        --bs-form-valid-color: #198754;
        --bs-form-valid-border-color: #198754;
        --bs-form-invalid-color: #dc3545;
        --bs-form-invalid-border-color: #dc3545
    }

    *,
    :after,
    :before {
        box-sizing: border-box
    }

    @media (prefers-reduced-motion:no-preference) {
        :root {
            scroll-behavior: smooth
        }
    }

    body {
        margin: 0;
        font-family: var(--bs-body-font-family);
        font-size: var(--bs-body-font-size);
        font-weight: var(--bs-body-font-weight);
        line-height: var(--bs-body-line-height);
        color: var(--bs-body-color);
        text-align: var(--bs-body-text-align);
        background-color: var(--bs-body-bg);
        -webkit-text-size-adjust: 100%;
        -webkit-tap-highlight-color: transparent
    }

    :root {
        --bs-breakpoint-xs: 0;
        --bs-breakpoint-sm: 576px;
        --bs-breakpoint-md: 768px;
        --bs-breakpoint-lg: 992px;
        --bs-breakpoint-xl: 1200px;
        --bs-breakpoint-xxl: 1400px
    }

    :root {
        --bs-btn-close-filter:
    }

    :root {
        --bs-carousel-indicator-active-bg: #fff;
        --bs-carousel-caption-color: #fff;
        --bs-carousel-control-icon-filter:
    }
</style>
<style>
    /*!
 * Bootstrap Icons v1.13.1 (https://icons.getbootstrap.com/)
 * Copyright 2019-2024 The Bootstrap Authors
 * Licensed under MIT (https://github.com/twbs/icons/blob/main/LICENSE)
 */
    @font-face {
        font-family: bootstrap-icons;
        src: url(data:font/woff2;base64,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)format("woff2")
    }

    .bi:before,
    [class*=" bi-"]:before,
    [class^=bi-]:before {
        display: inline-block;
        font-family: bootstrap-icons !important;
        font-style: normal;
        font-weight: 400 !important;
        font-variant: normal;
        text-transform: none;
        line-height: 1;
        vertical-align: -.125em;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale
    }

    .bi-123:before {
        content: ""
    }

    .bi-alarm-fill:before {
        content: ""
    }

    .bi-alarm:before {
        content: ""
    }

    .bi-align-bottom:before {
        content: ""
    }

    .bi-align-center:before {
        content: ""
    }

    .bi-align-end:before {
        content: ""
    }

    .bi-align-middle:before {
        content: ""
    }

    .bi-align-start:before {
        content: ""
    }

    .bi-align-top:before {
        content: ""
    }

    .bi-alt:before {
        content: ""
    }

    .bi-app-indicator:before {
        content: ""
    }

    .bi-app:before {
        content: ""
    }

    .bi-archive-fill:before {
        content: ""
    }

    .bi-archive:before {
        content: ""
    }

    .bi-arrow-90deg-down:before {
        content: ""
    }

    .bi-arrow-90deg-left:before {
        content: ""
    }

    .bi-arrow-90deg-right:before {
        content: ""
    }

    .bi-arrow-90deg-up:before {
        content: ""
    }

    .bi-arrow-bar-down:before {
        content: ""
    }

    .bi-arrow-bar-left:before {
        content: ""
    }

    .bi-arrow-bar-right:before {
        content: ""
    }

    .bi-arrow-bar-up:before {
        content: ""
    }

    .bi-arrow-clockwise:before {
        content: ""
    }

    .bi-arrow-counterclockwise:before {
        content: ""
    }

    .bi-arrow-down-circle-fill:before {
        content: ""
    }

    .bi-arrow-down-circle:before {
        content: ""
    }

    .bi-arrow-down-left-circle-fill:before {
        content: ""
    }

    .bi-arrow-down-left-circle:before {
        content: ""
    }

    .bi-arrow-down-left-square-fill:before {
        content: ""
    }

    .bi-arrow-down-left-square:before {
        content: ""
    }

    .bi-arrow-down-left:before {
        content: ""
    }

    .bi-arrow-down-right-circle-fill:before {
        content: ""
    }

    .bi-arrow-down-right-circle:before {
        content: ""
    }

    .bi-arrow-down-right-square-fill:before {
        content: ""
    }

    .bi-arrow-down-right-square:before {
        content: ""
    }

    .bi-arrow-down-right:before {
        content: ""
    }

    .bi-arrow-down-short:before {
        content: ""
    }

    .bi-arrow-down-square-fill:before {
        content: ""
    }

    .bi-arrow-down-square:before {
        content: ""
    }

    .bi-arrow-down-up:before {
        content: ""
    }

    .bi-arrow-down:before {
        content: ""
    }

    .bi-arrow-left-circle-fill:before {
        content: ""
    }

    .bi-arrow-left-circle:before {
        content: ""
    }

    .bi-arrow-left-right:before {
        content: ""
    }

    .bi-arrow-left-short:before {
        content: ""
    }

    .bi-arrow-left-square-fill:before {
        content: ""
    }

    .bi-arrow-left-square:before {
        content: ""
    }

    .bi-arrow-left:before {
        content: ""
    }

    .bi-arrow-repeat:before {
        content: ""
    }

    .bi-arrow-return-left:before {
        content: ""
    }

    .bi-arrow-return-right:before {
        content: ""
    }

    .bi-arrow-right-circle-fill:before {
        content: ""
    }

    .bi-arrow-right-circle:before {
        content: ""
    }

    .bi-arrow-right-short:before {
        content: ""
    }

    .bi-arrow-right-square-fill:before {
        content: ""
    }

    .bi-arrow-right-square:before {
        content: ""
    }

    .bi-arrow-right:before {
        content: ""
    }

    .bi-arrow-up-circle-fill:before {
        content: ""
    }

    .bi-arrow-up-circle:before {
        content: ""
    }

    .bi-arrow-up-left-circle-fill:before {
        content: ""
    }

    .bi-arrow-up-left-circle:before {
        content: ""
    }

    .bi-arrow-up-left-square-fill:before {
        content: ""
    }

    .bi-arrow-up-left-square:before {
        content: ""
    }

    .bi-arrow-up-left:before {
        content: ""
    }

    .bi-arrow-up-right-circle-fill:before {
        content: ""
    }

    .bi-arrow-up-right-circle:before {
        content: ""
    }

    .bi-arrow-up-right-square-fill:before {
        content: ""
    }

    .bi-arrow-up-right-square:before {
        content: ""
    }

    .bi-arrow-up-right:before {
        content: ""
    }

    .bi-arrow-up-short:before {
        content: ""
    }

    .bi-arrow-up-square-fill:before {
        content: ""
    }

    .bi-arrow-up-square:before {
        content: ""
    }

    .bi-arrow-up:before {
        content: ""
    }

    .bi-arrows-angle-contract:before {
        content: ""
    }

    .bi-arrows-angle-expand:before {
        content: ""
    }

    .bi-arrows-collapse:before {
        content: ""
    }

    .bi-arrows-expand:before {
        content: ""
    }

    .bi-arrows-fullscreen:before {
        content: ""
    }

    .bi-arrows-move:before {
        content: ""
    }

    .bi-aspect-ratio-fill:before {
        content: ""
    }

    .bi-aspect-ratio:before {
        content: ""
    }

    .bi-asterisk:before {
        content: ""
    }

    .bi-at:before {
        content: ""
    }

    .bi-award-fill:before {
        content: ""
    }

    .bi-award:before {
        content: ""
    }

    .bi-back:before {
        content: ""
    }

    .bi-backspace-fill:before {
        content: ""
    }

    .bi-backspace-reverse-fill:before {
        content: ""
    }

    .bi-backspace-reverse:before {
        content: ""
    }

    .bi-backspace:before {
        content: ""
    }

    .bi-badge-3d-fill:before {
        content: ""
    }

    .bi-badge-3d:before {
        content: ""
    }

    .bi-badge-4k-fill:before {
        content: ""
    }

    .bi-badge-4k:before {
        content: ""
    }

    .bi-badge-8k-fill:before {
        content: ""
    }

    .bi-badge-8k:before {
        content: ""
    }

    .bi-badge-ad-fill:before {
        content: ""
    }

    .bi-badge-ad:before {
        content: ""
    }

    .bi-badge-ar-fill:before {
        content: ""
    }

    .bi-badge-ar:before {
        content: ""
    }

    .bi-badge-cc-fill:before {
        content: ""
    }

    .bi-badge-cc:before {
        content: ""
    }

    .bi-badge-hd-fill:before {
        content: ""
    }

    .bi-badge-hd:before {
        content: ""
    }

    .bi-badge-tm-fill:before {
        content: ""
    }

    .bi-badge-tm:before {
        content: ""
    }

    .bi-badge-vo-fill:before {
        content: ""
    }

    .bi-badge-vo:before {
        content: ""
    }

    .bi-badge-vr-fill:before {
        content: ""
    }

    .bi-badge-vr:before {
        content: ""
    }

    .bi-badge-wc-fill:before {
        content: ""
    }

    .bi-badge-wc:before {
        content: ""
    }

    .bi-bag-check-fill:before {
        content: ""
    }

    .bi-bag-check:before {
        content: ""
    }

    .bi-bag-dash-fill:before {
        content: ""
    }

    .bi-bag-dash:before {
        content: ""
    }

    .bi-bag-fill:before {
        content: ""
    }

    .bi-bag-plus-fill:before {
        content: ""
    }

    .bi-bag-plus:before {
        content: ""
    }

    .bi-bag-x-fill:before {
        content: ""
    }

    .bi-bag-x:before {
        content: ""
    }

    .bi-bag:before {
        content: ""
    }

    .bi-bar-chart-fill:before {
        content: ""
    }

    .bi-bar-chart-line-fill:before {
        content: ""
    }

    .bi-bar-chart-line:before {
        content: ""
    }

    .bi-bar-chart-steps:before {
        content: ""
    }

    .bi-bar-chart:before {
        content: ""
    }

    .bi-basket-fill:before {
        content: ""
    }

    .bi-basket:before {
        content: ""
    }

    .bi-basket2-fill:before {
        content: ""
    }

    .bi-basket2:before {
        content: ""
    }

    .bi-basket3-fill:before {
        content: ""
    }

    .bi-basket3:before {
        content: ""
    }

    .bi-battery-charging:before {
        content: ""
    }

    .bi-battery-full:before {
        content: ""
    }

    .bi-battery-half:before {
        content: ""
    }

    .bi-battery:before {
        content: ""
    }

    .bi-bell-fill:before {
        content: ""
    }

    .bi-bell:before {
        content: ""
    }

    .bi-bezier:before {
        content: ""
    }

    .bi-bezier2:before {
        content: ""
    }

    .bi-bicycle:before {
        content: ""
    }

    .bi-binoculars-fill:before {
        content: ""
    }

    .bi-binoculars:before {
        content: ""
    }

    .bi-blockquote-left:before {
        content: ""
    }

    .bi-blockquote-right:before {
        content: ""
    }

    .bi-book-fill:before {
        content: ""
    }

    .bi-book-half:before {
        content: ""
    }

    .bi-book:before {
        content: ""
    }

    .bi-bookmark-check-fill:before {
        content: ""
    }

    .bi-bookmark-check:before {
        content: ""
    }

    .bi-bookmark-dash-fill:before {
        content: ""
    }

    .bi-bookmark-dash:before {
        content: ""
    }

    .bi-bookmark-fill:before {
        content: ""
    }

    .bi-bookmark-heart-fill:before {
        content: ""
    }

    .bi-bookmark-heart:before {
        content: ""
    }

    .bi-bookmark-plus-fill:before {
        content: ""
    }

    .bi-bookmark-plus:before {
        content: ""
    }

    .bi-bookmark-star-fill:before {
        content: ""
    }

    .bi-bookmark-star:before {
        content: ""
    }

    .bi-bookmark-x-fill:before {
        content: ""
    }

    .bi-bookmark-x:before {
        content: ""
    }

    .bi-bookmark:before {
        content: ""
    }

    .bi-bookmarks-fill:before {
        content: ""
    }

    .bi-bookmarks:before {
        content: ""
    }

    .bi-bookshelf:before {
        content: ""
    }

    .bi-bootstrap-fill:before {
        content: ""
    }

    .bi-bootstrap-reboot:before {
        content: ""
    }

    .bi-bootstrap:before {
        content: ""
    }

    .bi-border-all:before {
        content: ""
    }

    .bi-border-bottom:before {
        content: ""
    }

    .bi-border-center:before {
        content: ""
    }

    .bi-border-inner:before {
        content: ""
    }

    .bi-border-left:before {
        content: ""
    }

    .bi-border-middle:before {
        content: ""
    }

    .bi-border-outer:before {
        content: ""
    }

    .bi-border-right:before {
        content: ""
    }

    .bi-border-style:before {
        content: ""
    }

    .bi-border-top:before {
        content: ""
    }

    .bi-border-width:before {
        content: ""
    }

    .bi-border:before {
        content: ""
    }

    .bi-bounding-box-circles:before {
        content: ""
    }

    .bi-bounding-box:before {
        content: ""
    }

    .bi-box-arrow-down-left:before {
        content: ""
    }

    .bi-box-arrow-down-right:before {
        content: ""
    }

    .bi-box-arrow-down:before {
        content: ""
    }

    .bi-box-arrow-in-down-left:before {
        content: ""
    }

    .bi-box-arrow-in-down-right:before {
        content: ""
    }

    .bi-box-arrow-in-down:before {
        content: ""
    }

    .bi-box-arrow-in-left:before {
        content: ""
    }

    .bi-box-arrow-in-right:before {
        content: ""
    }

    .bi-box-arrow-in-up-left:before {
        content: ""
    }

    .bi-box-arrow-in-up-right:before {
        content: ""
    }

    .bi-box-arrow-in-up:before {
        content: ""
    }

    .bi-box-arrow-left:before {
        content: ""
    }

    .bi-box-arrow-right:before {
        content: ""
    }

    .bi-box-arrow-up-left:before {
        content: ""
    }

    .bi-box-arrow-up-right:before {
        content: ""
    }

    .bi-box-arrow-up:before {
        content: ""
    }

    .bi-box-seam:before {
        content: ""
    }

    .bi-box:before {
        content: ""
    }

    .bi-braces:before {
        content: ""
    }

    .bi-bricks:before {
        content: ""
    }

    .bi-briefcase-fill:before {
        content: ""
    }

    .bi-briefcase:before {
        content: ""
    }

    .bi-brightness-alt-high-fill:before {
        content: ""
    }

    .bi-brightness-alt-high:before {
        content: ""
    }

    .bi-brightness-alt-low-fill:before {
        content: ""
    }

    .bi-brightness-alt-low:before {
        content: ""
    }

    .bi-brightness-high-fill:before {
        content: ""
    }

    .bi-brightness-high:before {
        content: ""
    }

    .bi-brightness-low-fill:before {
        content: ""
    }

    .bi-brightness-low:before {
        content: ""
    }

    .bi-broadcast-pin:before {
        content: ""
    }

    .bi-broadcast:before {
        content: ""
    }

    .bi-brush-fill:before {
        content: ""
    }

    .bi-brush:before {
        content: ""
    }

    .bi-bucket-fill:before {
        content: ""
    }

    .bi-bucket:before {
        content: ""
    }

    .bi-bug-fill:before {
        content: ""
    }

    .bi-bug:before {
        content: ""
    }

    .bi-building:before {
        content: ""
    }

    .bi-bullseye:before {
        content: ""
    }

    .bi-calculator-fill:before {
        content: ""
    }

    .bi-calculator:before {
        content: ""
    }

    .bi-calendar-check-fill:before {
        content: ""
    }

    .bi-calendar-check:before {
        content: ""
    }

    .bi-calendar-date-fill:before {
        content: ""
    }

    .bi-calendar-date:before {
        content: ""
    }

    .bi-calendar-day-fill:before {
        content: ""
    }

    .bi-calendar-day:before {
        content: ""
    }

    .bi-calendar-event-fill:before {
        content: ""
    }

    .bi-calendar-event:before {
        content: ""
    }

    .bi-calendar-fill:before {
        content: ""
    }

    .bi-calendar-minus-fill:before {
        content: ""
    }

    .bi-calendar-minus:before {
        content: ""
    }

    .bi-calendar-month-fill:before {
        content: ""
    }

    .bi-calendar-month:before {
        content: ""
    }

    .bi-calendar-plus-fill:before {
        content: ""
    }

    .bi-calendar-plus:before {
        content: ""
    }

    .bi-calendar-range-fill:before {
        content: ""
    }

    .bi-calendar-range:before {
        content: ""
    }

    .bi-calendar-week-fill:before {
        content: ""
    }

    .bi-calendar-week:before {
        content: ""
    }

    .bi-calendar-x-fill:before {
        content: ""
    }

    .bi-calendar-x:before {
        content: ""
    }

    .bi-calendar:before {
        content: ""
    }

    .bi-calendar2-check-fill:before {
        content: ""
    }

    .bi-calendar2-check:before {
        content: ""
    }

    .bi-calendar2-date-fill:before {
        content: ""
    }

    .bi-calendar2-date:before {
        content: ""
    }

    .bi-calendar2-day-fill:before {
        content: ""
    }

    .bi-calendar2-day:before {
        content: ""
    }

    .bi-calendar2-event-fill:before {
        content: ""
    }

    .bi-calendar2-event:before {
        content: ""
    }

    .bi-calendar2-fill:before {
        content: ""
    }

    .bi-calendar2-minus-fill:before {
        content: ""
    }

    .bi-calendar2-minus:before {
        content: ""
    }

    .bi-calendar2-month-fill:before {
        content: ""
    }

    .bi-calendar2-month:before {
        content: ""
    }

    .bi-calendar2-plus-fill:before {
        content: ""
    }

    .bi-calendar2-plus:before {
        content: ""
    }

    .bi-calendar2-range-fill:before {
        content: ""
    }

    .bi-calendar2-range:before {
        content: ""
    }

    .bi-calendar2-week-fill:before {
        content: ""
    }

    .bi-calendar2-week:before {
        content: ""
    }

    .bi-calendar2-x-fill:before {
        content: ""
    }

    .bi-calendar2-x:before {
        content: ""
    }

    .bi-calendar2:before {
        content: ""
    }

    .bi-calendar3-event-fill:before {
        content: ""
    }

    .bi-calendar3-event:before {
        content: ""
    }

    .bi-calendar3-fill:before {
        content: ""
    }

    .bi-calendar3-range-fill:before {
        content: ""
    }

    .bi-calendar3-range:before {
        content: ""
    }

    .bi-calendar3-week-fill:before {
        content: ""
    }

    .bi-calendar3-week:before {
        content: ""
    }

    .bi-calendar3:before {
        content: ""
    }

    .bi-calendar4-event:before {
        content: ""
    }

    .bi-calendar4-range:before {
        content: ""
    }

    .bi-calendar4-week:before {
        content: ""
    }

    .bi-calendar4:before {
        content: ""
    }

    .bi-camera-fill:before {
        content: ""
    }

    .bi-camera-reels-fill:before {
        content: ""
    }

    .bi-camera-reels:before {
        content: ""
    }

    .bi-camera-video-fill:before {
        content: ""
    }

    .bi-camera-video-off-fill:before {
        content: ""
    }

    .bi-camera-video-off:before {
        content: ""
    }

    .bi-camera-video:before {
        content: ""
    }

    .bi-camera:before {
        content: ""
    }

    .bi-camera2:before {
        content: ""
    }

    .bi-capslock-fill:before {
        content: ""
    }

    .bi-capslock:before {
        content: ""
    }

    .bi-card-checklist:before {
        content: ""
    }

    .bi-card-heading:before {
        content: ""
    }

    .bi-card-image:before {
        content: ""
    }

    .bi-card-list:before {
        content: ""
    }

    .bi-card-text:before {
        content: ""
    }

    .bi-caret-down-fill:before {
        content: ""
    }

    .bi-caret-down-square-fill:before {
        content: ""
    }

    .bi-caret-down-square:before {
        content: ""
    }

    .bi-caret-down:before {
        content: ""
    }

    .bi-caret-left-fill:before {
        content: ""
    }

    .bi-caret-left-square-fill:before {
        content: ""
    }

    .bi-caret-left-square:before {
        content: ""
    }

    .bi-caret-left:before {
        content: ""
    }

    .bi-caret-right-fill:before {
        content: ""
    }

    .bi-caret-right-square-fill:before {
        content: ""
    }

    .bi-caret-right-square:before {
        content: ""
    }

    .bi-caret-right:before {
        content: ""
    }

    .bi-caret-up-fill:before {
        content: ""
    }

    .bi-caret-up-square-fill:before {
        content: ""
    }

    .bi-caret-up-square:before {
        content: ""
    }

    .bi-caret-up:before {
        content: ""
    }

    .bi-cart-check-fill:before {
        content: ""
    }

    .bi-cart-check:before {
        content: ""
    }

    .bi-cart-dash-fill:before {
        content: ""
    }

    .bi-cart-dash:before {
        content: ""
    }

    .bi-cart-fill:before {
        content: ""
    }

    .bi-cart-plus-fill:before {
        content: ""
    }

    .bi-cart-plus:before {
        content: ""
    }

    .bi-cart-x-fill:before {
        content: ""
    }

    .bi-cart-x:before {
        content: ""
    }

    .bi-cart:before {
        content: ""
    }

    .bi-cart2:before {
        content: ""
    }

    .bi-cart3:before {
        content: ""
    }

    .bi-cart4:before {
        content: ""
    }

    .bi-cash-stack:before {
        content: ""
    }

    .bi-cash:before {
        content: ""
    }

    .bi-cast:before {
        content: ""
    }

    .bi-chat-dots-fill:before {
        content: ""
    }

    .bi-chat-dots:before {
        content: ""
    }

    .bi-chat-fill:before {
        content: ""
    }

    .bi-chat-left-dots-fill:before {
        content: ""
    }

    .bi-chat-left-dots:before {
        content: ""
    }

    .bi-chat-left-fill:before {
        content: ""
    }

    .bi-chat-left-quote-fill:before {
        content: ""
    }

    .bi-chat-left-quote:before {
        content: ""
    }

    .bi-chat-left-text-fill:before {
        content: ""
    }

    .bi-chat-left-text:before {
        content: ""
    }

    .bi-chat-left:before {
        content: ""
    }

    .bi-chat-quote-fill:before {
        content: ""
    }

    .bi-chat-quote:before {
        content: ""
    }

    .bi-chat-right-dots-fill:before {
        content: ""
    }

    .bi-chat-right-dots:before {
        content: ""
    }

    .bi-chat-right-fill:before {
        content: ""
    }

    .bi-chat-right-quote-fill:before {
        content: ""
    }

    .bi-chat-right-quote:before {
        content: ""
    }

    .bi-chat-right-text-fill:before {
        content: ""
    }

    .bi-chat-right-text:before {
        content: ""
    }

    .bi-chat-right:before {
        content: ""
    }

    .bi-chat-square-dots-fill:before {
        content: ""
    }

    .bi-chat-square-dots:before {
        content: ""
    }

    .bi-chat-square-fill:before {
        content: ""
    }

    .bi-chat-square-quote-fill:before {
        content: ""
    }

    .bi-chat-square-quote:before {
        content: ""
    }

    .bi-chat-square-text-fill:before {
        content: ""
    }

    .bi-chat-square-text:before {
        content: ""
    }

    .bi-chat-square:before {
        content: ""
    }

    .bi-chat-text-fill:before {
        content: ""
    }

    .bi-chat-text:before {
        content: ""
    }

    .bi-chat:before {
        content: ""
    }

    .bi-check-all:before {
        content: ""
    }

    .bi-check-circle-fill:before {
        content: ""
    }

    .bi-check-circle:before {
        content: ""
    }

    .bi-check-square-fill:before {
        content: ""
    }

    .bi-check-square:before {
        content: ""
    }

    .bi-check:before {
        content: ""
    }

    .bi-check2-all:before {
        content: ""
    }

    .bi-check2-circle:before {
        content: ""
    }

    .bi-check2-square:before {
        content: ""
    }

    .bi-check2:before {
        content: ""
    }

    .bi-chevron-bar-contract:before {
        content: ""
    }

    .bi-chevron-bar-down:before {
        content: ""
    }

    .bi-chevron-bar-expand:before {
        content: ""
    }

    .bi-chevron-bar-left:before {
        content: ""
    }

    .bi-chevron-bar-right:before {
        content: ""
    }

    .bi-chevron-bar-up:before {
        content: ""
    }

    .bi-chevron-compact-down:before {
        content: ""
    }

    .bi-chevron-compact-left:before {
        content: ""
    }

    .bi-chevron-compact-right:before {
        content: ""
    }

    .bi-chevron-compact-up:before {
        content: ""
    }

    .bi-chevron-contract:before {
        content: ""
    }

    .bi-chevron-double-down:before {
        content: ""
    }

    .bi-chevron-double-left:before {
        content: ""
    }

    .bi-chevron-double-right:before {
        content: ""
    }

    .bi-chevron-double-up:before {
        content: ""
    }

    .bi-chevron-down:before {
        content: ""
    }

    .bi-chevron-expand:before {
        content: ""
    }

    .bi-chevron-left:before {
        content: ""
    }

    .bi-chevron-right:before {
        content: ""
    }

    .bi-chevron-up:before {
        content: ""
    }

    .bi-circle-fill:before {
        content: ""
    }

    .bi-circle-half:before {
        content: ""
    }

    .bi-circle-square:before {
        content: ""
    }

    .bi-circle:before {
        content: ""
    }

    .bi-clipboard-check:before {
        content: ""
    }

    .bi-clipboard-data:before {
        content: ""
    }

    .bi-clipboard-minus:before {
        content: ""
    }

    .bi-clipboard-plus:before {
        content: ""
    }

    .bi-clipboard-x:before {
        content: ""
    }

    .bi-clipboard:before {
        content: ""
    }

    .bi-clock-fill:before {
        content: ""
    }

    .bi-clock-history:before {
        content: ""
    }

    .bi-clock:before {
        content: ""
    }

    .bi-cloud-arrow-down-fill:before {
        content: ""
    }

    .bi-cloud-arrow-down:before {
        content: ""
    }

    .bi-cloud-arrow-up-fill:before {
        content: ""
    }

    .bi-cloud-arrow-up:before {
        content: ""
    }

    .bi-cloud-check-fill:before {
        content: ""
    }

    .bi-cloud-check:before {
        content: ""
    }

    .bi-cloud-download-fill:before {
        content: ""
    }

    .bi-cloud-download:before {
        content: ""
    }

    .bi-cloud-drizzle-fill:before {
        content: ""
    }

    .bi-cloud-drizzle:before {
        content: ""
    }

    .bi-cloud-fill:before {
        content: ""
    }

    .bi-cloud-fog-fill:before {
        content: ""
    }

    .bi-cloud-fog:before {
        content: ""
    }

    .bi-cloud-fog2-fill:before {
        content: ""
    }

    .bi-cloud-fog2:before {
        content: ""
    }

    .bi-cloud-hail-fill:before {
        content: ""
    }

    .bi-cloud-hail:before {
        content: ""
    }

    .bi-cloud-haze-fill:before {
        content: ""
    }

    .bi-cloud-haze:before {
        content: ""
    }

    .bi-cloud-haze2-fill:before {
        content: ""
    }

    .bi-cloud-lightning-fill:before {
        content: ""
    }

    .bi-cloud-lightning-rain-fill:before {
        content: ""
    }

    .bi-cloud-lightning-rain:before {
        content: ""
    }

    .bi-cloud-lightning:before {
        content: ""
    }

    .bi-cloud-minus-fill:before {
        content: ""
    }

    .bi-cloud-minus:before {
        content: ""
    }

    .bi-cloud-moon-fill:before {
        content: ""
    }

    .bi-cloud-moon:before {
        content: ""
    }

    .bi-cloud-plus-fill:before {
        content: ""
    }

    .bi-cloud-plus:before {
        content: ""
    }

    .bi-cloud-rain-fill:before {
        content: ""
    }

    .bi-cloud-rain-heavy-fill:before {
        content: ""
    }

    .bi-cloud-rain-heavy:before {
        content: ""
    }

    .bi-cloud-rain:before {
        content: ""
    }

    .bi-cloud-slash-fill:before {
        content: ""
    }

    .bi-cloud-slash:before {
        content: ""
    }

    .bi-cloud-sleet-fill:before {
        content: ""
    }

    .bi-cloud-sleet:before {
        content: ""
    }

    .bi-cloud-snow-fill:before {
        content: ""
    }

    .bi-cloud-snow:before {
        content: ""
    }

    .bi-cloud-sun-fill:before {
        content: ""
    }

    .bi-cloud-sun:before {
        content: ""
    }

    .bi-cloud-upload-fill:before {
        content: ""
    }

    .bi-cloud-upload:before {
        content: ""
    }

    .bi-cloud:before {
        content: ""
    }

    .bi-clouds-fill:before {
        content: ""
    }

    .bi-clouds:before {
        content: ""
    }

    .bi-cloudy-fill:before {
        content: ""
    }

    .bi-cloudy:before {
        content: ""
    }

    .bi-code-slash:before {
        content: ""
    }

    .bi-code-square:before {
        content: ""
    }

    .bi-code:before {
        content: ""
    }

    .bi-collection-fill:before {
        content: ""
    }

    .bi-collection-play-fill:before {
        content: ""
    }

    .bi-collection-play:before {
        content: ""
    }

    .bi-collection:before {
        content: ""
    }

    .bi-columns-gap:before {
        content: ""
    }

    .bi-columns:before {
        content: ""
    }

    .bi-command:before {
        content: ""
    }

    .bi-compass-fill:before {
        content: ""
    }

    .bi-compass:before {
        content: ""
    }

    .bi-cone-striped:before {
        content: ""
    }

    .bi-cone:before {
        content: ""
    }

    .bi-controller:before {
        content: ""
    }

    .bi-cpu-fill:before {
        content: ""
    }

    .bi-cpu:before {
        content: ""
    }

    .bi-credit-card-2-back-fill:before {
        content: ""
    }

    .bi-credit-card-2-back:before {
        content: ""
    }

    .bi-credit-card-2-front-fill:before {
        content: ""
    }

    .bi-credit-card-2-front:before {
        content: ""
    }

    .bi-credit-card-fill:before {
        content: ""
    }

    .bi-credit-card:before {
        content: ""
    }

    .bi-crop:before {
        content: ""
    }

    .bi-cup-fill:before {
        content: ""
    }

    .bi-cup-straw:before {
        content: ""
    }

    .bi-cup:before {
        content: ""
    }

    .bi-cursor-fill:before {
        content: ""
    }

    .bi-cursor-text:before {
        content: ""
    }

    .bi-cursor:before {
        content: ""
    }

    .bi-dash-circle-dotted:before {
        content: ""
    }

    .bi-dash-circle-fill:before {
        content: ""
    }

    .bi-dash-circle:before {
        content: ""
    }

    .bi-dash-square-dotted:before {
        content: ""
    }

    .bi-dash-square-fill:before {
        content: ""
    }

    .bi-dash-square:before {
        content: ""
    }

    .bi-dash:before {
        content: ""
    }

    .bi-diagram-2-fill:before {
        content: ""
    }

    .bi-diagram-2:before {
        content: ""
    }

    .bi-diagram-3-fill:before {
        content: ""
    }

    .bi-diagram-3:before {
        content: ""
    }

    .bi-diamond-fill:before {
        content: ""
    }

    .bi-diamond-half:before {
        content: ""
    }

    .bi-diamond:before {
        content: ""
    }

    .bi-dice-1-fill:before {
        content: ""
    }

    .bi-dice-1:before {
        content: ""
    }

    .bi-dice-2-fill:before {
        content: ""
    }

    .bi-dice-2:before {
        content: ""
    }

    .bi-dice-3-fill:before {
        content: ""
    }

    .bi-dice-3:before {
        content: ""
    }

    .bi-dice-4-fill:before {
        content: ""
    }

    .bi-dice-4:before {
        content: ""
    }

    .bi-dice-5-fill:before {
        content: ""
    }

    .bi-dice-5:before {
        content: ""
    }

    .bi-dice-6-fill:before {
        content: ""
    }

    .bi-dice-6:before {
        content: ""
    }

    .bi-disc-fill:before {
        content: ""
    }

    .bi-disc:before {
        content: ""
    }

    .bi-discord:before {
        content: ""
    }

    .bi-display-fill:before {
        content: ""
    }

    .bi-display:before {
        content: ""
    }

    .bi-distribute-horizontal:before {
        content: ""
    }

    .bi-distribute-vertical:before {
        content: ""
    }

    .bi-door-closed-fill:before {
        content: ""
    }

    .bi-door-closed:before {
        content: ""
    }

    .bi-door-open-fill:before {
        content: ""
    }

    .bi-door-open:before {
        content: ""
    }

    .bi-dot:before {
        content: ""
    }

    .bi-download:before {
        content: ""
    }

    .bi-droplet-fill:before {
        content: ""
    }

    .bi-droplet-half:before {
        content: ""
    }

    .bi-droplet:before {
        content: ""
    }

    .bi-earbuds:before {
        content: ""
    }

    .bi-easel-fill:before {
        content: ""
    }

    .bi-easel:before {
        content: ""
    }

    .bi-egg-fill:before {
        content: ""
    }

    .bi-egg-fried:before {
        content: ""
    }

    .bi-egg:before {
        content: ""
    }

    .bi-eject-fill:before {
        content: ""
    }

    .bi-eject:before {
        content: ""
    }

    .bi-emoji-angry-fill:before {
        content: ""
    }

    .bi-emoji-angry:before {
        content: ""
    }

    .bi-emoji-dizzy-fill:before {
        content: ""
    }

    .bi-emoji-dizzy:before {
        content: ""
    }

    .bi-emoji-expressionless-fill:before {
        content: ""
    }

    .bi-emoji-expressionless:before {
        content: ""
    }

    .bi-emoji-frown-fill:before {
        content: ""
    }

    .bi-emoji-frown:before {
        content: ""
    }

    .bi-emoji-heart-eyes-fill:before {
        content: ""
    }

    .bi-emoji-heart-eyes:before {
        content: ""
    }

    .bi-emoji-laughing-fill:before {
        content: ""
    }

    .bi-emoji-laughing:before {
        content: ""
    }

    .bi-emoji-neutral-fill:before {
        content: ""
    }

    .bi-emoji-neutral:before {
        content: ""
    }

    .bi-emoji-smile-fill:before {
        content: ""
    }

    .bi-emoji-smile-upside-down-fill:before {
        content: ""
    }

    .bi-emoji-smile-upside-down:before {
        content: ""
    }

    .bi-emoji-smile:before {
        content: ""
    }

    .bi-emoji-sunglasses-fill:before {
        content: ""
    }

    .bi-emoji-sunglasses:before {
        content: ""
    }

    .bi-emoji-wink-fill:before {
        content: ""
    }

    .bi-emoji-wink:before {
        content: ""
    }

    .bi-envelope-fill:before {
        content: ""
    }

    .bi-envelope-open-fill:before {
        content: ""
    }

    .bi-envelope-open:before {
        content: ""
    }

    .bi-envelope:before {
        content: ""
    }

    .bi-eraser-fill:before {
        content: ""
    }

    .bi-eraser:before {
        content: ""
    }

    .bi-exclamation-circle-fill:before {
        content: ""
    }

    .bi-exclamation-circle:before {
        content: ""
    }

    .bi-exclamation-diamond-fill:before {
        content: ""
    }

    .bi-exclamation-diamond:before {
        content: ""
    }

    .bi-exclamation-octagon-fill:before {
        content: ""
    }

    .bi-exclamation-octagon:before {
        content: ""
    }

    .bi-exclamation-square-fill:before {
        content: ""
    }

    .bi-exclamation-square:before {
        content: ""
    }

    .bi-exclamation-triangle-fill:before {
        content: ""
    }

    .bi-exclamation-triangle:before {
        content: ""
    }

    .bi-exclamation:before {
        content: ""
    }

    .bi-exclude:before {
        content: ""
    }

    .bi-eye-fill:before {
        content: ""
    }

    .bi-eye-slash-fill:before {
        content: ""
    }

    .bi-eye-slash:before {
        content: ""
    }

    .bi-eye:before {
        content: ""
    }

    .bi-eyedropper:before {
        content: ""
    }

    .bi-eyeglasses:before {
        content: ""
    }

    .bi-facebook:before {
        content: ""
    }

    .bi-file-arrow-down-fill:before {
        content: ""
    }

    .bi-file-arrow-down:before {
        content: ""
    }

    .bi-file-arrow-up-fill:before {
        content: ""
    }

    .bi-file-arrow-up:before {
        content: ""
    }

    .bi-file-bar-graph-fill:before {
        content: ""
    }

    .bi-file-bar-graph:before {
        content: ""
    }

    .bi-file-binary-fill:before {
        content: ""
    }

    .bi-file-binary:before {
        content: ""
    }

    .bi-file-break-fill:before {
        content: ""
    }

    .bi-file-break:before {
        content: ""
    }

    .bi-file-check-fill:before {
        content: ""
    }

    .bi-file-check:before {
        content: ""
    }

    .bi-file-code-fill:before {
        content: ""
    }

    .bi-file-code:before {
        content: ""
    }

    .bi-file-diff-fill:before {
        content: ""
    }

    .bi-file-diff:before {
        content: ""
    }

    .bi-file-earmark-arrow-down-fill:before {
        content: ""
    }

    .bi-file-earmark-arrow-down:before {
        content: ""
    }

    .bi-file-earmark-arrow-up-fill:before {
        content: ""
    }

    .bi-file-earmark-arrow-up:before {
        content: ""
    }

    .bi-file-earmark-bar-graph-fill:before {
        content: ""
    }

    .bi-file-earmark-bar-graph:before {
        content: ""
    }

    .bi-file-earmark-binary-fill:before {
        content: ""
    }

    .bi-file-earmark-binary:before {
        content: ""
    }

    .bi-file-earmark-break-fill:before {
        content: ""
    }

    .bi-file-earmark-break:before {
        content: ""
    }

    .bi-file-earmark-check-fill:before {
        content: ""
    }

    .bi-file-earmark-check:before {
        content: ""
    }

    .bi-file-earmark-code-fill:before {
        content: ""
    }

    .bi-file-earmark-code:before {
        content: ""
    }

    .bi-file-earmark-diff-fill:before {
        content: ""
    }

    .bi-file-earmark-diff:before {
        content: ""
    }

    .bi-file-earmark-easel-fill:before {
        content: ""
    }

    .bi-file-earmark-easel:before {
        content: ""
    }

    .bi-file-earmark-excel-fill:before {
        content: ""
    }

    .bi-file-earmark-excel:before {
        content: ""
    }

    .bi-file-earmark-fill:before {
        content: ""
    }

    .bi-file-earmark-font-fill:before {
        content: ""
    }

    .bi-file-earmark-font:before {
        content: ""
    }

    .bi-file-earmark-image-fill:before {
        content: ""
    }

    .bi-file-earmark-image:before {
        content: ""
    }

    .bi-file-earmark-lock-fill:before {
        content: ""
    }

    .bi-file-earmark-lock:before {
        content: ""
    }

    .bi-file-earmark-lock2-fill:before {
        content: ""
    }

    .bi-file-earmark-lock2:before {
        content: ""
    }

    .bi-file-earmark-medical-fill:before {
        content: ""
    }

    .bi-file-earmark-medical:before {
        content: ""
    }

    .bi-file-earmark-minus-fill:before {
        content: ""
    }

    .bi-file-earmark-minus:before {
        content: ""
    }

    .bi-file-earmark-music-fill:before {
        content: ""
    }

    .bi-file-earmark-music:before {
        content: ""
    }

    .bi-file-earmark-person-fill:before {
        content: ""
    }

    .bi-file-earmark-person:before {
        content: ""
    }

    .bi-file-earmark-play-fill:before {
        content: ""
    }

    .bi-file-earmark-play:before {
        content: ""
    }

    .bi-file-earmark-plus-fill:before {
        content: ""
    }

    .bi-file-earmark-plus:before {
        content: ""
    }

    .bi-file-earmark-post-fill:before {
        content: ""
    }

    .bi-file-earmark-post:before {
        content: ""
    }

    .bi-file-earmark-ppt-fill:before {
        content: ""
    }

    .bi-file-earmark-ppt:before {
        content: ""
    }

    .bi-file-earmark-richtext-fill:before {
        content: ""
    }

    .bi-file-earmark-richtext:before {
        content: ""
    }

    .bi-file-earmark-ruled-fill:before {
        content: ""
    }

    .bi-file-earmark-ruled:before {
        content: ""
    }

    .bi-file-earmark-slides-fill:before {
        content: ""
    }

    .bi-file-earmark-slides:before {
        content: ""
    }

    .bi-file-earmark-spreadsheet-fill:before {
        content: ""
    }

    .bi-file-earmark-spreadsheet:before {
        content: ""
    }

    .bi-file-earmark-text-fill:before {
        content: ""
    }

    .bi-file-earmark-text:before {
        content: ""
    }

    .bi-file-earmark-word-fill:before {
        content: ""
    }

    .bi-file-earmark-word:before {
        content: ""
    }

    .bi-file-earmark-x-fill:before {
        content: ""
    }

    .bi-file-earmark-x:before {
        content: ""
    }

    .bi-file-earmark-zip-fill:before {
        content: ""
    }

    .bi-file-earmark-zip:before {
        content: ""
    }

    .bi-file-earmark:before {
        content: ""
    }

    .bi-file-easel-fill:before {
        content: ""
    }

    .bi-file-easel:before {
        content: ""
    }

    .bi-file-excel-fill:before {
        content: ""
    }

    .bi-file-excel:before {
        content: ""
    }

    .bi-file-fill:before {
        content: ""
    }

    .bi-file-font-fill:before {
        content: ""
    }

    .bi-file-font:before {
        content: ""
    }

    .bi-file-image-fill:before {
        content: ""
    }

    .bi-file-image:before {
        content: ""
    }

    .bi-file-lock-fill:before {
        content: ""
    }

    .bi-file-lock:before {
        content: ""
    }

    .bi-file-lock2-fill:before {
        content: ""
    }

    .bi-file-lock2:before {
        content: ""
    }

    .bi-file-medical-fill:before {
        content: ""
    }

    .bi-file-medical:before {
        content: ""
    }

    .bi-file-minus-fill:before {
        content: ""
    }

    .bi-file-minus:before {
        content: ""
    }

    .bi-file-music-fill:before {
        content: ""
    }

    .bi-file-music:before {
        content: ""
    }

    .bi-file-person-fill:before {
        content: ""
    }

    .bi-file-person:before {
        content: ""
    }

    .bi-file-play-fill:before {
        content: ""
    }

    .bi-file-play:before {
        content: ""
    }

    .bi-file-plus-fill:before {
        content: ""
    }

    .bi-file-plus:before {
        content: ""
    }

    .bi-file-post-fill:before {
        content: ""
    }

    .bi-file-post:before {
        content: ""
    }

    .bi-file-ppt-fill:before {
        content: ""
    }

    .bi-file-ppt:before {
        content: ""
    }

    .bi-file-richtext-fill:before {
        content: ""
    }

    .bi-file-richtext:before {
        content: ""
    }

    .bi-file-ruled-fill:before {
        content: ""
    }

    .bi-file-ruled:before {
        content: ""
    }

    .bi-file-slides-fill:before {
        content: ""
    }

    .bi-file-slides:before {
        content: ""
    }

    .bi-file-spreadsheet-fill:before {
        content: ""
    }

    .bi-file-spreadsheet:before {
        content: ""
    }

    .bi-file-text-fill:before {
        content: ""
    }

    .bi-file-text:before {
        content: ""
    }

    .bi-file-word-fill:before {
        content: ""
    }

    .bi-file-word:before {
        content: ""
    }

    .bi-file-x-fill:before {
        content: ""
    }

    .bi-file-x:before {
        content: ""
    }

    .bi-file-zip-fill:before {
        content: ""
    }

    .bi-file-zip:before {
        content: ""
    }

    .bi-file:before {
        content: ""
    }

    .bi-files-alt:before {
        content: ""
    }

    .bi-files:before {
        content: ""
    }

    .bi-film:before {
        content: ""
    }

    .bi-filter-circle-fill:before {
        content: ""
    }

    .bi-filter-circle:before {
        content: ""
    }

    .bi-filter-left:before {
        content: ""
    }

    .bi-filter-right:before {
        content: ""
    }

    .bi-filter-square-fill:before {
        content: ""
    }

    .bi-filter-square:before {
        content: ""
    }

    .bi-filter:before {
        content: ""
    }

    .bi-flag-fill:before {
        content: ""
    }

    .bi-flag:before {
        content: ""
    }

    .bi-flower1:before {
        content: ""
    }

    .bi-flower2:before {
        content: ""
    }

    .bi-flower3:before {
        content: ""
    }

    .bi-folder-check:before {
        content: ""
    }

    .bi-folder-fill:before {
        content: ""
    }

    .bi-folder-minus:before {
        content: ""
    }

    .bi-folder-plus:before {
        content: ""
    }

    .bi-folder-symlink-fill:before {
        content: ""
    }

    .bi-folder-symlink:before {
        content: ""
    }

    .bi-folder-x:before {
        content: ""
    }

    .bi-folder:before {
        content: ""
    }

    .bi-folder2-open:before {
        content: ""
    }

    .bi-folder2:before {
        content: ""
    }

    .bi-fonts:before {
        content: ""
    }

    .bi-forward-fill:before {
        content: ""
    }

    .bi-forward:before {
        content: ""
    }

    .bi-front:before {
        content: ""
    }

    .bi-fullscreen-exit:before {
        content: ""
    }

    .bi-fullscreen:before {
        content: ""
    }

    .bi-funnel-fill:before {
        content: ""
    }

    .bi-funnel:before {
        content: ""
    }

    .bi-gear-fill:before {
        content: ""
    }

    .bi-gear-wide-connected:before {
        content: ""
    }

    .bi-gear-wide:before {
        content: ""
    }

    .bi-gear:before {
        content: ""
    }

    .bi-gem:before {
        content: ""
    }

    .bi-geo-alt-fill:before {
        content: ""
    }

    .bi-geo-alt:before {
        content: ""
    }

    .bi-geo-fill:before {
        content: ""
    }

    .bi-geo:before {
        content: ""
    }

    .bi-gift-fill:before {
        content: ""
    }

    .bi-gift:before {
        content: ""
    }

    .bi-github:before {
        content: ""
    }

    .bi-globe:before {
        content: ""
    }

    .bi-globe2:before {
        content: ""
    }

    .bi-google:before {
        content: ""
    }

    .bi-graph-down:before {
        content: ""
    }

    .bi-graph-up:before {
        content: ""
    }

    .bi-grid-1x2-fill:before {
        content: ""
    }

    .bi-grid-1x2:before {
        content: ""
    }

    .bi-grid-3x2-gap-fill:before {
        content: ""
    }

    .bi-grid-3x2-gap:before {
        content: ""
    }

    .bi-grid-3x2:before {
        content: ""
    }

    .bi-grid-3x3-gap-fill:before {
        content: ""
    }

    .bi-grid-3x3-gap:before {
        content: ""
    }

    .bi-grid-3x3:before {
        content: ""
    }

    .bi-grid-fill:before {
        content: ""
    }

    .bi-grid:before {
        content: ""
    }

    .bi-grip-horizontal:before {
        content: ""
    }

    .bi-grip-vertical:before {
        content: ""
    }

    .bi-hammer:before {
        content: ""
    }

    .bi-hand-index-fill:before {
        content: ""
    }

    .bi-hand-index-thumb-fill:before {
        content: ""
    }

    .bi-hand-index-thumb:before {
        content: ""
    }

    .bi-hand-index:before {
        content: ""
    }

    .bi-hand-thumbs-down-fill:before {
        content: ""
    }

    .bi-hand-thumbs-down:before {
        content: ""
    }

    .bi-hand-thumbs-up-fill:before {
        content: ""
    }

    .bi-hand-thumbs-up:before {
        content: ""
    }

    .bi-handbag-fill:before {
        content: ""
    }

    .bi-handbag:before {
        content: ""
    }

    .bi-hash:before {
        content: ""
    }

    .bi-hdd-fill:before {
        content: ""
    }

    .bi-hdd-network-fill:before {
        content: ""
    }

    .bi-hdd-network:before {
        content: ""
    }

    .bi-hdd-rack-fill:before {
        content: ""
    }

    .bi-hdd-rack:before {
        content: ""
    }

    .bi-hdd-stack-fill:before {
        content: ""
    }

    .bi-hdd-stack:before {
        content: ""
    }

    .bi-hdd:before {
        content: ""
    }

    .bi-headphones:before {
        content: ""
    }

    .bi-headset:before {
        content: ""
    }

    .bi-heart-fill:before {
        content: ""
    }

    .bi-heart-half:before {
        content: ""
    }

    .bi-heart:before {
        content: ""
    }

    .bi-heptagon-fill:before {
        content: ""
    }

    .bi-heptagon-half:before {
        content: ""
    }

    .bi-heptagon:before {
        content: ""
    }

    .bi-hexagon-fill:before {
        content: ""
    }

    .bi-hexagon-half:before {
        content: ""
    }

    .bi-hexagon:before {
        content: ""
    }

    .bi-hourglass-bottom:before {
        content: ""
    }

    .bi-hourglass-split:before {
        content: ""
    }

    .bi-hourglass-top:before {
        content: ""
    }

    .bi-hourglass:before {
        content: ""
    }

    .bi-house-door-fill:before {
        content: ""
    }

    .bi-house-door:before {
        content: ""
    }

    .bi-house-fill:before {
        content: ""
    }

    .bi-house:before {
        content: ""
    }

    .bi-hr:before {
        content: ""
    }

    .bi-hurricane:before {
        content: ""
    }

    .bi-image-alt:before {
        content: ""
    }

    .bi-image-fill:before {
        content: ""
    }

    .bi-image:before {
        content: ""
    }

    .bi-images:before {
        content: ""
    }

    .bi-inbox-fill:before {
        content: ""
    }

    .bi-inbox:before {
        content: ""
    }

    .bi-inboxes-fill:before {
        content: ""
    }

    .bi-inboxes:before {
        content: ""
    }

    .bi-info-circle-fill:before {
        content: ""
    }

    .bi-info-circle:before {
        content: ""
    }

    .bi-info-square-fill:before {
        content: ""
    }

    .bi-info-square:before {
        content: ""
    }

    .bi-info:before {
        content: ""
    }

    .bi-input-cursor-text:before {
        content: ""
    }

    .bi-input-cursor:before {
        content: ""
    }

    .bi-instagram:before {
        content: ""
    }

    .bi-intersect:before {
        content: ""
    }

    .bi-journal-album:before {
        content: ""
    }

    .bi-journal-arrow-down:before {
        content: ""
    }

    .bi-journal-arrow-up:before {
        content: ""
    }

    .bi-journal-bookmark-fill:before {
        content: ""
    }

    .bi-journal-bookmark:before {
        content: ""
    }

    .bi-journal-check:before {
        content: ""
    }

    .bi-journal-code:before {
        content: ""
    }

    .bi-journal-medical:before {
        content: ""
    }

    .bi-journal-minus:before {
        content: ""
    }

    .bi-journal-plus:before {
        content: ""
    }

    .bi-journal-richtext:before {
        content: ""
    }

    .bi-journal-text:before {
        content: ""
    }

    .bi-journal-x:before {
        content: ""
    }

    .bi-journal:before {
        content: ""
    }

    .bi-journals:before {
        content: ""
    }

    .bi-joystick:before {
        content: ""
    }

    .bi-justify-left:before {
        content: ""
    }

    .bi-justify-right:before {
        content: ""
    }

    .bi-justify:before {
        content: ""
    }

    .bi-kanban-fill:before {
        content: ""
    }

    .bi-kanban:before {
        content: ""
    }

    .bi-key-fill:before {
        content: ""
    }

    .bi-key:before {
        content: ""
    }

    .bi-keyboard-fill:before {
        content: ""
    }

    .bi-keyboard:before {
        content: ""
    }

    .bi-ladder:before {
        content: ""
    }

    .bi-lamp-fill:before {
        content: ""
    }

    .bi-lamp:before {
        content: ""
    }

    .bi-laptop-fill:before {
        content: ""
    }

    .bi-laptop:before {
        content: ""
    }

    .bi-layer-backward:before {
        content: ""
    }

    .bi-layer-forward:before {
        content: ""
    }

    .bi-layers-fill:before {
        content: ""
    }

    .bi-layers-half:before {
        content: ""
    }

    .bi-layers:before {
        content: ""
    }

    .bi-layout-sidebar-inset-reverse:before {
        content: ""
    }

    .bi-layout-sidebar-inset:before {
        content: ""
    }

    .bi-layout-sidebar-reverse:before {
        content: ""
    }

    .bi-layout-sidebar:before {
        content: ""
    }

    .bi-layout-split:before {
        content: ""
    }

    .bi-layout-text-sidebar-reverse:before {
        content: ""
    }

    .bi-layout-text-sidebar:before {
        content: ""
    }

    .bi-layout-text-window-reverse:before {
        content: ""
    }

    .bi-layout-text-window:before {
        content: ""
    }

    .bi-layout-three-columns:before {
        content: ""
    }

    .bi-layout-wtf:before {
        content: ""
    }

    .bi-life-preserver:before {
        content: ""
    }

    .bi-lightbulb-fill:before {
        content: ""
    }

    .bi-lightbulb-off-fill:before {
        content: ""
    }

    .bi-lightbulb-off:before {
        content: ""
    }

    .bi-lightbulb:before {
        content: ""
    }

    .bi-lightning-charge-fill:before {
        content: ""
    }

    .bi-lightning-charge:before {
        content: ""
    }

    .bi-lightning-fill:before {
        content: ""
    }

    .bi-lightning:before {
        content: ""
    }

    .bi-link-45deg:before {
        content: ""
    }

    .bi-link:before {
        content: ""
    }

    .bi-linkedin:before {
        content: ""
    }

    .bi-list-check:before {
        content: ""
    }

    .bi-list-nested:before {
        content: ""
    }

    .bi-list-ol:before {
        content: ""
    }

    .bi-list-stars:before {
        content: ""
    }

    .bi-list-task:before {
        content: ""
    }

    .bi-list-ul:before {
        content: ""
    }

    .bi-list:before {
        content: ""
    }

    .bi-lock-fill:before {
        content: ""
    }

    .bi-lock:before {
        content: ""
    }

    .bi-mailbox:before {
        content: ""
    }

    .bi-mailbox2:before {
        content: ""
    }

    .bi-map-fill:before {
        content: ""
    }

    .bi-map:before {
        content: ""
    }

    .bi-markdown-fill:before {
        content: ""
    }

    .bi-markdown:before {
        content: ""
    }

    .bi-mask:before {
        content: ""
    }

    .bi-megaphone-fill:before {
        content: ""
    }

    .bi-megaphone:before {
        content: ""
    }

    .bi-menu-app-fill:before {
        content: ""
    }

    .bi-menu-app:before {
        content: ""
    }

    .bi-menu-button-fill:before {
        content: ""
    }

    .bi-menu-button-wide-fill:before {
        content: ""
    }

    .bi-menu-button-wide:before {
        content: ""
    }

    .bi-menu-button:before {
        content: ""
    }

    .bi-menu-down:before {
        content: ""
    }

    .bi-menu-up:before {
        content: ""
    }

    .bi-mic-fill:before {
        content: ""
    }

    .bi-mic-mute-fill:before {
        content: ""
    }

    .bi-mic-mute:before {
        content: ""
    }

    .bi-mic:before {
        content: ""
    }

    .bi-minecart-loaded:before {
        content: ""
    }

    .bi-minecart:before {
        content: ""
    }

    .bi-moisture:before {
        content: ""
    }

    .bi-moon-fill:before {
        content: ""
    }

    .bi-moon-stars-fill:before {
        content: ""
    }

    .bi-moon-stars:before {
        content: ""
    }

    .bi-moon:before {
        content: ""
    }

    .bi-mouse-fill:before {
        content: ""
    }

    .bi-mouse:before {
        content: ""
    }

    .bi-mouse2-fill:before {
        content: ""
    }

    .bi-mouse2:before {
        content: ""
    }

    .bi-mouse3-fill:before {
        content: ""
    }

    .bi-mouse3:before {
        content: ""
    }

    .bi-music-note-beamed:before {
        content: ""
    }

    .bi-music-note-list:before {
        content: ""
    }

    .bi-music-note:before {
        content: ""
    }

    .bi-music-player-fill:before {
        content: ""
    }

    .bi-music-player:before {
        content: ""
    }

    .bi-newspaper:before {
        content: ""
    }

    .bi-node-minus-fill:before {
        content: ""
    }

    .bi-node-minus:before {
        content: ""
    }

    .bi-node-plus-fill:before {
        content: ""
    }

    .bi-node-plus:before {
        content: ""
    }

    .bi-nut-fill:before {
        content: ""
    }

    .bi-nut:before {
        content: ""
    }

    .bi-octagon-fill:before {
        content: ""
    }

    .bi-octagon-half:before {
        content: ""
    }

    .bi-octagon:before {
        content: ""
    }

    .bi-option:before {
        content: ""
    }

    .bi-outlet:before {
        content: ""
    }

    .bi-paint-bucket:before {
        content: ""
    }

    .bi-palette-fill:before {
        content: ""
    }

    .bi-palette:before {
        content: ""
    }

    .bi-palette2:before {
        content: ""
    }

    .bi-paperclip:before {
        content: ""
    }

    .bi-paragraph:before {
        content: ""
    }

    .bi-patch-check-fill:before {
        content: ""
    }

    .bi-patch-check:before {
        content: ""
    }

    .bi-patch-exclamation-fill:before {
        content: ""
    }

    .bi-patch-exclamation:before {
        content: ""
    }

    .bi-patch-minus-fill:before {
        content: ""
    }

    .bi-patch-minus:before {
        content: ""
    }

    .bi-patch-plus-fill:before {
        content: ""
    }

    .bi-patch-plus:before {
        content: ""
    }

    .bi-patch-question-fill:before {
        content: ""
    }

    .bi-patch-question:before {
        content: ""
    }

    .bi-pause-btn-fill:before {
        content: ""
    }

    .bi-pause-btn:before {
        content: ""
    }

    .bi-pause-circle-fill:before {
        content: ""
    }

    .bi-pause-circle:before {
        content: ""
    }

    .bi-pause-fill:before {
        content: ""
    }

    .bi-pause:before {
        content: ""
    }

    .bi-peace-fill:before {
        content: ""
    }

    .bi-peace:before {
        content: ""
    }

    .bi-pen-fill:before {
        content: ""
    }

    .bi-pen:before {
        content: ""
    }

    .bi-pencil-fill:before {
        content: ""
    }

    .bi-pencil-square:before {
        content: ""
    }

    .bi-pencil:before {
        content: ""
    }

    .bi-pentagon-fill:before {
        content: ""
    }

    .bi-pentagon-half:before {
        content: ""
    }

    .bi-pentagon:before {
        content: ""
    }

    .bi-people-fill:before {
        content: ""
    }

    .bi-people:before {
        content: ""
    }

    .bi-percent:before {
        content: ""
    }

    .bi-person-badge-fill:before {
        content: ""
    }

    .bi-person-badge:before {
        content: ""
    }

    .bi-person-bounding-box:before {
        content: ""
    }

    .bi-person-check-fill:before {
        content: ""
    }

    .bi-person-check:before {
        content: ""
    }

    .bi-person-circle:before {
        content: ""
    }

    .bi-person-dash-fill:before {
        content: ""
    }

    .bi-person-dash:before {
        content: ""
    }

    .bi-person-fill:before {
        content: ""
    }

    .bi-person-lines-fill:before {
        content: ""
    }

    .bi-person-plus-fill:before {
        content: ""
    }

    .bi-person-plus:before {
        content: ""
    }

    .bi-person-square:before {
        content: ""
    }

    .bi-person-x-fill:before {
        content: ""
    }

    .bi-person-x:before {
        content: ""
    }

    .bi-person:before {
        content: ""
    }

    .bi-phone-fill:before {
        content: ""
    }

    .bi-phone-landscape-fill:before {
        content: ""
    }

    .bi-phone-landscape:before {
        content: ""
    }

    .bi-phone-vibrate-fill:before {
        content: ""
    }

    .bi-phone-vibrate:before {
        content: ""
    }

    .bi-phone:before {
        content: ""
    }

    .bi-pie-chart-fill:before {
        content: ""
    }

    .bi-pie-chart:before {
        content: ""
    }

    .bi-pin-angle-fill:before {
        content: ""
    }

    .bi-pin-angle:before {
        content: ""
    }

    .bi-pin-fill:before {
        content: ""
    }

    .bi-pin:before {
        content: ""
    }

    .bi-pip-fill:before {
        content: ""
    }

    .bi-pip:before {
        content: ""
    }

    .bi-play-btn-fill:before {
        content: ""
    }

    .bi-play-btn:before {
        content: ""
    }

    .bi-play-circle-fill:before {
        content: ""
    }

    .bi-play-circle:before {
        content: ""
    }

    .bi-play-fill:before {
        content: ""
    }

    .bi-play:before {
        content: ""
    }

    .bi-plug-fill:before {
        content: ""
    }

    .bi-plug:before {
        content: ""
    }

    .bi-plus-circle-dotted:before {
        content: ""
    }

    .bi-plus-circle-fill:before {
        content: ""
    }

    .bi-plus-circle:before {
        content: ""
    }

    .bi-plus-square-dotted:before {
        content: ""
    }

    .bi-plus-square-fill:before {
        content: ""
    }

    .bi-plus-square:before {
        content: ""
    }

    .bi-plus:before {
        content: ""
    }

    .bi-power:before {
        content: ""
    }

    .bi-printer-fill:before {
        content: ""
    }

    .bi-printer:before {
        content: ""
    }

    .bi-puzzle-fill:before {
        content: ""
    }

    .bi-puzzle:before {
        content: ""
    }

    .bi-question-circle-fill:before {
        content: ""
    }

    .bi-question-circle:before {
        content: ""
    }

    .bi-question-diamond-fill:before {
        content: ""
    }

    .bi-question-diamond:before {
        content: ""
    }

    .bi-question-octagon-fill:before {
        content: ""
    }

    .bi-question-octagon:before {
        content: ""
    }

    .bi-question-square-fill:before {
        content: ""
    }

    .bi-question-square:before {
        content: ""
    }

    .bi-question:before {
        content: ""
    }

    .bi-rainbow:before {
        content: ""
    }

    .bi-receipt-cutoff:before {
        content: ""
    }

    .bi-receipt:before {
        content: ""
    }

    .bi-reception-0:before {
        content: ""
    }

    .bi-reception-1:before {
        content: ""
    }

    .bi-reception-2:before {
        content: ""
    }

    .bi-reception-3:before {
        content: ""
    }

    .bi-reception-4:before {
        content: ""
    }

    .bi-record-btn-fill:before {
        content: ""
    }

    .bi-record-btn:before {
        content: ""
    }

    .bi-record-circle-fill:before {
        content: ""
    }

    .bi-record-circle:before {
        content: ""
    }

    .bi-record-fill:before {
        content: ""
    }

    .bi-record:before {
        content: ""
    }

    .bi-record2-fill:before {
        content: ""
    }

    .bi-record2:before {
        content: ""
    }

    .bi-reply-all-fill:before {
        content: ""
    }

    .bi-reply-all:before {
        content: ""
    }

    .bi-reply-fill:before {
        content: ""
    }

    .bi-reply:before {
        content: ""
    }

    .bi-rss-fill:before {
        content: ""
    }

    .bi-rss:before {
        content: ""
    }

    .bi-rulers:before {
        content: ""
    }

    .bi-save-fill:before {
        content: ""
    }

    .bi-save:before {
        content: ""
    }

    .bi-save2-fill:before {
        content: ""
    }

    .bi-save2:before {
        content: ""
    }

    .bi-scissors:before {
        content: ""
    }

    .bi-screwdriver:before {
        content: ""
    }

    .bi-search:before {
        content: ""
    }

    .bi-segmented-nav:before {
        content: ""
    }

    .bi-server:before {
        content: ""
    }

    .bi-share-fill:before {
        content: ""
    }

    .bi-share:before {
        content: ""
    }

    .bi-shield-check:before {
        content: ""
    }

    .bi-shield-exclamation:before {
        content: ""
    }

    .bi-shield-fill-check:before {
        content: ""
    }

    .bi-shield-fill-exclamation:before {
        content: ""
    }

    .bi-shield-fill-minus:before {
        content: ""
    }

    .bi-shield-fill-plus:before {
        content: ""
    }

    .bi-shield-fill-x:before {
        content: ""
    }

    .bi-shield-fill:before {
        content: ""
    }

    .bi-shield-lock-fill:before {
        content: ""
    }

    .bi-shield-lock:before {
        content: ""
    }

    .bi-shield-minus:before {
        content: ""
    }

    .bi-shield-plus:before {
        content: ""
    }

    .bi-shield-shaded:before {
        content: ""
    }

    .bi-shield-slash-fill:before {
        content: ""
    }

    .bi-shield-slash:before {
        content: ""
    }

    .bi-shield-x:before {
        content: ""
    }

    .bi-shield:before {
        content: ""
    }

    .bi-shift-fill:before {
        content: ""
    }

    .bi-shift:before {
        content: ""
    }

    .bi-shop-window:before {
        content: ""
    }

    .bi-shop:before {
        content: ""
    }

    .bi-shuffle:before {
        content: ""
    }

    .bi-signpost-2-fill:before {
        content: ""
    }

    .bi-signpost-2:before {
        content: ""
    }

    .bi-signpost-fill:before {
        content: ""
    }

    .bi-signpost-split-fill:before {
        content: ""
    }

    .bi-signpost-split:before {
        content: ""
    }

    .bi-signpost:before {
        content: ""
    }

    .bi-sim-fill:before {
        content: ""
    }

    .bi-sim:before {
        content: ""
    }

    .bi-skip-backward-btn-fill:before {
        content: ""
    }

    .bi-skip-backward-btn:before {
        content: ""
    }

    .bi-skip-backward-circle-fill:before {
        content: ""
    }

    .bi-skip-backward-circle:before {
        content: ""
    }

    .bi-skip-backward-fill:before {
        content: ""
    }

    .bi-skip-backward:before {
        content: ""
    }

    .bi-skip-end-btn-fill:before {
        content: ""
    }

    .bi-skip-end-btn:before {
        content: ""
    }

    .bi-skip-end-circle-fill:before {
        content: ""
    }

    .bi-skip-end-circle:before {
        content: ""
    }

    .bi-skip-end-fill:before {
        content: ""
    }

    .bi-skip-end:before {
        content: ""
    }

    .bi-skip-forward-btn-fill:before {
        content: ""
    }

    .bi-skip-forward-btn:before {
        content: ""
    }

    .bi-skip-forward-circle-fill:before {
        content: ""
    }

    .bi-skip-forward-circle:before {
        content: ""
    }

    .bi-skip-forward-fill:before {
        content: ""
    }

    .bi-skip-forward:before {
        content: ""
    }

    .bi-skip-start-btn-fill:before {
        content: ""
    }

    .bi-skip-start-btn:before {
        content: ""
    }

    .bi-skip-start-circle-fill:before {
        content: ""
    }

    .bi-skip-start-circle:before {
        content: ""
    }

    .bi-skip-start-fill:before {
        content: ""
    }

    .bi-skip-start:before {
        content: ""
    }

    .bi-slack:before {
        content: ""
    }

    .bi-slash-circle-fill:before {
        content: ""
    }

    .bi-slash-circle:before {
        content: ""
    }

    .bi-slash-square-fill:before {
        content: ""
    }

    .bi-slash-square:before {
        content: ""
    }

    .bi-slash:before {
        content: ""
    }

    .bi-sliders:before {
        content: ""
    }

    .bi-smartwatch:before {
        content: ""
    }

    .bi-snow:before {
        content: ""
    }

    .bi-snow2:before {
        content: ""
    }

    .bi-snow3:before {
        content: ""
    }

    .bi-sort-alpha-down-alt:before {
        content: ""
    }

    .bi-sort-alpha-down:before {
        content: ""
    }

    .bi-sort-alpha-up-alt:before {
        content: ""
    }

    .bi-sort-alpha-up:before {
        content: ""
    }

    .bi-sort-down-alt:before {
        content: ""
    }

    .bi-sort-down:before {
        content: ""
    }

    .bi-sort-numeric-down-alt:before {
        content: ""
    }

    .bi-sort-numeric-down:before {
        content: ""
    }

    .bi-sort-numeric-up-alt:before {
        content: ""
    }

    .bi-sort-numeric-up:before {
        content: ""
    }

    .bi-sort-up-alt:before {
        content: ""
    }

    .bi-sort-up:before {
        content: ""
    }

    .bi-soundwave:before {
        content: ""
    }

    .bi-speaker-fill:before {
        content: ""
    }

    .bi-speaker:before {
        content: ""
    }

    .bi-speedometer:before {
        content: ""
    }

    .bi-speedometer2:before {
        content: ""
    }

    .bi-spellcheck:before {
        content: ""
    }

    .bi-square-fill:before {
        content: ""
    }

    .bi-square-half:before {
        content: ""
    }

    .bi-square:before {
        content: ""
    }

    .bi-stack:before {
        content: ""
    }

    .bi-star-fill:before {
        content: ""
    }

    .bi-star-half:before {
        content: ""
    }

    .bi-star:before {
        content: ""
    }

    .bi-stars:before {
        content: ""
    }

    .bi-stickies-fill:before {
        content: ""
    }

    .bi-stickies:before {
        content: ""
    }

    .bi-sticky-fill:before {
        content: ""
    }

    .bi-sticky:before {
        content: ""
    }

    .bi-stop-btn-fill:before {
        content: ""
    }

    .bi-stop-btn:before {
        content: ""
    }

    .bi-stop-circle-fill:before {
        content: ""
    }

    .bi-stop-circle:before {
        content: ""
    }

    .bi-stop-fill:before {
        content: ""
    }

    .bi-stop:before {
        content: ""
    }

    .bi-stoplights-fill:before {
        content: ""
    }

    .bi-stoplights:before {
        content: ""
    }

    .bi-stopwatch-fill:before {
        content: ""
    }

    .bi-stopwatch:before {
        content: ""
    }

    .bi-subtract:before {
        content: ""
    }

    .bi-suit-club-fill:before {
        content: ""
    }

    .bi-suit-club:before {
        content: ""
    }

    .bi-suit-diamond-fill:before {
        content: ""
    }

    .bi-suit-diamond:before {
        content: ""
    }

    .bi-suit-heart-fill:before {
        content: ""
    }

    .bi-suit-heart:before {
        content: ""
    }

    .bi-suit-spade-fill:before {
        content: ""
    }

    .bi-suit-spade:before {
        content: ""
    }

    .bi-sun-fill:before {
        content: ""
    }

    .bi-sun:before {
        content: ""
    }

    .bi-sunglasses:before {
        content: ""
    }

    .bi-sunrise-fill:before {
        content: ""
    }

    .bi-sunrise:before {
        content: ""
    }

    .bi-sunset-fill:before {
        content: ""
    }

    .bi-sunset:before {
        content: ""
    }

    .bi-symmetry-horizontal:before {
        content: ""
    }

    .bi-symmetry-vertical:before {
        content: ""
    }

    .bi-table:before {
        content: ""
    }

    .bi-tablet-fill:before {
        content: ""
    }

    .bi-tablet-landscape-fill:before {
        content: ""
    }

    .bi-tablet-landscape:before {
        content: ""
    }

    .bi-tablet:before {
        content: ""
    }

    .bi-tag-fill:before {
        content: ""
    }

    .bi-tag:before {
        content: ""
    }

    .bi-tags-fill:before {
        content: ""
    }

    .bi-tags:before {
        content: ""
    }

    .bi-telegram:before {
        content: ""
    }

    .bi-telephone-fill:before {
        content: ""
    }

    .bi-telephone-forward-fill:before {
        content: ""
    }

    .bi-telephone-forward:before {
        content: ""
    }

    .bi-telephone-inbound-fill:before {
        content: ""
    }

    .bi-telephone-inbound:before {
        content: ""
    }

    .bi-telephone-minus-fill:before {
        content: ""
    }

    .bi-telephone-minus:before {
        content: ""
    }

    .bi-telephone-outbound-fill:before {
        content: ""
    }

    .bi-telephone-outbound:before {
        content: ""
    }

    .bi-telephone-plus-fill:before {
        content: ""
    }

    .bi-telephone-plus:before {
        content: ""
    }

    .bi-telephone-x-fill:before {
        content: ""
    }

    .bi-telephone-x:before {
        content: ""
    }

    .bi-telephone:before {
        content: ""
    }

    .bi-terminal-fill:before {
        content: ""
    }

    .bi-terminal:before {
        content: ""
    }

    .bi-text-center:before {
        content: ""
    }

    .bi-text-indent-left:before {
        content: ""
    }

    .bi-text-indent-right:before {
        content: ""
    }

    .bi-text-left:before {
        content: ""
    }

    .bi-text-paragraph:before {
        content: ""
    }

    .bi-text-right:before {
        content: ""
    }

    .bi-textarea-resize:before {
        content: ""
    }

    .bi-textarea-t:before {
        content: ""
    }

    .bi-textarea:before {
        content: ""
    }

    .bi-thermometer-half:before {
        content: ""
    }

    .bi-thermometer-high:before {
        content: ""
    }

    .bi-thermometer-low:before {
        content: ""
    }

    .bi-thermometer-snow:before {
        content: ""
    }

    .bi-thermometer-sun:before {
        content: ""
    }

    .bi-thermometer:before {
        content: ""
    }

    .bi-three-dots-vertical:before {
        content: ""
    }

    .bi-three-dots:before {
        content: ""
    }

    .bi-toggle-off:before {
        content: ""
    }

    .bi-toggle-on:before {
        content: ""
    }

    .bi-toggle2-off:before {
        content: ""
    }

    .bi-toggle2-on:before {
        content: ""
    }

    .bi-toggles:before {
        content: ""
    }

    .bi-toggles2:before {
        content: ""
    }

    .bi-tools:before {
        content: ""
    }

    .bi-tornado:before {
        content: ""
    }

    .bi-trash-fill:before {
        content: ""
    }

    .bi-trash:before {
        content: ""
    }

    .bi-trash2-fill:before {
        content: ""
    }

    .bi-trash2:before {
        content: ""
    }

    .bi-tree-fill:before {
        content: ""
    }

    .bi-tree:before {
        content: ""
    }

    .bi-triangle-fill:before {
        content: ""
    }

    .bi-triangle-half:before {
        content: ""
    }

    .bi-triangle:before {
        content: ""
    }

    .bi-trophy-fill:before {
        content: ""
    }

    .bi-trophy:before {
        content: ""
    }

    .bi-tropical-storm:before {
        content: ""
    }

    .bi-truck-flatbed:before {
        content: ""
    }

    .bi-truck:before {
        content: ""
    }

    .bi-tsunami:before {
        content: ""
    }

    .bi-tv-fill:before {
        content: ""
    }

    .bi-tv:before {
        content: ""
    }

    .bi-twitch:before {
        content: ""
    }

    .bi-twitter:before {
        content: ""
    }

    .bi-type-bold:before {
        content: ""
    }

    .bi-type-h1:before {
        content: ""
    }

    .bi-type-h2:before {
        content: ""
    }

    .bi-type-h3:before {
        content: ""
    }

    .bi-type-italic:before {
        content: ""
    }

    .bi-type-strikethrough:before {
        content: ""
    }

    .bi-type-underline:before {
        content: ""
    }

    .bi-type:before {
        content: ""
    }

    .bi-ui-checks-grid:before {
        content: ""
    }

    .bi-ui-checks:before {
        content: ""
    }

    .bi-ui-radios-grid:before {
        content: ""
    }

    .bi-ui-radios:before {
        content: ""
    }

    .bi-umbrella-fill:before {
        content: ""
    }

    .bi-umbrella:before {
        content: ""
    }

    .bi-union:before {
        content: ""
    }

    .bi-unlock-fill:before {
        content: ""
    }

    .bi-unlock:before {
        content: ""
    }

    .bi-upc-scan:before {
        content: ""
    }

    .bi-upc:before {
        content: ""
    }

    .bi-upload:before {
        content: ""
    }

    .bi-vector-pen:before {
        content: ""
    }

    .bi-view-list:before {
        content: ""
    }

    .bi-view-stacked:before {
        content: ""
    }

    .bi-vinyl-fill:before {
        content: ""
    }

    .bi-vinyl:before {
        content: ""
    }

    .bi-voicemail:before {
        content: ""
    }

    .bi-volume-down-fill:before {
        content: ""
    }

    .bi-volume-down:before {
        content: ""
    }

    .bi-volume-mute-fill:before {
        content: ""
    }

    .bi-volume-mute:before {
        content: ""
    }

    .bi-volume-off-fill:before {
        content: ""
    }

    .bi-volume-off:before {
        content: ""
    }

    .bi-volume-up-fill:before {
        content: ""
    }

    .bi-volume-up:before {
        content: ""
    }

    .bi-vr:before {
        content: ""
    }

    .bi-wallet-fill:before {
        content: ""
    }

    .bi-wallet:before {
        content: ""
    }

    .bi-wallet2:before {
        content: ""
    }

    .bi-watch:before {
        content: ""
    }

    .bi-water:before {
        content: ""
    }

    .bi-whatsapp:before {
        content: ""
    }

    .bi-wifi-1:before {
        content: ""
    }

    .bi-wifi-2:before {
        content: ""
    }

    .bi-wifi-off:before {
        content: ""
    }

    .bi-wifi:before {
        content: ""
    }

    .bi-wind:before {
        content: ""
    }

    .bi-window-dock:before {
        content: ""
    }

    .bi-window-sidebar:before {
        content: ""
    }

    .bi-window:before {
        content: ""
    }

    .bi-wrench:before {
        content: ""
    }

    .bi-x-circle-fill:before {
        content: ""
    }

    .bi-x-circle:before {
        content: ""
    }

    .bi-x-diamond-fill:before {
        content: ""
    }

    .bi-x-diamond:before {
        content: ""
    }

    .bi-x-octagon-fill:before {
        content: ""
    }

    .bi-x-octagon:before {
        content: ""
    }

    .bi-x-square-fill:before {
        content: ""
    }

    .bi-x-square:before {
        content: ""
    }

    .bi-x:before {
        content: ""
    }

    .bi-youtube:before {
        content: ""
    }

    .bi-zoom-in:before {
        content: ""
    }

    .bi-zoom-out:before {
        content: ""
    }

    .bi-bank:before {
        content: ""
    }

    .bi-bank2:before {
        content: ""
    }

    .bi-bell-slash-fill:before {
        content: ""
    }

    .bi-bell-slash:before {
        content: ""
    }

    .bi-cash-coin:before {
        content: ""
    }

    .bi-check-lg:before {
        content: ""
    }

    .bi-coin:before {
        content: ""
    }

    .bi-currency-bitcoin:before {
        content: ""
    }

    .bi-currency-dollar:before {
        content: ""
    }

    .bi-currency-euro:before {
        content: ""
    }

    .bi-currency-exchange:before {
        content: ""
    }

    .bi-currency-pound:before {
        content: ""
    }

    .bi-currency-yen:before {
        content: ""
    }

    .bi-dash-lg:before {
        content: ""
    }

    .bi-exclamation-lg:before {
        content: ""
    }

    .bi-file-earmark-pdf-fill:before {
        content: ""
    }

    .bi-file-earmark-pdf:before {
        content: ""
    }

    .bi-file-pdf-fill:before {
        content: ""
    }

    .bi-file-pdf:before {
        content: ""
    }

    .bi-gender-ambiguous:before {
        content: ""
    }

    .bi-gender-female:before {
        content: ""
    }

    .bi-gender-male:before {
        content: ""
    }

    .bi-gender-trans:before {
        content: ""
    }

    .bi-headset-vr:before {
        content: ""
    }

    .bi-info-lg:before {
        content: ""
    }

    .bi-mastodon:before {
        content: ""
    }

    .bi-messenger:before {
        content: ""
    }

    .bi-piggy-bank-fill:before {
        content: ""
    }

    .bi-piggy-bank:before {
        content: ""
    }

    .bi-pin-map-fill:before {
        content: ""
    }

    .bi-pin-map:before {
        content: ""
    }

    .bi-plus-lg:before {
        content: ""
    }

    .bi-question-lg:before {
        content: ""
    }

    .bi-recycle:before {
        content: ""
    }

    .bi-reddit:before {
        content: ""
    }

    .bi-safe-fill:before {
        content: ""
    }

    .bi-safe2-fill:before {
        content: ""
    }

    .bi-safe2:before {
        content: ""
    }

    .bi-sd-card-fill:before {
        content: ""
    }

    .bi-sd-card:before {
        content: ""
    }

    .bi-skype:before {
        content: ""
    }

    .bi-slash-lg:before {
        content: ""
    }

    .bi-translate:before {
        content: ""
    }

    .bi-x-lg:before {
        content: ""
    }

    .bi-safe:before {
        content: ""
    }

    .bi-apple:before {
        content: ""
    }

    .bi-microsoft:before {
        content: ""
    }

    .bi-windows:before {
        content: ""
    }

    .bi-behance:before {
        content: ""
    }

    .bi-dribbble:before {
        content: ""
    }

    .bi-line:before {
        content: ""
    }

    .bi-medium:before {
        content: ""
    }

    .bi-paypal:before {
        content: ""
    }

    .bi-pinterest:before {
        content: ""
    }

    .bi-signal:before {
        content: ""
    }

    .bi-snapchat:before {
        content: ""
    }

    .bi-spotify:before {
        content: ""
    }

    .bi-stack-overflow:before {
        content: ""
    }

    .bi-strava:before {
        content: ""
    }

    .bi-wordpress:before {
        content: ""
    }

    .bi-vimeo:before {
        content: ""
    }

    .bi-activity:before {
        content: ""
    }

    .bi-easel2-fill:before {
        content: ""
    }

    .bi-easel2:before {
        content: ""
    }

    .bi-easel3-fill:before {
        content: ""
    }

    .bi-easel3:before {
        content: ""
    }

    .bi-fan:before {
        content: ""
    }

    .bi-fingerprint:before {
        content: ""
    }

    .bi-graph-down-arrow:before {
        content: ""
    }

    .bi-graph-up-arrow:before {
        content: ""
    }

    .bi-hypnotize:before {
        content: ""
    }

    .bi-magic:before {
        content: ""
    }

    .bi-person-rolodex:before {
        content: ""
    }

    .bi-person-video:before {
        content: ""
    }

    .bi-person-video2:before {
        content: ""
    }

    .bi-person-video3:before {
        content: ""
    }

    .bi-person-workspace:before {
        content: ""
    }

    .bi-radioactive:before {
        content: ""
    }

    .bi-webcam-fill:before {
        content: ""
    }

    .bi-webcam:before {
        content: ""
    }

    .bi-yin-yang:before {
        content: ""
    }

    .bi-bandaid-fill:before {
        content: ""
    }

    .bi-bandaid:before {
        content: ""
    }

    .bi-bluetooth:before {
        content: ""
    }

    .bi-body-text:before {
        content: ""
    }

    .bi-boombox:before {
        content: ""
    }

    .bi-boxes:before {
        content: ""
    }

    .bi-dpad-fill:before {
        content: ""
    }

    .bi-dpad:before {
        content: ""
    }

    .bi-ear-fill:before {
        content: ""
    }

    .bi-ear:before {
        content: ""
    }

    .bi-envelope-check-fill:before {
        content: ""
    }

    .bi-envelope-check:before {
        content: ""
    }

    .bi-envelope-dash-fill:before {
        content: ""
    }

    .bi-envelope-dash:before {
        content: ""
    }

    .bi-envelope-exclamation-fill:before {
        content: ""
    }

    .bi-envelope-exclamation:before {
        content: ""
    }

    .bi-envelope-plus-fill:before {
        content: ""
    }

    .bi-envelope-plus:before {
        content: ""
    }

    .bi-envelope-slash-fill:before {
        content: ""
    }

    .bi-envelope-slash:before {
        content: ""
    }

    .bi-envelope-x-fill:before {
        content: ""
    }

    .bi-envelope-x:before {
        content: ""
    }

    .bi-explicit-fill:before {
        content: ""
    }

    .bi-explicit:before {
        content: ""
    }

    .bi-git:before {
        content: ""
    }

    .bi-infinity:before {
        content: ""
    }

    .bi-list-columns-reverse:before {
        content: ""
    }

    .bi-list-columns:before {
        content: ""
    }

    .bi-meta:before {
        content: ""
    }

    .bi-nintendo-switch:before {
        content: ""
    }

    .bi-pc-display-horizontal:before {
        content: ""
    }

    .bi-pc-display:before {
        content: ""
    }

    .bi-pc-horizontal:before {
        content: ""
    }

    .bi-pc:before {
        content: ""
    }

    .bi-playstation:before {
        content: ""
    }

    .bi-plus-slash-minus:before {
        content: ""
    }

    .bi-projector-fill:before {
        content: ""
    }

    .bi-projector:before {
        content: ""
    }

    .bi-qr-code-scan:before {
        content: ""
    }

    .bi-qr-code:before {
        content: ""
    }

    .bi-quora:before {
        content: ""
    }

    .bi-quote:before {
        content: ""
    }

    .bi-robot:before {
        content: ""
    }

    .bi-send-check-fill:before {
        content: ""
    }

    .bi-send-check:before {
        content: ""
    }

    .bi-send-dash-fill:before {
        content: ""
    }

    .bi-send-dash:before {
        content: ""
    }

    .bi-send-exclamation-fill:before {
        content: ""
    }

    .bi-send-exclamation:before {
        content: ""
    }

    .bi-send-fill:before {
        content: ""
    }

    .bi-send-plus-fill:before {
        content: ""
    }

    .bi-send-plus:before {
        content: ""
    }

    .bi-send-slash-fill:before {
        content: ""
    }

    .bi-send-slash:before {
        content: ""
    }

    .bi-send-x-fill:before {
        content: ""
    }

    .bi-send-x:before {
        content: ""
    }

    .bi-send:before {
        content: ""
    }

    .bi-steam:before {
        content: ""
    }

    .bi-terminal-dash:before {
        content: ""
    }

    .bi-terminal-plus:before {
        content: ""
    }

    .bi-terminal-split:before {
        content: ""
    }

    .bi-ticket-detailed-fill:before {
        content: ""
    }

    .bi-ticket-detailed:before {
        content: ""
    }

    .bi-ticket-fill:before {
        content: ""
    }

    .bi-ticket-perforated-fill:before {
        content: ""
    }

    .bi-ticket-perforated:before {
        content: ""
    }

    .bi-ticket:before {
        content: ""
    }

    .bi-tiktok:before {
        content: ""
    }

    .bi-window-dash:before {
        content: ""
    }

    .bi-window-desktop:before {
        content: ""
    }

    .bi-window-fullscreen:before {
        content: ""
    }

    .bi-window-plus:before {
        content: ""
    }

    .bi-window-split:before {
        content: ""
    }

    .bi-window-stack:before {
        content: ""
    }

    .bi-window-x:before {
        content: ""
    }

    .bi-xbox:before {
        content: ""
    }

    .bi-ethernet:before {
        content: ""
    }

    .bi-hdmi-fill:before {
        content: ""
    }

    .bi-hdmi:before {
        content: ""
    }

    .bi-usb-c-fill:before {
        content: ""
    }

    .bi-usb-c:before {
        content: ""
    }

    .bi-usb-fill:before {
        content: ""
    }

    .bi-usb-plug-fill:before {
        content: ""
    }

    .bi-usb-plug:before {
        content: ""
    }

    .bi-usb-symbol:before {
        content: ""
    }

    .bi-usb:before {
        content: ""
    }

    .bi-boombox-fill:before {
        content: ""
    }

    .bi-displayport:before {
        content: ""
    }

    .bi-gpu-card:before {
        content: ""
    }

    .bi-memory:before {
        content: ""
    }

    .bi-modem-fill:before {
        content: ""
    }

    .bi-modem:before {
        content: ""
    }

    .bi-motherboard-fill:before {
        content: ""
    }

    .bi-motherboard:before {
        content: ""
    }

    .bi-optical-audio-fill:before {
        content: ""
    }

    .bi-optical-audio:before {
        content: ""
    }

    .bi-pci-card:before {
        content: ""
    }

    .bi-router-fill:before {
        content: ""
    }

    .bi-router:before {
        content: ""
    }

    .bi-thunderbolt-fill:before {
        content: ""
    }

    .bi-thunderbolt:before {
        content: ""
    }

    .bi-usb-drive-fill:before {
        content: ""
    }

    .bi-usb-drive:before {
        content: ""
    }

    .bi-usb-micro-fill:before {
        content: ""
    }

    .bi-usb-micro:before {
        content: ""
    }

    .bi-usb-mini-fill:before {
        content: ""
    }

    .bi-usb-mini:before {
        content: ""
    }

    .bi-cloud-haze2:before {
        content: ""
    }

    .bi-device-hdd-fill:before {
        content: ""
    }

    .bi-device-hdd:before {
        content: ""
    }

    .bi-device-ssd-fill:before {
        content: ""
    }

    .bi-device-ssd:before {
        content: ""
    }

    .bi-displayport-fill:before {
        content: ""
    }

    .bi-mortarboard-fill:before {
        content: ""
    }

    .bi-mortarboard:before {
        content: ""
    }

    .bi-terminal-x:before {
        content: ""
    }

    .bi-arrow-through-heart-fill:before {
        content: ""
    }

    .bi-arrow-through-heart:before {
        content: ""
    }

    .bi-badge-sd-fill:before {
        content: ""
    }

    .bi-badge-sd:before {
        content: ""
    }

    .bi-bag-heart-fill:before {
        content: ""
    }

    .bi-bag-heart:before {
        content: ""
    }

    .bi-balloon-fill:before {
        content: ""
    }

    .bi-balloon-heart-fill:before {
        content: ""
    }

    .bi-balloon-heart:before {
        content: ""
    }

    .bi-balloon:before {
        content: ""
    }

    .bi-box2-fill:before {
        content: ""
    }

    .bi-box2-heart-fill:before {
        content: ""
    }

    .bi-box2-heart:before {
        content: ""
    }

    .bi-box2:before {
        content: ""
    }

    .bi-braces-asterisk:before {
        content: ""
    }

    .bi-calendar-heart-fill:before {
        content: ""
    }

    .bi-calendar-heart:before {
        content: ""
    }

    .bi-calendar2-heart-fill:before {
        content: ""
    }

    .bi-calendar2-heart:before {
        content: ""
    }

    .bi-chat-heart-fill:before {
        content: ""
    }

    .bi-chat-heart:before {
        content: ""
    }

    .bi-chat-left-heart-fill:before {
        content: ""
    }

    .bi-chat-left-heart:before {
        content: ""
    }

    .bi-chat-right-heart-fill:before {
        content: ""
    }

    .bi-chat-right-heart:before {
        content: ""
    }

    .bi-chat-square-heart-fill:before {
        content: ""
    }

    .bi-chat-square-heart:before {
        content: ""
    }

    .bi-clipboard-check-fill:before {
        content: ""
    }

    .bi-clipboard-data-fill:before {
        content: ""
    }

    .bi-clipboard-fill:before {
        content: ""
    }

    .bi-clipboard-heart-fill:before {
        content: ""
    }

    .bi-clipboard-heart:before {
        content: ""
    }

    .bi-clipboard-minus-fill:before {
        content: ""
    }

    .bi-clipboard-plus-fill:before {
        content: ""
    }

    .bi-clipboard-pulse:before {
        content: ""
    }

    .bi-clipboard-x-fill:before {
        content: ""
    }

    .bi-clipboard2-check-fill:before {
        content: ""
    }

    .bi-clipboard2-check:before {
        content: ""
    }

    .bi-clipboard2-data-fill:before {
        content: ""
    }

    .bi-clipboard2-data:before {
        content: ""
    }

    .bi-clipboard2-fill:before {
        content: ""
    }

    .bi-clipboard2-heart-fill:before {
        content: ""
    }

    .bi-clipboard2-heart:before {
        content: ""
    }

    .bi-clipboard2-minus-fill:before {
        content: ""
    }

    .bi-clipboard2-minus:before {
        content: ""
    }

    .bi-clipboard2-plus-fill:before {
        content: ""
    }

    .bi-clipboard2-plus:before {
        content: ""
    }

    .bi-clipboard2-pulse-fill:before {
        content: ""
    }

    .bi-clipboard2-pulse:before {
        content: ""
    }

    .bi-clipboard2-x-fill:before {
        content: ""
    }

    .bi-clipboard2-x:before {
        content: ""
    }

    .bi-clipboard2:before {
        content: ""
    }

    .bi-emoji-kiss-fill:before {
        content: ""
    }

    .bi-emoji-kiss:before {
        content: ""
    }

    .bi-envelope-heart-fill:before {
        content: ""
    }

    .bi-envelope-heart:before {
        content: ""
    }

    .bi-envelope-open-heart-fill:before {
        content: ""
    }

    .bi-envelope-open-heart:before {
        content: ""
    }

    .bi-envelope-paper-fill:before {
        content: ""
    }

    .bi-envelope-paper-heart-fill:before {
        content: ""
    }

    .bi-envelope-paper-heart:before {
        content: ""
    }

    .bi-envelope-paper:before {
        content: ""
    }

    .bi-filetype-aac:before {
        content: ""
    }

    .bi-filetype-ai:before {
        content: ""
    }

    .bi-filetype-bmp:before {
        content: ""
    }

    .bi-filetype-cs:before {
        content: ""
    }

    .bi-filetype-css:before {
        content: ""
    }

    .bi-filetype-csv:before {
        content: ""
    }

    .bi-filetype-doc:before {
        content: ""
    }

    .bi-filetype-docx:before {
        content: ""
    }

    .bi-filetype-exe:before {
        content: ""
    }

    .bi-filetype-gif:before {
        content: ""
    }

    .bi-filetype-heic:before {
        content: ""
    }

    .bi-filetype-html:before {
        content: ""
    }

    .bi-filetype-java:before {
        content: ""
    }

    .bi-filetype-jpg:before {
        content: ""
    }

    .bi-filetype-js:before {
        content: ""
    }

    .bi-filetype-jsx:before {
        content: ""
    }

    .bi-filetype-key:before {
        content: ""
    }

    .bi-filetype-m4p:before {
        content: ""
    }

    .bi-filetype-md:before {
        content: ""
    }

    .bi-filetype-mdx:before {
        content: ""
    }

    .bi-filetype-mov:before {
        content: ""
    }

    .bi-filetype-mp3:before {
        content: ""
    }

    .bi-filetype-mp4:before {
        content: ""
    }

    .bi-filetype-otf:before {
        content: ""
    }

    .bi-filetype-pdf:before {
        content: ""
    }

    .bi-filetype-php:before {
        content: ""
    }

    .bi-filetype-png:before {
        content: ""
    }

    .bi-filetype-ppt:before {
        content: ""
    }

    .bi-filetype-psd:before {
        content: ""
    }

    .bi-filetype-py:before {
        content: ""
    }

    .bi-filetype-raw:before {
        content: ""
    }

    .bi-filetype-rb:before {
        content: ""
    }

    .bi-filetype-sass:before {
        content: ""
    }

    .bi-filetype-scss:before {
        content: ""
    }

    .bi-filetype-sh:before {
        content: ""
    }

    .bi-filetype-svg:before {
        content: ""
    }

    .bi-filetype-tiff:before {
        content: ""
    }

    .bi-filetype-tsx:before {
        content: ""
    }

    .bi-filetype-ttf:before {
        content: ""
    }

    .bi-filetype-txt:before {
        content: ""
    }

    .bi-filetype-wav:before {
        content: ""
    }

    .bi-filetype-woff:before {
        content: ""
    }

    .bi-filetype-xls:before {
        content: ""
    }

    .bi-filetype-xml:before {
        content: ""
    }

    .bi-filetype-yml:before {
        content: ""
    }

    .bi-heart-arrow:before {
        content: ""
    }

    .bi-heart-pulse-fill:before {
        content: ""
    }

    .bi-heart-pulse:before {
        content: ""
    }

    .bi-heartbreak-fill:before {
        content: ""
    }

    .bi-heartbreak:before {
        content: ""
    }

    .bi-hearts:before {
        content: ""
    }

    .bi-hospital-fill:before {
        content: ""
    }

    .bi-hospital:before {
        content: ""
    }

    .bi-house-heart-fill:before {
        content: ""
    }

    .bi-house-heart:before {
        content: ""
    }

    .bi-incognito:before {
        content: ""
    }

    .bi-magnet-fill:before {
        content: ""
    }

    .bi-magnet:before {
        content: ""
    }

    .bi-person-heart:before {
        content: ""
    }

    .bi-person-hearts:before {
        content: ""
    }

    .bi-phone-flip:before {
        content: ""
    }

    .bi-plugin:before {
        content: ""
    }

    .bi-postage-fill:before {
        content: ""
    }

    .bi-postage-heart-fill:before {
        content: ""
    }

    .bi-postage-heart:before {
        content: ""
    }

    .bi-postage:before {
        content: ""
    }

    .bi-postcard-fill:before {
        content: ""
    }

    .bi-postcard-heart-fill:before {
        content: ""
    }

    .bi-postcard-heart:before {
        content: ""
    }

    .bi-postcard:before {
        content: ""
    }

    .bi-search-heart-fill:before {
        content: ""
    }

    .bi-search-heart:before {
        content: ""
    }

    .bi-sliders2-vertical:before {
        content: ""
    }

    .bi-sliders2:before {
        content: ""
    }

    .bi-trash3-fill:before {
        content: ""
    }

    .bi-trash3:before {
        content: ""
    }

    .bi-valentine:before {
        content: ""
    }

    .bi-valentine2:before {
        content: ""
    }

    .bi-wrench-adjustable-circle-fill:before {
        content: ""
    }

    .bi-wrench-adjustable-circle:before {
        content: ""
    }

    .bi-wrench-adjustable:before {
        content: ""
    }

    .bi-filetype-json:before {
        content: ""
    }

    .bi-filetype-pptx:before {
        content: ""
    }

    .bi-filetype-xlsx:before {
        content: ""
    }

    .bi-1-circle-fill:before {
        content: ""
    }

    .bi-1-circle:before {
        content: ""
    }

    .bi-1-square-fill:before {
        content: ""
    }

    .bi-1-square:before {
        content: ""
    }

    .bi-2-circle-fill:before {
        content: ""
    }

    .bi-2-circle:before {
        content: ""
    }

    .bi-2-square-fill:before {
        content: ""
    }

    .bi-2-square:before {
        content: ""
    }

    .bi-3-circle-fill:before {
        content: ""
    }

    .bi-3-circle:before {
        content: ""
    }

    .bi-3-square-fill:before {
        content: ""
    }

    .bi-3-square:before {
        content: ""
    }

    .bi-4-circle-fill:before {
        content: ""
    }

    .bi-4-circle:before {
        content: ""
    }

    .bi-4-square-fill:before {
        content: ""
    }

    .bi-4-square:before {
        content: ""
    }

    .bi-5-circle-fill:before {
        content: ""
    }

    .bi-5-circle:before {
        content: ""
    }

    .bi-5-square-fill:before {
        content: ""
    }

    .bi-5-square:before {
        content: ""
    }

    .bi-6-circle-fill:before {
        content: ""
    }

    .bi-6-circle:before {
        content: ""
    }

    .bi-6-square-fill:before {
        content: ""
    }

    .bi-6-square:before {
        content: ""
    }

    .bi-7-circle-fill:before {
        content: ""
    }

    .bi-7-circle:before {
        content: ""
    }

    .bi-7-square-fill:before {
        content: ""
    }

    .bi-7-square:before {
        content: ""
    }

    .bi-8-circle-fill:before {
        content: ""
    }

    .bi-8-circle:before {
        content: ""
    }

    .bi-8-square-fill:before {
        content: ""
    }

    .bi-8-square:before {
        content: ""
    }

    .bi-9-circle-fill:before {
        content: ""
    }

    .bi-9-circle:before {
        content: ""
    }

    .bi-9-square-fill:before {
        content: ""
    }

    .bi-9-square:before {
        content: ""
    }

    .bi-airplane-engines-fill:before {
        content: ""
    }

    .bi-airplane-engines:before {
        content: ""
    }

    .bi-airplane-fill:before {
        content: ""
    }

    .bi-airplane:before {
        content: ""
    }

    .bi-alexa:before {
        content: ""
    }

    .bi-alipay:before {
        content: ""
    }

    .bi-android:before {
        content: ""
    }

    .bi-android2:before {
        content: ""
    }

    .bi-box-fill:before {
        content: ""
    }

    .bi-box-seam-fill:before {
        content: ""
    }

    .bi-browser-chrome:before {
        content: ""
    }

    .bi-browser-edge:before {
        content: ""
    }

    .bi-browser-firefox:before {
        content: ""
    }

    .bi-browser-safari:before {
        content: ""
    }

    .bi-c-circle-fill:before {
        content: ""
    }

    .bi-c-circle:before {
        content: ""
    }

    .bi-c-square-fill:before {
        content: ""
    }

    .bi-c-square:before {
        content: ""
    }

    .bi-capsule-pill:before {
        content: ""
    }

    .bi-capsule:before {
        content: ""
    }

    .bi-car-front-fill:before {
        content: ""
    }

    .bi-car-front:before {
        content: ""
    }

    .bi-cassette-fill:before {
        content: ""
    }

    .bi-cassette:before {
        content: ""
    }

    .bi-cc-circle-fill:before {
        content: ""
    }

    .bi-cc-circle:before {
        content: ""
    }

    .bi-cc-square-fill:before {
        content: ""
    }

    .bi-cc-square:before {
        content: ""
    }

    .bi-cup-hot-fill:before {
        content: ""
    }

    .bi-cup-hot:before {
        content: ""
    }

    .bi-currency-rupee:before {
        content: ""
    }

    .bi-dropbox:before {
        content: ""
    }

    .bi-escape:before {
        content: ""
    }

    .bi-fast-forward-btn-fill:before {
        content: ""
    }

    .bi-fast-forward-btn:before {
        content: ""
    }

    .bi-fast-forward-circle-fill:before {
        content: ""
    }

    .bi-fast-forward-circle:before {
        content: ""
    }

    .bi-fast-forward-fill:before {
        content: ""
    }

    .bi-fast-forward:before {
        content: ""
    }

    .bi-filetype-sql:before {
        content: ""
    }

    .bi-fire:before {
        content: ""
    }

    .bi-google-play:before {
        content: ""
    }

    .bi-h-circle-fill:before {
        content: ""
    }

    .bi-h-circle:before {
        content: ""
    }

    .bi-h-square-fill:before {
        content: ""
    }

    .bi-h-square:before {
        content: ""
    }

    .bi-indent:before {
        content: ""
    }

    .bi-lungs-fill:before {
        content: ""
    }

    .bi-lungs:before {
        content: ""
    }

    .bi-microsoft-teams:before {
        content: ""
    }

    .bi-p-circle-fill:before {
        content: ""
    }

    .bi-p-circle:before {
        content: ""
    }

    .bi-p-square-fill:before {
        content: ""
    }

    .bi-p-square:before {
        content: ""
    }

    .bi-pass-fill:before {
        content: ""
    }

    .bi-pass:before {
        content: ""
    }

    .bi-prescription:before {
        content: ""
    }

    .bi-prescription2:before {
        content: ""
    }

    .bi-r-circle-fill:before {
        content: ""
    }

    .bi-r-circle:before {
        content: ""
    }

    .bi-r-square-fill:before {
        content: ""
    }

    .bi-r-square:before {
        content: ""
    }

    .bi-repeat-1:before {
        content: ""
    }

    .bi-repeat:before {
        content: ""
    }

    .bi-rewind-btn-fill:before {
        content: ""
    }

    .bi-rewind-btn:before {
        content: ""
    }

    .bi-rewind-circle-fill:before {
        content: ""
    }

    .bi-rewind-circle:before {
        content: ""
    }

    .bi-rewind-fill:before {
        content: ""
    }

    .bi-rewind:before {
        content: ""
    }

    .bi-train-freight-front-fill:before {
        content: ""
    }

    .bi-train-freight-front:before {
        content: ""
    }

    .bi-train-front-fill:before {
        content: ""
    }

    .bi-train-front:before {
        content: ""
    }

    .bi-train-lightrail-front-fill:before {
        content: ""
    }

    .bi-train-lightrail-front:before {
        content: ""
    }

    .bi-truck-front-fill:before {
        content: ""
    }

    .bi-truck-front:before {
        content: ""
    }

    .bi-ubuntu:before {
        content: ""
    }

    .bi-unindent:before {
        content: ""
    }

    .bi-unity:before {
        content: ""
    }

    .bi-universal-access-circle:before {
        content: ""
    }

    .bi-universal-access:before {
        content: ""
    }

    .bi-virus:before {
        content: ""
    }

    .bi-virus2:before {
        content: ""
    }

    .bi-wechat:before {
        content: ""
    }

    .bi-yelp:before {
        content: ""
    }

    .bi-sign-stop-fill:before {
        content: ""
    }

    .bi-sign-stop-lights-fill:before {
        content: ""
    }

    .bi-sign-stop-lights:before {
        content: ""
    }

    .bi-sign-stop:before {
        content: ""
    }

    .bi-sign-turn-left-fill:before {
        content: ""
    }

    .bi-sign-turn-left:before {
        content: ""
    }

    .bi-sign-turn-right-fill:before {
        content: ""
    }

    .bi-sign-turn-right:before {
        content: ""
    }

    .bi-sign-turn-slight-left-fill:before {
        content: ""
    }

    .bi-sign-turn-slight-left:before {
        content: ""
    }

    .bi-sign-turn-slight-right-fill:before {
        content: ""
    }

    .bi-sign-turn-slight-right:before {
        content: ""
    }

    .bi-sign-yield-fill:before {
        content: ""
    }

    .bi-sign-yield:before {
        content: ""
    }

    .bi-ev-station-fill:before {
        content: ""
    }

    .bi-ev-station:before {
        content: ""
    }

    .bi-fuel-pump-diesel-fill:before {
        content: ""
    }

    .bi-fuel-pump-diesel:before {
        content: ""
    }

    .bi-fuel-pump-fill:before {
        content: ""
    }

    .bi-fuel-pump:before {
        content: ""
    }

    .bi-0-circle-fill:before {
        content: ""
    }

    .bi-0-circle:before {
        content: ""
    }

    .bi-0-square-fill:before {
        content: ""
    }

    .bi-0-square:before {
        content: ""
    }

    .bi-rocket-fill:before {
        content: ""
    }

    .bi-rocket-takeoff-fill:before {
        content: ""
    }

    .bi-rocket-takeoff:before {
        content: ""
    }

    .bi-rocket:before {
        content: ""
    }

    .bi-stripe:before {
        content: ""
    }

    .bi-subscript:before {
        content: ""
    }

    .bi-superscript:before {
        content: ""
    }

    .bi-trello:before {
        content: ""
    }

    .bi-envelope-at-fill:before {
        content: ""
    }

    .bi-envelope-at:before {
        content: ""
    }

    .bi-regex:before {
        content: ""
    }

    .bi-text-wrap:before {
        content: ""
    }

    .bi-sign-dead-end-fill:before {
        content: ""
    }

    .bi-sign-dead-end:before {
        content: ""
    }

    .bi-sign-do-not-enter-fill:before {
        content: ""
    }

    .bi-sign-do-not-enter:before {
        content: ""
    }

    .bi-sign-intersection-fill:before {
        content: ""
    }

    .bi-sign-intersection-side-fill:before {
        content: ""
    }

    .bi-sign-intersection-side:before {
        content: ""
    }

    .bi-sign-intersection-t-fill:before {
        content: ""
    }

    .bi-sign-intersection-t:before {
        content: ""
    }

    .bi-sign-intersection-y-fill:before {
        content: ""
    }

    .bi-sign-intersection-y:before {
        content: ""
    }

    .bi-sign-intersection:before {
        content: ""
    }

    .bi-sign-merge-left-fill:before {
        content: ""
    }

    .bi-sign-merge-left:before {
        content: ""
    }

    .bi-sign-merge-right-fill:before {
        content: ""
    }

    .bi-sign-merge-right:before {
        content: ""
    }

    .bi-sign-no-left-turn-fill:before {
        content: ""
    }

    .bi-sign-no-left-turn:before {
        content: ""
    }

    .bi-sign-no-parking-fill:before {
        content: ""
    }

    .bi-sign-no-parking:before {
        content: ""
    }

    .bi-sign-no-right-turn-fill:before {
        content: ""
    }

    .bi-sign-no-right-turn:before {
        content: ""
    }

    .bi-sign-railroad-fill:before {
        content: ""
    }

    .bi-sign-railroad:before {
        content: ""
    }

    .bi-building-add:before {
        content: ""
    }

    .bi-building-check:before {
        content: ""
    }

    .bi-building-dash:before {
        content: ""
    }

    .bi-building-down:before {
        content: ""
    }

    .bi-building-exclamation:before {
        content: ""
    }

    .bi-building-fill-add:before {
        content: ""
    }

    .bi-building-fill-check:before {
        content: ""
    }

    .bi-building-fill-dash:before {
        content: ""
    }

    .bi-building-fill-down:before {
        content: ""
    }

    .bi-building-fill-exclamation:before {
        content: ""
    }

    .bi-building-fill-gear:before {
        content: ""
    }

    .bi-building-fill-lock:before {
        content: ""
    }

    .bi-building-fill-slash:before {
        content: ""
    }

    .bi-building-fill-up:before {
        content: ""
    }

    .bi-building-fill-x:before {
        content: ""
    }

    .bi-building-fill:before {
        content: ""
    }

    .bi-building-gear:before {
        content: ""
    }

    .bi-building-lock:before {
        content: ""
    }

    .bi-building-slash:before {
        content: ""
    }

    .bi-building-up:before {
        content: ""
    }

    .bi-building-x:before {
        content: ""
    }

    .bi-buildings-fill:before {
        content: ""
    }

    .bi-buildings:before {
        content: ""
    }

    .bi-bus-front-fill:before {
        content: ""
    }

    .bi-bus-front:before {
        content: ""
    }

    .bi-ev-front-fill:before {
        content: ""
    }

    .bi-ev-front:before {
        content: ""
    }

    .bi-globe-americas:before {
        content: ""
    }

    .bi-globe-asia-australia:before {
        content: ""
    }

    .bi-globe-central-south-asia:before {
        content: ""
    }

    .bi-globe-europe-africa:before {
        content: ""
    }

    .bi-house-add-fill:before {
        content: ""
    }

    .bi-house-add:before {
        content: ""
    }

    .bi-house-check-fill:before {
        content: ""
    }

    .bi-house-check:before {
        content: ""
    }

    .bi-house-dash-fill:before {
        content: ""
    }

    .bi-house-dash:before {
        content: ""
    }

    .bi-house-down-fill:before {
        content: ""
    }

    .bi-house-down:before {
        content: ""
    }

    .bi-house-exclamation-fill:before {
        content: ""
    }

    .bi-house-exclamation:before {
        content: ""
    }

    .bi-house-gear-fill:before {
        content: ""
    }

    .bi-house-gear:before {
        content: ""
    }

    .bi-house-lock-fill:before {
        content: ""
    }

    .bi-house-lock:before {
        content: ""
    }

    .bi-house-slash-fill:before {
        content: ""
    }

    .bi-house-slash:before {
        content: ""
    }

    .bi-house-up-fill:before {
        content: ""
    }

    .bi-house-up:before {
        content: ""
    }

    .bi-house-x-fill:before {
        content: ""
    }

    .bi-house-x:before {
        content: ""
    }

    .bi-person-add:before {
        content: ""
    }

    .bi-person-down:before {
        content: ""
    }

    .bi-person-exclamation:before {
        content: ""
    }

    .bi-person-fill-add:before {
        content: ""
    }

    .bi-person-fill-check:before {
        content: ""
    }

    .bi-person-fill-dash:before {
        content: ""
    }

    .bi-person-fill-down:before {
        content: ""
    }

    .bi-person-fill-exclamation:before {
        content: ""
    }

    .bi-person-fill-gear:before {
        content: ""
    }

    .bi-person-fill-lock:before {
        content: ""
    }

    .bi-person-fill-slash:before {
        content: ""
    }

    .bi-person-fill-up:before {
        content: ""
    }

    .bi-person-fill-x:before {
        content: ""
    }

    .bi-person-gear:before {
        content: ""
    }

    .bi-person-lock:before {
        content: ""
    }

    .bi-person-slash:before {
        content: ""
    }

    .bi-person-up:before {
        content: ""
    }

    .bi-scooter:before {
        content: ""
    }

    .bi-taxi-front-fill:before {
        content: ""
    }

    .bi-taxi-front:before {
        content: ""
    }

    .bi-amd:before {
        content: ""
    }

    .bi-database-add:before {
        content: ""
    }

    .bi-database-check:before {
        content: ""
    }

    .bi-database-dash:before {
        content: ""
    }

    .bi-database-down:before {
        content: ""
    }

    .bi-database-exclamation:before {
        content: ""
    }

    .bi-database-fill-add:before {
        content: ""
    }

    .bi-database-fill-check:before {
        content: ""
    }

    .bi-database-fill-dash:before {
        content: ""
    }

    .bi-database-fill-down:before {
        content: ""
    }

    .bi-database-fill-exclamation:before {
        content: ""
    }

    .bi-database-fill-gear:before {
        content: ""
    }

    .bi-database-fill-lock:before {
        content: ""
    }

    .bi-database-fill-slash:before {
        content: ""
    }

    .bi-database-fill-up:before {
        content: ""
    }

    .bi-database-fill-x:before {
        content: ""
    }

    .bi-database-fill:before {
        content: ""
    }

    .bi-database-gear:before {
        content: ""
    }

    .bi-database-lock:before {
        content: ""
    }

    .bi-database-slash:before {
        content: ""
    }

    .bi-database-up:before {
        content: ""
    }

    .bi-database-x:before {
        content: ""
    }

    .bi-database:before {
        content: ""
    }

    .bi-houses-fill:before {
        content: ""
    }

    .bi-houses:before {
        content: ""
    }

    .bi-nvidia:before {
        content: ""
    }

    .bi-person-vcard-fill:before {
        content: ""
    }

    .bi-person-vcard:before {
        content: ""
    }

    .bi-sina-weibo:before {
        content: ""
    }

    .bi-tencent-qq:before {
        content: ""
    }

    .bi-wikipedia:before {
        content: ""
    }

    .bi-alphabet-uppercase:before {
        content: ""
    }

    .bi-alphabet:before {
        content: ""
    }

    .bi-amazon:before {
        content: ""
    }

    .bi-arrows-collapse-vertical:before {
        content: ""
    }

    .bi-arrows-expand-vertical:before {
        content: ""
    }

    .bi-arrows-vertical:before {
        content: ""
    }

    .bi-arrows:before {
        content: ""
    }

    .bi-ban-fill:before {
        content: ""
    }

    .bi-ban:before {
        content: ""
    }

    .bi-bing:before {
        content: ""
    }

    .bi-cake:before {
        content: ""
    }

    .bi-cake2:before {
        content: ""
    }

    .bi-cookie:before {
        content: ""
    }

    .bi-copy:before {
        content: ""
    }

    .bi-crosshair:before {
        content: ""
    }

    .bi-crosshair2:before {
        content: ""
    }

    .bi-emoji-astonished-fill:before {
        content: ""
    }

    .bi-emoji-astonished:before {
        content: ""
    }

    .bi-emoji-grimace-fill:before {
        content: ""
    }

    .bi-emoji-grimace:before {
        content: ""
    }

    .bi-emoji-grin-fill:before {
        content: ""
    }

    .bi-emoji-grin:before {
        content: ""
    }

    .bi-emoji-surprise-fill:before {
        content: ""
    }

    .bi-emoji-surprise:before {
        content: ""
    }

    .bi-emoji-tear-fill:before {
        content: ""
    }

    .bi-emoji-tear:before {
        content: ""
    }

    .bi-envelope-arrow-down-fill:before {
        content: ""
    }

    .bi-envelope-arrow-down:before {
        content: ""
    }

    .bi-envelope-arrow-up-fill:before {
        content: ""
    }

    .bi-envelope-arrow-up:before {
        content: ""
    }

    .bi-feather:before {
        content: ""
    }

    .bi-feather2:before {
        content: ""
    }

    .bi-floppy-fill:before {
        content: ""
    }

    .bi-floppy:before {
        content: ""
    }

    .bi-floppy2-fill:before {
        content: ""
    }

    .bi-floppy2:before {
        content: ""
    }

    .bi-gitlab:before {
        content: ""
    }

    .bi-highlighter:before {
        content: ""
    }

    .bi-marker-tip:before {
        content: ""
    }

    .bi-nvme-fill:before {
        content: ""
    }

    .bi-nvme:before {
        content: ""
    }

    .bi-opencollective:before {
        content: ""
    }

    .bi-pci-card-network:before {
        content: ""
    }

    .bi-pci-card-sound:before {
        content: ""
    }

    .bi-radar:before {
        content: ""
    }

    .bi-send-arrow-down-fill:before {
        content: ""
    }

    .bi-send-arrow-down:before {
        content: ""
    }

    .bi-send-arrow-up-fill:before {
        content: ""
    }

    .bi-send-arrow-up:before {
        content: ""
    }

    .bi-sim-slash-fill:before {
        content: ""
    }

    .bi-sim-slash:before {
        content: ""
    }

    .bi-sourceforge:before {
        content: ""
    }

    .bi-substack:before {
        content: ""
    }

    .bi-threads-fill:before {
        content: ""
    }

    .bi-threads:before {
        content: ""
    }

    .bi-transparency:before {
        content: ""
    }

    .bi-twitter-x:before {
        content: ""
    }

    .bi-type-h4:before {
        content: ""
    }

    .bi-type-h5:before {
        content: ""
    }

    .bi-type-h6:before {
        content: ""
    }

    .bi-backpack-fill:before {
        content: ""
    }

    .bi-backpack:before {
        content: ""
    }

    .bi-backpack2-fill:before {
        content: ""
    }

    .bi-backpack2:before {
        content: ""
    }

    .bi-backpack3-fill:before {
        content: ""
    }

    .bi-backpack3:before {
        content: ""
    }

    .bi-backpack4-fill:before {
        content: ""
    }

    .bi-backpack4:before {
        content: ""
    }

    .bi-brilliance:before {
        content: ""
    }

    .bi-cake-fill:before {
        content: ""
    }

    .bi-cake2-fill:before {
        content: ""
    }

    .bi-duffle-fill:before {
        content: ""
    }

    .bi-duffle:before {
        content: ""
    }

    .bi-exposure:before {
        content: ""
    }

    .bi-gender-neuter:before {
        content: ""
    }

    .bi-highlights:before {
        content: ""
    }

    .bi-luggage-fill:before {
        content: ""
    }

    .bi-luggage:before {
        content: ""
    }

    .bi-mailbox-flag:before {
        content: ""
    }

    .bi-mailbox2-flag:before {
        content: ""
    }

    .bi-noise-reduction:before {
        content: ""
    }

    .bi-passport-fill:before {
        content: ""
    }

    .bi-passport:before {
        content: ""
    }

    .bi-person-arms-up:before {
        content: ""
    }

    .bi-person-raised-hand:before {
        content: ""
    }

    .bi-person-standing-dress:before {
        content: ""
    }

    .bi-person-standing:before {
        content: ""
    }

    .bi-person-walking:before {
        content: ""
    }

    .bi-person-wheelchair:before {
        content: ""
    }

    .bi-shadows:before {
        content: ""
    }

    .bi-suitcase-fill:before {
        content: ""
    }

    .bi-suitcase-lg-fill:before {
        content: ""
    }

    .bi-suitcase-lg:before {
        content: ""
    }

    .bi-suitcase:before {
        content: "豈"
    }

    .bi-suitcase2-fill:before {
        content: "更"
    }

    .bi-suitcase2:before {
        content: "車"
    }

    .bi-vignette:before {
        content: "賈"
    }

    .bi-bluesky:before {
        content: ""
    }

    .bi-tux:before {
        content: "滑"
    }

    .bi-beaker-fill:before {
        content: "串"
    }

    .bi-beaker:before {
        content: "句"
    }

    .bi-flask-fill:before {
        content: "龜"
    }

    .bi-flask-florence-fill:before {
        content: "龜"
    }

    .bi-flask-florence:before {
        content: "契"
    }

    .bi-flask:before {
        content: "金"
    }

    .bi-leaf-fill:before {
        content: "喇"
    }

    .bi-leaf:before {
        content: "奈"
    }

    .bi-measuring-cup-fill:before {
        content: "懶"
    }

    .bi-measuring-cup:before {
        content: "癩"
    }

    .bi-unlock2-fill:before {
        content: "羅"
    }

    .bi-unlock2:before {
        content: "蘿"
    }

    .bi-battery-low:before {
        content: "螺"
    }

    .bi-anthropic:before {
        content: "裸"
    }

    .bi-apple-music:before {
        content: "邏"
    }

    .bi-claude:before {
        content: "樂"
    }

    .bi-openai:before {
        content: "洛"
    }

    .bi-perplexity:before {
        content: "烙"
    }

    .bi-css:before {
        content: "珞"
    }

    .bi-javascript:before {
        content: "落"
    }

    .bi-typescript:before {
        content: "酪"
    }

    .bi-fork-knife:before {
        content: "駱"
    }

    .bi-globe-americas-fill:before {
        content: "亂"
    }

    .bi-globe-asia-australia-fill:before {
        content: "卵"
    }

    .bi-globe-central-south-asia-fill:before {
        content: "欄"
    }

    .bi-globe-europe-africa-fill:before {
        content: "爛"
    }

    @font-face {
        font-family: Poppins;
        font-style: normal;
        font-weight: 300;
        src: local("pxiByp8kv8JHgFVrLDz8Z1xlFQ.woff2") format("woff2");
        unicode-range: u+00??, u+0131, u+0152-0153, u+02bb-02bc, u+02c6, u+02da, u+02dc, u+2000-206f, u+2074, u+20ac, u+2122, u+2191, u+2193, u+2212, u+2215, u+feff, u+fffd
    }

    @font-face {
        font-family: Poppins;
        font-style: normal;
        font-weight: 400;
        src: local("pxiEyp8kv8JHgFVrJJfecg.woff2") format("woff2");
        unicode-range: u+00??, u+0131, u+0152-0153, u+02bb-02bc, u+02c6, u+02da, u+02dc, u+2000-206f, u+2074, u+20ac, u+2122, u+2191, u+2193, u+2212, u+2215, u+feff, u+fffd
    }

    @font-face {
        font-family: Poppins;
        font-style: normal;
        font-weight: 500;
        src: local("pxiByp8kv8JHgFVrLGT9Z1xlFQ.woff2") format("woff2");
        unicode-range: u+00??, u+0131, u+0152-0153, u+02bb-02bc, u+02c6, u+02da, u+02dc, u+2000-206f, u+2074, u+20ac, u+2122, u+2191, u+2193, u+2212, u+2215, u+feff, u+fffd
    }

    @font-face {
        font-family: Poppins;
        font-style: normal;
        font-weight: 700;
        src: local("pxiByp8kv8JHgFVrLCz7Z1xlFQ.woff2") format("woff2");
        unicode-range: u+00??, u+0131, u+0152-0153, u+02bb-02bc, u+02c6, u+02da, u+02dc, u+2000-206f, u+2074, u+20ac, u+2122, u+2191, u+2193, u+2212, u+2215, u+feff, u+fffd
    }

    :root {
        --color-principal-btn: #245b98;
        --color-principal-fuerte: #164193;
        --color-principal-bg: linear-gradient(90deg, var(--color-principal-btn), var(--color-principal-fuerte));
        --accordion-color-ligth: #dce9fc;
        --letra: #fff
    }

    span {
        font-size: small
    }

    .form-select>option>::selection {
        font-size: small
    }

    .lenletratablaResumen {
        font-size: 10px
    }

    .lenletrabreadcrumb {
        font-size: 12px
    }

    .bt:hover {
        background: #722516;
        color: #f8f6f6;
        font-weight: 100
    }

    .bg-color {
        background: var(--color-principal-bg)
    }

    .btn-danger {
        --bs-btn-color: var(--letra) !important;
        --bs-btn-bg: var(--color-principal-btn) !important;
        --bs-btn-border-color: var(--color-principal-btn) !important;
        --bs-btn-hover-color: var(--letra) !important;
        --bs-btn-hover-bg: var(--color-principal-fuerte) !important;
        --bs-btn-hover-border-color: var(--color-principal-btn) !important;
        --bs-btn-focus-shadow-rgb: 225, 83, 97 !important;
        --bs-btn-active-color: var(--letra) !important;
        --bs-btn-active-bg: var(--color-principal-fuerte) !important;
        --bs-btn-active-border-color: var(--color-principal-btn) !important;
        --bs-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, .125) !important;
        --bs-btn-disabled-color: var(--letra) !important;
        --bs-btn-disabled-bg: var(--color-principal-btn) !important;
        --bs-btn-disabled-border-color: var(--color-principal-btn) !important
    }

    .sombra:hover {
        box-shadow: 10px 4px 20px var(--accordion-color-ligth)
    }

    /*!
 * Bootstrap  v5.3.8 (https://getbootstrap.com/)
 * Copyright 2011-2025 The Bootstrap Authors
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/main/LICENSE)
 */
    :root {
        --bs-blue: #0d6efd;
        --bs-indigo: #6610f2;
        --bs-purple: #6f42c1;
        --bs-pink: #d63384;
        --bs-red: #dc3545;
        --bs-orange: #fd7e14;
        --bs-yellow: #ffc107;
        --bs-green: #198754;
        --bs-teal: #20c997;
        --bs-cyan: #0dcaf0;
        --bs-black: #000;
        --bs-white: #fff;
        --bs-gray: #6c757d;
        --bs-gray-dark: #343a40;
        --bs-gray-100: #f8f9fa;
        --bs-gray-200: #e9ecef;
        --bs-gray-300: #dee2e6;
        --bs-gray-400: #ced4da;
        --bs-gray-500: #adb5bd;
        --bs-gray-600: #6c757d;
        --bs-gray-700: #495057;
        --bs-gray-800: #343a40;
        --bs-gray-900: #212529;
        --bs-primary: #0d6efd;
        --bs-secondary: #6c757d;
        --bs-success: #198754;
        --bs-info: #0dcaf0;
        --bs-warning: #ffc107;
        --bs-danger: #dc3545;
        --bs-light: #f8f9fa;
        --bs-dark: #212529;
        --bs-primary-rgb: 13, 110, 253;
        --bs-secondary-rgb: 108, 117, 125;
        --bs-success-rgb: 25, 135, 84;
        --bs-info-rgb: 13, 202, 240;
        --bs-warning-rgb: 255, 193, 7;
        --bs-danger-rgb: 220, 53, 69;
        --bs-light-rgb: 248, 249, 250;
        --bs-dark-rgb: 33, 37, 41;
        --bs-primary-text-emphasis: #052c65;
        --bs-secondary-text-emphasis: #2b2f32;
        --bs-success-text-emphasis: #0a3622;
        --bs-info-text-emphasis: #055160;
        --bs-warning-text-emphasis: #664d03;
        --bs-danger-text-emphasis: #58151c;
        --bs-light-text-emphasis: #495057;
        --bs-dark-text-emphasis: #495057;
        --bs-primary-bg-subtle: #cfe2ff;
        --bs-secondary-bg-subtle: #e2e3e5;
        --bs-success-bg-subtle: #d1e7dd;
        --bs-info-bg-subtle: #cff4fc;
        --bs-warning-bg-subtle: #fff3cd;
        --bs-danger-bg-subtle: #f8d7da;
        --bs-light-bg-subtle: #fcfcfd;
        --bs-dark-bg-subtle: #ced4da;
        --bs-primary-border-subtle: #9ec5fe;
        --bs-secondary-border-subtle: #c4c8cb;
        --bs-success-border-subtle: #a3cfbb;
        --bs-info-border-subtle: #9eeaf9;
        --bs-warning-border-subtle: #ffe69c;
        --bs-danger-border-subtle: #f1aeb5;
        --bs-light-border-subtle: #e9ecef;
        --bs-dark-border-subtle: #adb5bd;
        --bs-white-rgb: 255, 255, 255;
        --bs-black-rgb: 0, 0, 0;
        --bs-font-sans-serif: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        --bs-font-monospace: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        --bs-gradient: linear-gradient(180deg, #ffffff26, #fff0);
        --bs-body-font-family: var(--bs-font-sans-serif);
        --bs-body-font-size: 1rem;
        --bs-body-font-weight: 400;
        --bs-body-line-height: 1.5;
        --bs-body-color: #212529;
        --bs-body-color-rgb: 33, 37, 41;
        --bs-body-bg: #fff;
        --bs-body-bg-rgb: 255, 255, 255;
        --bs-emphasis-color: #000;
        --bs-emphasis-color-rgb: 0, 0, 0;
        --bs-secondary-color: #212529bf;
        --bs-secondary-color-rgb: 33, 37, 41;
        --bs-secondary-bg: #e9ecef;
        --bs-secondary-bg-rgb: 233, 236, 239;
        --bs-tertiary-color: #21252980;
        --bs-tertiary-color-rgb: 33, 37, 41;
        --bs-tertiary-bg: #f8f9fa;
        --bs-tertiary-bg-rgb: 248, 249, 250;
        --bs-heading-color: inherit;
        --bs-link-color: #0d6efd;
        --bs-link-color-rgb: 13, 110, 253;
        --bs-link-decoration: underline;
        --bs-link-hover-color: #0a58ca;
        --bs-link-hover-color-rgb: 10, 88, 202;
        --bs-code-color: #d63384;
        --bs-highlight-color: #212529;
        --bs-highlight-bg: #fff3cd;
        --bs-border-width: 1px;
        --bs-border-style: solid;
        --bs-border-color: #dee2e6;
        --bs-border-color-translucent: rgba(0, 0, 0, .175);
        --bs-border-radius: 0.375rem;
        --bs-border-radius-sm: 0.25rem;
        --bs-border-radius-lg: 0.5rem;
        --bs-border-radius-xl: 1rem;
        --bs-border-radius-xxl: 2rem;
        --bs-border-radius-2xl: var(--bs-border-radius-xxl);
        --bs-border-radius-pill: 50rem;
        --bs-box-shadow: 0 0.5rem 1rem #00000026;
        --bs-box-shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, .075);
        --bs-box-shadow-lg: 0 1rem 3rem rgba(0, 0, 0, .175);
        --bs-box-shadow-inset: inset 0 1px 2px rgba(0, 0, 0, .075);
        --bs-focus-ring-width: 0.25rem;
        --bs-focus-ring-opacity: 0.25;
        --bs-focus-ring-color: #0d6efd40;
        --bs-form-valid-color: #198754;
        --bs-form-valid-border-color: #198754;
        --bs-form-invalid-color: #dc3545;
        --bs-form-invalid-border-color: #dc3545
    }

    *,
    :after,
    :before {
        box-sizing: border-box
    }

    @media (prefers-reduced-motion:no-preference) {
        :root {
            scroll-behavior: smooth
        }
    }

    body {
        margin: 0;
        font-family: var(--bs-body-font-family);
        font-size: var(--bs-body-font-size);
        font-weight: var(--bs-body-font-weight);
        line-height: var(--bs-body-line-height);
        color: var(--bs-body-color);
        text-align: var(--bs-body-text-align);
        background-color: var(--bs-body-bg);
        -webkit-text-size-adjust: 100%;
        -webkit-tap-highlight-color: transparent
    }

    h2 {
        margin-top: 0;
        font-weight: 500;
        line-height: 1.2;
        color: var(--bs-heading-color)
    }

    h2 {
        font-size: calc(1.325rem + .9vw)
    }

    @media (min-width:1200px) {
        h2 {
            font-size: 2rem
        }
    }

    ol,
    ul {
        padding-left: 2rem
    }

    ol,
    ul {
        margin-top: 0;
        margin-bottom: 1rem
    }

    strong {
        font-weight: bolder
    }

    a {
        color: rgba(var(--bs-link-color-rgb), var(--bs-link-opacity, 1));
        text-decoration: underline
    }

    a:hover {
        --bs-link-color-rgb: var(--bs-link-hover-color-rgb)
    }

    a:not([href]):not([class]),
    a:not([href]):not([class]):hover {
        color: inherit;
        text-decoration: none
    }

    img {
        vertical-align: middle
    }

    table {
        caption-side: bottom;
        border-collapse: collapse
    }

    th {
        text-align: inherit
    }

    tbody,
    td,
    tfoot,
    th,
    thead,
    tr {
        border: 0 solid;
        border-color: inherit
    }

    label {
        display: inline-block
    }

    button:focus:not(:focus-visible) {
        outline: 0
    }

    button,
    input {
        margin: 0;
        font-family: inherit;
        line-height: inherit
    }

    button {
        text-transform: none
    }

    [role=button] {
        cursor: pointer
    }

    select:disabled {
        opacity: 1
    }

    [list]:not([type=date]):not([type=datetime-local]):not([type=month]):not([type=week]):not([type=time])::-webkit-calendar-picker-indicator {
        display: none !important
    }

    [type=button],
    [type=submit],
    button {
        -webkit-appearance: button
    }

    [type=button]:not(:disabled),
    [type=reset]:not(:disabled),
    [type=submit]:not(:disabled),
    button:not(:disabled) {
        cursor: pointer
    }

    ::-moz-focus-inner {
        padding: 0;
        border-style: none
    }

    ::-webkit-datetime-edit-day-field,
    ::-webkit-datetime-edit-fields-wrapper,
    ::-webkit-datetime-edit-hour-field,
    ::-webkit-datetime-edit-minute,
    ::-webkit-datetime-edit-month-field,
    ::-webkit-datetime-edit-text,
    ::-webkit-datetime-edit-year-field {
        padding: 0
    }

    ::-webkit-inner-spin-button {
        height: auto
    }

    [type=search]::-webkit-search-cancel-button {
        cursor: pointer;
        filter: grayscale(1)
    }

    ::-webkit-search-decoration {
        -webkit-appearance: none
    }

    ::-webkit-color-swatch-wrapper {
        padding: 0
    }

    ::-webkit-file-upload-button {
        font: inherit;
        -webkit-appearance: button
    }

    ::file-selector-button {
        font: inherit;
        -webkit-appearance: button
    }

    .blockquote-footer:before {
        content: "— "
    }

    .container {
        --bs-gutter-x: 1.5rem;
        --bs-gutter-y: 0;
        width: 100%;
        padding-right: calc(var(--bs-gutter-x)*.5);
        padding-left: calc(var(--bs-gutter-x)*.5);
        margin-right: auto;
        margin-left: auto
    }

    @media (min-width:576px) {
        .container {
            max-width: 540px
        }
    }

    @media (min-width:768px) {
        .container {
            max-width: 720px
        }
    }

    @media (min-width:992px) {
        .container {
            max-width: 960px
        }
    }

    @media (min-width:1200px) {
        .container {
            max-width: 1140px
        }
    }

    @media (min-width:1400px) {
        .container {
            max-width: 1320px
        }
    }

    :root {
        --bs-breakpoint-xs: 0;
        --bs-breakpoint-sm: 576px;
        --bs-breakpoint-md: 768px;
        --bs-breakpoint-lg: 992px;
        --bs-breakpoint-xl: 1200px;
        --bs-breakpoint-xxl: 1400px
    }

    .row {
        --bs-gutter-x: 1.5rem;
        --bs-gutter-y: 0;
        display: flex;
        flex-wrap: wrap;
        margin-top: calc(-1*var(--bs-gutter-y));
        margin-right: calc(-.5*var(--bs-gutter-x));
        margin-left: calc(-.5*var(--bs-gutter-x))
    }

    .row>* {
        flex-shrink: 0;
        width: 100%;
        max-width: 100%;
        padding-right: calc(var(--bs-gutter-x)*.5);
        padding-left: calc(var(--bs-gutter-x)*.5);
        margin-top: var(--bs-gutter-y)
    }

    @media (min-width:576px) {
        .col-sm-2 {
            flex: 0 0 auto;
            width: 16.66666667%
        }

        .col-sm-6 {
            flex: 0 0 auto;
            width: 50%
        }

        .col-sm-10 {
            flex: 0 0 auto;
            width: 83.33333333%
        }

        .col-sm-12 {
            flex: 0 0 auto;
            width: 100%
        }
    }

    .table {
        --bs-table-color-type: initial;
        --bs-table-bg-type: initial;
        --bs-table-color-state: initial;
        --bs-table-bg-state: initial;
        --bs-table-color: var(--bs-emphasis-color);
        --bs-table-bg: var(--bs-body-bg);
        --bs-table-border-color: var(--bs-border-color);
        --bs-table-accent-bg: #0000;
        --bs-table-striped-color: var(--bs-emphasis-color);
        --bs-table-striped-bg: rgba(var(--bs-emphasis-color-rgb), 0.05);
        --bs-table-active-color: var(--bs-emphasis-color);
        --bs-table-active-bg: rgba(var(--bs-emphasis-color-rgb), 0.1);
        --bs-table-hover-color: var(--bs-emphasis-color);
        --bs-table-hover-bg: rgba(var(--bs-emphasis-color-rgb), 0.075);
        width: 100%;
        margin-bottom: 1rem;
        vertical-align: top;
        border-color: var(--bs-table-border-color)
    }

    .table>:not(caption)>*>* {
        color: var(--bs-table-color-state, var(--bs-table-color-type, var(--bs-table-color)));
        background-color: var(--bs-table-bg);
        border-bottom-width: var(--bs-border-width);
        box-shadow: inset 0 0 0 9999px var(--bs-table-bg-state, var(--bs-table-bg-type, var(--bs-table-accent-bg)))
    }

    .table>tbody {
        vertical-align: inherit
    }

    .table>thead {
        vertical-align: bottom
    }

    .table-sm>:not(caption)>*>* {
        padding: .25rem
    }

    .table-bordered>:not(caption)>* {
        border-width: var(--bs-border-width)0
    }

    .table-bordered>:not(caption)>*>* {
        border-width: 0 var(--bs-border-width)
    }

    .table-hover>tbody>tr:hover>* {
        --bs-table-color-state: var(--bs-table-hover-color);
        --bs-table-bg-state: var(--bs-table-hover-bg)
    }

    .table-light {
        --bs-table-color: #000;
        --bs-table-bg: #f8f9fa;
        --bs-table-border-color: #c6c7c8;
        --bs-table-striped-bg: #ecedee;
        --bs-table-striped-color: #000;
        --bs-table-active-bg: #dfe0e1;
        --bs-table-active-color: #000;
        --bs-table-hover-bg: #e5e6e7;
        --bs-table-hover-color: #000
    }

    .table-light {
        color: var(--bs-table-color);
        border-color: var(--bs-table-border-color)
    }

    .form-control {
        display: block;
        width: 100%;
        font-weight: 400;
        line-height: 1.5;
        color: var(--bs-body-color);
        appearance: none;
        background-color: var(--bs-body-bg);
        background-clip: padding-box;
        border: var(--bs-border-width) solid var(--bs-border-color);
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out
    }

    @media (prefers-reduced-motion:reduce) {
        .form-control {
            transition: none
        }
    }

    .form-control[type=file]:not(:disabled):not([readonly]) {
        cursor: pointer
    }

    .form-control:focus {
        color: var(--bs-body-color);
        background-color: var(--bs-body-bg);
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 .25rem #0d6efd40
    }

    .form-control::-webkit-date-and-time-value {
        min-width: 85px;
        height: 1.5em;
        margin: 0
    }

    .form-control::-webkit-datetime-edit {
        display: block;
        padding: 0
    }

    .form-control::placeholder {
        color: var(--bs-secondary-color);
        opacity: 1
    }

    .form-control:disabled {
        background-color: var(--bs-secondary-bg);
        opacity: 1
    }

    .form-control::-webkit-file-upload-button {
        padding: .375rem .75rem;
        margin: -.375rem -.75rem;
        margin-inline-end: .75rem;
        color: var(--bs-body-color);
        background-color: var(--bs-tertiary-bg);
        pointer-events: none;
        border: 0 solid;
        border-color: inherit;
        border-inline-end-width: var(--bs-border-width);
        border-radius: 0;
        -webkit-transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out
    }

    .form-control::file-selector-button {
        padding: .375rem .75rem;
        margin: -.375rem -.75rem;
        margin-inline-end: .75rem;
        color: var(--bs-body-color);
        background-color: var(--bs-tertiary-bg);
        pointer-events: none;
        border: 0 solid;
        border-color: inherit;
        border-inline-end-width: var(--bs-border-width);
        border-radius: 0;
        transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out
    }

    @media (prefers-reduced-motion:reduce) {
        .form-control::-webkit-file-upload-button {
            -webkit-transition: none;
            transition: none
        }

        .form-control::file-selector-button {
            transition: none
        }
    }

    .form-control:hover:not(:disabled):not([readonly])::-webkit-file-upload-button {
        background-color: var(--bs-secondary-bg)
    }

    .form-control:hover:not(:disabled):not([readonly])::file-selector-button {
        background-color: var(--bs-secondary-bg)
    }

    .form-control-plaintext:focus {
        outline: 0
    }

    .form-control-sm {
        min-height: calc(1.5em + .5rem + calc(var(--bs-border-width)*2));
        padding: .25rem .5rem;
        font-size: .875rem;
        border-radius: var(--bs-border-radius-sm)
    }

    .form-control-sm::-webkit-file-upload-button {
        padding: .25rem .5rem;
        margin: -.25rem -.5rem;
        margin-inline-end: .5rem
    }

    .form-control-sm::file-selector-button {
        padding: .25rem .5rem;
        margin: -.25rem -.5rem;
        margin-inline-end: .5rem
    }

    .form-control-lg::-webkit-file-upload-button {
        padding: .5rem 1rem;
        margin: -.5rem -1rem;
        margin-inline-end: 1rem
    }

    .form-control-lg::file-selector-button {
        padding: .5rem 1rem;
        margin: -.5rem -1rem;
        margin-inline-end: 1rem
    }

    .form-control-color:not(:disabled):not([readonly]) {
        cursor: pointer
    }

    .form-control-color::-moz-color-swatch {
        border: 0 !important;
        border-radius: var(--bs-border-radius)
    }

    .form-control-color::-webkit-color-swatch {
        border: 0 !important;
        border-radius: var(--bs-border-radius)
    }

    .form-select:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 .25rem #0d6efd40
    }

    .form-select:disabled {
        background-color: var(--bs-secondary-bg)
    }

    .form-select:-moz-focusring {
        color: #0000;
        text-shadow: 0 0 0 var(--bs-body-color)
    }

    .form-check-input:active {
        filter: brightness(90%)
    }

    .form-check-input:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 .25rem #0d6efd40
    }

    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd
    }

    .form-check-input:checked[type=checkbox] {
        --bs-form-check-bg-image: url(data:image/svg+xml,%3csvg\ xmlns=\'http://www.w3.org/2000/svg\'\ viewBox=\'0\ 0\ 20\ 20\'%3e%3cpath\ fill=\'none\'\ stroke=\'%23fff\'\ stroke-linecap=\'round\'\ stroke-linejoin=\'round\'\ stroke-width=\'3\'\ d=\'m6\ 10\ 3\ 3\ 6-6\'/%3e%3c/svg%3e)
    }

    .form-check-input:checked[type=radio] {
        --bs-form-check-bg-image: url(data:image/svg+xml,%3csvg\ xmlns=\'http://www.w3.org/2000/svg\'\ viewBox=\'-4\ -4\ 8\ 8\'%3e%3ccircle\ r=\'2\'\ fill=\'%23fff\'/%3e%3c/svg%3e)
    }

    .form-check-input[type=checkbox]:indeterminate {
        background-color: #0d6efd;
        border-color: #0d6efd;
        --bs-form-check-bg-image: url(data:image/svg+xml,%3csvg\ xmlns=\'http://www.w3.org/2000/svg\'\ viewBox=\'0\ 0\ 20\ 20\'%3e%3cpath\ fill=\'none\'\ stroke=\'%23fff\'\ stroke-linecap=\'round\'\ stroke-linejoin=\'round\'\ stroke-width=\'3\'\ d=\'M6\ 10h8\'/%3e%3c/svg%3e)
    }

    .form-check-input:disabled {
        pointer-events: none;
        filter: none;
        opacity: .5
    }

    .form-check-input:disabled~.form-check-label {
        cursor: default;
        opacity: .5
    }

    .form-switch .form-check-input:focus {
        --bs-form-switch-bg: url(data:image/svg+xml,%3csvg\ xmlns=\'http://www.w3.org/2000/svg\'\ viewBox=\'-4\ -4\ 8\ 8\'%3e%3ccircle\ r=\'3\'\ fill=\'%2386b7fe\'/%3e%3c/svg%3e)
    }

    .form-switch .form-check-input:checked {
        background-position: 100%;
        --bs-form-switch-bg: url(data:image/svg+xml,%3csvg\ xmlns=\'http://www.w3.org/2000/svg\'\ viewBox=\'-4\ -4\ 8\ 8\'%3e%3ccircle\ r=\'3\'\ fill=\'%23fff\'/%3e%3c/svg%3e)
    }

    .btn-check:disabled+.btn {
        pointer-events: none;
        filter: none;
        opacity: .65
    }

    [data-bs-theme=dark] .form-switch .form-check-input:not(:checked):not(:focus) {
        --bs-form-switch-bg: url(data:image/svg+xml,%3csvg\ xmlns=\'http://www.w3.org/2000/svg\'\ viewBox=\'-4\ -4\ 8\ 8\'%3e%3ccircle\ r=\'3\'\ fill=\'rgba%28255,\ 255,\ 255,\ 0.25%29\'/%3e%3c/svg%3e)
    }

    .form-range:focus {
        outline: 0
    }

    .form-range:focus::-webkit-slider-thumb {
        box-shadow: 0 0 0 1px #fff, 0 0 0 .25rem #0d6efd40
    }

    .form-range:focus::-moz-range-thumb {
        box-shadow: 0 0 0 1px #fff, 0 0 0 .25rem #0d6efd40
    }

    .form-range::-moz-focus-outer {
        border: 0
    }

    .form-range::-webkit-slider-thumb {
        width: 1rem;
        height: 1rem;
        margin-top: -.25rem;
        appearance: none;
        background-color: #0d6efd;
        border: 0;
        border-radius: 1rem;
        -webkit-transition: background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        transition: background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out
    }

    @media (prefers-reduced-motion:reduce) {
        .form-range::-webkit-slider-thumb {
            -webkit-transition: none;
            transition: none
        }
    }

    .form-range::-webkit-slider-thumb:active {
        background-color: #b6d4fe
    }

    .form-range::-webkit-slider-runnable-track {
        width: 100%;
        height: .5rem;
        color: #0000;
        cursor: pointer;
        background-color: var(--bs-secondary-bg);
        border-color: #0000;
        border-radius: 1rem
    }

    .form-range::-moz-range-thumb {
        width: 1rem;
        height: 1rem;
        appearance: none;
        background-color: #0d6efd;
        border: 0;
        border-radius: 1rem;
        -moz-transition: background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        transition: background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out
    }

    @media (prefers-reduced-motion:reduce) {
        .form-range::-moz-range-thumb {
            -moz-transition: none;
            transition: none
        }
    }

    .form-range::-moz-range-thumb:active {
        background-color: #b6d4fe
    }

    .form-range::-moz-range-track {
        width: 100%;
        height: .5rem;
        color: #0000;
        cursor: pointer;
        background-color: var(--bs-secondary-bg);
        border-color: #0000;
        border-radius: 1rem
    }

    .form-range:disabled {
        pointer-events: none
    }

    .form-range:disabled::-webkit-slider-thumb {
        background-color: var(--bs-secondary-color)
    }

    .form-range:disabled::-moz-range-thumb {
        background-color: var(--bs-secondary-color)
    }

    .form-floating {
        position: relative
    }

    .form-floating>.form-control {
        height: calc(3.5rem + calc(var(--bs-border-width)*2));
        min-height: calc(3.5rem + calc(var(--bs-border-width)*2));
        line-height: 1.25
    }

    .form-floating>label {
        position: absolute;
        top: 0;
        left: 0;
        z-index: 2;
        max-width: 100%;
        height: 100%;
        padding: 1rem .75rem;
        overflow: hidden;
        color: rgba(var(--bs-body-color-rgb), .65);
        text-align: start;
        text-overflow: ellipsis;
        white-space: nowrap;
        pointer-events: none;
        border: var(--bs-border-width) solid #0000;
        transform-origin: 0 0;
        transition: opacity .1s ease-in-out, transform .1s ease-in-out
    }

    @media (prefers-reduced-motion:reduce) {
        .form-floating>label {
            transition: none
        }
    }

    .form-floating>.form-control {
        padding: 1rem .75rem
    }

    .form-floating>.form-control-plaintext::placeholder,
    .form-floating>.form-control::placeholder {
        color: #0000
    }

    .form-floating>.form-control-plaintext:focus,
    .form-floating>.form-control-plaintext:not(:placeholder-shown),
    .form-floating>.form-control:focus,
    .form-floating>.form-control:not(:placeholder-shown) {
        padding-top: 1.625rem;
        padding-bottom: .625rem
    }

    .form-floating>.form-control-plaintext:-webkit-autofill,
    .form-floating>.form-control:-webkit-autofill {
        padding-top: 1.625rem;
        padding-bottom: .625rem
    }

    .form-floating>.form-control:focus~label,
    .form-floating>.form-control:not(:placeholder-shown)~label {
        transform: scale(.85) translateY(-.5rem) translateX(.15rem)
    }

    .form-floating>.form-control:-webkit-autofill~label {
        transform: scale(.85) translateY(-.5rem) translateX(.15rem)
    }

    .form-floating>textarea:focus~label:after,
    .form-floating>textarea:not(:placeholder-shown)~label:after {
        position: absolute;
        inset: 1rem .375rem;
        z-index: -1;
        height: 1.5em;
        content: "";
        background-color: var(--bs-body-bg);
        border-radius: var(--bs-border-radius)
    }

    .form-floating>textarea:disabled~label:after {
        background-color: var(--bs-secondary-bg)
    }

    .form-floating>.form-control:disabled~label,
    .form-floating>:disabled~label {
        color: #6c757d
    }

    .input-group>.form-control:focus,
    .input-group>.form-floating:focus-within,
    .input-group>.form-select:focus {
        z-index: 5
    }

    .input-group .btn:focus {
        z-index: 5
    }

    .was-validated :valid~.valid-feedback,
    .was-validated :valid~.valid-tooltip {
        display: block
    }

    .was-validated .form-control:valid {
        border-color: var(--bs-form-valid-border-color);
        padding-right: calc(1.5em + .75rem);
        background-image: url(data:image/svg+xml,%3csvg\ xmlns=\'http://www.w3.org/2000/svg\'\ viewBox=\'0\ 0\ 8\ 8\'%3e%3cpath\ fill=\'%23198754\'\ d=\'M2.3\ 6.73.6\ 4.53c-.4-1.04.46-1.4\ 1.1-.8l1.1\ 1.4\ 3.4-3.8c.6-.63\ 1.6-.27\ 1.2.7l-4\ 4.6c-.43.5-.8.4-1.1.1\'/%3e%3c/svg%3e);
        background-repeat: no-repeat;
        background-position: right calc(.375em + .1875rem) center;
        background-size: calc(.75em + .375rem) calc(.75em + .375rem)
    }

    .form-control.is-valid:focus,
    .was-validated .form-control:valid:focus {
        border-color: var(--bs-form-valid-border-color);
        box-shadow: 0 0 0 .25rem rgba(var(--bs-success-rgb), .25)
    }

    .was-validated textarea.form-control:valid {
        padding-right: calc(1.5em + .75rem);
        background-position: top calc(.375em + .1875rem) right calc(.375em + .1875rem)
    }

    .was-validated .form-select:valid {
        border-color: var(--bs-form-valid-border-color)
    }

    .was-validated .form-select:valid:not([multiple]):not([size]),
    .was-validated .form-select:valid:not([multiple])[size="1"] {
        --bs-form-select-bg-icon: url(data:image/svg+xml,%3csvg\ xmlns=\'http://www.w3.org/2000/svg\'\ viewBox=\'0\ 0\ 8\ 8\'%3e%3cpath\ fill=\'%23198754\'\ d=\'M2.3\ 6.73.6\ 4.53c-.4-1.04.46-1.4\ 1.1-.8l1.1\ 1.4\ 3.4-3.8c.6-.63\ 1.6-.27\ 1.2.7l-4\ 4.6c-.43.5-.8.4-1.1.1\'/%3e%3c/svg%3e);
        padding-right: 4.125rem;
        background-position: right .75rem center, center right 2.25rem;
        background-size: 16px 12px, calc(.75em + .375rem) calc(.75em + .375rem)
    }

    .form-select.is-valid:focus,
    .was-validated .form-select:valid:focus {
        border-color: var(--bs-form-valid-border-color);
        box-shadow: 0 0 0 .25rem rgba(var(--bs-success-rgb), .25)
    }

    .was-validated .form-control-color:valid {
        width: calc(3rem + calc(1.5em + .75rem))
    }

    .was-validated .form-check-input:valid {
        border-color: var(--bs-form-valid-border-color)
    }

    .form-check-input.is-valid:checked,
    .was-validated .form-check-input:valid:checked {
        background-color: var(--bs-form-valid-color)
    }

    .form-check-input.is-valid:focus,
    .was-validated .form-check-input:valid:focus {
        box-shadow: 0 0 0 .25rem rgba(var(--bs-success-rgb), .25)
    }

    .was-validated .form-check-input:valid~.form-check-label {
        color: var(--bs-form-valid-color)
    }

    .input-group>.form-control:not(:focus).is-valid,
    .input-group>.form-floating:not(:focus-within).is-valid,
    .input-group>.form-select:not(:focus).is-valid,
    .was-validated .input-group>.form-control:not(:focus):valid,
    .was-validated .input-group>.form-floating:not(:focus-within):valid,
    .was-validated .input-group>.form-select:not(:focus):valid {
        z-index: 3
    }

    .was-validated :invalid~.invalid-feedback,
    .was-validated :invalid~.invalid-tooltip {
        display: block
    }

    .was-validated .form-control:invalid {
        border-color: var(--bs-form-invalid-border-color);
        padding-right: calc(1.5em + .75rem);
        background-image: url(data:image/svg+xml,%3csvg\ xmlns=\'http://www.w3.org/2000/svg\'\ viewBox=\'0\ 0\ 12\ 12\'\ width=\'12\'\ height=\'12\'\ fill=\'none\'\ stroke=\'%23dc3545\'%3e%3ccircle\ cx=\'6\'\ cy=\'6\'\ r=\'4.5\'/%3e%3cpath\ stroke-linejoin=\'round\'\ d=\'M5.8\ 3.6h.4L6\ 6.5z\'/%3e%3ccircle\ cx=\'6\'\ cy=\'8.2\'\ r=\'.6\'\ fill=\'%23dc3545\'\ stroke=\'none\'/%3e%3c/svg%3e);
        background-repeat: no-repeat;
        background-position: right calc(.375em + .1875rem) center;
        background-size: calc(.75em + .375rem) calc(.75em + .375rem)
    }

    .form-control.is-invalid:focus,
    .was-validated .form-control:invalid:focus {
        border-color: var(--bs-form-invalid-border-color);
        box-shadow: 0 0 0 .25rem rgba(var(--bs-danger-rgb), .25)
    }

    .was-validated textarea.form-control:invalid {
        padding-right: calc(1.5em + .75rem);
        background-position: top calc(.375em + .1875rem) right calc(.375em + .1875rem)
    }

    .was-validated .form-select:invalid {
        border-color: var(--bs-form-invalid-border-color)
    }

    .was-validated .form-select:invalid:not([multiple]):not([size]),
    .was-validated .form-select:invalid:not([multiple])[size="1"] {
        --bs-form-select-bg-icon: url(data:image/svg+xml,%3csvg\ xmlns=\'http://www.w3.org/2000/svg\'\ viewBox=\'0\ 0\ 12\ 12\'\ width=\'12\'\ height=\'12\'\ fill=\'none\'\ stroke=\'%23dc3545\'%3e%3ccircle\ cx=\'6\'\ cy=\'6\'\ r=\'4.5\'/%3e%3cpath\ stroke-linejoin=\'round\'\ d=\'M5.8\ 3.6h.4L6\ 6.5z\'/%3e%3ccircle\ cx=\'6\'\ cy=\'8.2\'\ r=\'.6\'\ fill=\'%23dc3545\'\ stroke=\'none\'/%3e%3c/svg%3e);
        padding-right: 4.125rem;
        background-position: right .75rem center, center right 2.25rem;
        background-size: 16px 12px, calc(.75em + .375rem) calc(.75em + .375rem)
    }

    .form-select.is-invalid:focus,
    .was-validated .form-select:invalid:focus {
        border-color: var(--bs-form-invalid-border-color);
        box-shadow: 0 0 0 .25rem rgba(var(--bs-danger-rgb), .25)
    }

    .was-validated .form-control-color:invalid {
        width: calc(3rem + calc(1.5em + .75rem))
    }

    .was-validated .form-check-input:invalid {
        border-color: var(--bs-form-invalid-border-color)
    }

    .form-check-input.is-invalid:checked,
    .was-validated .form-check-input:invalid:checked {
        background-color: var(--bs-form-invalid-color)
    }

    .form-check-input.is-invalid:focus,
    .was-validated .form-check-input:invalid:focus {
        box-shadow: 0 0 0 .25rem rgba(var(--bs-danger-rgb), .25)
    }

    .was-validated .form-check-input:invalid~.form-check-label {
        color: var(--bs-form-invalid-color)
    }

    .input-group>.form-control:not(:focus).is-invalid,
    .input-group>.form-floating:not(:focus-within).is-invalid,
    .input-group>.form-select:not(:focus).is-invalid,
    .was-validated .input-group>.form-control:not(:focus):invalid,
    .was-validated .input-group>.form-floating:not(:focus-within):invalid,
    .was-validated .input-group>.form-select:not(:focus):invalid {
        z-index: 4
    }

    .btn {
        --bs-btn-padding-x: 0.75rem;
        --bs-btn-padding-y: 0.375rem;
        --bs-btn-font-family: ;
        --bs-btn-font-size: 1rem;
        --bs-btn-font-weight: 400;
        --bs-btn-line-height: 1.5;
        --bs-btn-color: var(--bs-body-color);
        --bs-btn-bg: #0000;
        --bs-btn-border-width: var(--bs-border-width);
        --bs-btn-border-color: #0000;
        --bs-btn-border-radius: var(--bs-border-radius);
        --bs-btn-hover-border-color: #0000;
        --bs-btn-box-shadow: inset 0 1px 0#ffffff26, 0 1px 1px rgba(0, 0, 0, .075);
        --bs-btn-disabled-opacity: 0.65;
        --bs-btn-focus-box-shadow: 0 0 0 0.25rem rgba(var(--bs-btn-focus-shadow-rgb), .5);
        display: inline-block;
        padding: var(--bs-btn-padding-y) var(--bs-btn-padding-x);
        font-family: var(--bs-btn-font-family);
        font-size: var(--bs-btn-font-size);
        font-weight: var(--bs-btn-font-weight);
        line-height: var(--bs-btn-line-height);
        color: var(--bs-btn-color);
        text-align: center;
        text-decoration: none;
        vertical-align: middle;
        cursor: pointer;
        -webkit-user-select: none;
        user-select: none;
        border: var(--bs-btn-border-width) solid var(--bs-btn-border-color);
        border-radius: var(--bs-btn-border-radius);
        background-color: var(--bs-btn-bg);
        transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out
    }

    @media (prefers-reduced-motion:reduce) {
        .btn {
            transition: none
        }
    }

    .btn:hover {
        color: var(--bs-btn-hover-color);
        background-color: var(--bs-btn-hover-bg);
        border-color: var(--bs-btn-hover-border-color)
    }

    .btn-check+.btn:hover {
        color: var(--bs-btn-color);
        background-color: var(--bs-btn-bg);
        border-color: var(--bs-btn-border-color)
    }

    .btn:focus-visible {
        color: var(--bs-btn-hover-color);
        background-color: var(--bs-btn-hover-bg)
    }

    .btn-check:focus-visible+.btn,
    .btn:focus-visible {
        border-color: var(--bs-btn-hover-border-color);
        outline: 0;
        box-shadow: var(--bs-btn-focus-box-shadow)
    }

    .btn-check:checked+.btn,
    .btn:first-child:active,
    :not(.btn-check)+.btn:active {
        color: var(--bs-btn-active-color);
        background-color: var(--bs-btn-active-bg);
        border-color: var(--bs-btn-active-border-color)
    }

    .btn-check:checked+.btn:focus-visible,
    .btn-check:checked:focus-visible+.btn,
    .btn.active:focus-visible,
    .btn.show:focus-visible,
    .btn:first-child:active:focus-visible,
    :not(.btn-check)+.btn:active:focus-visible {
        box-shadow: var(--bs-btn-focus-box-shadow)
    }

    .btn:disabled,
    fieldset:disabled .btn {
        color: var(--bs-btn-disabled-color);
        pointer-events: none;
        background-color: var(--bs-btn-disabled-bg);
        border-color: var(--bs-btn-disabled-border-color);
        opacity: var(--bs-btn-disabled-opacity)
    }

    .btn-danger {
        --bs-btn-color: #fff;
        --bs-btn-bg: #dc3545;
        --bs-btn-border-color: #dc3545;
        --bs-btn-hover-color: #fff;
        --bs-btn-hover-bg: #bb2d3b;
        --bs-btn-hover-border-color: #b02a37;
        --bs-btn-focus-shadow-rgb: 225, 83, 97;
        --bs-btn-active-color: #fff;
        --bs-btn-active-bg: #b02a37;
        --bs-btn-active-border-color: #a52834;
        --bs-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, .125);
        --bs-btn-disabled-color: #fff;
        --bs-btn-disabled-bg: #dc3545;
        --bs-btn-disabled-border-color: #dc3545
    }

    .btn-light {
        --bs-btn-color: #000;
        --bs-btn-bg: #f8f9fa;
        --bs-btn-border-color: #f8f9fa;
        --bs-btn-hover-color: #000;
        --bs-btn-hover-bg: #d3d4d5;
        --bs-btn-hover-border-color: #c6c7c8;
        --bs-btn-focus-shadow-rgb: 211, 212, 213;
        --bs-btn-active-color: #000;
        --bs-btn-active-bg: #c6c7c8;
        --bs-btn-active-border-color: #babbbc;
        --bs-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, .125);
        --bs-btn-disabled-color: #000;
        --bs-btn-disabled-bg: #f8f9fa;
        --bs-btn-disabled-border-color: #f8f9fa
    }

    .btn-link:focus-visible {
        color: var(--bs-btn-color)
    }

    .btn-link:hover {
        color: var(--bs-btn-hover-color)
    }

    .btn-sm {
        --bs-btn-padding-y: 0.25rem;
        --bs-btn-padding-x: 0.5rem;
        --bs-btn-font-size: 0.875rem;
        --bs-btn-border-radius: var(--bs-border-radius-sm)
    }

    .collapse:not(.show) {
        display: none
    }

    .dropdown-toggle {
        white-space: nowrap
    }

    .dropdown-toggle:after {
        display: inline-block;
        margin-left: .255em;
        vertical-align: .255em;
        content: "";
        border-top: .3em solid;
        border-right: .3em solid #0000;
        border-bottom: 0;
        border-left: .3em solid #0000
    }

    .dropdown-toggle:empty:after {
        margin-left: 0
    }

    .dropdown-menu {
        --bs-dropdown-zindex: 1000;
        --bs-dropdown-min-width: 10rem;
        --bs-dropdown-padding-x: 0;
        --bs-dropdown-padding-y: 0.5rem;
        --bs-dropdown-spacer: 0.125rem;
        --bs-dropdown-font-size: 1rem;
        --bs-dropdown-color: var(--bs-body-color);
        --bs-dropdown-bg: var(--bs-body-bg);
        --bs-dropdown-border-color: var(--bs-border-color-translucent);
        --bs-dropdown-border-radius: var(--bs-border-radius);
        --bs-dropdown-border-width: var(--bs-border-width);
        --bs-dropdown-inner-border-radius: calc(var(--bs-border-radius) - var(--bs-border-width));
        --bs-dropdown-divider-bg: var(--bs-border-color-translucent);
        --bs-dropdown-divider-margin-y: 0.5rem;
        --bs-dropdown-box-shadow: var(--bs-box-shadow);
        --bs-dropdown-link-color: var(--bs-body-color);
        --bs-dropdown-link-hover-color: var(--bs-body-color);
        --bs-dropdown-link-hover-bg: var(--bs-tertiary-bg);
        --bs-dropdown-link-active-color: #fff;
        --bs-dropdown-link-active-bg: #0d6efd;
        --bs-dropdown-link-disabled-color: var(--bs-tertiary-color);
        --bs-dropdown-item-padding-x: 1rem;
        --bs-dropdown-item-padding-y: 0.25rem;
        --bs-dropdown-header-color: #6c757d;
        --bs-dropdown-header-padding-x: 1rem;
        --bs-dropdown-header-padding-y: 0.5rem;
        position: absolute;
        z-index: var(--bs-dropdown-zindex);
        display: none;
        min-width: var(--bs-dropdown-min-width);
        padding: var(--bs-dropdown-padding-y) var(--bs-dropdown-padding-x);
        margin: 0;
        font-size: var(--bs-dropdown-font-size);
        color: var(--bs-dropdown-color);
        text-align: left;
        list-style: none;
        background-color: var(--bs-dropdown-bg);
        background-clip: padding-box;
        border: var(--bs-dropdown-border-width) solid var(--bs-dropdown-border-color);
        border-radius: var(--bs-dropdown-border-radius)
    }

    .dropup .dropdown-toggle:after {
        display: inline-block;
        margin-left: .255em;
        vertical-align: .255em;
        content: "";
        border-top: 0;
        border-right: .3em solid #0000;
        border-bottom: .3em solid;
        border-left: .3em solid #0000
    }

    .dropup .dropdown-toggle:empty:after {
        margin-left: 0
    }

    .dropend .dropdown-toggle:after {
        display: inline-block;
        margin-left: .255em;
        vertical-align: .255em;
        content: "";
        border-top: .3em solid #0000;
        border-right: 0;
        border-bottom: .3em solid #0000;
        border-left: .3em solid
    }

    .dropend .dropdown-toggle:empty:after {
        margin-left: 0
    }

    .dropend .dropdown-toggle:after {
        vertical-align: 0
    }

    .dropstart .dropdown-toggle:after {
        display: inline-block;
        margin-left: .255em;
        vertical-align: .255em;
        content: "";
        display: none
    }

    .dropstart .dropdown-toggle:before {
        display: inline-block;
        margin-right: .255em;
        vertical-align: .255em;
        content: "";
        border-top: .3em solid #0000;
        border-right: .3em solid;
        border-bottom: .3em solid #0000
    }

    .dropstart .dropdown-toggle:empty:after {
        margin-left: 0
    }

    .dropstart .dropdown-toggle:before {
        vertical-align: 0
    }

    .dropdown-item:focus,
    .dropdown-item:hover {
        color: var(--bs-dropdown-link-hover-color);
        background-color: var(--bs-dropdown-link-hover-bg)
    }

    .dropdown-item:active {
        color: var(--bs-dropdown-link-active-color);
        text-decoration: none;
        background-color: var(--bs-dropdown-link-active-bg)
    }

    .dropdown-item:disabled {
        color: var(--bs-dropdown-link-disabled-color);
        pointer-events: none;
        background-color: initial
    }

    .btn-group-vertical>.btn-check:checked+.btn,
    .btn-group-vertical>.btn-check:focus+.btn,
    .btn-group-vertical>.btn:active,
    .btn-group-vertical>.btn:focus,
    .btn-group-vertical>.btn:hover,
    .btn-group>.btn-check:checked+.btn,
    .btn-group>.btn-check:focus+.btn,
    .btn-group>.btn:active,
    .btn-group>.btn:focus,
    .btn-group>.btn:hover {
        z-index: 1
    }

    .dropdown-toggle-split:after,
    .dropend .dropdown-toggle-split:after,
    .dropup .dropdown-toggle-split:after {
        margin-left: 0
    }

    .dropstart .dropdown-toggle-split:before {
        margin-right: 0
    }

    .nav-link {
        display: block;
        padding: var(--bs-nav-link-padding-y) var(--bs-nav-link-padding-x);
        font-size: var(--bs-nav-link-font-size);
        font-weight: var(--bs-nav-link-font-weight);
        text-decoration: none;
        background: 0 0;
        border: 0;
        transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out
    }

    @media (prefers-reduced-motion:reduce) {
        .nav-link {
            transition: none
        }
    }

    .nav-link:focus,
    .nav-link:hover {
        color: var(--bs-nav-link-hover-color)
    }

    .nav-link:focus-visible {
        outline: 0;
        box-shadow: 0 0 0 .25rem #0d6efd40
    }

    .nav-link:disabled {
        color: var(--bs-nav-link-disabled-color);
        pointer-events: none;
        cursor: default
    }

    .nav-tabs .nav-link:focus,
    .nav-tabs .nav-link:hover {
        isolation: isolate;
        border-color: var(--bs-nav-tabs-link-hover-border-color)
    }

    .nav-underline .nav-link:focus,
    .nav-underline .nav-link:hover {
        border-bottom-color: initial
    }

    .navbar-brand:focus,
    .navbar-brand:hover {
        color: var(--bs-navbar-brand-hover-color)
    }

    .navbar-text a:focus,
    .navbar-text a:hover {
        color: var(--bs-navbar-active-color)
    }

    .navbar-toggler:hover {
        text-decoration: none
    }

    .navbar-toggler:focus {
        text-decoration: none;
        outline: 0;
        box-shadow: 0 0 0 var(--bs-navbar-toggler-focus-width)
    }

    .card {
        --bs-card-spacer-y: 1rem;
        --bs-card-spacer-x: 1rem;
        --bs-card-title-spacer-y: 0.5rem;
        --bs-card-title-color: ;
        --bs-card-subtitle-color: ;
        --bs-card-border-width: var(--bs-border-width);
        --bs-card-border-color: var(--bs-border-color-translucent);
        --bs-card-border-radius: var(--bs-border-radius);
        --bs-card-box-shadow: ;
        --bs-card-inner-border-radius: calc(var(--bs-border-radius) - (var(--bs-border-width)));
        --bs-card-cap-padding-y: 0.5rem;
        --bs-card-cap-padding-x: 1rem;
        --bs-card-cap-bg: rgba(var(--bs-body-color-rgb), 0.03);
        --bs-card-cap-color: ;
        --bs-card-height: ;
        --bs-card-color: ;
        --bs-card-bg: var(--bs-body-bg);
        --bs-card-img-overlay-padding: 1rem;
        --bs-card-group-margin: 0.75rem;
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        height: var(--bs-card-height);
        color: var(--bs-body-color);
        word-wrap: break-word;
        background-color: var(--bs-card-bg);
        background-clip: initial;
        border: var(--bs-card-border-width) solid var(--bs-card-border-color);
        border-radius: var(--bs-card-border-radius)
    }

    .card-body {
        flex: 1 1 auto;
        padding: var(--bs-card-spacer-y) var(--bs-card-spacer-x);
        color: var(--bs-card-color)
    }

    .card-header {
        padding: var(--bs-card-cap-padding-y) var(--bs-card-cap-padding-x);
        margin-bottom: 0;
        color: var(--bs-card-cap-color);
        background-color: var(--bs-card-cap-bg);
        border-bottom: var(--bs-card-border-width) solid var(--bs-card-border-color)
    }

    .card-header:first-child {
        border-radius: var(--bs-card-inner-border-radius) var(--bs-card-inner-border-radius)0 0
    }

    .accordion {
        --bs-accordion-color: var(--bs-body-color);
        --bs-accordion-bg: var(--bs-body-bg);
        --bs-accordion-transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, border-radius 0.15s ease;
        --bs-accordion-border-color: var(--bs-border-color);
        --bs-accordion-border-width: var(--bs-border-width);
        --bs-accordion-border-radius: var(--bs-border-radius);
        --bs-accordion-inner-border-radius: calc(var(--bs-border-radius) - (var(--bs-border-width)));
        --bs-accordion-btn-padding-x: 1.25rem;
        --bs-accordion-btn-padding-y: 1rem;
        --bs-accordion-btn-color: var(--bs-body-color);
        --bs-accordion-btn-bg: var(--bs-accordion-bg);
        --bs-accordion-btn-icon: url(data:image/svg+xml,%3csvg\ xmlns=\'http://www.w3.org/2000/svg\'\ viewBox=\'0\ 0\ 16\ 16\'\ fill=\'none\'\ stroke=\'%23212529\'\ stroke-linecap=\'round\'\ stroke-linejoin=\'round\'%3e%3cpath\ d=\'m2\ 5\ 6\ 6\ 6-6\'/%3e%3c/svg%3e);
        --bs-accordion-btn-icon-width: 1.25rem;
        --bs-accordion-btn-icon-transform: rotate(-180deg);
        --bs-accordion-btn-icon-transition: transform 0.2s ease-in-out;
        --bs-accordion-btn-active-icon: url(data:image/svg+xml,%3csvg\ xmlns=\'http://www.w3.org/2000/svg\'\ viewBox=\'0\ 0\ 16\ 16\'\ fill=\'none\'\ stroke=\'%23052c65\'\ stroke-linecap=\'round\'\ stroke-linejoin=\'round\'%3e%3cpath\ d=\'m2\ 5\ 6\ 6\ 6-6\'/%3e%3c/svg%3e);
        --bs-accordion-btn-focus-box-shadow: 0 0 0 0.25rem #0d6efd40;
        --bs-accordion-body-padding-x: 1.25rem;
        --bs-accordion-body-padding-y: 1rem;
        --bs-accordion-active-color: var(--bs-primary-text-emphasis);
        --bs-accordion-active-bg: var(--bs-primary-bg-subtle)
    }

    .accordion-button {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
        padding: var(--bs-accordion-btn-padding-y) var(--bs-accordion-btn-padding-x);
        font-size: 1rem;
        color: var(--bs-accordion-btn-color);
        text-align: left;
        background-color: var(--bs-accordion-btn-bg);
        border: 0;
        overflow-anchor: none;
        transition: var(--bs-accordion-transition)
    }

    @media (prefers-reduced-motion:reduce) {
        .accordion-button {
            transition: none
        }
    }

    .accordion-button:not(.collapsed) {
        color: var(--bs-accordion-active-color);
        background-color: var(--bs-accordion-active-bg);
        box-shadow: inset 0 calc(-1*var(--bs-accordion-border-width))0 var(--bs-accordion-border-color)
    }

    .accordion-button:not(.collapsed):after {
        background-image: var(--bs-accordion-btn-active-icon);
        transform: var(--bs-accordion-btn-icon-transform)
    }

    .accordion-button:after {
        flex-shrink: 0;
        width: var(--bs-accordion-btn-icon-width);
        height: var(--bs-accordion-btn-icon-width);
        margin-left: auto;
        content: "";
        background-image: var(--bs-accordion-btn-icon);
        background-repeat: no-repeat;
        background-size: var(--bs-accordion-btn-icon-width);
        transition: var(--bs-accordion-btn-icon-transition)
    }

    @media (prefers-reduced-motion:reduce) {
        .accordion-button:after {
            transition: none
        }
    }

    .accordion-button:hover {
        z-index: 2
    }

    .accordion-button:focus {
        z-index: 3;
        outline: 0;
        box-shadow: var(--bs-accordion-btn-focus-box-shadow)
    }

    .accordion-header {
        margin-bottom: 0
    }

    .accordion-item {
        color: var(--bs-accordion-color);
        background-color: var(--bs-accordion-bg);
        border: var(--bs-accordion-border-width) solid var(--bs-accordion-border-color)
    }

    .accordion-item:first-of-type {
        border-top-left-radius: var(--bs-accordion-border-radius);
        border-top-right-radius: var(--bs-accordion-border-radius)
    }

    .accordion-item:first-of-type>.accordion-header .accordion-button {
        border-top-left-radius: var(--bs-accordion-inner-border-radius);
        border-top-right-radius: var(--bs-accordion-inner-border-radius)
    }

    .accordion-item:not(:first-of-type) {
        border-top: 0
    }

    .accordion-item:last-of-type {
        border-bottom-right-radius: var(--bs-accordion-border-radius);
        border-bottom-left-radius: var(--bs-accordion-border-radius)
    }

    .accordion-item:last-of-type>.accordion-header .accordion-button.collapsed {
        border-bottom-right-radius: var(--bs-accordion-inner-border-radius);
        border-bottom-left-radius: var(--bs-accordion-inner-border-radius)
    }

    .accordion-item:last-of-type>.accordion-collapse {
        border-bottom-right-radius: var(--bs-accordion-border-radius);
        border-bottom-left-radius: var(--bs-accordion-border-radius)
    }

    .accordion-flush>.accordion-item {
        border-right: 0;
        border-left: 0;
        border-radius: 0
    }

    .accordion-flush>.accordion-item:first-child {
        border-top: 0
    }

    .accordion-flush>.accordion-item:last-child {
        border-bottom: 0
    }

    .accordion-flush>.accordion-item>.accordion-collapse,
    .accordion-flush>.accordion-item>.accordion-header .accordion-button,
    .accordion-flush>.accordion-item>.accordion-header .accordion-button.collapsed {
        border-radius: 0
    }

    [data-bs-theme=dark] .accordion-button:after {
        --bs-accordion-btn-icon: url(data:image/svg+xml,%3csvg\ xmlns=\'http://www.w3.org/2000/svg\'\ viewBox=\'0\ 0\ 16\ 16\'\ fill=\'%236ea8fe\'%3e%3cpath\ fill-rule=\'evenodd\'\ d=\'M1.646\ 4.646a.5.5\ 0\ 0\ 1\ .708\ 0L8\ 10.293l5.646-5.647a.5.5\ 0\ 0\ 1\ .708.708l-6\ 6a.5.5\ 0\ 0\ 1-.708\ 0l-6-6a.5.5\ 0\ 0\ 1\ 0-.708\'/%3e%3c/svg%3e);
        --bs-accordion-btn-active-icon: url(data:image/svg+xml,%3csvg\ xmlns=\'http://www.w3.org/2000/svg\'\ viewBox=\'0\ 0\ 16\ 16\'\ fill=\'%236ea8fe\'%3e%3cpath\ fill-rule=\'evenodd\'\ d=\'M1.646\ 4.646a.5.5\ 0\ 0\ 1\ .708\ 0L8\ 10.293l5.646-5.647a.5.5\ 0\ 0\ 1\ .708.708l-6\ 6a.5.5\ 0\ 0\ 1-.708\ 0l-6-6a.5.5\ 0\ 0\ 1\ 0-.708\'/%3e%3c/svg%3e)
    }

    .breadcrumb {
        --bs-breadcrumb-padding-x: 0;
        --bs-breadcrumb-padding-y: 0;
        --bs-breadcrumb-margin-bottom: 1rem;
        --bs-breadcrumb-bg: ;
        --bs-breadcrumb-border-radius: ;
        --bs-breadcrumb-divider-color: var(--bs-secondary-color);
        --bs-breadcrumb-item-padding-x: 0.5rem;
        --bs-breadcrumb-item-active-color: var(--bs-secondary-color);
        display: flex;
        flex-wrap: wrap;
        padding: var(--bs-breadcrumb-padding-y) var(--bs-breadcrumb-padding-x);
        margin-bottom: var(--bs-breadcrumb-margin-bottom);
        font-size: var(--bs-breadcrumb-font-size);
        list-style: none;
        background-color: var(--bs-breadcrumb-bg);
        border-radius: var(--bs-breadcrumb-border-radius)
    }

    .breadcrumb-item+.breadcrumb-item {
        padding-left: var(--bs-breadcrumb-item-padding-x)
    }

    .breadcrumb-item+.breadcrumb-item:before {
        float: left;
        padding-right: var(--bs-breadcrumb-item-padding-x);
        color: var(--bs-breadcrumb-divider-color);
        content: var(--bs-breadcrumb-divider, "/")
    }

    .breadcrumb-item.active {
        color: var(--bs-breadcrumb-item-active-color)
    }

    .page-link:hover {
        z-index: 2;
        color: var(--bs-pagination-hover-color);
        background-color: var(--bs-pagination-hover-bg);
        border-color: var(--bs-pagination-hover-border-color)
    }

    .page-link:focus {
        z-index: 3;
        color: var(--bs-pagination-focus-color);
        background-color: var(--bs-pagination-focus-bg);
        outline: 0;
        box-shadow: var(--bs-pagination-focus-box-shadow)
    }

    @keyframes progress-bar-stripes {
        0% {
            background-position-x: var(--bs-progress-height)
        }
    }

    .list-group {
        --bs-list-group-color: var(--bs-body-color);
        --bs-list-group-bg: var(--bs-body-bg);
        --bs-list-group-border-color: var(--bs-border-color);
        --bs-list-group-border-width: var(--bs-border-width);
        --bs-list-group-border-radius: var(--bs-border-radius);
        --bs-list-group-item-padding-x: 1rem;
        --bs-list-group-item-padding-y: 0.5rem;
        --bs-list-group-action-color: var(--bs-secondary-color);
        --bs-list-group-action-hover-color: var(--bs-emphasis-color);
        --bs-list-group-action-hover-bg: var(--bs-tertiary-bg);
        --bs-list-group-action-active-color: var(--bs-body-color);
        --bs-list-group-action-active-bg: var(--bs-secondary-bg);
        --bs-list-group-disabled-color: var(--bs-secondary-color);
        --bs-list-group-disabled-bg: var(--bs-body-bg);
        --bs-list-group-active-color: #fff;
        --bs-list-group-active-bg: #0d6efd;
        --bs-list-group-active-border-color: #0d6efd;
        display: flex;
        flex-direction: column;
        padding-left: 0;
        margin-bottom: 0;
        border-radius: var(--bs-list-group-border-radius)
    }

    .list-group-numbered>.list-group-item:before {
        content: counters(section, ".")". ";
        counter-increment: section
    }

    .list-group-item {
        position: relative;
        display: block;
        padding: var(--bs-list-group-item-padding-y) var(--bs-list-group-item-padding-x);
        color: var(--bs-list-group-color);
        text-decoration: none;
        background-color: var(--bs-list-group-bg);
        border: var(--bs-list-group-border-width) solid var(--bs-list-group-border-color)
    }

    .list-group-item:first-child {
        border-top-left-radius: inherit;
        border-top-right-radius: inherit
    }

    .list-group-item:last-child {
        border-bottom-right-radius: inherit;
        border-bottom-left-radius: inherit
    }

    .list-group-item:disabled {
        color: var(--bs-list-group-disabled-color);
        pointer-events: none;
        background-color: var(--bs-list-group-disabled-bg)
    }

    .list-group-item-action:not(.active):focus,
    .list-group-item-action:not(.active):hover {
        z-index: 1;
        color: var(--bs-list-group-action-hover-color);
        text-decoration: none;
        background-color: var(--bs-list-group-action-hover-bg)
    }

    .list-group-item-action:not(.active):active {
        color: var(--bs-list-group-action-active-color);
        background-color: var(--bs-list-group-action-active-bg)
    }

    .btn-close:hover {
        color: var(--bs-btn-close-color)
    }

    .btn-close:hover {
        text-decoration: none;
        opacity: var(--bs-btn-close-hover-opacity)
    }

    .btn-close:focus {
        outline: 0;
        box-shadow: var(--bs-btn-close-focus-shadow);
        opacity: var(--bs-btn-close-focus-opacity)
    }

    .btn-close:disabled {
        pointer-events: none;
        -webkit-user-select: none;
        user-select: none;
        opacity: var(--bs-btn-close-disabled-opacity)
    }

    :root {
        --bs-btn-close-filter:
    }

    .tooltip .tooltip-arrow:before {
        position: absolute;
        content: "";
        border-color: #0000;
        border-style: solid
    }

    .bs-tooltip-auto[data-popper-placement^=top] .tooltip-arrow:before,
    .bs-tooltip-top .tooltip-arrow:before {
        top: -1px;
        border-width: var(--bs-tooltip-arrow-height) calc(var(--bs-tooltip-arrow-width)*.5)0;
        border-top-color: var(--bs-tooltip-bg)
    }

    .bs-tooltip-auto[data-popper-placement^=right] .tooltip-arrow:before,
    .bs-tooltip-end .tooltip-arrow:before {
        right: -1px;
        border-width: calc(var(--bs-tooltip-arrow-width)*.5) var(--bs-tooltip-arrow-height) calc(var(--bs-tooltip-arrow-width)*.5)0;
        border-right-color: var(--bs-tooltip-bg)
    }

    .bs-tooltip-auto[data-popper-placement^=bottom] .tooltip-arrow:before,
    .bs-tooltip-bottom .tooltip-arrow:before {
        bottom: -1px;
        border-width: 0 calc(var(--bs-tooltip-arrow-width)*.5) var(--bs-tooltip-arrow-height);
        border-bottom-color: var(--bs-tooltip-bg)
    }

    .bs-tooltip-auto[data-popper-placement^=left] .tooltip-arrow:before,
    .bs-tooltip-start .tooltip-arrow:before {
        left: -1px;
        border-width: calc(var(--bs-tooltip-arrow-width)*.5)0 calc(var(--bs-tooltip-arrow-width)*.5) var(--bs-tooltip-arrow-height);
        border-left-color: var(--bs-tooltip-bg)
    }

    .popover .popover-arrow:after,
    .popover .popover-arrow:before {
        position: absolute;
        display: block;
        content: "";
        border: 0 solid #0000
    }

    .bs-popover-auto[data-popper-placement^=top]>.popover-arrow:after,
    .bs-popover-auto[data-popper-placement^=top]>.popover-arrow:before,
    .bs-popover-top>.popover-arrow:after,
    .bs-popover-top>.popover-arrow:before {
        border-width: var(--bs-popover-arrow-height) calc(var(--bs-popover-arrow-width)*.5)0
    }

    .bs-popover-auto[data-popper-placement^=top]>.popover-arrow:before,
    .bs-popover-top>.popover-arrow:before {
        bottom: 0;
        border-top-color: var(--bs-popover-arrow-border)
    }

    .bs-popover-auto[data-popper-placement^=top]>.popover-arrow:after,
    .bs-popover-top>.popover-arrow:after {
        bottom: var(--bs-popover-border-width);
        border-top-color: var(--bs-popover-bg)
    }

    .bs-popover-auto[data-popper-placement^=right]>.popover-arrow:after,
    .bs-popover-auto[data-popper-placement^=right]>.popover-arrow:before,
    .bs-popover-end>.popover-arrow:after,
    .bs-popover-end>.popover-arrow:before {
        border-width: calc(var(--bs-popover-arrow-width)*.5) var(--bs-popover-arrow-height) calc(var(--bs-popover-arrow-width)*.5)0
    }

    .bs-popover-auto[data-popper-placement^=right]>.popover-arrow:before,
    .bs-popover-end>.popover-arrow:before {
        left: 0;
        border-right-color: var(--bs-popover-arrow-border)
    }

    .bs-popover-auto[data-popper-placement^=right]>.popover-arrow:after,
    .bs-popover-end>.popover-arrow:after {
        left: var(--bs-popover-border-width);
        border-right-color: var(--bs-popover-bg)
    }

    .bs-popover-auto[data-popper-placement^=bottom]>.popover-arrow:after,
    .bs-popover-auto[data-popper-placement^=bottom]>.popover-arrow:before,
    .bs-popover-bottom>.popover-arrow:after,
    .bs-popover-bottom>.popover-arrow:before {
        border-width: 0 calc(var(--bs-popover-arrow-width)*.5) var(--bs-popover-arrow-height)
    }

    .bs-popover-auto[data-popper-placement^=bottom]>.popover-arrow:before,
    .bs-popover-bottom>.popover-arrow:before {
        top: 0;
        border-bottom-color: var(--bs-popover-arrow-border)
    }

    .bs-popover-auto[data-popper-placement^=bottom]>.popover-arrow:after,
    .bs-popover-bottom>.popover-arrow:after {
        top: var(--bs-popover-border-width);
        border-bottom-color: var(--bs-popover-bg)
    }

    .bs-popover-auto[data-popper-placement^=bottom] .popover-header:before,
    .bs-popover-bottom .popover-header:before {
        position: absolute;
        top: 0;
        left: 50%;
        display: block;
        width: var(--bs-popover-arrow-width);
        margin-left: calc(-.5*var(--bs-popover-arrow-width));
        content: "";
        border-bottom: var(--bs-popover-border-width) solid var(--bs-popover-header-bg)
    }

    .bs-popover-auto[data-popper-placement^=left]>.popover-arrow:after,
    .bs-popover-auto[data-popper-placement^=left]>.popover-arrow:before,
    .bs-popover-start>.popover-arrow:after,
    .bs-popover-start>.popover-arrow:before {
        border-width: calc(var(--bs-popover-arrow-width)*.5)0 calc(var(--bs-popover-arrow-width)*.5) var(--bs-popover-arrow-height)
    }

    .bs-popover-auto[data-popper-placement^=left]>.popover-arrow:before,
    .bs-popover-start>.popover-arrow:before {
        right: 0;
        border-left-color: var(--bs-popover-arrow-border)
    }

    .bs-popover-auto[data-popper-placement^=left]>.popover-arrow:after,
    .bs-popover-start>.popover-arrow:after {
        right: var(--bs-popover-border-width);
        border-left-color: var(--bs-popover-bg)
    }

    .carousel-inner:after {
        display: block;
        clear: both;
        content: ""
    }

    .carousel-control-next:focus,
    .carousel-control-next:hover,
    .carousel-control-prev:focus,
    .carousel-control-prev:hover {
        color: #fff;
        text-decoration: none;
        outline: 0;
        opacity: .9
    }

    :root {
        --bs-carousel-indicator-active-bg: #fff;
        --bs-carousel-caption-color: #fff;
        --bs-carousel-control-icon-filter:
    }

    @keyframes spinner-border {
        to {
            transform: rotate(1turn)
        }
    }

    @keyframes spinner-grow {
        0% {
            transform: scale(0)
        }

        50% {
            opacity: 1;
            transform: none
        }
    }

    .placeholder.btn:before {
        display: inline-block;
        content: ""
    }

    @keyframes placeholder-glow {
        50% {
            opacity: .2
        }
    }

    @keyframes placeholder-wave {
        to {
            mask-position: -200%0
        }
    }

    .clearfix:after {
        display: block;
        clear: both;
        content: ""
    }

    .link-primary:focus,
    .link-primary:hover {
        color: RGBA(10, 88, 202, var(--bs-link-opacity, 1)) !important;
        -webkit-text-decoration-color: RGBA(10, 88, 202, var(--bs-link-underline-opacity, 1)) !important;
        text-decoration-color: RGBA(10, 88, 202, var(--bs-link-underline-opacity, 1)) !important
    }

    .link-secondary {
        color: RGBA(var(--bs-secondary-rgb), var(--bs-link-opacity, 1)) !important;
        -webkit-text-decoration-color: RGBA(var(--bs-secondary-rgb), var(--bs-link-underline-opacity, 1)) !important;
        text-decoration-color: RGBA(var(--bs-secondary-rgb), var(--bs-link-underline-opacity, 1)) !important
    }

    .link-secondary:focus,
    .link-secondary:hover {
        color: RGBA(86, 94, 100, var(--bs-link-opacity, 1)) !important;
        -webkit-text-decoration-color: RGBA(86, 94, 100, var(--bs-link-underline-opacity, 1)) !important;
        text-decoration-color: RGBA(86, 94, 100, var(--bs-link-underline-opacity, 1)) !important
    }

    .link-success:focus,
    .link-success:hover {
        color: RGBA(20, 108, 67, var(--bs-link-opacity, 1)) !important;
        -webkit-text-decoration-color: RGBA(20, 108, 67, var(--bs-link-underline-opacity, 1)) !important;
        text-decoration-color: RGBA(20, 108, 67, var(--bs-link-underline-opacity, 1)) !important
    }

    .link-info:focus,
    .link-info:hover {
        color: RGBA(61, 213, 243, var(--bs-link-opacity, 1)) !important;
        -webkit-text-decoration-color: RGBA(61, 213, 243, var(--bs-link-underline-opacity, 1)) !important;
        text-decoration-color: RGBA(61, 213, 243, var(--bs-link-underline-opacity, 1)) !important
    }

    .link-warning:focus,
    .link-warning:hover {
        color: RGBA(255, 205, 57, var(--bs-link-opacity, 1)) !important;
        -webkit-text-decoration-color: RGBA(255, 205, 57, var(--bs-link-underline-opacity, 1)) !important;
        text-decoration-color: RGBA(255, 205, 57, var(--bs-link-underline-opacity, 1)) !important
    }

    .link-danger:focus,
    .link-danger:hover {
        color: RGBA(176, 42, 55, var(--bs-link-opacity, 1)) !important;
        -webkit-text-decoration-color: RGBA(176, 42, 55, var(--bs-link-underline-opacity, 1)) !important;
        text-decoration-color: RGBA(176, 42, 55, var(--bs-link-underline-opacity, 1)) !important
    }

    .link-light:focus,
    .link-light:hover {
        color: RGBA(249, 250, 251, var(--bs-link-opacity, 1)) !important;
        -webkit-text-decoration-color: RGBA(249, 250, 251, var(--bs-link-underline-opacity, 1)) !important;
        text-decoration-color: RGBA(249, 250, 251, var(--bs-link-underline-opacity, 1)) !important
    }

    .link-dark:focus,
    .link-dark:hover {
        color: RGBA(26, 30, 33, var(--bs-link-opacity, 1)) !important;
        -webkit-text-decoration-color: RGBA(26, 30, 33, var(--bs-link-underline-opacity, 1)) !important;
        text-decoration-color: RGBA(26, 30, 33, var(--bs-link-underline-opacity, 1)) !important
    }

    .link-body-emphasis:focus,
    .link-body-emphasis:hover {
        color: RGBA(var(--bs-emphasis-color-rgb), var(--bs-link-opacity, .75)) !important;
        -webkit-text-decoration-color: RGBA(var(--bs-emphasis-color-rgb), var(--bs-link-underline-opacity, .75)) !important;
        text-decoration-color: RGBA(var(--bs-emphasis-color-rgb), var(--bs-link-underline-opacity, .75)) !important
    }

    .focus-ring:focus {
        outline: 0;
        box-shadow: var(--bs-focus-ring-x, 0) var(--bs-focus-ring-y, 0) var(--bs-focus-ring-blur, 0) var(--bs-focus-ring-width) var(--bs-focus-ring-color)
    }

    .icon-link-hover:focus-visible>.bi,
    .icon-link-hover:hover>.bi {
        transform: var(--bs-icon-link-transform, translate3d(.25em, 0, 0))
    }

    .ratio:before {
        display: block;
        padding-top: var(--bs-aspect-ratio);
        content: ""
    }

    .visually-hidden-focusable:not(:focus):not(:focus-within) {
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important
    }

    .visually-hidden-focusable:not(:focus):not(:focus-within):not(caption) {
        position: absolute !important
    }

    .visually-hidden-focusable:not(:focus):not(:focus-within) * {
        overflow: hidden !important
    }

    .stretched-link:after {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 1;
        content: ""
    }

    .float-start {
        float: left !important
    }

    .float-end {
        float: right !important
    }

    .d-flex {
        display: flex !important
    }

    .shadow-lg {
        box-shadow: var(--bs-box-shadow-lg) !important
    }

    .align-items-center {
        align-items: center !important
    }

    .mb-5 {
        margin-bottom: 3rem !important
    }

    .p-3 {
        padding: 1rem !important
    }

    .py-3 {
        padding-top: 1rem !important;
        padding-bottom: 1rem !important
    }

    .text-end {
        text-align: right !important
    }

    .text-center {
        text-align: center !important
    }

    .link-opacity-10-hover:hover {
        --bs-link-opacity: 0.1
    }

    .link-opacity-25-hover:hover {
        --bs-link-opacity: 0.25
    }

    .link-opacity-50-hover:hover {
        --bs-link-opacity: 0.5
    }

    .link-opacity-75-hover:hover {
        --bs-link-opacity: 0.75
    }

    .link-opacity-100-hover:hover {
        --bs-link-opacity: 1
    }

    .link-offset-1-hover:hover {
        text-underline-offset: .125em !important
    }

    .link-offset-2-hover:hover {
        text-underline-offset: .25em !important
    }

    .link-offset-3-hover:hover {
        text-underline-offset: .375em !important
    }

    .link-underline-opacity-0-hover:hover {
        --bs-link-underline-opacity: 0
    }

    .link-underline-opacity-10-hover:hover {
        --bs-link-underline-opacity: 0.1
    }

    .link-underline-opacity-25-hover:hover {
        --bs-link-underline-opacity: 0.25
    }

    .link-underline-opacity-50-hover:hover {
        --bs-link-underline-opacity: 0.5
    }

    .link-underline-opacity-75-hover:hover {
        --bs-link-underline-opacity: 0.75
    }

    .link-underline-opacity-100-hover:hover {
        --bs-link-underline-opacity: 1
    }

    .bg-light {
        --bs-bg-opacity: 1;
        background-color: rgba(var(--bs-light-rgb), var(--bs-bg-opacity)) !important
    }

    .bg-body {
        --bs-bg-opacity: 1;
        background-color: rgba(var(--bs-body-bg-rgb), var(--bs-bg-opacity)) !important
    }

    .rounded {
        border-radius: var(--bs-border-radius) !important
    }

    @media (min-width:576px) {
        .px-sm-2 {
            padding-right: .5rem !important;
            padding-left: .5rem !important
        }
    }
</style>
<style>
    :root {
        --swal2-outline: 0 0 0 3px rgba(100, 150, 200, 0.5);
        --swal2-container-padding: 0.625em;
        --swal2-backdrop: rgba(0, 0, 0, 0.4);
        --swal2-backdrop-transition: background-color 0.15s;
        --swal2-width: 32em;
        --swal2-padding: 0 0 1.25em;
        --swal2-border: none;
        --swal2-border-radius: 0.3125rem;
        --swal2-background: white;
        --swal2-color: #545454;
        --swal2-show-animation: swal2-show 0.3s;
        --swal2-hide-animation: swal2-hide 0.15s forwards;
        --swal2-icon-zoom: 1;
        --swal2-icon-animations: true;
        --swal2-title-padding: 0.8em 1em 0;
        --swal2-html-container-padding: 1em 1.6em 0.3em;
        --swal2-input-border: 1px solid #d9d9d9;
        --swal2-input-border-radius: 0.1875em;
        --swal2-input-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.06), 0 0 0 3px transparent;
        --swal2-input-background: transparent;
        --swal2-input-transition: border-color 0.2s, box-shadow 0.2s;
        --swal2-input-hover-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.06), 0 0 0 3px transparent;
        --swal2-input-focus-border: 1px solid #b4dbed;
        --swal2-input-focus-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.06), 0 0 0 3px rgba(100, 150, 200, 0.5);
        --swal2-progress-step-background: #add8e6;
        --swal2-validation-message-background: #f0f0f0;
        --swal2-validation-message-color: #666;
        --swal2-footer-border-color: #eee;
        --swal2-footer-background: transparent;
        --swal2-footer-color: inherit;
        --swal2-timer-progress-bar-background: rgba(0, 0, 0, 0.3);
        --swal2-close-button-position: initial;
        --swal2-close-button-inset: auto;
        --swal2-close-button-font-size: 2.5em;
        --swal2-close-button-color: #ccc;
        --swal2-close-button-transition: color 0.2s, box-shadow 0.2s;
        --swal2-close-button-outline: initial;
        --swal2-close-button-box-shadow: inset 0 0 0 3px transparent;
        --swal2-close-button-focus-box-shadow: inset var(--swal2-outline);
        --swal2-close-button-hover-transform: none;
        --swal2-actions-justify-content: center;
        --swal2-actions-width: auto;
        --swal2-actions-margin: 1.25em auto 0;
        --swal2-actions-padding: 0;
        --swal2-actions-border-radius: 0;
        --swal2-actions-background: transparent;
        --swal2-action-button-transition: background-color 0.2s, box-shadow 0.2s;
        --swal2-action-button-hover: black 10%;
        --swal2-action-button-active: black 10%;
        --swal2-confirm-button-box-shadow: none;
        --swal2-confirm-button-border-radius: 0.25em;
        --swal2-confirm-button-background-color: #7066e0;
        --swal2-confirm-button-color: #fff;
        --swal2-deny-button-box-shadow: none;
        --swal2-deny-button-border-radius: 0.25em;
        --swal2-deny-button-background-color: #dc3741;
        --swal2-deny-button-color: #fff;
        --swal2-cancel-button-box-shadow: none;
        --swal2-cancel-button-border-radius: 0.25em;
        --swal2-cancel-button-background-color: #6e7881;
        --swal2-cancel-button-color: #fff;
        --swal2-toast-show-animation: swal2-toast-show 0.5s;
        --swal2-toast-hide-animation: swal2-toast-hide 0.1s forwards;
        --swal2-toast-border: none;
        --swal2-toast-box-shadow: 0 0 1px hsl(0deg 0% 0%/0.075), 0 1px 2px hsl(0deg 0% 0%/0.075), 1px 2px 4px hsl(0deg 0% 0%/0.075), 1px 3px 8px hsl(0deg 0% 0%/0.075), 2px 4px 16px hsl(0deg 0% 0%/0.075)
    }

    div:where(.swal2-container) div:where(.swal2-popup):focus {
        outline: none
    }

    div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-confirm):hover {
        background-color: color-mix(in srgb, var(--swal2-confirm-button-background-color), var(--swal2-action-button-hover))
    }

    div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-confirm):active {
        background-color: color-mix(in srgb, var(--swal2-confirm-button-background-color), var(--swal2-action-button-active))
    }

    div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-deny):hover {
        background-color: color-mix(in srgb, var(--swal2-deny-button-background-color), var(--swal2-action-button-hover))
    }

    div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-deny):active {
        background-color: color-mix(in srgb, var(--swal2-deny-button-background-color), var(--swal2-action-button-active))
    }

    div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-cancel):hover {
        background-color: color-mix(in srgb, var(--swal2-cancel-button-background-color), var(--swal2-action-button-hover))
    }

    div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-cancel):active {
        background-color: color-mix(in srgb, var(--swal2-cancel-button-background-color), var(--swal2-action-button-active))
    }

    div:where(.swal2-container) button:where(.swal2-styled):focus-visible {
        outline: none;
        box-shadow: var(--swal2-action-button-focus-box-shadow)
    }

    div:where(.swal2-container) button:where(.swal2-styled)::-moz-focus-inner {
        border: 0
    }

    div:where(.swal2-container) button:where(.swal2-close):hover {
        transform: var(--swal2-close-button-hover-transform);
        background: rgba(0, 0, 0, 0);
        color: #f27474
    }

    div:where(.swal2-container) button:where(.swal2-close):focus-visible {
        outline: none;
        box-shadow: var(--swal2-close-button-focus-box-shadow)
    }

    div:where(.swal2-container) button:where(.swal2-close)::-moz-focus-inner {
        border: 0
    }

    div:where(.swal2-container) input:where(.swal2-input):hover,
    div:where(.swal2-container) input:where(.swal2-file):hover,
    div:where(.swal2-container) textarea:where(.swal2-textarea):hover {
        box-shadow: var(--swal2-input-hover-box-shadow)
    }

    div:where(.swal2-container) input:where(.swal2-input):focus,
    div:where(.swal2-container) input:where(.swal2-file):focus,
    div:where(.swal2-container) textarea:where(.swal2-textarea):focus {
        border: var(--swal2-input-focus-border);
        outline: none;
        box-shadow: var(--swal2-input-focus-box-shadow)
    }

    div:where(.swal2-container) input:where(.swal2-input)::placeholder,
    div:where(.swal2-container) input:where(.swal2-file)::placeholder,
    div:where(.swal2-container) textarea:where(.swal2-textarea)::placeholder {
        color: #ccc
    }

    div:where(.swal2-container) div:where(.swal2-validation-message)::before {
        content: "!";
        display: inline-block;
        width: 1.5em;
        min-width: 1.5em;
        height: 1.5em;
        margin: 0 .625em;
        border-radius: 50%;
        background-color: #f27474;
        color: #fff;
        font-weight: 600;
        line-height: 1.5em;
        text-align: center
    }

    @keyframes swal2-show {
        0% {
            transform: translate3d(0, -50px, 0) scale(0.9);
            opacity: 0
        }

        100% {
            transform: translate3d(0, 0, 0) scale(1);
            opacity: 1
        }
    }

    @keyframes swal2-hide {
        0% {
            transform: translate3d(0, 0, 0) scale(1);
            opacity: 1
        }

        100% {
            transform: translate3d(0, -50px, 0) scale(0.9);
            opacity: 0
        }
    }

    @keyframes swal2-animate-success-line-tip {
        0% {
            top: 1.1875em;
            left: .0625em;
            width: 0
        }

        54% {
            top: 1.0625em;
            left: .125em;
            width: 0
        }

        70% {
            top: 2.1875em;
            left: -0.375em;
            width: 3.125em
        }

        84% {
            top: 3em;
            left: 1.3125em;
            width: 1.0625em
        }

        100% {
            top: 2.8125em;
            left: .8125em;
            width: 1.5625em
        }
    }

    @keyframes swal2-animate-success-line-long {
        0% {
            top: 3.375em;
            right: 2.875em;
            width: 0
        }

        65% {
            top: 3.375em;
            right: 2.875em;
            width: 0
        }

        84% {
            top: 2.1875em;
            right: 0;
            width: 3.4375em
        }

        100% {
            top: 2.375em;
            right: .5em;
            width: 2.9375em
        }
    }

    @keyframes swal2-rotate-success-circular-line {
        0% {
            transform: rotate(-45deg)
        }

        5% {
            transform: rotate(-45deg)
        }

        12% {
            transform: rotate(-405deg)
        }

        100% {
            transform: rotate(-405deg)
        }
    }

    @keyframes swal2-animate-error-x-mark {
        0% {
            margin-top: 1.625em;
            transform: scale(0.4);
            opacity: 0
        }

        50% {
            margin-top: 1.625em;
            transform: scale(0.4);
            opacity: 0
        }

        80% {
            margin-top: -0.375em;
            transform: scale(1.15)
        }

        100% {
            margin-top: 0;
            transform: scale(1);
            opacity: 1
        }
    }

    @keyframes swal2-animate-error-icon {
        0% {
            transform: rotateX(100deg);
            opacity: 0
        }

        100% {
            transform: rotateX(0deg);
            opacity: 1
        }
    }

    @keyframes swal2-rotate-loading {
        0% {
            transform: rotate(0deg)
        }

        100% {
            transform: rotate(360deg)
        }
    }

    @keyframes swal2-animate-question-mark {
        0% {
            transform: rotateY(-360deg)
        }

        100% {
            transform: rotateY(0)
        }
    }

    @keyframes swal2-animate-i-mark {
        0% {
            transform: rotateZ(45deg);
            opacity: 0
        }

        25% {
            transform: rotateZ(-25deg);
            opacity: .4
        }

        50% {
            transform: rotateZ(15deg);
            opacity: .8
        }

        75% {
            transform: rotateZ(-5deg);
            opacity: 1
        }

        100% {
            transform: rotateX(0);
            opacity: 1
        }
    }

    @keyframes swal2-toast-show {
        0% {
            transform: translateY(-0.625em) rotateZ(2deg)
        }

        33% {
            transform: translateY(0) rotateZ(-2deg)
        }

        66% {
            transform: translateY(0.3125em) rotateZ(2deg)
        }

        100% {
            transform: translateY(0) rotateZ(0deg)
        }
    }

    @keyframes swal2-toast-hide {
        100% {
            transform: rotateZ(1deg);
            opacity: 0
        }
    }

    @keyframes swal2-toast-animate-success-line-tip {
        0% {
            top: .5625em;
            left: .0625em;
            width: 0
        }

        54% {
            top: .125em;
            left: .125em;
            width: 0
        }

        70% {
            top: .625em;
            left: -0.25em;
            width: 1.625em
        }

        84% {
            top: 1.0625em;
            left: .75em;
            width: .5em
        }

        100% {
            top: 1.125em;
            left: .1875em;
            width: .75em
        }
    }

    @keyframes swal2-toast-animate-success-line-long {
        0% {
            top: 1.625em;
            right: 1.375em;
            width: 0
        }

        65% {
            top: 1.25em;
            right: .9375em;
            width: 0
        }

        84% {
            top: .9375em;
            right: 0;
            width: 1.125em
        }

        100% {
            top: .9375em;
            right: .1875em;
            width: 1.375em
        }
    }
</style>
<style>
    .bg-light[_ngcontent-xwn-c57] {
        background-color: #d7d7d7
    }

    .nav-link[_ngcontent-xwn-c57] {
        outline: hidden
    }

    #divHijo[_ngcontent-xwn-c57] {
        margin: 0 auto
    }
</style>
<style>
    #accordionFlushExample[_ngcontent-xwn-c56] {
        font-size: small
    }
</style>
<style>
    .sombra[_ngcontent-xwn-c78]:hover {
        box-shadow: 10px 4px 20px var(--accordion-color-ligth)
    }
</style>
<meta name=referrer content=no-referrer>
<link rel=icon type=image/x-icon
    href="data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAD////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////+///////////////////////////////////c4uj/6+vr/+3u7v/t7e3/7O3t/+7u7v/4+Pj///79/+jk5P/L2Oj/8fX5///////+/v////////n5/P/p5un/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA==">
<style>
    .sf-hidden {
        display: none !important
    }
</style>
<link rel=canonical href=https://dgpatrimonios.seniat.gob.ve/sucesion/resumen/calculo/manual>
<meta http-equiv=content-security-policy
    content="default-src 'none'; font-src 'self' data:; img-src 'self' data:; style-src 'unsafe-inline'; media-src 'self' data:; script-src 'unsafe-inline' data:; object-src 'self' data:; frame-src 'self' data:;">
</head>

<body>
    <app-root _nghost-xwn-c36 ng-version=12.2.17><router-outlet _ngcontent-xwn-c36></router-outlet><app-inicio
            _nghost-xwn-c57>
            <div _ngcontent-xwn-c57 class=container>
                <div _ngcontent-xwn-c57 class="row align-items-center"><app-headersuc _ngcontent-xwn-c57 style=padding:0
                        _nghost-xwn-c54><img _ngcontent-xwn-c54 id=banner
                            src="data:image/png;base64,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+dM/66r2z5oz5447l+y7317PrV73wH2PT57SNXde/8EHL960ceS+B+8/7PBDH7j3ntHR8b332W23XXe/6oqbN23edNDi2W94/Wm3337PvXevnDw0w6bDU6b33nX3w+9999t/cdFld9zxwH77LhobG5k3b/7tt90xY+asQxcvvvPO2499yfGrVz1lHR56aPXpp516xRVXn3TKIQvmT31m+dZLfn1l0TJHHX3U3fdd39PT88gDT8yeuXPRpqeXP3T2W0+59/7Vv/7NlWeeffr1t/522RNP7rf3fue8+42f+OSXPvSP7773nqXX3HD7IYcfsv/iRd86/4cDfVNOOenAow9bdMFFl/3m4jvPessZmzat2rBmw/wFO53y2sM1pc98+oIzTn1VY936H1/4g9NeeXKpUotiVHT9+q2TJtWuv+qPr33Nmc88/aQY6uqpXX3tHdNmTB+cPPTko48NZaXxln/oieeOOOqAt7/1lRf8+Jd77jH1kMWLP/uZn1R7S0rFkYcddcvNd27YLN/6txu7Bl01LWWqu++5aPbM0le/etHkqUNzZs+54Ce/mDJl6oIF8401M2fOGhneNnvOzO9973sf/tAHL7zgskMWH5T7+n33Pr5mzeZTTzv2oaX3Xn3Vnz557qfm77Tg3/7tp+97/6tLpdIXPvezo486bMmSGz7wwff98Q+3X3vtDfvud0izkd988y1nnfWWBx54aGCgu9EYHx8b23//fdioqqxbv2Ldpi3jDdlzzwOKnI5/2T5PL7v3nz/1b+9//0fvuP3Gt7/lxAcfXNZdm3rySfNdwhOK0QlMYAITmMCLXhSC4g6lFUGcwrCxEiPgFSKSW2tVQMhJkWV9AN1215Nf+sIP7rnnKTYl327Omzf4zne+/c2vOwlAu5k7GGVyaXb3PctvvPkuZorRT5866UPve1tUmEBEBEmAUtSopplwQm0BBSRUMj13PfDkF752oUkHCMno5tV7LZr8lc++d0p/BuDyq+7603U3DU6eUx/fus9eu7/2taemBvVW2zFDWRXGkEgUFUOJAat1GSe/vfKWC39yOWLFoavVGttnr50+88/vKiXabBVGiU3W8d0jQlClF6NT+18rCI2qIgRuJUn3ug3tH/z4F6s3rk9KmUsSVV694rljjjx41bNPZ6g1x1vcZUbHG6XS4J9uPm+nnSc/8/SzM6bMczYMDlY+8e43X/Tb6x596nEyrVWrN/b0TD7rjWf/5uJLuNk677wPbdo8/OnzPl9He+acBXvtefCNV99SbGvtNHfa5q3r3/QPr+KUvv+DHxe5TJ28W7XStWz50739PYVsPerwlyy56Z5tw1v7+gZGxrZOmtR/5DEvvfbGa/Ntzf322OfGR27cZc9dVj2z6s2vfdsPv//r3pL74qffsmrNts9/56L6+Ibps3qjlNZvqI9v2/S+d5x9xNF7iYdonUkNc/CeKYEa3U7gRFBU+ht3Uh07EGHNNFhl5hTKNgLEgRQkpIo0K+2928L5A84r8qikqUOlTYWmeepHHGJXd6kRpG1EmQmGGJ4RxGQhA2AUcNG7PFFhlZIMcMzaSTtWsi9/52fdk2uvP3YxAEo8m9wGUhSUtGMzSqxYSkjBJtUYNdGRvP6N71yTZHOCjBn2hcRSZrLMNsZz0hJTdaxdvvm2m+bP6KlRb8Jpx0tTbRSOgYwLlESwa0scE+qaNG3nS696qH/wp1/+9JtSmzA1TQwlsuyVVCwR+6w1Vj9k790+/ZGPOUyySVOpriYoKSTrqk569tl11153a0MZiJAtKXc737dxwzML5/cunD1NosAmhUZQIpQ2PL77vZ8peg2ZGKHRd5VzQ915M4UWYGUuXXf90oULJ3eVy7FwpEkr6pL77n9y2aapU2a3QzBGyOQjm8eOXHzEW99wTN8gPApjYqvFGrOnntq0ctVzq1Y8s3D+1IrBuM/JRh8sONs+08ptQrBSwfY5QP1rvN1fJZ3lL3/cERH/BwUpBXSY5L9p6akiWu0OObg0ptw01hVF1+hoad264uTZ88rV6b/8xT177nEkQ9/97s9/6tyPzZy+/9e/ctk73/ey15xxwM9/edcNNz2zdZTf/b43f/q88177+tMauXz3u3+sZDPe+Z6TW7514QU3VMu7bN0mn/zkh9K09rnPffuuu6/58pc/fccdS6+8+p4rr7zhwYe2LT5kX+/jb397b1fXvCgJ25QIRW7Xri62bGjffP0P5yyMnzvvIyFPD1183M03LBVaZkr24SefPPzI0yb37rr8gd+zGcqbXfffuX7d8uLnF986afLgoYe95Jmnn7nmijvOfsupivxHP/zt0YctGms0z3nXmU89vvkPlz289/4z9zto7660/PSTz9135eNdzVuOP/mwl5/8ytS4i3929fBIaXR089y5cxctWjQ8OuWLX7lyrL71+Jce5fMycju9f8qpxx9x0XB95tzpvm1uvOEnaJUffXDk0t/eWaueds01P9933z3f+pZDPvGJi/946W3dPZXzPv36M1/zgSMOP+b3v//BK05/yeJD992wvnHNNXfvttuuK5aPn3DCax96cOkvfn7VCccfv9PcSevWrl/x7KPPPLX1nrufW7Jk5RVX3n38yw7atjk2hvunD81eWX1868ZRE5IrfnPPVb97+NRTD913//kH7PvSL33+2+//wIl9vTjogEWPPvrMUUcdtPq5DT/+yeUjo7TqufGRMZ42deq2rfUVz45OmjLkfaO3u3vGtJnr14wcud9hRx357v6u2pxdK+Nbx0c3ttauHHtuZXzswS2PL73riMM+PjQlQ2xP7EQmMIEJTGACLxr+vJ02O3rkRlWUArECRUQ0zoSopGRMkiTVsTZ+8KNf/+sPfj02EmBNuWze8upXfPgDr+zt7YdvF6FtqRyDzU3hrP3ejy9vNMpsWjYp3vCGl+w6r9xuigUxqhFGvCHXjrZFeSwpFwo22UNrNrzv3G9uHHOlxOWjW+dM7fraZ9++18KZQeOW4falVz8x3o7Vvnq9NfKhD31han+l1Wo5CqRETCGyaiKCxKUSC0O5S7puWfroeV/43uiIH+ibOrZtZMEuA//y9fdPHSq18zqzJUk1WkJgKkSI6H+FqUyoQMWlynD1sdErfn/fqjUjg1P61297ziRdo5tG237KssefGJrUu/LZZx3XrLPf+Nbrzj77rUtunVQUebmybmxk44H77/7Rd+POex+85k8P1Mf9K8887lWvOe4HP7vsJxf+KkPrwOOPnT1z4CeX3FXun1QqbYsX3bVol92n9c047zu/KZrDux91YK5jP7jk6qOOeeXll9zearb7hvrWrL4NZvz6+9dt2dAYHakP9PWvXfXMLnvvfs+K9nU3XI+t4yeeEG595MHmZXfYyLmf/OOLf+sQdt1nYO7Cg372uzv23XvB4yueffTuh1Htw+hzrzrxmJT2alOnVBOBEtMLNvayPd75bxX5drhv9a7pE6/qyugtGJEIMSNmo5TneX+3eccbT3v5UXO9gjojmopIUIaN2zsmthGzyIUSrAJBqRUsezcOQES06ORrEBPDxBhb0FBypWaj+Y2v/HCPuVP3mDdHgChEFIk601EtUCEU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"
                            width=100%></app-headersuc></div>
                <div _ngcontent-xwn-c57 class="row align-items-center" style=color:#fff;background-color:#d7d7d7>
                    <div _ngcontent-xwn-c57 class="bg-light clearfix">
                        <div _ngcontent-xwn-c57 class=float-start><span _ngcontent-xwn-c57 style=color:black>RAMON
                                ERNESTO BAUZA MARIN</span></div>
                        <div _ngcontent-xwn-c57 class=float-end><a _ngcontent-xwn-c57 data-bs-toggle=dropdown
                                href=https://dgpatrimonios.seniat.gob.ve/# role=button aria-expanded=false
                                class="nav-link dropdown-toggle link-secondary"><i _ngcontent-xwn-c57
                                    class="bi bi-list"></i></a>
                            <ul _ngcontent-xwn-c57 class="dropdown-menu sf-hidden"></ul>
                        </div>
                    </div>
                </div>
                <div _ngcontent-xwn-c57 class="row bg-color">
                    <div _ngcontent-xwn-c57 class=col-sm-12 style=text-align:center;color:white><span _ngcontent-xwn-c57
                            style=width:100vh>Autoliquidación de Impuesto sobre Sucesiones</span></div>
                </div>
                <div _ngcontent-xwn-c57 class=row>
                    <div _ngcontent-xwn-c57 class="col-sm-2 px-sm-2" style=background-color:#c1bdbb><app-menusuc
                            _ngcontent-xwn-c57 _nghost-xwn-c56>
                            <div _ngcontent-xwn-c56 id=wrapper class=d-flex>
                                <div _ngcontent-xwn-c56 id=sidebar-wrapper class="bg-light border-right show">
                                    <div _ngcontent-xwn-c56 class=sidebar-heading>
                                        <div _ngcontent-xwn-c56 style=text-align:center><span _ngcontent-xwn-c56
                                                style=font-size:1em;align-items:center><a _ngcontent-xwn-c56
                                                    ngbtooltip="Regresar Inicio" style=cursor:pointer><i
                                                        _ngcontent-xwn-c56 class="bi bi-arrow-left"></i>&nbsp;
                                                    Inicio</a></span></div>
                                    </div>
                                    <div _ngcontent-xwn-c56>
                                        <div _ngcontent-xwn-c56 id=accordionFlushExample
                                            class="accordion accordion-flush">
                                            <div _ngcontent-xwn-c56 class="accordion-item lenletratablaResumen">
                                                <h2 _ngcontent-xwn-c56 id=flush-headingTipoHerencia
                                                    class=accordion-header><button _ngcontent-xwn-c56 type=button
                                                        data-bs-toggle=collapse
                                                        data-bs-target=#flush-collapseTipoHerencia aria-expanded=false
                                                        aria-controls=flush-collapseTipoHerencia
                                                        class="accordion-button collapsed"> Herencia </button></h2>
                                                <div _ngcontent-xwn-c56 id=flush-collapseTipoHerencia
                                                    aria-labelledby=flush-headingTipoHerencia
                                                    data-bs-parent=#accordionFlushExample
                                                    class="accordion-collapse collapse sf-hidden"></div>
                                            </div>
                                            <div _ngcontent-xwn-c56 class=accordion-item>
                                                <h2 _ngcontent-xwn-c56 id=flush-headingProrrogas class=accordion-header>
                                                    <button _ngcontent-xwn-c56 type=button data-bs-toggle=collapse
                                                        data-bs-target=#flush-collapseProrrogas aria-expanded=false
                                                        aria-controls=flush-collapseProrrogas
                                                        class="accordion-button collapsed"> Prórrogas </button></h2>
                                                <div _ngcontent-xwn-c56 id=flush-collapseProrrogas
                                                    aria-labelledby=flush-headingProrrogas
                                                    data-bs-parent=#accordionFlushExample
                                                    class="accordion-collapse collapse sf-hidden"></div>
                                            </div>
                                            <div _ngcontent-xwn-c56 class=accordion-item>
                                                <h2 _ngcontent-xwn-c56 id=flush-headingOne class=accordion-header>
                                                    <button _ngcontent-xwn-c56 type=button data-bs-toggle=collapse
                                                        data-bs-target=#flush-collapseOne aria-expanded=false
                                                        aria-controls=flush-collapseOne
                                                        class="accordion-button collapsed"> Identificación Herederos
                                                    </button></h2>
                                                <div _ngcontent-xwn-c56 id=flush-collapseOne
                                                    aria-labelledby=flush-headingOne
                                                    data-bs-parent=#accordionFlushExample
                                                    class="accordion-collapse collapse sf-hidden"></div>
                                            </div>
                                            <div _ngcontent-xwn-c56 class=accordion-item>
                                                <h2 _ngcontent-xwn-c56 id=flush-headingInmuebles class=accordion-header>
                                                    <button _ngcontent-xwn-c56 type=button data-bs-toggle=collapse
                                                        data-bs-target=#flush-collapseInmuebles aria-expanded=false
                                                        aria-controls=flush-collapseInmuebles
                                                        class="accordion-button collapsed"> Bienes Inmuebles </button>
                                                </h2>
                                                <div _ngcontent-xwn-c56 id=flush-collapseInmuebles
                                                    aria-labelledby=flush-headingInmuebles
                                                    data-bs-parent=#accordionFlushExample
                                                    class="accordion-collapse collapse sf-hidden"></div>
                                            </div>
                                            <div _ngcontent-xwn-c56 class=accordion-item>
                                                <h2 _ngcontent-xwn-c56 id=flush-headingTwo class=accordion-header>
                                                    <button _ngcontent-xwn-c56 type=button data-bs-toggle=collapse
                                                        data-bs-target=#flush-collapseTwo aria-expanded=false
                                                        aria-controls=flush-collapseTwo
                                                        class="accordion-button collapsed"> Bienes Muebles </button>
                                                </h2>
                                                <div _ngcontent-xwn-c56 id=flush-collapseTwo
                                                    aria-labelledby=flush-headingTwo
                                                    data-bs-parent=#accordionFlushExample
                                                    class="accordion-collapse collapse sf-hidden"></div>
                                            </div>
                                            <div _ngcontent-xwn-c56 class=accordion-item>
                                                <h2 _ngcontent-xwn-c56 id=flush-headingFor class=accordion-header>
                                                    <button _ngcontent-xwn-c56 type=button data-bs-toggle=collapse
                                                        data-bs-target=#flush-collapseFor aria-expanded=false
                                                        aria-controls=flush-collapseFor
                                                        class="accordion-button collapsed"> Pasivos Deuda </button></h2>
                                                <div _ngcontent-xwn-c56 id=flush-collapseFor
                                                    aria-labelledby=flush-headingFor
                                                    data-bs-parent=#accordionFlushExample
                                                    class="accordion-collapse collapse sf-hidden"></div>
                                            </div>
                                            <div _ngcontent-xwn-c56 class=accordion-item>
                                                <h2 _ngcontent-xwn-c56 id=flush-headingPasivosGastos
                                                    class=accordion-header><button _ngcontent-xwn-c56 type=button
                                                        data-bs-toggle=collapse
                                                        data-bs-target=#flush-collapsePasivosGastos aria-expanded=false
                                                        aria-controls=flush-collapsePasivosGastos
                                                        class="accordion-button collapsed"> Pasivos Gastos </button>
                                                </h2>
                                                <div _ngcontent-xwn-c56 id=flush-collapsePasivosGastos
                                                    aria-labelledby=flush-headingPasivosGastos
                                                    data-bs-parent=#accordionFlushExample
                                                    class="accordion-collapse collapse sf-hidden"></div>
                                            </div>
                                            <div _ngcontent-xwn-c56 class=accordion-item>
                                                <h2 _ngcontent-xwn-c56 id=flush-headingDesgravamenes
                                                    class=accordion-header><button _ngcontent-xwn-c56 type=button
                                                        data-bs-toggle=collapse
                                                        data-bs-target=#flush-collapseDesgravamenes aria-expanded=false
                                                        aria-controls=flush-collapseDesgravamenes
                                                        class="accordion-button collapsed"> Desgravámenes </button></h2>
                                                <div _ngcontent-xwn-c56 id=flush-collapseDesgravamenes
                                                    aria-labelledby=flush-headingDesgravamenes
                                                    data-bs-parent=#accordionFlushExample
                                                    class="accordion-collapse collapse sf-hidden"></div>
                                            </div>
                                            <div _ngcontent-xwn-c56 class=accordion-item>
                                                <h2 _ngcontent-xwn-c56 id=flush-headingExenciones
                                                    class=accordion-header><button _ngcontent-xwn-c56 type=button
                                                        data-bs-toggle=collapse data-bs-target=#flush-collapseExenciones
                                                        aria-expanded=false aria-controls=flush-collapseExenciones
                                                        class="accordion-button collapsed"> Exenciones </button></h2>
                                                <div _ngcontent-xwn-c56 id=flush-collapseExenciones
                                                    aria-labelledby=flush-headingExenciones
                                                    data-bs-parent=#accordionFlushExample
                                                    class="accordion-collapse collapse sf-hidden"></div>
                                            </div>
                                            <div _ngcontent-xwn-c56 class=accordion-item>
                                                <h2 _ngcontent-xwn-c56 id=flush-headingExoneraciones
                                                    class=accordion-header><button _ngcontent-xwn-c56 type=button
                                                        data-bs-toggle=collapse
                                                        data-bs-target=#flush-collapseExoneraciones aria-expanded=false
                                                        aria-controls=flush-collapseExoneraciones
                                                        class="accordion-button collapsed"> Exoneraciones </button></h2>
                                                <div _ngcontent-xwn-c56 id=flush-collapseExoneraciones
                                                    aria-labelledby=flush-headingExoneraciones
                                                    data-bs-parent=#accordionFlushExample
                                                    class="accordion-collapse collapse sf-hidden"></div>
                                            </div>
                                            <div _ngcontent-xwn-c56 class=accordion-item>
                                                <h2 _ngcontent-xwn-c56 id=flush-headingBienesLitigiosos
                                                    class=accordion-header><button _ngcontent-xwn-c56 type=button
                                                        data-bs-toggle=collapse
                                                        data-bs-target=#flush-collapseBienesLitigiosos
                                                        aria-expanded=false aria-controls=flush-collapseBienesLitigiosos
                                                        class="accordion-button collapsed"> Bienes Litigiosos </button>
                                                </h2>
                                                <div _ngcontent-xwn-c56 id=flush-collapseBienesLitigiosos
                                                    aria-labelledby=flush-headingBienesLitigiosos
                                                    data-bs-parent=#accordionFlushExample
                                                    class="accordion-collapse collapse sf-hidden"></div>
                                            </div>
                                            <div _ngcontent-xwn-c56 class=accordion-item>
                                                <h2 _ngcontent-xwn-c56 id=flush-headingResumenDeclaracion
                                                    class=accordion-header><button _ngcontent-xwn-c56 type=button
                                                        data-bs-toggle=collapse
                                                        data-bs-target=#flush-collapseResumenDeclaracion
                                                        aria-expanded=true
                                                        aria-controls=flush-collapseResumenDeclaracion
                                                        class=accordion-button> Resumen Declaración </button></h2>
                                                <div _ngcontent-xwn-c56 id=flush-collapseResumenDeclaracion
                                                    aria-labelledby=flush-headingResumenDeclaracion
                                                    data-bs-parent=#accordionFlushExample
                                                    class="accordion-collapse collapse show">
                                                    <ul _ngcontent-xwn-c56 class=list-group>
                                                        <li _ngcontent-xwn-c56 class=list-group-item><a
                                                                _ngcontent-xwn-c56 class=link-secondary
                                                                style=cursor:pointer>Resumen Declaración</a>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div _ngcontent-xwn-c56 class=accordion-item>
                                                <h2 _ngcontent-xwn-c56 id=flush-headingVerDeclaracion
                                                    class=accordion-header><button _ngcontent-xwn-c56 type=button
                                                        data-bs-toggle=collapse
                                                        data-bs-target=#flush-collapseVerDeclaracion aria-expanded=false
                                                        aria-controls=flush-collapseVerDeclaracion
                                                        class="accordion-button collapsed"> Ver Declaración </button>
                                                </h2>
                                                <div _ngcontent-xwn-c56 id=flush-collapseVerDeclaracion
                                                    aria-labelledby=flush-headingVerDeclaracion
                                                    data-bs-parent=#accordionFlushExample
                                                    class="accordion-collapse collapse sf-hidden"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </app-menusuc></div>
                    <div _ngcontent-xwn-c57 id=divHijo class=col-sm-10><app-contentsuc _ngcontent-xwn-c57
                            _nghost-xwn-c55>
                            <div _ngcontent-xwn-c55 class=row>
                                <div _ngcontent-xwn-c55 class=col-sm-12><router-outlet
                                        _ngcontent-xwn-c55></router-outlet><app-calculo _nghost-xwn-c63>
                                        <div _ngcontent-xwn-c63 class=lenletrabreadcrumb>
                                            <nav _ngcontent-xwn-c63 aria-label=breadcrumb>
                                                <ol _ngcontent-xwn-c63 class=breadcrumb>
                                                    <li _ngcontent-xwn-c63 class=breadcrumb-item><a _ngcontent-xwn-c63
                                                            href=https://dgpatrimonios.seniat.gob.ve/sucesion/principal>Inicio</a>
                                                    <li _ngcontent-xwn-c63 aria-current=page
                                                        class="breadcrumb-item active"><strong
                                                            _ngcontent-xwn-c63>Resumen Declaración</strong>
                                                </ol>
                                            </nav>
                                        </div>
                                        <div _ngcontent-xwn-c63 class="shadow-lg p-3 mb-5 bg-body rounded">
                                            <div _ngcontent-xwn-c63 class=card>
                                                <div _ngcontent-xwn-c63 class=card-header>
                                                    <div _ngcontent-xwn-c63 class="bg-light clearfix">
                                                        <div _ngcontent-xwn-c63 class=float-start><span
                                                                _ngcontent-xwn-c63><strong _ngcontent-xwn-c63>Cálculo
                                                                    Manual Cuota Parte Hereditaria</strong></span></div>
                                                        <div _ngcontent-xwn-c63 class=float-end><a _ngcontent-xwn-c63
                                                                placement=top ngbtooltip=Regresar
                                                                class="btn btn-light"><i _ngcontent-xwn-c63
                                                                    class="bi bi-arrow-clockwise"></i></a></div>
                                                    </div>
                                                </div>
                                                <div _ngcontent-xwn-c63 class=card-body>
                                                    <div _ngcontent-xwn-c63>
                                                        <form _ngcontent-xwn-c63 novalidate
                                                            class="ng-untouched ng-pristine ng-valid">
                                                            <div _ngcontent-xwn-c63 class="row py-3">
                                                                <div _ngcontent-xwn-c63 class=col-sm-6>
                                                                    <div _ngcontent-xwn-c63 class=form-group>
                                                                        <div _ngcontent-xwn-c63 class=form-floating>
                                                                            <input _ngcontent-xwn-c63 id=ut
                                                                                placeholder=# type=text readonly
                                                                                class="form-control form-control-sm"
                                                                                value=0,4000000000><label
                                                                                _ngcontent-xwn-c63 for=ut>Unidad
                                                                                Tributaria Aplicada para cálculo</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div _ngcontent-xwn-c63 class=col-sm-6>
                                                                    <div _ngcontent-xwn-c63 class=form-group>
                                                                        <div _ngcontent-xwn-c63 class=form-floating>
                                                                            <input _ngcontent-xwn-c63 id=ip type=text
                                                                                placeholder=# currencymask readonly
                                                                                class="form-control form-control-sm"
                                                                                style=text-align:right
                                                                                value=" 11333,18"><label
                                                                                _ngcontent-xwn-c63 for=ip>Total Impuesto
                                                                                a Pagar</label></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <table _ngcontent-xwn-c63 colspan=11
                                                                class="table table-bordered table-sm lenletratablaResumen">
                                                                <thead _ngcontent-xwn-c63 class=table-light>
                                                                    <tr _ngcontent-xwn-c63>
                                                                        <th _ngcontent-xwn-c63>Apellido(s) y Nombre(s)
                                                                        <th _ngcontent-xwn-c63>C.I./Pasaporte
                                                                        <th _ngcontent-xwn-c63>Parentesco
                                                                        <th _ngcontent-xwn-c63>Grado
                                                                        <th _ngcontent-xwn-c63>Premuerto
                                                                        <th _ngcontent-xwn-c63>Cuota Parte
                                                                            Hereditaria(UT)
                                                                        <th _ngcontent-xwn-c63>Reducción (Bs.)
                                                                <tbody _ngcontent-xwn-c63 formarrayname=items
                                                                    class="ng-untouched ng-pristine ng-valid">
                                                                    <tr _ngcontent-xwn-c63
                                                                        class="ng-untouched ng-pristine ng-valid">
                                                                        <td _ngcontent-xwn-c63
                                                                            style=text-align:left!important>BAUZA
                                                                            PEDRONI RAMON ERNESTO
                                                                        <td _ngcontent-xwn-c63 style=text-align:center>
                                                                            V213264954
                                                                        <td _ngcontent-xwn-c63 style=text-align:center>
                                                                            OTRO PARIENTE
                                                                        <td _ngcontent-xwn-c63 style=text-align:center>4
                                                                        <td _ngcontent-xwn-c63 style=text-align:center>
                                                                            NO
                                                                        <td _ngcontent-xwn-c63><input _ngcontent-xwn-c63
                                                                                formcontrolname=valor currencymask
                                                                                class="form-group form-control form-control-sm text-end ng-untouched ng-pristine ng-valid"
                                                                                id=itemv-0 style=text-align:right
                                                                                value=18.641,67>
                                                                        <td _ngcontent-xwn-c63><input _ngcontent-xwn-c63
                                                                                formcontrolname=deducion currencymask
                                                                                class="form-group form-control form-control-sm text-end ng-untouched ng-pristine ng-valid"
                                                                                id=itemd-0 style=text-align:right
                                                                                value=0,00>
                                                                    <tr _ngcontent-xwn-c63
                                                                        class="ng-untouched ng-pristine ng-valid">
                                                                        <td _ngcontent-xwn-c63
                                                                            style=text-align:left!important>BAUZA
                                                                            PEDRONI ANDRES ALEJANDRO
                                                                        <td _ngcontent-xwn-c63 style=text-align:center>
                                                                            V213264962
                                                                        <td _ngcontent-xwn-c63 style=text-align:center>
                                                                            TIA/TIO
                                                                        <td _ngcontent-xwn-c63 style=text-align:center>3
                                                                        <td _ngcontent-xwn-c63 style=text-align:center>
                                                                            NO
                                                                        <td _ngcontent-xwn-c63><input _ngcontent-xwn-c63
                                                                                formcontrolname=valor currencymask
                                                                                class="form-group form-control form-control-sm text-end ng-untouched ng-pristine ng-valid"
                                                                                id=itemv-1 style=text-align:right
                                                                                value=18.641,67>
                                                                        <td _ngcontent-xwn-c63><input _ngcontent-xwn-c63
                                                                                formcontrolname=deducion currencymask
                                                                                class="form-group form-control form-control-sm text-end ng-untouched ng-pristine ng-valid"
                                                                                id=itemd-1 style=text-align:right
                                                                                value=0,00>
                                                                    <tr _ngcontent-xwn-c63
                                                                        class="ng-untouched ng-pristine ng-valid">
                                                                        <td _ngcontent-xwn-c63
                                                                            style=text-align:left!important>PEDRONI
                                                                            LEPERVANCHE PAOLA MARIA
                                                                        <td _ngcontent-xwn-c63 style=text-align:center>
                                                                            V069727138
                                                                        <td _ngcontent-xwn-c63 style=text-align:center>
                                                                            OTRO
                                                                        <td _ngcontent-xwn-c63 style=text-align:center>4
                                                                        <td _ngcontent-xwn-c63 style=text-align:center>
                                                                            NO
                                                                        <td _ngcontent-xwn-c63><input _ngcontent-xwn-c63
                                                                                formcontrolname=valor currencymask
                                                                                class="form-group form-control form-control-sm text-end ng-untouched ng-pristine ng-valid"
                                                                                id=itemv-2 style=text-align:right
                                                                                value=18.641,67>
                                                                        <td _ngcontent-xwn-c63><input _ngcontent-xwn-c63
                                                                                formcontrolname=deducion currencymask
                                                                                class="form-group form-control form-control-sm text-end ng-untouched ng-pristine ng-valid"
                                                                                id=itemd-2 style=text-align:right
                                                                                value=0,00>
                                                                <tfoot _ngcontent-xwn-c63>
                                                                    <tr _ngcontent-xwn-c63>
                                                                        <td _ngcontent-xwn-c63 colspan=7
                                                                            class=text-center><button _ngcontent-xwn-c63
                                                                                type=submit
                                                                                class="btn btn-sm btn-danger">Calcular</button>
                                                            </table>
                                                        </form>
                                                        <div _ngcontent-xwn-c63>
                                                            <div _ngcontent-xwn-c63 class=row>
                                                                <div _ngcontent-xwn-c63 class=col-sm-12>
                                                                    <table _ngcontent-xwn-c63
                                                                        class="table table-bordered table-sm lenletratablaResumen">
                                                                        <thead _ngcontent-xwn-c63 class=table-light>
                                                                            <tr _ngcontent-xwn-c63>
                                                                                <th _ngcontent-xwn-c63>Apellido(s) y
                                                                                    Nombre(s)
                                                                                <th _ngcontent-xwn-c63>C.I./Pasaporte
                                                                                <th _ngcontent-xwn-c63>Parentesco
                                                                                <th _ngcontent-xwn-c63>Grado
                                                                                <th _ngcontent-xwn-c63>Premuerto
                                                                                <th _ngcontent-xwn-c63>Cuota Parte
                                                                                    Hereditaria(UT)
                                                                                <th _ngcontent-xwn-c63>Porcentaje o
                                                                                    Tarifa (%)
                                                                                <th _ngcontent-xwn-c63>Sustraendo (UT)
                                                                                <th _ngcontent-xwn-c63>Impuesto
                                                                                    Determinado (Bs.)
                                                                                <th _ngcontent-xwn-c63>Reduccion (Bs.)
                                                                                <th _ngcontent-xwn-c63>Impuesto a Pagar
                                                                                    (Impuesto Determinado - Reduccion)
                                                                        <tbody _ngcontent-xwn-c63>
                                                                            <tr _ngcontent-xwn-c63>
                                                                                <td _ngcontent-xwn-c63
                                                                                    style=text-align:left!important>
                                                                                    BAUZA PEDRONI RAMON ERNESTO
                                                                                <td _ngcontent-xwn-c63
                                                                                    style=text-align:center>V213264954
                                                                                <td _ngcontent-xwn-c63
                                                                                    style=text-align:center>OTRO
                                                                                    PARIENTE
                                                                                <td _ngcontent-xwn-c63
                                                                                    style=text-align:center>4
                                                                                <td _ngcontent-xwn-c63
                                                                                    style=text-align:center>NO
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=18.641,67>
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=55,00>
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=498,25>
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=3.901,87>
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=0,00>
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=3.901,87>
                                                                            <tr _ngcontent-xwn-c63>
                                                                                <td _ngcontent-xwn-c63
                                                                                    style=text-align:left!important>
                                                                                    BAUZA PEDRONI ANDRES ALEJANDRO
                                                                                <td _ngcontent-xwn-c63
                                                                                    style=text-align:center>V213264962
                                                                                <td _ngcontent-xwn-c63
                                                                                    style=text-align:center>TIA/TIO
                                                                                <td _ngcontent-xwn-c63
                                                                                    style=text-align:center>3
                                                                                <td _ngcontent-xwn-c63
                                                                                    style=text-align:center>NO
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=18.641,67>
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=50,00>
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=497,23>
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=3.529,44>
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=0,00>
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=3.529,44>
                                                                            <tr _ngcontent-xwn-c63>
                                                                                <td _ngcontent-xwn-c63
                                                                                    style=text-align:left!important>
                                                                                    PEDRONI LEPERVANCHE PAOLA MARIA
                                                                                <td _ngcontent-xwn-c63
                                                                                    style=text-align:center>V069727138
                                                                                <td _ngcontent-xwn-c63
                                                                                    style=text-align:center>OTRO
                                                                                <td _ngcontent-xwn-c63
                                                                                    style=text-align:center>4
                                                                                <td _ngcontent-xwn-c63
                                                                                    style=text-align:center>NO
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=18.641,67>
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=55,00>
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=498,25>
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=3.901,87>
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=0,00>
                                                                                <td _ngcontent-xwn-c63><input
                                                                                        _ngcontent-xwn-c63 readonly
                                                                                        class="form-group form-control form-control-sm text-end"
                                                                                        value=3.901,87>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                            <div _ngcontent-xwn-c63 class=row>
                                                                <div _ngcontent-xwn-c63 class="col-sm-12 text-center"><a
                                                                        _ngcontent-xwn-c63
                                                                        class="btn btn-sm btn-danger">Aceptar</a>&nbsp;
                                                                    <a _ngcontent-xwn-c63
                                                                        class="btn btn-sm btn-danger">Cancelar</a></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </app-calculo></div>
                            </div>
                        </app-contentsuc></div>
                </div>
            </div>
        </app-inicio></app-root>