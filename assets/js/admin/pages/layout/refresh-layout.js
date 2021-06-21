import layoutActivation from './vendor';

import '../../bootstrap/js/dist/modal';
import '../../bootstrap/js/dist/tooltip';

/**
 * Refresh layout
 */
export default function (Routing, form, modal, event) {

    let body = $('body');
    let formID = form.attr('id');
    let formData = new FormData(document.getElementById(formID));
    let action = form.attr('action');
    let loader = body.find('#layout-preloader');
    let scrollElement = form.data('scroll-to');

    if (modal) {
        body.find('#' + modal.attr('id')).remove();
        body.removeClass('modal-open').removeAttr('style');
        $('.modal-backdrop').remove();
    }

    $.ajax({
        url: action + "?ajax=true",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        async: true,
        beforeSend: function () {
            loader.toggleClass('d-none');
        },
        success: function (response) {

            $.ajax({
                url: window.location.href + "?ajax=true",
                type: "GET",
                processData: false,
                contentType: false,
                dataType: 'json',
                async: true,
                beforeSend: function () {
                },
                success: function (response) {

                    let html = $(response.html).find("#layout-grid")[0];
                    $("#layout-grid").replaceWith(html);

                    if (typeof $(scrollElement) != "undefined" && $(scrollElement).length > 0) {
                        $("body, html").animate({
                            scrollTop: $(scrollElement).offset().top
                        }, 'slow');
                    }

                    layoutActivation(Routing);

                    $('[data-toggle=tooltip]').tooltip({trigger: "hover"});

                    loader.toggleClass('d-none');
                },
                error: function (errors) {
                    /** Display errors */
                    import('../../core/errors').then(({default: displayErrors}) => {
                        new displayErrors(errors);
                    }).catch(error => 'An error occurred while loading the component "errors"');
                }
            });
        },
        error: function (errors) {
            /** Display errors */
            import('../../core/errors').then(({default: displayErrors}) => {
                new displayErrors(errors);
            }).catch(error => 'An error occurred while loading the component "errors"');
        }
    });

    event.stopImmediatePropagation();
    return false;
}