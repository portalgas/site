<div class="prodDeliveries form">
<?php echo $this->Form->create('ProdDelivery'); 

echo '<fieldset>';
echo '<legend>'.__('Edit ProdDelivery').'</legend>';

echo $this->Form->input('id');
echo $this->Form->input('prod_group_id');
echo $this->Form->input('name');
echo $this->Form->value('ProdDelivery.data_inizio');
echo $this->Form->input('data_inizio',array('type' => 'text','size'=>'30','label' => __('DataInizio'), 'value' => $this->Time->i18nFormat($this->Form->value('ProdDelivery.data_inizio'),"%A, %e %B %Y")));
echo $this->Ajax->datepicker('ProdDeliveryDataInizio',array('dateFormat' => 'DD, d MM yy','altField' => '#ProdDeliveryDataInizioDb', 'altFormat' => 'yy-mm-dd'));
echo '<input type="hidden" id="ProdDeliveryDataInizioDb" name="data[ProdDelivery][data_inizio_db]" value="'.$this->Form->value('ProdDelivery.data_inizio').'" />';

echo $this->Form->input('data_fine',array('type' => 'text','size'=>'30','label' => __('DataFine'), 'value' => $this->Time->i18nFormat($this->Form->value('ProdDelivery.data_fine'),"%A, %e %B %Y")));
echo $this->Ajax->datepicker('ProdDeliveryDataFine',array('dateFormat' => 'DD, d MM yy','altField' => '#ProdDeliveryDataFineDb', 'altFormat' => 'yy-mm-dd'));
echo '<input type="hidden" id="ProdDeliveryDataFineDb" name="data[ProdDelivery][data_fine_db]" value="'.$this->Form->value('ProdDelivery.data_fine').'" />';

if($user->organization['Organization']['hasVisibility']=='Y') {
	echo $this->App->drawFormRadio('Delivery','isVisibleFrontEnd',array('options' => $isVisibleFrontEnd, 'value'=>$this->Form->value('ProdDelivery.isVisibleFrontEnd'), 'label'=>__('isVisibleFrontEnd'), 'required'=>'required',
												   		    'after'=>$this->App->drawTooltip(null,__('toolTipIsVisibleFrontEndProdDelivery'),$type='HELP')));

	echo $this->App->drawFormRadio('Delivery','isVisibleBackOffice',array('options' => $isVisibleBackOffice, 'value'=>$this->Form->value('ProdDelivery.isVisibleBackOffice'), 'label'=>__('isVisibleBackOffice'), 'required'=>'required',
												   		    'after'=>$this->App->drawTooltip(null,__('toolTipIsVisibleBackOfficeProdDelivery'),$type='HELP')));
}

echo $this->Form->input('ricorrenza_num',  array('type' => 'text', 'size'=>'4',  'value' => $ricorrenza_num, 'required'=>'false'));
echo $this->Form->input('ricorrenza_type',array('options' => $ricorrenza_type,
		'default' => '',
		'required'=>'false'));		

echo '</fieldset>';

echo $this->Form->end(__('Submit'));
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List ProdDeliveries'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Delete'), array('action' => 'delete', null, 'delivery_id='.$this->Form->value('ProdDelivery.id')),array('class' => 'action actionDelete','title' => __('Delete'))); ?></li>
	</ul>
</div>
