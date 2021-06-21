/**
 *  Dropdowns
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */

window.addEventListener("load", function () {
    let dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
    let dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl)
    })
})