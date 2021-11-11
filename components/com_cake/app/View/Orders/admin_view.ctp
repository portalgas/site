<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$this->Form->value('Order.id')));
$this->Html->addCrumb(__('View Order'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

echo '<div class="table-responsive"><table class="table">';
echo '<tr>';
echo '	<th>'.$this->App->drawOrdersStateDiv($this->request->data).'&nbsp;'.__($this->request->data['Order']['state_code'].'-label').'</th>';
echo '</tr>';
echo '</table></div>';

if(!empty($des_order_id))
	echo $this->element('boxDesOrder', array('results' => $desOrdersResults));

echo $this->Form->create('Order',array('id' => 'formGas'));
echo '<fieldset>';
echo '<legend>'.__('View Order').'</legend>';

echo '<div class="tabs">';
echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
echo '<li class="active"><a href="#tabs-0" data-toggle="tab">'.__('Dati ordine').'</a></li>';
echo '<li><a href="#tabs-1" data-toggle="tab">'.__('Note Referente').'</a></li>';
echo '<li><a href="#tabs-2" data-toggle="tab">'.__('Per gli utenti').'</a></li>';
echo '<li><a href="#tabs-3" data-toggle="tab">'.__('Durante l\'ordine').'</a></li>';
if($user->organization['Template']['payToDelivery']=='ON' || $user->organization['Template']['payToDelivery']=='ON-POST')
	echo '<li><a href="#tabs-4" data-toggle="tab">'.__('Dopo l\'arrivo della merce').'</a></li>';
else
if($user->organization['Template']['payToDelivery']=='POST')
	echo '<li><a href="#tabs-4" data-toggle="tab">'.__('Dopo la consegna').'</a></li>';
echo '<li><a href="#tabs-5" data-toggle="tab">'.__('Suppliers Organizations Referents').'</a></li>';
if($user->organization['Organization']['hasUserGroupsCassiere']=='Y' || $user->organization['Organization']['hasUserGroupsTesoriere']=='Y')
	echo '<li><a href="#tabs-6" data-toggle="tab">'.__('Fattura').'</a></li>';
echo '</ul>';

echo '<div class="tab-content">';
echo '<div class="tab-pane fade active in" id="tabs-0">';
             
		echo $this->Form->input('id');
		
		echo '<div class="input text ">';
		echo '<label for="OrderSuppliersOrganizationId">'.__('SuppliersOrganization').'</label> ';
		echo $this->Form->value('SuppliersOrganization.name');
		echo '<div style="float:right;" id="suppliers_organization_details"></div>';
		echo '</div>';
		echo '<input type="hidden" name="data[Order][supplier_organization_id]" value="'.$this->Form->value('Order.supplier_organization_id').'" />';				

		echo '<div class="input text ">';
		echo '<label>'.__('Delivery').'</label> ';
		if($this->request->data['Delivery']['sys']=='N')
			echo $this->Form->value('Delivery.luogoData');
		else 
			echo $this->Form->value('Delivery.luogo');
		echo '</div>';
		
		echo '<div class="input text ">';
		echo '<label>'.__('DataInizio').'</label> ';
		echo $this->Time->i18nFormat($this->Form->value('Order.data_inizio'),"%A, %e %B %Y");
		echo '</div>';
		
		if($this->request->data['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty')) {
			echo '<div class="input text ">';
			echo '<label>Riaperto l\'ordine fino a</label> ';
			echo $this->Time->i18nFormat($this->Form->value('Order.data_fine_validation'),"%A, %e %B %Y");
			echo '</div>';				
		}
		else {
			echo '<div class="input text ">';
			echo '<label>'.__('DataFine').'</label> ';
			echo $this->Time->i18nFormat($this->Form->value('Order.data_fine'),"%A, %e %B %Y");
			echo '</div>';
		}

		if($this->request->data['Order']['data_incoming_order']!=Configure::read('DB.field.date.empty')) {
			echo '<div class="input text ">';
			echo '<label>'.__('DataIncomingOrder').'</label> ';
			echo $this->Time->i18nFormat($this->Form->value('Order.data_incoming_order'),"%A, %e %B %Y");
			echo '</div>';				
		}
		
		if(!empty($this->request->data['Order']['nota'])) {
			echo '<div class="input text ">';
			echo '<label>'.__('Nota');
			echo '<br /><br /><img width="150" class="print_screen" id="print_screen_order_nota" src="'.Configure::read('App.img.cake').'/print_screen_order_nota.jpg" title="" border="0" />';
			echo '</label> ';
			echo $this->Form->value('Order.nota');
			echo '</div>';
		}
		
		echo $this->Html->div('clearfix','');

		if($user->organization['Organization']['hasVisibility']=='Y') {
			echo $this->Html->div('clearfix','');
			echo $this->App->drawFormRadio('Order','isVisibleFrontEnd',array('options' => $isVisibleFrontEnd, 'value'=>$this->Form->value('Order.isVisibleFrontEnd'), 'label'=>__('isVisibleFrontEnd'), ''=>'false',
					'after'=>$this->App->drawTooltip(null,__('toolTipIsVisibleFrontEnd'),$type='HELP')));
		
			echo $this->App->drawFormRadio('Order','isVisibleBackOffice',array('options' => $isVisibleBackOffice, 'value'=>$this->Form->value('Order.isVisibleBackOffice'), 'label'=>__('isVisibleBackOffice'), ''=>'false',
					'after'=>$this->App->drawTooltip(null,__('toolTipIsVisibleBackOffice'),$type='HELP')));
		}
echo '</div>';

echo '<div class="tab-pane fade" id="tabs-1">';	

	/*
	 * mail_open_testo
	*/
		if($this->Form->value('Order.state_code') == 'CREATE-INCOMPLETE' || $this->Form->value('Order.state_code') == 'OPEN-NEXT' || $this->Form->value('Order.state_code') == 'OPEN')
		echo $this->Form->input('mail_open_testo', array('class' => 'noeditor', 'disabled' => 'disabled', 'after' => '<img width="100" class="print_screen" id="print_screen_mail_open_testo" src="'.Configure::read('App.img.cake').'/print_screen_mail_open_testo.jpg" title="" border="0" />'));
	else
		echo $this->Form->input('mail_open_testo', array('class' => 'noeditor', 'disabled' => 'disabled', 'cols' => '70', 'after' => '<img width="100" class="print_screen" id="print_screen_mail_open_testo" src="'.Configure::read('App.img.cake').'/print_screen_mail_open_testo.jpg" title="" border="0" />'));
	
	/*
	 * legenda
	*/
	if(($this->Form->value('Order.state_code') == 'CREATE-INCOMPLETE' || $this->Form->value('Order.state_code') == 'OPEN-NEXT' || $this->Form->value('Order.state_code') == 'OPEN') 
	    && $this->Form->value('Order.mail_open_data')==Configure::read('DB.field.datetime.empty') || $this->Form->value('Order.mail_open_data')=='')
		echo $this->element('legendaOrdersSendMail', array('modalita' => 'EDIT'));
	else
	if($this->Form->value('Order.mail_open_data')!=Configure::read('DB.field.datetime.empty') || $this->Form->value('Order.mail_close_data')!=Configure::read('DB.field.datetime.empty'))
		echo $this->element('legendaOrdersJustSendMail',array('mail_open_data' => $this->Form->value('Order.mail_open_data'), 'mail_close_data' => $this->Form->value('Order.mail_close_data')));

echo '</div>';


echo '<div class="tab-pane fade" id="tabs-2">';
echo $this->element('boxOrdersTypeDraw', array('modalita' => 'VIEW', 'value' => $this->Form->value('Order.type_draw')));
echo $this->Html->div('clearfix','');
echo '</div>';



echo '<div class="tab-pane fade" id="tabs-3">';

echo '<div class="input text ">';
echo '<label>'.__('order_qta_massima').'</label> ';
if($this->request->data['Order']['qta_massima'] == 0)
	echo 'Nessun limite';
else {
	echo $this->Form->value('Order.qta_massima');
	echo ' '.$this->Form->value('Order.qta_massima_um');
}
echo '</div>';

echo '<div class="input text ">';
echo '<label>'.__('order_importo_massimo').'</label> ';
if($this->request->data['Order']['importo_massimo'] == 0)
	echo 'Nessun limite';
else
	echo $this->Form->value('Order.importo_massimo').'&nbsp;&euro;';
echo '</div>';

echo '</div>';


echo '<div class="tab-pane fade" id="tabs-4">';

echo $this->element('boxOrdersTypeGest', array('modalita' => 'VIEW', 'value' => $this->Form->value('Order.typeGest')));
		
echo $this->Html->div('clearfix','');

if($user->organization['Organization']['hasTrasport']=='Y') {
	echo '<div class="input text ">';
	echo '<div class="action actionTrasport"></div><label>'.__('HasTrasport').'</label> ';
	echo $this->App->traslateEnum($this->Form->value('Order.hasTrasport'));

	if($this->request->data['Order']['trasport']!='0.00')
		echo ' ('.$this->request->data['Order']['trasport_e'].')';
	echo '</div>';
}
if($user->organization['Organization']['hasCostMore']=='Y') {
	echo '<div class="input text ">';
	echo '<div class="action actionCostMore"></div><label>'.__('HasCostMore').'</label> ';
	echo $this->App->traslateEnum($this->Form->value('Order.hasCostMore'));
	
	if($this->request->data['Order']['cost_more']!='0.00')
		echo ' ('.$this->request->data['Order']['cost_more_e'].')';
	echo '</div>';
}
if($user->organization['Organization']['hasCostLess']=='Y') {
	echo '<div class="input text ">';
	echo '<div class="action actionCostLess"></div><label>'.__('HasCostLess').'</label> ';
	echo $this->App->traslateEnum($this->Form->value('Order.hasCostLess'));

	if($this->request->data['Order']['cost_less']!='0.00')
		echo ' ('.$this->request->data['Order']['cost_less_e'].')';
	echo '</div>';
}

echo '</div>';


echo '<div class="tab-pane fade" id="tabs-5">';
if(!empty($suppliersOrganizationsReferent)) {
	echo '<div class="table-responsive"><table class="table table-hover table-striped">';
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
		 * UserGroups
		 */
		echo '<td>';
		echo $userGroups[$referent['SuppliersOrganizationsReferent']['group_id']]['name'];
		// echo ' '.$userGroups[$referent['SuppliersOrganizationsReferent']['UserGroup']]['descri'];
		echo '</td>';
		
		echo '</tr>';			
	}
	echo '</table></div>';
}
else
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "Non ci sono referenti associati"));	
echo '</div>';

