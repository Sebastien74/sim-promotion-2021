/**
 * Catalog
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */

import setPositions from "./positions";

import 'jquery-ui-bundle';
import '../../bootstrap/js/dist/tab';

import '../../../../scss/admin/pages/calatog.scss';
import '../../../../scss/admin/lib/sweetalert.scss';
import '../../lib/sweetalert/sweetalert.min';

$(function () {

    let body = $('body');
    let trans = body.find('#product-translations');
    let preloader = $("#product-preloader");

    body.on('click', '#save-product', function () {
        preloader.removeClass('d-none');
        $('#form-catalog-product').submit();
    });

    body.on('click', '#medias-path', function (e) {

        e.preventDefault();

        let path = $(this).attr('href');

        if ($('#product-edition').hasClass('is-product')) {

            return swal({
                title: trans.data('swal-product-title'),
                text: trans.data('swal-product-text'),
                type: "info",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: trans.data('swal-media-confirm-text'),
                cancelButtonText: trans.data('swal-product-cancel-text'),
                closeOnConfirm: false
            }, function () {
                document.location.href = path;
                preloader.removeClass('d-none');
            });
        }
    });

    body.on('click', '.swal-product-value', function (e) {

        e.preventDefault();

        let path = $(this).attr('href');

        return swal({
            title: trans.data('swal-product-title'),
            text: trans.data('swal-product-text'),
            type: "info",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: trans.data('swal-value-confirm-text'),
            cancelButtonText: trans.data('swal-product-cancel-text'),
            closeOnConfirm: false
        }, function () {
            document.location.href = path;
            preloader.removeClass('d-none');
        });
    });

    let featuresSortable = $('#features-sortable').sortable({
        placeholder: "ui-state-highlight",
        items: '.ui-feature',
        handle: ".handle-feature",
        start: function (e, ui) {
            ui.placeholder.height(ui.item.height());
        },
        update: function (event, ui) {

            let loader = body.find('.main-preloader');
            let sortables = $('body').find('.ui-feature');
            let length = sortables.length;

            loader.removeClass('d-none');

            sortables.each(function (i, el) {

                let newPosition = i + 1;
                let path = $(el).data('pos-path');

                $.ajax({
                    url: path + "?position=" + newPosition,
                    type: "GET",
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    async: false,
                    beforeSend: function () {
                    },
                    success: function (response) {
                        if ((i + 1) === length) {
                            loader.addClass('d-none');
                        }
                    },
                    error: function (errors) {
                        /** Display errors */
                        import('../../core/errors').then(({default: displayErrors}) => {
                            new displayErrors(errors);
                        }).catch(error => 'An error occurred while loading the component "errors"');
                    }
                });
            });
            event.stopImmediatePropagation();
        }
    });

    featuresSortable.disableSelection();

    let featureValuesSortable = $('.feature-values-sortable').sortable({
        placeholder: "ui-state-highlight",
        items: '.ui-value',
        handle: ".handle-value",
        start: function (e, ui) {
            ui.placeholder.height(ui.item.height());
        },
        update: function (event, ui) {
            let items = $('body').find('#features-sortable .ui-value');
            setPositions(items);
            event.stopImmediatePropagation();
        }
    });

    featureValuesSortable.disableSelection();

    let videoValuesSortable = $('#videos-sortable').sortable({
        placeholder: "ui-state-highlight",
        items: '.ui-video',
        handle: ".handle-video",
        start: function (e, ui) {
            ui.placeholder.height(ui.item.height());
        },
        update: function (event, ui) {
            let items = $('body').find('#videos-sortable .ui-video');
            setPositions(items);
            event.stopImmediatePropagation();
        }
    });

    videoValuesSortable.disableSelection();
});