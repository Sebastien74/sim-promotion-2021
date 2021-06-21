import '../bootstrap/js/dist/modal';

/**
 * To display Errors messages
 */
export default function (error = null, element = null) {

    if (!error) {
        return false;
    }

    let isDebug = $('html').data('debug');

    if (error.status !== 200 && isDebug) {

        let body = $('body');

        body.find('.alert').remove();
        $(".main-preloader").fadeOut();

        let trans = $('#data-translation');
        let text = error;
        let status = 500;
        let statusText = trans.data('internal-error');

        if (typeof error != 'string') {
            text = error.responseText;
            status = error.status;
            statusText = error.statusText;
        }

        let adminBody = $('#admin-body');
        let blockToDisplay = element === null ? adminBody : (element.length > 0 ? element : adminBody);

        if (body.hasClass('internal') && typeof text != 'undefined') {
            let message = '<div class="internal-error-alert ribbon-vwrapper alert alert-danger">';
            message += '<div class="ribbon ribbon-bookmark ribbon-vertical-l ribbon-danger">';
            message += '<img src="/medias/icons/fontawesome/regular/exclamation-triangle.svg" class="img-fluid">';
            message += '</div>';
            message += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
            message += '<span aria-hidden="true"><img src="/medias/icons/fontawesome/regular/times.svg" class="img-fluid lazyload"></span>';
            message += '</button>';
            message += text;
            message += '</div>';
            blockToDisplay.prepend(message);
        }

        if (status != 0 && statusText !== "error") {
            let message = '<div class="internal-error-alert ribbon-vwrapper alert alert-danger">';
            message += '<div class="ribbon ribbon-bookmark ribbon-vertical-l ribbon-danger">';
            message += '<img src="/medias/icons/fontawesome/regular/exclamation-triangle.svg" class="img-fluid">';
            message += '</div>';
            message += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
            message += '<span aria-hidden="true"><img src="/medias/icons/fontawesome/regular/times.svg" class="img-fluid lazyload"></span>';
            message += '</button>';
            message += '<strong class="mr-2">' + trans.data('error') + ' ' + status + '</strong>' + statusText;
            message += '</div>';
            blockToDisplay.prepend(message);
        }

        $('.stripe-preloader').addClass('d-none');
        $('.modal').modal('hide');

        let errorEl = body.find('.page-wrapper > .container-fluid .internal-error-alert').first();
        let elOffset = errorEl.offset().top;
        let elHeight = errorEl.height();
        let windowHeight = $(window).height();
        let offset;

        if (elHeight < windowHeight) {
            offset = elOffset - ((windowHeight / 2) - (elHeight / 2));
        } else {
            offset = elOffset;
        }

        $('html, body').animate({scrollTop: offset}, 700);
    }
}