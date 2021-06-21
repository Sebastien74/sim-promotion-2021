/**
 * Popup
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 *
 * https://github.com/biati-digital/glightbox
 */

import GLightbox from 'glightbox'
import 'glightbox/dist/css/glightbox.min.css'

export default function () {

    const lightbox = GLightbox({
        selector: '.glightbox',
        touchNavigation: true,
        loop: true,
        height: '70%',
        autoplayVideos: true
    })
}