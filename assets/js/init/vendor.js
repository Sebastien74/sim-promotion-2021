import '../../scss/init/vendor.scss';
import './bootstrap/bootstrap.bundle.min';

let body = $('body');

import 'bootstrap/dist/js/bootstrap.min';

/** Bytes generator */
function bytesGenerator(event, el, tokenLength = 30) {

    let spinnerIcon = el.find('svg');
    let group = el.closest('.form-group');
    let input = group.find('input');

    group.removeClass('is-invalid');
    group.find('.invalid-feedback').remove();
    input.removeClass('is-invalid');

    spinnerIcon.toggleClass('fa-spin');

    const rand = () => Math.random().toString(36).substr(2);
    const token = (length) => (rand() + rand() + rand() + rand()).substr(0, length);

    input.val(token(tokenLength));

    spinnerIcon.toggleClass('fa-spin');
}

body.on('click', '.generate-bytes', function (e) {
    e.preventDefault();
    bytesGenerator(e, $(this), 60);
});

/** Scroll to element */
function scrollToElement(el) {
    let elOffset = el.offset().top;
    let elHeight = el.height();
    let windowHeight = $(window).height();
    let offset;
    if (elHeight < windowHeight) {
        offset = elOffset - ((windowHeight / 2) - (elHeight / 2));
    } else {
        offset = elOffset;
    }
    let speed = 700;
    $('html, body').animate({scrollTop: offset}, speed);
}

/** Scroll to alert */
let alert = $('.alert');
if (alert.length > 0) {
    scrollToElement(alert);
}

/** Scroll to error */
let error = $(document).find('.invalid-feedback');
if (error.length > 0) {
    scrollToElement(error);
}

/** Generator */
let generator = $('.generator');
if (generator.length > 0) {
    let xHttp = new XMLHttpRequest();
    xHttp.open("GET", generator.data('path'), true);
    xHttp.setRequestHeader("Content-Type", "application/json");
    xHttp.onload = function (e) {
        if (this.readyState === 4 && this.status === 200) {
            let response = JSON.parse(this.response);
            if(response.success) {
                window.location.replace(response.redirect);
            }
        }
    };
    xHttp.send();
}