<?php
// $this->App->dd($results);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home',$this->Form->value('Order.id')));
$this->Html->addCrumb(__('Close Order'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

if(!empty($des_order_id))
	echo $this->element('boxDesOrder', array('results' => $desOrdersResults));	

echo $this->Form->create('Order', array('type' => 'post'));
echo '<fieldset>';
	
echo '<legend>'.__('Close Order').'</legend>';

	echo '<div class="input text"><label for="">'.__('StateOrder').'</label> ';
	echo $this->App->drawOrdersStateDiv($results).__($results['Order']['state_code'].'-label');
	echo '</div>';		

	echo '<div class="input text"><label for="">'.__('Supplier').'</label> ';
	echo $results['SuppliersOrganization']['name'];
	echo '</div>';

	echo '<div class="input text"><label for="">'.__('Delivery').'</label> '; 
		
	if($results['Delivery']['sys']=='N')
		echo $results['Delivery']['luogoData'];
	else 
		echo $results['Delivery']['luogo'];		
	echo '</div>';

	echo '<div class="input text"><label for="">Decorrenza</label> '.$results['Order']['name'].'</div>';

	if(!$canOrdersClose) {
		switch($user->organization['Organization']['canOrdersClose']) {
			case 'ALL':
				$msgCan = $msg;
			break;
			case 'SUPER-REFERENT':
				$msgCan = "Solo il super-referente può chiudere l'ordine, contattalo";
			break;
			case 'REFERENT':
				$msgCan = "Solo il referente può chiudere l'ordine, contattalo";
			break;
		}
		echo $this->Element('boxMsg', ['class_msg' => 'danger', 'msg' => $msgCan]); 

		echo '</fieldset>';
		echo $this->Form->hidden('id', ['value' => $results['Order']['id']]);
		echo $this->Form->end();	
	}
	else { 
		/*
		 * non +, l'ordine andra' allo stato CLOSE anche se non pagato al produttore 
		 * echo $this->App->drawFormRadio('Order','order_just_pay', array('options' => $order_just_pay, 'value' => 'Y', 'name' => 'order_just_pay', 'label'=>__('order_just_pay')));
		 */ 
		
		echo $this->Element('boxMsg',array('msg' => $msg)); 
		
		/*
		 * se order_just_pay = Y forzo il pagamento di un produttore
		 */		
		if($user->organization['Template']['orderSupplierPaid']=='Y' && $canOrdersClose) 
			echo $this->App->drawFormRadio('Order','order_just_pay', ['options' => $order_just_pay, 'value' => 'Y', 'name' => 'order_just_pay', 'label'=>__('order_just_pay')]);
		
			
		echo '</fieldset>';
		echo $this->Form->hidden('id', ['value' => $results['Order']['id']]);
			
		if($canOrdersClose)
			echo $this->Form->end(__('Submit'));
		else
			echo $this->Form->end();
    } // end if($canOrdersClose) 
    
echo '</div>';

$options = [];
echo $this->MenuOrders->drawWrapper($results['Order']['id'], $options);
?>