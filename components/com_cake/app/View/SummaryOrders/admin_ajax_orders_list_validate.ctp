<?php
if(!empty($orders)) {
	$options = array('id'=>'order_id',
					 'onChange' => 'choiceOrder(this);',
					 'empty' => Configure::read('option.empty'));
	
	echo $this->Form->input('order_id',$options);			
}
else 
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "Non ci sono consegne con ordini con dati aggregati"));		
?>	
<script type="text/javascript">
jQuery(document).ready(function() {
	if(order_id>0)	choiceOrder();
});
</script>