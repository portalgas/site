<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo Configure::read('GoogleKey');?>&sensor=false&v=3.exp"></script>

<script type="text/javascript">
var marker = new Array();
var icon1 = '/images/cake/puntina.png';
var icon2 = '/images/cake/puntina03.png';

jQuery(document).ready(function () {

    var map;
    var myOptions = {
        zoom: 6,
        center: new google.maps.LatLng(41.871132,12.454501), /* rome */
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("map"), myOptions);
		
	function isInfoWindowOpen(infoWindow){
		var map = infoWindow.getMap();
		return (map !== null && typeof map !== "undefined");
	}

	<?php
	foreach ($results as $numResult => $result) {	
	
		$name = str_replace("'", "", $result['Organization']['name']);
		$url = Configure::read('App.server').'/home-'.$result['Organization']['j_seo'];
		$address = $result['Organization']['localita'].' ';
		if(!empty($result['Organization']['provincia'])) $address .= '('.$result['Organization']['provincia'].') ';
		if(!empty($result['Organization']['indirizzo'])) $address .= $result['Organization']['indirizzo'].' ';
		if(!empty($result['Organization']['cap'])) $address .= $result['Organization']['cap'].' ';
		$address = str_replace("'", "", $address);
		
		$img = '';
		if(!empty($result['Organization']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Organization']['img1']))
			$img = '<img width="50" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" />';			
	?>	
		/* console.log("<?php echo $result['Organization']['name'];?>"); */
	
		var latlng = new google.maps.LatLng(<?php echo $result['Organization']['lat'];?>, <?php echo $result['Organization']['lng'];?>);
		
		var contentString<?php echo $result['Organization']['id'];?> = '<h3><?php echo $name;?></h3>' + 
				'<p><?php echo $img;?></p>' +
				'<p><b>Indirizzo</b> <?php echo $address;?></p>' + 
				'<p><b>Email</b> <?php if(!empty($result['Organization']['mail'])) echo '<a href="/contattaci?contactOrganizationId='.$result['Organization']['id'].'" title="scrivi una mail al G.A.S.">Contattaci scrivendo una mail</a>';?> </p>' +
				'<p><a href="<?php echo $url;?>">Pagina del G.A.S. </a></p>';
		
		var infowindow<?php echo $result['Organization']['id'];?> = new google.maps.InfoWindow({
				content: contentString<?php echo $result['Organization']['id'];?>
	    });

	  
		marker[<?php echo $result['Organization']['id'];?>] = new google.maps.Marker({
									position: latlng,
									map: map,
									icon: icon1,
									title: '<?php echo $name;?>'
								});
		
		google.maps.event.addListener(marker[<?php echo $result['Organization']['id'];?>], 'click', function() {
			if (isInfoWindowOpen(infowindow<?php echo $result['Organization']['id'];?>)) 
				infowindow<?php echo $result['Organization']['id'];?>.close();
			else
				infowindow<?php echo $result['Organization']['id'];?>.open(map, this);
		});
		
		google.maps.event.addListener(marker[<?php echo $result['Organization']['id'];?>], 'mouseover', function() {
			this.setIcon(icon2);
		});
		
		google.maps.event.addListener(marker[<?php echo $result['Organization']['id'];?>], 'mouseout', function() {
			this.setIcon(icon1);
		});
	<?php
	}
	?>	
		
	jQuery('.listsUser > li > a').mouseover(function () {
		var organization_id = jQuery(this).attr('data-attr-id');
		marker[organization_id].setIcon(icon2);
		return false;
	});
	
	jQuery('.listsUser > li > a').mouseout(function () {
		var organization_id = jQuery(this).attr('data-attr-id');
		marker[organization_id].setIcon(icon1);
		return false;
	});	
	
	jQuery('.listsUser > li > a').click(function () {
		var organization_id = jQuery(this).attr('data-attr-id');
		google.maps.event.trigger(marker[organization_id], 'click');
		return false;
	});	
});
</script>


<div id="map" style="width: 100%; height: 650px"></div>

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
foreach ($results as $result) {	

	$name = str_replace("'", "", $result['Organization']['name']);
	$address = $result['Organization']['provincia'];
	$address = str_replace("'", "", $address);	

	echo '<li><a data-attr-id="'.$result['Organization']['id'].'">'.$name.' ('.$result['Organization']['provincia'].')</a></li>';
	
}
echo '</ul>';
?>