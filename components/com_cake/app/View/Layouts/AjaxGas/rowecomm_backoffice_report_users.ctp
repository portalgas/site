<?php
$options = array('tr.no_display' => true);

echo $this->RowEcomm->drawBackOfficeReportUsers($user, $numArticlesOrder, $results, $permissions, $options);

echo $resultsJS;
?>