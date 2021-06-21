/**
 *  Ajax Form
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    let body = $('body');

    body.on('click', '.ajax-post', function (e) {

        e.preventDefault();

        let el = $(this);
        let form = el.closest('form');
        let formId = form.attr('id');
        let formData = new FormData(document.getElementById(formId));
        let stripePreloader = el.closest('.refer-preloader').find('.stripe-preloader');
        let loader = stripePreloader.length > 0 ? stripePreloader : body.find('.main-preloader');
        let hasRefresh = el.hasClass('refresh');
        let removeModal = el.hasClass('remove-modal');
        let closeModal = el.hasClass('close-modal');

        if (el.hasClass('inner-preloader-btn')) {
            loader = $('body').find('.inner-preloader');
        }

        $.ajax({
            url: form.attr('action') + "?ajax=true",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            async: true,
            beforeSend: function () {

                /** Remove errors */
                import('../../vendor/components/remove-errors').then(({default: removeErrors}) => {
                    new removeErrors();
                }).catch(error => 'An error occurred while loading the component "remove-errors"');

                loader.removeClass('d-none');
            },
            success: function (response) {

                if (response.flashBag) {
                    loader.addClass('d-none');
                } else if (hasRefresh && response.success && response.redirection) {
                    window.location.href = response.redirection;
                } else if (hasRefresh && response.success) {
                    location.reload();
                }
                else if (response.html) {

                    let html = $(response.html).find(".ajax-content")[0];
                    let ajaxContent = form.find(".ajax-content");

                    if (ajaxContent.length === 0) {
                        ajaxContent = form.closest(".ajax-content");
                    }

                    if (response.success && removeModal) {
                        /** Reset modal */
                        let modal = form.closest('.modal');
                        import('../../vendor/components/reset-modal').then(({default: resetModal}) => {
                            new resetModal(modal, true);
                        }).catch(error => 'An error occurred while loading the component "reset-modal"');
                    }

                    if (response.success && closeModal) {
                        /** Reset modal */
                        let modal = form.closest('.modal');
                        import('../../vendor/components/reset-modal').then(({default: resetModal}) => {
                            new resetModal(modal);
                        }).catch(error => 'An error occurred while loading the component "reset-modal"');
                    }

                    ajaxContent.replaceWith(html);

                    /** Refresh dropify */
                    import('./dropify').then(({default: dropifyJS}) => {
                        new dropifyJS();
                    }).catch(error => 'An error occurred while loading the component "dropify"');

                    /** Refresh select2 */
                    import('../../vendor/plugins/select2').then(({default: select2}) => {
                        new select2();
                    }).catch(error => 'An error occurred while loading the component "select2"');

                    /** Refresh summernote */
                    import('../plugins/summernote').then(({default: summernote}) => {
                        new summernote();
                    }).catch(error => 'An error occurred while loading the component "summernote"');

                    /** Scroll to errors */
                    import('../../vendor/components/scroll-error').then(({default: scrollErrors}) => {
                        new scrollErrors();
                    }).catch(error => 'An error occurred while loading the component "scroll-error"');

                    if (response.success && closeModal) {

                        /** Reset modal */
                        let modal = form.closest('.modal');
                        import('../../vendor/components/reset-modal').then(({default: resetModal}) => {
                            new resetModal(modal);
                        }).catch(error => 'An error occurred while loading the component "reset-modal"');

                        form[0].reset();
                        loader.addClass('d-none');
                    }

                    if (response.success && removeModal) {
                        /** Reset modal */
                        let modal = form.closest('.modal');
                        import('../../vendor/components/reset-modal').then(({default: resetModal}) => {
                            new resetModal(modal, true);
                        }).catch(error => 'An error occurred while loading the component "reset-modal"');
                    }

                    loader.addClass('d-none');
                }
            },
            error: function (errors) {

                /** Display errors */
                import('../core/errors').then(({default: displayErrors}) => {
                    new displayErrors(errors);
                }).catch(error => 'An error occurred while loading the component "errors"');

                loader.addClass('d-none');
            }
        });

        e.stopImmediatePropagation();
        return false;
    });
}