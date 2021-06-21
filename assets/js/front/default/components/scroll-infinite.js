/**
 *  Scroll infinite
 */
export default function (scroller) {

    let infiniteScroll = function (scroller) {

        let baseUrl = scroller.dataset.href
        let previousScroll = 0
        let loader = document.getElementById('scroller-loader')
        let nextIdentifier = scroller.dataset.next

        if (typeof nextIdentifier == 'undefined') {
            nextIdentifier = scroller.closest('.layout-zone').nextElementSibling
        }

        if (!nextIdentifier) {
            nextIdentifier = scroller.closest('#body-page').nextElementSibling
        }

        if (document.body.contains(scroller) && parseInt(scroller.dataset.scrollActive) === 1) {

            let deviceAgent = navigator.userAgent.toLowerCase()
            let agentID = deviceAgent.match(/(iphone|ipod|ipad)/)
            let maxPage = parseInt(scroller.getAttribute('data-max'))

            window.addEventListener('scroll', function (e) {

                let currentScroll = this.scrollY

                if (currentScroll > previousScroll) {

                    let scrollDistance = window.scrollY + window.innerHeight
                    let next = nextIdentifier ? nextIdentifier : document.getElementById('footer')

                    if (document.body.contains(next)) {
                        let nextDistance = next.offsetTop
                        if (scrollDistance >= nextDistance) {
                            getItems(scroller, baseUrl, maxPage, loader)
                        }
                    } else {
                        if (scrollDistance === document.height || agentID && scrollDistance + 150 > document.height) {
                            getItems(scroller, baseUrl, maxPage, loader)
                        }
                    }
                }

                previousScroll = currentScroll
            })
        }
    }

    infiniteScroll(scroller)

    let getItems = function (scroller, baseUrl, maxPage, loader) {

        let pageID = parseInt(scroller.getAttribute('data-page')) + 1
        let url = baseUrl + "?scroll-ajax=true&page=" + pageID
        let loaded = scroller.getAttribute('data-load')

        if (pageID <= maxPage && parseInt(maxPage) !== 1 && !loaded) {

            scroller.setAttribute('data-load', true)

            let beforeSend = function () {
                loader.classList.remove('d-none')
            }

            let xHttp = new XMLHttpRequest()
            xHttp.open("GET", url, true)
            xHttp.setRequestHeader("Content-Type", "application/json; charset=utf-8")
            beforeSend()
            xHttp.send()
            xHttp.onload = function (e) {
                if (this.readyState === 4 && this.status === 200) {

                    let response = JSON.parse(this.response)
                    let html = document.createElement('div')
                    html.innerHTML = response.html
                    let items = html.getElementsByClassName('results')[0].getElementsByClassName('item')
                    let container = document.getElementById('results')

                    for (let i = 0; i < items.length; i++) {
                        container.insertAdjacentHTML('beforeend', items[i].outerHTML)
                    }

                    loader.classList.add('d-none')
                    scroller.setAttribute('data-page', pageID);
                    scroller.removeAttribute('data-load');
                }
            }
        }
    }
}