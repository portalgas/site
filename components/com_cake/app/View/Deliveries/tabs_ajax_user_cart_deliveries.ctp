<?php
$this->App->d($resultsWithModifies, false);
/*
 * ctrl se ho un occorrenza
 */
$tot=0;
if(isset($results['Tab']))
foreach($results['Tab'] as $numTabs => $tab) 
	foreach($tab['Delivery'] as $numDelivery => $delivery) 
		if(($delivery['totOrders']>0 && $delivery['totArticlesOrder']>0) ||
			(isset($storeroomResults['Tab'][$numTabs]['Delivery'][$numDelivery]['totStorerooms']) && $storeroomResults['Tab'][$numTabs]['Delivery'][$numDelivery]['totStorerooms']>0)) 
				$tot++;

if($tot==0)
	echo $this->element('boxMsgFrontEnd',array('class_msg' => 'notice', 'msg' => "Non sono stati ancora effettuati acquisti"));
else {	


	/*
	 * D I S P E N S A
	 */
	 $tot_importo_storeroom = 0;
	 $tot_qta_storeroom = 0;		 
	if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') { 
		$i=0;
		if(isset($storeroomResults['Tab'][$numTabs]['Delivery'][$numDelivery])) {
				
			$storeroomDelivery = $storeroomResults['Tab'][$numTabs]['Delivery'][$numDelivery];
		
				if($storeroomDelivery['totStorerooms']) {
					echo '<h2>Dispensa</h2>';
					echo $this->Tabs->setTableHeaderEcommStoreroomFrontEnd($storeroomDelivery['id']);
				
					$supplier_organization_id_old = 0;
					foreach($storeroomDelivery['Storeroom'] as $numStoreroom  => $storeroom) {
		
						if($storeroom['SuppliersOrganization']['id']!=$supplier_organization_id_old) {
							echo '<tr style="height:30px;">';
							echo '<td colspan="9" class="trGroup">'.__('Supplier').': '.$storeroom['SuppliersOrganization']['name'];
							if(!empty($storeroom['SuppliersOrganization']['descrizione'])) echo '/'.$storeroom['SuppliersOrganization']['descrizione'];
								
							echo '</td>';
							echo '</tr>';
						}
				
						echo "\r\n";
						echo '<tr>';
						echo '<td>'.($numStoreroom+1).'</td>';
						echo "\n";
						echo '<td>';
						if($storeroom['Article']['bio']=='Y')
							echo '<span class="bio" title="'.Configure::read('bio').'"></span>';
						else echo "";
						echo '</td>';
						echo '<td>'.$storeroom['name'].'</td>';
				
						echo "\n";  
						echo '<td style="white-space: nowrap;">'.$this->App->getArticleConf($storeroom['Article']['qta'], $storeroom['Article']['um']).'</td>';
						echo '<td style="white-space: nowrap;">'.$storeroom['prezzo_e'].'</td>';
						echo '<td style="white-space: nowrap;">'.$this->App->getArticlePrezzoUM($storeroom['prezzo'], $storeroom['Article']['qta'], $storeroom['Article']['um'], $storeroom['Article']['um_riferimento']).'</td>';
						echo '<td style="text-align:center;white-space: nowrap;">'.$storeroom['qta'].'</td>';
						echo '<td style="white-space: nowrap;">'.$this->App->getArticleImporto($storeroom['prezzo'], $storeroom['qta']).'</td>';
				
						echo '<td style="white-space:nowrap;text-align:center;">';
						echo "\n";
						//echo '<a title="'.__('Edit').'" class="action actionEdit" href="/?option=com_cake&controller=Storerooms&action=userToStoreroom&id='.$data['cart']['id'].'"></a>';
						echo '<a title="'.__('Edit').'" class="action actionEdit" href="javascript:viewContentAjax(\''.$storeroom['id'].'\')"><button type="button" class="btn btn-primary">'.__('Edit').'</button></a>';
						echo '</td>';
						echo '</tr>';
							
						$tot_importo_storeroom = ($tot_importo_storeroom + ($storeroom['prezzo'] * $storeroom['qta']));
						$tot_qta_storeroom += $storeroom['qta'];									
						$supplier_organization_id_old=$storeroom['SuppliersOrganization']['id'];
					} // end foreach($storeroomDelivery['Storeroom'] as $numStoreroom  => $storeroom)
				
				echo '</tbody>';
				echo '<tfooter>';
				echo '<tr>';		
				echo '<th></th>';
				echo '<th></th>';
				echo '<th></th>';
				echo '<th></th>';
				echo '<th></th>';
				echo '<th></th>';
				echo '<th style="text-align:center;">'.$tot_qta_storeroom.'</th>';
				echo '<th>'.number_format($tot_importo_storeroom,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
				echo '<th></th>';
				echo '</tr>';
				echo '</tfooter>';
				echo '</table>';					
				echo '</table>';						
					
				} // end if($storeroomDelivery['totStorerooms']) 
			} // end if(isset($storeroomResults['Tab'][$numTabs]['Delivery'][$numDelivery])) 
		} // if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') 
		/* 
		 * E N D  - D I S P E N S A
		 */				
			 
			 
			 
	/*
	 * rendering C O N T E N U T O 
	 */		
	$tot_importo_order=0;  /* il totale dell'utente di un ordine */
	$tot_importo=0;  /* il totale dell'utente di tutta la consegna */
    $tot_importo_cost_less = 0;
    $tot_importo_trasport = 0;	
    $tot_importo_cost_more = 0;    
	foreach($results['Tab'] as $numTabs => $tab) {
	
		
		foreach($tab['Delivery'] as $numDelivery => $delivery) {

			/*
			 * rendering dettaglio Consegna (ctrl su consegna e dispensa)
			 */
			if(($delivery['totOrders']>0 && $delivery['totArticlesOrder']>0) ||
				(isset($storeroomResults['Tab'][$numTabs]) && $storeroomResults['Tab'][$numTabs]['Delivery'][$numDelivery]['totStorerooms']>0)) {
				
				echo $this->Tabs->messageDelivery($delivery, $user);
			}

			if($delivery['totOrders']>0 && $delivery['totArticlesOrder']>0) {
				
				echo $this->Tabs->setTableHeaderEcommSimpleFrontEnd($delivery['id']);
				echo '<tbody>';
			}
			
			if($delivery['totOrders']>0 && $delivery['totArticlesOrder']>0) {
						
				if(isset($delivery['Order'])) {
					$order_id_old=0;
					foreach($delivery['Order'] as $numOrder => $order) {
	
						if(isset($order['ArticlesOrder'])) { // cosi' escludo gli ordini senza acquisti
								
							foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {
											
									if($order['Order']['id']!=$order_id_old) {

										$tot_importo_order=0;
										
										echo "\n";
										if($order['Order']['data_fine']!=Configure::read('DB.field.date.empty'))
											$data_fine = $this->Time->i18nFormat($order['Order']['data_fine'],"%A %e %B %Y");
										else
											$data_fine = "";
											
										echo '<tr style="height:30px;">';
										echo '<td colspan="11" class="trGroup">';
										
										if(!empty($order['SuppliersOrganization']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$order['SuppliersOrganization']['img1']))
											echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$order['SuppliersOrganization']['img1'].'" />';
										
										echo __('Supplier').': '.$order['SuppliersOrganization']['name'];
										if(!empty($order['SuppliersOrganization']['descrizione'])) echo '/'.$order['SuppliersOrganization']['descrizione'];
												
					                    if(isset($user->organization['Organization']['hasCashFilterSupplier']) && $user->organization['Organization']['hasCashFilterSupplier']=='Y') {
									    	if(!$order['SuppliersOrganization']['isSupplierOrganizationCashExcluded'])
									            echo ' - Escluso dal prepagato';       
											else
											    echo ' - Gestito con il prepagato'; 
					                    }

										echo '<span style="float:right">'.__('StateOrder').': '.__($order['Order']['state_code'].'-label').'&nbsp;';
										
										echo $this->App->utilsCommons->getOrderTime($order['Order']);
										
										/*
										 * se l'ordine e' in questo stato, il referente ha confermato gli importi
										*/
										if($order['Order']['state_code']=='PROCESSED-ON-DELIVERY' || $order['Order']['state_code']=='CLOSE')
											echo '&nbsp;-&nbsp;'.__('CarrelloInPROCESSED-ON-DELIVERY');
										
										echo '</span>';
									
										echo '</td>';
										echo '</tr>';
									}
							
									if($order['Order']['state_code']=='RI-OPEN-VALIDATE') // non permetto modifiche per gestione colli
										$order['Order']['permissionToEditUtente']=false;
									
									echo $this->RowEcomm->drawFrontEndSimple($numArticlesOrder, $order, $this->RowEcomm->prepareResult($numArticlesOrder, $order));
										 
									/*
									 * importo Cart per il totale
									*/
									$importo = 0;
									if(number_format($order['Cart'][$numArticlesOrder]['importo_forzato'])==0) {
										if(number_format($order['Cart'][$numArticlesOrder]['qta_forzato'])>0)
											$importo = ($order['Cart'][$numArticlesOrder]['qta_forzato'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']);
										else
											$importo = ($order['Cart'][$numArticlesOrder]['qta'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']);
									}
									else 
										$importo = $order['Cart'][$numArticlesOrder]['importo_forzato'];
									
									$tot_importo_order += $importo; /* totale del ordine */
									$tot_importo += $importo;  /* totale della consegna */
										
									$order_id_old = $order['Order']['id'];
						} // foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) 
					} // end if(isset($order['ArticlesOrder']))
                        
                        
								 
					/*
					 * only Order.state_code = PROCESSED-ON-DELIVERY / CLOSE (in carico al cassiere) 
					 * ctrl se ci sono variazioni in
					 *
					 * SummaryOrder
					 * SummaryOrderTrasport
					 * SummaryOrderCostMore
					 * SummaryOrderCostLess
					 *
					 * $resultsWithModifies[order_id][SummaryOrder][0][SummaryOrder][importo] e' la somma di SummaryOrderTrasport + SummaryOrderCostMore + SummaryOrderCostLess
					 */
					if(array_key_exists($order['Order']['id'], $resultsWithModifies)) {

						$resultsWithModifiesOrder = $resultsWithModifies[$order['Order']['id']];
						
						/*
						 * sub totale singolo ordine
						 */
						if(isset($order['ArticlesOrder'])) { // cosi' escludo gli ordini senza acquisti
						
							if(!isset($resultsWithModifiesOrder['SummaryOrder'][0])) {
									echo '<tr>';
									echo '	<th></th>';
									echo '	<th colspan="6" style="text-align:right;"></th>';
									echo '	<th style="text-align:center;"></th>';
									echo '	<th colspan="2" style="text-align:right;">Totale dell\'ordine&nbsp;&nbsp;</th>';
									echo '<th style="white-space:nowrap;">'.number_format($tot_importo_order,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
									echo '</tr>';									
							}
						}
						
						unset($resultsWithModifies[$order['Order']['id']]);
						
						/*
						echo "<pre>";
						print_r($resultsWithModifiesOrder);
						echo "</pre>";
						*/
						if(isset($resultsWithModifiesOrder['SummaryOrderTrasport'][0])) {
						
							$importo_trasport = $resultsWithModifiesOrder['SummaryOrderTrasport'][0]['SummaryOrderTrasport']['importo_trasport'];
							// echo '<br />importo_trasport '.$importo_trasport;
							
							if($importo_trasport > 0) {
								echo '<tr>';
								echo '	<th></th>';
								echo '	<th colspan="6" style="text-align:right;"></th>';
								echo '	<th style="text-align:center;"></th>';
								echo '	<th colspan="2" style="text-align:right;">Trasporto&nbsp;&nbsp;</th>';
								echo '<th style="white-space:nowrap;">'.number_format($importo_trasport,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
								echo '</tr>';
							}
									
							$tot_importo_trasport += $importo_trasport;	
							// echo '<br />tot_importo_trasport '.$tot_importo_trasport;
						}
						if(isset($resultsWithModifiesOrder['SummaryOrderCostMore'][0])) {
						
							$importo_cost_more = $resultsWithModifiesOrder['SummaryOrderCostMore'][0]['SummaryOrderCostMore']['importo_cost_more'];
							
							if($importo_cost_more > 0) {
								echo '<tr>';
								echo '	<th></th>';
								echo '	<th colspan="6" style="text-align:right;"></th>';
								echo '	<th style="text-align:center;"></th>';
								echo '	<th colspan="2" style="text-align:right;">Costo aggiuntivo&nbsp;&nbsp;</th>';
								echo '<th style="white-space:nowrap;">'.number_format($importo_cost_more,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
								echo '</tr>';						
							}
							
							$tot_importo_cost_more += $importo_cost_more;	
						}
						if(isset($resultsWithModifiesOrder['SummaryOrderCostLess'][0])) {
						
							$importo_cost_less = $resultsWithModifiesOrder['SummaryOrderCostLess'][0]['SummaryOrderCostLess']['importo_cost_less'];
							
							if($importo_cost_less != 0) {
								echo '<tr>';
								echo '	<th></th>';
								echo '	<th colspan="6" style="text-align:right;"></th>';
								echo '	<th style="text-align:center;"></th>';
								echo '	<th colspan="2" style="text-align:right;">Sconto&nbsp;&nbsp;</th>';
								echo '<th style="white-space:nowrap;">'.number_format($importo_cost_less,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
								echo '</tr>';
							}
							
							$tot_importo_cost_less += $importo_cost_less;	
						}	

						if(isset($resultsWithModifiesOrder['SummaryOrder'][0])) {
						
							$importo = $resultsWithModifiesOrder['SummaryOrder'][0]['SummaryOrder']['importo'];
							//echo '<br />importo '.$importo;
							
							if($importo > 0 && (''.$importo != ''.number_format($tot_importo_order,2))) {
								echo '<tr>';
								echo '	<th></th>';
								echo '	<th colspan="9" style="text-align:right;">';
								
								if($importo_trasport==0 && $importo_cost_less==0 && $importo_cost_more==0) {
									echo 'Totale dell\'ordine&nbsp;modificato&nbsp;dal&nbsp;referente&nbsp;';                                                             
									/*
									 * l'importo dell'ordine e' stato modificato con l'aggregazione dei dai, tolgo il vecchio e aggiungo il nuovo
									 */
									$tot_importo = ($tot_importo - $tot_importo_order + $importo);
								}
								else
									echo 'Totale dell\'ordine&nbsp;&nbsp;';   
								echo '</th>';
								echo '<th style="white-space:nowrap;">';
								echo number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
								echo '</tr>';
							}
						}					
					}
					/*
					 * END variazioni in
					 * SummaryOrder
					 * SummaryOrderTrasport
					 * SummaryOrderCostMore
					 * SummaryOrderCostLess
					 */
					 else {
						/*
						 * sub totale singolo ordine
						 */
						if(isset($order['ArticlesOrder'])) { // cosi' escludo gli ordini senza acquisti						
							echo '<tr>';
							echo '	<th></th>';
							echo '	<th colspan="6" style="text-align:right;"></th>';
							echo '	<th style="text-align:center;"></th>';
							echo '	<th colspan="2" style="text-align:right;">Totale dell\'ordine&nbsp;&nbsp;</th>';
							echo '<th style="white-space:nowrap;"><span class="totalePrezzoNew-'.$order['ArticlesOrder'][0]['order_id'].'">'.number_format($tot_importo_order,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</span></th>';
							echo '</tr>';
						}
					 }
                       
				} // foreach($delivery['Order'] as $numOrder => $order) 
			}  // end if(isset($delivery['Order']))
				
			/*
			 * totale importo della consegna
			*/
			$tot_importo = ($tot_importo_storeroom + $tot_importo + $tot_importo_trasport + ($tot_importo_cost_less) + $tot_importo_cost_more);	
			
			if($user->organization['Template']['payToDelivery']=='POST')
				$msg = sprintf(__('TotaleConfirmTesoriere'), '<span class="totalePrezzoNewALL">'.number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;').'</span>';
			if($user->organization['Template']['payToDelivery']=='ON' || $user->organization['Template']['payToDelivery']=='ON-POST')
				$msg = sprintf(__('TotaleConfirmCassiere'), '<span class="totalePrezzoNewALL">'.number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;').'</span>';
			
					
					
				echo '<tr>';
				echo '<td colspan="2"></td>';
				echo '<td colspan="9" style="text-align:right;"><h4>'.$msg.'</h4></td>';			
				echo '</tr>';
							
				echo '</tbody>';
				echo '</table>';
			}  // if($delivery['totOrders']>0 && $delivery['totArticlesOrder']>0)

			
		} // end foreach($tab['Delivery'] as $numDelivery => $delivery)
		 			
	} // end foreach($results['Tab'] as $numTabs => $tab)
?>
	
<script type="text/javascript">
function viewContentAjax(id) {
	$('#ajaxContent').animate({opacity:0});
	var url = "/?option=com_cake&controller=Storerooms&action=userToStoreroom&id="+id+"&format=notmpl";
	$('#ajaxContent').load(url);
	$('#ajaxContent').animate({opacity:1},1500);
	return;
}
</script>
<script type="text/javascript">
$(document).ready(function() {

	$(".rowEcomm").each(function () {
		activeEcommRows(this);    /* active + / - , mouseenter mouseleave */
		activeSubmitEcomm(this);	
	});	
	
	$('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	$('.actionTrView').each(function () {
		actionTrView(this);
	});
	
	$('.actionNotaView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	$('.actionNotaView').each(function () {
		actionNotaView(this); 
	});
});	
</script>

<?php
if(!empty($arrayDistances)) {
	echo "<h2>Quanta strada hanno fatto i tuoi acquisti?</h2>";
	$tot_distance = 0;
	foreach($arrayDistances as $arrayDistance) {
	
		$percentuale = $arrayDistance['percentuale'];
		if($percentuale==0) $percentuale = 1;
	
		echo '<p>';		
		echo '<strong>'.$arrayDistance['supplierName'].'</strong> da '.$arrayDistance['supplierLocalita'].' ha percorso '.$arrayDistance['distance'].' Km';
		echo '</p>';
		echo '<div class="progressBar">';
		echo '<span style="width: '.$percentuale.'%;"></span>';
		echo '</div>';
		
		$tot_distance += $arrayDistance['distance'];
	}
	
	$tot_distance = number_format($tot_distance,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	
	echo '<div role="group" class="btn-group pull-right">';
	echo '<button class="btn btn-success" type="button">per un totale di '.$tot_distance.' Km</button>';
	echo '</div>';
}
}
?>