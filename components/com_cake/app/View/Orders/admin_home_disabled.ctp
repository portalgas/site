<?php
/*
 $results['Order']['state_code'] = 'PROCESSED-POST-DELIVERY';
 $results['Order']['state_code'] = 'OPEN';
 $results['Order']['state_code'] = 'PROCESSED-BEFORE-DELIVERY';
 */
if($this->request->data['Delivery']['sys']=='N')
	$label_crumb = __('Order home').': '.__('Supplier').' <b>'.$this->request->data['SuppliersOrganization']['name'].'</b> '.__('piecesToDelivery').' <b>'.$this->request->data['Delivery']['luogoData'].'</b>';
else
	$label_crumb = __('Order home').': '.__('Supplier').' <b>'.$this->request->data['SuppliersOrganization']['name'].'</b> '.__('piecesToDelivery').' <b>'.$this->request->data['Delivery']['luogo'].'</b>';

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb($label_crumb);
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

echo '<table cellpadding = "0" cellspacing = "0">';
echo '<tr>';
echo '	<th>'.$this->App->drawOrdersStateDiv($results).'&nbsp;'.__($results['Order']['state_code'].'-label').'</th>';
echo '</tr>';
echo '</table>';
		
if($results['Delivery']['isVisibleFrontEnd']=='N') {
	$title = "La consegna non &egrave; visibile dagli utenti nel front-end";
	$info = 'Per renderla visibile, vai in "'.__('Edit Delivery').'" e modifica lo STATO.';
	
	echo '<ul class="workflow"><li><span class="livAlert "><a class="actionDeleteWF">'.$title.'</a>';
	echo '<div class="helpTextWF"><img width="24" height="24" alt="Informazione per aiutarti" src="/images/cake/tooltips/24x24/help.png" />'.$info.'</div></span>';
	echo '</li></ul><br />';
}	

if($results['Order']['isVisibleFrontEnd']=='N') {
	$title = "L'ordine non e&grave; visibile dagli utenti nel front-end";
	$info = 'Per renderla visibile, vai in "'.__('Edit Delivery').'" e modifica lo STATO.';
	
	echo '<ul class="workflow"><li><span class="livAlert "><a class="actionDeleteWF">'.$title.'</a>';
	echo '<div class="helpTextWF"><img width="24" height="24" alt="Informazione per aiutarti" src="/images/cake/tooltips/24x24/help.png" />'.$info.'</div></span>';
	echo '</li></ul><br />';	
}

/*
 * trasversale
 */
$title = "Ordine creato il ".$this->Time->i18nFormat($results['Order']['created'],"%A %e %B %Y");
if($results['Order']['state_code']=='PROCESSED-BEFORE-DELIVERY' ||
	$results['Order']['state_code']=='PROCESSED-ON-DELIVERY' ||
	$results['Order']['state_code']=='PROCESSED-POST-DELIVERY')
	$title .= ", si e&grave; aperto ".$this->Time->i18nFormat($results['Order']['data_inizio'],"%A %e %B %Y");

