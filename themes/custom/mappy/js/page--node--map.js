(function ($) {
    // Add basemap.
    var baseLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://cartodb.com/attributions">CartoDB</a>'
    });

    nodePoint = [drupalSettings.mappy.nodepageMap.lat, drupalSettings.mappy.nodepageMap.lon];

    // Create map and set center and zoom.
    var map = L.map('map', {
        zoomControl: false,
        scrollWheelZoom: false,
        center: nodePoint,
        zoom: 14
    });

    // Add basemap to map.
    map.addLayer(baseLayer);

    // Set path to marker image.
    L.Icon.Default.imagePath = '/themes/custom/mappy/images/leaflet';
    var pmpIcon = L.Icon.extend({
        options: {
            iconUrl: '/themes/custom/mappy/images/leaflet/pmp-marker-icon-green.png',
            iconRetinaUrl: '/themes/custom/mappy/images/leaflet/pmp-marker-icon-green-2x.png',
            iconSize: [25, 41],
            iconAnchor: [13, 40],
            popupAnchor: [1, -46]
        }
    });

    L.marker(nodePoint, {icon: new pmpIcon}).addTo(map).bindPopup(drupalSettings.mappy.nodepageMap.label).openPopup();

    // Add zoom controls in bottom right of map.
    new L.Control.Zoom({position: 'bottomright'}).addTo(map);

})(jQuery);