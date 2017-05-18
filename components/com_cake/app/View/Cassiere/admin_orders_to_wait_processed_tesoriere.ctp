<?php$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));if(!isset($delivery_id)) $delivery_id = 0;$this->Html->addCrumb(__('Tesoriere'),array('controller' => 'Tesoriere', 'action' => 'home', $delivery_id));$this->Html->addCrumb(__('ListOrdersToWaitProcessedTesoriere'));echo $this->Html->getCrumbList(array('class'=>'crumbs'));?><script type="text/javascript">jQuery(document).ready(function() {	jQuery('.submit').css('display','none');			jQuery('#delivery_id').change(function() {		caricaOrdini();	});	var delivery_id = jQuery('#delivery_id').val();	if(delivery_id!="" && delivery_id!=undefined) caricaOrdini();		jQuery('#formGas').submit(function() {		var delivery_id = jQuery('#delivery_id').val();		var order_id_selected = '';		for(i = 0; i < jQuery("input[name='order_id_selected']:checked").length; i++) {			order_id_selected += jQuery("input[name='order_id_selected']:checked").eq(i).val()+',';		}		if(delivery_id=='') {			alert("<?php echo __('jsAlertDeliveryRequired');?>");			return false;		}		if(order_id_selected=='') {			alert("<?php echo __('jsAlertOrderToStatoToRunRequired');?>");			return false;		}	    		order_id_selected = order_id_selected.substring(0,order_id_selected.length-1);		jQuery('#order_id_selected').val(order_id_selected);				return true;	});});	function caricaOrdini() {	var delivery_id = jQuery('#delivery_id').val();	if(delivery_id=="" || delivery_id==undefined) {		jQuery('#orders-result').html('');		jQuery('#orders-result').css('display', 'none');		return;	}	jQuery('#orders-result').html('');	jQuery('#orders-result').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');	jQuery('#orders-result').css('display', 'block');		jQuery.ajax({		type: "get",		url: "/administrator/index.php?option=com_cake&controller=Cassiere&action=ajax_orders_to_wait_processed_tesoriere&delivery_id="+delivery_id+"&format=notmpl",		data: "", 		success: function(response) {			jQuery('#orders-result').css('background', 'none repeat scroll 0 0 transparent');			jQuery('#orders-result').html(response);		},		error:function (XMLHttpRequest, textStatus, errorThrown) {			jQuery('#orders-result').css('background', 'none repeat scroll 0 0 transparent');			jQuery('#orders-result').html(textStatus);		}	});	}</script><div class="cassiere">	<h2 class="ico-users">		<?php echo __('Cassiere');?>			</h2></div><div class="cassiere"><?php echo $this->Form->create('Cassiere', array('id'=>'formGas'));?>	<fieldset>	<?php		if(!empty($deliveries)) {			$options = array('id'=>'delivery_id');			if(!empty($delivery_id) && $delivery_id>0)				$options += array('default' => $delivery_id);			else				$options += array('empty' => Configure::read('option.empty'));			 			echo $this->Form->input('delivery_id',$options);					}		else 			echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "Non ci sono consegne con ordini in carico al cassiere"));		?>			<div id="orders-result" style="display:block;min-height:50px;"></div>	<?php		echo $this->Form->end();	?>	</fieldset></div>