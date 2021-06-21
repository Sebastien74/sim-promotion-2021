import route from "../../vendor/components/routing";

/**
 *  Medias sortable
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    let loader = $('body').find('#medias-sortable-preloader');

    let sortable = $('#medias-sortable-container').sortable({
        placeholder: "ui-state-highlight",
        items: '.sortable-item',
        handle: ".handle-item",
        start: function (e, ui) {
            ui.placeholder.height(ui.item.height());
        },
        update: function (event, ui) {

            loader.removeClass('d-none');

            let body = $('body');
            let items = body.find('.sortable-item');
            let website = body.data('id');

            $('[data-toggle="tooltip"]').tooltip('hide');

            items.each(function (i, el) {
                let elementId = $(el).attr('id');
                $('#' + elementId).attr('data-position', (i + 1));
            });

            items.each(function (i) {

                let item = $(this);
                let position = item.attr('data-position');
                let elData = item.find('.data-locales');

                elData.find('.media-locale-data').each(function () {

                    let el = $(this);
                    let url = route('admin_mediarelation_position', {
                        website: website,
                        mediaRelation: el.data('id'),
                        position: position
                    });

                    $.ajax({
                        url: url,
                        type: "GET",
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        beforeSend: function () {
                        },
                        success: function (response) {
                            el.removeClass('active');
                            let body = $('body');
                            let othersToProcess = body.find('.media-locale-data.active');
                            if (othersToProcess.length === 0) {
                                loader.addClass('d-none');
                                body.find('.media-locale-data').addClass('active');
                            }
                        },
                        error: function (errors) {

                            /** Display errors */
                            import('../core/errors').then(({default: displayErrors}) => {
                                new displayErrors(errors);
                            }).catch(error => 'An error occurred while loading the component "errors"');
                        }
                    });
                });
            });
        }
    });

    // sortable.disableSelection();
}