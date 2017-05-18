<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
if(!isset($delivery_id)) $delivery_id = 0;
$this->Html->addCrumb(__('Tesoriere'),array('controller' => 'Tesoriere', 'action' => 'home', $delivery_id));
$this->Html->addCrumb(__('OrdersProcessedTesoriere'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('.submit').css('display','none');
	
	var delivery_id = jQuery('#delivery_id').val();
	if(delivery_id!="" && delivery_id!=undefined) caricaOrdini();
	
	jQuery('#delivery_id').change(function() {
		caricaOrdini();
	});

	jQuery('#sumbitElabora').click(function() {

		if(!ctrlCampi()) return false;

		var delivery_id = jQuery('#delivery_id').val();
		var order_id_selected = '';
		for(i = 0; i < jQuery("input[name='order_id_selected']:checked").length; i++) {
			order_id_selected += jQuery("input[name='order_id_selected']:checked").eq(i).val()+',';
		}
		order_id_selected = order_id_selected.substring(0,order_id_selected.length-1);

		jQuery('#doc-preview').css('display', 'block');
		jQuery('#doc-preview').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');

		jQuery.ajax({
			type: "get", 
			url: "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_summary_orders&delivery_id="+delivery_id+"&order_id_selected="+order_id_selected+"&format=notmpl",
			data: "",  
			success: function(response) {
				jQuery('#doc-preview').css('background', 'none repeat scroll 0 0 transparent');
				jQuery("#doc-preview").html(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				jQuery('#doc-preview').css('background', 'none repeat scroll 0 0 transparent');
				jQuery('#doc-preview').html(textStatus);
			}
		});
		
		return false;
	});

	jQuery('#sumbitToPROCESSED_REFERENTE_POST_DELIVERY').click(function() {

		if(!ctrlCampi()) return false;

		var order_id_selected = '';
		for(i = 0; i < jQuery("input[name='order_id_selected']:checked").length; i++) {
			order_id_selected += jQuery("input[name='order_id_selected']:checked").eq(i).val()+',';
		}
		order_id_selected = order_id_selected.substring(0,order_id_selected.length-1);
		jQuery('#order_id_selected').val(order_id_selected);

		jQuery('#action_submit').val('OrdersToPROCESSED_REFERENTE_POST_DELIVERY');

		return true;
	});		


	jQuery('#sumbitToTO_PAYMENT').click(function() {

		if(!ctrlCampi()) return false;

		var order_id_selected = '';
		for(i = 0; i < jQuery("input[name='order_id_selected']:checked").length; i++) {
			order_id_selected += jQuery("input[name='order_id_selected']:checked").eq(i).val()+',';
		}
		order_id_selected = order_id_selected.substring(0,order_id_selected.length-1);
		jQuery('#order_id_selected').val(order_id_selected);
		
		jQuery('#action_submit').val('OrdersToTO_PAYMENT');

		return true;
	});		  
});

function ctrlCampi() {
	var delivery_id = jQuery('#delivery_id').val();
	var order_id_selected = '';
	for(i = 0; i < jQuery("input[name='order_id_selected']:checked").length; i++) {
		order_id_selected += jQuery("input[name='order_id_selected']:checked").eq(i).val()+',';
	}

	if(delivery_id=='') {
		order_id_selected = order_id_selected.substring(0,order_id_selected.length-1);
		alert("<?php echo __('jsAlertDeliveryRequired');?>");
		return false;
	}
	if(order_id_selected=='') {
		alert("<?php echo __('jsAlertOrderAtLeastRequired');?>");
		return false;
	}
	return true;
}
function caricaOrdini() {
	var delivery_id = jQuery('#delivery_id').val();

	if(delivery_id=="") {
		jQuery('#orders-result').html('');
		jQuery('#orders-result').css('display', 'none');
		jQuery('#doc-preview').css('display', 'none');	
		return;
	}

	jQuery('#orders-result').html('');
	jQuery('#orders-result').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');
	jQuery('#orders-result').css('display', 'block');
	jQuery('#doc-preview').css('display', 'none');
	jQuery('#doc-preview').html('');	
			
	jQuery.ajax({
		type: "get",
		url: "/administrator/index.php?option=com_cake&controller=Tesoriere&action=orders_index&delivery_id="+delivery_id+"&order_state_code_checked=<?php echo $order_state_code_checked;?>&format=notmpl",
		data: "", 
		success: function(response) {
			jQuery('#orders-result').css('background', 'none repeat scroll 0 0 transparent');
			jQuery('#orders-result').html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			jQuery('#orders-result').css('background', 'none repeat scroll 0 0 transparent');
			jQuery('#orders-result').html(textStatus);
		}
	});
}
</script>

<h2 class="ico-config">
	<?php echo __('OrdersProcessedTesoriere');?>
</h2>


<div class="docs">
<?php echo $this->Form->create('Tesoriere',array('id' => 'formGas'));?>
	<fieldset>
	<?php
	$options = array('id'=>'delivery_id');
	if(!empty($delivery_id) && $delivery_id>0)
		$options += array('default' => $delivery_id);
	else
		$options += array('empty' => Configure::read('option.empty'));
	
	echo $this->Form->input('delivery_id',$options);
	?>	
	
	<div id="orders-result" style="display:none;min-height:50px;"></div>
	
	<div class="submit">
		<label for="order_id">Azioni</label>
		<div style="margin-left: 210px;">
	
			<table cellpadding="0" cellspacing="0">
				<tr>
					
						<?php 
						/*
						 echo '<td>';
						 echo '<div class="submit"><input id="sumbitElabora" type="submit" value="Elabora gli ordini" /></div>';
						 echo '</td>';
						*/
						echo '<td>';
						echo '<div class="submit"><input id="sumbitToPROCESSED_REFERENTE_POST_DELIVERY" type="submit" class="buttonBlu" value="Porta gli ordini allo stato \'in carico al referente\'" /></div>';
						echo '</td>';
						echo '<td>';
						echo '<div class="submit"><input id="sumbitToTO_PAYMENT" type="submit" class="buttonBlu" value="Porta gli ordini allo stato \'in attesa del pagamento\'" /></div>';
						?>
					</td>
				</tr>
			</table>
		</div>
	</div>
	
	<?php	
		echo $this->Form->hidden('order_id_selected', array('id' => 'order_id_selected', 'value' => ''));
		echo $this->Form->hidden('action_submit', array('id' => 'action_submit', 'value' => ''));
		
		echo $this->Form->end();
	?>
	
	<div id="doc-preview" style="display:none;"></div>
	
	</fieldset>
</div>

<?php
/*
 * legenda profilata
*/
echo $this->App->drawLegenda($user, $orderStatesToLegenda);
?>