<?php
$model = 'SummaryOrderCostLess';

$tmp = '';

$tmp .= $this->ExportDocs->delivery($user, $results['Delivery'][0]);

$tmp .= '<div class="clearfix"></div>';
	
$tmp .= $this->SummaryOrderPlus->draw($user, $model, $results);

echo $tmp;
?>