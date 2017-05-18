<?php 
if(empty($results))
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "L'utente non ha effettuato acquisti"));
else {
	echo '<div id="tabsDelivery">';
	
	echo $this->ProdTabs->drawTableHeaderBackOfficeReportUsers($prodDeliveryResults, $permissions);	
	
	foreach($results as $numProdDeliveriesArticle => $result)
		echo $this->ProdRowEcomm->drawBackOfficeReportUsers($numProdDeliveriesArticle, $prodDeliveryResults, $result, $permissions);

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