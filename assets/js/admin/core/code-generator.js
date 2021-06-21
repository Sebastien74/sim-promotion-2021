import route from "../../vendor/components/routing";

/**
 * Code generator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (event, el) {

    let form = $(el).closest('form');
    let referEls = form.find('.refer-code');
    let referVal = referEls.length === 1 ? referEls.val() : (referEls.length > 1 ? form.find('.refer-code.admin-name').val() : '');
    let referName = referVal.length > 0 ? referVal.replace(/[/]/g, '-') : 'undefined';
    let spinnerIcon = $(el).find('svg');
    let inModal = $(el).closest('modal');

    $.ajax({
        url: route('admin_code_generator', {
            string: referName,
            url: $(el).data('url-id'),
            classname: $(el).data('classname'),
            entityId: $(el).data('entity-id'),
        }),
        type: "GET",
        processData: false,
        contentType: false,
        dataType: 'json',
        async: true,
        beforeSend: function () {
            spinnerIcon.toggleClass('fa-spin');
        },
        success: function (response) {

            if (response.code && response.code !== 'undefined') {

                if ($(el).hasClass('has-code') || inModal.length > 0) {
                    $(el).prev().val(response.code);
                } else {
                    $(el).closest('.url-edit-group').find("input[code='code']").val(response.code);
                }
            }

            spinnerIcon.toggleClass('fa-spin');
        },
        error: function (errors) {

            /** Display errors */
            import('../core/errors').then(({default: displayErrors}) => {
                new displayErrors(errors);
            }).catch(error => 'An error occurred while loading the component "errors"');
        }
    });

    event.stopImmediatePropagation();
    return false;
}