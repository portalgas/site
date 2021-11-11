<?php
echo '<div id="tabsDelivery">';

echo '<h2>'.__('Delivery').'</h2>';

if(empty($results))	echo $this->Tabs->messageNotOrders();else {
?>
<?php	echo $this->Tabs->drawTabsAjax($results);
	echo '<div class="tab-content deliveries">';	foreach($results as $numTabs => $result)		echo '<div class="tab-pane deliveries" id="tabs-'.$numTabs.'"></div>';	echo '</div>';
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
	
		<script type="text/javascript">	$(document).ready(function() {				$('#tabs a').click(function (e) {
			e.preventDefault()
		  	$(this).tab('show')
		})
						<?php		/*		 * la prima volta carico il tab 0 e richiama drawDelivery()		 */ 
		if(isset($results[0]['Delivery']['data'])) {			echo "$('#tabs a:first').tab('show');";			echo "\r\n";			echo 'drawDelivery(\''.$results[0]['Delivery']['data'].'\', 0)';		}		?>
		
		$('#myModal').on('show.bs.modal', function (e) {
			var url = "'/?option=com_cake&controller=PopUp&action=delivery_info&format=notmpl";
			$(".modal-body").load(url).animate({ opacity: 1}, 750);
		});
		
		$('#myModalScheda').on('show.bs.modal', function (e) {
  			var invoker = $(e.relatedTarget);
			var url = invoker.attr('url');
			$(".modal-body").load(url).animate({ opacity: 1}, 750);
		});		});		function drawDelivery(deliveryData, numTabs) {
				$('#calendar_view').html('');		$('#tabs-'+numTabs).html('');	
		$('#tabs-'+numTabs).css('min-height','100px');		$('#tabs-'+numTabs).addClass('active');
		$('#tabs-'+numTabs).css('background', 'url("/images/cake/ajax-loader.gif") no-repeat scroll center 0 transparent');				$.ajax({				type: "GET",				url: "/?option=com_cake&controller=Deliveries&action=tabsAjaxDeliveries&data="+deliveryData+"&format=notmpl", 				data: "",				success: function(response){					$('#tabs-'+numTabs).css('background', 'none repeat scroll 0 0 transparent');					$('#tabs-'+numTabs).html(response);				},				error:function (XMLHttpRequest, textStatus, errorThrown) {					$('#tabs-'+numTabs).css('background', 'none repeat scroll 0 0 transparent');					$('#tabs-'+numTabs).html(textStatus);				}		});	}	</script><?php} // if(empty($results))?></div>