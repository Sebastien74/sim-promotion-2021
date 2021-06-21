/**
 *  To set img size attributes
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */

let body = document.body;

let setAttributes = function () {

    let images = body.querySelectorAll('img')

    for (let i = 0; i < images.length; i++) {

        let image = images[i]
        let imageParent = image.parentNode
        let width = imageParent.offsetWidth
        let height = imageParent.offsetHeight

        if (width > 1 && height > 1 && width && !image.classList.contains('force-size')) {
            if (parseInt(image.getAttribute('width')) !== width) {
                image.setAttribute('width', width);
            }
            if (parseInt(image.getAttribute('height')) !== height) {
                image.setAttribute('height', height);
            }
        }
    }
}

if (!body.classList.contains('skin-admin')) {
    window.addEventListener("load", function () {
        setAttributes()
    })
}