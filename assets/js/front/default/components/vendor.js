// import './cart'
import './form/form'
import './form/newsletter'
import './scroll'
import '../../../vendor/components/decrypt'
import '../../../gdpr/vendor'

let scroller = document.getElementById('scroll-wrapper')
if (document.body.contains(scroller)) {
    import('./scroll-infinite').then(({default: scrollInfiniteModule}) => {
        new scrollInfiniteModule(scroller)
    }).catch(error => 'An error occurred while loading the component "scroll-infinite"')
}

let boxAlertElem = document.getElementById('website-alert')
if (boxAlertElem) {
    import('./website-alert').then(({default: boxAlert}) => {
        new boxAlert(boxAlertElem)
    }).catch(error => 'An error occurred while loading the component "website-alert"')
}

let popupImages = document.getElementsByClassName('image-popup')
let popupGallery = document.getElementsByClassName('popup-gallery')
if (popupImages.length > 0 || popupGallery.length > 0) {
    import('../../../vendor/plugins/popup').then(({default: popup}) => {
        new popup()
    }).catch(error => 'An error occurred while loading the component "popup"')
}

let splideSlider = document.getElementsByClassName('splide')
if (splideSlider.length > 0) {
    import('./splide-slider').then(({default: splide}) => {
        new splide(splideSlider)
    }).catch(error => 'An error occurred while loading the component "splide-slider"')
}

let maps = document.getElementsByClassName('map-box')
if (maps.length > 0) {
    import('./map/map').then(({default: mapModule}) => {
        window.addEventListener('scroll', function (e) {
            if (!document.body.classList.contains('map-initialized')) {
                new mapModule(maps)
                document.body.classList.add('map-initialized')
            }
        })
    }).catch(error => 'An error occurred while loading the component "components/map"')
}

let videosHTML = document.getElementsByClassName('video-block-html')
if (videosHTML.length > 0) {
    import('./video').then(({default: videos}) => {
        new videos(videosHTML)
    }).catch(error => 'An error occurred while loading the component "video"')
}

let grids = document.getElementsByClassName('card-columns')
if (grids.length > 0) {
    import('../../../vendor/plugins/masonry').then(({default: masonry}) => {
        new masonry()
    }).catch(error => 'An error occurred while loading the component "vendor/plugins/masonry"')
}

let calendars = document.getElementsByClassName('calendar-render-container')
if (calendars.length > 0) {
    import('./calendar').then(({default: calendars}) => {
        new calendars()
    }).catch(error => 'An error occurred while loading the component "components/calendar"')
}

let counters = document.querySelectorAll('[data-component="counter"]')
if (counters.length > 0) {
    import('./counters').then(({default: counters}) => {
        new counters()
    }).catch(error => 'An error occurred while loading the component "components/counters"')
}

let formCalendars = document.querySelectorAll('[data-component="form-calendar"]')
if (formCalendars.length > 0) {
    import('./form/form-calendar').then(({default: formCalendar}) => {
        new formCalendar()
    }).catch(error => 'An error occurred while loading the component "components/form/form-calendar"')
}

let aosElements = document.querySelectorAll('*[data-aos]')
if (aosElements.length > 0) {
    import('./aos').then(({default: AOS}) => {
        new AOS()
    }).catch(error => 'An error occurred while loading the component "components/aos"');
}