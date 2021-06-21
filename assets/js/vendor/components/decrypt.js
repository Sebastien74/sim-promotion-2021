import route from "./routing-import"
import Routing from '../../../../public/bundles/fosjsrouting/js/router.min'

/**
 *  Emails & phones decrypt
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
document.addEventListener('DOMContentLoaded', function () {

    /** Emails */
    let emails = document.querySelectorAll('a[data-mailto]')
    for (let i = 0; i < emails.length; i++) {

        let el = emails[i]
        let mailto = el.dataset.mailto
        let id = el.dataset.id
        let elText = el.getElementsByClassName("email-text")[0]

        if (typeof mailto != "undefined") {
            let xHttp = new XMLHttpRequest()
            xHttp.open("GET", route(Routing, 'front_decrypt', {website: id, string: mailto}), true)
            xHttp.setRequestHeader("Content-Type", "application/json; charset=utf-8")
            xHttp.send()
            xHttp.onload = function (e) {
                if (this.readyState === 4 && this.status === 200) {
                    let response = JSON.parse(this.response)
                    if (response.result !== false) {
                        el.setAttribute("href", "mailto:" + response.result)
                        if(elText) {
                            elText.innerHTML = response.result
                        }
                        el.classList.remove('loading')
                    }
                }
            }
        }
    }

    /** Phones */
    let phones = document.querySelectorAll('a[data-tel]')
    for (let i = 0; i < phones.length; i++) {

        let el = phones[i]
        let id = el.dataset.id
        let telTo = el.dataset.tel
        let telText = el.dataset.text
        let elText = el.getElementsByClassName("phone-text")[0]

        el.onclick = function (event) {
            /** Facebook track */
            if(el.classList.contains('fb-phone-track')) {
                fbq('track', 'Contact')
            }
            if(el.classList.contains('has-desktop')) {
                event.preventDefault()
            }
        }

        if (typeof telTo != "undefined") {
            let xHttp = new XMLHttpRequest()
            xHttp.open("GET", route(Routing, 'front_decrypt', {website: id, string: telTo}), true)
            xHttp.setRequestHeader("Content-Type", "application/json; charset=utf-8")
            xHttp.send()
            xHttp.onload = function (e) {
                if (this.readyState === 4 && this.status === 200) {
                    let response = JSON.parse(this.response)
                    if (response.result !== false) {
                        el.setAttribute("href", "tel:" + response.result)
                        el.classList.remove('loading')
                    }
                }
            }
        }

        if (typeof telText != "undefined") {
            let xHttp = new XMLHttpRequest()
            xHttp.open("GET", route(Routing, 'front_decrypt', {website: id, string: telText}), true)
            xHttp.setRequestHeader("Content-Type", "application/json; charset=utf-8")
            xHttp.send()
            xHttp.onload = function (e) {
                if (this.readyState === 4 && this.status === 200) {
                    let response = JSON.parse(this.response)
                    if (response.result !== false) {
                        elText.innerHTML = response.result
                        el.classList.remove('loading')
                    }
                }
            }
        }
    }
})