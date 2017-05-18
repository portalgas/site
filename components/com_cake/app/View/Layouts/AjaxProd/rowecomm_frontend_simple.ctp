<?php
$options = array('tr.no_display' => true);

echo $this->ProdRowEcomm->drawFrontEndSimple($numProdDeliveriesArticle, $prodDeliveryResults, $results, $options);

echo $resultsJS;
?>