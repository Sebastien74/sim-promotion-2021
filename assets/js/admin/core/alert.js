/**
 * To display alert messages
 */
export default function (message, type = 'info', element = null, removeOld = true) {

    if (removeOld === true) {
        $('body').find('.alert').remove();
    }

    let blockToDisplay = element === null ? $('#admin-body') : element;

    let alert = '<div class="internal-error-alert ribbon-vwrapper alert alert-' + type + '">';
    alert += '<div class="ribbon ribbon-bookmark ribbon-vertical-l ribbon-' + type + '">';
    alert += '<img src="/medias/icons/fontawesome/regular/exclamation-triangle.svg" class="img-fluid">';
    alert += '</div>';
    alert += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
    alert += '<span aria-hidden="true"><img src="/medias/icons/fontawesome/regular/times.svg" class="img-fluid lazyload"></span>';
    alert += '</button>';
    alert += '<strong class="mb-0">' + message + '</strong>';
    alert += '</div>';
    blockToDisplay.prepend(alert);
}