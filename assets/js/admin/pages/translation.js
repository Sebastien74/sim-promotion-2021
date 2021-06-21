import route from "../../vendor/components/routing";

import '../../../scss/admin/pages/translation.scss';

let body = $('body');

body.on('click', '.translation-extract-btn', function (e) {

    e.preventDefault();

    let body = $('body');
    let domain = $(this).data('domain');
    let loader = $('#main-preloader');
    let generator = $('#translation-generator');
    let website = body.data('id');
    let item = body.find('#translation-generator-locales li.undo').first();

    loader.removeClass('d-none');
    generator.removeClass('d-none');

    extract(website, generator, item, domain);
});

let extract = function (website, generator, item, domain) {

    let locale = item.data('locale');

    $.ajax({
        url: route('admin_translation_extract', {website: website, locale: locale}),
        type: "GET",
        processData: false,
        contentType: false,
        dataType: 'json',
        complete: function () {

            item.removeClass('undo');
            let progressItem = $('body').find('#translation-generator-locales li.undo').first();
            let progressLocale = progressItem.data('locale');

            if (progressItem.length > 0) {
                generator.find('.extraction-title img').attr('src', '/medias/icons/flags/'  + progressLocale + '.svg');
                extract(website, generator, progressItem, domain);
            } else {
                progress(website, generator, domain);
            }
        }
    });
};

let progress = function (website, generator, domain) {

    let urlArgs = typeof domain != 'undefined' ? {website: website, domain: domain} : {website: website};

    $.ajax({
        url: route('admin_translation_progress', urlArgs),
        type: "GET",
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {

            generator.addClass('d-none');
            $('#progress-block').append(response.html);

            let list = $('body').find('.translation-list.undo').first();
            let translation = list.find('li.undo').first();

            generateTranslation(list, translation, website, generator);
        }
    });
};

let generateTranslation = function (list, translation, website, generator) {

    let mainCounter = $('body').find('#main-counter');
    let translations = list.find('li');
    let listId = list.attr('id');
    let count = parseInt(mainCounter.attr('data-count'));
    let total = parseInt(mainCounter.data('total'));

    $.ajax({
        url: translation.data('href'),
        type: "GET",
        processData: false,
        contentType: false,
        dataType: 'json',
        complete: function (response) {

            translation.removeClass('undo');
            translation = list.find('li.undo').first();

            if (translation.length === 0) {
                list.removeClass('undo');
                list = $('body').find('.translation-list.undo').first();
                translation = list.find('li.undo').first();
            }

            mainCounter.attr('data-count', count + 1).text(count + 1);

            if (count + 1 === total) {
                generateYaml(website, generator);
            } else {

                let listEl = $('#' + listId);
                let progress = parseInt(listEl.attr('data-progress')) + 1;
                let progressBlock = $('body #' + listId).closest('.progress-bloc');
                let progressBar = progressBlock.find('.progress-bar');
                let percent = (parseInt(progress) * 100) / parseInt(translations.length);

                listEl.attr('data-progress', progress);
                progressBlock.find('.counter').text(progress);

                progressBar.attr('aria-valuenow', percent);
                progressBar.attr('style', "width: " + percent + "%");
                if (percent === 100) {
                    progressBar.addClass('bg-info');
                }

                generateTranslation(list, translation, website, generator);
            }
        }
    });
};

let generateYaml = function (website, generator) {

    $('#progress-block').remove();

    generator.find('.extraction-title').addClass('d-none');
    generator.find('.yaml-title').removeClass('d-none');
    generator.removeClass('d-none');

    $.ajax({
        url: route('admin_translation_generate_files', {website: website}),
        type: "GET",
        processData: false,
        contentType: false,
        dataType: 'json',
        complete: function (response) {
            clearCache(website, generator);
        }
    });
};

let clearCache = function (website, generator) {

    generator.find('.yaml-title').addClass('d-none');
    generator.find('.cache-title').removeClass('d-none');

    $.ajax({
        url: route('cache_clear', {website: website}) + '?ajax=true',
        type: "GET",
        processData: false,
        contentType: false,
        dataType: 'json',
        complete: function (response) {
            generator.find('.cache-title').addClass('d-none');
            generator.find('.cache-generate-title').removeClass('d-none');
            location.reload();
        }
    });
};

body.on('click', '.save-row-trans', function (e) {

    e.preventDefault();

    let btn = $(this);
    let row = btn.closest('tr');
    let form = $('body').find(btn.data('form-id'));
    let formGroup = row.find('.form-group');
    let formControl = row.find('.form-control');
    let myFormData = new FormData(document.getElementById(form.attr('id')));

    $.ajax({
        url: form.attr('action') + '?ajax=true',
        type: "POST",
        data: myFormData,
        processData: false,
        contentType: false,
        dataType: 'json',
        beforeSend: function (response) {
            btn.find('svg').toggleClass('d-none');
            formGroup.removeClass('has-success');
            formControl.removeClass('form-control-success');
        },
        success: function (response) {
            formGroup.addClass('has-success');
            formControl.addClass('form-control-success');
            btn.find('svg').toggleClass('d-none');
        },
        error: function (errors) {

            /** Display errors */
            import('../core/errors').then(({default: displayErrors}) => {
                new displayErrors(errors);
            }).catch(error => 'An error occurred while loading the component "errors"');
        }
    });

    e.stopImmediatePropagation();
    return false;
});