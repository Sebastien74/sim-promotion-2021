/**
 *  Services
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    let cookieName = 'felixCookies'
    let cookiesArray = []
    let activesCookies = document.getElementsByClassName('active-gdpr-cookie')

    for (let i = 0; i < activesCookies.length; i++) {

        let el = activesCookies[i]

        el.onclick = function (e) {

            e.preventDefault()

            let FelixCookies = Cookies.get(cookieName)
            let service = el.dataset.service
            let code = el.dataset.code
            let reload = el.dataset.reload

            /** Services activation */
            import('./services-activation').then(({default: activeService}) => {
                new activeService(service, code, true)
            }).catch(error => 'An error occurred while loading the component "gdpr-services-activation"')

            if (FelixCookies) {

                Object.entries(JSON.parse(FelixCookies)).forEach(([key, cookie]) => {
                    let slug = cookie.slug
                    let status = slug === code ? true : cookie.status
                    if (slug !== code) {
                        cookiesArray.push({slug: slug, status: status});
                    }
                })

                /** Cookie remove */
                import('../cookies/cookie-remove').then(({default: removeCookie}) => {
                    new removeCookie(cookieName)
                }).catch(error => 'An error occurred while loading the component "gdpr-cookie-remove"')
            }

            cookiesArray.push({slug: code, status: true})

            /** Cookie create */
            import('../cookies/cookie-create').then(({default: createCookie}) => {
                new createCookie(cookieName, JSON.stringify(cookiesArray))
            }).catch(error => 'An error occurred while loading the component "gdpr-cookie-create"')

            if (reload) {
                setTimeout(function () {
                    location.reload()
                }, 100)
            }
        }
    }
};