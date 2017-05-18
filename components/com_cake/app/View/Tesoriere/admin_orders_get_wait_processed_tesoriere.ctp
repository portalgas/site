<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
if(!isset($delivery_id)) $delivery_id = 0;
$this->Html->addCrumb(__('Tesoriere'),array('controller' => 'Tesoriere', 'action' => 'home', $delivery_id));
$this->Html->addCrumb(__('OrdersWaitProcessedTesoriere'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('.submit').css('display','none');
		
	jQuery('#delivery_id').change(function() {
		caricaOrdini();
	});

	jQuery('.legenda').css('display', 'none');
	var delivery_id = jQuery('#delivery_id').val();
	if(delivery_id!="" && delivery_id!=undefined) caricaOrdini();
	
	jQuery('#formGas').submit(function() {
		var delivery_id = jQuery('#delivery_id').val();
		var order_id_selected = '';
		for(i = 0; i < jQuery("input[name='order_id_selected']:checked").length; i++) {
			order_id_selected += jQuery("input[name='order_id_selected']:checked").eq(i).val()+',';
		}

		if(delivery_id=='') {
			alert("<?php echo __('jsAlertDeliveryRequired');?>");
			return false;
		}
		if(order_id_selected=='') {
			alert("<?php echo __('jsAlertOrderToStatoToRunRequired');?>");
			return false;
		}	    
		order_id_selected = order_id_selected.substring(0,order_id_selected.length-1);
		jQuery('#order_id_selected').val(order_id_selected);
		
		return true;
	});
});
	
function caricaOrdini() {
	var delivery_id = jQuery('#delivery_id').val();
	if(delivery_id=="" || delivery_id==undefined) {
		jQuery('#orders-result').html('');
		jQuery('#orders-result').css('display', 'none');
		jQuery('.legenda').css('display', 'none');	
		return;
	}

	jQuery('#orders-result').html('');
	jQuery('#orders-result').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');
	jQuery('#orders-result').css('display', 'block');	
	jQuery('.legenda').css('display', 'block');
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

<h2 class="ico-wait">
	<?php echo __('OrdersWaitProcessedTesoriere').' (dallo stato "In attesa di essere elaborato dal tesoriere" a "in carico al tesoriere")';?>
</h2>


<div class="tesoriere">
<?php echo $this->Form->create(array('id'=>'formGas'));?>
	<fieldset>
	<?php
	$options = array('id'=>'delivery_id');
	if(!empty($delivery_id) && $delivery_id>0)
		$options += array('default' => $delivery_id);
	else
		$options += array('empty' => Configure::read('option.empty'));
	 
	echo $this->Form->input('delivery_id',$options);
		
	
	echo $this->Form->hidden('order_id_selected', array('id' => 'order_id_selected', 'value' => ''));
	?>	
	
	<div id="orders-result" style="display:block;min-height:50px;"></div>

	<?php
		echo $this->Form->end('Prendi in carico gli ordini "in attesa di essere elaborati dal tesoriere"');
	?>
	</fieldset>
</div>

<?php
/*
 * legenda profilata
*/
echo $this->App->drawLegenda($user, $orderStatesToLegenda);
?>