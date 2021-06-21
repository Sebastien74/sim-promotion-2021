/**
 *  Forms
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 *
 *  1 - Add ID to forms
 *  2 - Ajax Post
 *  3 - Prototype
 *  4 - Bootstrap Tags input
 *  5 - Color Picker
 *  6 - Assert
 *  7 - Dropzone
 *  8 - Dropify
 *  9 - Duplicate
 *  10 - Counter
 *  11 - Search in index
 *  12 - Entities status switcher
 *  13 - Loader on submit
 *  14 - Input label btn
 *  15 - Date Picker
 */

let body = $('body');

/** 1 - Add ID to forms */
let forms = body.find('form');
if(forms.length > 0) {
    forms.each(function () {
        let form = $(this);
        let id = form.attr('id');
        if(!id) {
            let uniqId = 'form-' + Math.floor(Math.random() * 10000);
            form.attr('id', uniqId);
        }
    });
}

/** 2 - Ajax Post */
import ajax from "./ajax";
ajax();

/** 3 - Prototype */
let prototypes = body.find('.add-collection');
if (prototypes.length > 0) {
    import('./prototype').then(({default: prototype}) => {
        new prototype();
    }).catch(error => 'An error occurred while loading the component "prototype"');
}

/** 4 - Bootstrap Tags input */
let tagsInput = $('[data-role="tagsinput"]');
if (tagsInput.length > 0) {
    import('./../lib/bootstrap-tagsinput.min').then(({default: tagsInputModule}) => {
        new tagsInputModule();
    }).catch(error => 'An error occurred while loading the component "bootstrap-tagsinput"');
}

/** 5 - Color Picker */
let colorPicker = body.find('.colorpicker');
if (colorPicker.length > 0) {
    import('./../plugins/colorpicker').then(({default: asColorPicker}) => {
        new asColorPicker();
    }).catch(error => 'An error occurred while loading the component "colorpicker"');
}

/** 6 - Assert */
let assertModals = body.find('.modal');
if (assertModals.length > 0) {
    import('./assert').then(({default: assertModal}) => {
        new assertModal();
    }).catch(error => 'An error occurred while loading the component "assert"');
}

/** 7 - Dropzone */
let dropzones = body.find('.js-reference-dropzone');
if (dropzones.length > 0) {
    import('./dropzone').then(({default: dropzone}) => {
        new dropzone();
    }).catch(error => 'An error occurred while loading the component "dropzone"');
}

/** 8 - Dropify */
let dropifies = body.find('.dropify');
if (dropifies.length > 0) {
    import('./dropify').then(({default: dropify}) => {
        new dropify();
    }).catch(error => 'An error occurred while loading the component "dropify"');
}

/** 9 - Duplicate */
let duplicates = body.find('.duplicate-btn');
if (duplicates.length > 0) {
    import('./duplicate').then(({default: duplicate}) => {
        new duplicate();
    }).catch(error => 'An error occurred while loading the component "duplicate"');
}

/** 10 - Counter */
let counters = body.find('.counter-form-group');
if (counters.length > 0) {
    import('./counter').then(({default: counter}) => {
        new counter();
    }).catch(error => 'An error occurred while loading the component "counter"');
}

/** 11 - Search in index */
let searchIndexEl = body.find('#index-search-submit');
if (searchIndexEl.length > 0) {
    import('./search-index').then(({default: searchIndex}) => {
        new searchIndex();
    }).catch(error => 'An error occurred while loading the component "search-index"');
}

/** 12 - Entities status switcher */
let switchers = body.find('.entity-switcher-status');
if (switchers.length > 0) {
    import('./entity-switcher').then(({default: switcher}) => {
        new switcher();
    }).catch(error => 'An error occurred while loading the component "entity-switcher"');
}

/** 13 - Loader on submit */
$("button[type='submit']").on('click', function (e) {
    let el = $(this);
    if (!el.hasClass('ajax-post') && !el.hasClass('disable-preloader')) {
        let stripePreloader = el.closest('.refer-preloader').find('.stripe-preloader');
        let loader = stripePreloader.length > 0 ? stripePreloader : $('body').find('.main-preloader');
        $(loader[0]).removeClass('d-none');
    }
});

/** 14 - Input label btn */
body.on('change', '.input-btn', function () {
    $('.input-btn').closest('label').removeClass('active');
    $(this).closest('label').addClass('active');
});

/** 15 - Date Picker */
if (body.find('.datepicker').length > 0) {
    import('./date-pickers').then(({default: datepickerPlugin}) => {
        new datepickerPlugin();
    }).catch(error => 'An error occurred while loading the component "datepickerPlugin"');
}