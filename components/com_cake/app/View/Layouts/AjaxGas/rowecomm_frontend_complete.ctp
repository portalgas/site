<?php
$options = array('tr.no_display' => true);

echo $this->RowEcomm->drawFrontEndComplete($numArticlesOrder, $order, $results, $options);

echo $resultsJS;
?>