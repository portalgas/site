<?php
$options =  array('onChange' => 'choiceOrderHistory(this);');
if(!empty($order_id) && $order_id>0)
	$options += array('default' => $order_id);
else
	$options += array('empty' => Configure::read('option.empty'));

echo $this->Form->input('order_id',$options);
?>
<script type="text/javascript">
$(document).ready(function() {
	if(order_id>0)	choiceOrderHistory();
});
</script>