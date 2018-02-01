<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Deliveries'), array('controller' => 'Deliveries', 'action' => 'index'));
$this->Html->addCrumb(__('Edit Delivery'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="deliveries form">
<?php echo $this->Form->create('Delivery');?>
	<fieldset>
		<legend><?php echo __('Edit Delivery'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('luogo');
		
		echo $this->Form->input('data',array('type' => 'text','size'=>'30','value' => $this->Time->i18nFormat($this->Form->value('Delivery.data'),"%A, %e %B %Y")));
		
		echo '<table style="float: right;width: 90%;">';
		echo '	<tr class="view">';
		echo '		<td>';
		echo '			<a action="deliveries_small-'.$this->Form->value('Delivery.id').'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a> Visualizza gli ordini associati';
		echo '		</td>';
		echo '	</tr>';
		echo '	<tr class="trView" id="trViewId-'.$this->Form->value('Delivery.id').'">';
		echo '		<td id="tdViewId-'.$this->Form->value('Delivery.id').'"></td>';
		echo '	</tr>';
		echo '</table>';
	
		echo $this->Ajax->datepicker('DeliveryData',array('dateFormat' => 'DD, d MM yy','altField' => '#DeliveryDataDb', 'altFormat' => 'yy-mm-dd'));
		echo '<input type="hidden" id="DeliveryDataDb" name="data[Delivery][data_db]" value="'.$this->Form->value('Delivery.data').'" />';
		
		echo $this->Html->div('clearfix','');
		
		echo '<div class="row">';
		$options['required'] = 'required';
		echo $this->App->drawHour('Delivery', 'orario_da', __('orario_da'), $this->Form->value('Delivery.orario_da'), $options);	
		echo $this->App->drawHour('Delivery', 'orario_a', __('orario_a'), $this->Form->value('Delivery.orario_a'), $options);		
		echo '</div>';		
		
		echo $this->Html->div('clearfix','');
		echo '<br />';
		
		echo '<div class="row">';
		echo '<div class="col-md-12">';		
		echo $this->Form->input('nota');
		echo $this->Html->div('clearfix','');
		echo $this->Form->input('nota_evidenza',array('options' => $nota_evidenza,
													  'value' => $this->Form->value('Delivery.nota_evidenza'),
													  'label' => 'Nota evidenza',
													  'after'=>'<div id="DeliveryNotaEvidenzaImg" style="float:right;height:18px;width:400px;" class=""></div>'));
		echo '</div>';
		echo '</div>';
		
		echo $this->Html->div('clearfix','');
		
		if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
		
			if(isset($this->request['data']['Delivery']['stato_elaborazione']) && 
			   $this->request['data']['Delivery']['stato_elaborazione']=='OPEN' &&  // consegna aperta
			   $this->request['data']['Delivery']['isToStoreroom']=='Y' &&  // consegna aperta
			   $this->request['data']['Delivery']['isToStoreroomPay']=='N') {
				$msg = "La consegna ha settato il flag 'Dispensa' a Si:<br />se cambi il suo valore a No, gli acquisti che dovevano essere ritirati durante questa consegna torneranno in dispensa.";
				echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => $msg));
			}
			echo $this->App->drawFormRadio('Delivery','isToStoreroom',array('options' => $isToStoreroom, 'value'=>$this->Form->value('Delivery.isToStoreroom'), 'label'=>__('isToStoreroom'), 'required'=>'required',
																			'after'=>$this->App->drawTooltip(null,__('toolTipIsToStoreroom'),$type='HELP')));
	
			echo '<div class="input text"><label for="">'.__('isToStoreroomPay').'</label>';
			if($this->request['data']['Delivery']['isToStoreroomPay']=='Y') 
				echo '<span style="color:green;">è già stato</span> richiesto agli utenti il pagamento della dispensa</div>';
			else
				echo '<span style="color:red;">non è stato</span> ancora richiesto agli utenti il pagamento della dispensa</div>';
		}
		
		if($user->organization['Organization']['hasVisibility']=='Y') {
			echo $this->App->drawFormRadio('Delivery','isVisibleFrontEnd',array('options' => $isVisibleFrontEnd, 'value'=>$this->Form->value('Delivery.isVisibleFrontEnd'), 'label'=>__('isVisibleFrontEnd'), 'required'=>'required',
														   		    'after'=>$this->App->drawTooltip(null,__('toolTipIsVisibleFrontEndDelivery'),$type='HELP')));
	
			echo $this->App->drawFormRadio('Delivery','isVisibleBackOffice',array('options' => $isVisibleBackOffice, 'value'=>$this->Form->value('Delivery.isVisibleBackOffice'), 'label'=>__('isVisibleBackOffice'), 'required'=>'required',
														   		    'after'=>$this->App->drawTooltip(null,__('toolTipIsVisibleBackOfficeDelivery'),$type='HELP')));
		}
	?>
	</fieldset>
<?php 
echo $this->Form->hidden('isToStoreroomPay',array('value'=>$this->request['data']['Delivery']['isToStoreroomPay']));
echo $this->Form->hidden('isToStoreroom_old',array('value'=>$this->request['data']['Delivery']['isToStoreroom']));
echo $this->Form->end(__('Submit'));
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Deliveries'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Delete'), array('action' => 'delete', null, 'delivery_id='.$this->Form->value('Delivery.id')),array('class' => 'action actionDelete','title' => __('Delete'))); ?></li>
	</ul>
</div>

<script type="text/javascript">
$(document).ready(function() {

	$('#DeliveryNotaEvidenzaImg').addClass("nota_evidenza_<?php echo strtolower($this->Form->value('Delivery.nota_evidenza'));?>");
	
	$('#DeliveryNotaEvidenza').change(function() {
		var deliveryNotaEvidenza = $(this).val();
		setNotaEvidenza(deliveryNotaEvidenza);
	});
	
	<?php
	if(!empty($nota_evidenzaDefault)) 
		echo 'setNotaEvidenza(\''.$nota_evidenzaDefault.'\');';
	?>
	
});

function setNotaEvidenza(deliveryNotaEvidenza) {
	$('#DeliveryNotaEvidenzaImg').removeClass();
	$('#DeliveryNotaEvidenzaImg').addClass("nota_evidenza_"+deliveryNotaEvidenza.toLowerCase());
}
</script>
