/**
 *  Video
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (videos) {

    for (let i = 0; i < videos.length; i++) {

        let videoBlock = videos[i]
        let video = videoBlock.getElementsByClassName('html-video')[0]
        let soundControl = videoBlock.getElementsByClassName('sound-control')

        if (soundControl.length > 0) {

            let button = soundControl[0]

            button.onclick = function (event) {

                event.preventDefault()
                video.muted = !video.muted

                // if (!button.classList.contains('active')) {
                //     // button.setAttribute('data-original-title', button.data('pause-text')).parent().find('.tooltip-inner').html(button.data('pause-text'));
                // } else {
                //     // button.attr('data-original-title', button.data('play-text')).parent().find('.tooltip-inner').html(button.data('play-text'));
                // }

                button.getElementsByClassName('pause')[0].classList.toggle('d-none')
                button.getElementsByClassName('play')[0].classList.toggle('d-none')
                button.classList.toggle('active')
            }
        }
    }
}