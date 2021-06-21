import '../lib/select2totree';

/**
 *  Tree select
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    let treeSelects = $('body').find('.tree-select');

    if (treeSelects.length > 0) {
        $('.tree-select').select2ToTree();
    }
}