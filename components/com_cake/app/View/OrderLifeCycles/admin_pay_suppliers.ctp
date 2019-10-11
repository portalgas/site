<?php
$this->App->d($results);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$results['Order']['id']));
$this->Html->addCrumb(__('OrderLifeCyclesSupplierPaid'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

echo '<h2 class="ico-pay">';
echo __('OrderLifeCyclesSupplierPaid');
echo '</h2>';

echo $this->element('boxOrder',array('results' => $results));

/*
 * msg
 */	
if(!$results['isPaid']) 
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => __('OrderLifeCyclesSupplierPaidTesoriere')));
			
echo $this->Form->create('SummaryOrder',array('id' => 'formGas'));
echo '<fieldset style="margin: 0;padding: 0;">';


echo '<div class="table-responsive"><table class="table table-hover">';
echo '<tr>';
echo '<th>'.__('Importo totale ordine').'</th>';
echo '<th>'.__('Tesoriere fattura importo').'</th>';
echo '<th>'.__('Tesoriere Importo Pay').'</th>';
echo '<th>'.__('Tesoriere Data Pay').'</th>';
echo '</tr>';

if(!$results['isPaid']) 
	echo '<tr class="OrderTesoriereStatoPay'.$results['Order']['tesoriere_stato_pay'].'">';
else
	echo '<tr class="OrderTesoriereStatoPay'.$results['Order']['tesoriere_stato_pay'].'">';

echo '<td>';
echo number_format($results['Order']['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
echo '</td>';
echo '<td>';
echo $results['Order']['tesoriere_fattura_importo_e'];
echo '</td>';
echo '<td>';
echo $results['Order']['tesoriere_importo_pay_e'];
echo '</td>';
echo '<td>';
if($results['Order']['tesoriere_data_pay']==Configure::read('DB.field.date.empty'))
	$tesoriere_data_pay = '';
else
	$tesoriere_data_pay = $this->Time->i18nFormat($results['Order']['tesoriere_data_pay'],"%A, %e %B %Y");

echo $tesoriere_data_pay;
echo '</td>';
echo '</tr>';

echo '</table></div>';
	
echo $this->Form->hidden('order_id',array('value' => $results['Order']['id']));
echo '</fieldset>';
	
echo $this->Form->end();

echo '</div>'; // end contentMenuLaterale
?>