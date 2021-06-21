/**
 * Entity switcher form
 */
export default function () {

    $('.entity-switcher-status').on('change', function (e) {

        e.preventDefault();

        let el = $(this);
        let form = el.closest('form');
        let status = form.find('input').is(':checked');
        let loader = $('#index-preloader');

        $.ajax({
            url: form.attr('action') + "?status=" + status,
            type: form.attr('method'),
            processData: false,
            contentType: false,
            dataType: 'json',
            async: true,
            beforeSend: function () {
                loader.removeClass('d-none');
            },
            success: function (response) {
                if (response.reload) {
                    setTimeout(function () {
                        location.reload(true);
                    }, 200);
                } else {
                    loader.addClass('d-none');
                }
            },
            error: function (errors) {

                /** Display errors */
                import('../core/errors').then(({default: displayErrors}) => {
                    new displayErrors(errors);
                }).catch(error => 'An error occurred while loading the component "errors"');
            }
        });

        e.stopImmediatePropagation();
        return false;
    });
}
