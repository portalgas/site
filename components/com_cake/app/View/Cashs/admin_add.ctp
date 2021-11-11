<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
if(!isset($delivery_id)) $delivery_id = 0;
$this->Html->addCrumb(__('Cassiere'),array('controller' => 'Cassiere', 'action' => 'home', $delivery_id));
$this->Html->addCrumb(__('Add Cash'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="cashs form">';
echo $this->Form->create('Cash', array('id' => 'formGas'));
echo '<fieldset>';
echo '<legend>'.__('Add Cash').'</legend>';
	    $options = array('options' => $users, 
						 'empty' => __('CashVoceGeneric'),
						 'default' => $user_id, 
						 'escape' => false);
		if(count($users) > Configure::read('HtmlSelectWithSearchNum')) 
			$options += array('class'=> 'selectpicker', 'data-live-search' => true); 
		else
		if(count($users) > 1)
			$options += array('empty' => Configure::read('option.empty'));
	
		echo $this->Form->input('user_id', $options);
		echo '<div style="white-space: nowrap;">';
		echo $this->Form->input('importo', array('type' => 'text', 'label' => __('CashSaldo'), 'after'=>'&nbsp;&euro;', 'style' => 'display:inline', 'class'=>'double'));
		echo '</div>';
		echo $this->Form->input('nota');
		
		echo $this->element('boxCashTotaleImporto', array('totale_importo' => $totale_importo));
echo '</fieldset>';
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
			alert("Devi indicare l'importo della voce di cassa");
			$('#CashImporto').focus();
			return false;
		}		
				
		return true;
	});
});
</script>
