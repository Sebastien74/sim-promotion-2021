/**
 * Ajax GET refresh
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    let body = $('body');

    body.on('click', '.ajax-get-refresh', function (e) {

        e.preventDefault();

        let el = $(this);
        let target = el.data('target');
        let targetAttr = typeof target != 'undefined' ? target : '.ajax-content';
        let mainLoader = body.find('.main-preloader');
        let loader = body.find(el.data('target-loader'));
        let customPreloader = true;

        if (loader.length < 1) {
            loader = mainLoader;
            customPreloader = false;
        }

        $.ajax({
            url: el.attr('href') + "?ajax=true",
            type: "GET",
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function () {
                $('.alert').remove();
                if (loader.length >= 1) {
                    if (customPreloader) {
                        loader.parent().removeClass('d-none');
                    }
                    loader.removeClass('d-none');
                }
            },
            success: function (response) {

                if (response.html) {

                    let html = $(response.html).find(targetAttr)[0];
                    let body = $('body');
                    let ajaxContent = body.find(targetAttr);
                    ajaxContent.replaceWith(html);

                    if (loader.length >= 1) {
                        loader.addClass('d-none');
                        if (customPreloader) {
                            loader.parent().addClass('d-none');
                        }
                    }

                    $('[data-toggle=tooltip]').tooltip({trigger: "hover"});

                    mainLoader.addClass('d-none');

                    let scrollToEl = body.find('.scroll-to-response-ajax');
                    if (scrollToEl.length >= 1) {
                        $('html, body').animate({scrollTop: scrollToEl.offset().top}, 800);
                    }
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