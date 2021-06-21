/**
 *  Async resources
 *
 *  @copyright 2020
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 *  @version 1.0
 *
 *  Licensed under the MIT License (LICENSE.txt).
 */

let onLoadStylesheets = function () {
    let head = document.getElementsByTagName('head');
    let stylesheets = head[0].querySelectorAll("link[as='style']");
    stylesheets.forEach((stylesheet, index) => {
        stylesheet.setAttribute('rel', 'stylesheet');
        stylesheet.removeAttribute('as');
    });
};

let onLoadJavaScripts = function () {

    let scripts = document.querySelectorAll('[data-as="script"]');
    let comment = null;

    for (let index = 0; index < scripts.length; ++index) {

        if (scripts[index].getAttribute('data-comment') !== comment) {
            comment = scripts[index].getAttribute('data-comment');
            let commentEl = document.createComment(comment + " javaScript");
            document.body.appendChild(commentEl);
        }

        scripts[index].removeAttribute('data-comment');

        let usedLaterScript = document.createElement('script');
        usedLaterScript.defer = true;
        usedLaterScript.src = scripts[index].getAttribute('data-href');
        usedLaterScript.setAttribute('nonce', scripts[index].getAttribute('data-nonce'));
        usedLaterScript.setAttribute('crossorigin', scripts[index].getAttribute('data-crossorigin'));
        document.body.appendChild(usedLaterScript);

        scripts[index].remove();
    }
};

window.addEventListener("load", function () {
    onLoadStylesheets();
    onLoadJavaScripts();
});