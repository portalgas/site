<?php
// no direct access
defined('_JEXEC') or die;

// echo '<h3>j_content_id '.$j_content_id.'</h3>';

if(!empty($j_content_id)) {

	$address = '';
	if(!empty($supplier['indirizzo']) && !empty($supplier['localita'])) 
		$address = $supplier['indirizzo'].' '.$supplier['localita'].' '.$supplier['provincia'].' '.$supplier['cap'];
	
	// echo '<h3>address '.$address.'</h3>';
		
	if(!empty($address)) {
	
		$name = str_replace("'", "", $supplier['name']);
		$indirizzo = str_replace("'", "", $supplier['indirizzo']);
		$localita = str_replace("'", "", $supplier['localita']);
		$indirizzo = $indirizzo.' '.$localita;
?>
			<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBAYuif5WmS-Fpv23_w5nmIajjGv5lD8tc&sensor=false&v=3.exp"></script>

			<script type="text/javascript">
			var geocoder = new google.maps.Geocoder();
			geocoder.geocode( {'address': "<?php echo $address;?>"}, function(results, status) {
				
				//console.log("status "+status+ " results[0].geometry.location "+results[0].geometry.location);
				
				if (status == google.maps.GeocoderStatus.OK) {
					var options = {
						zoom: 13,
						center: results[0].geometry.location,
						mapTypeId: google.maps.MapTypeId.ROADMAP
					};	
					
					var contentString = '<h3><?php echo $name;?></h3>' + 
										'<p><b>Indirizzo</b> <?php echo $indirizzo;?>' +      
										'<p><b>Email</b> <?php if(!empty($supplier['mail'])) echo '<a href="mailto:'.$supplier['mail'].'">'.$supplier['mail'].'</a>';?>' + 
										'<p><b>Telefono</b> <?php echo $supplier['telefono'];?>';
					
					var infowindow = new google.maps.InfoWindow({
							content: contentString
					});
					
					var map = new google.maps.Map(document.getElementById('map'), options);
					var marker = new google.maps.Marker({position: results[0].geometry.location, map: map});
					
					google.maps.event.addListener(marker, 'click', function() {
						infowindow.open(map, this);
					});
					
				} else {
					console.log("Problema nella ricerca dell'indirizzo: " + status);
				}			
			});
			</script>

			<div id="map" style="width: 100%; height: 500px"></div>
<?php
	} // if(!empty($address))
}
?>