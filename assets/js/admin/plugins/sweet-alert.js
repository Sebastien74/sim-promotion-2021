import '../../../scss/admin/lib/sweetalert.scss';
import '../lib/sweetalert/sweetalert.min';

/**
 *  On delete alert
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (e, el) {

    let body = $('body');
    let trans = $('#data-translation');
    let href = el.attr('href');
    let type = el.data('type');
    let reload = el.data('reload');
    let target = type === 'collection' ? el.closest('.prototype') : $(el.data('target'));
    let stripePreloader = el.closest('.refer-preloader').find('.stripe-preloader');
    let loader = stripePreloader.length > 0 ? stripePreloader : body.find('.main-preloader');

    let postParentForm = function (el) {

        let parentForm = el.closest('form');

        if (parentForm.length > 0) {

            let masterFormId = parentForm.attr('id');
            let formData = new FormData(document.getElementById(masterFormId));
            parentForm.addClass('is-submit');

            $.ajax({
                url: parentForm.attr('action') + "?ajax=true",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                async: true,
                beforeSend: function () {
                },
                success: function (response) {
                },
                error: function (errors) {
                    /** Display errors */
                    import('../core/errors').then(({default: displayErrors}) => {
                        new displayErrors(errors);
                    }).catch(error => 'An error occurred while loading the component "errors"');
                }
            });
        }
    }

    swal({
        title: trans.data('swal-delete-title'),
        text: trans.data('swal-delete-text'),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: trans.data('swal-delete-confirm-text'),
        cancelButtonText: trans.data('swal-delete-cancel-text'),
        closeOnConfirm: false
    }, function () {

        body.find('.sa-button-container .confirm').attr('disabled', '');
        body.find('.sa-button-container .cancel').attr('disabled', '');

        if (href === '') {
            target.remove();
            setTimeout(function () {
                swal(trans.data('swal-delete-success'), trans.data('swal-delete-success-text'), "success");
                swal.close();
            }, 1500);
            return true;
        }

        let url = href + '?ajax=true';
        if (href.indexOf('?') > -1) {
            url = href + '&ajax=true'
        }

        $.ajax({
            url: url,
            type: "DELETE",
            processData: false,
            contentType: false,
            async: true,
            dataType: 'json',
            beforeSend: function () {
            },
            success: function (response) {

                swal(trans.data('swal-delete-success'), trans.data('swal-delete-success-text'), "success");

                if (response.success) {
                    target.remove();
                    postParentForm(el);
                }

                if (response.success && response.reload || reload !== '') {
                    loader.removeClass('d-none');
                    swal.close();
                    $('#main-preloader').removeClass('d-none');
                    setTimeout(function () {
                        location.reload();
                    }, 100);
                }

                swal.close();
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