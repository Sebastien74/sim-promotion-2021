/**
 *  Tooltips
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    let tooltips = $('[data-toggle="tooltip"]');

    tooltips.tooltip();

    tooltips.click(function () {
        tooltips.tooltip("hide");
    });
};