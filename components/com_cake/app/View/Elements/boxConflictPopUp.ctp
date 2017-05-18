<?php 
echo $this->Html->script('jquery/jquery.cookie');

echo $this->Form->create('PopUp',array('id'=>'formGas'));
echo $this->Form->submit(__('Chiudi e non mostrare più il messaggio'),array('id' => $order_id, 'div'=> 'submitMultiple left'));
echo $this->Form->submit(__('Close'),array('id' => 'close', 'div'=> 'submitMultiple','class' => 'buttonBlu right'));
echo $this->Form->end();
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#<?php echo $order_id;?>').click(function() {
		jQuery.cookie("<?php echo $cookie_name;?>", "<?php echo $order_id;?>", { expires: 100, path: '<?php echo Configure::read('App.server');?>/' });

  		var close_popup = function() {
  			jQuery('.popupWrap').remove();
  			jQuery('.popup').remove();
  		}	
		jQuery('.popup').css('display','hide');		
		jQuery('.popup').animate( {opacity:0}, 200, close_popup );
		
		return false;
	});

	jQuery('#close').click(function() {
  		var close_popup = function() {
  			jQuery('.popupWrap').remove();
  			jQuery('.popup').remove();
  		}	
		jQuery('.popupWrap').css('display','hide');		
		jQuery('.popupWrap').animate( {opacity:0}, 200, close_popup );
		
		return false;
	});	
});
</script>