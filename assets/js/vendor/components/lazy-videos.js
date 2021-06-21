/**
 *  Lazy loading video
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    window.addEventListener("load", function(event) {

        let lazyVideos = [].slice.call(document.querySelectorAll("video.lazy-video"));

        if ("IntersectionObserver" in window) {

            let lazyVideoObserver = new IntersectionObserver(function (entries, observer) {

                entries.forEach(function (video) {
                    if (video.isIntersecting) {
                        for (let source in video.target.children) {
                            let videoSource = video.target.children[source];
                            if (typeof videoSource.tagName === "string" && videoSource.tagName === "SOURCE") {
                                videoSource.src = videoSource.dataset.src;
                            }
                        }

                        video.target.load();
                        video.target.classList.remove("lazy");
                        lazyVideoObserver.unobserve(video.target);
                    }
                });
            });

            lazyVideos.forEach(function (lazyVideo) {
                lazyVideoObserver.observe(lazyVideo);
            });
        }
    });
}