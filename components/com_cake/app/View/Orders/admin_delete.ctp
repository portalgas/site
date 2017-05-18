<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home',$this->Form->value('Order.id')));
$this->Html->addCrumb(__('Title Delete Order'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

if(!empty($des_order_id))
	echo $this->element('boxDesOrder', array('results' => $desOrdersResults));	

echo $this->Form->create('Order', array('type' => 'post'));?>
	<fieldset>
		<legend><?php echo __('Title Delete Order'); ?></legend>

		<div class="input text"><label for=""><?php echo __('Supplier')?></label><?php echo $results['SuppliersOrganization']['name'];?></div>

		<div class="input text"><label for="">Decorrenza</label><?php echo $results['Order']['name'];?></div>

		<div class="input text"><label for=""><?php echo __('StateOrder');?></label><?php echo $this->App->drawOrdersStateDiv($results);?><?php echo __($results['Order']['state_code'].'-label');?></div>

		<?php echo $this->Element('boxMsg',array('msg' => "Elementi associati che verranno cancellati definitivamente")); ?>

		<?php  ($totArticlesOrder > 0 ? $class = 'qtaUno' : $class = 'qtaZero');?>
		<div class="input text"><label for="">Totale articoli associati all'ordine</label><span class="<?php echo $class;?>"><?php echo $totArticlesOrder;?></span></div>

		<?php  ($totCart > 0 ? $class = 'qtaUno' : $class = 'qtaZero');?>
		<div class="input text"><label for="">Eventuali acquisti associati all'ordine</label><span class="<?php echo $class;?>"><?php echo $totCart;?></span></div>

<?php
		/*
		 *  D.E.S.  titolare
		 */
		if($isTitolareDesSupplier) {
			echo '<div class="input text"><label for="">Sei titolare dell\'ordine condiviso: cancellerai l\'ordine condiviso e tutti gli ordini associati</label><span class="qtaUno">D.E.S.</span></div>';
		}
		
/*
 *  form invio mail se acquisti
 */
 if($totCart > 0) {
		echo '<div class="orders-mail">';
		
		$msg = "L'ordine sarà eliminato: vuoi inviare una mail ai gasisti che hanno già effettuato acquisti?";
		echo $this->element('boxMsg',array('class_msg' => 'notice nomargin','msg' => $msg));

		echo '<div class="left label" style="width:100px !important;">&nbsp;</div>';
		echo '<div class="left radio">';
		echo '<p>';
		echo '<input type="radio" name="send_mail" id="send_mail_Y" value="Y" checked="checked" /><label for="send_mail_Y"><span style="color:green;">Si</span>, invia la mail</label>';
		echo '</p>';
		echo '<p>';
		echo '<input type="radio" name="send_mail" id="send_mail_N" value="N" /><label for="send_mail_N"><span style="color:red;">No</span>, non inviare la mail</label>';
		echo '</p>';
		echo '</div>';
		
		echo $this->Form->input('name',array('label' => 'Intestazione', 'value' => str_replace('<br />', '', $body_header), 'disabled' => 'true'));
		echo $this->Form->input('mail_open_testo', array('label' => "Testo della mail",'value' => $testo_mail));
		// echo '<textarea cols="85%" rows="4" class="noeditor" disabled="true" id="body_footer_no_reply" style="display:inline;">'.str_replace('<br />', '', $body_footer_no_reply).'</textarea>';
		echo '</div>';
}

echo '</fieldset>';
echo $this->Form->hidden('id',array('value' => $results['Order']['id']));
echo $this->Form->end(__('Submit Delete'));

echo '</div>';

$options = [];
echo $this->MenuOrders->drawWrapper($results['Order']['id'], $options);
?>
