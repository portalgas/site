<?php
if(empty($des_order_id))
	$label_order =  __('Edit Order');
else
	$label_order =  __('Edit DesOrder');

if($this->request->data['Delivery']['sys']=='N')
	$label_crumb = $label_order.': '.__('Supplier').' <b>'.$this->request->data['SuppliersOrganization']['name'].'</b> '.__('piecesToDelivery').' <b> '.$this->request->data['Delivery']['luogoData'].'</b>';
else 
	$label_crumb = $label_order.': '.__('Supplier').' <b>'.$this->request->data['SuppliersOrganization']['name'].'</b> '.__('piecesToDelivery').' <b>'.$this->request->data['Delivery']['luogo'].'</b>';
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$this->Form->value('Order.id')));
$this->Html->addCrumb($label_crumb);
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

if(!empty($des_order_id))
	echo $this->element('boxDesOrder', array('results' => $desOrdersResults));	

echo '<table cellpadding = "0" cellspacing = "0">';
echo '<tr>';
echo '	<th>'.$this->App->drawOrdersStateDiv($this->request->data).'&nbsp;'.__($this->request->data['Order']['state_code'].'-label').'</th>';
echo '</tr>';
echo '</table>';

echo $this->Form->create('Order',array('id' => 'formGas'));
echo '<fieldset>';
echo '<legend>'.__('Edit Order').'</legend>';

echo '<div class="tabs">';
echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
echo '<li class="active"><a href="#tabs-0" data-toggle="tab">'.__('Dati ordine').'</a></li>';
echo '<li><a href="#tabs-1" data-toggle="tab">'.__('Note Referente').'</a></li>';
echo '<li><a href="#tabs-2" data-toggle="tab">'.__('Per gli utenti').'</a></li>';
if(empty($des_order_id))
	echo '<li><a href="#tabs-3" data-toggle="tab">'.__('Durante l\'ordine').'</a></li>';
if($user->organization['Organization']['payToDelivery']=='ON' || $user->organization['Organization']['payToDelivery']=='ON-POST')
	echo '<li><a href="#tabs-4" data-toggle="tab">'.__('Dopo l\'arrivo della merce').'</a></li>';
else
	if($user->organization['Organization']['payToDelivery']=='POST')
	echo '<li><a href="#tabs-4" data-toggle="tab">'.__('Dopo la consegna').'</a></li>';
echo '<li><a href="#tabs-5" data-toggle="tab">'.__('Suppliers Organizations Referents').'</a></li>';
echo '</ul>';

