import "aos/dist/aos.css"

const AOS = require("aos")

/**
 *  AOS Plugin effects
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    AOS.init({
        duration: 800
    })

    onElementHeightChange(document.body, function () {
        AOS.refresh()
    })

    function onElementHeightChange(elm, callback) {

        let lastHeight = elm.clientHeight
        let newHeight

        (function run() {

            newHeight = elm.clientHeight
            if (lastHeight !== newHeight) callback()
            lastHeight = newHeight

            if (elm.onElementHeightChangeTimer) {
                clearTimeout(elm.onElementHeightChangeTimer);
            }

            elm.onElementHeightChangeTimer = setTimeout(run, 200)
        })()
    }
}