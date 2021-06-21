/**
 *  Scroll to errors
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 *  @inheritDoc https://github.com/jshjohnson/Choices
 */

const Choices = require("choices.js")

let trans = document.getElementById('data-translation')

export default function (selectors) {

    for (let i = 0; i < selectors.length; i++) {

        let select = selectors[i]

        const choice = new Choices(select, {
            noResultsText: trans.getAttribute('data-choices-no-result'),
            itemSelectText: '',
            classNames: {
                containerOuter: 'selector-group'
            }
        })

        choice.passedElement.element.addEventListener(
            'showDropdown',
            function (event) {
                let invalidGroup = select.closest('.form-group.is-invalid')
                if (invalidGroup) {
                    invalidGroup.classList.remove('is-invalid')
                }
                let feedbacks = invalidGroup.getElementsByClassName('invalid-feedback')
                if(feedbacks) {
                    feedbacks[0].remove()
                }
            },
            false,
        )
    }
}