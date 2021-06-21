import 'lazysizes';

// import lazyImages from './lazy-images';
import lazyBackgrounds from './lazy-backgrounds';
import lazyVideos from './lazy-videos';

/**
 * Lazy load
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 *
 * Licensed under the MIT License (LICENSE.txt).
 */

/** On images loaded */
document.addEventListener("lazyloaded", function (e) {

    let lazyElements = document.getElementsByClassName('lazyloaded');
    for (let i = 0; i < lazyElements.length; i++) {
        let lazyElement = lazyElements[i];
        let spinnerWrap = lazyElement.parentNode.parentNode.getElementsByClassName('spinner-wrap');
        if (spinnerWrap.length > 0) {
            spinnerWrap[0].remove();
        }
    }

    // let images = document.body.querySelectorAll("img[data-src]");
    // if (images.length > 0) {
    //     lazyImages(images);
    // }

}, false);

/** Lazy loading background */
let backgrounds = document.body.querySelectorAll("*[data-background]");
if (backgrounds.length > 0) {
    lazyBackgrounds(backgrounds);
}

/** Lazy loading video */
let videosEl = document.body.getElementsByClassName("lazy-video");
if (videosEl.length > 0) {
    lazyVideos();
}