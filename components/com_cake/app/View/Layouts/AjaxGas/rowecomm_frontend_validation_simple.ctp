<?php
$options = array('tr.no_display' => true);

echo $this->RowEcomm->drawFrontEndCartsValidationSimple($numArticlesOrder, $order, $results, $options);

echo $resultsJS;
?>