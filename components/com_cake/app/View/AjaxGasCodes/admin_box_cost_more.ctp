<?php
$model = 'SummaryOrderCostMore';

$tmp = '';

$tmp .= $this->ExportDocs->delivery($results['Delivery'][0]);

$tmp .= '<div class="clearfix"></div>';
	
$tmp .= $this->SummaryOrderPlus->draw($user, $model, $results);

echo $tmp;
?>