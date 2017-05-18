<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('List MonitoringOrders'), array('controller' => 'MonitoringOrders', 'action' => 'home'));
$this->Html->addCrumb(__('Gest MonitoringOrders'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="monitoring-orders form">';
?>

<h2 class="ico-monitoring-orders">
	<?php echo __('Monitoring Orders');?>
</h2>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('.submit').css('display','none');
		
	jQuery('#delivery_id').change(function() {
		caricaOrdini();
	});
	
	var delivery_id = jQuery('#delivery_id').val();
	if(delivery_id!="" && delivery_id!=undefined) caricaOrdini();
});
	
function caricaOrdini() {
	var delivery_id = jQuery('#delivery_id').val();
	if(delivery_id=="" || delivery_id==undefined) {
		jQuery('#orders-result').html('');
		jQuery('#orders-result').css('display', 'none');
		return;
	}

	jQuery('#orders-result').html('');
	jQuery('#orders-result').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');
	jQuery('#orders-result').css('display', 'block');	
	jQuery.ajax({
		type: "get",
		url: "/administrator/index.php?option=com_cake&controller=MonitoringOrders&action=orders_index&delivery_id="+delivery_id+"&format=notmpl",
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

<div class="legenda  legenda-ico-info">
Scegli quali ordini monitorare
</div>

<?php echo $this->Form->create('Order', array('id'=>'formGas'));?>
	<fieldset>
	<?php
	$options = array('id'=>'delivery_id');
	if(!empty($delivery_id) && $delivery_id>0)
		$options += array('default' => $delivery_id);
	else
		$options += array('empty' => Configure::read('option.empty'));
	 
	echo $this->Form->input('delivery_id',$options);	
	?>	
	
	<div id="orders-result" style="display:block;min-height:50px;"></div>

	<?php
		echo $this->Form->end(__('Submit'));
	?>
	</fieldset>


<?php
echo '</div>';

echo '<div class="actions">';
echo '<h3>'.__('Actions').'</h3>';
echo '<ul>';
echo '<li>'.$this->Html->link(__('List MonitoringOrders'), array('controller' => 'MonitoringOrders', 'action' => 'home'),array('class'=>'action actionReload')).'</li>';
echo '</ul>';
echo '</div>';
?>