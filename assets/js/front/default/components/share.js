/**
 *  To manage shares links
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */

import 'bootstrap/js/dist/modal';

export default function (body) {

    $(function () {

        if ($(window).width() > 991) {
            body.on('click', '.share-link-popup', function (e) {
                e.preventDefault();
                window.open(this.href, 'targetWindow', 'toolbar=no,location=0,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=500');
                return false;
            });
        }

        body.on('click', '.share-email-submit', function (e) {

            e.preventDefault();

            let el = $(this);
            let form = el.closest('form');
            let fields = form.find('.form-control');
            let isValid = true;

            fields.each(function () {

                let field = $(this);

                if (field.prop('required') && field.val() === '') {
                    field.addClass('is-invalid');
                    isValid = false;
                }
            });

            if (isValid) {

                let to = form.find('#share-to-input').val();
                let subject = form.find('#share-subject-input').val();
                let body = el.data('url') + '%0D%0D' + form.find('#share-message-input').val();
                let href = 'mailto:' + to + '?&subject=' + subject + '&body=' + body;

                window.location = href;

                form[0].reset();

                $('#share-email-modal').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                return false;
            }
        });
    });
}