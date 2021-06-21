/**
 *  Scroll event
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */

window.addEventListener("load", function () {

    let btn = document.getElementById('scroll-top-btn')

    /** To scroll to top of the page */
    let showBtn = function (btn) {
        if (window.scrollY > 300) {
            if (!btn.classList.contains('show')) {
                btn.classList.add('show');
            }
        } else {
            btn.classList.remove('show');
        }
    }

    if (document.body.contains(btn)) {

        showBtn(btn)

        window.onscroll = function () {
            showBtn(btn)
        };

        btn.onclick = function (event) {
            event.preventDefault()
            window.scrollTo({top: 0, behavior: 'smooth'})
        }
    }

    /** To scroll to element */
    let links = document.getElementsByClassName('scroll-link')
    for (let i = 0; i < links.length; i++) {
        links[i].onclick = function (event) {
            let el = this
            let target = document.querySelectorAll(el.dataset.target)
            if (typeof target != 'undefined') {
                event.preventDefault()
                window.scrollTo({top: target[0].offsetTop, behavior: 'smooth'})
            }
        }
    }
})