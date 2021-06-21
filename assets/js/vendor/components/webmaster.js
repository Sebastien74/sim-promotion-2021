import "../../../scss/vendor/components/_webmaster.scss"

import usersSwitcher from "../../security/switcher"

/**
 *  Webmaster toolbox
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */

window.addEventListener("load", function () {

    let body = document.body
    let isUserBack = body.dataset.userback

    if (typeof isUserBack != 'undefined' && isUserBack) {

        usersSwitcher()

        /** Dropdown button event */
        let dropdownEvents = function () {

            let tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]')
            for (let i = 0; i < tooltips.length; i++) {
                if(tooltips[i].id !== ' webmaster-box-wrapper') {
                    new bootstrap.Tooltip(tooltips[i])
                }
            }

            let box = document.getElementById('webmaster-box-wrapper')
            if (box) {
                let tooltipBox = new bootstrap.Tooltip(box)
                let dropdown = document.getElementById("webmaster-box-dropdown")
                dropdown.addEventListener("mouseenter", function (event) {
                    if (!dropdown.classList.contains('show')) {
                        dropdown.click()
                        tooltipBox.hide()
                        tooltipBox.disable()
                    }
                })
            }
        }

        /** Toolbox */
        let xHttp = new XMLHttpRequest()
        let toolbox = document.getElementById('data-tool-box')
        if(toolbox) {
            xHttp.open("GET", toolbox.dataset.path, true)
            xHttp.setRequestHeader("Content-Type", "application/json")
            xHttp.onload = function (e) {
                if (this.readyState === 4 && this.status === 200) {
                    let response = JSON.parse(this.response)
                    if (response.html) {

                        /** Toolbox */
                        let toolbox = document.createElement('div')
                        toolbox.innerHTML = response.html
                        document.body.appendChild(toolbox)

                        /** Button edition */
                        let webmasterBtn = document.getElementById("webmaster-edit-btn")
                        if (webmasterBtn) {
                            webmasterBtn.addEventListener("click", function (event) {
                                body.classList.toggle('editor')
                                event.preventDefault()
                            }, false)
                        }

                        dropdownEvents()
                        usersSwitcher()
                    }
                }
            }
            xHttp.send()
        }

        /** Translations block clone modal */
        document.getElementsByClassName('edit-trans-block').forEach(function (block) {

            let modal = block.getElementsByClassName('modal')[0]
            let form = modal.getElementsByClassName('trans-edit-form')[0]
            let formContainerId = form.getAttribute('id')
            document.body.append(modal.cloneNode(true))
            modal.remove()

            let formContainer = document.getElementById(formContainerId)
            let outer = formContainer.innerHTML
            let regex = new RegExp('<' + formContainer.tagName, 'i')
            let replacementTag = 'form'
            let newTag = outer.replace(regex, '<' + replacementTag)

            regex = new RegExp('</' + formContainer.tagName, 'i')
            newTag = newTag.replace(regex, '</' + replacementTag)
            formContainer.innerHTML = newTag
        })

        /** Modal translations btn hover */
        let modalButtons = document.getElementsByClassName('edit-trans-btn-modal')
        for (let i = 0; i < modalButtons.length; i++) {
            modalButtons[i].addEventListener('mouseover', function () {
                let modal = new bootstrap.Modal(document.getElementById(this.dataset.target), {
                    backdrop: false,
                })
                modal.show()
                if (!body.classList.contains('trans-edit-mode')) {
                    body.classList.add('trans-edit-mode')
                }
            }, false)
        }

        /** Serialize form data */
        let serialize = function (form) {
            let serialized = []
            for (let i = 0; i < form.elements.length; i++) {
                let field = form.elements[i]
                if (!field.name || field.disabled || field.type === 'file' || field.type === 'reset' || field.type === 'submit' || field.type === 'button') continue
                if (field.type === 'select-multiple') {
                    for (let n = 0; n < field.options.length; n++) {
                        if (!field.options[n].selected) continue;
                        serialized.push(encodeURIComponent(field.name) + "=" + encodeURIComponent(field.options[n].value))
                    }
                } else if ((field.type !== 'checkbox' && field.type !== 'radio') || field.checked) {
                    serialized.push(encodeURIComponent(field.name) + "=" + encodeURIComponent(field.value))
                }
            }
            return serialized.join('&')
        }

        /** Modal translations form submit */
        let transSaves = document.getElementsByClassName('trans-edit-save')
        for (let i = 0; i < transSaves.length; i++) {

            transSaves[i].addEventListener("click", function (event) {

                event.preventDefault()

                let btn = this
                let icon = btn.getElementsByTagName('svg')[0]
                let spinner = btn.getElementsByClassName('spinner-border')[0]
                let form = btn.closest('form')
                let inputContent = form.querySelectorAll('input[name="content"]')[0]

                icon.classList.add('d-none')
                spinner.classList.remove('d-none')
                inputContent.classList.remove('is-valid')
                inputContent.classList.remove('is-invalid')

                if (document.querySelector('.error') !== null) {
                    document.getElementsByClassName('error')[0].remove()
                }

                let xHttp = new XMLHttpRequest()
                xHttp.open("POST", form.getAttribute('action') + '?' + serialize(document.getElementById(form.getAttribute('id'))), true)
                xHttp.setRequestHeader("Content-Type", "x-www-form-urlencoded")
                xHttp.send()
                xHttp.onload = function (e) {
                    if (this.readyState === 4 && this.status === 200) {
                        let response = JSON.parse(this.response)
                        icon.classList.remove('d-none')
                        spinner.classList.add('d-none')
                        if (!response.success && response.message) {
                            inputContent.classList.add('is-invalid')
                            let node = document.createElement("div")
                            node.classList.add('error')
                            node.appendChild(document.createTextNode(response.message))
                            form.appendChild(node)
                        } else {
                            document.getElementById(btn.dataset.result).innerHTML = response.content
                            inputContent.classList.add('is-valid')
                            setTimeout(function () {
                                inputContent.classList.remove('is-valid')
                            }, 1500)
                        }
                    }
                }
            }, false)
        }

        /** Internal modal alert */
        let modalEl = document.getElementById('internal-error-modal')
        if(modalEl) {
            let modal = new bootstrap.Modal(document.getElementById('internal-error-modal'), {
                backdrop: false,
            })
            modal.show()
        }
    }
})