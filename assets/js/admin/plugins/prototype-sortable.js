/**
 *  Prototypes sortable
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (body) {

    let sortables = body.find('.prototype-sortable');

    sortables.each(function () {

        let el = $(this);
        let asDeletable = el.find('.swal-delete-link');
        let loader = el.find('.prototype-preloader');
        let itemsClass = el.find('.prototype-block-group').length > 0 ? '.prototype-block-group' : '.prototype-block';

        if (asDeletable.length > 0) {
            el.find('.prototype-block').addClass('has-deletable');
        }

        let sortable = el.sortable({
            placeholder: "ui-state-highlight",
            items: itemsClass,
            handle: ".handle-item-prototype",
            start: function (e, ui) {
                ui.placeholder.width(ui.item.width());
                ui.placeholder.height(ui.item.height());
            },
            update: function (event, ui) {

                loader.removeClass('d-none');
                let items = el.find('.handle-item-prototype');

                $('[data-toggle="tooltip"]').tooltip('hide');
                items.each(function (i, el) {
                    let elementId = $(el).attr('id');
                    let item = $('#' + elementId);
                    item.attr('data-position', (i + 1));
                    item.addClass('in-progress');
                });

                items.each(function (i) {

                    let item = $(this);
                    let position = item.attr('data-position');
                    let path = item.data('path');

                    let url = path + '?ajax=true&position=';
                    if (path.indexOf('?') > -1) {
                        url = path + '&ajax=true'
                    }

                    $.ajax({
                        url: url + '&position=' + position,
                        type: "GET",
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        beforeSend: function () {
                        },
                        success: function () {
                            item.removeClass('in-progress');
                            if ($('body').find('.handle-item-prototype.in-progress').length === 0) {
                                loader.addClass('d-none');
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
            }
        });

        sortable.disableSelection();
    });
}