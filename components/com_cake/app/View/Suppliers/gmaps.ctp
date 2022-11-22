<?php
$this->App->d($results);

/*
 * per linkare all'articolo di joomla
 */
$com_path = JPATH_SITE.'/components/com_content/';
require_once $com_path.'router.php';
require_once $com_path.'helpers/route.php';
?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo Configure::read('GoogleApiKey');?>&v=3.exp"></script>

<script type="text/javascript">
var marker = new Array();
var icon1 = '/images/cake/puntina.png';
var icon2 = '/images/cake/puntina03.png';

$(document).ready(function () {

    var map;
    var myOptions = {
        zoom: 6,
        center: new google.maps.LatLng(<?php echo $user->organization['Organization']['lat'];?>, <?php echo $user->organization['Organization']['lng'];?>),
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("map"), myOptions);
	
	var totMarker = 0;
	<?php
	foreach ($results as $numResult => $result) {	
	
		$name = str_replace("'", " ", $result['Supplier']['name']);
		$indirizzo = str_replace("'", "", $result['Supplier']['indirizzo']);
		$localita = str_replace("'", "", $result['Supplier']['localita']);
		$categoria = str_replace("'", "", $result['CategoriesSupplier']['name']);
		
		if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
			$img = '<img width="50" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" />';	
		
		// if($numResult<=0) {
	?>	
		/* console.log("<?php echo $result['Supplier']['name'];?>"); */
	
		var latlng = new google.maps.LatLng(<?php echo $result['Supplier']['lat'];?>, <?php echo $result['Supplier']['lng'];?>);
		
		var contentString<?php echo $result['Supplier']['id'];?> = '<h3><?php echo $name;?></h3>' + 
				'<p><?php echo $img;?></p>' +
				'<p><b>Categoria</b> <?php echo $categoria;?>' +
				'<p><b>Indirizzo</b> <?php echo $indirizzo;?> <?php echo $localita;?>';
		
		<?php
		/*
		if(!empty($result['Supplier']['j_content_id']) && !empty($result['CategoriesSupplier']['j_category_id'])) {
					
			$url = JRoute::_(ContentHelperRoute::getArticleRoute($result['Supplier']['j_content_id'], $result['CategoriesSupplier']['j_category_id'])).'?tmpl=popup';					
			
			echo "contentString".$result['Supplier']['id']." += '<p><a rel=\"nofollow\" data-toggle=\"modal\" data-target=\"#myModalScheda\" url=\"".$url."\" href=\"#\"><img border=\"0\" alt=\"Leggi la scheda del produttore\" src=\"".Configure::read('App.img.cake')."/apps/32x32/kontact.png\" /> Leggi la scheda del produttore</a></p>';";
		}
		*/
		if(!empty($result['Supplier']['slug'])) {
			$url = "https://neo.portalgas.it/site/produttore/".$result['Supplier']['slug'];
			echo "contentString".$result['Supplier']['id']." += '<p><a target=\"_blank\" href=\"".$url."\" title=\"".$name."\"><img border=\"0\" alt=\"Leggi la scheda del produttore\" src=\"".Configure::read('App.img.cake')."/apps/32x32/kontact.png\" /> Leggi la scheda del produttore</a></p>';";
		}

		if(isset($result['SuppliersOrganizationsReferent']) && !empty($result['SuppliersOrganizationsReferent'])) {

			echo "\ncontentString".$result['Supplier']['id']." += '<p><b>".__('Suppliers Organizations Referents')."</b></p>';";
				
			foreach($result['SuppliersOrganizationsReferent'] as $suppliersOrganizationsReferent) {
				
				$suppliersOrganizationsReferentName = str_replace("'", " ", $suppliersOrganizationsReferent['User']['name']);
				
				$tmp = '';
				$tmp .= '<div>';
				$tmp .= $suppliersOrganizationsReferentName;
				if(!empty($suppliersOrganizationsReferent['User']['email'])) {
					$suppliersOrganizationsReferentEmail = str_replace("'", "", $suppliersOrganizationsReferent['User']['email']);
					$tmp .= ' <a href="mailto:'.$email.'">'.$suppliersOrganizationsReferentEmail.'</a>';
				}
				$tmp .= '</div>';
				
				if(!empty($suppliersOrganizationsReferent['Profile']['address']) && !empty($suppliersOrganizationsReferent['Profile']['phone'])) 
					$tmp .= '<div>';
				
				if(!empty($suppliersOrganizationsReferent['Profile']['address'])) {
					$suppliersOrganizationsReferentAddress = str_replace("'", "", $suppliersOrganizationsReferent['Profile']['address']);
					$tmp .= $suppliersOrganizationsReferentAddress.' ';
				}
				if(!empty($suppliersOrganizationsReferent['Profile']['phone'])) {
					$suppliersOrganizationsReferentPhone = str_replace("'", "", $suppliersOrganizationsReferent['Profile']['address']);
					$tmp .= $suppliersOrganizationsReferentPhone.' ';
				}

				if(!empty($suppliersOrganizationsReferent['Profile']['address']) && !empty($suppliersOrganizationsReferent['Profile']['phone'])) 
					$tmp .= '</div>';
				
				echo "\ncontentString".$result['Supplier']['id']." += '<p>".$tmp."</p>';";
			}
		}
		?>
			
		
		var infowindow<?php echo $result['Supplier']['id'];?> = new google.maps.InfoWindow({
				content: contentString<?php echo $result['Supplier']['id'];?>
	    });

	  
		marker[totMarker] = new google.maps.Marker({
									position: latlng,
									map: map,
									icon: icon1,
									title: '<?php echo $name;?>'
								});
		
		google.maps.event.addListener(marker[totMarker], 'click', function() {
			infowindow<?php echo $result['Supplier']['id'];?>.open(map, this);
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
		var supplier_id = $(this).attr('data-attr-id');
		marker[supplier_id].setIcon(icon2);
		return false;
	});
	
	$('.listsUser > li > a').mouseout(function () {
		var supplier_id = $(this).attr('data-attr-id');
		marker[supplier_id].setIcon(icon1);
		return false;
	});
	
	$('.listsUser > li > a').click(function () {
		var supplier_id = $(this).attr('data-attr-id');
		google.maps.event.trigger(marker[supplier_id], 'click');
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
foreach ($results as $numResult => $result) {	

	$name = str_replace("'", "", $result['Supplier']['name']);
	$indirizzo = str_replace("'", "", $result['Supplier']['indirizzo']);

	echo '<li><a data-attr-id="'.$numResult.'">'.$name.'</a></li>';
}
echo '</ul>';
?>


<div id="myModalScheda" class="modal fade">
 <div class="modal-dialog modal-lg">
  <div class="modal-content">
   <div class="modal-header">
	<button type="button" class="close" data-dismiss="modal">&times;</button>
	<h4 class="modal-title">Produttore</h4>
   </div>
   <div class="modal-body">
   </div>
   <div class="modal-footer">
	<button type="button" class="btn btn-success" data-dismiss="modal">Chiudi</button>
   </div> 
  </div>
 </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
		
	$('#myModalScheda').on('show.bs.modal', function (e) {
		var invoker = $(e.relatedTarget);
		var url = invoker.attr('url');
		$(".modal-body").load(url).animate({ opacity: 1}, 750);
	});	
});
</script>