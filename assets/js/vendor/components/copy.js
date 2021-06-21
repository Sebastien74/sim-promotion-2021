/**
 *  Copy
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    $(function () {

        $('body').on('click', '.copy-link', function () {
            let el = $(this);
            let refer = el.closest('.refer-copy');
            let text = refer.find('.to-copy').text();
            copyText(text, refer);
        });

        let copyText = function (text, refer) {
            let $temp = $("<input>");
            refer.append($temp);
            $temp.val(text).select();
            document.execCommand("copy");
            $temp.remove();
        }
    });
};