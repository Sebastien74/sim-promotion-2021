import tagsInput from './bootstrap-tagsinput';
import sortable from './sortable';
import colorPicker from "./colorpicker";
import tooltips from "./tooltips";
import treeSelect from "./tree-select";
import summernote from "./summernote";

/**
 *  Plugins
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 *
 *  1 - Summernote
 *  2 - Nestable
 *  3 - Medias sortable
 *  4 - Prototypes sortable
 *  5 - CSV Table export
 *  6 - Sweet alert on delete
 *  7 - Sweet alert on click
 *  8 - Data table
 *  9 - Color picker
 *  10 - Tooltips
 *  11 - Tree Select
 *  12 - Sortable
 *  13 - Tag input
 */

let body = document.body

/** 1 - Summernote */
summernote();

/** 2 - Nestable */
let nestableEls = body.querySelectorAll('.nestable-list-container');
if (nestableEls.length > 0) {
    import('./nestable').then(({default: nestableModule}) => {
        new nestableModule();
    }).catch(error => 'An error occurred while loading the component "plugins/nestable"');
}

/** 3 - Medias sortable */
let mediasSortableEls = body.querySelectorAll('#medias-sortable-container');
if (mediasSortableEls.length > 0) {
    import('./medias-sortable').then(({default: mediasSortable}) => {
        new mediasSortable();
    }).catch(error => 'An error occurred while loading the component "plugins/medias-sortable"');
}

/** 4 - Prototypes sortable */
let prototypesSortableEls = body.querySelectorAll('.prototype-sortable');
if (prototypesSortableEls.length > 0) {
    import('./prototype-sortable').then(({default: prototypesSortable}) => {
        new prototypesSortable($(body));
    }).catch(error => 'An error occurred while loading the component "plugins/prototype-sortable"');
}

/** 5 - CSV Table export */
$(body).on('click', '.csv-export', function () {
    import('./csv-table').then(({default: csvTable}) => {
        new csvTable($(this));
    }).catch(error => 'An error occurred while loading the component "plugins/csv-table"');
});

/** 6 - Sweet alert on delete */
$(body).on('click', '.swal-delete-link', function (e) {
    e.preventDefault();
    import('./sweet-alert').then(({default: sweetAlert}) => {
        new sweetAlert(e, $(this));
    }).catch(error => 'An error occurred while loading the component "plugins/sweet-alert"');
});

/** 7 - Sweet alert on click */
$(body).on('click', '.swal-link-confirm', function (e) {
    e.preventDefault();
    import('./sweet-alert-link').then(({default: sweetAlert}) => {
        new sweetAlert(e, $(this));
    }).catch(error => 'An error occurred while loading the component "plugins/sweet-alert-link"');
});

/** 8 - Data table */
if (body.querySelectorAll('.data-table').length > 0) {
    import('./data-tables').then(({default: dataTables}) => {
        new dataTables();
    }).catch(error => 'An error occurred while loading the component "plugins/data-tables"');
}

/** 9 - Color picker */
colorPicker();

/** 10 - Tooltips */
tooltips();

/** 11 - Tree Select */
treeSelect();

/** 12 - Sortable */
sortable();

/** 13 - Tag input */
tagsInput();