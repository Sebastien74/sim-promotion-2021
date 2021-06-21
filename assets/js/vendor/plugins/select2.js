import '../../../scss/vendor/components/_select2.scss';
import 'select2/dist/js/select2.full.min';

/**
 *  Selects2
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (selectId = null, containerId = null) {

    let language = $('html').attr('lang');

    $.fn.select2.amd.define('select2/i18n/' + language, [], require("select2/src/js/select2/i18n/" + language));

    /**
     *  Set by element ID
     */
    if (selectId) {

        let body = $('body');
        let selectById = body.find('#' + selectId);

        selectById.select2();

        if (containerId) {
            selectById.on('select2:open', function () {
                $('span.select2-container--open').attr('id', containerId);
            });
        }
    }

    /**
     *  To add custom class to dropdown
     */
    function dropdownClass(select) {
        let dropdownClass = select.data('dropdown-class') ? select.data('dropdown-class') : 'select2-dropdown-container';
        let dropdownBelow = $('body').find('.select2-dropdown--below');
        let dropdown = dropdownBelow.parent();
        if (!dropdown.hasClass(dropdownClass)) {
            dropdown.addClass(dropdownClass);
        }
    }

    /**
     *  Selects2 basic
     */
    let selects2Update = function () {

        let body = $('body');

        /** In visible DOM */
        let selects = body.find('.select-2');
        selects.each(function () {
            generateSerial($(this));
            let select = $(this);
            if (!select.hasClass('select2-active') && !select.hasClass('select-icons')) {
                let group = select.closest('.form-group');
                let allowClear = group.length > 0 && group.hasClass('allow-clear');
                select.select2({
                    allowClear: allowClear,
                    language: language,
                    minimumResultsForSearch: select.hasClass('disable-search') ? Infinity : false /** Hide search box */
                });
                select.on('select2:open', function (e) {
                    dropdownClass(select);
                });
                select.addClass('select2-active');
            }
        });

        /** In modal */
        let modals = body.find('.modal');
        modals.each(function () {
            let modal = $(this);
            let modalId = modal.attr('id');
            let selects = modal.find('.select-2');
            if (selects.length > 0) {
                let modalEl = $('#' + modalId);
                modalEl.on('show.bs.modal', function (e) {
                    selects.each(function () {
                        let select = $(this);
                        if (!select.hasClass('select2-active') && !select.hasClass('select-icons')) {
                            select.select2({
                                language: language,
                                dropdownParent: modalEl,
                                minimumResultsForSearch: select.hasClass('disable-search') ? Infinity : false /** Hide search box */
                            });
                            select.on('select2:open', function (e) {
                                dropdownClass(select);
                            });
                            select.addClass('select2-active');
                        }
                    });
                });
            }
        });
    };

    /**
     *  Selects2 icons
     */
    let selectsIconUpdate = function () {

        let body = $('body');

        /** In visible DOM */
        let selectsIcon = body.find('.select-icons');
        selectsIcon.each(function () {
            generateSerial($(this));
            let select = $(this);
            if (!select.hasClass('select2-active')) {
                select.select2({
                    language: language,
                    templateResult: iconFormat,
                    minimumResultsForSearch: select.hasClass('disable-search') ? Infinity : false,
                    /** Hide search box */
                    templateSelection: iconFormat,
                    escapeMarkup: function (m) {
                        return m;
                    }
                });
                select.on('select2:open', function (e) {
                    dropdownClass(select);
                });
                select.addClass('select2-active');
            }
        });

        /** In modal */
        let modals = body.find('.modal');
        modals.each(function () {

            let modal = $(this);
            let modalId = modal.attr('id');
            let select = modal.find('.select-icons');

            generateSerial(select);

            $('#' + modalId).on('shown.bs.modal', function (e) {
                if (!select.hasClass('select2-active')) {
                    $(select).select2({
                        language: language,
                        dropdownParent: $('#' + modalId),
                        templateResult: iconFormat,
                        minimumResultsForSearch: select.hasClass('disable-search') ? Infinity : false,
                        /** Hide search box */
                        templateSelection: iconFormat,
                        escapeMarkup: function (m) {
                            return m;
                        }
                    });
                    select.on('select2:open', function (e) {
                        dropdownClass(select);
                    });
                    select.addClass('select2-active');
                }
            });
        });

        /** Format icon */
        function iconFormat(icon) {

            if (!icon.id) {
                return icon.text;
            }

            let element = $(icon.element);

            if (typeof element.data('background') !== 'undefined') {
                return "<span class='select-2-background-wrap'><i class='select-2-background' style='background: url(" + element.data('background') + ");'></i></span>";
            } else if (typeof element.data('color') !== 'undefined') {
                let color = element.data('color');
                return "<span class='color-wrapper mr-2'><span class='color " + element.data('class') + "' style='background-color:" + color + "; border: 1px solid " + color + ";'></span></span>" + icon.text;
            } else if (typeof element.data('image') !== 'undefined' && typeof element.data('text') !== 'undefined') {
                let width = element.data('width') !== 'undefined' ? element.data('width') : 'auto';
                let height = element.data('height') !== 'undefined' ? element.data('height') : 'auto';
                let classname = element.data('class') !== 'undefined' ? element.data('class') : null;
                return "<img data-src='" + element.data('image') + "' class='img-fluid img-icon lazyload mr-2 " + classname + "' width='" + width + "' height='" + height + "'/>" + icon.text;
            } else if (typeof element.data('svg') !== 'undefined' && typeof element.data('text') !== 'undefined') {
                return element.data('svg') + icon.text;
            } else if (typeof element.data('image') !== 'undefined') {
                return "<img data-src='" + element.data('image') + "' class='img-fluid img-icon lazyload' />";
            } else {
                return "<i class='" + element.data('icon') + "'></i>" + icon.text;
            }
        }
    };

    function generateSerial(el) {

        let chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
        let stringLength = 10;
        let randomString = '';

        for (let x = 0; x < stringLength; x++) {

            let letterOrNumber = Math.floor(Math.random() * 2);

            if (letterOrNumber == 0) {
                let newNum = Math.floor(Math.random() * 9);
                randomString += newNum;
            } else {
                let rnum = Math.floor(Math.random() * chars.length);
                randomString += chars.substring(rnum, rnum + 1);
            }
        }

        let id = el.attr('id');
        if (typeof id == 'undefined' || id === false || id === "false" || !id) {
            el.attr('id', randomString);
            el.closest('.form-group').find('label').attr('for', randomString);
        }
    }

    selects2Update();
    selectsIconUpdate();
};