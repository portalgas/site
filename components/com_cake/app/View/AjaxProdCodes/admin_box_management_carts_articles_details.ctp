<?php 
if(empty($results))
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non sono stati ancora effettuati acquisti"));
else {
	echo '<div id="tabsDelivery">';

	echo $this->ProdTabs->drawTableHeaderBackOfficeReportArticlesDetails($prodDeliveryResults, $permissions);	
	
		foreach($results as $numProdDeliveriesArticle => $result) 
			echo $this->ProdRowEcomm->drawBackOfficeReportArticlesDetails($numProdDeliveriesArticle, $prodDeliveryResults, $result, $permissions);
				
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