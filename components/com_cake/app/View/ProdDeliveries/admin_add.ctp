<div class="prodDeliveries form">
<?php echo $this->Form->create('ProdDelivery'); 

echo '<fieldset>';
echo '<legend>'.__('Add ProdDelivery').'</legend>';

echo $this->Form->input('prod_group_id');
echo $this->Form->input('name');

echo $this->Form->input('data_inizio',array('type' => 'text','size'=>'30', 'value' => $data_inizio));
echo $this->Ajax->datepicker('ProdDeliveryDataInizio',array('dateFormat' => 'DD, d MM yy','altField' => '#ProdDeliveryDataInizioDb', 'altFormat' => 'yy-mm-dd'));
echo '<input type="hidden" id="ProdDeliveryDataInizioDb" name="data[ProdDelivery][data_inizio_db]" value="'.$data_inizio_db.'" />';

echo $this->Form->input('data_fine',array('type' => 'text','size'=>'30', 'value' => $data_fine));
echo $this->Ajax->datepicker('ProdDeliveryDataFine',array('dateFormat' => 'DD, d MM yy','altField' => '#ProdDeliveryDataFineDb', 'altFormat' => 'yy-mm-dd'));
echo '<input type="hidden" id="ProdDeliveryDataFineDb" name="data[ProdDelivery][data_fine_db]" value="'.$data_fine_db.'" />';

if($user->organization['Organization']['hasVisibility']=='Y') {
	echo $this->App->drawFormRadio('ProdDelivery','isVisibleFrontEnd',array('options' => $isVisibleFrontEnd, 'value'=> $isVisibleFrontEndDefault, 'label'=>__('isVisibleFrontEnd'), 'required'=>'required',
												   		    'after'=>$this->App->drawTooltip(null,__('toolTipIsVisibleFrontEndProdDelivery'),$type='HELP')));

	echo $this->App->drawFormRadio('ProdDelivery','isVisibleBackOffice',array('options' => $isVisibleBackOffice, 'value'=> $isVisibleBackOfficeDefault, 'label'=>__('isVisibleBackOffice'), 'required'=>'required',
												   		    'after'=>$this->App->drawTooltip(null,__('toolTipIsVisibleBackOfficeProdDelivery'),$type='HELP')));
}

echo $this->Form->input('ricorrenza_num',  array('type' => 'text', 'size'=>'4', 'class' => 'noWidth', 'value' => $ricorrenza_num, 'required'=>'false'));
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
	</ul>
</div>