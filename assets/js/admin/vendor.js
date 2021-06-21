/**
 *  Vendor
 *
 *  @copyright 2020
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 *  @version 1.0
 *
 *  Licensed under the MIT License (LICENSE.txt).
 *
 *  1 - jQuery UI
 *  2 - Routing
 *  3 - Preloader
 *  4 - Layout management
 *  6 - Core
 *  6 - Active URL
 *  7 - Code generator
 *  8 - Bytes generator
 *  9 - Password generator
 *  10 - Tree search
 *  11 - Index search
 *  12 - Medias modal library
 *  13 - Map
 *  14 - Delete pack
 *  15 - Delete index
 *  16 - Media Tab
 *  17 - Websites selector
 *  18 - Tab item click
 */

let body = document.body

/** 1 - jQuery UI */
import 'jquery-ui-bundle'

/** 2 - Routing */
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'

/** 4 - Layout management */
import layoutManagement from './pages/layout/vendor'

if (document.getElementById('zones-sortable')) {
    layoutManagement(Routing)
}

/** 5 - Core */
import "../vendor/first-paint"
import "../vendor/vendor"
import "./core/core"
import './form/vendor'
import './plugins/vendor'

/** 6 - Active URL */
let urlLinks = document.querySelectorAll('.active-urls a')
for (let i = 0; i < urlLinks.length; i++) {
    let link = urlLinks[i]
    link.onclick = function (e) {
        e.preventDefault()
        import('./core/urls').then(({default: activeUrls}) => {
            new activeUrls(e, link)
        }).catch(error => 'An error occurred while loading the component "core/urls"')
    }
}

/** 7 - Code generator */
let generateLinks = document.querySelectorAll('.generate-code')
for (let i = 0; i < generateLinks.length; i++) {
    let link = generateLinks[i]
    link.onclick = function (e) {
        e.preventDefault()
        import('./core/code-generator').then(({default: codeGenerator}) => {
            new codeGenerator(e, link)
        }).catch(error => 'An error occurred while loading the component "core/code-generator"')
    }
}

/** 8 - Bytes generator */
let bytesLinks = document.querySelectorAll('.generate-bytes')
for (let i = 0; i < bytesLinks.length; i++) {
    let link = bytesLinks[i]
    link.onclick = function (e) {
        e.preventDefault()
        import('./core/bytes-generator').then(({default: bytesGenerator}) => {
            new bytesGenerator(e, link)
        }).catch(error => 'An error occurred while loading the component "core/bytes-generator"')
    }
}

/** 9 - Password generator */
let passwordLinks = document.querySelectorAll('.generator-password')
for (let i = 0; i < passwordLinks.length; i++) {
    let link = passwordLinks[i]
    link.onclick = function (e) {
        e.preventDefault()
        import('./core/password-generator').then(({default: passwordGenerator}) => {
            new passwordGenerator(e, link)
        }).catch(error => 'An error occurred while loading the component "core/password-generator"')
    }
}

/** 10 - Tree search */
if (document.querySelectorAll('.pages-search input').length > 0) {
    import('./core/tree-search').then(({default: treeSearch}) => {
        new treeSearch()
    }).catch(error => 'An error occurred while loading the component "core/tree-search"')
}

/** 11 - Index search */
if (document.querySelectorAll('.search-in-list input').length > 0) {
    import('./core/search').then(({default: search}) => {
        new search()
    }).catch(error => 'An error occurred while loading the component "core/search"')
}

/** 12 - Medias modal library */
let mediasModals = document.querySelectorAll('.open-modal-medias')
for (let i = 0; i < mediasModals.length; i++) {
    let modalEl = mediasModals[i]
    modalEl.onclick = function (e) {
        e.preventDefault()
        import('./media/open-modal').then(({default: openModal}) => {
            new openModal(Routing, e, modalEl)
        }).catch(error => 'An error occurred while loading the component "media/open-modal"')
    }
}

/** 13 - Map */
if (document.querySelectorAll('.input-places').length > 0) {
    import('./lib/map').then(({default: mapLibrary}) => {
        new mapLibrary()
    }).catch(error => 'An error occurred while loading the component "media/vendor"')
}

/** 14 - Delete pack */
if (body.getElementsByClassName('delete-pack').length > 0
    || document.getElementById('delete-pack-btn')) {
    import('./delete/delete-pack').then(({default: deletePack}) => {
        new deletePack()
    }).catch(error => 'An error occurred while loading the component "delete-pack"')
}

/** 15 - Delete index */
if (document.getElementById('delete-index-all')
    || document.getElementById('index-delete-show')
    || body.getElementsByClassName('delete-input-index').length > 0
    || document.getElementById('index-delete-submit')) {
    import('./delete/delete-index').then(({default: deleteIndex}) => {
        new deleteIndex()
    }).catch(error => 'An error occurred while loading the component "delete-index"')
}

/** 16 - Media Tab */
let mediasTabs = document.querySelectorAll('.media-tab-content-loader')
for (let i = 0; i < mediasTabs.length; i++) {
    let mediasTabEl = mediasTabs[i]
    mediasTabEl.onclick = function () {
        import('./core/medias-tab').then(({default: mediasTab}) => {
            new mediasTab(mediasTabEl)
        }).catch(error => 'An error occurred while loading the component "core/medias-tab"')
    }
}

import websitesSelector from './core/websites-selector'

window.addEventListener("load", function () {

    /** 17 - Websites selector */
    if (document.getElementById('websites-selector-form')) {
        websitesSelector()
    }

    /** 18 - Tab item click */
    let navLinks = document.querySelectorAll('.nav-link')
    for (let i = 0; i < navLinks.length; i++) {
        let navLinkEl = navLinks[i]
        navLinkEl.onclick = function () {
            import('./core/tab').then(({default: tabPlugin}) => {
                new tabPlugin()
            }).catch(error => 'An error occurred while loading the component "core/tab"')
        }
    }
})