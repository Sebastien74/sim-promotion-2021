import route from "../../vendor/components/routing"

/**
 *  Cookies remove
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (groupSlug) {

    let xHttp = new XMLHttpRequest()
    xHttp.open("GET", route('front_gdpr_cookies_db', {slug: groupSlug, _format: 'json'}), true)
    xHttp.setRequestHeader("Content-Type", "application/json; charset=utf-8")
    xHttp.send()
    xHttp.onload = function (e) {
        if (this.readyState === 4 && this.status === 200) {
            let response = JSON.parse(this.response)
            let cookies = response.cookies
            cookies.forEach(function (name) {
                /** Cookie remove */
                import('../cookies/cookie-remove').then(({default: removeCookie}) => {
                    new removeCookie(name)
                }).catch(error => 'An error occurred while loading the component "gdpr-cookie-remove"')
            })
        }
    }
}