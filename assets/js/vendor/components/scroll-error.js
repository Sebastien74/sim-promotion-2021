/**
 *  Scroll to errors
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    let scroll = false
    let errors = document.getElementsByClassName('invalid-feedback')

    let scrollToError = function (el) {

        let elOffset = el.getBoundingClientRect().top + window.scrollY
        let elHeight = el.offsetHeight
        let windowHeight = window.innerHeight
        let offset

        if (elHeight < windowHeight) {
            offset = elOffset - ((windowHeight / 2) - (elHeight / 2))
        } else {
            offset = elOffset
        }

        window.scrollTo({top: offset, behavior: 'smooth'})
    };

    if (!scroll && errors.length > 0) {

        let el = errors[0]
        let isCollapse = el.closest('.collapse')
        let isTab = el.closest('.tab-pane')

        if (isCollapse) {
            let collapseId = isCollapse.getAttribute('id')
            document.querySelectorAll("*[data-target='#" + collapseId + "']")[0].click()
        }

        if (isTab) {

            let target = isTab.getAttribute('aria-labelledby')
            document.getElementById(target).click()
            el = document.getElementById(isTab.getAttribute('id')).getElementsByClassName('invalid-feedback')[0]

            setTimeout(function () {
                scrollToError(el)
                scroll = true
            }, 200)

        } else {
            scrollToError(el)
            scroll = true
        }
    }
}

