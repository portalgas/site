<?php
$options = array('tr.no_display' => true);

echo $this->RowEcomm->drawBackOfficeReportArticlesDetails($user, $numArticlesOrder, $results, $permissions, $options);

echo $resultsJS;
?>