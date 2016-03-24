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

  // Add search.
  var searchCtrl = L.control.fuseSearch();
  searchCtrl.addTo(map);

  // Add points.
  function addDataToMap(data, map, spiderfier, icon) {
    var dataLayer = L.geoJson(data, {
      pointToLayer: function (feature, latLng) {
        var marker = new L.marker(latLng, {icon: icon});
        marker.feature = feature;
        spiderfier.addMarker(marker);
        return marker;
      }
    });
    dataLayer.addTo(map);
  }

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

  // Add spiderfier to expand overlapping markers on click.
  // @see https://github.com/jawj/OverlappingMarkerSpiderfier-Leaflet
  var spiderfier = new OverlappingMarkerSpiderfier(map);
  spiderfier.nearbyDistance = 20;
  spiderfier.keepSpiderfied = true;
  // Rather than attaching a listener to each layer, attach a listener to the spiderfier.
  var popup = new L.Popup({closeButton: false, offset: new L.Point(0.5, -24)});
  spiderfier.addListener('click', function(marker) {
    popup.setContent(marker.feature.properties.name);
    popup.setLatLng(marker.getLatLng());
    map.openPopup(popup);
    // I don't know why it's called 'nothing,' nor how to change
    // it, but this is the entire node display.
    $("div.sidebar__content").html(marker.feature.properties.nothing);
  });

  $.getJSON('/points?_format=json', function (data) {
    addDataToMap(data, map, spiderfier, new pmpIcon);
    searchCtrl.indexFeatures(data, ['title_1', 'field_address_text', 'description', 'field_tags']);
  });

  // Add zoom controls in bottom right of map.
  new L.Control.Zoom({position: 'bottomright'}).addTo(map);

})(jQuery);
