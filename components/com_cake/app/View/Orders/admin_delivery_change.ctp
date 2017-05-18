<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$this->Form->value('Order.id')));
$this->Html->addCrumb(__('OrderDeliveryChange'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

echo '<table cellpadding = "0" cellspacing = "0">';
echo '<tr>';
echo '	<th>'.$this->App->drawOrdersStateDiv($this->request->data).'&nbsp;'.__($this->request->data['Order']['state_code'].'-label').'</th>';
echo '</tr>';
echo '</table>';

echo $this->Form->create('Order',array('id' => 'formGas'));
echo '<fieldset>';
echo '<legend>'.__('OrderDeliveryChange').'</legend>';

		echo $this->Form->input('id');
		echo '<div class="input text">';
		echo '<label for="OrderSuppliersOrganizationId">'.__('SuppliersOrganization').'</label>';
		echo $this->Form->value('SuppliersOrganization.name');
		echo '</div>';

		echo '<div class="input text">';
		echo '<label for="OrderDeliveryId">Consegna precedente</label>';
		if($OrderOldresults['Delivery']['sys']=='N')
			echo $OrderOldresults['Delivery']['luogoData'];
		else
			echo $OrderOldresults['Delivery']['luogo'];
		echo '</div>';
		

		echo '<div class="input text">';
		echo '<label for="OrderDeliveryId">Nuova consegna</label>';
		if($this->request->data['Delivery']['sys']=='N')
			echo $this->Form->value('Delivery.luogoData');
		else
			echo $this->Form->value('Delivery.luogo');
		echo '</div>';
				
		$msg = "L'ordine ha cambiato il giorno di consegna: vuoi inviare una mail ai gasisti che hanno già effettuato acquisti?";
		echo $this->element('boxMsg',array('class_msg' => 'notice nomargin','msg' => $msg));
		
		echo $this->Form->input('name',array('label' => 'Intestazione', 'value' => str_replace('<br />', '', $body_header), 'disabled' => 'true'));
		echo $this->Form->input('mail_open_testo', array('label' => "Testo della mail", 'value' => $testo_mail));
		// echo '<textarea cols="85%" rows="4" class="noeditor" disabled="true" id="body_footer_no_reply" style="display:inline;">'.str_replace('<br />', '', $body_footer_no_reply).'</textarea>';
		
	echo '</fieldset>';

echo '<input type="hidden" name="data[Order][delivery_id_old]" value="'.$delivery_id_old.'" />';

echo $this->Form->submit(__('Exit'), array('id' => 'action_exit', 'class' => 'buttonBlu', 'div'=> 'submitMultiple'));
echo $this->Form->submit(__('Send'), array('div'=> 'submitMultiple'));

echo $this->Form->end();

echo '</div>';

$options = [];
echo $this->MenuOrders->drawWrapper($this->Form->value('Order.id'), $options);
?>
<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('#action_exit').click(function() {	
		window.location.replace('<?php echo Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&order_id='.$this->request->data['Order']['id'].'&delivery_id='.$this->request->data['Order']['delivery_id'];?>');
		return false;
	});	
		
	jQuery('#formGas').submit(function() {

		return true;
	});
});
</script>