/**
 *  Send master form on Dropzone process
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    let masterForm = $('body').find('.master-dropzone-form');

    if (masterForm.length > 0 && !masterForm.hasClass('is-submit')) {

        let masterFormId = masterForm.attr('id');
        let formData = new FormData(document.getElementById(masterFormId));
        masterForm.addClass('is-submit');

        $.ajax({
            url: masterForm.attr('action') + "?ajax=true",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            async: true,
            beforeSend: function () {
            },
            success: function (response) {
            },
            error: function (error) {
                displayErrors(error);
            }
        });
    }

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