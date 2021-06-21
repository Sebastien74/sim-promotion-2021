import plugins from './plugins';

/**
 *  Plugins
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 *
 *  1 - Plugins
 *  2 - Popup
 *  3 - Lottie
 *  4 - Touch spin
 *  5 - Masonry
 *  6 - Dropify
 */

let body = document.body

/** 1 - Plugins */
plugins()

/** 2 - Popup */
let popupImages = body.querySelectorAll('.image-popup')
let popupGallery = body.querySelectorAll('.popup-gallery')
if (popupImages.length > 0 || popupGallery.length > 0) {
    import('./popup').then(({default: popup}) => {
        new popup(popupImages, popupGallery);
    }).catch(error => 'An error occurred while loading the component "popup"');
}

/** 3 - Lottie */
let icons = body.querySelectorAll('.ai')
if (icons.length > 0) {
    import('./lottie-icon').then(({default: lottiePlugin}) => {
        new lottiePlugin();
    }).catch(error => 'An error occurred while loading the component "lottie"');
}

/** 4 - Touch spin */
let inputs = body.querySelectorAll("input[type='number']")
if (inputs.length > 0) {
    import('./touchspin').then(({default: touchspin}) => {
        new touchspin(inputs);
    }).catch(error => 'An error occurred while loading the component "touchspin"');
}

/** 5 - Masonry */
let columns = body.querySelectorAll('.grid-columns');
if (columns.length > 0) {
    import('./masonry').then(({default: masonry}) => {
        new masonry();
    }).catch(error => 'An error occurred while loading the component "masonry"');
}

/** 6 - Dropify */
let dropifyEls = body.querySelectorAll('input.dropify');
if (dropifyEls.length > 0) {
    import('./dropify').then(({default: dropify}) => {
        new dropify();
    }).catch(error => 'An error occurred while loading the component "dropify"');
}