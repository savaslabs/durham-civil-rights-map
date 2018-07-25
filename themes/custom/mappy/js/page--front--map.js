(function ($) {
  // Add basemap.
  var baseLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://cartodb.com/attributions">CartoDB</a>'
  });

  // Create map and set center and zoom.
  var map = L.map('map', {
    zoomControl: false,
    scrollWheelZoom: false,
    center: [35.9908385, -78.93],
    zoom: 13
  });

  // Add basemap to map.
  map.addLayer(baseLayer);

  // Add search.
  var searchCtrl = L.control.fuseSearch();
  searchCtrl.addTo(map);

  // Add points.
  function addDataToMap(data, map, icon) {
    var dataLayer = L.geoJson(data, {
      pointToLayer: function (feature, latLng) {
        return L.marker(latLng, {icon: icon}).addTo(map);
      },
      onEachFeature: function (feature, layer) {
        feature.layer = layer;
        var popupText = feature.properties.name;
        layer.bindPopup(popupText);
        layer.on('click', function (e) {
          // I don't know why it's called 'nothing,' nor how to change
          // it, but this is the entire node display.
          $("div.sidebar__content").html(feature.properties.nothing);
          ga('send', {
            hitType: 'event',
            eventCategory: 'Map',
            eventAction: 'click',
            eventLabel: feature.properties.title_1
          });
        });
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

  $.getJSON('/points?_format=json', function (data) {
    addDataToMap(data, map, new pmpIcon);
    searchCtrl.indexFeatures(data, ['title_1', 'field_address_text', 'description', 'field_tags']);
    ga('send', {
      hitType: 'event',
      eventCategory: 'Map',
      eventAction: 'loaded',
      eventLabel: 'Map JSON loaded',
      nonInteraction: true
    });

  });

  // Add zoom controls in bottom right of map.
  new L.Control.Zoom({position: 'bottomright'}).addTo(map);

})(jQuery);
