<?php
echo '<div class="old-menu">';

$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
if(!isset($delivery_id)) $delivery_id = 0;
$this->Html->addCrumb(__('Tesoriere'),array('controller' => 'Tesoriere', 'action' => 'home', $delivery_id));
$this->Html->addCrumb(__('Pay Suppliers'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<h2 class="ico-users">
	<?php echo __('Pay Suppliers');?>
</h2>

<script type="text/javascript">
$(document).ready(function() {

	$('.submit').css('display','none');
		
	$('#delivery_id').change(function() {
		caricaOrdini();
	});
	
	var delivery_id = $('#delivery_id').val();
	if(delivery_id!="" && delivery_id!=undefined) caricaOrdini();
});
	
function caricaOrdini() {
	var delivery_id = $('#delivery_id').val();
	if(delivery_id=="" || delivery_id==undefined) {
		$('#orders-result').html('');
		$('#orders-result').css('display', 'none');
		return;
	}

	$('#orders-result').html('');
	$('#orders-result').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');
	$('#orders-result').css('display', 'block');	
	$.ajax({
		type: "get",
		url: "/administrator/index.php?option=com_cake&controller=Tesoriere&action=orders_to_pay_index&delivery_id="+delivery_id+"&format=notmpl",
		data: "", 
		success: function(response) {
			$('#orders-result').css('background', 'none repeat scroll 0 0 transparent');
			$('#orders-result').html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			$('#orders-result').css('background', 'none repeat scroll 0 0 transparent');
			$('#orders-result').html(textStatus);
		}
	});	
}
</script>

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

<?php 
echo $this->element('menuTesoriereLaterale');
?>