if($user->organization['Organization']['hasUserGroupsCassiere']=='Y' || $user->organization['Organization']['hasUserGroupsTesoriere']=='Y') {

		echo '<div class="tab-pane fade" id="tabs-6">';
	
		/*
		 * ora la fattura e' per tutti: cassiere e tesoriere 
		 * if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') {
		 */
			echo '<div class="input text">';
			echo '<label>'.__('Fattura').'</label> ';
		
			if(!empty($this->request->data['Order']['tesoriere_doc1']) && file_exists(Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$user->organization['Organization']['id'].DS.$this->request->data['Order']['tesoriere_doc1'])) {
						$ico = $this->App->drawDocumentIco($this->request->data['Order']['tesoriere_doc1']);
						echo '<a alt="Scarica il documento" title="Scarica il documento" href="'.Configure::read('App.server').Configure::read('App.web.doc.upload.tesoriere').'/'.$user->organization['Organization']['id'].'/'.$this->request->data['Order']['tesoriere_doc1'].'" target="_blank"><img src="'.$ico.'" /></a>';
					}
					else
						echo "";
			echo '</div>';
		
			
			if($this->request->data['Order']['tesoriere_fattura_importo']>0) {
				echo '<div class="input text">';
				echo '<label>'.__('Tesoriere fattura importo').'</label> ';
				echo number_format($this->request->data['Order']['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
				echo '</div>';			
			}
		
		
			if($this->request->data['Order']['tot_importo']>0) {
				echo '<div class="input text">';
				echo '<label>'.__('Importo totale ordine').'</label> ';
				echo number_format($this->request->data['Order']['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
				echo '</div>';			
			}
		
			if($this->request->data['Order']['tesoriere_fattura_importo']>0 && $this->request->data['Order']['tot_importo']>0) {
		
				$differenza = ($this->request->data['Order']['tot_importo'] - $this->request->data['Order']['tesoriere_fattura_importo']);
				
				echo '<div class="input text">';
				echo '<label>'.__('Delta').'</label> ';
				echo '<span style="padding:3px;';
				if($differenza==0) 
					echo 'background-color:#fff;color: #000;';
				else
				if($differenza>0) 
					echo 'background-color:#006600;color:#fff;';
				else
					echo 'background-color:red;color:#fff;';
				echo '">';
				echo number_format($differenza,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
				echo '</span>';
				echo '</div>';			
		
			}
		
			if(!empty($this->request->data['Order']['tesoriere_nota'])) {
				echo '<div class="input text">';
				echo '<label>'.__('Nota del referente').'</label> ';
				echo $this->request->data['Order']['tesoriere_nota']; 
				echo '</div>';			
			}
		// } // end if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST')
				
		/*
		 * pagamento ai produttori
		 */
		if($user->organization['Template']['orderSupplierPaid']=='Y') { 
			if($this->request->data['Order']['tesoriere_stato_pay']=='Y') {
				echo '<div class="input text">';
				echo '<label>Pagamento al produttore</label> ';
				echo '<span style="padding:3px;color: #000;background-color:green;">';
				echo "effettuato il pagamento";
				echo '</span>';
				
				if($this->request->data['Order']['tesoriere_data_pay']!=Configure::read('DB.field.date.empty')) 
					echo ' il '.$this->Time->i18nFormat($this->Form->value('Order.tesoriere_data_pay'),"%A, %e %B %Y");
				if($this->request->data['Order']['tesoriere_importo_pay']!='0.00')
					echo ' dell\'importo '.number_format($this->request->data['Order']['tesoriere_importo_pay'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';	
				
				echo '</div>';		
			}
			else {
				echo '<div class="input text">';
				echo '<label>Pagamento al produttore</label> ';
				echo '<span style="padding:3px;color: #000;background-color:red;">';
				echo "non ancora effettuato il pagamento";
				echo '</span>';
				echo '</div>';		
			}
		} // end if($user->organization['Template']['orderSupplierPaid']=='Y') 
	
	echo '</div>';
} // end if($user->organization['Organization']['hasUserGroupsCassiere']=='Y' || $user->organization['Organization']['hasUserGroupsTesoriere']=='Y') 


echo '</div>'; // end class tab-content
echo '</div>'; // tabs

echo '</fieldset>';
echo $this->Form->end();


echo $this->element('print_screen_order');
echo '</div>'; // end contentMenuLaterale

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
	suppliersOrganizationDetails(<?php echo $this->Form->value('Order.supplier_organization_id');?>);	
});
</script>