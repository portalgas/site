<?php
$options = array('tr.no_display' => true);

echo $this->ProdRowEcomm->drawBackOfficeReportUsers($numProdDeliveriesArticle, $prodDeliveryResults, $results, $permissions, $options);

echo $resultsJS;
?>