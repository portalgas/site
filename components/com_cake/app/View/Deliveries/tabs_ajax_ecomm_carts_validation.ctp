<?php
if(empty($results))  {

	echo '<div role="alert" class="alert alert-success">';
	echo '<a href="#" class="close" data-dismiss="alert">&times;</a>';
	echo __('OrderToValidateOkFrontEnd');
	echo '</div>';
} 
else {
	echo '<input type="hidden" name="Order_type_draw" id="order_type_draw_'.$order['Order']['id'].'" value="VALIDATION_SIMPLE" />';
	
	echo $this->Tabs->setTableHeaderEcommCartsValidationFrontEnd($order['Order']['delivery_id']);
	
	$i=0;
	foreach($results as $numArticlesOrder => $result) {
		echo $this->RowEcomm->drawFrontEndCartsValidationSimple($i, $order, $result);
		$i++;
	}
	
	echo '</table>';
}  // if(empty($results)) 
?>
<script type="text/javascript">
$(document).ready(function() {

	$(".rowEcomm").each(function () {
		activeEcommRows(this);    /* active + / - , mouseenter mouseleave */
		activeSubmitEcomm(this);		
	});	
	
	$('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	$('.actionTrView').each(function () {
		actionTrView(this);
	});
	
	$('.actionNotaView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	$('.actionNotaView').each(function () {
		actionNotaView(this); 
	});
});	
</script>