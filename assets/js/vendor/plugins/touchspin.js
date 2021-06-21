import '../../admin/lib/jquery.bootstrap-touchspin';

/**
 *  Touchspin
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (inputs) {

    $(function () {

        let touchSpins = inputs ? inputs : $("input[type='number']");

        if (touchSpins.length > 0) {

            touchSpins.each(function () {

                let input = $(this);
                input.TouchSpin({
                    min: input.attr('min') ? input.attr('min') : 0,
                    max: input.attr('max') ? input.attr('max') : 1000000000
                });
            });
        }
    });
}