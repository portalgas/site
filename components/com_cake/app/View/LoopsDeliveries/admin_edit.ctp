<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List LoopsDeliveries'), array('controller' => 'LoopsDeliveries', 'action' => 'index'));
$this->Html->addCrumb(__('Edit LoopsDelivery'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="loopsDeliveries form">';
echo $this->Form->create('LoopsDelivery');
echo '<fieldset>';
echo '<legend>'.__('Edit LoopsDelivery').'</legend>';

echo $this->Form->input('luogo');

echo '<h3>La nuova consegna avrà come data</h3>';	
echo $this->Time->i18nFormat($this->Form->value('LoopsDelivery.data_copy'),"%A, %e %B %Y");

echo '<h3>ma la cambio in</h3>';
echo $this->Form->input('data_copy_reale',array('label' => false, 'type' => 'text','size'=>'30','value' => $this->Time->i18nFormat($this->Form->value('LoopsDelivery.data_copy_reale'),"%A, %e %B %Y")));

echo $this->Ajax->datepicker('LoopsDeliveryDataCopyReale',array('dateFormat' => 'DD, d MM yy','altField' => '#LoopsDeliveryDataCopyRealeDb', 'altFormat' => 'yy-mm-dd'));
echo '<input type="hidden" id="LoopsDeliveryDataCopyRealeDb" name="data[LoopsDelivery][data_copy_reale_db]" value="'.$this->Form->value('LoopsDelivery.data_copy_reale').'" />';

echo '<div class="row">';
echo $this->App->drawHour('LoopsDelivery', 'orario_da', __('Orario da'), $this->Form->value('LoopsDelivery.orario_da'));
echo '</div>';

echo '<div class="row">';
echo $this->App->drawHour('LoopsDelivery', 'orario_a', __('Orario a'), $this->Form->value('LoopsDelivery.orario_a'));
echo '</div>';

echo $this->Form->input('nota');
echo $this->Html->div('clearfix','');
echo $this->Form->input('nota_evidenza',array('options' => $nota_evidenza,
											  'id' => 'DeliveryNotaEvidenza',
											  'value' => $this->Form->value('LoopsDelivery.nota_evidenza'),
											  'label' => 'Nota evidenza',
											  'after'=>'<div id="DeliveryNotaEvidenzaImg" style="float:right;height:18px;width:400px;" class=""></div>'));
									
echo $this->Html->div('clearfix','');

echo $this->App->drawFormRadio('LoopsDelivery','flag_send_mail',array('options' => $flag_send_mail, 'value' => $this->Form->value('LoopsDelivery.flag_send_mail'), 'label'=> "Notifico con una mail a chi crea la ricorsione", 'required'=>'required'));

echo '</fieldset>';
echo $this->Form->end(__('Submit')); 
echo '</div>';
echo '<div class="actions">';
?>
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List LoopsDeliveries'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Delete'), array('action' => 'delete', $this->Form->value('LoopsDelivery.id')), array('class' => 'action actionDelete','title' => __('Delete'))); ?></li>
	</ul>
</div>

<script type="text/javascript">
$(document).ready(function() {

	$('#DeliveryNotaEvidenzaImg').addClass("nota_evidenza_<?php echo strtolower($this->Form->value('LoopsDelivery.nota_evidenza'));?>");
	
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