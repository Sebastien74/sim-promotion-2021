import resetModal from "../../vendor/components/reset-modal";
import route from "../core/routing";

/**
 *  Save files
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (Routing, e, el) {

    let body = $('body');
    let mediasModal = body.find('#medias-library-modal');
    let options = el.data('options');
    let files = mediasModal.find('.file.active');
    let type = mediasModal.data('type');

    let addMedia = function ({file, body, options, type, media, src, mediasModal}) {

        let loader = file.find('.loader-media');

        $.ajax({
            url: route(Routing, 'admin_medias_modal_add', {
                "website": body.data('id'),
                "options": JSON.stringify(options),
                "media": media
            }),
            type: "GET",
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function () {
                loader.removeClass('d-none');
            },
            success: function () {

                file.removeClass('active');
                let loaders = $('body').find('#medias-library-modal').find('.file.active').length;
                if (loaders === 0 && type === 'multiple') {
                    resetModal(mediasModal, true);
                    $('#main-preloader').removeClass('d-none');
                    location.reload();
                } else if (type === 'single') {

                    let dropifyWrapper = $(options.btnId).parent().parent().find('.dropify-wrapper');
                    let render = dropifyWrapper.find('.dropify-render').find('img');

                    if (render.length > 0) {
                        render.attr('src', src);
                    } else {
                        let renderView = dropifyWrapper.find('.dropify-message');
                        renderView.html('<img src="' + src + '" />');
                    }

                    resetModal(mediasModal, true);
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
    };

    files.each(function () {

        let file = $(this);
        let src = $(this).find('img').attr('original-src');

        addMedia({
            file: file,
            body: body,
            options: options,
            type: type,
            media: $(this).data('id'),
            src: src,
            mediasModal: mediasModal
        });
    });
}