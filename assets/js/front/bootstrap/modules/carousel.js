/**
 *  Carousel
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */

window.addEventListener("load", function () {

    let body = document.body
    let carousels = body.querySelectorAll('[data-component="carousel-bootstrap"]')

    for (let i = 0; i < carousels.length; i++) {

        let carousel = carousels[i]
        let interval = carousel.dataset.bsInterval ? parseInt(carousel.dataset.bsInterval) : 5000
        let autoplay = parseInt(carousel.dataset.bsAutoplay) === 1
        let pause = carousel.dataset.bsPause
        let hasMultiplePerSlide = carousel.classList.contains('multiple-carousel')

        if (hasMultiplePerSlide) {

            // let itemsCount = carousel.find('.item-col').length;
            // let itemPerSlide = carousel.data('per-slide') ? carousel.data('per-slide') : 3;
            // itemPerSlide = itemsCount < itemPerSlide ? itemsCount : itemPerSlide;
            // itemPerSlide = $(window).width() < 992 ? 2 : itemPerSlide;
            //
            // carousel.find('.carousel-item').each(function () {
            //
            //     let minPerSlide = itemPerSlide;
            //     let next = $(this).next();
            //
            //     if (!next.length) {
            //         next = $(this).siblings(':first');
            //     }
            //
            //     next.children(':first-child').clone().appendTo($(this));
            //
            //     for (let i = 0; i < minPerSlide; i++) {
            //         next = next.next();
            //         if (!next.length) {
            //             next = $(this).siblings(':first');
            //         }
            //         next.children(':first-child').clone().appendTo($(this));
            //     }
            // });
            //
            // carousel.carousel({
            //     interval: interval
            // });

        } else {

            let bootstrapCarousel = new bootstrap.Carousel(carousel, {
                interval: interval,
                keyboard: true,
                slide: autoplay, /** autoplay */
                pause: pause, /** hover or false */
            })
        }
    }
})