<?php 
/*
 * T O - A R T I C L E S - D E T A I L S 
 * Documento con articoli aggregati con il dettaglio degli utenti
 */


if($this->layout=='pdf') {
	App::import('Vendor','xtcpdf');
	
	$output = new XTCPDF($organization, PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	$output->headerText = $fileData['fileTitle'];
			
	// add a page
	$output->AddPage();
	$css = $output->getCss();
}
else 
if($this->layout=='ajax') {
	App::import('Vendor','xtcpreview');
	$output = new XTCPREVIEW();
	$css = $output->getCss();
}

$html = $this->ExportDocs->organization($user);
$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

if(isset($results['Delivery']))
foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {

	$html = $this->ExportDocs->delivery($result['Delivery']);
	$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

	if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0) {

		if(isset($result['Delivery']['Order']))
		foreach($result['Delivery']['Order'] as $numOrder => $order) {

			$hasTrasport = $order['Order']['hasTrasport'];
			$importo_trasporto = $order['Order']['trasport'];
			
			$hasCostMore = $order['Order']['hasCostMore'];
			$importo_cost_more = $order['Order']['cost_more']; 
			
			$hasCostLess = $order['Order']['hasCostLess'];
			$importo_cost_less = $order['Order']['cost_less'];
			
			$html = $this->ExportDocs->suppliersOrganization($order['SuppliersOrganization']);
			$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');


			$html = '';
			$html .= '	<table cellpadding="0" cellspacing="0">';
			$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
			$html .= '		<tr>';
			$html .= '			<th width="'.$output->getCELLWIDTH20().'">'.__('N').'</th>';
			$html .= '			<th width="'.$output->getCELLWIDTH30().'">'.__('Bio').'</th>';
			
			if($acquistato_il=='Y' && $article_img=='Y') {
				$html .= '			<th style="white-space:nowrap;" width="'.$output->getCELLWIDTH90().'">Foto</th>';
				$html .= '			<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH10()).'">'.__('Name').'</th>';
				$html .= '			<th width="'.$output->getCELLWIDTH100().'">Utente</th>';
				$html .= '			<th style="white-space:nowrap;" width="'.$output->getCELLWIDTH90().'">Acquistato il</th>';
			}
			else
			if($acquistato_il=='Y' && $article_img=='N') {
				$html .= '			<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'">'.__('Name').'</th>';
				$html .= '			<th width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'">Utente</th>';
				$html .= '			<th style="white-space:nowrap;" width="'.$output->getCELLWIDTH100().'">Acquistato il</th>';
			}
			else
			if($acquistato_il=='N' && $article_img=='Y') {
				$html .= '			<th style="white-space:nowrap;" width="'.$output->getCELLWIDTH100().'">Foto</th>';
				$html .= '			<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'">'.__('Name').'</th>';
				$html .= '			<th width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'">Utente</th>';
			}
			else			
			if($acquistato_il=='N' && $article_img=='N') {
				$html .= '			<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH50()).'">'.__('Name').'</th>';
				$html .= '			<th width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'">Utente</th>';
			}
			
			$html .= '			<th width="'.$output->getCELLWIDTH50().'">'.__('qta').'</th>';
			$html .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('PrezzoUnita').'</th>';
			$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Importo').'</th>';
			$html .= '	</tr>';			
			$html .= '	</thead><tbody>';	
			
			$tot_qta = 0;
			$tot_importo = 0;
			$tot_qta_singolo_articolo = 0;
			$tot_importo_singolo_articolo = 0;
			$order_id_old = 0;
			$article_organization_id_old = 0;
			$article_id_old = 0;
			$name = '';
			$qta = 0;
			$prezzo = '';
			$totale = 0;
			$i=0;
			if(isset($order['ArticlesOrder']))
			foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {
			
				/*
				 * per gli articoli DES che prendono gli articoli da un'altro GAS il pregresso potrebbe non avere ArticlesOrder.name
				 */	
				 if(!empty($order['ArticlesOrder'][$numArticlesOrder]['name']))
					 $name = $order['ArticlesOrder'][$numArticlesOrder]['name'];
				 else
					 $name = $order['Article'][$numArticlesOrder]['name'];
				$name = $name.' '.$this->App->getArticleConf($order['Article'][$numArticlesOrder]['qta'], $order['Article'][$numArticlesOrder]['um']);
				
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y') {
					if(!empty($order['Article'][$numArticlesOrder]['codice']))
						$name = $order['Article'][$numArticlesOrder]['codice'].' '.$name;
					
				}

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
								

				$tot_qta += $qta;
				$tot_importo += $importo;
											
				$html .= '<tr>';
				if($order_id_old != $order['ArticlesOrder'][$numArticlesOrder]['order_id'] ||  
				   $article_organization_id_old != $order['ArticlesOrder'][$numArticlesOrder]['article_organization_id_old']  ||  
				   $article_id_old != $order['ArticlesOrder'][$numArticlesOrder]['article_id'] ) {
				   
					/*
					 * totale per ogni articolo
					 */
					if($numArticlesOrder>0 && $totale_per_articolo=='Y')  {

						$html .= '	<th width="'.$output->getCELLWIDTH20().'"></th>';
						$html .= '	<th width="'.$output->getCELLWIDTH30().'"></th>';
						
						if($acquistato_il=='Y' && $article_img=='Y') {
							$html .= '			<th style="white-space:nowrap;" width="'.$output->getCELLWIDTH90().'"></th>';
							$html .= '			<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH10()).'"></th>';
							$html .= '			<th width="'.$output->getCELLWIDTH100().'"></th>';
							$html .= '			<th style="white-space:nowrap;" width="'.$output->getCELLWIDTH90().'" style="text-align:right;">'.__('qta_tot').'</th>';
						}
						else
						if($acquistato_il=='Y' && $article_img=='N') {
							$html .= '	<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'"></th>';
							$html .= '	<th width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'"></th>';
							$html .= '  <th width="'.$output->getCELLWIDTH100().'" style="text-align:right;">'.__('qta_tot').'</th>';
						}
						else
						if($acquistato_il=='N' && $article_img=='Y') {
							$html .= '	<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'"></th>';
							$html .= '	<th width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'"></th>';
							$html .= '  <th width="'.$output->getCELLWIDTH100().'" style="text-align:right;">'.__('qta_tot').'</th>';
						}
						else			
						if($acquistato_il=='N' && $article_img=='N') {
							$html .= '	<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH50()).'"></th>';
							$html .= '	<th width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'" style="text-align:right;">'.__('qta_tot').'</th>';
						}
									
						$html .= '  <th style="text-align:center;" width="'.$output->getCELLWIDTH50().'">&nbsp;'.$tot_qta_singolo_articolo.'</th>';
						$html .= '	<th width="'.($output->getCELLWIDTH70()+$output->getCELLWIDTH70()).'" colspan="2" style="text-align:right;">'.__('Importo_totale').'&nbsp;'.number_format($tot_importo_singolo_articolo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'.$this->App->traslateQtaImportoModificati($importo_modificato).'</th>';
			
						$html .= '</tr>';
						
						$tot_qta_singolo_articolo = 0;
						$tot_importo_singolo_articolo = 0;	

						$html .= '<tr>';
					} // end if($numArticlesOrder>0 && $totale_per_articolo=='Y')
					
					$html .= '	<td width="'.$output->getCELLWIDTH20().'">'.($i+1).'</td>';
					$html .= '	<td width="'.$output->getCELLWIDTH30().'">';
					if($order['Article'][$numArticlesOrder]['bio']=='Y')
						$html .= 'Bio';
					$html .= '</td>';

					$foto = '';
					if(!empty($order['Article'][$numArticlesOrder]['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$order['Article'][$numArticlesOrder]['organization_id'].DS.$order['Article'][$numArticlesOrder]['img1'])) {
						$foto = '<img width="40" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$order['Article'][$numArticlesOrder]['organization_id'].'/'.$order['Article'][$numArticlesOrder]['img1'].'" />';	
					}
					
					if($acquistato_il=='Y' && $article_img=='Y') {
						$html .= '<td width="'.$output->getCELLWIDTH90().'">'.$foto.'</td>';
						$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH10()).'">'.$name.'</td>';						
						$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$order['User'][$numArticlesOrder]['name'].'</td>';  
						$html .= '<td width="'.$output->getCELLWIDTH90().'">'.$this->time->i18nFormat($order['Cart'][$numArticlesOrder]['created'],"%e %B alle %R").'</td>'; // %T per avere anche i secondi
					}
					else
					if($acquistato_il=='Y' && $article_img=='N') {
						$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'">'.$name.'</td>';
						$html .= '<td width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'">'.$order['User'][$numArticlesOrder]['name'].'</td>';
						$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$this->time->i18nFormat($order['Cart'][$numArticlesOrder]['created'],"%e %B alle %R").'</td>'; // %T per avere anche i secondi
					}
					else
					if($acquistato_il=='N' && $article_img=='Y') {
						$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$foto.'</td>';
						$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'">'.$name.'</td>';
						$html .= '<td width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'">'.$order['User'][$numArticlesOrder]['name'].'</td>';
					}
					else			
					if($acquistato_il=='N' && $article_img=='N') {
						$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH50()).'">'.$name.'</td>';
						$html .= '<td width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'">'.$order['User'][$numArticlesOrder]['name'].'</td>';
					}
				
					$i++;
				}
				else {
					$html .= '	<td width="'.$output->getCELLWIDTH20().'"></td>';
					$html .= '	<td width="'.$output->getCELLWIDTH30().'"></td>';

					if($acquistato_il=='Y' && $article_img=='Y') {
						$html .= '<td width="'.$output->getCELLWIDTH90().'"></td>';
						$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH10()).'"></td>';						
						$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$order['User'][$numArticlesOrder]['name'].'</td>';  
						$html .= '<td width="'.$output->getCELLWIDTH90().'">'.$this->time->i18nFormat($order['Cart'][$numArticlesOrder]['created'],"%e %B alle %R").'</td>'; // %T per avere anche i secondi
					}
					else
					if($acquistato_il=='Y' && $article_img=='N') {
						$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'"></td>';
						$html .= '<td width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'">'.$order['User'][$numArticlesOrder]['name'].'</td>';
						$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$this->time->i18nFormat($order['Cart'][$numArticlesOrder]['created'],"%e %B alle %R").'</td>'; // %T per avere anche i secondi
					}
					else
					if($acquistato_il=='N' && $article_img=='Y') {
						$html .= '<td width="'.$output->getCELLWIDTH100().'"></td>';
						$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'"></td>';
						$html .= '<td width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'">'.$order['User'][$numArticlesOrder]['name'].'</td>';
					}
					else			
					if($acquistato_il=='N' && $article_img=='N') {
						$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH50()).'"></td>';
						$html .= '<td width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'">'.$order['User'][$numArticlesOrder]['name'].'</td>';
					}
				}
				
				$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH50().'">'.$qta.$this->App->traslateQtaImportoModificati($qta_modificata).'</td>';
				$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH70().'">'.$this->App->getArticlePrezzo($order['ArticlesOrder'][$numArticlesOrder]['prezzo']).'</td>';
				$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'.$this->App->traslateQtaImportoModificati($importo_modificato).'</td>';
				$html .= '</tr>';	
					
				$userName = $order['User'][$numArticlesOrder]['name'];
				
				if($order['Article'][$numArticlesOrder]['bio']=='Y')
					$bio = 'Si';
				else
					$bio = 'No';
				$created = $order['Cart'][$numArticlesOrder]['created'];
				$prezzo = $order['ArticlesOrder'][$numArticlesOrder]['prezzo'];
				
				$tot_qta_singolo_articolo += $qta;
				$tot_importo_singolo_articolo += $importo;
				
				$order_id_old = $order['ArticlesOrder'][$numArticlesOrder]['order_id'];
				$article_organization_id_old = $order['ArticlesOrder'][$numArticlesOrder]['article_organization_id_old'];
				$article_id_old = $order['ArticlesOrder'][$numArticlesOrder]['article_id'];
				
			}  // end foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) 

		
			
			/*
			 * totale per ogni articolo
			*/
			if($totale_per_articolo=='Y')  {
			
				$html .= '<tr>';
				$html .= '	<th width="'.$output->getCELLWIDTH20().'"></th>';
				$html .= '	<th width="'.$output->getCELLWIDTH30().'"></th>';
			
				if($acquistato_il=='Y' && $article_img=='Y') {
					$html .= '			<th style="white-space:nowrap;" width="'.$output->getCELLWIDTH90().'"></th>';
					$html .= '			<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH10()).'"></th>';
					$html .= '			<th width="'.$output->getCELLWIDTH100().'"></th>';
					$html .= '			<th style="white-space:nowrap;" width="'.$output->getCELLWIDTH90().'" style="text-align:right;">'.__('qta_tot').'</th>';
				}
				else
				if($acquistato_il=='Y' && $article_img=='N') {
					$html .= '	<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'"></th>';
					$html .= '	<th width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'"></th>';
					$html .= '  <th width="'.$output->getCELLWIDTH100().'" style="text-align:right;">'.__('qta_tot').'</th>';
				}
				else
				if($acquistato_il=='N' && $article_img=='Y') {
					$html .= '	<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'"></th>';
					$html .= '	<th width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'"></th>';
					$html .= '  <th width="'.$output->getCELLWIDTH100().'" style="text-align:right;">'.__('qta_tot').'</th>';
				}
				else
				if($acquistato_il=='N' && $article_img=='N') {
					$html .= '	<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH50()).'"></th>';
					$html .= '	<th width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'" style="text-align:right;">'.__('qta_tot').'</th>';
				}
					
				$html .= '  <th style="text-align:center;" width="'.$output->getCELLWIDTH50().'">&nbsp;'.$tot_qta_singolo_articolo.'</th>';
				$html .= '	<th width="'.($output->getCELLWIDTH70()+$output->getCELLWIDTH70()).'" colspan="2" style="text-align:right;">'.__('Importo_totale').'&nbsp;'.number_format($tot_importo_singolo_articolo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'.$this->App->traslateQtaImportoModificati($importo_modificato).'</th>';
					
				$html .= '</tr>';
			}

			
			
			/*
			 * ctrl se il totale e' stato modificato in Carts::managementCartsGroupByUsers
			*/	
			if(!empty($resultsSummaryOrder[0]['totale_importo']) &&
					$resultsSummaryOrder[0]['totale_importo'] != ($tot_importo + $order['Order']['trasport'] + $order['Order']['cost_more'] + ($order['Order']['cost_less'])).'' ) {  
				$tmp_importo = $resultsSummaryOrder[0]['totale_importo'];
				$importo_modificato = true;
			}
			else {
				$tmp_importo = $tot_importo;
				$importo_modificato = false;
			}
		
			$html .= '<tr>';
			$html .= '	<th width="'.$output->getCELLWIDTH20().'"></th>';
			$html .= '	<th width="'.$output->getCELLWIDTH30().'"></th>';
			
			if($acquistato_il=='Y' && $article_img=='Y') {
				$html .= '			<th style="white-space:nowrap;" width="'.$output->getCELLWIDTH90().'"></th>';
				$html .= '			<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH10()).'"></th>';
				$html .= '			<th width="'.$output->getCELLWIDTH100().'"></th>';
				$html .= '			<th style="white-space:nowrap;" width="'.$output->getCELLWIDTH90().'" style="text-align:right;">'.__('qta_tot').'</th>';
			}
			else
			if($acquistato_il=='Y' && $article_img=='N') {
				$html .= '	<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'"></th>';
				$html .= '	<th width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'"></th>';
				$html .= '  <th width="'.$output->getCELLWIDTH100().'" style="text-align:right;">'.__('qta_tot').'</th>';
			}
			else
			if($acquistato_il=='N' && $article_img=='Y') {
				$html .= '	<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'"></th>';
				$html .= '	<th width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'"></th>';
				$html .= '  <th width="'.$output->getCELLWIDTH100().'" style="text-align:right;">'.__('qta_tot').'</th>';
			}
			else			
			if($acquistato_il=='N' && $article_img=='N') {
				$html .= '	<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH50()).'"></th>';
				$html .= '	<th width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'" style="text-align:right;">'.__('qta_tot').'</th>';
			}
						
			$html .= '  <th style="text-align:center;" width="'.$output->getCELLWIDTH50().'">&nbsp;'.$tot_qta.'</th>';
			$html .= '	<th width="'.($output->getCELLWIDTH70()+$output->getCELLWIDTH70()).'" colspan="2" style="text-align:right;">'.__('Importo_totale').'&nbsp;'.number_format($tmp_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'.$this->App->traslateQtaImportoModificati($importo_modificato).'</th>';

			$html .= '</tr>';

			
			
			$htmlTmp = '<tr>';
			$htmlTmp .= '	<td width="'.$output->getCELLWIDTH20().'"></td>';
			$htmlTmp .= '	<td width="'.$output->getCELLWIDTH30().'"></td>';
			
			if($acquistato_il=='Y' && $article_img=='Y') {
				$htmlTmp .= '<td width="'.$output->getCELLWIDTH90().'"></td>';
				$htmlTmp .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH10()).'"></td>';
				$htmlTmp .= '<td width="'.$output->getCELLWIDTH100().'"></td>';
				$htmlTmp .= '<td width="'.$output->getCELLWIDTH90().'"></td>';
			}
			else
			if($acquistato_il=='Y' && $article_img=='N') {
				$htmlTmp .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'"></td>';
				$htmlTmp .= '<td width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'"></td>';
				$htmlTmp .= '<td width="'.$output->getCELLWIDTH100().'"></td>';
			}
			else
			if($acquistato_il=='N' && $article_img=='Y') {
				$htmlTmp .= '<td width="'.$output->getCELLWIDTH100().'"></td>';
				$htmlTmp .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'"></td>';
				$htmlTmp .= '<td width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'"></td>';
			}
			else
			if($acquistato_il=='N' && $article_img=='N') {
				$htmlTmp .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH50()).'"></td>';
				$htmlTmp .= '<td width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH60()).'"></td>';
			}
			
			
			
			/*
			 * TRASPORTO
			 */
			if($hasTrasport=='Y' && $trasportAndCost=='Y') {
				$html .= $htmlTmp;
				$html .= '  <td style="text-align:center;" width="'.$output->getCELLWIDTH50().'"></td>';
				$html .= '	<th width="'.($output->getCELLWIDTH70()+$output->getCELLWIDTH70()).'" colspan="2" style="text-align:right;">'.__('TrasportShort').'&nbsp;'.number_format($importo_trasporto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
			}
			
			/*
			 * COSTO AGGIUNTIVO
			*/
			if($hasCostMore=='Y' && $trasportAndCost=='Y') {
				$html .= $htmlTmp;
				$html .= '  <td style="text-align:center;" width="'.$output->getCELLWIDTH50().'"></td>';
				$html .= '	<th width="'.($output->getCELLWIDTH70()+$output->getCELLWIDTH70()).'" colspan="2" style="text-align:right;">'.__('CostMoreShort').'&nbsp;'.number_format($importo_cost_more,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
			}
				
			/*
			 * SCONTO
			*/
			if($hasCostLess=='Y' && $trasportAndCost=='Y') {
				$html .= $htmlTmp;
				$html .= '  <td style="text-align:center;" width="'.$output->getCELLWIDTH50().'"></td>';
				$html .= '	<th width="'.($output->getCELLWIDTH70()+$output->getCELLWIDTH70()).'" colspan="2" style="text-align:right;">'.__('CostLessShort').'&nbsp;'.number_format($importo_cost_less,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
			}
				
			/*
			 * TOTALE
			*/
			if(($hasTrasport=='Y' || $hasCostMore=='Y' || $hasCostLess=='Y') && $trasportAndCost=='Y') {
					
				/*
				 * se modificato, quindi in SummaryOrder e' gia' compreso l'importo del trasporto
				*/
				if($importo_modificato)
					$importo_completo = ($tmp_importo);
				else
					$importo_completo = ($tmp_importo + $importo_trasporto + $importo_cost_more + (-1 * $importo_cost_less));
					
				$html .= $htmlTmp;				
				$html .= '  <td style="text-align:center;" width="'.$output->getCELLWIDTH50().'"></td>';
				$html .= '	<th width="'.($output->getCELLWIDTH70()+$output->getCELLWIDTH70()).'" colspan="2" style="text-align:right;">'.__('Totale').'&nbsp;'.number_format($importo_completo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'.$this->App->traslateQtaImportoModificati($importo_modificato).'</th>';
	
				$html .= '</tr>';
			}
			
			$html .= '</tbody></table>';
			$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

			$html = '';
			$html = $this->ExportDocs->suppliersOrganizationsReferent($order['SuppliersOrganizationsReferent']);
			$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
				
		}  // end foreach($result['Delivery']['Order'] as $numOrder => $order)
			
		$html = '';
		$html = $output->getLegenda();
		$output->writeHTML($css.$html, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
		
	}
	else {
		$html = $this->ExportDocs->suppliersOrganization($result['Delivery']['Order'][0]['SuppliersOrganization']);
		$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
		
		$html = '<div class="h4PdfNotFound">'.__('export_docs_not_found').'</div>';
		$output->writeHTMLCell(0,0,15,40, $css.$html, $border=0, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	}	
}  // end foreach($results['Delivery'] as $numDelivery => $result['Delivery']) 

			
// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData['fileName'].'.pdf', 'D');
exit;
?>