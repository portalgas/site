<?php
echo $this->Html->script('genericBackOfficeGasDes.min');

/*
echo "<pre>";
print_r($results);
echo "</pre>";
*/
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('Des'),array('controller' => 'Des', 'action' => 'index'));
$this->Html->addCrumb(__('List DesOrders'),array('controller' => 'DesOrders', 'action' => 'index'));
$this->Html->addCrumb(__('List DesOrdersOrganizations'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

if($totaliDesOrdersOrganization==0 && !$isTitolareDesSupplier)
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Il primo ordine dev'essere creato dal titolare dell'ordine."));
	
			
echo '<div class="orders">';	
	
	if(!empty($results)) {

		if(!empty($results['DesOrder']['nota'])) {
			echo '<p style="padding-left: 45px;" ';
			echo 'class="nota_evidenza_'.strtolower($results['DesOrder']['nota_evidenza']).'"';
			echo '>';
			echo $results['DesOrder']['nota'];
			echo '</p>';
		}
		
		echo '<table cellpadding="0" cellspacing="0">';
		echo '<tr>';
		echo '<th colspan="2">'.__('N').'</th>';
		echo '<th colspan="2">'.__('Supplier').'</th>';
		echo '<th colspan="2">'.__('OwnOrganization').'</th>';
		echo '<th>'.__('DesDelivery').'</th>';
		echo '<th>'.__('Data fine max').'</th>';	
		echo '<th>'.__('Orders').'</th>';
		echo '<th colspan="2">'.__('stato_elaborazione').'</th>';		
		echo '</tr>';


		echo '<tr class="view-2">';
		echo '<td><a action="details_users-'.$results['DesSupplier']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
		echo '<td>'.($numResult+1).'</td>';
		
		echo '<td>';
		if(!empty($results['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$results['Supplier']['img1']))
			echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$results['Supplier']['img1'].'" />';	
		echo '</td>';			
		echo '<td>'.$results['Supplier']['name'];
		if(!empty($results['Supplier']['descrizione']))
			echo ' - '.$results['Supplier']['descrizione'];
		echo '</td>';

		if(empty($results['OwnOrganization']['id'])) {
			$msg = "Il produttore non ha un GAS titolare associato";
			echo '<td colspan="2">';
			echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => $msg));
			echo '</td>';
		}
		else {
			echo '<td>';
			echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$results['OwnOrganization']['img1'].'" alt="'.$results['OwnOrganization']['name'].'" />';
			echo '</td>';
			echo '<td>'.$results['OwnOrganization']['name'].'</td>';
		}
		
		echo '<td>';
		echo $results['DesOrder']['luogo'];
		echo '</td>';	

		echo '<td>';
		echo $this->Time->i18nFormat($results['DesOrder']['data_fine_max'],"%A %e %B %Y");
		echo '</td>';

		echo '<td>';
		echo count($results['DesOrdersOrganizations']);
		echo '</td>';
		
		echo '<td colspan="2">';
		echo $this->App->drawDesOrdersStateDiv($results);
		echo '&nbsp;';
		echo __($results['DesOrder']['state_code'].'-label');
		echo '</td>';
		
	echo '</tr>';

	/*
	 * dettaglio, elenco referenti di tutti i GAS
	 */
	echo '<tr class="trView" id="trViewId-'.$results['DesSupplier']['id'].'">';
	echo '	<td colspan="2"></td>';
	echo '	<td colspan="9" id="tdViewId-'.$results['DesSupplier']['id'].'"></td>';
	echo '</tr>';	 


	if(!empty($results['DesOrdersOrganizations'])) {

		echo '<tr>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td colspan="8">';

		echo '<table cellpadding="0" cellspacing="0">';
		echo '<tr>';
		echo '<th>'.__('N').'</th>';
		echo '<th colspan="3">'.__('Organization').'</th>';
		echo '<th>'.__('Data inizio').'</th>';
		echo '<th>'.__('Data fine').'</th>';
		echo '<th>'.__('Aperto/Chiuso').'</th>';
		echo '<th>'.__('stato_elaborazione').'</th>';
		echo '<th class="actions">'.__('Actions').'</th>';	
		echo '</tr>';

		foreach ($results['DesOrdersOrganizations'] as $numResult2 => $resultDesOrdersOrganization) {
			/*
			echo "<pre>";
			print_r($resultDesOrdersOrganization);
			echo "</pre>";
			*/
			echo '<tr class="view-2">';
			echo '<td>'.($numResult2+1).'</td>';
			
			echo '<td>';
			echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$resultDesOrdersOrganization['Organization']['img1'].'" alt="'.$resultDesOrdersOrganization['Organization']['name'].'" />';
			echo '</td>';
			echo '<td colspan="2">'.$resultDesOrdersOrganization['Organization']['name'].'</td>';
			echo "<pre>";
		
			if(!empty($resultDesOrdersOrganization['Order']['id'] )) {
				echo '	<td style="white-space:nowrap;">';
				echo $this->Time->i18nFormat($resultDesOrdersOrganization['Order']['data_inizio'],"%A %e %B %Y").'<br />';
				echo '	</td>';
				
				echo '	<td style="white-space:nowrap;">';
				echo $this->Time->i18nFormat($resultDesOrdersOrganization['Order']['data_fine'],"%A %e %B %Y");
				if($resultDesOrdersOrganization['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty'))
					echo '<br />Riaperto fino a '.$this->Time->i18nFormat($resultDesOrdersOrganization['Order']['data_fine_validation'],"%A %e %B %Y");
				echo '	</td>';
				
				echo '	<td style="white-space:nowrap;">';
				echo $this->App->utilsCommons->getOrderTime($resultDesOrdersOrganization['Order']);
				echo '	</td>';

				echo '<td>';
				echo $this->App->drawOrdersStateDiv($resultDesOrdersOrganization);
				echo '&nbsp;';
				echo __($resultDesOrdersOrganization['Order']['state_code'].'-label');
				echo '</td>';				
			}
			else {
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
			}
			echo '<td class="actions-table-img-3">';

			/*
			 * referentDesAllGas per il produttore: potra' visualizzare gli ordini dei GAS
			 */
			if($isReferentDesAllGasDesSupplier) {
				echo $this->Html->link(null, array('controller' => 'DesOrders', 'action' => 'prepare_print_all_gas', null, 'des_order_id='.$resultDesOrdersOrganization['DesOrdersOrganization']['des_order_id'].'&organization_id='.$resultDesOrdersOrganization['DesOrdersOrganization']['organization_id']), array('class' => 'action actionPrinter','title' => __('PrintAllGas')));
			}
			
			if($resultDesOrdersOrganization['Organization']['id']==$user->organization['Organization']['id']) {
				echo '<a id="actionMenu-'.$resultDesOrdersOrganization['DesOrdersOrganization']['des_order_id'].'" class="action actionMenu" title="'.__('Expand menu').'"></a>';
				echo '<div class="menuDetails" id="menuDetails-'.$resultDesOrdersOrganization['DesOrdersOrganization']['des_order_id'].'" style="display:none;">';
				echo '	<a class="menuDetailsClose" id="menuDetailsClose-'.$resultDesOrdersOrganization['DesOrdersOrganization']['des_order_id'].'"></a>';
				echo '<div id="des-order-sotto-menu-'.$resultDesOrdersOrganization['DesOrdersOrganization']['des_order_id'].'"></div>';
				echo '</div>';
			}
			
			echo '</td>';
			echo '</tr>';
			
		} // loop DesOrdersOrganizations
		echo '</table>';
			
		echo '</td>';
		echo '</tr>';

	} // end desOrder gia' creati

	
	/*
	 * desOrder da creare creati
	 */
	if(!empty($desOrganizationsResults)) {
		echo '<tr>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td colspan="9">';
				
		echo '<table>';
		echo '<tr>';
		echo '<th>'.__('N').'</th>';
		echo '<th colspan="3">'.__('Organization').'</th>';
		echo '<th>'.__('Actions').'</th>';	
		echo '</tr>';		
		foreach ($desOrganizationsResults as $numResult3 => $desOrganizationsResult) {
			
			echo '<tr class="view-2">';
			echo '<td style="width:15px;">'.($numResult3+1).'</td>';
			echo '<td style="width:50px;">';
			echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$desOrganizationsResult['Organization']['img1'].'" alt="'.$desOrganizationsResult['Organization']['name'].'" />';
			echo '</td>';
			echo '<td style="width:200px;">'.$desOrganizationsResult['Organization']['name'].'</td>';
			echo '<td></td>';
			echo '<td style="width:75px;">';
			
			if($desOrganizationsResult['Organization']['id']==$user->organization['Organization']['id'] &&
			  (($totaliDesOrdersOrganization==0 && $isTitolareDesSupplier) || ($totaliDesOrdersOrganization>0)) // se non sono stati creati ordini, il primo puo' farlo solo il titolare 
			 ) 	
				echo $this->Html->link(null, array('controller' => 'DesOrders', 'action' => 'prepare_order_add', $results['DesOrder']['id']), array('class' => 'action actionAdd','title' => __('Add DesOrder')));
						
			echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
			
		echo '</td>';
		echo '</tr>';
	} // end esOrder da creare creati

	echo '</table>';


	

} 
else  
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => "Non ci sono ancora ordini registrati"));
	
	
/*
 * legenda profilata
*/
echo $this->App->drawLegenda($user, $desOrderStatesToLegenda);

	
echo '</div>';
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(".actionMenu").each(function () {
		jQuery(this).click(function() {

			jQuery('.menuDetails').css('display','none');
			
			var idRow = jQuery(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			jQuery('#menuDetails-'+numRow).show();

			viewDesOrderSottoMenu(numRow,"bgLeft");

			var offset = jQuery(this).offset();
			var newTop = (offset.top - 100);
			var newLeft = (offset.left - 350);

			jQuery('#menuDetails-'+numRow).offset({ top: newTop, left: newLeft});			
		});
	});	

	jQuery(".menuDetailsClose").each(function () {
		jQuery(this).click(function() {
			var idRow = jQuery(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			jQuery('#menuDetails-'+numRow).hide('slow');
		});
	});		
});
</script>