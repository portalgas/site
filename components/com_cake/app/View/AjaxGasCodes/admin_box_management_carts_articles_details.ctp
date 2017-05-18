<?php 
if($results['Delivery'][0]['totArticlesOrder']==0)
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non sono stati ancora effettuati acquisti"));
else {
	echo '<div id="tabsDelivery">';
	$order = ($results['Delivery'][0]['Order'][0]);
	echo $this->Tabs->drawTableHeaderBackOfficeReportArticlesDetails($order, $permissions);	
	
	if($results['Delivery'][0]['totOrders']>0) {
		foreach($results['Delivery'] as $numDelivery => $delivery) 
			foreach($delivery['Order'] as $numOrder => $order)
				foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder)	
					echo $this->RowEcomm->drawBackOfficeReportArticlesDetails($numArticlesOrder, $this->RowEcomm->prepareResult($numArticlesOrder, $order), $permissions);
	}
				
	echo '</tbody>';
	echo '</table>';
	echo '</div>';		
}
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(".rowEcomm").each(function () {
		activeEcommRows(this);    /* active + / - , mouseenter mouseleave */
		activeSubmitEcomm(this);
		activeImportoForzato(this);
		activeNotaEcomm(this);		
	});	
	
	jQuery('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	jQuery('.actionTrView').each(function () {
		actionTrView(this);
	});
	
	jQuery('.actionNotaView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	jQuery('.actionNotaView').each(function () {
		actionNotaView(this); 
	});
});	
</script>