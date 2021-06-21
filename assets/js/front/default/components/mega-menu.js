/**
 *  Mega menu
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    $('.navbar-toggler').on('click', function () {
        $('body').toggleClass('menu-open');
        $(this).closest('nav').toggleClass('open');
        $('#nav-toggler-icon').toggleClass('open');
    });

    function dropdownAddCol() {

        let windowWidth = $(window).width();
        let dropdowns = $('.mega-dropdown-menu');

        if (windowWidth > 767 && windowWidth <= 1200) {
            dropdowns.not('row').addClass('row');
            dropdowns.find('.level-2').not('col').addClass('col');
        } else {
            dropdowns.removeClass('row');
            dropdowns.find('.level-2').removeClass('col');
        }
    }

    dropdownAddCol();

    $(window).resize(function () {
        dropdownAddCol();
    });
}