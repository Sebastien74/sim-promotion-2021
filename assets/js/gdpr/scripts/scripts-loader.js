import route from "../../vendor/components/routing"

/**
 *  Script loader
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (reloadActivation = false, saveIcon = false, reload = false) {

    let xHttp = new XMLHttpRequest()
    xHttp.open("GET", route('front_gdpr_scripts', {_format: 'json'}), false)
    xHttp.setRequestHeader("Content-Type", "application/json")
    xHttp.onload = function (e) {
        if (this.readyState === 4 && this.status === 200) {

            let response = JSON.parse(this.response)
            let head = document.head
            let body = document.body
            let html = document.documentElement
            let headerScripts = response.headerScripts
            let bodyPrependScripts = response.bodyPrependScripts
            let bodyAppendScripts = response.bodyAppendScripts
            let scripts = html.querySelectorAll('script')
            let preloader = document.getElementById('main-preloader')

            let analyticsScripts = html.getElementsByClassName('analytics-script')
            for (let i = 0; i < analyticsScripts.length; i++) {
                analyticsScripts[i].remove()
            }

            for (let i = 0; i < scripts.length; i++) {

                let script = scripts[i]
                let src = script.getAttribute('src')

                if (src && src.toLowerCase().indexOf('google-analytics') >= 0) {
                    script.remove()
                }

                if (src && src.toLowerCase().indexOf('googletagmanager') >= 0) {
                    script.remove()
                }
            }

            if (reloadActivation && response.reload || reload) {
                if (preloader) {
                    preloader.classList.remove('d-none')
                    preloader.classList.remove('disappear')
                }
                location.reload()
            }

            if (headerScripts.trim()) {
                /** Create object html */
                let prependNode = document.createElement('div')
                prependNode.innerHTML = headerScripts.trim()
                /** Create script elem */
                let scr = document.createElement("script")
                scr.type = "text/javascript"
                scr.text = prependNode.textContent
                /** Inject */
                head.appendChild(scr)
            }

            if (bodyAppendScripts.trim()) {
                body.innerHTML += bodyAppendScripts.trim()
            }

            if (bodyPrependScripts.trim()) {
                let prependNode = document.createElement('div')
                prependNode.innerHTML = bodyPrependScripts.trim()
                body.prepend(prependNode.children[0])
            }

            let saveIconEl = saveIcon ? document.getElementById(saveIcon.getAttribute('id')) : ''
            if (saveIcon && saveIconEl) {
                if (preloader) {
                    preloader.classList.remove('d-none')
                    preloader.classList.remove('disappear')
                }
                location.reload()
            }
        }
    }
    xHttp.send()
}