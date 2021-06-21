import '../../scss/in-build/vendor.scss';

/**
 *  Build
 *
 *  @copyright 2020
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 *  @version 1.0
 *
 *  Licensed under the MIT License (LICENSE.txt).
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