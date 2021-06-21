import './leaflet'
import './leaflet.markercluster'
import '../../../../../scss/front/default/components/map/_map.scss'

/**
 *  Open street map
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (maps) {

    let trans = document.getElementById('data-translation')

    for (let i = 0; i < maps.length; i++) {

        let mapBox = maps[i]
        let layerUrl = mapBox.dataset.layer ? mapBox.dataset.layer : 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png'
        let data = mapBox.getElementsByClassName('data-map')[0]
        let points = data.getElementsByClassName('point')
        let isMultiple = mapBox.dataset.multiple

        let map = L.map(mapBox.getAttribute('id'), {
            center: [data.dataset.latitude, data.dataset.longitude],
            zoom: data.dataset.zoom,
            zoomControl: true, /* false = no zoom control buttons displayed */
            scrollWheelZoom: false, /* false = scrolling zoom on the map is locked */
            dragging: !L.Browser.mobile
        })

        L.tileLayer(layerUrl, {
            minZoom: data.dataset.minZoom,
            maxZoom: data.dataset.maxZoom,
            attribution: '<a href="https://wikimediafoundation.org/wiki/Maps_Terms_of_Use">Wikimedia</a>',
            tap: false
        }).addTo(map)

        let markerClusters
        if (parseInt(data.dataset.markerClusters) === 1) {
            markerClusters = L.markerClusterGroup()
        }

        for (let j = 0; j < points.length; j++) {

            let point = points[j]
            let marker
            let markerWidth = parseInt(point.dataset.markerWidth)
            let markerHeight = parseInt(point.dataset.markerHeight)

            let icon = L.icon({
                iconUrl: point.dataset.marker,
                iconSize: [markerWidth, markerHeight],
                className: point.dataset.category,
                iconAnchor: [(markerWidth / 2), markerHeight], /** Marker position: ([icon]width / 2) & [icon]height */
                popupAnchor: [0, -markerWidth] /** Popup position */
            })

            if (parseInt(data.dataset.markerClusters) !== 1) {
                marker = L.marker([point.dataset.latitude, point.dataset.longitude], {icon: icon}).addTo(map)
            } else {
                marker = L.marker([point.dataset.latitude, point.dataset.longitude], {icon: icon})
                markerClusters.addLayer(marker)
            }

            html(point, data, marker)
        }

        map.scrollWheelZoom.disable()
        map.doubleClickZoom.disable()

        if (parseInt(data.dataset.markerClusters) === 1) {
            map.addLayer(markerClusters)
        }

        let filters = function () {

            let filters = document.body.getElementsByClassName('map-filter-checkbox')
            for (let i = 0; i < filters.length; i++) {
                filters[i].onclick = function () {
                    filters[i].closest('.marker-select').click()
                }
            }

            let selects = document.body.getElementsByClassName('marker-select')
            for (let i = 0; i < selects.length; i++) {

                let el = selects[i]

                el.onclick = function () {

                    let markerSelects = document.body.getElementsByClassName('marker-select')
                    let markers = document.body.getElementsByClassName('leaflet-marker-icon')

                    /** Multi filters */
                    if (isMultiple) {

                        el.classList.toggle('active')

                        for (let j = 0; j < markers.length; j++) {

                            let marker = markers[j]

                            if (!marker.classList.contains('d-none')) {
                                marker.classList.add('d-none')
                            }

                            let selected = false
                            let activeSelects = document.body.querySelectorAll('.marker-select.active')
                            for (let j = 0; j < activeSelects.length; j++) {
                                if (marker.classList.contains(activeSelects[j].dataset.category)) {
                                    selected = true
                                }
                            }

                            if (selected) {
                                marker.classList.remove('d-none')
                            }
                        }
                    }

                    /** Uniq filters */
                    else {

                        let category = el.dataset.category

                        for (let j = 0; j < markerSelects.length; j++) {
                            markerSelects[j].classList.remove('active')
                            markerSelects[j].classList.add('disabled-filter')
                        }

                        el.classList.add('active')
                        el.classList.remove('disabled-filter')

                        for (let j = 0; j < markers.length; j++) {
                            let marker = markers[j]
                            if (marker.classList.contains(category) && marker.classList.contains('d-none')) {
                                marker.classList.remove('d-none')
                            } else if (!marker.classList.contains(category)) {
                                marker.classList.add('d-none')
                            }
                        }
                    }
                }
            }
        }

        filters()

        function html(point, data, marker) {

            let display = false
            let html = '<div class="place-card mb-0">'
            let haveBottom = typeof point.dataset.googleMapUrl != "undefined" && point.dataset.googleMapUrl !== ""
                || typeof point.dataset.googleMapDirectionUrl != "undefined" && point.dataset.googleMapDirectionUrl !== ""
                || typeof point.dataset.link != "undefined" && point.dataset.link !== ""

            if (typeof point.dataset.media != "undefined" && point.dataset.media !== "") {
                html += '<div class="image-wrap">'
                html += '<img src="' + point.dataset.media + '" class="img-fluid" />'
                html += '</div>'
                display = true
            }

            html += '<div class="content-body">'
            html += '<div class="top">'

            let haveTitle = typeof point.dataset.title != "undefined" && point.dataset.title !== ""
            let haveBody = typeof point.dataset.body != "undefined" && point.dataset.body !== ""

            if (haveTitle || haveBody) {
                html += '<div class="description-wrap">'
                display = true
            }

            if (haveTitle) {
                html += '<strong class="description-title">' + point.dataset.title + '</strong>'
            }

            if (haveBody) {
                html += point.dataset.body
            }

            if (haveTitle || haveBody) {
                html += '</div>'
            }

            if (typeof point.dataset.name != "undefined" && point.dataset.name !== "") {
                html += '<strong class="place-name">' + point.dataset.name + '</strong>'
                display = true
            }

            html += '<div class="place-address">'

            if (typeof point.dataset.address != "undefined" && point.dataset.address !== "") {
                html += '<div class="address">'
                html += point.dataset.address
                html += '</div>'
                display = true
            }

            if (typeof point.dataset.zipCode != "undefined" && point.dataset.zipCode !== "") {
                html += '<span class="zip-code">'
                html += point.dataset.zipCode
                html += '</span>'
                if (typeof point.dataset.city != "undefined" && point.dataset.city !== ""
                    || typeof point.dataset.department != "undefined" && point.dataset.department !== ""
                    || typeof point.dataset.country != "undefined" && point.dataset.country !== "") {
                    html += " - "
                }
                display = true
            }

            if (typeof point.dataset.city != "undefined" && point.dataset.city !== "") {
                html += '<span class="city">'
                html += point.dataset.city
                html += '</span>'
                if (typeof point.dataset.department != "undefined" && point.dataset.department !== ""
                    || typeof point.dataset.country != "undefined" && point.dataset.country !== "") {
                    html += " - "
                }
                display = true
            }

            if (typeof point.dataset.department != "undefined" && point.dataset.department !== "") {
                html += '<span class="department">'
                html += point.dataset.department
                html += '</span>'
                display = true
            }

            if (typeof point.dataset.country != "undefined" && point.dataset.country !== "") {
                html += '<br>'
                html += '<span class="country">'
                html += point.dataset.country
                html += '</span>'
                display = true
            }

            html += '</div>'

            let phones = point.getElementsByClassName('phone')
            if (phones.length > 0) {
                html += '<div class="phones mt-3">'
                for (let k = 0; k < phones.length; k++) {
                    let phone = phones[k]
                    let number = phone.dataset.number
                    let href = phone.dataset.href
                    html += '<div class="w-100">' + trans.dataset.phoneMap + ' <a href="tel:' + href + '" class="text-primary">' + number + '</a></div>'
                }
                html += '</div>'
            }

            html += '</div>'

            if (haveBottom) {
                html += '<div class="bottom row w-100">'
            }

            if (typeof point.dataset.googleMapUrl != "undefined" && point.dataset.googleMapUrl !== "") {
                html += '<a href="' + point.dataset.googleMapUrl + '" target="_blank" class="map-link col">'
                html += '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M447.7 364l.3 104c0 6.6-5.4 12-12 12l-104-.3c-6.6 0-12-5.4-12-12v-10c0-6.6 5.4-12 12-12l58 .3.7-.7L224 278.6 57.3 445.3l.7.7 58-.3c6.6 0 12 5.4 12 12v10c0 6.6-5.4 12-12 12L12 480c-6.6 0-12-5.4-12-12l.3-104c0-6.6 5.4-12 12-12h10c6.6 0 12 5.4 12 12l-.3 58 .7.7L201.4 256 34.7 89.3l-.7.7.3 58c0 6.6-5.4 12-12 12h-10c-6.6 0-12-5.4-12-12L0 44c0-6.6 5.4-12 12-12l104 .3c6.6 0 12 5.4 12 12v10c0 6.6-5.4 12-12 12L58 66l-.7.7L224 233.4 390.7 66.7l-.7-.7-58 .3c-6.6 0-12-5.4-12-12v-10c0-6.6 5.4-12 12-12l104-.3c6.6 0 12 5.4 12 12l-.3 104c0 6.6-5.4 12-12 12h-10c-6.6 0-12-5.4-12-12l.3-58-.7-.7L246.6 256l166.7 166.7.7-.7-.3-58c0-6.6 5.4-12 12-12h10c6.6 0 12 5.4 12 12z"/></svg>'
                html += '<span class="text">' + data.dataset.enlargeTxt + '</span>'
                html += '</a>'
                display = true
            }

            if (typeof point.dataset.googleMapDirectionUrl != "undefined" && point.dataset.googleMapDirectionUrl !== "") {
                html += '<a href="' + point.dataset.googleMapDirectionUrl + '" target="_blank" class="direction-link col">'
                html += '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M502.61 233.32L278.68 9.39C272.42 3.13 264.21 0 256 0s-16.42 3.13-22.68 9.39L9.39 233.32c-12.52 12.53-12.52 32.83 0 45.36l223.93 223.93c6.26 6.26 14.47 9.39 22.68 9.39s16.42-3.13 22.68-9.39l223.93-223.93c12.52-12.53 12.52-32.83 0-45.36zM255.95 479.98L32.02 255.95 255.95 32.01c.01 0 .02-.01.05-.01l.05.02 223.93 224.03-224.03 223.93zM330.89 224H208c-26.51 0-48 21.49-48 48v40c0 4.42 3.58 8 8 8h16c4.42 0 8-3.58 8-8v-40c0-8.84 7.16-16 16-16h122.89l-54.63 50.43c-3.25 3-3.45 8.06-.45 11.3l10.84 11.74c3 3.25 8.06 3.45 11.3.45l78.4-72.36c4.87-4.52 7.66-10.94 7.66-17.58s-2.78-13.06-7.72-17.62l-78.34-72.31c-3.25-3-8.31-2.79-11.3.45l-10.84 11.74c-3 3.25-2.79 8.31.45 11.3L330.89 224z"/></svg>'
                html += '<span class="text">' + data.dataset.directionTxt + '</span>'
                html += '</a>'
                display = true
            }

            if (typeof point.dataset.link != "undefined" && point.dataset.link !== "") {
                let target = parseInt(point.dataset.linkTarget) === 1 ? 'target="_blank"' : ""
                html += '<a href="' + point.dataset.link + '" ' + target + ' class="target-link col">'
                html += '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M301.148 394.702l-79.2 79.19c-50.778 50.799-133.037 50.824-183.84 0-50.799-50.778-50.824-133.037 0-183.84l79.19-79.2a132.833 132.833 0 0 1 3.532-3.403c7.55-7.005 19.795-2.004 20.208 8.286.193 4.807.598 9.607 1.216 14.384.481 3.717-.746 7.447-3.397 10.096-16.48 16.469-75.142 75.128-75.3 75.286-36.738 36.759-36.731 96.188 0 132.94 36.759 36.738 96.188 36.731 132.94 0l79.2-79.2.36-.36c36.301-36.672 36.14-96.07-.37-132.58-8.214-8.214-17.577-14.58-27.585-19.109-4.566-2.066-7.426-6.667-7.134-11.67a62.197 62.197 0 0 1 2.826-15.259c2.103-6.601 9.531-9.961 15.919-7.28 15.073 6.324 29.187 15.62 41.435 27.868 50.688 50.689 50.679 133.17 0 183.851zm-90.296-93.554c12.248 12.248 26.362 21.544 41.435 27.868 6.388 2.68 13.816-.68 15.919-7.28a62.197 62.197 0 0 0 2.826-15.259c.292-5.003-2.569-9.604-7.134-11.67-10.008-4.528-19.371-10.894-27.585-19.109-36.51-36.51-36.671-95.908-.37-132.58l.36-.36 79.2-79.2c36.752-36.731 96.181-36.738 132.94 0 36.731 36.752 36.738 96.181 0 132.94-.157.157-58.819 58.817-75.3 75.286-2.651 2.65-3.878 6.379-3.397 10.096a163.156 163.156 0 0 1 1.216 14.384c.413 10.291 12.659 15.291 20.208 8.286a131.324 131.324 0 0 0 3.532-3.403l79.19-79.2c50.824-50.803 50.799-133.062 0-183.84-50.802-50.824-133.062-50.799-183.84 0l-79.2 79.19c-50.679 50.682-50.688 133.163 0 183.851z"/></svg>'
                html += '<span class="text">' + point.dataset.linkLabelIcon + '</span>'
                html += '</a>'
                display = true
            }

            if (haveBottom) {
                html += '</div>'
            }

            html += '</div>'
            html += '</div>'

            if (display) {
                marker.bindPopup(html)
                L.popup({
                    autoClose: true
                })
            }
        }
    }
}