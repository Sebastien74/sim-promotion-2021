/**
 * Admin Core
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 *
 *  1 - Core
 *  2 - Scroll to errors
 *  3 - Ajax GET refresh
 *  4 - Remove saying href attribute
 *  5 - Close command console
 */

/** 1 - Core */
import './popper.min';
import './bootstrap.min';
import './perfect-scrollbar.jquery.min';
import './sidebarmenu';
import './sticky-kit';
import './jquery.sparkline.min';
import './custom';
import './tree-list';
import 'simplebar';

/** 2 - Scroll to errors */
let errors = $(document).find('.invalid-feedback');
if (errors.length > 0) {
    import('../../vendor/components/scroll-error').then(({default: scrollErrors}) => {
        new scrollErrors();
    }).catch(error => 'An error occurred while loading the component "scroll-error"');
}

/** 3 - Ajax GET refresh */
import('./ajax-get').then(({default: ajaxGet}) => {
    new ajaxGet();
}).catch(error => 'An error occurred while loading the component "ajax-get"');

/** 4 - Remove saying href attribute */
$('#saying').find('a').removeAttr('href').addClass('text-info');

/** 5 - Close command console */
$(document).on('click', '.close-console', function () {
    $("#coresphere_consolebundle_console").fadeOut();
});