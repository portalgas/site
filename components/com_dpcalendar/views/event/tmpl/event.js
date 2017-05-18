dpjQuery(document).ready(function() {
	if (dpjQuery('#dp-event-details-map').length < 1) {
		return;
	}
	var dpcalendarMapZoom = dpjQuery('#dp-event-details-map').attr('data-zoom');
	if (dpcalendarMapZoom == null) {
		dpcalendarMapZoom = 4;
	}
	
	var lat = 0; 
	var long = 0;
	if( typeof geoip_latitude === 'function' ) {
		lat = geoip_latitude(); 
		long = geoip_longitude(); 
	}
	
	var dpcalendarMap = new google.maps.Map(document.getElementById('dp-event-details-map'), {zoom: parseInt(dpcalendarMapZoom), mapTypeId: google.maps.MapTypeId.ROADMAP, center: new google.maps.LatLng(lat, long)});
	var dpcalendarMapBounds = new google.maps.LatLngBounds();
	var dpcalendarMapMarkers = [];
	
	dpjQuery('.dp-location').each(function(i) {
		var latitude = dpjQuery(this).data('latitude');
		var longitude = dpjQuery(this).data('longitude');
		if (latitude == null || latitude == "") {
			return;
		}
		var l = new google.maps.LatLng(latitude, longitude);
		var marker = new google.maps.Marker({position: l, map: dpcalendarMap, title: dpjQuery(this).data('title')});
	 	
	 	dpcalendarMapBounds.extend(l);
	 	dpcalendarMap.setCenter(dpcalendarMapBounds.getCenter());
	});
});