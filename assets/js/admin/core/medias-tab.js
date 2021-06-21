import dropifyJS from "../form/dropify";
import summerNote from "../plugins/summernote";
import select2 from "../../vendor/plugins/select2";
import '../bootstrap/js/dist/tooltip';

export default function (el) {

    let body = $('body');

    $('html, body').animate({scrollTop: $(el).offset().top - 50}, 800);

    if (!$(el).hasClass('active')) {

        let target = $(el).data('target');
        let contentWrap = body.find(target).find('.card-body');

        $.ajax({
            url: $(el).data('path') + "?ajax=true",
            type: "GET",
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function () {
                $(el).addClass('active');
            },
            success: function (response) {
                if (response.html) {

                    contentWrap.html(response.html);

                    dropifyJS();
                    summerNote();
                    select2();

                    $('[data-toggle="tooltip"]').tooltip();

                    $('html, body').animate({scrollTop: $(el).offset().top - 50}, 800);
                }
            },
            error: function (errors) {
                /** Display errors */
                import('./errors').then(({default: displayErrors}) => {
                    new displayErrors(errors);
                }).catch(error => 'An error occurred while loading the component "errors"');
            }
        });
    }
}