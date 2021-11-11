<?php
$debug=false;
$this->App->d($results);
$this->App->dd($acl_owner_articles);

echo $this->Html->script('genericBackOfficeGasDes.min');


$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
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
		
		echo '<div class="table-responsive"><table class="table">';
		echo '<tr>';
		echo '<th colspan="2">'.__('N').'</th>';
		echo '<th colspan="2">'.__('Supplier').'</th>';
		echo '<th colspan="2">'.__('OwnOrganization').'</th>';
		echo '<th>'.__('DesDelivery').'</th>';
		echo '<th>'.__('DataFineMax').'</th>';	
		echo '<th>'.__('Orders').'</th>';
		echo '<th colspan="2">'.__('StatoElaborazione').'</th>';		
		echo '</tr>';


		echo '<tr class="view-2">';
		echo '<td><a action="details_users-'.$results['DesSupplier']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
		echo '<td>'.((int)$numResult+1).'</td>';
		
		echo '<td>';
		if(!empty($results['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$results['Supplier']['img1']))
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$results['Supplier']['img1'].'" />';	
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

		echo '<tr style="border:0px;">';
		echo '<td style="border:0px;"></td>';
		echo '<td style="border:0px;"></td>';
		echo '<td style="border:0px;" colspan="10"><h3 style="padding:5px;" class="p-3 mb-2 bg-info text-white">'.__('List DesOrders').'</h3></td>';
		echo '</tr>';
		
		echo '<tr style="border:0px;">';
		echo '<td style="border:0px;"></td>';
		echo '<td style="border:0px;"></td>';
		echo '<td style="border:0px;" colspan="8">';

		echo '<div class="table-responsive"><table class="table table-hover">';
		echo '<tr>';
		echo '<th>'.__('N').'</th>';
		echo '<th colspan="3">'.__('GasOrganization').'</th>';
		echo '<th>'.__('DataInizio').'</th>';
		echo '<th>'.__('DataFine').'</th>';
		echo '<th>'.__('OpenClose').'</th>';
		echo '<th>'.__('StatoElaborazione').'</th>';
		echo '<th class="actions">'.__('Actions').'</th>';	
		echo '</tr>';

		foreach ($results['DesOrdersOrganizations'] as $numResult2 => $resultDesOrdersOrganization) {
			
			// $this->App->d($resultDesOrdersOrganization, $debug);
			
			echo '<tr class="view-2">';
			echo '<td>'.($numResult2+1).'</td>';
			
			echo '<td>';
			echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$resultDesOrdersOrganization['Organization']['img1'].'" alt="'.$resultDesOrdersOrganization['Organization']['name'].'" />';
			echo '</td>';
			echo '<td colspan="2">'.$resultDesOrdersOrganization['Organization']['name'].'</td>';
			
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
		echo '</table></div>';
			
		echo '</td>';
		echo '</tr>';

	} // end desOrder gia' creati

	
	/*
	 * desOrder da creare creati
	 */
	if(!empty($desOrganizationsResults)) {
		echo '<tr style="border:0px;">';
		echo '<td style="border:0px;"></td>';
		echo '<td style="border:0px;"></td>';
		echo '<td style="border:0px;" colspan="9">';
				
		echo '<div class="table-responsive"><table class="table table-hover">';
		echo '<tr>';
		echo '<th>'.__('N').'</th>';
		echo '<th colspan="2">'.__('GasOrganization').'</th>';
		echo '<th>'.__('Actions').'</th>';	
		echo '</tr>';		
		foreach ($desOrganizationsResults as $numResult3 => $desOrganizationsResult) {
			
			echo '<tr class="view-2">';
			echo '<td style="width:15px;">'.($numResult3+1).'</td>';
			echo '<td style="width:50px;">';
			echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$desOrganizationsResult['Organization']['img1'].'" alt="'.$desOrganizationsResult['Organization']['name'].'" />';
			echo '</td>';
			echo '<td style="width:75%;">'.$desOrganizationsResult['Organization']['name'].'</td>';
			echo '<td>';
		
			/*
			 * actions
			 */
			
			$this->App->d('totaliDesOrdersOrganization '.$totaliDesOrdersOrganization, $debug); 
			$this->App->d('DesOrder.state_code '.$results['DesOrder']['state_code'], $debug); 
			$this->App->d($acl_owner_articles, $debug);
			if($isTitolareDesSupplier)
				$this->App->d('isTitolareDesSupplier Y', $debug);
			else
				$this->App->d('isTitolareDesSupplier N', $debug);


			if($desOrganizationsResult['Organization']['id']==$user->organization['Organization']['id']) {
				if($acl_owner_articles!==true) {
					switch ($acl_owner_articles['owner_articles']) {
						case 'REFERENT':
							if($results['DesOrder']['state_code']=='OPEN') {
								$label = __('DesOrderOrganizationSupplierOwnerArticlesError');
								echo $this->Html->link('<span class="btn btn-danger">'.$label.'</span>', ['controller' => 'SuppliersOrganizations', 'action' => 'edit', $acl_owner_articles['supplier_organization_id']], ['escape' => false, 'title' => __($label)]);
								echo $this->element('boxMsg', ['class_msg' => 'danger', 'msg' => __('DesOrderOrganizationSupplierOwnerArticlesMsg')]);
							}
						break;
						case 'GAS-TITOLARE':
							if($results['DesOrder']['state_code']=='OPEN') {
								echo '<span class="label label-info">'.__('DesOrderOrganizationGasTitolareOwnerArticlesError').'</span>';
							}
						break;						
						case '':
							// Il G.A.S. non ha il produttore associato
							echo '<span class="label label-info">'.__('DesOrderOrganizationNotSupplier').'</span>';
						break;
						case 'DES':
						case 'SUPPLIER':
							// e' corretto ma se lo user non e' titolare non puo' far nulla
							echo '<span class="label label-info">'.__('DesOrderOrganizationNotIsTitolareDesSupplier').'</span>';
						break;
					}
				}
				else
				if(($totaliDesOrdersOrganization==0 && $isTitolareDesSupplier) || ($totaliDesOrdersOrganization>0)) { // se non sono stati creati ordini, il primo puo' farlo solo il titolare 	
					if($results['DesOrder']['state_code']=='OPEN')
						echo $this->Html->link(null, ['controller' => 'DesOrders', 'action' => 'prepare_order_add', $results['DesOrder']['id']], ['class' => 'action actionAdd','title' => __('Add DesOrder')]);
					else
				 		echo '<span class="label label-warning">'.__('DesOrderOrganizationNotMyOrderCreateClose').'</span>';						
				}
			}
			else {
			    if($results['DesOrder']['state_code']=='OPEN')
				 	echo '<span class="label label-warning">'.__('DesOrderOrganizationNotOrderCreate').'</span>';
				else
				 	echo '<span class="label label-warning">'.__('DesOrderOrganizationNotOrderCreateClose').'</span>';
			} 				
			echo '</td>';
			echo '</tr>';
		}
		echo '</table></div>';
			
		echo '</td>';
		echo '</tr>';
	} // end desOrder da creare creati

	echo '</table></div>';


	

} 
else  
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono ancora ordini registrati"));
	
	
/*
 * legenda profilata
*/
echo $this->App->drawLegenda($user, $desOrderStatesToLegenda);

	
echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {
	$(".actionMenu").each(function () {
		$(this).click(function() {

			$('.menuDetails').css('display','none');
			
			var idRow = $(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			$('#menuDetails-'+numRow).show();

			viewDesOrderSottoMenu(numRow,"bgLeft");

			var offset = $(this).offset();
			var newTop = (offset.top - 100);
			var newLeft = (offset.left - 350);

			$('#menuDetails-'+numRow).offset({ top: newTop, left: newLeft});			
		});
	});	

	$(".menuDetailsClose").each(function () {
		$(this).click(function() {
			var idRow = $(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			$('#menuDetails-'+numRow).hide('slow');
		});
	});		
});
</script>