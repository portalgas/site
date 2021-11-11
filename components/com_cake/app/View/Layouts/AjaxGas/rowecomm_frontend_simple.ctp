<?php
$options = array('tr.no_display' => true);

echo $this->RowEcomm->drawFrontEndSimple($numArticlesOrder, $order, $results, $options);

echo $resultsJS;
?>