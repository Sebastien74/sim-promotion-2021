/**
 *  Modal
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */

window.addEventListener("load", function () {

    let body = document.body

    /** Modals with timer */
    let timerModals = body.querySelectorAll('[data-modal-timer]')
    if (timerModals) {
        for (let i = 0; i < timerModals.length; i++) {
            let timerModal = new bootstrap.Modal(timerModals[i], {
                keyboard: false
            })
            setTimeout(function () {
                timerModal.show()
            }, parseInt(timerModals[i].dataset.modalTimer))
        }
    }
})