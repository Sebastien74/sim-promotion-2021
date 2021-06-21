/**
 *  Audio hover
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (audiosHover) {

    /** Example :
     *
     *  <div class="my-hover-element">
     *       <div class="audio-hover" data-src="path-to-mp3-file" data-target=".my-hover-element"></div>
     *  </div>
     */

    audiosHover.each(function () {

        let xhr = new XMLHttpRequest();
        let audio = $(this);

        xhr.open('GET', audio.data('src'));
        xhr.responseType = 'arraybuffer';
        xhr.addEventListener('load', () => {
            let audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            let playSound = (audioBuffer) => {
                let target = audio.data('target') ? $(this).closest(audio.data('target')) : audio.parent();
                target.mouseenter(function () {
                    let source = audioCtx.createBufferSource();
                    source.buffer = audioBuffer;
                    source.connect(audioCtx.destination);
                    source.loop = false;
                    source.start();
                    target.mouseleave(function () {
                        source.stop();
                    });
                });
            };
            audioCtx.decodeAudioData(xhr.response).then(playSound);
        });
        xhr.send();
    });
}