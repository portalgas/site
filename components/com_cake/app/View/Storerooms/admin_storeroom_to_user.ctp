<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('Storeroom'), array('controller' => 'Storerooms', 'action' => 'index'));
$this->Html->addCrumb(__('Associate article to user'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
	
<div class="orders form" style="min-height:450px;">
<?php echo $this->Form->create('Storeroom');?>
	<fieldset>
		<legend><?php echo __('Associate article to user'); ?></legend>

	<?php
		echo $this->Form->input('supplier',array('value' => $this->data['SuppliersOrganization']['name'], 'disabled' => 'true'));
				
		echo $this->Form->input('name',array('value' => $this->data['Storeroom']['name'], 'disabled' => 'true'));
	
		echo '<div class="input select">';
		
		echo "\r\n";
		echo '<table style="width: 80%; float: right;">';
		echo '<tr>';
		echo '<th>'.__('Conf').'</th>';
		echo '<th>'.__('PrezzoUnita').'</th>';
		echo '<th>'.__('Prezzo/UM').'</th>';
		echo '<th>'.__('Importo').'</th>';
		echo '</tr>';
		
		echo "\r\n";
		echo '<tr>';
		echo "\r\n";
		echo '<td>';
		echo $this->App->getArticleConf($this->data['Article']['qta'], $this->data['Article']['um']);
		echo '</td>';
		echo "\r\n";
		
		echo "\r\n";
		echo '<td>';
		echo number_format($this->data['Storeroom']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
		echo '</td>';

		echo '<td>';
		echo $this->App->getArticlePrezzoUM($this->data['Storeroom']['prezzo'], $this->data['Article']['qta'], $this->data['Article']['um'], $this->data['Article']['um_riferimento']);
		echo '</td>';
				
		echo "\r\n";
		echo '<td>';
		$options['label'] = false;
		$options['style'] = 'width:75px';
		$options['value'] = '0.00';
		$options['after'] = ' <span style="font-size:14px;">&euro;</span>';
		echo $this->Form->input('prezzoNew',$options);
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		echo "\r\n";
		
		echo '</div>';

		echo $this->Form->input('qta', array('empty' => Configure::read('option.empty'),
											 'label' => __('qta'), 
										     'id' => 'qta',
											 'type' => 'select', 
											 'options' => array_combine(range(1, $this->data['Storeroom']['qta']),range(1, $this->data['Storeroom']['qta'])),
											 'selected'=>'',
											 'onChange' => 'javascript:setImporto(this);'));	

		$options = array('empty' => Configure::read('option.empty'));
		if(count($users) > Configure::read('HtmlSelectWithSearchNum')) 
			$options += array('class'=> 'selectpicker', 'data-live-search' => true); 
		echo $this->Form->input('user_id',$options);
		echo $this->Form->input('delivery_id',array('empty' => Configure::read('option.empty')));

		echo $this->Form->hidden('id');	
		echo $this->Form->hidden('order_id',array('value'=>0));
		
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Storeroom'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>
<script type="text/javascript">
function setImporto() {
	var prezzo = '<?php echo $this->data['Storeroom']['prezzo']?>';
	var qta = jQuery("#qta").val();	
	
	prezzoNew = number_format(prezzo*qta,2,',','.');
	jQuery('#StoreroomPrezzoNew').val(prezzoNew);
}
</script>