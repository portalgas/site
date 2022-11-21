<?php
$this->App->d($results);
?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo Configure::read('GoogleApiKey');?>&sensor=false&v=3.exp"></script>

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
	
	function isInfoWindowOpen(infoWindow){
		var map = infoWindow.getMap();
		return (map !== null && typeof map !== "undefined");
	}

	<?php
	foreach ($results as $numResult => $result) {	
	
		$user_name = str_replace("'", "", $result['User']['name']);
		$address = str_replace("'", "", $result['Profile']['address']);
		$phone = str_replace("'", "", $result['Profile']['phone']);
		$phone2 = str_replace("'", "", $result['Profile']['phone2']);
		
		/*
		 * satispay
		 */
		$satispay = 'N'; 
		$satispay_phone = '';
		if(isset($result['Profile']['satispay'])) {
			$satispay = $result['Profile']['satispay'];
			if($satispay=='Y') {
				$satispay_phone = str_replace("'", "", $result['Profile']['satispay_phone']);
				if($satispay_phone==$phone)
					$satispay_phone = '';
			}
		}
		
		$options['title'] = $user_name;
		$options['alt'] = $user_name;
		// if($numResult<=0) {
	?>	
		/* console.log("<?php echo $user_name;?>"); */
	
		var latlng = new google.maps.LatLng(<?php echo $result['Profile']['lat'];?>, <?php echo $result['Profile']['lng'];?>);
		
		var contentString<?php echo $result['User']['id'];?> = '<h3><?php echo $user_name;?></h3>' + 
				'<p><?php echo $this->App->drawUserAvatar($user, $result['User']['id'], $result['User'], $options);?></p>' +
				'<p><b>Indirizzo</b> <?php echo $address;?></p>' +      
				'<p><b>Email</b> <?php if(!empty($result['User']['email'])) echo '<a href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a>';?></p>' + 
				'<p><b>Telefono</b> <?php echo $result['Profile']['phone'];?></p>';

				<?php
				if($satispay=='Y') {
				?>
					contentString<?php echo $result['User']['id'];?> += '<p><img src="/images/satispay.png" style="width:50px;" /> Ha Satispay</p>';
					
					<?php
					if(!empty($satispay_phone)) {
					?>	
						contentString<?php echo $result['User']['id'];?> += '<p><b>Telefono per Satispay</b> <?php echo $satispay_phone;?></p>';
					<?php
					} // end if
					?>	
				<?php
				} // end if
				?>	
				
				<?php
				if(isset($result['SuppliersOrganization'])) {
				?>
					contentString<?php echo $result['User']['id'];?> += '<p><button class="btn btn-info btn-sm" type="button" data-toggle="collapse" data-target="#referenti-<?php echo $result['User']['id'];?>" aria-expanded="false" aria-controls="referenti-<?php echo $result['User']['id'];?>">Referente dei produttori</button></p>' +
																		'<p><div class="collapse" id="referenti-<?php echo $result['User']['id'];?>"></p>';
					<?php
					foreach ($result['SuppliersOrganization'] as $numResult2 => $suppliersOrganization) {	
						$name = str_replace("'"," ",$suppliersOrganization['SuppliersOrganization']['name']);
						$descrizione = str_replace("'"," ", $suppliersOrganization['SuppliersOrganization']['descrizione']);
						$descrizione = str_replace("<br/>"," ", $descrizione);
						$descrizione = str_replace("<br />"," ", $descrizione);
						$descrizione = str_replace("<br>"," ", $descrizione);
						
						$img = "";
						if(!empty($suppliersOrganization['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$suppliersOrganization['Supplier']['img1']))
							$img .= '<span><img width="35" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$suppliersOrganization['Supplier']['img1'].'" alt="'.$name.'" /></span> ';						
					?>
						contentString<?php echo $result['User']['id'];?> += '<p><?php echo $img.$name;?></p>';
					<?php
					} // end loop
					?>
					contentString<?php echo $result['User']['id'];?> += '</div></p>';
				<?php
				} // end if
				?>				

		
		var infowindow<?php echo $result['User']['id'];?> = new google.maps.InfoWindow({
				content: contentString<?php echo $result['User']['id'];?>
	    });

	  
		marker[totMarker] = new google.maps.Marker({
									position: latlng,
									map: map,
									icon: icon1,
									title: '<?php echo $user_name;?>'
								});
		
		google.maps.event.addListener(marker[totMarker], 'click', function() {
			if (isInfoWindowOpen(infowindow<?php echo $result['User']['id'];?>)) 
				infowindow<?php echo $result['User']['id'];?>.close();
			else
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
		var user_id = $(this).attr('data-attr-id');
		marker[user_id].setIcon(icon2);
		return false;
	});
	
	$('.listsUser > li > a').mouseout(function () {
		var user_id = $(this).attr('data-attr-id');
		marker[user_id].setIcon(icon1);
		return false;
	});
	
	$('.listsUser > li > a').click(function () {
		var user_id = $(this).attr('data-attr-id');
		google.maps.event.trigger(marker[user_id], 'click');
		return false;
	});
	
	
});
</script>


<div id="map" style="width: 100%; height: 500px"></div>

<style>
ul.listsUser {
    list-style: outside none none;
	margin: 5px 0;
	padding: 0;
}
ul.listsUser > li {
    float: left;
	padding: 5px;
	cursor:pointer;
}
.cakeContainer ul.listsUser > li > a {
    font-weight: normal;
    text-decoration: none;
}
</style>
<?php
echo '<ul class="listsUser">';
foreach ($results as $user_id => $result) {	

	$user_name = str_replace("'", "", $result['User']['name']);
	$address = str_replace("'", "", $result['Profile']['address']);

	echo '<li><a data-attr-id="'.$user_id.'">'.$user_name.'</a></li>';
	
}
echo '</ul>';
?>