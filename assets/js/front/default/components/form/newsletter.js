/**
 *  Newsletter form
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */

document.addEventListener('DOMContentLoaded', function () {

    /** Reset inputs */
    let resetInputs = function () {
        let inputsEmail = document.getElementsByClassName('newsletter-form-email')
        for (let i = 0; i < inputsEmail.length; i++) {
            inputsEmail[i].setAttribute('value', '')
        }
        let inputsExternalEmail = document.getElementsByClassName('external-input-email')
        for (let i = 0; i < inputsExternalEmail.length; i++) {
            inputsExternalEmail[i].setAttribute('value', '')
        }
    }

    resetInputs()

    /** Events */
    let formsEvents = function() {

        let forms = document.getElementsByClassName('newsletter-form')

        if (forms.length > 0) {

            for (let i = 0; i < forms.length; i++) {
                forms[i].addEventListener('keydown', function (event) {
                    if (event.key === "Enter") {
                        sendRequest(event, this)
                        return false
                    }
                })
            }

            let submits = document.getElementsByClassName('newsletter-submit')
            for (let i = 0; i < submits.length; i++) {
                submits[i].onclick = function (event) {
                    sendRequest(event, this.closest('form'))
                }
            }
        }
    }

    formsEvents()

    function sendRequest(event, form) {

        event.preventDefault()

        let icon = form.getElementsByClassName('newsletter-submit')[0].querySelector('svg')
        let iconSpinner = form.getElementsByClassName('spinner-border')[0]
        let containerId = form.closest('.newsletter-form-container').getAttribute('id')

        let beforeSend = function () {
            /** Remove errors */
            import('../../../../vendor/components/remove-errors').then(({default: removeErrors}) => {
                new removeErrors()
            }).catch(error => 'An error occurred while loading the component "remove-errors"')
            iconSpinner.classList.remove('d-none')
            icon.classList.add('d-none')
        }

        let xHttp = new XMLHttpRequest()
        xHttp.open("POST", form.getAttribute('action'), true)
        xHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
        beforeSend()
        xHttp.send(serialize(form))
        xHttp.onload = function (e) {

            if (this.readyState === 4 && this.status === 200) {

                let response = JSON.parse(this.response)

                if (response.success) {
                    externalCampaign(document.getElementsByClassName('newsletter-form-email')[0], form)
                }

                document.getElementById(containerId).outerHTML = response.html

                formsEvents()

                /** On keyup on field */
                import('../../../../vendor/components/keyup-fields').then(({default: keyupFields}) => {
                    new keyupFields()
                }).catch(error => 'An error occurred while loading the component "keyup-fields"')

                if (response.success && response.showModal) {
                    let modal = new bootstrap.Modal(document.getElementById(form.dataset.modal))
                    modal.show()
                    setTimeout(function () {
                        modal.hide()
                    }, 4500)
                }

                if (response.success) {
                    resetInputs()
                }
            }
        }
    }
})

/** Serialize form data */
let serialize = function (form) {
    let serialized = []
    for (let i = 0; i < form.elements.length; i++) {
        let field = form.elements[i]
        if (!field.name || field.disabled || field.type === 'file' || field.type === 'reset' || field.type === 'submit' || field.type === 'button') continue
        if (field.type === 'select-multiple') {
            for (let n = 0; n < field.options.length; n++) {
                if (!field.options[n].selected) continue
                serialized.push(encodeURIComponent(field.name) + "=" + encodeURIComponent(field.options[n].value))
            }
        } else if ((field.type !== 'checkbox' && field.type !== 'radio') || field.checked) {
            serialized.push(encodeURIComponent(field.name) + "=" + encodeURIComponent(field.value))
        }
    }
    return serialized.join('&')
}

/** External Campaign process */
let externalCampaign = function (emailField, form) {

    let externalForm = form.closest('.newsletter-form-container').getElementsByClassName('external-campaign-form')
    let campaignAction = externalForm && typeof externalForm.length > 0 ? externalForm[0].getAttribute('action') : false

    if (campaignAction) {

        externalForm[0].getElementsByClassName('external-input-email')[0].setAttribute('value', emailField.getAttribute('value'))

        let xHttp = new XMLHttpRequest()
        xHttp.open("GET", campaignAction, true)
        xHttp.setRequestHeader("Content-Type", "application/json; charset=utf-8")
        xHttp.send(serialize(externalForm[0]))
    }
}