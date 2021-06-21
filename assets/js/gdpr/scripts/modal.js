import route from "../../vendor/components/routing";
import management from "./management";
import services from "./services";

/**
 *  Modal
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    let body = document.body
    let cookieName = 'felixCookies'
    let FelixCookies = Cookies.get(cookieName)
    let cookiesInit = typeof FelixCookies != 'undefined'
    let gdprActive = body.dataset.gdpr

    if (!cookiesInit && parseInt(gdprActive) === 1) {
        window.addEventListener('scroll', function (e) {
            if (!body.classList.contains('scroll-cookies-modal')) {
                body.classList.add('scroll-cookies-modal')
                openModal()
                e.stopImmediatePropagation()
                return false
            }
        })
    }

    let openModalEls = document.getElementsByClassName('open-gdpr-modal')
    for (let i = 0; i < openModalEls.length; i++) {
        openModalEls[i].onclick = function (e) {
            e.preventDefault()
            cookiesInit = true
            openModal()
        }
    }

    function openModal() {

        let xHttp = new XMLHttpRequest()
        xHttp.open("GET", route('front_gdpr_modal', {_format: 'json'}), true)
        xHttp.setRequestHeader("Content-Type", "application/json; charset=utf-8")
        xHttp.send()
        xHttp.onload = function (e) {

            if (this.readyState === 4 && this.status === 200) {

                let response = JSON.parse(this.response)
                let body = document.body
                let backdrop = cookiesInit ? true : 'static'

                let html = document.createElement('div')
                html.innerHTML = response.html
                document.body.appendChild(html)

                let modalEl = document.getElementById('gdpr-modal')
                let modal = new bootstrap.Modal(modalEl, {
                    backdrop: backdrop,
                    keyboard: cookiesInit
                })
                modal.show()
                modalEl.addEventListener('hidden.bs.modal', function () {
                    modalEl.remove()
                    body.classList.remove('gdpr-modal-open')
                })
                body.classList.add('gdpr-modal-open')

                switchBlocks()

                let tooltipTriggerList = [].slice.call(modalEl.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })

                let preloader = document.getElementById('main-preloader')
                if (preloader) {
                    let preloaderClicks = modalEl.querySelectorAll('[data-toggle="preloader"]')
                    for (let i = 0; i < preloaderClicks.length; i++) {
                        preloaderClicks[i].onclick = function () {
                            preloader.classList.remove('d-none')
                            body.classList.add('preloader-active')
                        }
                    }
                }

                let pathname = window.location.pathname;
                if (pathname.indexOf('cookies') !== -1) {
                    let cookiesWraps = document.getElementsByClassName('cookies-more-wrap')
                    for (let i = 0; i < cookiesWraps.length; i++) {
                        cookiesWraps[i].remove()
                    }
                }

                let moreWrap = document.getElementById('cookies-more-wrap')
                if (moreWrap && preloader) {
                    moreWrap.onclick = function () {
                        if (preloader) {
                            preloader.classList.remove('d-none')
                            body.classList.add('preloader-active')
                            window.location.href = this.querySelectorAll('a').getAttribute('href')
                        }
                    }
                }

                management(modal)
                services()
            }
        }
    }

    function switchBlocks() {
        let modal = document.getElementById('gdpr-modal')
        let switchers = modal.querySelectorAll('button.switch')
        for (let i = 0; i < switchers.length; i++) {
            switchers[i].onclick = function (e) {
                let switchBlocks = modal.getElementsByClassName('switch-block')
                for (let j = 0; j < switchBlocks.length; j++) {
                    switchBlocks[j].classList.add('d-none')
                }
                document.getElementById(switchers[i].dataset.target).classList.remove('d-none')
            }
        }
    }
}