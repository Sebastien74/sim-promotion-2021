/**
 *  News vendor
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */

export default function (body) {

    let defaultTeasers = body.find('.newscasts-teaser-default');
    if (defaultTeasers.length > 0) {
        import('./teaser-default').then(({default: defaultTeaser}) => {
            new defaultTeaser();
        }).catch(error => 'An error occurred while loading the component "components/news/teaser-default"');
    }

    let multipleCarouselTeaser = body.find('.newscasts-teaser-multiple-carousel');
    if (multipleCarouselTeaser.length > 0) {
        import('./teaser-multiple-carousel').then(({default: multipleCarousel}) => {
            new multipleCarousel(body, multipleCarouselTeaser);
        }).catch(error => 'An error occurred while loading the component "components/news/teaser-multiple-carousel"');
    }
}