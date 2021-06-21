/**
 *  Form package
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 *
 *  1 - On keyup on field
 *  2 - Scroll to errors
 *  3 - Datepicker
 *  4 - Selects
 *  5 - Touchspin
 */
export default function () {

    /** 1 - On keyup on field */
    import('../../../../vendor/components/keyup-fields').then(({default: keyupFields}) => {
        new keyupFields()
    }).catch(error => 'An error occurred while loading the component "keyup-fields"')

    /** 2 - Scroll to errors */
    let errors = document.getElementsByClassName('invalid-feedback')
    if (errors && errors.length > 0) {
        import('../../../../vendor/components/scroll-error').then(({default: scrollErrors}) => {
            new scrollErrors()
        }).catch(error => 'An error occurred while loading the component "scroll-error"')
    }

    /** 3 - Datepicker */
    let inputPickers = document.querySelectorAll('input.datepicker')
    if (inputPickers.length > 0) {
        import('./datepicker').then(({default: datepicker}) => {
            new datepicker(inputPickers)
        }).catch(error => 'An error occurred while loading the component "datepicker"')
    }

    /** 4 - Selects */
    let selectors = document.querySelectorAll('.select-choice')
    if (selectors && selectors.length > 0) {
        import('../../../../vendor/plugins/choice').then(({default: choices}) => {
            new choices(selectors)
        }).catch(error => 'An error occurred while loading the component "choices"')
    }
    //
    // /** 5 - Touchspin */
    // let inputs = document.querySelectorAll("input[type='number']")
    // if (inputs.length > 0) {
    //     import('../../../../vendor/plugins/touchspin').then(({default: touchspin}) => {
    //         new touchspin()
    //     }).catch(error => 'An error occurred while loading the component "touchspin"')
    // }
}