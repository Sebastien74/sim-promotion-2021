import '../../../scss/admin/pages/development.scss';

import places from 'places.js';

let locale = $('html').attr('lang');

$.fn.simulateKeyPress = function (character) {
    $(this).trigger({ type: 'keypress', which: character.charCodeAt(0) });
};


$('#cities-bio').find('.bio-places').each(function () {

    let el = $(this);
    let data = $(this);
    // let input = $('#bio-places');
    // setTimeout(function () {
    //     console.log(el);
    //     input.val(el.data('city') + ' ' + el.data('zipcode'));
    //     $('body').simulateKeyPress('x');
    // }, 3000);

    let placesAutocomplete = places({
        appId: 'plIZX27D5L3L',
        apiKey: '61cd64b7ddb5453f558240e9e5a17bc0',
        language: locale,
        type: 'townhall',
        container: document.querySelector('#' + el.attr('id'))
    });

    el.val(el.data('city'));

    placesAutocomplete.on('change', function (e) {

        console.log(e);
        // $('input.latitude').val(e.suggestion.latlng.lat);
        // $('input.longitude').val(e.suggestion.latlng.lng);
        // $('input.zip-code').val(e.suggestion.postcode);
        // $('input.department').val(e.suggestion.county);
        // $('input.region').val(e.suggestion.administrative);
        //
        // let address = e.suggestion.name ? e.suggestion.name : e.suggestion.value;
        // $('input.address').val(address);
        //
        // let city = e.suggestion.city ? e.suggestion.city : e.suggestion.name;
        // $('input.city').val(city);
        //
        // let country = e.suggestion.countryCode;
        // countryEl.val(country.toUpperCase());
        // countryEl.select2().trigger('change');
    });
});

