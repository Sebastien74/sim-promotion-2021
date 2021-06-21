import '../../../scss/front/default/vendor.scss'

import Routing from '../../../../public/bundles/fosjsrouting/js/router.min';

/**
 *  Front Vendor
 *
 *  @copyright 2020
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 *  @version 1.0
 *
 *  Licensed under the MIT License (LICENSE.txt).
 *
 *  1 - First paint
 *  2 - Browsers
 *  3 - Bootstrap
 *  4 - Components
 *  5 - Recaptcha
 *  6 - Remove attr title on hover
 */

let body = document.body

/** 1 - First paint */

import './components/preloader';
import '../../vendor/components/lazy-load';
import '../../vendor/async';
import '../../vendor/components/images-sizes'

/** 2 - Browsers */

import '../../../js/vendor/browsers'

/** 3 - Bootstrap */

import '../bootstrap/modules/navigation'
import '../bootstrap/modules/tooltip'
import '../bootstrap/modules/carousel'
import '../bootstrap/modules/modal'
import '../bootstrap/modules/popover'
import '../bootstrap/modules/dropdown'

/** 4 - Components */

import './components/vendor'
import '../../vendor/components/webmaster'

/** 5 - Recaptcha */
let formSecurity = body.querySelectorAll('form.security')
if (formSecurity.length > 0) {
    import('../../vendor/components/recaptcha').then(({default: recaptcha}) => {
        new recaptcha(Routing);
    }).catch(error => 'An error occurred while loading the component "recaptcha"')
}

window.addEventListener("load", function () {

    /** 6 - Remove attr title on hover */
    let elements = body.querySelectorAll("*[title]")
    for (let i = 0; i < elements.length; i++) {
        let element = elements[i]
        if (!element.hasAttribute("data-bs-toggle")) {
            element.addEventListener('mouseover', function () {
                element.setAttribute("data-tmp", element.getAttribute("title"))
                element.setAttribute("title", "")
            }, false)
            element.addEventListener('mouseleave', function () {
                element.setAttribute("title", element.getAttribute("data-tmp"))
                element.removeAttribute("data-tmp")
            }, false)
        }
    }
})