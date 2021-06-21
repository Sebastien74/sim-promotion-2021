/**
 *  Remove errors form on field click
 */
export default function () {

    let errors = function () {

        let formInputsControls = document.getElementsByClassName('form-control')
        for (let i = 0; i < formInputsControls.length; i++) {
            formInputsControls[i].onclick = function () {
                removeErrors(formInputsControls[i])
            }
        }

        let select2Selections = document.getElementsByClassName('select2-selection')
        for (let i = 0; i < select2Selections.length; i++) {
            select2Selections[i].onclick = function () {
                removeErrors(select2Selections[i])
            }
        }

        let customControlInputs = document.getElementsByClassName('custom-control-input')
        for (let i = 0; i < customControlInputs.length; i++) {
            customControlInputs[i].addEventListener('change', () => {
                removeErrors(customControlInputs[i])
            })
        }

        let checkInputs = document.getElementsByClassName('form-check-input')
        for (let i = 0; i < checkInputs.length; i++) {
            checkInputs[i].addEventListener('change', () => {
                removeErrors(checkInputs[i])
            })
        }
    }

    errors()

    let removeErrors = function (el) {

        let formGroup = el.closest('.form-group')
        let invalidGroup = formGroup ? formGroup.getElementsByClassName('invalid-feedback') : null

        if (el.classList.contains('is-invalid') || invalidGroup && invalidGroup.length > 0) {

            el.classList.remove('is-invalid')

            let invalids = el.closest('.is-invalid')
            if(invalids) {
                for (let i = 0; i < invalids.length; i++) {
                    let feedbacks = invalids[i].getElementsByClassName('invalid-feedback')
                    for (let j = 0; j < feedbacks.length; j++) {
                        feedbacks[j].remove()
                    }
                }
            }

            if (invalidGroup && invalidGroup.length > 0) {
                invalidGroup[0].remove()
            }

            if (formGroup && formGroup.length > 0) {
                formGroup[0].classList.remove('is-invalid')
                let invalids = formGroup[0].closest('.is-invalid')
                if(invalids) {
                    for (let i = 0; i < invalids.length; i++) {
                        invalids[i].classList.remove('is-invalid')
                    }
                }
            }
        }
    }
}