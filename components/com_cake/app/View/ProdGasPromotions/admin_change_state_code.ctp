<?php
$this->App->d($results);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index_gas'));

switch($results['ProdGasPromotion']['state_code']) {
	case "WORKING":
		$this->Html->addCrumb(__('Edit ProdGasPromotion'), array('controller' => 'ProdGasPromotions', 'action' => 'edit_gas', $results['ProdGasPromotion']['id']));
	break;
	case "TRASMISSION-TO-GAS":
	case "FINISH":
	case "PRODGASPROMOTION-CLOSE":
	break;
}
$this->Html->addCrumb(__('Title ChangeStateCode ProdGasPromotion'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="orders form">';

echo $this->Form->create('ProdGasPromotion', ['type' => 'post']);
echo '<fieldset>';
echo '<legend>'.__('Title ChangeStateCode ProdGasPromotion').'</legend>';

echo $this->Element('boxProdGasPromotionOrganizations', array('results' => $results));

$nextResults['ProdGasPromotion']['state_code'] = 'WORKING';

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
		<li><?php echo $this->Html->link(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index_gas'),array('class'=>'action actionReload'));?></li>
	</ul>	
</div>