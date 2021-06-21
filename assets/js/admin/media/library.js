import displayAlert from "../core/alert";
import dropifyJS from "../form/dropify";
import resetModal from "../../vendor/components/reset-modal";
import route from "../../vendor/components/routing";
import select2 from "../../vendor/plugins/select2";

import '../lib/sweetalert/sweetalert.min';
import '../bootstrap/js/dist/modal';
import '../bootstrap/js/dist/tooltip';

import '../../../scss/admin/pages/library.scss';
import '../../../scss/admin/lib/sweetalert.scss';

let body = $('body');

let folderModal = $('#new-modal-folder');
folderModal.on('show.bs.modal', function (e) {
    folderModal.find('form')[0].reset();
    let select = folderModal.find('#folder_parent');
    select.find("option").removeAttr("selected");
    select.trigger("change");
});

body.on('click', '.open-media-edit', function (e) {

    e.preventDefault();

    let el = $(this);
    let loader = $('#medias-preloader');

    $.ajax({
        url: el.attr('href') + "?ajax=1",
        type: "GET",
        processData: false,
        contentType: false,
        dataType: 'json',
        beforeSend: function () {
            loader.removeClass('d-none');
            loader.parent().removeClass('d-none');
        },
        success: function (response) {

            if (response.html) {

                let body = $('body');
                body.append(response.html);

                let modal = body.find('#media-edition-modal');
                modal.modal('show');

                dropifyJS();

                /** Touch spin */
                import('../../vendor/plugins/touchspin').then(({default: touchSpin}) => {
                    new touchSpin();
                }).catch(error => 'An error occurred while loading the component "plugins/touchspin"');

                $('[data-toggle="tooltip"]').tooltip();

                modal.on('hidden.bs.modal', function (e) {
                    modal.remove();
                });
            }

            loader.addClass('d-none');
            loader.parent().addClass('d-none');
        },
        error: function (errors) {
            /** Display errors */
            import('../core/errors').then(({default: displayErrors}) => {
                new displayErrors(errors);
            }).catch(error => 'An error occurred while loading the component "errors"');
        }
    });

    e.stopImmediatePropagation();
    return false;
});

$('#search-medias-form').on('keyup keypress', function (e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13) {
        e.preventDefault();
        return false;
    }
});

/** Refresh medias on search */

body.on('keyup', '#search_searchMedia', function (e) {

    let el = $(this);
    let form = el.closest('form');
    let formData = new FormData(document.getElementById(form.attr('id')));
    let loader = $('#medias-card').find('#medias-preloader');

    $.ajax({
        url: form.attr('action'),
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        beforeSend: function () {
            loader.parent().removeClass('d-none');
            loader.removeClass('d-none');
        },
        success: function (response) {

            let html = $(response.html);
            let ajaxContent = $('body').find('#medias-results');
            html.find('.card-subtitle').remove();
            ajaxContent.replaceWith(html);

            let tooltips = $('[data-toggle="tooltip"]');
            tooltips.tooltip('hide');
            tooltips.tooltip();

            loader.parent().addClass('d-none');
            loader.addClass('d-none');
        },
        error: function (errors) {
            /** Display errors */
            import('../core/errors').then(({default: displayErrors}) => {
                new displayErrors(errors);
            }).catch(error => 'An error occurred while loading the component "errors"');
        }
    });

    e.stopImmediatePropagation();
    return false;
});

body.on('click', '.check-pack-media-label', function () {

    let el = $(this);
    let file = el.closest('.file');

    file.toggleClass('active');

    let body = $('body');
    let inputsChecked = body.find(".file.active");
    let brnWrapper = body.find("#media-management-buttons");

    if (inputsChecked.length > 0) {
        brnWrapper.removeClass('d-none').addClass('d-inline-block');
    } else {
        brnWrapper.addClass('d-none').removeClass('d-inline-block');
    }
});

/** Show move to folder modal */
body.on('click', '#select-folder-btn', function (e) {

    let btn = $(this);
    let loader = body.find('#medias-preloader');
    let path = btn.data('path');
    let url = path + '?ajax=true';
    if (path.indexOf('?') > -1) {
        url = path + '&ajax=true'
    }

    $.ajax({
        url: url,
        type: "GET",
        processData: false,
        contentType: false,
        dataType: 'json',
        beforeSend: function () {
            loader.toggleClass('d-none');
            loader.parent().toggleClass('d-none');
        },
        success: function (response) {

            let html = response.html;
            let modal = $(html).find('.modal');
            let container = $('body');

            container.append(response.html);

            let modalEl = container.find('#' + modal.attr('id'));

            modalEl.modal('show');
            loader.toggleClass('d-none');
            loader.parent().toggleClass('d-none');

            select2();

            modalEl.on("hide.bs.modal", function () {
                resetModal(modalEl, true);
                $('.modal-wrapper').remove();
            });
        },
        error: function (errors) {}
    });

    e.stopImmediatePropagation();
    return false;
});

/** To move media in folder */
body.on('click', '#select_folder_save', function (e) {

    e.preventDefault();

    let el = $(this);
    let select = el.closest('form').find('select');
    let folder = select.val();
    let modal = body.find('#select-folder');

    resetModal(modal, true);

    $('#media-management-buttons').addClass('d-none').removeClass('d-inline-block');

    body.find('.file.active').each(function () {
        let file = $(this);
        ajaxManagement(file, route('admin_folder_media_move', {
            "website": body.data('id'),
            "media": file.data('id'),
            "folderId": folder
        }));
    });
});

/** Warning Message delete */
body.on('click', '.sa-warning-delete-medias', function () {

    let trans = $('#data-translation');

    body.find('#media-management-buttons').addClass('d-none').removeClass('d-inline-block');

    swal({
        title: trans.data('swal-delete-title'),
        text: trans.data('swal-delete-text'),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: trans.data('swal-delete-confirm-text'),
        cancelButtonText: trans.data('swal-delete-cancel-text'),
        closeOnConfirm: false
    }, function () {

        $('.alert').remove();

        body.find('.sa-button-container .confirm').attr('disabled', '');
        body.find('.sa-button-container .cancel').attr('disabled', '');

        body.find('.file.active').each(function () {
            let file = $(this);
            ajaxManagement(file, route('admin_media_remove', {
                "website": body.data('id'),
                "media": file.data('id')
            }));
        });

        swal(trans.data('deletion-completed'), "", "success");

        setTimeout(function () {
            swal.close();
        }, 1500);
    });
});

let ajaxManagement = function (file, url) {

    $.ajax({
        url: url,
        type: "GET",
        processData: false,
        contentType: false,
        dataType: 'json',
        beforeSend: function () {
        },
        success: function (response) {
            if (response.success) {
                file.fadeOut(200).remove();
            } else {
                displayAlert(response.message, 'danger', null, false);
            }
            file.removeClass('active');
            file.find("input.check-pack-media").prop('checked', false);
        },
        error: function (errors) {
            /** Display errors */
            import('../core/errors').then(({default: displayErrors}) => {
                new displayErrors(errors);
            }).catch(error => 'An error occurred while loading the component "errors"');
        }
    });
};