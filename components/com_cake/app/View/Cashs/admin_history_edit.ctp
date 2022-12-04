<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
if(!isset($delivery_id)) $delivery_id = 0;
$this->Html->addCrumb(__('Cassiere'),array('controller' => 'Cassiere', 'action' => 'home', $delivery_id));
$this->Html->addCrumb(__('Edit Cash History'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="cashs form">';
echo $this->Form->create('CashesHistory', ['id' => 'formGas']);
echo '<fieldset>';
echo '<legend>'.__('Edit Cash History').'</legend>';
		
		echo '<div class="box-details table-responsive"><table class="table">';
		echo '<tr>';
		echo '	<th>'.('Name').'</th>';
		echo '	<th>'.__('Email').'</th>';
		echo '	<th>'.__('CashSaldo').'</th>';
		echo '	<th>'.__('nota').'</th>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>';
		echo $results['Cash']['User']['name'];
		echo '</td>';
		echo '<td>';
		if(!empty($results['Cash']['User']['email']))
			echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$results['Cash']['User']['email'].'">'.$results['Cash']['User']['email'].'</a>';
		echo '</td>';
		echo '<td>';
		echo $results['Cash']['importo_e'];
		echo '</td>';
		echo '<td>';
		echo $results['Cash']['nota'];
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		echo '</div>';			
	
		// echo $this->element('boxCashPreviuos', ['results' => $results]);

	echo '<div style="white-space: nowrap;">';
	echo $this->Form->input('importo', ['type' => 'text', 'label' => __('Importo'), 'after'=>'&nbsp;&euro;', 'style' => 'display:inline', 'class'=>'double', 'value' => $results['CashesHistory']['importo_'], 'disabled' => true]);
	echo '</div>';
	echo $this->Form->input('nota', ['value' => $results['CashesHistory']['nota']]);
	
	echo $this->element('boxCashTotaleImporto', array('totale_importo' => $totale_importo));
echo '</fieldset>';

echo $this->Form->input('id', ['type' => 'hidden', 'value' => $results['CashesHistory']['id']]);
echo $this->Form->end(__('Submit'));
echo '</div>';
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Cashs'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>
