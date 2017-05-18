<?php
echo '<div id="ajaxContent">';
echo '<div id="tabsDelivery">';echo '<h2>Il tuo carrello della spesa</h2>';
if(empty($results))	echo $this->Tabs->messageNotOrders("Non sono stati ancora effettuati acquisti");else {	echo $this->Tabs->drawTabsAjax($results);
	echo '<div class="tab-content deliveries">';
	foreach($results as $numTabs => $result)
		echo '<div class="tab-pane deliveries" id="tabs-'.$numTabs.'"></div>';
	echo '</div>';
	?>	<div id="calendar_view"></div>		<script type="text/javascript">	jQuery(document).ready(function() {
		jQuery('#tabs a').click(function (e) {
			e.preventDefault()
		  	jQuery(this).tab('show')
		})
						<?php		/*		 * la prima volta carico il tab 0 e richiama drawDelivery()		 */ 		if(isset($results[0]['Delivery']['data'])) {
			echo "jQuery('#tabs a:first').tab('show');";
			echo "\r\n";
			echo 'drawDelivery(\''.$results[0]['Delivery']['data'].'\', 0)';
		}		?>	});		function drawDelivery(deliveryData, numTabs) {		jQuery('#calendar_view').html('');
		jQuery('#tabs-'+numTabs).html('');
		jQuery('#tabs-'+numTabs).css('min-height','100px');			jQuery('#tabs-'+numTabs).css('background', 'url("/images/cake/ajax-loader.gif") no-repeat scroll center 0 transparent');				jQuery.ajax({				type: "GET",				url: "/?option=com_cake&controller=Deliveries&action=tabsAjaxUserCartDeliveries&data="+deliveryData+"&format=notmpl", 				data: "",				success: function(response){					jQuery('#tabs-'+numTabs).css('background', 'none repeat scroll 0 0 transparent');					jQuery('#tabs-'+numTabs).html(response);				},				error:function (XMLHttpRequest, textStatus, errorThrown) {					jQuery('#tabs-'+numTabs).css('background', 'none repeat scroll 0 0 transparent');					jQuery('#tabs-'+numTabs).html(textStatus);				}		});	}	</script><?php} // if(empty($results))?>
</div>
</div>