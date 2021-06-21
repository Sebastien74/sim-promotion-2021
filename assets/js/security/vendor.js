import '../../scss/security/vendor.scss';
import Routing from '../../../public/bundles/fosjsrouting/js/router.min';

/**
 *  Security Vendor
 *
 *  @copyright 2020
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 *  @version 1.0
 *
 *  Licensed under the MIT License (LICENSE.txt).
 *
 *  1 - Preloader
 *  2 - Lazy load
 *  3 - Recaptcha
 */

/** 1 - Preloader */
import './preloader';

/** 2 - Lazy load */
import '../vendor/components/lazy-load';

/** 3 - Recaptcha */
let formSecurity = document.querySelectorAll('form.security')
if (formSecurity.length > 0) {
    import('../vendor/components/recaptcha').then(({default: recaptcha}) => {
        new recaptcha(Routing);
    }).catch(error => 'An error occurred while loading the component "recaptcha"');
}