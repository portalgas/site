<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
if(!isset($delivery_id)) $delivery_id = 0;
$this->Html->addCrumb(__('Cassiere'),array('controller' => 'Cassiere', 'action' => 'home', $delivery_id));
$this->Html->addCrumb(__('Add Cash'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="cashs form">
<?php echo $this->Form->create('Cash', array('id' => 'formGas'));?>
	<fieldset>
		<legend><?php echo __('Add Cash'); ?></legend>
	<?php
	    $options = array('options' => $users, 
						 'empty' => __('CashVoceGeneric'), 
						 'escape' => false);
		if(count($users) > Configure::read('HtmlSelectWithSearchNum')) 
			$options += array('class'=> 'selectpicker', 'data-live-search' => true); 
		else
		if(count($users) > 1)
			$options += array('empty' => Configure::read('option.empty'));
	
		echo $this->Form->input('user_id', $options);
		echo $this->Form->input('importo', array('type' => 'text', 'label' => __('CashSaldo'), 'size'=>10, 'after'=>'&euro;', 'class'=>'double noWidth'));
		echo $this->Form->input('nota');
		
		echo $this->element('boxCashTotaleImporto', array('totale_importo' => $totale_importo));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Cashs'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	 
	jQuery('#formGas').submit(function() {

		var importo = jQuery('#CashImporto').val();
		if(importo=='' || importo==undefined || importo=='0,00' || importo=='0.00' || importo=='0') {
			alert("Devi indicare l'importo della voce di cassa");
			jQuery('#CashImporto').focus();
			return false;
		}		
				
		return true;
	});
});
</script>
