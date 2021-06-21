/**
 *  parallax.js v1.5.0 (https://github.com/pixelcog/parallax.js/)
 */

import 'jquery-parallax.js';

export default function (parallaxEls) {

    if (parallaxEls.length > 0) {
        parallaxEls.each(function () {
            let parallaxEl = $(this);
            parallaxEl.parallax({
                naturalWidth: parallaxEl.outerWidth(),
                naturalHeight: parallaxEl.outerHeight(),
                speed: 0.2,
                zIndex: 0
            });
        });
    }

    $(window).trigger('resize').trigger('scroll');
}