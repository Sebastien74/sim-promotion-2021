import '../../../scss/admin/lib/summernote/summernote-bs4.scss';

import "../lib/summernote/codemirror/codemirror.min";
import "../lib/summernote/codemirror/xml.min";
import "../lib/summernote/codemirror/formatting.min";
import '../lib/summernote/summernote-bs4.min';
import "../lib/summernote/lang/app";

/**
 * Summernote
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function () {

    let summernotes = $('body').find('.summernote');
    let pluginsData = $('#cms-plugins-data');
    let colors = ['#000000', "#ffffff"];
    let colorsData = pluginsData.data('colors-editor');
    let adminFonts = [];
    let fontsData = pluginsData.data('fonts-editor');

    if (typeof colorsData != "undefined") {
        let colorsDataExplode = colorsData.split(',');
        for (let i = 0; i < colorsDataExplode.length; i++) {
            let color = colorsDataExplode[i].trim();
            if (color && $.inArray(color, colors) < 0) {
                colors.push(color);
            }
        }
    }

    if (typeof fontsData != "undefined") {
        let fontsDataExplode = fontsData.split(',');
        for (let i = 0; i < fontsDataExplode.length; i++) {
            if (fontsDataExplode[i].trim()) {
                adminFonts.push(fontsDataExplode[i].trim());
            }
        }
    }

    summernotes.each(function () {

        let summernote = $(this);
        let placeholder = summernote.attr('placeholder');
        let uniqId = 'summernote' + '_' + Math.random().toString(36).substr(2, 9);
        let inTable = summernote.closest('table').hasClass('table');

        summernote.attr('id', uniqId);
        summernote.summernote({
            lang: $('html').attr('lang-code'),
            fontSizeUnits: ['px'],
            height: 250, // set editor height
            focus: false, // set focus to editable area after initializing
            fontNames: adminFonts,
            fontNamesIgnoreCheck: ['Poppins'],
            colors: [colors],
            placeholder: placeholder,
            toolbar: getOptions(inTable, adminFonts),
            createRange: true,
            dialogsInBody: true,
            dialogsFade: true,
            prettifyHtml: false,
            codemirror: {
                theme: 'monokai',
                lineNumbers: true,
                htmlMode: true,
                mode: "text/html",
                tabMode: 'indent'
            },
            callbacks: {
                onInit: function () {
                    // if (summernote.summernote('isEmpty')) {
                    //     summernote.closest('.form-group').find('.note-editable').html('<p></p>');
                    // }
                },
                onPaste: function (e) {
                    let bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                    e.preventDefault();
                    document.execCommand('insertText', false, bufferText);
                },
                onKeydown: function (e) {
                    // if (e.keyCode == 65 && e.ctrlKey) {
                    //     console.log(e.target.innerHTML);
                    // }
                }
            }
        });

        if (adminFonts.length > 0) {
            summernote.summernote('fontName', adminFonts[0]);
            $('body').find('.note-current-fontname').text(adminFonts[0]);
        }

        // $('.note-codable', $('#'+summernote.attr('id')).siblings('.note-editor')).bind('keyup', function() {
        //
        //     let code = $(this);
        //     let editable = code.closest('.note-editor').find('.note-editable');
        //     let value = code.val();
        //
        //     editable.html(value);
        //     summernote.val(value);
        //
        //     $('body').on('click', '.note-btn-group.note-view', function() {
        //         editable.html(value);
        //         summernote.val(value);
        //         summernote.summernote({
        //             placeholder: value !== "" ? "" : placeholder
        //         });
        //     });
        // });
    });

    function getOptions(inTable, adminFonts) {

        if (inTable) {
            return [
                ['style', ['style']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture']],
                ['view', ['codeview']],
                ['color', ['color']]
            ]
        } else if (adminFonts.length > 0) {
            return [
                ['style', ['style']],
                ['fontsize', ['fontsize']],
                ['font', ['fontname', 'bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['codeview']],
                ['table', ['table']],
                ['color', ['color']],
            ]
        } else {
            return [
                ['style', ['style']],
                ['fontsize', ['fontsize']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['codeview']],
                ['table', ['table']],
                ['color', ['color']],
            ]
        }
    };
}