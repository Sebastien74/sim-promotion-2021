/**
 *  Form
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */

import('./form-package').then(({default: formPackage}) => {
    new formPackage()
}).catch(error => 'An error occurred while loading the component "components/form/form-package"')

window.addEventListener("load", function () {

    /** Forms reset */
    let forms = document.querySelectorAll('form')
    for (let i = 0; i < forms.length; i++) {
        forms[i].reset()
    }

    /** To display thanks modal */
    let modals = document.querySelectorAll('.thanks-modal.show')
    for (let i = 0; i < modals.length; i++) {
        modals[i].show()
    }

    /** Set filename on input file change */
    let fileChange = function () {
        let inputsFile = document.querySelectorAll('input[type="file"]')
        for (let i = 0; i < inputsFile.length; i++) {
            let input = inputsFile[i]
            input.addEventListener('change', (event) => {
                let fileName = event.target.files[0].name
                input.setAttribute('placeholder', fileName)
                input.parentNode.getElementsByClassName('custom-file-label')[0].innerHTML = fileName
            })
        }
    }
    fileChange()

    /** For hidden zones as step */
    let formHiddenZones = function (btn, container) {

        let hasHidden = container.querySelectorAll('.layout-zone.d-none')

        if (hasHidden.length > 0) {

            let zones = container.getElementsByClassName('layout-zone')

            if (zones.length > 0) {

                let currentZoneId = btn.closest('.layout-zone').getAttribute('id')
                let zoneLength = zones.length
                let zoneInit = false

                for (let i = 0; i < zones.length; i++) {

                    let index = i + 1
                    let zone = zones[i]
                    let invalidFields = zone.getElementsByClassName('is-invalid')
                    let invalidFeedbacks = zone.getElementsByClassName('invalid-feedback')
                    let haveInvalidFields = invalidFields.length > 0

                    if (!zoneInit && haveInvalidFields && zone.getAttribute('id') !== currentZoneId) {
                        zoneInit = true
                        zone.classList.remove('d-none')
                        for (let j = 0; j < invalidFields.length; j++) {
                            invalidFields[j].classList.remove('is-invalid')
                        }
                        for (let j = 0; j < invalidFeedbacks.length; j++) {
                            invalidFeedbacks[j].classList.remove('invalid-feedback')
                        }
                        window.scrollTo({top: zone.offsetTop - 50, behavior: 'smooth'})
                    } else if (haveInvalidFields && !zone.classList.contains('d-none')) {
                        zoneInit = true
                    } else if (index < zoneLength && !zone.classList.contains('d-none')) {
                        zone.classList.add('d-none')
                    } else if (!zoneInit && index === zoneLength) {
                        zone.classList.remove('d-none')
                    }
                }
            }
        }
    }

    /** Post process */
    let post = function () {

        /** On form submit */
        let submits = document.querySelectorAll('.form [type="submit"]')
        for (let i = 0; i < submits.length; i++) {

            submits[i].onclick = function (event) {

                let alertBlock = document.getElementById('alert-form-block')
                if (alertBlock) {
                    alertBlock.classList.add('d-none')
                }
                let alerts = document.getElementsByClassName('alert-success')
                for (let j = 0; j < alerts.length; j++) {
                    alerts[i].remove()
                }

                let loader = submits[i].closest('.form-container').getElementsByClassName('form-loader')[0]
                let validFiles = checkInputsFiles(submits[i])
                let form = submits[i].closest('form')

                if (!validFiles) {
                    event.preventDefault()
                    loader.classList.add('d-none')
                    return true
                } else if (validFiles && !form.classList.contains('form-ajax')) {
                    form.unbind('submit').submit()
                }

                if (!submits[i].classList.contains('form-ajax')) {
                    loader.classList.remove('d-none')
                }
            }
        }

        /** On Ajax form submit */
        let ajaxSubmits = document.querySelectorAll('.form-ajax [type="submit"]')
        for (let i = 0; i < ajaxSubmits.length; i++) {
            ajaxSubmits[i].onclick = function (event) {

                event.preventDefault()

                let loader = ajaxSubmits[i].closest('.form-container').getElementsByClassName('form-loader')[0]
                let form = ajaxSubmits[i].closest('form')
                let formId = form.getAttribute('id')
                let redirection = form.dataset.redirection
                let formCalendars = document.querySelectorAll('[data-component="form-calendar"]')

                let xHttp = new XMLHttpRequest()
                xHttp.open("POST", form.getAttribute('action'), true)
                xHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
                beforeSend(loader)
                xHttp.send(serialize(form))
                xHttp.onload = function (e) {
                    if (this.readyState === 4 && this.status === 200) {

                        let response = JSON.parse(this.response)

                        if (response.success && typeof redirection != 'undefined' && redirection) {
                            document.location.href = redirection + '?token=' + response.token
                        } else {

                            let html = document.createElement('div')
                            html.innerHTML = response.html
                            let form = html.getElementsByClassName('form-ajax')[0]
                            let container = document.getElementById(formId).closest('.form-container')

                            container.innerHTML = form.closest('.form-container').innerHTML

                            fileChange()
                            formHiddenZones(ajaxSubmits[i], container)

                            if (response.success) {

                                let inputs = document.getElementById(formId).getElementsByClassName('form-control')
                                for (let j = 0; j < inputs.length; j++) {
                                    let el = inputs[j]
                                    if (el.type && el.type === 'checkbox') {
                                        inputs[j].checked = false
                                    } else {
                                        inputs[j].value = ""
                                    }
                                }

                                let checkboxes = document.getElementById(formId).getElementsByClassName('form-check-input')
                                for (let j = 0; j < checkboxes.length; j++) {
                                    let el = checkboxes[j]
                                    if (el.type && el.type === 'checkbox') {
                                        checkboxes[j].checked = false
                                    }
                                }
                            }

                            if (response.dataId) {
                                form.setAttribute('data-custom-id', response.dataId)
                            }

                            if (formCalendars.length > 0) {
                                import('./form-calendar').then(({default: formCalendarRefreshModule}) => {
                                    new formCalendarRefreshModule(e, form)
                                }).catch(error => 'An error occurred while loading the component "components/form/form-calendar"')
                            }

                            if (response.success && response.showModal) {
                                let modalEl = document.getElementById(form.dataset.modal)
                                let cloneModal = modalEl.cloneNode(true)
                                let modal = new bootstrap.Modal(cloneModal)
                                modal.show()
                                setTimeout(function () {
                                    modal.hide()
                                }, 4500)
                            } else if (!response.success && response.message) {
                                let alert = '<div class="alert alert-danger">' + response.message + '</div>'
                                document.getElementById(formId).append(alert)
                            }

                            /** Form packages */
                            import('./form-package').then(({default: formPackage}) => {
                                new formPackage()
                            }).catch(error => 'An error occurred while loading the component "packages/form"')

                            post()
                        }
                    }
                }
            }
        }

        let beforeSend = function (loader) {
            loader.classList.remove('d-none')
            /** Remove errors */
            import('../../../../vendor/components/remove-errors').then(({default: removeErrors}) => {
                new removeErrors()
            }).catch(error => 'An error occurred while loading the component "remove-errors"')
        }

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
    }

    post()

    /** Multiple files validation */
    let checkInputsFiles = function (el) {

        let isValid = true
        let form = el.closest('form')
        let inputsFileMultiple = form.querySelectorAll('input[type="file"][multiple="multiple"]')

        if (inputsFileMultiple.length > 0) {
            let inputs = form.querySelectorAll('input')
            for (let i = 0; i < inputs.length; i++) {
                let input = inputs[i]
                let isRequired = input.hasAttribute('required')
                if (isRequired && !input.value || isRequired && input.type === 'checkbox' && !input.checked) {
                    isValid = false
                    document.getElementById('alert-form-block').classList.remove('d-none')
                }
            }
        }

        return isValid
    }
})