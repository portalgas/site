<?php
$options = array('tr.no_display' => true);

echo $this->RowBookmarks->drawFrontEndSimple($numResult, $results, $options);

echo $resultsJS;
?>