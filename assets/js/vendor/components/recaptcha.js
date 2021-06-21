/**
 *  Recaptcha
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */

import '../../../scss/vendor/components/_recaptcha.scss';
import route from "./routing-import";

export default function (Routing) {

    let recaptcha = function () {

        let body = document.body
        let forms = body.querySelectorAll('form.security')
        let recaptchaEl = document.getElementById('recaptcha')

        if (recaptchaEl) {

            if (recaptchaEl.classList.contains('d-none')) {
                recaptchaEl.classList.remove('d-none')
            }

            recaptchaEl.onclick = function () {
                if (!recaptchaEl.classList.contains('active')) {
                    recaptchaEl.classList.add('active')
                }
            }

            recaptchaEl.addEventListener('mouseleave', e => {
                if (recaptchaEl.classList.contains('active')) {
                    recaptchaEl.classList.remove('active')
                }
            })
        }

        for (let i = 0; i < forms.length; i++) {

            let form = forms[i]
            let data = form.getElementsByClassName('form-data')[0]
            let string = encodeURIComponent(data.dataset.id)
            let website = data.dataset.website

            if (string !== '' && website !== '') {
                let xHttp = new XMLHttpRequest()
                xHttp.open("GET", route(Routing, 'front_encrypt', {string: string, 'website': website}), true)
                xHttp.setRequestHeader("Content-Type", "application/json; charset=utf-8")
                xHttp.send()
                xHttp.onload = function (e) {
                    if (this.readyState === 4 && this.status === 200) {
                        let response = JSON.parse(this.response)
                        if (response.result !== false) {
                            form.getElementsByClassName('field_ho')[0].value = response.result
                        }
                    }
                }
            }
        }

        // if (forms.length > 0) {
        //
        //     let recaptchaForms = body.querySelectorAll('.recaptcha-referer');
        //     let recaptchaFormReferer = forms[0].closest('.recaptcha-referer');
        //     let recaptchaFormRefererEl = $(recaptchaFormReferer);
        //
        //     if (recaptchaForms.length === 1 && recaptchaFormRefererEl.length === 1) {
        //
        //         $(window).scroll(function () {
        //
        //             let docViewTop = $(window).scrollTop();
        //             let docViewBottom = docViewTop + $(window).height();
        //             let recaptchaRefererTop = recaptchaFormRefererEl.offset().top;
        //
        //             if ((recaptchaRefererTop <= docViewBottom) && (recaptchaRefererTop >= docViewTop)) {
        //                 recaptchaEl.removeClass('d-none');
        //             } else {
        //                 recaptchaEl.addClass('d-none');
        //             }
        //         });
        //     } else {
        //         recaptchaEl.removeClass('d-none');
        //     }
        // }
    }

    recaptcha()
}