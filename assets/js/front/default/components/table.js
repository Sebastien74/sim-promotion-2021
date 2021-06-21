// import '../../../../scss/front/default/components/_table.scss';

/**
 *  To generate responsive tables
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (tables) {

    responsiveTables(tables);

    window.onresize = function () {
        responsiveTables(tables);
    }

    function responsiveTables(tables) {

        for (let i = 0; i < tables.length; i++) {

            let table = tables[i]
            let inBody = table.closest('.body.header-table')

            if (inBody.length > 0 && window.innerWidth < 992) {

            }
        }

        // tables.each(function () {
        //
        //     let table = $(this);
        //     let inBody = table.closest('.body.header-table');
        //
        //     if (inBody.length > 0 && $(window).width() < 992) {
        //
        //         let head = table.find('tr:first-child');
        //         let cols = head.find('td');
        //         let colsCount = cols.length;
        //         let width = 100 / colsCount;
        //
        //         let headElements = {};
        //         for (let i = 0; i < cols.length; i++) {
        //             if (typeof headElements['td' + i] === "object") {
        //                 headElements['td' + i].push($(cols[i]).text());
        //             } else if (headElements['td' + i]) {
        //                 headElements['td' + i] = [];
        //                 headElements['td' + i].push($(cols[i]).text());
        //             } else {
        //                 headElements['td' + i] = $(cols[i]).text();
        //             }
        //         }
        //
        //         table.find('tr').each(function (i) {
        //             if (i > 0) {
        //                 $(this).find('td').each(function (j) {
        //
        //                     let col = $(this);
        //                     let html = '<div class="content">' + col.html() + '</div>';
        //
        //                     col.html(html);
        //                     col.attr('data-title', headElements['td' + j]);
        //                 });
        //             }
        //         });
        //
        //         table.addClass('table-responsive body-table');
        //         table.find('td').attr('scope', 'col').css('width', width + '%').addClass('d-inline-block');
        //     } else {
        //
        //         table.removeClass('table-responsive');
        //         table.removeClass(' body-table');
        //         table.find('td').attr('scope', 'col').css('width', 'initial').removeClass('d-inline-block');
        //     }
        // });
    }
}