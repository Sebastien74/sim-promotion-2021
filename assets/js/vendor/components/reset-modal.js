/**
 *  Reset modal
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (modal, remove = false) {

    let body = $('body');
    $('body .modal-backdrop').last().remove();

    body.removeClass('modal-open').removeAttr('style');

    if (remove) {
        modal.remove();
    } else {
        modal.modal('hide');
    }
}