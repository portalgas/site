<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('ValidateDates'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<h2 class="ico-pay">';
echo __('ValidateDates');
echo '</h2>';
?>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('#delivery_id').change(function() {
		caricaOrdini();
	});
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
		url: "/administrator/index.php?option=com_cake&controller=ValidateDates&action=ajax_index_deliveries&delivery_id="+delivery_id+"&format=notmpl",
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