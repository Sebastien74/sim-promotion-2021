import tooltips from "../plugins/tooltips";
import route from "../core/routing";
import masterDropzoneForm from "./master-dropzone-form";

/**
 * Media library modal
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (Routing, e, el) {

    let body = $('body');
    let stripePreloader = $(el).closest('.refer-preloader').find('.stripe-preloader');
    let loader = stripePreloader.length > 0 ? stripePreloader : body.find('.main-preloader');

    /** Open modal */

    $.ajax({
        url: route(Routing, 'admin_medias_modal', {
            "website": body.data('id'),
            "options": JSON.stringify($(el).data('options'))
        }),
        type: "GET",
        processData: false,
        contentType: false,
        dataType: 'json',
        beforeSend: function () {
            loader.removeClass('d-none');
            masterDropzoneForm();
        },
        success: function (response) {

            let body = $('body');
            body.append(response.html);

            loader.addClass('d-none');

            let modal = body.find('#medias-library-modal');
            modal.modal('show');
            modal.find('.btn-edit').remove();
            modal.find('.btn-zip').remove();

            /** Nestable */
            import('../plugins/nestable').then(({default: nestable}) => {
                new nestable();
            }).catch(error => 'An error occurred while loading the component "plugins/nestable"');

            tooltips();

            modal.on('hidden.bs.modal', function (e) {
                modal.remove();
            });
        },
        error: function (errors) {
            /** Display errors */
            import('../core/errors').then(({default: displayErrors}) => {
                new displayErrors(errors);
            }).catch(error => 'An error occurred while loading the component "errors"');
        }
    });

    /** Media Save file */
    body.on('click', '#save-file-library', function (e) {

        e.preventDefault();

        let loader = $('body').find('#modal-preloader');
        loader.removeClass('d-none');

        import('./save-file').then(({default: saveFile}) => {
            new saveFile(Routing, e, $(this));
        }).catch(error => 'An error occurred while loading the component "media/save-file"');
    });

    body.on('click', '#medias-library-modal .ajax-get-refresh', function (e) {
        e.preventDefault();
        $('body').find('#medias-library-modal .ajax-get-refresh').removeClass('btn-outline-info').addClass('btn-info');
        $(this).removeClass('btn-info').addClass('btn-outline-info');
    });

    /** Media Data wrap */
    body.on('click', '.file-data-wrap', function (e) {
        e.preventDefault();
        import('./data-wrap').then(({default: dataWrap}) => {
            new dataWrap(e, $(this));
        }).catch(error => 'An error occurred while loading the component "media/data-wrap"');
    });

    e.stopImmediatePropagation();
    return false;
}