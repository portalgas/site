<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
if(!isset($delivery_id)) $delivery_id = 0;
$this->Html->addCrumb(__('Cassiere'),array('controller' => 'Cassiere', 'action' => 'home', $delivery_id));
$this->Html->addCrumb(__('Edit Cash'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="cashs form">';
echo $this->Form->create('Cash', ['id' => 'formGas']);
echo '<fieldset>';
echo '<legend>'.__('Edit Cash').'</legend>';
	
		echo $this->Form->input('id');
		
        /*
         * voce di spesa generica
         */		
		if(!empty($results['User']['id'])) {
			echo '<div class="box-details table-responsive"><table class="table">';
			echo '<tr>';
			echo '	<th>'.('Name').'</th>';
			echo '	<th>'.__('Email').'</th>';
			echo '</tr>';
			echo '<tr>';

			echo '<td>';
			echo $results['User']['name'];
			echo '</td>';
			
			echo '<td>';
			if(!empty($results['User']['email']))
				echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$results['User']['email'].'">'.$results['User']['email'].'</a>';
			echo '</td>';
			echo '</tr>';
			echo '</table>';
			echo '</div>';			
		}
	
		echo $this->element('boxCashPreviuos', ['results' => $results]);

	echo '<div style="white-space: nowrap;">';
	echo $this->Form->input('importo', ['type' => 'text', 'label' => __('CashSaldo'), 'after'=>'&nbsp;&euro;', 'style' => 'display:inline', 'class'=>'double', 'value' => $results['Cash']['importo_']]);
	echo '</div>';
	echo $this->Form->input('nota', ['value' => $results['Cash']['nota']]);
	
	echo $this->element('boxCashTotaleImporto', array('totale_importo' => $totale_importo));
echo '</fieldset>';

echo $this->Form->input('user_id', ['type' => 'hidden', 'value' => $results['User']['id']]);
echo $this->Form->input('id', ['type' => 'hidden', 'value' => $results['Cash']['id']]);
echo $this->Form->end(__('Submit'));
echo '</div>';
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Cashs'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<script type="text/javascript">
$(document).ready(function() {
	 
	$('#formGas').submit(function() {

		var importo = $('#CashImporto').val();
		if(importo=='' || importo==undefined || importo=='0,00' || importo=='0.00' || importo=='0') {
			alert("Devi indicare l'importo");
			$('#CashImporto').focus();
			return false;
		}		
				
		return true;
	});
});
</script>
