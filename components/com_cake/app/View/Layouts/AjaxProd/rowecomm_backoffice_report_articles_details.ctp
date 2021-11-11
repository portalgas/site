<?php
$options = array('tr.no_display' => true);

echo $this->ProdRowEcomm->drawBackOfficeReportArticlesDetails($numProdDeliveriesArticle, $prodDeliveryResults, $results, $permissions, $options);

echo $resultsJS;
?>