<?php
$options = array('tr.no_display' => true);

echo $this->RowEcomm->drawBackOfficeReportArticlesDetails($numArticlesOrder, $results, $permissions, $options);

echo $resultsJS;
?>