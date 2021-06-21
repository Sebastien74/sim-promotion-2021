/**
 *  Preloader
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */

let body = document.body
let preloader = document.getElementById("main-preloader")

if (preloader) {

    window.addEventListener("load", function () {
        if (!preloader.classList.contains('disappear')) {
            preloader.classList.add('disappear')
            body.classList.remove('overflow-hidden')
        } else if (!preloader.classList.contains('d-none')) {
            body.classList.add('d-none')
        }
    })

    window.addEventListener('pageshow', function (event) {
        if (!preloader.classList.contains('disappear')) {
            preloader.classList.add('disappear')
            body.classList.remove('overflow-hidden')
        } else if (event.persisted) {
            if (!preloader.classList.contains('d-none')) {
                body.classList.add('d-none')
            }
        }
    })

    let preloaderEls = document.querySelectorAll('[data-toggle="preloader"]')
    for (let i = 0; i < preloaderEls.length; i++) {
        preloaderEls[i].addEventListener("click", function (e) {
            if (e.which !== 2) {
                body.classList.add('overflow-hidden')
                preloader.classList.remove('disappear')
            }
        })
    }

    let paginationLinks = document.querySelectorAll('.pagination a.page-link')
    for (let i = 0; i < paginationLinks.length; i++) {
        paginationLinks[i].addEventListener("click", function () {
            if (this.hasAttribute("role")) {
                preloader.classList.remove('disappear')
                body.classList.add('preloader-active')
            }
        })
    }
}