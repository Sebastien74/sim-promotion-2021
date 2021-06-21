import '../../../../scss/front/default/components/_forum.scss';

import route from "../../../vendor/components/routing";
// import removeErrors from "../../../../../../../../assets/js/vendor/core/remove-errors";
// import route from "../../../../../../../../assets/js/vendor/core/routing";

// import 'bootstrap/js/dist/tooltip';
// import 'bootstrap/js/dist/popover';

/**
 *  Forum
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (body) {

    let loadForm = $('.forum-form');
    if (loadForm[0]) {
        loadForm[0].reset();
    }

    body.on('keyup keypress', '.form-control', function (e) {
        let keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            sendRequest(e, $(this).closest('form'));
            return false;
        }
    });

    body.on('click', '.forum-submit', function handler(e) {
        let el = $(this);
        let form = el.closest('form');
        sendRequest(e, form);
    });

    let popovers = function () {
        $('.share-popover').popover({
            trigger: 'focus',
            template: '<div class="popover forum-share-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
            html: true
        });
    }

    let sendRequest = function (e, form) {

        e.preventDefault();

        let body = $('body');
        let formId = form.attr('id');
        let formData = new FormData(document.getElementById(formId));
        let containerId = form.closest('.forum-container').attr('id');
        let loader = form.closest('.forum-container').find('.form-loader');

        $.ajax({
            url: form.attr('action'),
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            async: true,
            beforeSend: function () {

                loader.removeClass('d-none');

                /** Remove errors */
                import('../../../vendor/components/remove-errors').then(({default: removeErrors}) => {
                    new removeErrors();
                }).catch(error => 'An error occurred while loading the component "remove-errors"');
            },
            success: function (response) {

                let html = response.html;
                let content = $(html).find('.form-ajax-container');

                body.find('#' + containerId).html(content);

                /** Form packages */
                import('../trash/packages/form').then(({default: formPackage}) => {
                    new formPackage(body);
                }).catch(error => 'An error occurred while loading the component "packages/form"');

                getComments();
                popovers();

                if (response.success) {
                    $('body').find('.form-control').val('');
                }
            },
            error: function (error) {
            }
        });

        e.stopImmediatePropagation();
        return false;
    }

    getComments();

    body.on('click', '.forum-comment-submit', function (e) {

        e.preventDefault();

        let el = $(this);
        let form = el.closest('form');
        let formId = form.attr('id');
        let formData = new FormData(document.getElementById(formId));
        let target = el.data('target');
        let container = body.find(target);
        let loader = form.closest(target).find('.form-loader');

        $.ajax({
            url: form.attr('action'),
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            async: false,
            beforeSend: function () {

                loader.removeClass('d-none');

                /** Remove errors */
                import('../../../vendor/components/remove-errors').then(({default: removeErrors}) => {
                    new removeErrors();
                }).catch(error => 'An error occurred while loading the component "remove-errors"');
            },
            success: function (response) {

                let html = response.html;
                let content = $(html).find('.ajax-container');

                container.html(content);

                /** Form packages */
                import('../trash/packages/form').then(({default: formPackage}) => {
                    new formPackage(body);
                }).catch(error => 'An error occurred while loading the component "packages/form"');

                popovers();

                if (response.success) {
                    $('#' + formId).find('.form-control').val('');
                }
            },
            error: function (error) {
            }
        });

        e.stopImmediatePropagation();
        return false;
    });

    function getComments(comments = [], ajaxProgress = false) {

        let loader = $('#comments-loaders');
        let commentsIds = comments;

        if (commentsIds.length === 0 && !ajaxProgress) {
            let commentsEls = body.find('#comments-data li');
            commentsEls.each(function () {
                commentsIds.push($(this).data('id'));
            });
        }

        if (commentsIds.length === 0) {
            loader.addClass('d-none');
        } else {

            let commentId = commentsIds[0];

            $.ajax({
                url: route('front_forum_comment', {comment: commentId}),
                type: "GET",
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function () {
                },
                success: function (response) {
                    body.find('#comments #result').append(response.html);
                    popovers();
                    commentsIds.splice(0, 1);
                    getComments(commentsIds, true);
                },
                error: function (error) {
                }
            });
        }
    }
}