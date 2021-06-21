/**
 *  Log errors
 */
export default function () {

    let isDebug = $('html').data('debug');

    if (!isDebug) {

        window.onerror = function (messageOrEvent, source, lineno, colno, error) {

            try {

                if (source !== '' && lineno !== 0 && colno !== 0) {

                    let url = Routing.generate('javascript_errors_logger', {
                        browser: encodeURIComponent(((navigator.userAgent + '|' + navigator.vendor + '|' + navigator.platform + '|' + navigator.platform) || '').toString().substring(0, 150)),
                        message: encodeURIComponent((messageOrEvent || '').toString().substring(0, 150)),
                        source: encodeURIComponent((source || '').toString().substring(0, 150)),
                        line: encodeURIComponent((lineno || '').toString().substring(0, 150)),
                        col: encodeURIComponent((colno || '').toString().substring(0, 150)),
                        url: encodeURIComponent((window.location.href || '').toString().substring(0, 150))
                    });

                    url = url.indexOf(window.location.host) === -1 ? url.replace(location.protocol, "") : url;

                    $.ajax({
                        url: url,
                        type: "GET",
                        processData: false,
                        contentType: false,
                        dataType: 'json'
                    });
                }

                console.log(messageOrEvent);

            } catch (e) {
                console.log(e);
            }

            return true;
        };
    }
}