<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
if($type=='GAS')
	$action = 'index_gas';
if($type=='GAS-USERS')
	$action = 'index_gas_users';	
$this->Html->addCrumb(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => $action));
if($type=='GAS')
	$action = 'edit_gas';
if($type=='GAS-USERS')
	$action = 'edit_gas_users';	
$this->Html->addCrumb(__('Edit ProdGasPromotion'), array('controller' => 'ProdGasPromotions', 'action' => $action, $results['ProdGasPromotion']['id']));
$this->Html->addCrumb(__('Title Delete ProdGasPromotion'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="orders form">';

echo $this->Form->create('ProdGasPromotion', ['type' => 'post']);
echo '<fieldset>';
echo '<legend>'.__('Title Delete ProdGasPromotion').'</legend>';

echo $this->Element('boxProdGasPromotionOrganizations', array('results' => $results));

$msg = "Cancello definitivamente la promozione ".$results['ProdGasPromotion']['name'];
echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFound', 'msg' => $msg));

echo '</fieldset>';
echo $this->Form->hidden('id', ['value' => $results['ProdGasPromotion']['prod_gas_promotion_id']]);
echo $this->Form->hidden('type', ['value' => $type]);
echo $this->Form->end(__('Submit Delete'));
echo '</div>';

echo '<div class="actions">';
	echo '<h3>';
	echo __('Actions');
	echo '</h3>';
	echo '<ul>';		
		echo '<li>'; 
		if($type=='GAS')
			$action = 'index_gas';
		if($type=='GAS-USERS')
			$action = 'index_gas_users';
		echo $this->Html->link(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => $action), array('class'=>'action actionReload'));
		echo '</li>';

		echo '<li>';
		if($type=='GAS')
			$action = 'edit_gas';
		if($type=='GAS-USERS')
			$action = 'edit_gas_users';		
		echo $this->Html->link(__('Edit ProdGasPromotion'), array('controller' => 'ProdGasPromotions', 'action' => $action, $results['ProdGasPromotion']['id']),array('class'=>'action actionEdit'));
		echo '</li>';
echo '</ul>';
echo '</div>';
?>