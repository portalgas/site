<?php
if(empty($results))	
	echo $this->Tabs->messageNotOrders();
else {

	echo $this->Tabs->drawTabsAjax($results);
	echo '<div class="tab-content deliveries">';
	foreach($results as $numTabs => $result)
		echo '<div class="tab-pane deliveries" id="tabs-'.$numTabs.'"></div>';
	echo '</div>';
?>
	<div id="calendar_view"></div>
	
	<div id="myModal" class="modal fade">
	 <div class="modal-dialog modal-lg">
	  <div class="modal-content">
	   <div class="modal-header">
	    <button type="button" class="close" data-dismiss="modal">&times;</button>
	    <h4 class="modal-title">Legenda</h4>
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
	jQuery(document).ready(function() {

		jQuery('#tabs a').click(function (e) {
			e.preventDefault();
		  	jQuery(this).tab('show');
		})
		
		<?php
		/*
		 * la prima volta carico il tab 0 e richiama drawDelivery()
		 */ 
		if(isset($results[0]['Delivery']['data'])) {
			echo "jQuery('#tabs a:first').tab('show');";			echo "\r\n";
			echo 'drawDelivery(\''.$results[0]['Delivery']['data'].'\', 0)';
		}
		?>
		
		jQuery('#myModal').on('show.bs.modal', function (e) {
			var url = "'/?option=com_cake&controller=PopUp&action=delivery_info&format=notmpl";
			jQuery(".modal-body").load(url).animate({ opacity: 1}, 750);
		});		
		
		jQuery('.selectpicker').selectpicker({
			style: 'selectpicker selectpicker-lg'
		});
	
		jQuery('a').tooltip();
		
		jQuery('#slideshow_<?php echo $orderResult['Order']['id'];?>').carousel({
			interval: false
		}) 
		
	});

	
	function drawDelivery(deliveryData, numTabs) {
		
		jQuery('#calendar_view').html('');
		jQuery('.tab-pane.deliveries').html('');
		jQuery('#tabs-'+numTabs).addClass('active');
		jQuery('#tabs-'+numTabs).css('min-height','100');
		jQuery('#tabs-'+numTabs).css('background', 'url("/images/cake/ajax-loader.gif") no-repeat scroll center 0 transparent');
		
		jQuery.ajax({
				type: "GET",
				url: "/?option=com_cake&controller=Deliveries&action=tabsAjaxEcommDeliveries&data="+deliveryData+"&format=notmpl", 
				data: "",
				success: function(response){
					jQuery('#tabs-'+numTabs).css('background', 'none repeat scroll 0 0 transparent');
					jQuery('#tabs-'+numTabs).html(response);
				},
				error:function (XMLHttpRequest, textStatus, errorThrown) {
					jQuery('#tabs-'+numTabs).css('background', 'none repeat scroll 0 0 transparent');
					jQuery('#tabs-'+numTabs).html(textStatus);
				}
		});
	}
</script>
<?php
} // if(empty($results))
?>