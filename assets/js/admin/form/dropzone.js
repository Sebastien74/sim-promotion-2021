import 'dropzone/dist/dropzone.css';

import Dropzone from "dropzone";
import masterDropzoneForm from "../media/master-dropzone-form";

/**
 *  Dropzone
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    let trans = $('#data-translation');
    let referenceClass = '.js-reference-dropzone';
    let form = $('body').find(referenceClass);

    if (form.length === 0) {
        return;
    }

    Dropzone.autoDiscover = false;

    let field = form.find('.dropzone-field');

    let dropzone = new Dropzone(referenceClass, {
        url: form.attr('action') + "?ajax=1",
        paramName: field.attr('name'),
        maxFilesize: 100,
        acceptedFiles: field.attr('accept'),
        dictDefaultMessage: '<img src="/medias/icons/fontawesome/light/download.svg" class="img-fluid mb-4"><br>' + trans.data('dropzone-default-message'),
        dictFallbackMessage: trans.data('dropzone-fallback-message'),
        dictFallbackText: trans.data('dropzone-invalid-file-type'),
        dictFileTooBig: trans.data('dropzone-file-too-big'),
        dictInvalidFileType: trans.data('dropzone-invalid-file-type'),
        dictResponseError: trans.data('dropzone-response-error'),
        dictCancelUpload: trans.data('dropzone-cancel-upload'),
        dictCancelUploadConfirmation: trans.data('dropzone-cancel-upload-confirmation'),
        dictRemoveFile: trans.data('dropzone-remove-file'),
        dictMaxFilesExceeded: trans.data('dropzone-max-files-exceeded')
    });

    dropzone.on("sending", function (file, response) {
        masterDropzoneForm();
    });

    dropzone.on("success", function (file, response) {
        if (response.errors) {
            displayErrors(response.errors);
            $('body').attr('data-dropzone-success', false);
        }
    });

    dropzone.on("error", function (file, response) {
        displayErrors(response.errors);
    });

    dropzone.on("queuecomplete", function (file, response) {

        let body = $('body');
        let success = body.attr('data-dropzone-success');

        if (typeof success == "undefined") {
            body.find('.main-preloader').removeClass('d-none');
            window.location.href = window.location.href;
        }

        body.removeAttr('data-dropzone-success');
    });

    function displayErrors(errors) {

        let message = '<div class="internal-error-alert ribbon-vwrapper alert alert-danger">';
        message += '<div class="ribbon ribbon-bookmark ribbon-vertical-l ribbon-danger">';
        message += '<img src="/medias/icons/fontawesome/regular/exclamation-triangle.svg" class="img-fluid lazyload">';
        message += '</div>';
        message += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
        message += '<span aria-hidden="true"><img src="/medias/icons/fontawesome/regular/times.svg" class="img-fluid lazyload"></span>';
        message += '</button>';
        message += errors;
        message += '</div>';

        let dropzoneErrorsEl = $('#dropzone-errors');
        let errorsEl = dropzoneErrorsEl.length > 0 ? dropzoneErrorsEl : $('#admin-body');

        errorsEl.prepend(message);
    }
}