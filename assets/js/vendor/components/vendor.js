/**
 * Core vendor
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 *
 *  1 - Decrypt phone
 *  2 - Recaptcha
 *  3 - Waves effect
 *  4 - Form
 *  5 - Copy
 */

import Routing from '../../../../public/bundles/fosjsrouting/js/router.min'

let body = document.body

/** 1 - Decrypt */
import './decrypt'

window.addEventListener("load", function () {

    /** 2 - Recaptcha */
    let formSecurity = body.querySelectorAll('form.security')
    if (formSecurity.length > 0) {
        import('./recaptcha').then(({default: recaptcha}) => {
            new recaptcha(Routing);
        }).catch(error => 'An error occurred while loading the component "recaptcha"');
    }
});

/** 3 - Waves effect */
let waves = body.querySelectorAll('.waves-effect')
if (waves.length > 0) {
    import('../libraries/waves').then(({default: waves}) => {
        new waves();
    }).catch(error => 'An error occurred while loading the component "decrypt-email"');
}

/** 4 - Form */
let forms = body.querySelectorAll('form')
if (forms.length > 0) {

    /** Form custom fields */
    import('./form').then(({default: form}) => {
        new form();
    }).catch(error => 'An error occurred while loading the component "form"');

    /** Keyup */
    import('./keyup-fields').then(({default: keyupForm}) => {
        new keyupForm();
    }).catch(error => 'An error occurred while loading the component "keyup-fields"');
}

/** 5 - Copy */
import('./copy').then(({default: copy}) => {
    new copy();
}).catch(error => 'An error occurred while loading the component "copy"');