echo '<div class="tab-content">';
echo '<div class="tab-pane fade active in" id="tabs-0">';
             
		echo $this->Form->input('id');
		
		echo '<div class="row">';
		echo '<div class="col-md-10">';
		echo '<label for="OrderSuppliersOrganizationId">'.__('SuppliersOrganization').'</label> ';
		echo $this->Form->value('SuppliersOrganization.name');
		echo '</div>';
		echo '<div class="col-md-2" id="suppliers_organization_details"></div>';
		echo '</div>';
		echo '<input type="hidden" name="data[Order][supplier_organization_id]" value="'.$this->Form->value('Order.supplier_organization_id').'" />';
				
		/*
		 * ctrl che la consegna dell'ordine sia visibile in backOffice
		 */
		if(isset($msgDeliveryNotValid) && !empty($msgDeliveryNotValid)) 
			echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => $msgDeliveryNotValid));
		
		/*
		 * consegna
		*/
		echo $this->element('boxOrdersDelivery', array('modalita' => 'EDIT', 'isManagerDelivery' => $isManagerDelivery));
		echo $this->Html->div('clearfix','');
		
		echo $this->App->drawDate('Order', 'data_inizio', __('Data inizio'), $this->Form->value('Order.data_inizio'));
		
		if(!empty($this->request->data['Order']['data_fine_validation']) && $this->request->data['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty')) {
			
			echo $this->Form->input('data_fine_validation',array('type' => 'text', 'label' => "Riaperto l'ordine fino a", 'value' => $this->Time->i18nFormat($this->Form->value('Order.data_fine_validation'),"%A, %e %B %Y"), 'required' => 'false'));
			echo $this->Ajax->datepicker('OrderDataFineValidation',array('dateFormat' => 'DD, d MM yy','altField' => '#OrderDataFineValidationDb', 'altFormat' => 'yy-mm-dd'));
			echo '<input type="hidden" id="OrderDataFineValidationDb" name="data[Order][data_fine_validation_db]" value="'.$this->Form->value('Order.data_fine_validation').'" />';
				
			echo '<input type="hidden" id="OrderDataFineDb" name="data[Order][data_fine_db]" value="'.$this->Form->value('Order.data_fine').'" />';
		}
		else {
			echo $this->App->drawDate('Order', 'data_fine', __('Data fine'), $this->Form->value('Order.data_fine'));
		}
					
		if($this->request->data['Order']['data_incoming_order']!='0000-00-00') {
			echo '<div class="input text required">';
			echo '<label>'.__('Data Incoming Order').'</label> ';
			echo $this->Time->i18nFormat($this->Form->value('Order.data_incoming_order'),"%A, %e %B %Y");
			echo '</div>';
		}
		
		/* 
		 * DES, data chiusura ordine
		 */
		if(!empty($des_order_id)) {
			echo '<div class="input text ">';
			echo '<label>'.__('Data fine max').'</label> ';
			echo $this->Time->i18nFormat($desOrdersResults['DesOrder']['data_fine_max'],"%A, %e %B %Y");
			echo '</div>';	
		}		
		
		echo $this->Form->input('nota', array('type' => 'text', 'after' => '<img width="150" class="print_screen" id="print_screen_order_nota" src="'.Configure::read('App.img.cake').'/print_screen_order_nota.jpg" title="" border="0" />'));
		 		
		echo $this->Html->div('clearfix','');

		if($user->organization['Organization']['hasVisibility']=='Y') {
			echo $this->Html->div('clearfix','');
			echo $this->App->drawFormRadio('Order','isVisibleFrontEnd',array('options' => $isVisibleFrontEnd, 'value'=>$this->Form->value('Order.isVisibleFrontEnd'), 'label'=>__('isVisibleFrontEnd'), 'required'=>'false',
					'after'=>$this->App->drawTooltip(null,__('toolTipIsVisibleFrontEnd'),$type='HELP')));
		
			echo $this->App->drawFormRadio('Order','isVisibleBackOffice',array('options' => $isVisibleBackOffice, 'value'=>$this->Form->value('Order.isVisibleBackOffice'), 'label'=>__('isVisibleBackOffice'), 'required'=>'false',
					'after'=>$this->App->drawTooltip(null,__('toolTipIsVisibleBackOffice'),$type='HELP')));
		}
		
echo '</div>';
echo '<div class="tab-pane fade" id="tabs-1">';	

        echo '<div id="mail_order_open_Y">';
    
	/*
	 * mail_open_testo
	*/
	if($this->Form->value('Order.state_code') == 'CREATE-INCOMPLETE' || $this->Form->value('Order.state_code') == 'OPEN-NEXT' || $this->Form->value('Order.state_code') == 'OPEN')
		echo $this->Form->input('mail_open_testo', array('after' => '<img width="100" class="print_screen" id="print_screen_mail_open_testo" src="'.Configure::read('App.img.cake').'/print_screen_mail_open_testo.jpg" title="" border="0" />'));
	else
		echo $this->Form->input('mail_open_testo', array('class' => 'noeditor', 'disabled' => 'disabled', 'cols' => '70', 'after' => '<img width="100" class="print_screen" id="print_screen_mail_open_testo" src="'.Configure::read('App.img.cake').'/print_screen_mail_open_testo.jpg" title="" border="0" />'));
	
	/*
	 * legenda
	*/
	if(($this->Form->value('Order.state_code') == 'CREATE-INCOMPLETE' || $this->Form->value('Order.state_code') == 'OPEN-NEXT' || $this->Form->value('Order.state_code') == 'OPEN')
		 && $this->Form->value('Order.mail_open_data')=='0000-00-00 00:00:00' || $this->Form->value('Order.mail_open_data')=='')
		echo $this->element('legendaOrdersSendMail', array('modalita' => 'EDIT'));
	else
	if($this->Form->value('Order.mail_open_data')!='0000-00-00 00:00:00' || $this->Form->value('Order.mail_close_data')!='0000-00-00 00:00:00')
		echo $this->element('legendaOrdersJustSendMail',array('mail_open_data' => $this->Form->value('Order.mail_open_data'), 'mail_close_data' => $this->Form->value('Order.mail_close_data')));

		echo $this->element('legendaOrderTestoMailFrontEnd');
        echo '</div>';

        echo '<div id="mail_order_open_N">'.__('msg_mail_order_open_N').'</div>';
        echo '<div id="mail_order_close_Y"></div>';
        echo '<div id="mail_order_close_N">'.__('msg_mail_order_close_N').'</div>';
    
