/**
 *  Prototype
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    $('body').on('click', '.add-collection', function (e) {

        e.preventDefault();

        let el = $(this);
        let collectionTarget = el.attr('data-collection-target');
        let beforeTarget = $(el.attr('data-before'));
        let collectionHolder = $(el.attr('data-target'));
        let newIndex = collectionHolder.attr('data-index');
        let prototype = collectionHolder.attr('data-prototype');
        let form = prototype.replace(/__name__/g, newIndex);

        collectionHolder.attr('data-index', parseInt(newIndex) + 1);

        // Form second level collection
        // if (form.match("data-prototype")) {
        //
        //     // Convert NODE list to array with (toArray) return by (htmlToElements) form to HTML elements
        //     let elements = toArray(htmlToElements(form));
        //
        //     $(elements).each(function (i, e) {
        //
        //         let prototypeExist = $(e).data('prototype');
        //
        //         // Check if prototype exist
        //         if (typeof prototypeExist != "undefined") {
        //             let secondIndex = 0;
        //             let secondPrototype = prototypeExist;
        //             form = secondPrototype.replace(/__second_name__/g, secondIndex);
        //         }
        //     });
        // }

        let body = $('body');
        let type = $(this).attr('data-type');
        if (typeof type != "undefined" && type === "table") {
            body.find('table .dataTables_empty').closest('tr').remove();
        }

        let formEl = $(form);
        let formCheckboxes = formEl.find('.custom-control-input');

        if (beforeTarget.length > 0) {
            beforeTarget.before(form);
        } else if (typeof collectionTarget != "undefined") {
            if (collectionTarget === "prepend") {
                collectionHolder.prepend(form);
            } else if (collectionTarget === "append") {
                collectionHolder.append(form);
            }
        } else {
            collectionHolder.prepend(form);
        }

        if (formCheckboxes.length > 0) {

            let collections = $('body').find('.collection');

            collections.each(function () {

                let block = $(this).find('.prototype').last();
                let checkboxes = block.find('.custom-control-input');

                checkboxes.each(function () {

                    let checkbox = $(this);
                    let uniqId = 'input-' + Math.floor(Math.random() * 10000);

                    checkbox.attr('id', uniqId);
                    checkbox.parent().find('label').attr('for', uniqId);
                });
            });
        }

        /** Plugins vendor */
        import('../../vendor/plugins/plugins').then(({default: activePlugins}) => {
            new activePlugins();
        }).catch(error => 'An error occurred while loading the component "vendor/plugins"');

        /** Plugins admin */
        import('../plugins/vendor').then(({default: activeAdminPlugins}) => {
            new activeAdminPlugins();
        }).catch(error => 'An error occurred while loading the component "plugins"');

        /** Touch spin */
        import('../../vendor/plugins/touchspin').then(({default: touchSpin}) => {
            new touchSpin();
        }).catch(error => 'An error occurred while loading the component "plugins/touchspin"');

        /** Code generator */
        if(body.find("input[code='code']").length > 0) {
            import('../core/code-generator').then(({default: codeGenerator}) => {
                new codeGenerator();
            }).catch(error => 'An error occurred while loading the component "core/code-generator"');
        }

        return false;
    });
}