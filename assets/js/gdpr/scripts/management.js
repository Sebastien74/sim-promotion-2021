/**
 *  Management
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (modal = null) {

    let activeModalTrigger = function (element, status) {
        let el = document.getElementById(element)
        if (el) {
            el.onclick = function (e) {
                e.preventDefault()
                let checkboxes = document.getElementsByClassName('cookie-group-checkbox')
                for (let i = 0; i < checkboxes.length; i++) {
                    checkboxes[i].checked = status
                }
                let choiceValidate = document.getElementById('gdpr-choices-validate')
                if (choiceValidate) {
                    choiceValidate.click()
                }
            }
        }
    }

    let cookieName = 'felixCookies'
    let saveIcon = null
    let choiceValidate = document.getElementById('gdpr-choices-validate')
    if (choiceValidate) {
        saveIcon = choiceValidate.getElementsByClassName('spinner-border')[0]
    }

    activeModalTrigger('gdpr-all-allowed', true)
    activeModalTrigger('gdpr-all-disallowed', false)

    let checkboxes = document.getElementsByClassName('cookie-group-checkbox')
    if (checkboxes) {

        for (let i = 0; i < checkboxes.length; i++) {

            let el = checkboxes[i]

            el.onchange = function () {

                let elId = el.getAttribute('id')
                let code = el.dataset.code
                let elements = document.querySelectorAll("input[data-code='" + code + "']")

                for (let j = 0; j < elements.length; j++) {
                    let elt = elements[j]
                    let eltId = elt.getAttribute('id')
                    if (eltId !== elId) {
                        elt.checked = el.checked
                    }
                }
            }
        }
    }

    if (choiceValidate) {

        choiceValidate.onclick = function (e) {

            let cookiesArray = []
            let haveDenied = false
            let haveIcon = saveIcon && saveIcon.length > 0
            let checkboxes = document.getElementsByClassName('cookie-group-checkbox')

            if (haveIcon) {
                saveIcon.classList.remove('d-none')
            }

            for (let i = 0; i < checkboxes.length; i++) {

                let el = checkboxes[i]
                let code = el.dataset.code
                let service = el.dataset.service
                let isConsent = el.checked

                cookiesArray.push({slug: code, status: isConsent})

                if (!isConsent) {
                    /** Cookies remove */
                    import('../cookies/cookies-remove').then(({default: removeCookiesDB}) => {
                        new removeCookiesDB(code)
                    }).catch(error => 'An error occurred while loading the component "gdpr-cookies-remove"')
                    haveDenied = true
                }

                if (service !== "") {
                    /** Services activation */
                    import('./services-activation').then(({default: activeService}) => {
                        new activeService(service, code, isConsent)
                    }).catch(error => 'An error occurred while loading the component "gdpr-services-activation"')
                }
            }

            /** Cookie create */
            import('../cookies/cookie-create').then(({default: createCookie}) => {
                new createCookie(cookieName, JSON.stringify(cookiesArray))
            }).catch(error => 'An error occurred while loading the component "gdpr-cookie-create"')

            if (modal) {
                let body = document.body
                body.classList.remove('gdpr-modal-open')
                body.classList.remove('modal-open')
                modal.hide()
                document.getElementById('gdpr-modal').remove()
            }

            /** Scripts loader */
            import('./scripts-loader').then(({default: scriptsLoader}) => {
                new scriptsLoader(true, saveIcon, haveDenied)
            }).catch(error => 'An error occurred while loading the component "gdpr-scripts-loader"')
        }
    }
}