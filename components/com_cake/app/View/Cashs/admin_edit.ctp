<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
if(!isset($delivery_id)) $delivery_id = 0;
$this->Html->addCrumb(__('Cassiere'),array('controller' => 'Cassiere', 'action' => 'home', $delivery_id));
$this->Html->addCrumb(__('Edit Cash'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="cashs form">';
echo $this->Form->create('Cash', array('id' => 'formGas'));
echo '<fieldset>';
echo '<legend>'.__('Edit Cash').'</legend>';

		echo $this->Form->input('id');
		
		echo '<div class="box-details table-responsive"><table class="table">';
		echo '<tr>';
		echo '	<th></th>';
		echo '	<th>'.('Name').'</th>';
		echo '	<th>'.__('Email').'</th>';
		echo '  <th>'.__('Telephone').'</th>';
		echo '  <th>'.__('Address').'</th>';
		echo '</tr>';
		echo '<tr>';

		echo '<td>';
		echo $this->App->drawUserAvatar($user, $utente['User']['id'], $utente['User']);
		echo '</td>';
		
		echo '<td>';
		echo $utente['User']['name'];
		echo '</td>';
		
		echo '<td>';
		if(!empty($utente['User']['email']))
			echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$utente['User']['email'].'">'.$utente['User']['email'].'</a>';
		echo '</td>';
		echo '<td>';
		if(!empty($utente['Profile']['phone'])) echo $utente['Profile']['phone'].'<br />';
		if(!empty($utente['Profile']['phone2'])) echo $utente['Profile']['phone2'];
		echo '</td>';
		echo '<td>';
		echo $utente['Profile']['address'];
		if(!empty($utente['Profile']['city'])) echo ' '.$utente['Profile']['city'];
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		echo '</div>';
	
		echo $this->element('boxCashPreviuos',array('results' => $this->request->data));

		echo '<div style="white-space: nowrap;">';
		echo $this->Form->input('importo', array('type' => 'text', 'label' => __('CashSaldo'), 'after'=>'&nbsp;&euro;', 'value' => '', 'style' => 'display:inline', 'class'=>'double'));
		echo '</div>';
		
		
		echo $this->Form->input('nota', array('value' => ''));
		
		echo $this->element('boxCashTotaleImporto', array('totale_importo' => $totale_importo));
	
	echo '</fieldset>';

	echo $this->Form->hidden('user_id',array('value' => $this->request->data['Cash']['user_id']));
	
	echo $this->Form->end(__('Submit'));
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Cashs'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Delete'), array('action' => 'delete', $this->Form->value('Cash.id')),array('class' => 'action actionDelete','title' => __('Delete'))); ?></li>
	</ul>
</div>
<script type="text/javascript">
$(document).ready(function() {
	 
	$('#formGas').submit(function() {

		var importo = $('#CashImporto').val();
		if(importo=='' || importo==undefined) { /* || importo=='0,00' || importo=='0.00' || importo=='0') { permetto di portare il saldo a ZERO */
			alert("Devi indicare l'importo della voce di cassa");
			$('#CashImporto').focus();
			return false;
		}
		
		return true;
	});
});
</script>