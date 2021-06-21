/**
 * Set positions
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (items) {

    let body = $('body');
    let loader = body.find('#product-preloader');
    let length = items.length;

    loader.removeClass('d-none');

    items.each(function (i, el) {

        let newPosition = i + 1;
        let elementId = $(el).attr('id');
        let path = $(el).data('pos-path');

        $('#' + elementId).attr('data-position', newPosition);

        $.ajax({
            url: path + "?position=" + newPosition,
            type: "GET",
            processData: false,
            contentType: false,
            dataType: 'json',
            async: false,
            beforeSend: function () {
            },
            success: function (response) {
                if ((i + 1) === length) {
                    loader.addClass('d-none');
                }
            },
            error: function (errors) {
                /** Display errors */
                import('../../core/errors').then(({default: displayErrors}) => {
                    new displayErrors(errors);
                }).catch(error => 'An error occurred while loading the component "errors"');
            }
        });
    });
}