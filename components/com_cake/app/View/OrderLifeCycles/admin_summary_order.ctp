<?php
$this->App->d($results);

$summaryOrderNotPaid = $results['SummaryOrder']['SummaryOrderNotPaid'];
$summaryOrderPaid = $results['SummaryOrder']['SummaryOrderPaid'];
$requestPayment = $results['RequestPayment'];

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$results['Order']['id']));
$this->Html->addCrumb(__('OrderLifeCyclesSummaryOrder'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

echo '<h2 class="ico-pay">';
echo __('OrderLifeCyclesSummaryOrder');
echo '</h2>';

echo $this->element('boxOrder',array('results' => $results));

/*
 * msg
 */		
if(!empty($requestPayment)) {
	
	/*
	 * ordine associato a richiesta di pagamento
	 */
	echo '<h1>';
	echo __('request_payment_num').' '.$requestPayment['RequestPayment']['num'].' ('.$this->Time->i18nFormat($requestPayment['RequestPayment']['created'],"%A %e %B %Y").')';
	echo '<span style="float:right;">';
	echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPayment['RequestPayment']['stato_elaborazione']).' <span style="padding-left: 20px;" title="'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPayment['RequestPayment']['stato_elaborazione']).'" class="stato_'.strtolower($requestPayment['RequestPayment']['stato_elaborazione']).'"></span>';
	echo '</span>';
	echo '</h1>';	

	$msg = __('OrderLifeCyclesSummaryOrderRequestPayment');
}
else
if(empty($summaryOrderNotPaid)) {
	/*
	 * ordine saldato da tutti i gasisti
	 */
	$msg = __('OrderLifeCyclesSummaryOrderSummaryOrderPaid');
}
else {
	/*
	 * ordine NON saldato da tutti i gasisti
	 */
	$msg = __('OrderLifeCyclesSummaryOrderSummaryOrderNotPaid');
}
echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => $msg));

 	
echo $this->Form->create('SummaryOrder',array('id' => 'formGas'));
echo '<fieldset style="margin: 0;padding: 0;">';



/*
 * non pagato
 */	
if(isset($summaryOrderNotPaid) && !empty($summaryOrderNotPaid))
	$tot = count($summaryOrderNotPaid);
else
	$tot = 0;

echo '	<div class="panel-group">';
echo '<div class="panel panel-primary">';
echo '<div class="panel-heading">';
echo '<h4 class="panel-title">';
echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapse1"><i class="fa fa-lg fa-minus" aria-hidden="true"></i> '.__('OrderLifeCyclesSummaryOrderNotPaid').' ('.$tot.')</a>';
echo '</h4>';
echo '</div>';
echo '<div id="collapse1" class="panel-collapse collapse in">';
echo '<div class="panel-body">';

$tot_importo = 0;	
$tot_importo_pagato = 0;	
if(!empty($summaryOrderNotPaid)) {

	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '<th>';
	echo __('N').'</th>';
	echo '<th colspan="2">'.__('Name').'</th>';
	echo '<th style="text-align:center;">'.__('Importo_dovuto').'</th>';
	echo '<th style="text-align:center;">'.__('Importo_pagato').'</th>';
	echo '</th>';
	echo '<th style="width:1px;"></th>';
	echo '</tr>';
		
	foreach($summaryOrderNotPaid as $numResult => $result) {
	
		echo '<tr>';
		echo '<td>'.((int)$numResult+1).'</td>';
		echo '<td>';
		echo $result['User']['name'];
		echo '</td>';
		echo '<td>';
		echo $result['User']['email'];
		echo '</td>';
		echo '<td style="text-align:center;">'.$result['SummaryOrder']['importo_e'].'</td>';
		echo '<td style="text-align:center;">'.$result['SummaryOrder']['importo_pagato_e'].'</td>';
		echo '<td style="background-color:red;"></td>';
	
		$tot_importo += $result['SummaryOrder']['importo'];
		$tot_importo_pagato += $result['SummaryOrder']['importo_pagato'];
	}
	
	/* 
	 * totali
	 */
    $tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
    $tot_importo_pagato = number_format($tot_importo_pagato,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	 
	echo '<tr>';
	echo '<td colspan="3" style="font-weith:bold;text-align:right">Totali</td>';
	echo '<td style="text-align:center;">'.$tot_importo.'&nbsp;&euro;</td>';
	echo '<td style="text-align:center;">'.$tot_importo_pagato.'&nbsp;&euro;</td>';
	echo '<td></td>';
	echo '</tr>';
		
	echo '</table></div>';
}
else
	echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFound', 'msg' => __('OrderLifeCyclesSummaryOrderNotPaidNotFound')));



$tot_summaryOrderPaid = 0;
if(!empty($summaryOrderPaid)) 
	$tot_summaryOrderPaid = count($summaryOrderPaid);

echo '</div>';
echo '</div>';
echo '</div>';
echo '<div class="panel panel-primary">';
echo '<div class="panel-heading">';
echo '<h4 class="panel-title">';
echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapse2"><i class="fa fa-lg fa-plus" aria-hidden="true"></i> '.__('OrderLifeCyclesSummaryOrderPaid').' ('.$tot_summaryOrderPaid.')</a>';
echo '</h4>';
echo '</div>';
echo '<div id="collapse2" class="panel-collapse collapse">';
echo '<div class="panel-body">';

		  
/*
 * pagato
 */	
echo '<h1></h1>'; 
$tot_importo = 0;	
$tot_importo_pagato = 0;	
if(!empty($summaryOrderPaid)) {
	
	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '<th>';
	echo __('N').'</th>';
	echo '<th colspan="2">'.__('Name').'</th>';
	echo '<th style="text-align:center;">'.__('Importo_dovuto').'</th>';
	echo '<th style="text-align:center;">'.__('Importo_pagato').'</th>';
	echo '<th style="width:1px;"></th>';
	echo '</th>';

	foreach($summaryOrderPaid as $numResult => $result) {
			
		echo '<tr>';
		
		echo '<td>'.((int)$numResult+1).'</td>';
		echo '<td>';
		echo $result['User']['name'];
		echo '</td>';
		echo '<td>';
		echo $result['User']['email'];
		echo '</td>';
		echo '<td style="text-align:center;">'.$result['SummaryOrder']['importo_e'].'</td>';
		echo '<td style="text-align:center;">'.$result['SummaryOrder']['importo_pagato_e'].'</td>';
		echo '<td style="background-color:green;"></td>';
	
		$tot_importo += $result['SummaryOrder']['importo'];
		$tot_importo_pagato += $result['SummaryOrder']['importo_pagato'];
	}
	
	/* 
	 * totali
	 */
    $tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
    $tot_importo_pagato = number_format($tot_importo_pagato,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	 
	echo '<tr>';
	echo '<td colspan="3" style="font-weith:bold;text-align:right">Totali</td>';
	echo '<td style="text-align:center;">'.$tot_importo.'&nbsp;&euro;</td>';
	echo '<td style="text-align:center;">'.$tot_importo_pagato.'&nbsp;&euro;</td>';
	echo '<td></td>';
	echo '</tr>';
		
	echo '</table></div>';
}
else
	echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFound', 'msg' => __('OrderLifeCyclesSummaryOrderPaidNotFound')));


					
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div> <!-- panel-group --> ';
	
	
echo $this->Form->hidden('order_id',array('value' => $results['Order']['id']));
echo '</fieldset>';
echo $this->Form->end();

echo '</div>'; // end contentMenuLaterale
?>