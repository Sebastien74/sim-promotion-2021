import '@splidejs/splide/dist/css/splide.min.css';
import Splide from '@splidejs/splide';

/**
 *  Splide Sliders
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 *  @doc https://splidejs.com/
 */
export default function (sliders) {

    for (let i = 0, len = sliders.length; i < len; i++) {

        let slider = sliders[i]

        new Splide(slider, {
            type: slider.dataset.fade,
            arrows: parseInt(slider.dataset.arrows) === 1,
            perPage: parseInt(slider.dataset.items),
            perMove: 1,
            autoplay: parseInt(slider.dataset.autoplay) === 1,
            pauseOnHover: parseInt(slider.dataset.pause) === 1,
            pagination: parseInt(slider.dataset.dots) === 1,
            rewind: true,
            interval: parseInt(slider.dataset.interval),
            lazyLoad: 'nearby',
            breakpoints: {
                '1200': {
                    perPage: parseInt(slider.dataset.itemsMiniPc),
                },
                '991': {
                    perPage: parseInt(slider.dataset.itemsTablet),
                },
                '767': {
                    perPage: parseInt(slider.dataset.itemsMobile),
                },
            }
        }).mount()
    }
}