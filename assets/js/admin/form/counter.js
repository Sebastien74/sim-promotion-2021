/**
 *  Counter
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    $('body').on('keyup', '.counter-form-group .form-control', function () {

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