echo '</div>';

echo '<div class="tab-pane fade" id="tabs-2">';
echo $this->element('boxOrdersTypeDraw', array('modalita' => 'EDIT', 'value' => $this->Form->value('Order.type_draw')));
echo $this->Html->div('clearfix','');
echo '</div>';

if(empty($des_order_id))  {
	echo '<div class="tab-pane fade" id="tabs-3">';

	echo "\r\n";
	echo '<table>';
	echo '<tr>';
	echo '<td>';
	echo __('order_qta_massima');
	echo '</td>';
	echo '<td>';
	echo $this->Form->input('qta_massima', array('label' => false, 'type' => 'text', 'id' => 'qta_massima'));
	echo '</td>';
	echo '<td>';
	echo $this->Form->input('qta_massima_um',array('id' => 'qta_massima_um', 'label' => false,'options' => $qta_massima_um_options, 'required' => 'false'));
	echo '</td>';
	echo '<td><div class="legenda legenda-ico-mails">'.__('order_qta_massima_help').'</div></td>';
	echo '</tr>';

	echo '<tr>';
	echo '<td>';
	echo __('order_importo_massimo');
	echo '</td>';
	echo '<td colspan="2"  style="white-space: nowrap;">';
	echo $this->Form->input('importo_massimo', array('label' => false, 'type' => 'text', 'id' => 'importo_massimo', 'class' => 'double', 'style' => 'display:inline', 'after' => '&nbsp;&euro;'));
	echo '</td>';
	echo '<td><div class="legenda legenda-ico-mails">'.__('order_importo_massimo_help').'</div></td>';
	echo '</tr>';
	echo '</table>';

	echo '</div>';
}

if($user->organization['Organization']['payToDelivery']=='ON' || $user->organization['Organization']['payToDelivery']=='POST' || $user->organization['Organization']['payToDelivery']=='ON-POST') {
	echo '<div class="tab-pane fade" id="tabs-4">';
	
	echo $this->element('boxOrdersTypeGest', array('modalita' => 'EDIT', 'value' => $this->Form->value('Order.typeGest')));
	
	echo $this->Html->div('clearfix','');
	
	if($user->organization['Organization']['hasTrasport']=='Y') {
		echo '<div class="row">';
		echo '<div class="col-md-1 action actionTrasport">';
		echo '</div>';
		echo '<div class="col-md-11">';
		echo $this->App->drawFormRadio('Order','hasTrasport',array('options' => $hasTrasport, 'value'=> $this->Form->value('Order.hasTrasport'), 'label'=>__('HasTrasport'), 'required'=>'false',
				'after' => $this->App->drawTooltip(null,__('toolTipHasTrasport'),$type='HELP')));
		echo '</div>';
		echo '</div>';
	}
	
	if($user->organization['Organization']['hasCostMore']=='Y') {
		echo '<div class="row">';
		echo '<div class="col-md-1 action actionCostMore">';
		echo '</div>';
		echo '<div class="col-md-11">';			
		echo $this->App->drawFormRadio('Order','hasCostMore',array('options' => $hasCostMore, 'value'=> $this->Form->value('Order.hasCostMore'), 'label'=>__('HasCostMore'), 'required'=>'false',
			'after' => $this->App->drawTooltip(null,__('toolTipHasCostMore'),$type='HELP')));
		echo '</div>';
		echo '</div>';
	}
	
	if($user->organization['Organization']['hasCostLess']=='Y') {
		echo '<div class="row">';
		echo '<div class="col-md-1 action actionCostLess">';
		echo '</div>';
		echo '<div class="col-md-11">';				
		echo $this->App->drawFormRadio('Order','hasCostLess',array('options' => $hasCostLess, 'value'=> $this->Form->value('Order.hasCostLess'), 'label'=>__('HasCostLess'), 'required'=>'false',
			'after' => $this->App->drawTooltip(null,__('toolTipHasCostLess'),$type='HELP')));		
		echo '</div>';
		echo '</div>';
	}
	
	echo '</div>';		
}


