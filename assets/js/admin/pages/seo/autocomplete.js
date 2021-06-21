/**
 * Autocomplete
 */
export default function () {

    let body = $('body');

    body.on('click', '.autocomplete', function (e) {
        e.preventDefault();
        let form = $(this).closest('form');
        let metaTitle = form.find('.meta-title').val();
        let metaDescription = form.find('.meta-description').val();
        form.find('.meta-og-title').val(metaTitle);
        form.find('.meta-og-description').val(metaDescription);
        resetCounters();
    });

    body.on('click', '.resetautocomplete', function (e) {
        e.preventDefault();
        let form = $(this).closest('form');
        form.find('.meta-og-title').val('');
        form.find('.meta-og-description').val('');
        resetCounters();
    });

    let resetCounters = function () {

        $('.counter-form-group .form-control').each(function () {

            let el = $(this);
            let count = el.val().length;
            let counter = el.closest('.counter-form-group').find('.char-counter');
            let limit = counter.attr('data-limit');

            counter.find('.count').text(count);

            if (count > limit) {
                counter.removeClass('text-info').addClass('text-danger');
            } else {
                counter.removeClass('text-danger').addClass('text-info');
            }
        });
    }
}