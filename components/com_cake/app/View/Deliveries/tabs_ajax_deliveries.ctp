<?php
/*
 * per linkare all'articolo di joomla
 */
$com_path = JPATH_SITE.'/components/com_content/';
require_once $com_path.'router.php';
require_once $com_path.'helpers/route.php';

if(empty($results))	
	echo $this->Tabs->messageNotOrders();
else {	


	/*
	 * D I S P E N S A
	 * */
	 $tot_importo_storeroom = 0;
	 $tot_qta_storeroom = 0;
	 if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
		 /*
		 foreach ($storeroomResults as $numStoreroom => $deliveryStoreroom) {
			if($deliveryStoreroom['Delivery']['id']==$delivery['id']) 
				echo $this->element('boxMsg',array('class_msg' => 'success', 'msg' => "Durante la consegna sar&agrave; gestita anche la dispensa."));
		}
		*/
		$supplier_organization_id_old = 0;
		if(!empty($storeroomResults))  {
			echo '<h2>Dispensa</h2>';
			echo $this->Tabs->setTableHeaderStoreroomFrontEnd($delivery_id);				
			foreach ($storeroomResults as $numStoreroom => $storeroom) {
				
				if($storeroom['SuppliersOrganization']['id']!=$supplier_organization_id_old) {
					echo '<tr style="height:30px;">';
					echo '<td colspan="8" class="trGroup">'.__('Supplier').': '.$storeroom['SuppliersOrganization']['name'];
					if(!empty($storeroom['SuppliersOrganization']['descrizione'])) echo '/'.$storeroom['SuppliersOrganization']['descrizione'];
						
					echo '</td>';
					echo '</tr>';
				}
		
				echo "\r\n";
				echo '<tr>';
		
				echo '<td>'.($numStoreroom+1).'</td>';
				echo '<td>';
				if($storeroom['Article']['bio']=='Y')
					echo '<span class="bio" title="'.Configure::read('bio').'"></span>';
				else echo "";
				echo '</td>';
				echo '<td>'.$storeroom['Storeroom']['name'].'</td>';
		
				echo "\n";  
				echo '<td style="white-space: nowrap;">'.$this->App->getArticleConf($storeroom['Article']['qta'], $storeroom['Article']['um']).'</td>';
				echo '<td style="white-space: nowrap;text-align:center;">'.number_format($storeroom['Storeroom']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
				echo '<td style="white-space: nowrap;">'.$this->App->getArticlePrezzoUM($storeroom['Storeroom']['prezzo'], $storeroom['Article']['qta'], $storeroom['Article']['um'], $storeroom['Article']['um_riferimento']).'</td>';
				echo '<td style="text-align:center;white-space: nowrap;">'.$storeroom['Storeroom']['qta'].'</td>';
				echo '<td style="white-space: nowrap;">'.$this->App->getArticleImporto($storeroom['Storeroom']['prezzo'], $storeroom['Storeroom']['qta']).'</td>';
		
				echo '</tr>';
						
				$tot_importo_storeroom = ($tot_importo_storeroom + ($storeroom['Storeroom']['prezzo'] * $storeroom['Storeroom']['qta']));
				$tot_qta_storeroom += $storeroom['Storeroom']['qta'];				
				$supplier_organization_id_old=$storeroom['SuppliersOrganization']['id'];				
			} // loop $storeroomResults
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
			echo '</tr>';
			echo '</tfooter>';
			echo '</table>';	
		}
	} // end dispensa
	

	foreach($results['Tab'] as $numTabs => $tab) {
		
		foreach($tab['Delivery'] as $numDelivery => $delivery) {
				
			echo $this->Tabs->messageDelivery($delivery, $user);
	
			if($delivery['totOrders']>0) {
						
				echo $this->Tabs->setTableHeader($delivery['id'], $user);
				echo '<tbody>';
				
				$i=0;
				/*
				 * ordine NON chiusi
				 */
				foreach($delivery['Order'] as $order)  {
						
					if($order['Order']['state_code']=='OPEN-NEXT' || $order['Order']['state_code']=='OPEN' || $order['Order']['state_code']=='RI-OPEN-VALIDATE') {
						draw_row($i, $delivery, $order, $user, $this->App, $this->Time);
						$i++;
					} 
				}  // end ciclo Orders	

				/*
				 * ordine chiusi
				 */				
				foreach($delivery['Order'] as $order)  {
						
					if($order['Order']['state_code']!='OPEN-NEXT' && $order['Order']['state_code']!='OPEN' && $order['Order']['state_code']!='RI-OPEN-VALIDATE') {
						draw_row($i, $delivery, $order, $user, $this->App, $this->Time);
						$i++;
					}
				}  // end ciclo Orders
			}
			else {
				$msg = "Non ci sono ancora produttori associati";
				echo $this->element('boxMsgFrontEnd', array('class_msg' => 'notice', 'msg' => $msg));			
			}


		/*
		 * F O O T E R
		 *
		/*
		 * in AppController setto $this->user
		 * $this->user->organization_id                    = organization dell'utente (in table.User)
		 * $this->user->organization['Organization']['id'] = organization che si sta navigando (dal templates ho il parametro organization_id e organizationSEO che metto in $user->set('org_id',$organization_id)) 
		 */
		$tot_importo_delivery = ($tot_importo_storeroom + $tot_importo_delivery); 
		if($user->id > 0 && $user->organization_id == $user->organization['Organization']['id'] && $tot_importo_delivery>0) {			
				echo "\n";
				echo '<table class="table" style="background-color:#C3D2E5;border-bottom: 3px solid #84A7DB;border-top: 3px solid #84A7DB;">';
				echo '<tr>';

			echo '<td class="pull-left action-deliveries">';
			/*
		 	*  ico INFO
		 	*/
			echo '<a title="Maggiori informazioni" href="#" rel="nofollow" data-toggle="modal" data-target="#myModal">';
			echo '<i class="fa fa-info-circle fa-2x"></i>';
			echo '</a>';

			/*
		 	*  ico CALENDAR
		 	*/	
			if($delivery['totOrders']>0) {	 
				echo '<a title="Visualizza il calendario" href="javascript:viewCalendar('.$delivery['id'].');">';
				echo '<i class="fa fa-calendar fa-2x"></i>';
				echo '</a>';
			}
		
			/*
		 	*  ico PDF
		 	*
		 	* in AppController setto $this->user
		 	* $this->user->organization_id                    = organization dell'utente (in table.User)
		 	* $this->user->organization['Organization']['id'] = organization che si sta navigando (dal templates ho il parametro organization_id e organizationSEO che metto in $user->set('org_id',$organization_id))
		 	*/
			if(isset($user) && $user->id > 0 && $user->organization_id == $user->organization['Organization']['id']) {
				echo '<a href="#" title="'.__('Print Cart Delivery').'" rel="nofollow" onclick="window.open(\'/?option=com_cake&controller=ExportDocs&action=userCart&delivery_id='.$delivery['id'].'&doc_formato=PDF&format=notmpl\',\'win2\',\'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no\'); return false;">';
				echo '<i class="fa fa-file-pdf-o fa-2x"></i>';
				echo '</a>';				
			}
				echo '</td>';

				if($user->organization['Organization']['payToDelivery']=='POST')
					$msg = sprintf(__('TotaleConfirmTesoriere'), number_format($tot_importo_delivery,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;');
				if($user->organization['Organization']['payToDelivery']=='ON' || $user->organization['Organization']['payToDelivery']=='ON-POST')
					$msg = sprintf(__('TotaleConfirmCassiere'), number_format($tot_importo_delivery,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;');
				
				echo "\n";
				echo '<td colspan="2" style="text-align:right;padding-right:10px;font-weight: bold;">';
				echo $msg;		
				echo '</td>';
				echo "\n";
				echo "\n";
				echo '</tr>';
				echo '</table>';
	    } // end if($user->id > 0 && $user->organization_id == $user->organization['Organization']['id'] && $tot_importo_delivery>0)  
		
			
		echo '</tbody>';
		echo '</table>';
		echo '</div>';  // class=table
		echo '</div>';  // class="container"
		
		echo '<div class="clearfix"></div>';
		?>
		<script type="text/javascript">
		$(document).ready(function() {
			$("#showHideAllCart_<?php echo $delivery['id']?>" ).click(function() {
				showHideAllCart(<?php echo $delivery['id']?>);
			});
		});
		</script>
		<?php				
	} // end ciclo Deliveries
		
} // end ciclo Tabs
?>
	<script type="text/javascript">
	function showHideSingleCart(deliveryId, numRow) {

			$("input[name='showHideAllCart_"+deliveryId+"']").prop('checked',false);
			
			if($(".deliveryId"+deliveryId+"-A"+numRow).css('display')=='none') {  /* chiudo i dati per gli acquisti */

				/* prima li chiuso tutti */
				$(".th-deliveryId"+deliveryId+"-A").css('display','table-cell');
				$(".th-deliveryId"+deliveryId+"-B").css('display','none');
				$(".deliveryId"+deliveryId+"-A").css('display','table-cell');
				$(".deliveryId"+deliveryId+"-B").css('display','none');
				
				
				/* th */
				$(".th-deliveryId"+deliveryId+"-A").css('display','table-cell');
				$(".th-deliveryId"+deliveryId+"-B").css('display','none');
				
				/* tr */
				$(".deliveryId"+deliveryId+"-A"+numRow).css('display','table-cell');
				$(".deliveryId"+deliveryId+"-B"+numRow).css('display','none');			
			}
			else { /* apro */
				/* th */
				$(".th-deliveryId"+deliveryId+"-A").css('display','none');
				$(".th-deliveryId"+deliveryId+"-B").css('display','table-cell');

				/* tr */
				$(".deliveryId"+deliveryId+"-A"+numRow).css('display','none');
				$(".deliveryId"+deliveryId+"-B"+numRow).css('display','table-cell');
			}
	}
	function showHideAllCart(deliveryId) {

		var checked = $("input[name='showHideAllCart_"+deliveryId+"']:checked").val();
		
		/*console.log("showHideAllCart(deliveryId="+deliveryId+") checked "+checked);*/
		
		if(checked=='ALL') {  /* apro tutti */
			/* th */
			$(".th-deliveryId"+deliveryId+"-A").css('display','none');
			$(".th-deliveryId"+deliveryId+"-B").css('display','table-cell');

			/* tr */
			$(".deliveryId"+deliveryId+"-A").css('display','none');
			$(".deliveryId"+deliveryId+"-B").css('display','table-cell');
		}	
		else {  /* chiudi tutti */
			/* th */
			$(".th-deliveryId"+deliveryId+"-A").css('display','table-cell');
			$(".th-deliveryId"+deliveryId+"-B").css('display','none');
			
			/* tr */
			$(".deliveryId"+deliveryId+"-A").css('display','table-cell');
			$(".deliveryId"+deliveryId+"-B").css('display','none');
		}
	}
	</script>
<?php
} // end if(empty($results))


function draw_row($i, $delivery, $order, $user, $App, $Time) {

					if($order['Order']['data_inizio']!=Configure::read('DB.field.date.empty')) 
						$data_inizio = $Time->i18nFormat($order['Order']['data_inizio'],"%A %e %B");
					else
						$data_inizio = "";
					if($order['Order']['data_fine']!=Configure::read('DB.field.date.empty')) 
						$data_fine = $Time->i18nFormat($order['Order']['data_fine'],"%A %e %B");
					else
						$data_fine = "";
					if($order['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty'))
						$data_fine_validation = $Time->i18nFormat($order['Order']['data_fine_validation'],"%A %e %B");
					else
						$data_fine_validation = "";
					
					$classRowColsA = 'deliveryId'.$delivery['id'].'-A'.$i;
					$classRowColsB = 'deliveryId'.$delivery['id'].'-B'.$i;
					$classColsA = 'deliveryId'.$delivery['id'].'-A';
					$classColsB = 'deliveryId'.$delivery['id'].'-B';
					
					echo "\n";
					echo '<tr>';
					echo "\n";
					echo '<td>'.($i+1).'</td>';
					echo "\n";
					echo '<td>';
					
					if(!empty($order['SuppliersOrganization']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$order['SuppliersOrganization']['img1']))
						echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$order['SuppliersOrganization']['img1'].'" />';
					
					echo '<span style="cursor:help;" title="';
					if(!empty($order['SuppliersOrganization']['indirizzo'])) echo $order['SuppliersOrganization']['indirizzo'].' ';
					if(!empty($order['SuppliersOrganization']['localita'])) echo $order['SuppliersOrganization']['localita'].' ';
					if(!empty($order['SuppliersOrganization']['provincia'])) echo '('.$order['SuppliersOrganization']['provincia'].') ';
					if(!empty($order['SuppliersOrganization']['telefono'])) echo $order['SuppliersOrganization']['telefono'].' ';
					if(!empty($order['SuppliersOrganization']['www'])) echo $order['SuppliersOrganization']['www'].' ';
					echo '">';
					echo $order['SuppliersOrganization']['name'];
					if(!empty($order['SuppliersOrganization']['descrizione'])) echo '/'.$order['SuppliersOrganization']['descrizione'];
					
					echo'</span>';
					if(!empty($order['Order']['nota'])) echo '<div class="hidden-xs nota">'.$order['Order']['nota'].'</div>';
					echo '</td>';
					
					/*
					 * link all'articolo di joomla
					 */
					echo "\n";
					echo '<td class="hidden-xs '.$classRowColsA.' '.$classColsA.'" style="display:table-cell;text-align: center;">';
					if($user->id > 0 && $user->get('org_id') == $user->organization['Organization']['id'] && 
					!empty($order['Order']['mail_open_testo'])) {
						$url = '?option=com_cake&controller=PopUp&action=order_mail_open_testo&order_id='.$order['Order']['id'].'&format=notmpl';					
						echo '<a rel="nofollow" data-toggle="modal" data-target="#myModalScheda" url="'.$url.'" href="#"><img border="0" title="Leggi le note del referente che ha inviato per mail" src="'.Configure::read('App.img.cake').'/apps/32x32/messenger.png" /></a> ';
					}		
					
					if(!empty($order['SuppliersOrganization']['j_content_id']) && !empty($order['SuppliersOrganization']['j_catid'])) {
					
						$url = JRoute::_(ContentHelperRoute::getArticleRoute($order['SuppliersOrganization']['j_content_id'], $order['SuppliersOrganization']['j_catid'])).'?tmpl=popup';					
						echo '<a rel="nofollow" data-toggle="modal" data-target="#myModalScheda" url="'.$url.'" href="#"><img border="0" title="Leggi la scheda del produttore" src="'.Configure::read('App.img.cake').'/apps/32x32/kontact.png" /></a>';
					}
					echo '</td>';
					echo "\n";
					echo '<td class="'.$classRowColsA.' '.$classColsA.'" style="display:table-cell;">';
					if($order['Order']['state_code']=='RI-OPEN-VALIDATE')
						echo $data_fine_validation;
					else
						echo $data_fine;
					echo '</td>';
					
					echo "\n";
					echo '<td class="hidden-xs '.$classRowColsA.' '.$classColsA.'" style="display:table-cell;">';
					echo $App->utilsCommons->getOrderTime($order['Order']);	
					echo '</td>';
					
					echo "\n";
					echo '<td class="hidden-xs '.$classRowColsA.' '.$classColsA.'" style="display:table-cell;">';
					echo $order['SuppliersOrganization']['frequenza'];
					echo '</td>';
					
					echo "\n";
					echo '<td class="'.$classRowColsA.' '.$classColsA.'" style="display:table-cell;">';
					echo $App->drawListSuppliersOrganizationsReferents($user, $order['SuppliersOrganizationsReferent'], $options = array('view_coreferente' => 'N'));
					echo '</td>';
						
					/* 
					 * A C Q U I S T I 
					 *
					 * in AppController setto $this->user
					 * $this->user->organization_id                    = organization dell'utente (in table.User)
					 * $this->user->organization['Organization']['id'] = organization che si sta navigando (dal templates ho il parametro organization_id e organizationSEO che metto in $user->set('org_id',$organization_id)) 
					 */
					if($user->id > 0 && $user->get('org_id') == $user->organization['Organization']['id']) {						
						echo "\n";
						echo '<td class="'.$classRowColsB.' '.$classColsB.' dettaglioAcquisti" style="display:none;" colspan="5">';
						echo '<nobr>';
						echo '<ul class="acquisti">';
						
						$tot_order_import=0;
						$tot_cart_in_order=0;
						if(isset($order['ArticlesOrder']))
						foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {
							if(!empty($order['Cart'][$numArticlesOrder])) {
								
								$tot_cart_in_order++;
								
								/*
								 * gestione qta e importi
								 * */
								if($order['Cart'][$numArticlesOrder]['qta_forzato']>0) {
									$qta = $order['Cart'][$numArticlesOrder]['qta_forzato'];
									$qta_modificata = true;
								}	
								else {
									$qta = $order['Cart'][$numArticlesOrder]['qta'];
									$qta_modificata = false;
								}
								$importo_modificato = false;
								if($order['Cart'][$numArticlesOrder]['importo_forzato']==0) {
									if($order['Cart'][$numArticlesOrder]['qta_forzato']>0) 
										$importo = ($order['Cart'][$numArticlesOrder]['qta_forzato'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']);
									else {
										$importo = ($order['Cart'][$numArticlesOrder]['qta'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']);
									}	
								}	
								else {
									$importo = $order['Cart'][$numArticlesOrder]['importo_forzato'];
									$importo_modificato = true;
								}
								
								$tot_order_import += $importo;
								$importo = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	
								echo "\n";
								echo '<li>';
								if($order['ArticlesOrder'][$numArticlesOrder]['stato']=='LOCK' || 
								   $order['ArticlesOrder'][$numArticlesOrder]['stato']=='QTAMAXORDER') 
										echo '<span class="stato_'.strtolower($order['ArticlesOrder'][$numArticlesOrder]['stato']).'"></span>';
								echo $order['ArticlesOrder'][$numArticlesOrder]['name'];
								
								echo ' <b>q.t&agrave;</b> '.$qta.'&nbsp;'.$App->traslateQtaImportoModificati($qta_modificata);
								echo ' di '.$App->getArticleConf($order['Article'][$numArticlesOrder]['qta'], $App->traslateEnum($order['Article'][$numArticlesOrder]['um'])).',';
								echo ' <b>importo</b> '.$importo.'&nbsp;&euro;&nbsp;'.$App->traslateQtaImportoModificati($importo_modificato);
								echo '</li>';
							}
						} // end foreach
						
						echo '</ul>';
						if($tot_order_import>0 && $tot_cart_in_order>1) {
							
								$tot_order_import = number_format($tot_order_import,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
								
								echo '<div style="float:right">';
								echo ' <b>Somma dell\'ordine</b> '.$tot_order_import.'&nbsp;&euro;';
								echo '</div>';							
						}
						echo '</td>';
						
						echo "\n";
						echo "<td style=\"text-align:center\">";
						if($tot_cart_in_order>0) {
							echo '<a href="javascript:showHideSingleCart('.$delivery['id'].','.$i.');">';
							echo '<img class="img-responsive-disabled" src="'.Configure::read('App.img.cake').'/cesta-piena.png" title="ci sono prodotti ordinati" border="0" /></a>';
						}
						else 
							echo '<img class="img-responsive-disabled" src="'.Configure::read('App.img.cake').'/cesta-vuota.png" title="nessun prodotto ordinato" border="0" />';
						echo "</td>";
					} // end if($user->id > 0 && $user->get('org_id') == $user->organization['Organization']['id'])  

					echo "\n";
					echo '</tr>';	
}
?>

<script type="text/javascript">
	$(document).ready(function ($) {
		$('.selectpicker').selectpicker({
			style: 'btn-default'
		});
		$('#tabs').tab();

		$(function(){
		   $('a').tooltip();
		});
	});
	</script>