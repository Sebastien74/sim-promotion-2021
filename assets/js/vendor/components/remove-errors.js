/**
 *  Remove form errors
 */
export default function () {

    let feedbacks = document.getElementsByClassName('invalid-feedback')
    if(feedbacks) {
        for (let i = 0; i < feedbacks.length; i++) {
            feedbacks[i].remove()
        }
    }

    let invalids = document.getElementsByClassName('is-invalid');
    if(invalids) {
        for (let i = 0; i < invalids.length; i++) {
            invalids[i].classList.remove('is-invalid')
        }
    }

    /** On keyup on field */
    import('./keyup-fields').then(({default: keyupFields}) => {
        new keyupFields()
    }).catch(error => 'An error occurred while loading the component "keyup-fields"')
}