echo '<div class="tab-pane fade" id="tabs-5">';
if(!empty($suppliersOrganizationsReferent)) {
	echo '<table>';
	echo '<tr>';
	echo '<th colspan="2"></th>';
	echo '<th>'.__('Address').'</th>';
	echo '<th>'.__('Telephone').'</th>';
	echo '<th>'.__('UserGroup').'</th>';
	echo '</tr>';
			
	foreach ($suppliersOrganizationsReferent as $referent) {
		echo "\n\r";
		echo '<tr>';
		echo '<td style="width:16px;">';
		echo '<span style="cursor:pointer;" title="'.$this->App->traslateEnum($referent['SuppliersOrganizationsReferent']['type']).'">';
		if($referent['SuppliersOrganizationsReferent']['type']=='REFERENTE')
			echo '<img style="margin-right:5px;" alt="'.$this->App->traslateEnum($referent['SuppliersOrganizationsReferent']['type']).'" src="'.Configure::read('App.img.cake').'/icons/16x16/user.png" />';
		else
		if($referent['SuppliersOrganizationsReferent']['type']=='COREFERENTE')
			echo '<img style="margin-right:5px;" alt="'.$this->App->traslateEnum($referent['SuppliersOrganizationsReferent']['type']).'" src="'.Configure::read('App.img.cake').'/icons/16x16/user_add.png" />';
		echo '</td>';
		
		echo '<td>';	
		echo $referent['User']['name'];
	
		if(!empty($referent['User']['email']))	
			echo ' <a class="fa fa-envelope-o fa-lg" title="'.__('Email send').'" target="_blank" href="mailto:'.$referent['User']['email'].'"></a>';
		echo '</td>';

		echo '<td>';
		if(!empty($referent['Profile']['address'])) echo $referent['Profile']['address'];
		echo '</td>';

		echo '<td>';
		if(!empty($referent['Profile']['phone'])) echo $referent['Profile']['phone'].'<br />';
		if(!empty($referent['Profile']['phone2'])) echo $referent['Profile']['phone2'];
		echo '</td>';
		
		/*
		 * userGroups
		 */
		echo '<td>';
		echo $userGroups[$referent['SuppliersOrganizationsReferent']['group_id']]['name'];
		// echo ' '.$userGroups[$referent['SuppliersOrganizationsReferent']['group_id']]['descri'];
		echo '</td>';
		 
		echo '</tr>';			
	} // loop Referenti
	
	/*
	 * Des Referenti
	 */
	 if(!empty($des_order_id) && !empty($desSuppliersReferents)) {
				
		foreach ($desSuppliersReferents as $referent) {
			echo "\n\r";
			echo '<tr>';
			echo '<td style="width:16px;">';
			echo '<span title="'.__('UserGroupsReferentDes').'" style="cursor:pointer;"><img class="img-responsive-disabled" src="'.Configure::read('App.img.cake').'/icons/16x16/group_link.png" alt="'.__('UserGroupsReferentDes').'" style="margin-right:5px;"></span>';
			echo '</td>';
			
			echo '<td>';	
			echo $referent['User']['name'];
		
			if(!empty($referent['User']['email']))	
				echo ' <a class="fa fa-envelope-o fa-lg" title="'.__('Email send').'" target="_blank" href="mailto:'.$referent['User']['email'].'"></a>';
			echo '</td>';

			echo '<td>';
			if(!empty($referent['Profile']['address'])) echo $referent['Profile']['address'];
			echo '</td>';

			echo '<td>';
			if(!empty($referent['Profile']['phone'])) echo $referent['Profile']['phone'].'<br />';
			if(!empty($referent['Profile']['phone2'])) echo $referent['Profile']['phone2'];
			echo '</td>';
			
			/*
			 * userGroups
			 */
			echo '<td>';
			echo $userGroups[$referent['DesSuppliersReferent']['group_id']]['name'];
			// echo ' '.$userGroups[$referent['DesSuppliersReferent']['group_id']]['descri'];
			echo '</td>';
			 
			echo '</tr>';			
		} // loop Des Referenti	 
	 }
	 
	echo '</table>';
}
else
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "Non ci sono referenti associati"));	
echo '</div>';

