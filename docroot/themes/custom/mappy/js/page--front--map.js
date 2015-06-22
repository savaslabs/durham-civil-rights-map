(function ($) {
    // Add basemap.
    var baseLayer = L.tileLayer('http://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="http://cartodb.com/attributions">CartoDB</a>'
    });

    // Create map and set center and zoom.
    var map = L.map('map', {
        zoomControl: false,
        scrollWheelZoom: false,
        center: [35.9908385, -78.9005222],
        zoom: 12
    });

    // Add basemap to map.
    map.addLayer(baseLayer);

    // Add points.
    function addDataToMap(data, map) {
        var dataLayer = L.geoJson(data, {
            onEachFeature: function(feature, layer) {
                var popupText = feature.properties.name;
                layer.bindPopup(popupText);
                layer.on('click', function(e) {
                    $("div.sidebar").html(feature.properties.description);
                });
            }
        });
        dataLayer.addTo(map);
    }

    $.getJSON('/points', function(data) {
        addDataToMap(data, map);
    });

    // Set path to marker image. TODO: Update this to something custom.
    L.Icon.Default.imagePath = '/themes/custom/mappy/images/leaflet';

    // Add zoom controls in bottom right of map.
    new L.Control.Zoom({ position: 'bottomright' }).addTo(map);

})(jQuery);
