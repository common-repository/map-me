function initialize() {

  // Variable from wp shortcode
  if (!map_data) return;

  //console.log(map_data);

  var center = map_data.center;
  var locations = map_data.locations;
  var options = map_data.options;

  var mapType = options.type;

  if (!mapType) mapType = 'roadmap';

  map = new google.maps.Map(document.getElementById('googleMap'), {
    zoom: Number(options.zoom),
    scrollwheel: options.scroll,
    center: new google.maps.LatLng(center.latitude, center.longitude),
    mapTypeId: mapType,
    disableDefaultUI: options.controls,
    styles: window[options.style]
  });

  var infowindow = new google.maps.InfoWindow({ maxWidth: 250 });

  var marker, i;
  for (i = 0; i < locations.length; i++) {

    if (!locations[i].latitude || !locations[i].longitude) continue;

    var latlng = new google.maps.LatLng(locations[i].latitude, locations[i].longitude);

    marker = new google.maps.Marker({
      position: latlng,
      map: map //remove if offset is taking place
    });

    marker.setIcon(locations[i].icon);

    if (locations[i].animation) {
      var marker_animation = locations[i].animation;

      marker.setZIndex(google.maps.Marker.MAX_ZINDEX + 1);

      if (marker_animation == 'BOUNCE') {
        marker.setAnimation(google.maps.Animation.BOUNCE);
      }
      if (marker_animation == 'DROP') {
        marker.setAnimation(google.maps.Animation.DROP);
      }
    }

    if (locations[i].info_window) {
      google.maps.event.addListener(
        marker,
        'click',
        (function(marker, i) {
          return function() {
            infowindow.setContent(locations[i].info_window_data);
            infowindow.open(map, marker);
          };
        })(marker, i)
      );
    }
  }
}

google.maps.event.addDomListener(window, 'load', initialize);
