<?php 
echo $this->Html->script('jquery/jquery.cookie');

echo $this->Form->create('PopUp',array('id'=>'formGas'));
echo $this->Form->submit(__('Chiudi e non mostrare più il messaggio'), ['id' => $order_id, 'div'=> 'submitMultiple left', 'class' => 'btn btn-primary']);
echo $this->Form->end();
?>
<script type="text/javascript">
$(document).ready(function() {
	$('#<?php echo $order_id;?>').click(function() {
		$.cookie("<?php echo $cookie_name;?>", "<?php echo $order_id;?>", { expires: <?php echo Configure::read('Cookies.expire');?>, path: '<?php echo Configure::read('Cookies.path');?>/' });

		$('#tmpModal').modal('toggle');
		
		return false;
	});
});
</script>