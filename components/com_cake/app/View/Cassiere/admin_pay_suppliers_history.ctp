<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
if(!isset($delivery_id)) $delivery_id = 0;
$this->Html->addCrumb(__('Cassiere'),array('controller' => 'Cassiere', 'action' => 'home', $delivery_id));
$this->Html->addCrumb(__('Pay Suppliers History'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<h2 class="ico-users">
	<?php echo __('Pay Suppliers History');?>
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
		url: "/administrator/index.php?option=com_cake&controller=Cassiere&action=orders_to_pay_index_history&delivery_id="+delivery_id+"&format=notmpl",
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

<div class="tesoriere">
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
</div>