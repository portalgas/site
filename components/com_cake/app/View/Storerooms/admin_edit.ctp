<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('Storeroom'), array('controller' => 'Storerooms', 'action' => 'index'));
$this->Html->addCrumb(__('Edit associate article to user'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
/*
echo "<pre>";
print_r($this->data);
echo "</pre>";
*/
?>
	
<div class="orders form">
<?php echo $this->Form->create('Storeroom');?>
	<fieldset>
		<legend><?php echo __('Edit associate article to user'); ?></legend>

	<?php	
		echo $this->Form->input('name',array('disabled' => 'true'));

		echo '<div class="input select">';
		
		echo "\r\n";
		echo '<table style="width: 77%; float: right;">';
		echo '<tr>';
		echo '<th>'.__('Conf').'</th>';
		echo '<th>'.__('Prezzo/UM').'</th>';
		echo '<th>'.__('PrezzoUnita').'</th>';
		echo '<th>'.__('Importo').'</th>';
		echo '<th>'.__('qta').'</th>';
		echo '</tr>';
		
		echo "\r\n";
		echo '<tr>';
		echo "\r\n";
		echo '<td>';
		echo $this->data['Article']['qta'].' '.$this->App->traslateEnum($this->data['Article']['um']);
		echo '</td>';
		echo "\r\n";
		
		echo '<td>';
		$prezzo = number_format(round(((1/$this->data['Article']['qta']) * $this->data['Storeroom']['prezzo']),2),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		echo $prezzo.'/'.$this->App->traslateEnum($this->data['Article']['um_riferimento']);
		echo '</td>';
		
		echo "\r\n";
		echo '<td>';
		echo $this->data['Storeroom']['prezzo_e'];
		echo '</td>';
		
		echo "\r\n";
		echo '<td style="white-space: nowrap;">';
		$options['label'] = false;
		$options['disabled'] = true; 
		$options['style'] = 'display:inline;';
		$options['value'] = $this->data['Storeroom']['prezzo_'];
		$options['after'] = ' <span style="font-size:14px;">&euro;</span>';
		echo $this->Form->input('prezzoNew',$options);
		echo '</td>';
		echo '<td>';
		echo $this->Form->input('qta', array('empty' => Configure::read('option.empty'), 
											 'label' => false,
											 'id' => 'qta',
											 'type' => 'select', 
											 'options' => array_combine(range(1, $this->data['Storeroom']['qta']),range(1, $this->data['Storeroom']['qta'])),
											 'default' => $this->data['Storeroom']['qta'],
											 'onChange' => 'javascript:setImporto(this);'));	
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		echo "\r\n";
		
		echo '</div>';
		


		$optionsCommons = array('type'=>'text', 'disabled' => 'true', 'style'=>'width:auto !important');
		$options = $optionsCommons + array('label' => 'Utente','value' => $this->data['User']['name'], 'size'=>'50');
		echo $this->Form->input('disabled',$options);
		
		echo $this->Form->input('delivery_id');

		echo $this->Form->hidden('id');	
		echo $this->Form->hidden('user_id');
		echo $this->Form->hidden('order_id',array('value'=>0));
		
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Storeroom'), array('action' => 'index_to_users'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>
<script type="text/javascript">
function setImporto() {
	var prezzo = '<?php echo $this->data['Storeroom']['prezzo']?>';
	var qta = $("#qta").val();	

	prezzoNew = number_format(prezzo*qta,2,',','.');
	$('#StoreroomPrezzoNew').val(prezzoNew);	
}
$(function() {
	setImporto();
});
</script>