/**
 *  Website alert
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (boxAlert) {

    let closeAlert = boxAlert.getElementById('close-website-alert')

    closeAlert.addEventListener('click', () => {
        let oReq = new XMLHttpRequest()
        oReq.onload = reqListener
        oReq.open("get", closeAlert.dataset.path, true)
        oReq.send()
    })

    function reqListener() {
        let response = JSON.parse(this.responseText)
        if (response.success) {
            boxAlert.remove()
        }
    }
}