echo '</div>'; // end class tab-content
echo '</fieldset>';

echo '<input type="hidden" name="data[Order][organization_id]" value="'.$this->Form->value('Order.organization_id').'" />'; // serve per ModelOrder::date_comparison_to_delivery
echo '<input type="hidden" name="data[Order][des_order_id]" value="'.$des_order_id.'" />';
if(!empty($des_order_id)) {
	echo '<input type="hidden" name="data[Order][qta_massima_um]" value="" />';
	echo '<input type="hidden" name="data[Order][qta_massima]" value="0" />';
	echo '<input type="hidden" name="data[Order][importo_massimo]" value="0" />';
	echo '<input type="hidden" name="data[Order][des_data_fine_max]" value="'.$desOrdersResults['DesOrder']['data_fine_max'].'" />';
}
else {
	/*
	 * setto una data futura cosi' non si blocca al controllo OrderModel::dateToDesDataFineMax
	 */
	echo '<input type="hidden" name="data[Order][des_data_fine_max]" value="2200-01-01" />';
}

echo $this->Form->end(__('Submit'));

echo $this->element('print_screen_order');
echo '</div>';

$options = [];
echo $this->MenuOrders->drawWrapper($this->Form->value('Order.id'), $options);
?>
<script type="text/javascript">
function suppliersOrganizationDetails(supplier_organization_id) {
	if(supplier_organization_id!=undefined && supplier_organization_id!=0 && supplier_organization_id!='') {
		var url = "/administrator/index.php?option=com_cake&controller=Ajax&action=suppliersOrganizationDetails&supplier_organization_id="+supplier_organization_id+"&des_order_id=9999&format=notmpl";
		var idDivTarget = 'suppliers_organization_details';
		ajaxCallBox(url, idDivTarget);		
	}
}
$(document).ready(function() {

	var battute = <?php echo Configure::read('OrderNotaMaxLen');?>;
	
	$("input[name='data[Order][nota]']").after("<p style='float:right' class='avviso'>Hai ancora <strong>"+ (battute - $("input[name='data[Order][nota]']").val().length)+"</strong> caratteri disponibili</p>");

	$("input[name='data[Order][nota]']").keyup(function() {
		if($(this).val().length > battute) {
			$(this).val($(this).val().substr(0, battute));
		}
		$(this).parent().find('p.avviso').html("Hai ancora <strong>"+ (battute - $(this).val().length)+"</strong> caratteri disponibili");
	});
	
	suppliersOrganizationDetails(<?php echo $this->Form->value('Order.supplier_organization_id');?>);
	
	$('#qta_massima').focusout(function() {validateNumberField(this,'quantita\' massima');});
	$('.double').focusout(function() {validateNumberField(this,'importo massimo');});
	
	$('#formGas').submit(function() {

		var typeDelivery = $("input[name='typeDelivery']:checked").val();
		if(typeDelivery=='delivery_old') {
			$('.nav-tabs a[href="#tabs-0"]').tab('show');
			alert("Se desideri associare l'ordine ad una consegna scaduta clicca sul link");
			$('#delivery_id').focus();
			$('#delivery_old_content').css('background-color','yellow');
			return false;		
		}
		
		if(typeDelivery==undefined || typeDelivery!='to_defined') {
			var delivery_id = $('#delivery_id').val();
			if(delivery_id=='' || delivery_id==undefined) {
				$('.nav-tabs a[href="#tabs-0"]').tab('show');
				alert("Devi scegliere la consegna da associare all'ordine");
				$('#delivery_id').focus();
				return false;
			}	    
		}

		var orderDataInizioDb = $('#OrderDataInizioDb').val();
		if(orderDataInizioDb=='' || orderDataInizioDb==undefined) {
			$('.nav-tabs a[href="#tabs-0"]').tab('show');
			alert("Devi indicare la data di apertura dell'ordine");
			return false;
		}	
		
		var OrderDataFineDb = $('#OrderDataFineDb').val();
		if(OrderDataFineDb=='' || OrderDataFineDb==undefined) {
			$('.nav-tabs a[href="#tabs-0"]').tab('show');
			alert("Devi indicare la data di chiusura dell'ordine");
			return false;
		}	
		
		return true;
	});
});
</script>