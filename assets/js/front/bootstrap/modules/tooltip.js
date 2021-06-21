/**
 *  Tooltips
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */

window.addEventListener("load", function () {
    let tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    for (let i = 0; i < tooltips.length; i++) {
        let tooltipEl = tooltips[i]
        let bsTooltip = new bootstrap.Tooltip(tooltipEl)
        tooltipEl.addEventListener('click', event => {
            bsTooltip.update()
            bsTooltip.hide()
        })
    }
})