
<?php
$options = array('tr.no_display' => true);

echo $this->ProdRowEcomm->drawFrontEndComplete($numProdDeliveriesArticle, $prodDeliveryResults, $results, $options);

echo $resultsJS;
?>