/**
 *  Bootstrap navigation
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */

window.addEventListener("load", function () {

    let body = document.body
    let breakpoints = {
        sm: 576,
        md: 768,
        lg: 992,
        xl: 1200,
        navbar_expand: 'lg'
    }

    /** Collapse events */
    let collapses = document.getElementsByClassName('navbar-collapse')
    for (let i = 0; i < collapses.length; i++) {
        collapses[i].addEventListener('show.bs.collapse', function () {
            body.classList.add('menu-open')
            collapses[i].closest('nav').classList.add('open')
            let togglers = document.getElementsByClassName('nav-toggler-icon')
            togglers[0].closest('.navbar-toggler').classList.add('d-none')
            for (let j = 0; j < togglers.length; j++) {
                togglers[j].classList.add('open')
            }
        })
        collapses[i].addEventListener('hide.bs.collapse', function () {
            body.classList.remove('menu-open')
            collapses[i].closest('nav').classList.remove('open')
            let togglers = document.getElementsByClassName('nav-toggler-icon')
            togglers[0].closest('.navbar-toggler').classList.remove('d-none')
            for (let j = 0; j < togglers.length; j++) {
                togglers[j].classList.remove('open')
            }
        })
    }

    /** Dropdown hover & click */
    let screenWidth = document.documentElement.clientWidth
    let navigations = document.querySelectorAll('.menu-container.dropdown-hover')
    for (let i = 0; i < navigations.length; i++) {
        if (screenWidth >= breakpoints.lg) {
            let dropdowns = navigations[i].querySelectorAll('.dropdown')
            for (let j = 0; j < dropdowns.length; j++) {
                let dropdown = dropdowns[j]
                dropdown.addEventListener("mouseenter", function () {
                    dropdown.classList.add('show')
                    dropdown.getElementsByClassName('dropdown-menu')[0].classList.add('show')
                })
                dropdown.addEventListener("mouseleave", function () {
                    dropdown.classList.remove('show')
                    dropdown.getElementsByClassName('dropdown-menu')[0].classList.remove('show')
                })
            }
        } else {
            let dropdowns = navigations[i].querySelectorAll('.dropdown-toggle')
            for (let j = 0; j < dropdowns.length; j++) {
                let dropdown = dropdowns[j].closest('.dropdown')
                dropdown.addEventListener("click", function () {
                    if(dropdown.classList.contains('show')) {
                        dropdown.classList.remove('show')
                        dropdown.getElementsByClassName('dropdown-menu')[0].classList.remove('show')
                    } else {
                        dropdown.classList.add('show')
                        dropdown.getElementsByClassName('dropdown-menu')[0].classList.add('show')
                    }
                })
            }
        }
    }

    /** Set actives classes */
    let dropdownsMenus = body.getElementsByClassName('dropdown-menu')
    for (let i = 0; i < dropdownsMenus.length; i++) {
        let actives = dropdownsMenus[i].querySelectorAll('li.active')
        for (let j = 0; j < actives.length; j++) {
            let firstLevel = actives[j].closest('.level-1')
            if(firstLevel && !firstLevel.classList.contains('active')) {
                firstLevel.classList.add('active')
                firstLevel.querySelectorAll('a.link-level-1')[0].classList.add('active')
            }
        }
    }

    let breadcrumb = document.getElementById('breadcrumb')
    if(breadcrumb) {
        let firstBreadcrumb = breadcrumb.querySelectorAll("a[data-position='2']")
        if(firstBreadcrumb.length > 0) {
            let firstBreadcrumbHref = firstBreadcrumb[0].getAttribute('href')
            let itemMenu = document.querySelectorAll("a.link-level-1[href='" + firstBreadcrumbHref + "']")
            if(itemMenu.length > 0 && !itemMenu[0].classList.contains('active')) {
                itemMenu[0].classList.add('active')
                itemMenu[0].parentNode.classList.add('active')
            }
        }
    }

    /** Dropdowns management */
    let getNextSibling = function (elem, selector) {
        let sibling = elem.nextElementSibling
        if (!selector) return sibling
        while (sibling) {
            if (sibling.matches(selector)) return sibling
            sibling = sibling.nextElementSibling
        }

    }
    let dropdownSubmenus = document.querySelectorAll('.dropdown-submenu > .dropdown-toggle')
    if(dropdownSubmenus.length > 0) {
        for (let i = 0; i < dropdownSubmenus.length; i++) {
            let submenu = dropdownSubmenus[i]
            submenu.onclick = function (event) {
                event.preventDefault()
                for (let j = 0; j < dropdownSubmenus.length; j++) {
                    let el = dropdownSubmenus[j]
                    let next = getNextSibling(el, '.dropdown-menu')
                    if (el.getAttribute('id') !== submenu.getAttribute('id')) {
                        next.classList.remove('show')
                    } else {
                        next.classList.toggle('show')
                    }
                }
                event.stopPropagation()
            }
        }
    }

    let dropdowns = document.getElementsByClassName('dropdown')
    for (let i = 0; i < dropdowns.length; i++) {
        dropdowns[i].addEventListener('hidden.bs.dropdown', function () {
            let showDropdowns = document.querySelectorAll('.dropdown-menu.show')
            for (let j = 0; j < showDropdowns.length; j++) {
                /** hide any open menus when parent closes */
                showDropdowns[j].classList.remove('show')
            }
        })
    }

    /** Sticky navigations */
    let setFixedNavigations = function () {
        let navigations = document.getElementsByClassName('menu-container has-sticky')
        for (let i = 0; i < navigations.length; i++) {
            let navigation = navigations[i]
            let sticky = navigation.offsetTop
            let navigationHeight = navigation.offsetHeight
            if (window.pageYOffset >= (sticky + (navigationHeight * 3))) {
                navigation.classList.add("fixed-top")
            } else if (window.pageYOffset >= (sticky + (navigationHeight * 2))) {
                navigation.classList.add("animation")
            } else if (window.pageYOffset <= 50) {
                navigation.classList.remove("fixed-top")
                navigation.classList.remove("animation")
            }
        }
    };

    window.addEventListener('scroll', function () {
        setFixedNavigations()
    })
})