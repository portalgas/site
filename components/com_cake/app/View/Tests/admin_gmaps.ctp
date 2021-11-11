<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo Configure::read('GoogleApiKey');?>&v=3.exp"></script>

<script type="text/javascript">
var marker = new Array();
var icon1 = '/images/cake/puntina.png';
var icon2 = '/images/cake/puntina03.png';

$(document).ready(function () {

    var map;
    var myOptions = {
        zoom: 12,
        center: new google.maps.LatLng(<?php echo $user->organization['Organization']['lat'];?>, <?php echo $user->organization['Organization']['lng'];?>),
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("map"), myOptions);
	
	var totMarker = 0;
	<?php
	foreach ($results as $numResult => $result) {	
	
		$name = str_replace("'", "", $result['User']['name']);
		$address = str_replace("'", "", $result['Profile']['address']);
		// if($numResult<=0) {
	?>	
		/* console.log("<?php echo $result['User']['name'];?>"); */
	
		var latlng = new google.maps.LatLng(<?php echo $result['Profile']['lat'];?>, <?php echo $result['Profile']['lng'];?>);
		
		var contentString<?php echo $result['User']['id'];?> = '<h3><?php echo $name;?></h3>' + 
				'<p><?php echo $this->App->drawUserAvatar($user, $result['User']['id'], $result['User']);?></p>' +
				'<p><b>Indirizzo</b> <?php echo $address;?>' +      
				'<p><b>Email</b> <?php if(!empty($result['User']['email'])) echo '<a href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a>';?>' + 
				'<p><b>Telefono</b> <?php echo $result['Profile']['phone'];?>';
		
		var infowindow<?php echo $result['User']['id'];?> = new google.maps.InfoWindow({
				content: contentString<?php echo $result['User']['id'];?>
	    });

	  
		marker[totMarker] = new google.maps.Marker({
									position: latlng,
									map: map,
									icon: icon1,
									title: '<?php echo $name;?>'
								});
		
		google.maps.event.addListener(marker[totMarker], 'click', function() {
			infowindow<?php echo $result['User']['id'];?>.open(map, this);
		});
		
		google.maps.event.addListener(marker[totMarker], 'mouseover', function() {
			this.setIcon(icon2);
		});
		
		google.maps.event.addListener(marker[totMarker], 'mouseout', function() {
			this.setIcon(icon1);
		});

		totMarker++;
	<?php
		// }
	}
	?>	
		
	$('.listsUser > li > a').mouseover(function () {
		var markerId = $(this).attr('markerId');
		marker[markerId].setIcon(icon2);
		return false;
	});
	
	$('.listsUser > li > a').mouseout(function () {
		var markerId = $(this).attr('markerId');
		marker[markerId].setIcon(icon1);
		return false;
	});
	
});
</script>


<div id="map" style="width: 100%; height: 500px"></div>

<style>
ul.listsUser {
    list-style: outside none none;
	margin: 5px 0;
}
ul.listsUser > li {
    float: left;
	padding: 5px;
}
.cakeContainer ul.listsUser > li > a {
    font-weight: normal;
    text-decoration: none;
}
</style>
<?php
echo '<ul class="listsUser">';
foreach ($results as $numResult => $result) {	

	$name = str_replace("'", "", $result['User']['name']);
	$address = str_replace("'", "", $result['Profile']['address']);

	echo '<li><a markerId="'.$numResult.'">'.$name.'</a></li>';
	
}
echo '</ul>';
?>