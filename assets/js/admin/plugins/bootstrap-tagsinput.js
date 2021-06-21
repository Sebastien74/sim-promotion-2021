/**
 *  Bootstrap tags input
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    $('.bootstrap-tagsinput input').keydown(function (event) {
        if (event.which == 13) {
            $(this).blur();
            $(this).focus();
            return false;
        }
    });
}
