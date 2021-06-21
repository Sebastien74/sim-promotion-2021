let Masonry = require('masonry-layout')

/**
 *  Masonry
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (item = null) {

    let grids = document.getElementsByClassName('card-columns')

    if (grids.length > 0) {

        for (let i = 0; i < grids.length; i++) {

            let gridColumn = grids[i]
            let grid = new Masonry(gridColumn, {
                itemSelector: '.grid-item',
                columnWidth: '.grid-item',
                percentPosition: true
            })

            if (item) {
                grid.append(item).masonry('appended', item)
            }
        }
    }
}