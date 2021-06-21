/**
 *  Lazy loading background
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (backgrounds) {

    let setBackgrounds = function (backgrounds) {

        let height = window.innerHeight;
        let width = window.innerWidth;
        let orientation = height > width ? 'portrait' : 'landscape';
        let screenType = width > 991 ? 'desktop' : 'tablet';
        if (width < 768) {
            screenType = 'mobile';
        }

        for (let i = 0; i < backgrounds.length; i++) {

            let el = backgrounds[i];
            let background = el.dataset.background;
            let desktopBackground = el.dataset['desktop-background'];
            let tabletBackground = el.dataset['tablet-background'];
            let mobileBackground = el.dataset['mobile-background'];

            if (orientation === 'portrait') {
                if (screenType === 'mobile' && typeof mobileBackground != 'undefined') {
                    background = mobileBackground;
                } else if (screenType === 'mobile' && typeof mobileBackground == 'undefined' && typeof tabletBackground != 'undefined') {
                    background = tabletBackground;
                } else if (screenType === 'tablet' && typeof tabletBackground != 'undefined') {
                    background = tabletBackground;
                } else if (screenType === 'tablet' && typeof tabletBackground == 'undefined' && typeof mobileBackground != 'undefined') {
                    background = mobileBackground;
                }
            }

            background = orientation === 'landscape' && typeof desktopBackground != 'undefined'
                ? desktopBackground : background;

            el.style.background = 'url(' + background + ')';
        }
    };

    window.addEventListener("load", function(event) {
        setBackgrounds(backgrounds);
        window.addEventListener("resize", function(event) {
            setBackgrounds(backgrounds);
        });
    });
}