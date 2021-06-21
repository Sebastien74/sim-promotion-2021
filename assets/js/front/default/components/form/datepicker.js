import 'vanillajs-datepicker/dist/css/datepicker.min.css';
import 'vanillajs-datepicker/dist/css/datepicker-bs4.min.css';

import {Datepicker} from 'vanillajs-datepicker'

/**
 *  Datepickers
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 *  @doc https://mymth.github.io/vanillajs-datepicker/#/?id=quick-start
 */
export default function (pickers) {

    /** 'vanillajs-datepicker/dist/js/locales/fr' */
    (function () {
        Datepicker.locales.fr = {
            days: ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
            daysShort: ["dim.", "lun.", "mar.", "mer.", "jeu.", "ven.", "sam."],
            daysMin: ["d", "l", "ma", "me", "j", "v", "s"],
            months: ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"],
            monthsShort: ["janv.", "févr.", "mars", "avril", "mai", "juin", "juil.", "août", "sept.", "oct.", "nov.", "déc."],
            today: "Aujourd'hui",
            monthsTitle: "Mois",
            clear: "Effacer",
            weekStart: 1,
            format: "dd/mm/yyyy"
        };
    }());

    let trans = document.getElementById('data-translation')
    let locale = document.documentElement.getAttribute('lang')
    let weekStart = locale === 'en' ? 0 : 1
    let shortTime = locale === 'en'

    for (let i = 0; i < pickers.length; i++) {

        let datepicker = pickers[i]
        let type = datepicker.dataset.type
        let hasTime = type === 'hour'
        let displayTime = hasTime || type === 'datetime'
        let format = trans.dataset.formatDatepicker

        if (type === 'hour') {
            format = 'HH:mm'
        } else if (type === 'datetime') {
            format = format + ' HH:mm'
        }

        const datepickerJS = new Datepicker(datepicker, {
            language: locale,
            format: format,
            weekStart: weekStart,
            daysShort: shortTime
        })
    }
}