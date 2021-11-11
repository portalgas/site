<?php
$this->App->d($results);

if(empty($results) && empty($ordersToValidateResults))	
	echo $this->Tabs->messageNotOrders();
else {

	/*
	 * elenco P R O D U T T O R I  con ordini da validare i colli (pezzi_confezione)
	 */
	if(!empty($ordersToValidateResults)) {	
		
		echo '<div class="container" style="padding-left: 0px; margin-left: 0px; width: 100%; padding-bottom:5px;">';
		echo '<div class="col-xs-12">';
		
		echo '<form role="select" class="select-orders navbar-form" accept-charset="utf-8" method="get">';
		echo '<select id="all_order_to_validate" name="all_order_to_validate" size="1" data-live-search="true" class="orders_select selectpicker dropup" data-width="100%">';
		echo '<option value="">Seleziona il produttore per completare i colli</option>';
		foreach($ordersToValidateResults as $ordersToValidateResult) {

			echo "\n";
			if($ordersToValidateResult['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty'))
				$data_fine_validation = $this->Time->i18nFormat($ordersToValidateResult['Order']['data_fine_validation'],"%A %e %B %Y");
			else
				$data_fine_validation = "";
				
			echo '<option value="'.$ordersToValidateResult['id'].'">'.$ordersToValidateResult['SuppliersOrganization']['name'];
			echo ' - '.__('Delivery').' '.$ordersToValidateResult['Delivery']['name'];
			echo ' - ordine riaperto per completare i colli fino a '.$data_fine_validation.'</option>';
		}
		echo '</select>';
		echo '</form>';	
		
		echo '</div>';	
		echo '</div>';  // class="container"	
	} // end if(!empty($ordersToValidateResults))
	
	if(!empty($results)) {
		echo '<div class="container" style="padding-left: 0px; margin-left: 0px; width: 100%; padding-bottom:5px;">';
		echo '<div class="col-xs-12">';
		
		echo '<form role="select" class="select-orders navbar-form" accept-charset="utf-8" method="get">';
		echo '<select id="all_orders" name="all_orders" size="1" data-live-search="true" class="orders_select selectpicker dropup" data-width="auto">';
		echo '<option value="">Seleziona il produttore</option>';
		foreach($results as $result) {
		
			echo "\n";
			if($result['Order']['data_fine']!=Configure::read('DB.field.date.empty'))
				$data_fine = $this->Time->i18nFormat($result['Order']['data_fine'],"%A %e %B %Y");
			else
				$data_fine = "";
		
			echo '<option value="'.$result['id'].'">';

			if($result['Order']['type_draw']=='PROMOTION')
				echo __('FEOptionProdGasPromotion');	
			
			echo $result['SuppliersOrganization']['name'];
			echo ' - '.__('Delivery').' '.$result['Delivery']['name'];
			echo ' - chiude il '.$data_fine.'</option>';
		}
		echo '</select>';
		echo '</form>';
		
		echo '</div>';	
		echo '</div>';	 // class="container"
	} // if(!empty($results)
		
	echo '<div style="min-height: 50px;">';
	$deliveries = [];
	if(!empty($results))
		foreach($results as $result) { 
			if(!in_array($result['Delivery']['id'], $deliveries)) {
				array_push($deliveries, $result['Delivery']['id']);
				echo '<div class="articlesOrderResult" id="articlesOrderResult_'.$result['Delivery']['id'].'"></div>';	
			}		
		}
		
	if(!empty($ordersToValidateResults)) { // ordini da validare i colli (pezzi_confezione)
		foreach($ordersToValidateResults as $ordersToValidateResult) {
			echo '<div class="articlesOrderResult" id="articlesOrderResult_'.$ordersToValidateResult['Delivery']['id'].'"></div>';	
		}		
	}		
	echo '</div>';
	/*
	echo "<pre>";
	print_r($deliveries);
	echo "</pre>";
	*/
	
	echo $this->element('boxIntroHelpEcomm', array('id' => 0));
	?>
	<script type="text/javascript">
	$(document).ready(function() {
	
		$('#all_order_to_validate').change(function() {

		   	var key = $("#all_order_to_validate option:selected").val();
			keyArray = key.split('-');
			var deliveryId = keyArray[0];
			var orderId = keyArray[1];
		
		    if(orderId!="" && deliveryId!="") {

	        	 if($('#all_orders').length>0) {
		        	 $('#all_orders option:first-child').prop('selected', true);
					 $('#all_orders').selectpicker('refresh');
	        	 }
	        	 
				 $('.introHelp').css('display', 'none');
				 $('.articlesOrderResult').html('');
				 $('.articlesOrderResult').css('display', 'none');				 
				 $('#articlesOrderResult_'+deliveryId).css('display', 'block');
				 $('#articlesOrderResult_'+deliveryId).css('min-height','100');
				 $('#articlesOrderResult_'+deliveryId).css('background', 'url("/images/cake/ajax-loader.gif") no-repeat scroll center 0 transparent');
				 
				 url = '/?option=com_cake&controller=Deliveries&action=tabsAjaxEcommCartsValidation&delivery_id='+deliveryId+'&order_id='+orderId+'&a=&b=&format=notmpl';
				 
				 $.ajax({
					type: "GET",
					url: url,
					data: "",
					success: function(response){
						$('#articlesOrderResult_'+deliveryId).css('background', 'none repeat scroll 0 0 transparent');
						$('#articlesOrderResult_'+deliveryId).html(response);
					},
					error:function (XMLHttpRequest, textStatus, errorThrown) {
						$('#articlesOrderResult_'+deliveryId).css('background', 'none repeat scroll 0 0 transparent');
						$('#articlesOrderResult_'+deliveryId).html(textStatus);
					}
			 	 });
			}
			else {
				 $('#articlesOrderResult_'+deliveryId).css('display', 'none');
				 $('#articlesOrderResult_'+deliveryId).html('');
				 $('#articlesOrderResult_'+deliveryId).css('min-height','100');
				 $('.introHelp').css('display', 'block');	
			}
		});
	
		$('#all_orders').change(function() {
		   var key = $("#all_orders option:selected").val();
		   if(key!='') {
	        	 if($('#all_order_to_validate').length>0) {
		        	 $('#all_order_to_validate option:first-child').prop('selected', true);
					 $('#all_order_to_validate').selectpicker('refresh');
	        	 }
	        	 		   
			     getAllOrderArticlesOrders(key);
			}
			else {
				 $('#articlesOrderResult_'+deliveryId).css('display', 'none');
				 $('#articlesOrderResult_'+deliveryId).html('');
				 $('#articlesOrderResult_'+deliveryId).css('min-height','100');
				 $('.introHelp').css('display', 'block');		
			}
		});    
	});
	
	function getAllOrderArticlesOrders(key) {
	
		keyArray = key.split('-');
		var deliveryId = keyArray[0];
		var orderId = keyArray[1];
		/* console.log("getAllOrderArticlesOrders() deliveryId "+deliveryId);*/
		
	    if(orderId!="" && deliveryId!="") {
	    	$('.introHelp').css('display', 'none');
			 $('.articlesOrderResult').html('');
			 $('.articlesOrderResult').css('display', 'none');
			 $('#articlesOrderResult_'+deliveryId).css('display', 'block');
			 $('#articlesOrderResult_'+deliveryId).css('min-height','100');
			 $('#articlesOrderResult_'+deliveryId).css('background', 'url("/images/cake/ajax-loader.gif") no-repeat scroll center 0 transparent');
			 
			 url = "/?option=com_cake&controller=Deliveries&action=tabsAjaxEcommArticlesOrder&delivery_id="+deliveryId+"&order_id="+orderId+"&a=&b=&c=&format=notmpl";
			 if(debug) console.log('ecomm_deliveries '+url);
			 
			 $.ajax({
				type: "GET",
				url: url,
				data: "",
				success: function(response){
					$('#articlesOrderResult_'+deliveryId).css('background', 'none repeat scroll 0 0 transparent');
					$('#articlesOrderResult_'+deliveryId).html(response);
				},
				error:function (XMLHttpRequest, textStatus, errorThrown) {
					$('#articlesOrderResult_'+deliveryId).css('background', 'none repeat scroll 0 0 transparent');
					$('#articlesOrderResult_'+deliveryId).html(textStatus);
				}
		 	 });
		 }
		 else {
			 $('#articlesOrderResult_'+deliveryId).css('display', 'none');
			 $('#articlesOrderResult_'+deliveryId).html('');
		 }
	}
	</script>
	
	<script type="text/javascript">
	$(document).ready(function() {
		$('.selectpicker').selectpicker({
			style: 'selectpicker selectpicker-my'
		});
	
		$('a').tooltip();
	});
	</script>
<?php
} // if(empty($results))
?>
</div>