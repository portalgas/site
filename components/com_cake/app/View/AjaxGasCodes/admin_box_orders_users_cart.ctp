<?php 
$this->App->d($results);

if(!empty($results)) {
		
	if($drawListAllOrders=='Y')  
		echo '<table cellpadding="0" cellspacing="0">';
	
	/*
	 * ciclo di tutti gli ordini in cui un utente ha effettuato acquisti
	 * se $drawListAllOrders=='N' ho solo un ordine
	 * */
	$tot_importo = 0; /* totale importo di un consegna per un utente */
	foreach ($results as $numResult => $result) {
			
		if($drawListAllOrders=='Y') {		
				echo '<tr id="'.$result['Order']['id'].'_'.$user_id.'">';
				echo '	<th>'.((int)$numResult+1).'</th>';
				echo '	<th>'.__('Order').' '.$result['SuppliersOrganization']['name'].'</th>';
				echo '  <th>';
				echo $this->App->utilsCommons->getOrderTime($result['Order']);
				echo '  </th>';
				echo '  <th>';
				echo $this->App->drawOrdersStateDiv($result);
				echo '&nbsp;';
				echo __($result['Order']['state_code'].'-label');
				echo '  </th>';
				
				echo '<th>';	
		
				if($user->organization['Template']['payToDelivery']=='ON' || $user->organization['Template']['payToDelivery']=='ON-POST') {
					echo '<ul>';
					echo '<li style="float: left;">';
					echo '<a style="height: 32px;padding-left: 35px;" class="box-cart actionEdit" action="readCartsUsers">';
					echo __('Read Carts Short');
					echo '</a>';
					echo '</li>';
					echo '<li style="float: left;">';
					echo '<a style="height: 32px;padding-left: 35px;" class="box-cart actionEditDbOneWF" action="managementCartsUsers">';
					echo __('Management Carts One Short');
					echo '</a>';
					echo '</li>';
					if($result['Order']['typeGest']=='AGGREGATE') {
						echo '<li style="float: left;">';
						echo '<a style="height: 32px;padding-left: 35px;" class="box-cart actionEditDbGroupByUsersWF" action="managementCartsUsers">';
						echo __('Management Carts Group By Users Short');
						echo '</a>';
						echo '</li>';						
					}
					if($result['Order']['typeGest']=='SPLIT') {
						echo '<li style="float: left;">';
						echo '<a style="height: 32px;padding-left: 35px;" class="box-cart actionEditDbSplitWF" action="managementCartsUsers">';
						echo __('Management Carts Split Short');
						echo '</a>';
						echo '</li>';						
					}				
					echo '</ul>';
				}
				echo '  </th>';
				
				echo '</tr>';

				/*
				 * eventuali options
				 */
				echo '<tr id="'.$result['Order']['id'].'_'.$user_id.'">';
				echo '	<td style="border:none;"></td>';
				echo '	<td style="border:none;" colspan="4" id="box-cart-options'.$result['Order']['id'].'_'.$user_id.'"></td>';
				echo '</tr>';
				
				echo '<tr id="'.$result['Order']['id'].'_'.$user_id.'">';
				echo '	<td></td>';
				echo '	<td colspan="4" id="box-cart-content'.$result['Order']['id'].'_'.$user_id.'">';
						
		} // end if($drawListAllOrders=='Y') 
	 		
		
		
		
		/*
		 * intestazione articoli acquistati
		 * se $drawListAllOrders=='N' eseguo solo questa parte
		 */
		echo '<table>';
		echo '<tr>';
		echo '<th>'.__('Name').'</th>';
		echo '<th>'.__('PrezzoUnita').'</th>';
		echo '<th>'.__('Prezzo/UM').'</th>';
		echo '<th>'.__('qta').'</th>';
		echo '<th style="text-align:right;">'.__('Importo').'</th>';
		if($result['Order']['hasTrasport']=='Y' || $result['Order']['hasCostMore']=='Y' || $result['Order']['hasCostLess']=='Y') {
			echo '<th style="text-align:right;">'.__('TrasportAndCost').'</th>';
			echo '<th style="text-align:right;">'.__('Totale').'</th>';
		}
		echo '</tr>';

		$tot_qta_sub = 0; /* totale qta di un ordine */
		$tot_importo_sub = 0; /* totale importo di un ordine */
		foreach ($result['ArticlesOrder'] as $numResult2 => $articlesOrderResults) {

			$name = $articlesOrderResults['ArticlesOrder']['name'].' '.$this->App->getArticleConf($articlesOrderResults['Article']['qta'], $articlesOrderResults['Article']['um']);
			$prezzo_umrif = number_format($articlesOrderResults['ArticlesOrder']['prezzo'] / $articlesOrderResults['Article']['qta'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			
			/*
			 * gestione QTA e IMPORTI
			* */
			$qta_modificata = false;
			if($articlesOrderResults['Cart']['qta_forzato']>0) {
				$qta = $articlesOrderResults['Cart']['qta_forzato'];
				$qta_modificata = true;
			}
			else {
				$qta = $articlesOrderResults['Cart']['qta'];
				$qta_modificata = false;
			}
			$importo_modificato = false;
			if($articlesOrderResults['Cart']['importo_forzato']==0) {
				if($articlesOrderResults['Cart']['qta_forzato']>0)
					$importo = ($articlesOrderResults['Cart']['qta_forzato'] * $articlesOrderResults['ArticlesOrder']['prezzo']);
				else {
					$importo = ($articlesOrderResults['Cart']['qta'] * $articlesOrderResults['ArticlesOrder']['prezzo']);
				}
			}
			else {
				$importo = $articlesOrderResults['Cart']['importo_forzato'];
				$importo_modificato = true;
			}
			
			echo '<td>'.$name.'</td>';
			echo '<td>'.$articlesOrderResults['ArticlesOrder']['prezzo_e'].'</td>';
			echo '<td>'.$prezzo_umrif.' al '.$this->App->traslateEnum($articlesOrderResults['Article']['um_riferimento']).'</td>';
			echo '<td style="text-align:center;">'.$qta.$this->App->traslateQtaImportoModificati($qta_modificata).'</td>';
			echo '<td style="text-align:right;">'.number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'.$this->App->traslateQtaImportoModificati($importo_modificato).'</td>';
			
			if($result['Order']['hasTrasport']=='Y' || $result['Order']['hasCostMore']=='Y' || $result['Order']['hasCostLess']=='Y') {
				echo '<td style="text-align:right;">';
				if($result['Order']['hasTrasport']=='Y' && $result['Order']['trasport']!='0.00') echo  __('TrasportShort').' '.$result['Order']['trasport'].'<br />';
				if($result['Order']['hasCostMore']=='Y' && $result['Order']['cost_more']!='0.00') echo  __('CostMoreShort').' '.$result['Order']['cost_more'].'<br />';
				if($result['Order']['hasCostLess']=='Y' && $result['Order']['cost_less']!='0.00') echo  __('CostLessShort').' '.$result['Order']['cost_less'];
				echo '</td>';
				
				$importo_completo = ($importo + $result['Order']['trasport'] + $result['Order']['cost_more'] + ($result['Order']['cost_less']));
				
				echo '<td style="text-align:right;">'.number_format($importo_completo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'.$this->App->traslateQtaImportoModificati($importo_modificato).'</td>';
				
			}
				
			echo '</td>';
			echo '</tr>';	

			$tot_qta_sub += $qta;
			$tot_importo_sub += $importo;
			
		} // end foreach ($result['ArticlesOrder'] as $numResult2 => $articlesOrderResults)

		/*
		 * sub importo di un ordine  
		 */
		echo '<tr>';
		echo '<td colspan="3" style="text-align:right;font-weight: bold;">Totale&nbsp;dell\'utente&nbsp;</td>';
		echo '<td style="text-align:center;">'.$tot_qta_sub.'</td>';
		echo '<td style="text-align:right;">'.number_format($tot_importo_sub,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
		if($result['Order']['hasTrasport']=='Y' || $result['Order']['hasCostMore']=='Y' || $result['Order']['hasCostLess']=='Y') {
			echo '<th style="text-align:right;"></td>';
			echo '<th style="text-align:right;"></td>';
		}
		echo '</tr>';
		echo '</table>';
		
		
		
		

		if($drawListAllOrders=='Y') {
				
				$tot_importo += $tot_importo_sub;
				
				echo '</td>';
				echo '</tr>';
		} // if($drawListAllOrders=='Y')
				
		
		
			} // end foreach ($results as $result)

			
		if($drawListAllOrders=='Y') {	
			?>
				<script type="text/javascript">
				$(document).ready(function() {
				
					$('.box-cart').click(function() {
		
						/* id = order.id_user.id */
						var idRow = $(this).closest('tr').attr('id');
		
						/* console.log('idRow '+idRow); */ 
		
						var delivery_id = $('#delivery_id').val();
		
						dataElementArray = idRow.split('_');
						var order_id = dataElementArray[0];
						var user_id = dataElementArray[1];

						var idDivOptionsTarget = 'box-cart-options'+idRow;
						var idDivTarget = 'box-cart-content'+idRow;
						var articlesOptions = 'options-users-cart';
						var order_by = "";
						
						var action = $(this).attr('action');
						if(action=='readCartsUsers') {
							AjaxCallToReadCartsUsers(delivery_id, user_id, order_id, idDivTarget);
							$('#'+idDivOptionsTarget).hide();
						}
						if(action=='managementCartsUsers') 
							AjaxCallToOptions(delivery_id, order_id, user_id, idDivOptionsTarget);
					});
				});
				</script>	
			<?php 
		
			/*
			 * importo di tutti gli ordini dell'utente
			*/
			echo '<tr>';
			echo '<th colspan="5" style="text-align:right;">Totale importo '.number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
			echo '</tr>';
			echo '</table>';
			
		} // end if($drawListAllOrders=='Y') 	
}
?>