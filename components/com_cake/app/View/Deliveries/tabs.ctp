<?php
echo '<div id="tabsDelivery">';

echo '<h2>'.__('Delivery').'</h2>';

if(empty($results))
?>
<?php
	echo '<div class="tab-content deliveries">';
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
	
	
			e.preventDefault()
		  	jQuery(this).tab('show')
		})
				
		if(isset($results[0]['Delivery']['data'])) {
		
		jQuery('#myModal').on('show.bs.modal', function (e) {
			var url = "'/?option=com_cake&controller=PopUp&action=delivery_info&format=notmpl";
			jQuery(".modal-body").load(url).animate({ opacity: 1}, 750);
		});
		
		jQuery('#myModalScheda').on('show.bs.modal', function (e) {
  			var invoker = jQuery(e.relatedTarget);
			var url = invoker.attr('url');
			jQuery(".modal-body").load(url).animate({ opacity: 1}, 750);
		});	
		
		jQuery('#tabs-'+numTabs).css('min-height','100px');
		jQuery('#tabs-'+numTabs).css('background', 'url("/images/cake/ajax-loader.gif") no-repeat scroll center 0 transparent');