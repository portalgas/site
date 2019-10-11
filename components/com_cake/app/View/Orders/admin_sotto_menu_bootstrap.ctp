<?php
$options = [];
// $options['openCloseClassCss'] = 'open';
$options['openCloseClassCss'] = 'open';
$options['linkListOrders'] = true;

echo $this->MenuOrders->drawContent($results, $desOrdersResults, $orderActions, $orderStates, $options);
?>