echo $this->OrderHome->drawBoxTitle(0, $title, 0, array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflow'), array('href'=>'actionAddWF'));
/*
 * trasversale
*/

if($results['Order']['state_code']=='CREATE-INCOMPLETE') {
		     	
	echo $this->OrderHome->drawAction('EditOrder', $results, array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv'));
	echo $this->OrderHome->drawAction('DeleteOrder', $results);	
	echo $this->OrderHome->drawAction('AddArticlesOrderError', $results, array('ULopen'=>false,'ULclose'=>true), array('span'=>'alert'));
	echo $this->OrderHome->drawBoxTitleUlClose();
	/*	 * box - end	*/		
	$title = "L'ordine si dovrebbe aprire ".$this->Time->i18nFormat($results['Order']['data_inizio'],"%A %e %B %Y");	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 0, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'), array('href'=>'actionOpenWF'));}
else 
if($results['Order']['state_code']=='OPEN-NEXT') {

	echo $this->OrderHome->drawAction('EditOrder', $results, array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv'));
	if($this->App->isUserPermissionArticlesOrder($user))     	
		echo $this->OrderHome->drawAction('EditArticlesOrderShort', $results);
	echo $this->OrderHome->drawAction('DeleteOrder', $results, array('ULopen'=>false,'ULclose'=>true));
	echo $this->OrderHome->drawBoxTitleUlClose();
	/* 
	 * box - end 
	 */
	
	$title = "L'ordine si aprira&grave; ".$this->Time->i18nFormat($results['Order']['data_inizio'],"%A %e %B %Y");
	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 0, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'), array('href'=>'actionOpenWF'));
}
else
if($results['Order']['state_code']=='OPEN') {

	echo $this->OrderHome->drawAction('EditOrder', $results, array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv'));
	if($this->App->isUserPermissionArticlesOrder($user))     	
		echo $this->OrderHome->drawAction('EditArticlesOrderShort', $results);
	else
		echo $this->OrderHome->drawAction('ListArticles', $results);
	echo $this->OrderHome->drawAction('DeleteOrder', $results, array('ULopen'=>false,'ULclose'=>true));    							
	echo $this->OrderHome->drawBoxTitleUlClose();
	/*	 * box - end	*/
	/*	 * box - ini	*/
	$title = "L'ordine si e&grave; aperto ".$this->Time->i18nFormat($results['Order']['data_inizio'],"%A %e %B %Y").'&nbsp;';
	
	if($results['Order']['mail_open_data']!=Configure::read('DB.field.datetime.empty'))
		$title .= "(il ".$this->Time->i18nFormat($results['Order']['mail_open_data'],"%e %B")." e&grave; stata inviata la mail ai gasisti)&nbsp;";
				
	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 0, array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflow'), array('span'=>'statoCurrent','href'=>'actionOpenWF'));
	echo $this->OrderHome->drawAction('ManagementCartsOne', $results, array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv'));
	echo $this->OrderHome->drawAction('ExportDoc', $results, array('ULopen'=>false,'ULclose'=>true));
	echo $this->OrderHome->drawBoxTitleUlClose();
	/*	 * box - end	*/		
	$title = "L'ordine si chiudera&grave; ".$this->Time->i18nFormat($results['Order']['data_fine'],"%A %e %B %Y");
	if($results['Order']['dayDiffToDateFine']==0)  
		$title .= ' (oggi)';
	else if($results['Order']['dayDiffToDateFine']==-1)
		$title .= ' (domani)';
	else
		$title .= ' (tra '.(-1 * $results['Order']['dayDiffToDateFine']).'&nbsp;gg)';		
	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 1, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'), array('href'=>'actionCloseWF'));
}
else
if($results['Order']['state_code']=='RI-OPEN-VALIDATE') {

	echo $this->OrderHome->drawAction('EditOrder', $results, array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv'));
	if($this->App->isUserPermissionArticlesOrder($user))
		echo $this->OrderHome->drawAction('EditArticlesOrderShort', $results);
	else
		echo $this->OrderHome->drawAction('ListArticles', $results);
	echo $this->OrderHome->drawAction('DeleteOrder', $results, array('ULopen'=>false,'ULclose'=>true));
	echo $this->OrderHome->drawBoxTitleUlClose();
	/*
	 * box - end
	*/
	/*
	 * box - ini
	*/
	$title = "L'ordine si e&grave; aperto ".$this->Time->i18nFormat($results['Order']['data_inizio'],"%A %e %B %Y").'&nbsp;';

	if($results['Order']['mail_open_data']!=Configure::read('DB.field.datetime.empty'))
		$title .= "(il ".$this->Time->i18nFormat($results['Order']['mail_open_data'],"%e %B")." e&grave; stata inviata la mail ai gasisti)&nbsp;";

	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 0, array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflow'), array('span'=>'statoCurrent','href'=>'actionOpenWF'));
	echo $this->OrderHome->drawAction('ManagementCartsOne', $results, array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv'));
	if($user->organization['Organization']['hasValidate']=='Y' && $isToValidate) 
		echo $this->OrderHome->drawAction('ValidateOrder', $results, array('ULopen'=>false,'ULclose'=>false));
	echo $this->OrderHome->drawAction('ExportDoc', $results, array('ULopen'=>false,'ULclose'=>true));
	echo $this->OrderHome->drawBoxTitleUlClose();
	/*
	 * box - end
	*/

	
	$title = "L'ordine e&grave; stato riaperto fino a ".$this->Time->i18nFormat($results['Order']['data_fine_validation'],"%A %e %B %Y");
	if($results['Order']['dayDiffToDateFine']==0)
		$title .= ' (oggi)';
	else if($results['Order']['dayDiffToDateFine']==-1)
		$title .= ' (domani)';
	else
		$title .= ' (tra '.(-1 * $results['Order']['dayDiffToDateFine']).'&nbsp;gg)';
	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 1, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'), array('href'=>'actionCloseWF'));
}
else
if($results['Order']['state_code']=='PROCESSED-BEFORE-DELIVERY') {

	echo $this->OrderHome->drawAction('EditOrder', $results, array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv'));
	if($this->App->isUserPermissionArticlesOrder($user))     	
		echo $this->OrderHome->drawAction('EditArticlesOrderShort', $results);
	else
		echo $this->OrderHome->drawAction('ListArticles', $results);
	echo $this->OrderHome->drawAction('DeleteOrder', $results, array('ULopen'=>false,'ULclose'=>true));
	echo $this->OrderHome->drawBoxTitleUlClose();
	/*
	 * box - end
	*/
	
	$title = "L'ordine si e&grave; chiuso ".$this->Time->i18nFormat($results['Order']['data_fine'],"%A %e %B %Y");
   	if($results['Delivery']['daysToEndConsegna']>0)
   		$title .= ' - la consegna chiudera&grave; '.$this->Time->i18nFormat($results['Delivery']['data'],"%A %e %B");
   	else
   		if($results['Delivery']['daysToEndConsegna']==0)
   		$title .= ' - la consegna chiudera&grave; oggi!';
   	else
   		$title .= ' - la consegna si e&grave; chiusa '.$this->Time->i18nFormat($results['Delivery']['data'],"%A %e %B"); 		
	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 0,  array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflow'), array('span'=>'statoCurrent', 'href'=>'actionCloseWF'));
			
	/*
	 * II livello - ini
	*/
	$title = "Gestisci gli acquisti:";
	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 1,  array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv'));
	echo $this->OrderHome->drawAction('ManagementCartsOne', $results, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflowIILiv'));
	echo $this->OrderHome->drawBoxTitleUlClose();

	if($user->organization['Organization']['hasValidate']=='Y' && $isToValidate) {
		$title = "Se i tuoi articoli hanno i colli:";
 		echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 2,  array('ULopen'=>true,'ULclose'=>false));				
		echo $this->OrderHome->drawAction('ValidateOrder', $results, array('ULopen'=>true,'ULclose'=>true));				
		echo $this->OrderHome->drawBoxTitleUlClose();
	}

	$title = "Stampa l'ordine:";
   	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 3, array('ULopen'=>true,'ULclose'=>false));
	echo $this->OrderHome->drawAction('ExportDocToUsers', $results, array('ULopen'=>true,'ULclose'=>false));
	echo $this->OrderHome->drawAction('ExportDocToArticle', $results);
	echo $this->OrderHome->drawAction('ExportDocToArticlesDetails', $results, array('ULopen'=>false,'ULclose'=>true));	echo $this->OrderHome->drawBoxTitleUlClose();
	/*
	 * II livello - end	 */
	echo $this->OrderHome->drawBoxTitleUlClose();	/*	 * box - end	*/	
	
	if($results['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty')) {
		$title = "L'ordine e&grave; riaperto fino a ".$this->Time->i18nFormat($results['Order']['data_fine_validation'],"%A %e %B %Y")." per permettere ai gasiti di completare i colli";
		echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 0,  array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflow'), array('span'=>'statoCurrent', 'href'=>'actionCloseWF'));
	}
		
}
else
if($results['Order']['state_code']=='PROCESSED-POST-DELIVERY') { 	

	echo $this->OrderHome->drawAction('EditOrder', $results, array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv'));
	if($this->App->isUserPermissionArticlesOrder($user))     	
		echo $this->OrderHome->drawAction('EditArticlesOrderShort', $results);
	else
		echo $this->OrderHome->drawAction('ListArticles', $results);
	echo $this->OrderHome->drawAction('DeleteOrder', $results, array('ULopen'=>false,'ULclose'=>true));
	echo $this->OrderHome->drawBoxTitleUlClose();
	/*
	 * box - end
	*/
	
	$title = "L'ordine si e&grave; chiuso ".$this->Time->i18nFormat($results['Order']['data_fine'],"%A %e %B %Y");
   	if($results['Delivery']['daysToEndConsegna']>0)
   		$title .= ' - la consegna chiudera&grave; '.$this->Time->i18nFormat($results['Delivery']['data'],"%A %e %B");
   	else
   		if($results['Delivery']['daysToEndConsegna']==0)
   		$title .= ' - la consegna chiudera&grave; oggi!';
   	else
   		$title .= ' - la consegna si e&grave; chiusa '.$this->Time->i18nFormat($results['Delivery']['data'],"%A %e %B"); 		
	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 0,  array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflow'), array('span'=>'statoCurrent', 'href'=>'actionCloseWF'));
			
	/*	 * II livello - ini	*/
	$title = "Gestisci gli acquisti:";	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 1,  array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv'));
	
	if(empty($results['Order']['gestType']))
		echo $this->OrderHome->drawAction('ManagementCartsOne', $results, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflowIILiv'));
	else
	echo $this->OrderHome->drawAction('ManagementCartsOne', $results, array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv'));
	
	if($results['Order']['gestType']=='AGGREGATE') echo $this->OrderHome->drawAction('ManagementCartsGroupByUsers', $results);
	if($results['Order']['gestType']=='SPLIT') echo $this->OrderHome->drawAction('ManagementCartsSplit', $results, array('ULopen'=>false,'ULclose'=>true));
	echo $this->OrderHome->drawBoxTitleUlClose();

	if($user->organization['Organization']['hasTrasport']=='Y' && $results['Order']['hasTrasport']=='Y') {
		$title = "Se devi gestire il trasporto sugli articoli consegnati:";
  		echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 4,  array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv'));
		echo $this->OrderHome->drawAction('ManagementTrasport', $results, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflowIILiv tre'));		echo $this->OrderHome->drawBoxTitleUlClose();
	}
			
	$title = "Stampa l'ordine:";
  	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 5,  array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv'));
	echo $this->OrderHome->drawAction('ExportDocToUsers', $results, array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv tre'));
	echo $this->OrderHome->drawAction('ExportDocToArticle', $results);
	echo $this->OrderHome->drawAction('ExportDocToArticlesDetails', $results, $results, array('ULopen'=>false,'ULclose'=>true));	echo $this->OrderHome->drawBoxTitleUlClose();
	
				/*	 * II livello - end	*/
	echo $this->OrderHome->drawBoxTitleUlClose();
	
	if($isCartToStoreroom) {
		$title = __('CartsToStoreroom');
		echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 6,  array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'), array('href'=>'actionStoreroomWF'));
	}
	
	echo $this->OrderHome->drawBoxTitleUlClose();	/*	 * box - end	*/

	if($isReferenteTesoriere)		echo $this->OrderHome->drawAction('order_state_in_TO_PAYMENT', $results, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'),array('span'=>'blu'));	else		echo $this->OrderHome->drawAction('order_state_in_WAIT_PROCESSED_TESORIERE', $results, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'),array('span'=>'blu'));}
else
if($results['Order']['state_code']=='PROCESSED-ON-DELIVERY') {

	echo $this->OrderHome->drawAction('EditOrder', $results, array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv'));
	if($this->App->isUserPermissionArticlesOrder($user))
		echo $this->OrderHome->drawAction('EditArticlesOrderShort', $results);
	else
		echo $this->OrderHome->drawAction('ListArticles', $results);
	echo $this->OrderHome->drawAction('DeleteOrder', $results, array('ULopen'=>false,'ULclose'=>true));
	echo $this->OrderHome->drawBoxTitleUlClose();
	/*
	 * box - end
	*/

	$title = "L'ordine si e&grave; chiuso ".$this->Time->i18nFormat($results['Order']['data_fine'],"%A %e %B %Y");
	if($results['Delivery']['daysToEndConsegna']>0)
		$title .= ' - la consegna chiudera&grave; '.$this->Time->i18nFormat($results['Delivery']['data'],"%A %e %B");
	else
	if($results['Delivery']['daysToEndConsegna']==0)
		$title .= ' - la consegna chiudera&grave; oggi!';
	else
		$title .= ' - la consegna si e&grave; chiusa '.$this->Time->i18nFormat($results['Delivery']['data'],"%A %e %B");
	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 0,  array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflow'), array('span'=>'statoCurrent', 'href'=>'actionCloseWF'));
		
	/*
	 * II livello - ini
	*/
	$title = "Stampa l'ordine:";
	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 1,  array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv'));
	echo $this->OrderHome->drawAction('ExportDocToUsers', $results, array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv tre'));
	echo $this->OrderHome->drawAction('ExportDocToArticle', $results);
	echo $this->OrderHome->drawAction('ExportDocToArticlesDetails', $results, $results, array('ULopen'=>false,'ULclose'=>true));
	echo $this->OrderHome->drawBoxTitleUlClose();

		
	/*
	 * II livello - end
	*/
	echo $this->OrderHome->drawBoxTitleUlClose();



	echo $this->OrderHome->drawBoxTitleUlClose();
	/*
	 * box - end
	*/
/*
	if($isReferenteTesoriere)
		echo $this->OrderHome->drawAction('order_state_in_TO_PAYMENT', $results, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'),array('span'=>'blu'));
	else
		echo $this->OrderHome->drawAction('order_state_in_WAIT_PROCESSED_TESORIERE', $results, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'),array('span'=>'blu'));
	*/
	$title = "In carico al tesoriere dopo la consegna";
	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 2, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'), array('href'=>'actionPayWF','span'=>'blu'));
	
	
}
else
if($results['Order']['state_code']=='WAIT-PROCESSED-TESORIERE') {

	echo $this->OrderHome->drawBoxTitleUlClose();
	
	$title = "L'ordine si e&grave; aperto ".$this->Time->i18nFormat($results['Order']['data_inizio'],"%A %e %B %Y").'&nbsp;';
	
	if($results['Order']['mail_open_data']!=Configure::read('DB.field.datetime.empty'))
		$title .= "(il ".$this->Time->i18nFormat($results['Order']['mail_open_data'],"%e %B")." e&grave; stata inviata la mail ai gasisti)&nbsp;";
	
	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 0, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'), array('href'=>'actionOpenWF'));
	
	$title = "L'ordine si e&grave; chiuso ".$this->Time->i18nFormat($results['Order']['data_fine'],"%A %e %B %Y")." - la consegna si e&grave; chiusa ".$this->Time->i18nFormat($results['Delivery']['data'],"%A %e %B");
	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 1, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'), array('href'=>'actionCloseWF'));
	
	echo $this->OrderHome->drawAction('order_state_in_PROCESSED_POST_DELIVERY', $results, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'),array('span'=>'blu statoCurrent')); 
}
else
if($results['Order']['state_code']=='PROCESSED-TESORIERE') {

	echo $this->OrderHome->drawBoxTitleUlClose();		$title = "L'ordine si e&grave; aperto ".$this->Time->i18nFormat($results['Order']['data_inizio'],"%A %e %B %Y").'&nbsp;';
	
	if($results['Order']['mail_open_data']!=Configure::read('DB.field.datetime.empty'))
		$title .= "(il ".$this->Time->i18nFormat($results['Order']['mail_open_data'],"%e %B")." e&grave; stata inviata la mail ai gasisti)&nbsp;";
		echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 0, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'), array('href'=>'actionOpenWF'));		$title = "L'ordine si e&grave; chiuso ".$this->Time->i18nFormat($results['Order']['data_fine'],"%A %e %B %Y")." - la consegna si e&grave; chiusa ".$this->Time->i18nFormat($results['Delivery']['data'],"%A %e %B");	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 1, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'), array('href'=>'actionCloseWF'));
	$title = "Ordine in carico al tesoriere";
	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 2, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'), array('href'=>'actionCloseWF','span'=>'statoCurrent'));
}
else 
if($results['Order']['state_code']=='TO-PAYMENT') {

	echo $this->OrderHome->drawBoxTitleUlClose();
	
	$title = "L'ordine si e&grave; aperto ".$this->Time->i18nFormat($results['Order']['data_inizio'],"%A %e %B %Y").'&nbsp;';

	if($results['Order']['mail_open_data']!=Configure::read('DB.field.datetime.empty'))
		$title .= "(il ".$this->Time->i18nFormat($results['Order']['mail_open_data'],"%e %B")." e&grave; stata inviata la mail ai gasisti)&nbsp;";

	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 0, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'), array('href'=>'actionOpenWF'));
	
	$title = "L'ordine si e&grave; chiuso ".$this->Time->i18nFormat($results['Order']['data_fine'],"%A %e %B %Y")." - la consegna si e&grave; chiusa ".$this->Time->i18nFormat($results['Delivery']['data'],"%A %e %B");
	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 1, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'), array('href'=>'actionCloseWF'));
	
	echo $this->OrderHome->drawAction('EditRequestPaymentsOrder', $results, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'),array('span'=>'blu statoCurrent'));							
}
else
if($results['Order']['state_code']=='CLOSE') {

	echo $this->OrderHome->drawBoxTitleUlClose();
	
	$title = "L'ordine si e&grave; aperto ".$this->Time->i18nFormat($results['Order']['data_inizio'],"%A %e %B %Y").'&nbsp;';
	
	if($results['Order']['mail_open_data']!=Configure::read('DB.field.datetime.empty'))
		$title .= "(il ".$this->Time->i18nFormat($results['Order']['mail_open_data'],"%e %B")." e&grave; stata inviata la mail ai gasisti)&nbsp;";
	
	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 0, array('ULopen'=>true,'ULclose'=>true,'ULclass'=>'workflow'), array('href'=>'actionOpenWF'));
	
	$title = "L'ordine si e&grave; chiuso ".$this->Time->i18nFormat($results['Order']['data_fine'],"%A %e %B %Y")." - la consegna si e&grave; chiusa ".$this->Time->i18nFormat($results['Delivery']['data'],"%A %e %B");
	echo $this->OrderHome->drawBoxTitle($results['Order']['state_code'], $title, 1, array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflow'), array('span'=>'statoCurrent','href'=>'actionCloseWF'));
	echo $this->OrderHome->drawAction('ExportDoc', $results, array('ULopen'=>true,'ULclose'=>false,'ULclass'=>'workflowIILiv'));
	echo $this->OrderHome->drawAction('RequestPayment', $results, array('ULopen'=>false,'ULclose'=>true));		
	echo $this->OrderHome->drawBoxTitleUlClose();
}

echo '</div>';

$options = [];
echo $this->MenuOrders->drawWrapper($results['Order']['id'], $options);

if($results['Order']['state_code']!='PROCESSED-ON-DELIVERY') {
	echo '<div id="help">';
	echo '<div class="logo"></div>';
	echo '</div>';
}
?>

	

	
<script type="text/javascript">
$(document).ready(function() {
	$('.helpWF').each(function(){
		$(this).mouseenter(function(){
			$(this).children('div').css('display','block');
		});
		$(this).mouseleave(function(){
			$(this).children('div').css('display','none');
		});
	});
	
	$('.logo').click(function () {
		var url = '/administrator/index.php?option=com_cake&controller=Orders&action=home&order_id=<?php echo $results['Order']['id'];?>&popup=Y&format=notmpl';
		apriPopUpBootstrap(url, '');
	});	
});
</script>