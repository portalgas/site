<?php
$this->App->d($results);

$type = $results['ProdGasPromotion']['type'];
$nextResults['ProdGasPromotion']['state_code'] = $next_code;

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
if($type=='GAS')
	$this->Html->addCrumb(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index_gas'));
else
if($type=='GAS-USERS')
	$this->Html->addCrumb(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index_gas_users'));

switch($results['ProdGasPromotion']['state_code']) {
	case "PRODGASPROMOTION-GAS-WORKING":
		$this->Html->addCrumb(__('Edit ProdGasPromotion'), ['controller' => 'ProdGasPromotions', 'action' => 'edit_gas', $results['ProdGasPromotion']['id']]);
	case "PRODGASPROMOTION-GAS-USERS-WORKING":
		$this->Html->addCrumb(__('Edit ProdGasPromotion'), ['controller' => 'ProdGasPromotions', 'action' => 'edit_gas_users', $results['ProdGasPromotion']['id']]);
		
	break;
	case "PRODGASPROMOTION-GAS-TRASMISSION-TO-GAS":
	case "PRODGASPROMOTION-GAS-FINISH":
	case "PRODGASPROMOTION-GAS-CLOSE":
	break;
}
$this->Html->addCrumb(__('Title ChangeStateCode ProdGasPromotion'));
echo $this->Html->getCrumbList(['class'=>'crumbs']);

echo '<div class="orders form">';

echo $this->Form->create('ProdGasPromotion', ['type' => 'post']);
echo '<fieldset>';
echo '<legend>'.__('Title ChangeStateCode ProdGasPromotion').'</legend>';

echo $this->Element('boxProdGasPromotionOrganizations', ['results' => $results]);

echo '<div class="table-responsive"><table class="table table-hover">';
echo '<tr>';
echo '	<th>dallo stato</th>';
echo '	<th>allo stato</th>';
echo '</tr>';
echo '<tr>';
echo '	<td>'.$this->App->drawProdGasPromotionsStateDiv($results)."&nbsp;".__($results['ProdGasPromotion']['state_code'].'-label').'</td>';
echo '	<td>'.$this->App->drawProdGasPromotionsStateDiv($nextResults)."&nbsp;".__($nextResults['ProdGasPromotion']['state_code'].'-label').'</td>';
echo '</tr>';
echo '</table></div>';

echo '</fieldset>';
echo $this->Form->hidden('id', ['value' => $results['ProdGasPromotion']['id']]);
echo $this->Form->hidden('next_code', ['value' => $next_code]);
echo $this->Form->end(__('ChangeStateProdGasPromotion'));
echo '</div>';
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<?php 
		if($type=='GAS')
			echo '<li>'.$this->Html->link(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index_gas'),array('class'=>'action actionReload')).'</li>';
		else
		if($type=='GAS-USERS')
			echo '<li>'.$this->Html->link(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index_gas_users'),array('class'=>'action actionReload')).'</li>';
		?>
	</ul>	
</div>