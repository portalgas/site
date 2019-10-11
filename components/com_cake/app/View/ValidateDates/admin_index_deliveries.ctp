<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('ValidateDates'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<h2 class="ico-pay">';
echo __('ValidateDates').' - '.__('Payment').' '.$organizationPayToDelivery;
echo '</h2>';
?>

<script type="text/javascript">
$(document).ready(function() {

	$('#delivery_id').change(function() {
		caricaOrdini();
	});
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
		url: "/administrator/index.php?option=com_cake&controller=ValidateDates&action=ajax_index_deliveries&delivery_id="+delivery_id+"&format=notmpl",
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



<div class="orders">
	<?php echo $this->Form->create(array('id'=>'formGas'));?>
	<fieldset>
	<?php
	$options = array('id'=>'delivery_id', 
					 'empty' => Configure::read('option.empty'));
	 
	echo $this->Form->input('delivery_id',$options);
	?>	
	
	<div id="orders-result" style="display:block;min-height:50px;"></div>

	</fieldset>
	<?php
		echo $this->Form->end();
	?>
	
</div>