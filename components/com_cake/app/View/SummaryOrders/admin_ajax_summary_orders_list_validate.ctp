<?php
/*
echo "<pre>";
print_r($summaryOrdersResults);
echo "</pre>";
*/
$hasTrasport = $orderResults['Order']['hasTrasport']; /* trasporto */
$hasCostMore = $orderResults['Order']['hasCostMore']; /* spesa aggiuntiva */
$hasCostLess = $orderResults['Order']['hasCostLess'];  /* sconto */
$typeGest = $orderResults['Order']['typeGest'];   /* AGGREGATE / SPLIT */

/*
 *  test
$orderResults['Order']['state_code']='PROCESSED-TESORIERE';
$hasTrasport='Y';
*/
$GLOBALS['tot_importi_aggregati_diversi']=0;

function drawTotali($user_id, $qta_user_tot, $importo_user_tot, $summaryOrdersResults) {
	
	$importo_summary_order = 0;
	
	$html = '';
	$html .= '<tr>';
	$html .= '<td></td>';
	$html .= '<td></td>';
	$html .= '<td></td>';
	$html .= '<td></td>';
	$html .= '<td style="text-align:center;">'.$qta_user_tot.'</td>';
	$html .= '<td style="text-align:right;">'.number_format($importo_user_tot,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
	$html .= '<td style="text-align:right;">';
	foreach($summaryOrdersResults as $numResult => $summaryOrdersResult) {
		
		$key = $summaryOrdersResult['SummaryOrder']['order_id'].'-'.$summaryOrdersResult['SummaryOrder']['delivery_id'].'-'.$summaryOrdersResult['SummaryOrder']['user_id'];
			
		if($summaryOrdersResult['User']['id']==$user_id) {
			$importo_summary_order = $summaryOrdersResult['SummaryOrder']['importo'];
			
			$html .= '<span id="summary-order-importo-'.$key.'">';
			$html .= number_format($importo_summary_order,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$html .= '</span>';
			$html .= '&nbsp;&euro;';
			
			
			unset($summaryOrdersResults[$numResult]);
			
			break;
		}
	}
	
	$importo_user_tot = number_format($importo_user_tot,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$importo_summary_order = number_format(floatval($importo_summary_order),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	
	// $html .= '('.floatval($importo_summary_order).' - '.$importo_user_tot.')';
	$html .= '</td>';
	

	
	if($importo_summary_order == $importo_user_tot) {
		$html .= '<td style="background-color:green;"></td>';
		$html .= '<td></td>';				
	}
	else {
		$html .= '<td id="color-'.$key.'" style="background-color:red;"></td>';
		$html .= '<td style="text-align:right;">';
		$html .= '<a href="#" class="ricalcola" id="'.$key.'">ricalcola importo aggregato</a>';
		$html .= '</td>';
		
		$GLOBALS['tot_importi_aggregati_diversi']++;
	}
			
	$html .= '</tr>';

	return $html;	
}


/*
 * elenco Acqusiti, vefifica che se ci sono importi aggregati diversi dall'importo reale 
 */
if(!empty($results)) {
			
	$html = '';
	$html .= '<table cellpadding="0" cellspacing="0">';
	$html .= '<thead>'; 
	$html .= '	<tr>';	
	$html .= '		<th colspan="2"></th>';
	$html .= '		<th></th>';
	$html .= '		<th></th>';
	$html .= '		<th></th>';
	$html .= '		<th></th>';
	$html .= '		<th style="text-align:right;">'.__('importo_aggregato').'</th>';
	$html .= '		<th style="width:3px;"></th>';
	$html .= '		<th></th>';
	$html .= '	</tr>';
	
	$html .= '	<tr>';	
	$html .= '		<th colspan="2">'.__('Name').'</th>';
	$html .= '		<th>'.__('PrezzoUnita').'</th>';
	$html .= '		<th>'.__('Prezzo/UM').'</th>';
	$html .= '		<th style="text-align:center;">'.__('qta').'</th>';
	$html .= '		<th style="text-align:right;">'.__('Importo').'</th>';
	$html .= '		<th style="text-align:right;">'.$label_importo_aggregato.'</th>';
	$html .= '		<th style="width:3px;"></th>';
	$html .= '		<th>'.__('Actions').'</th>';
	$html .= '	</tr>';				
	$html .= '</thead><tbody>';

	$user_id_old = 0;
	$importo_user_tot = 0;
	$qta_user_tot = 0;
	foreach ($results as $result) {

		if($user_id_old != $result['User']['id']) {
		
			if($user_id_old > 0) {
	
				$html .= drawTotali($user_id_old, $qta_user_tot, $importo_user_tot, $summaryOrdersResults);
				
				$importo_user_tot = 0;	
				$qta_user_tot = 0;						
			}
			
			$html .= '<tr>';
			$html .= '<td colspan="9">';
			$html .= $this->App->drawUserAvatar($user, $result['User']['id'], $result['User']).' ';
			$html .= $result['User']['name'];
			$html .= '</td>';
			$html .= '</tr>';	
		}
		
		/*
		 * gestione QTA e IMPORTI
		* */
		$qta_modificata = false;
		if($result['Cart']['qta_forzato']>0) {
			$qta = $result['Cart']['qta_forzato'];
			$qta_modificata = true;
		}
		else {
			$qta = $result['Cart']['qta'];
			$qta_modificata = false;
		}
		$importo_modificato = false;
		if($result['Cart']['importo_forzato']==0) {
			if($result['Cart']['qta_forzato']>0) 
				$importo = ($result['Cart']['qta_forzato'] * $result['ArticlesOrder']['prezzo']);
			else {
				$importo = ($result['Cart']['qta'] * $result['ArticlesOrder']['prezzo']);
			}
		}
		else {
			$importo = $result['Cart']['importo_forzato'];
			$importo_modificato = true;
		}
			
		if($result['Cart']['deleteToReferent'] == 'N') {
			$importo_user_tot += $importo;	
			$qta_user_tot += $qta;	
			$deleteToReferent = '';
		}
		else {
			$deleteToReferent = 'text-decoration:line-through;';		
		}
		
		$importo = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
							
		$html .= '<tr>';
		$name = $result['ArticlesOrder']['name'].' '.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
			if(!empty($result['Article']['codice']))
				$name = $result['Article']['codice'].' '.$name;
		}
		$html .= '<td></td>';
		$html .= '<td style="'.$deleteToReferent.'">'.$name.'</td>';
		$html .= '<td>'.$result['ArticlesOrder']['prezzo_e'].'</td>';
		$html .= '<td>'.$this->App->utilsCommons->getArticlePrezzoUM($result['ArticlesOrder']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']).'</td>';
		$html .= '<td style="'.$deleteToReferent.'text-align:center;">'.$qta.$this->App->traslateQtaImportoModificati($qta_modificata).'</td>';
		$html .= '<td style="'.$deleteToReferent.'text-align:right;">'.$importo.$this->App->traslateQtaImportoModificati($importo_modificato).'</td>';
		$html .= '<td></td>';
		$html .= '<td></td>';
		$html .= '<td></td>';
		
		$html .= '</tr>';
		
		$user_id_old = $result['User']['id'];
				
	} // end foreach ($results s $result)

	/*
	 * ultimo totale
	 */
	$html .= drawTotali($user_id_old, $qta_user_tot, $importo_user_tot, $summaryOrdersResults);
	 			
	$html .= '</tbody></table>';			

}  // end if(!empty($results))
	

/*
 * inizio disegno HTML
 */
echo '<table cellpadding = "0" cellspacing = "0">';
echo '<tr>';
echo '	<th style="border-radius:5px;" colspan="3">'.$this->App->drawOrdersStateDiv($orderResults).'&nbsp;'.__($orderResults['Order']['state_code'].'-label').'</th>';
echo '</tr>';
if($typeGest=='AGGREGATE' || $hasTrasport=='Y' || $hasCostMore=='Y' || $hasCostLess=='Y') {
	echo '<tr>';
	echo '	<th style="width:20%">Caratteristiche ordine</th>';
	echo '	<td>';
	echo '<ul class="menuLateraleItems">';
	if($typeGest=='AGGREGATE') {
		echo '<li><div title="'.__('Management Carts Group By Users').'" class="bgLeft actionEditDbGroupByUsers">'.__('Management Carts Group By Users').'</div></li>';
	}
	if($hasTrasport=='Y') {
		echo '<li><div title="'.__('Management trasport').'" class="bgLeft actionTrasport">'.__('Management trasport').' ('.$orderResults['Order']['trasport_e'].')</div></li>';
		$label_importo_aggregato = '+ '.__('Importo').' '.__('Trasport').'<br />';
	}
	if($hasCostMore=='Y') {
		echo '<li><div title="'.__('Management cost_more').'" class="bgLeft actionCostMore">'.__('Management cost_more').' ('.$orderResults['Order']['cost_more_e'].')</div></li>';
		$label_importo_aggregato = '+ '.__('Importo').' '.__('CostMore').'<br />';
	}
	if($hasCostLess=='Y') {
		echo '<li><div title="'.__('Management cost_less').'" class="bgLeft actionCostLess">'.__('Management cost_less').' ('.$orderResults['Order']['cost_less_e'].')</div></li>';
		$label_importo_aggregato = '+ '.__('Importo').' '.__('CostLess').'<br />';
	}	
	echo '</ul>';
	echo '</td>';
	
	echo '	<th width="10%">';
	echo 	$this->Html->link(null, array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$orderResults['Order']['id']), array('class' => 'action actionWorkflow','title' => __('Order home')));

	echo '	<a id="actionMenu-'.$orderResults['Order']['id'].'" class="action actionMenu" title="'.__('Expand menu').'"></a>';
	echo '	<div class="menuDetails" id="menuDetails-'.$orderResults['Order']['id'].'" style="display:none;">';
	echo '		<a class="menuDetailsClose" id="menuDetailsClose-'.$orderResults['Order']['id'].'"></a>';
	echo '		<div id="order-sotto-menu-'.$orderResults['Order']['id'].'"></div>';
	echo '	</div>';		
	echo '</th>';
		
	echo '</tr>';	
}
echo '</table>';

echo $this->element('boxSummaryOrdersValidate',array('results' => $orderResults, 
												     'tot_importi_aggregati_diversi' => $GLOBALS['tot_importi_aggregati_diversi']));
echo $html;


echo '<script type="text/javascript">';
echo 'jQuery(document).ready(function() {';
?>
	jQuery(".actionMenu").click(function() {

		jQuery('.menuDetails').css('display','none');
		
		var idRow = jQuery(this).attr('id');
		numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		jQuery('#menuDetails-'+numRow).show();

		viewOrderSottoMenu(numRow,"bgLeft");

		var offset = jQuery(this).offset();
		var newTop = (offset.top - 100);
		var newLeft = (offset.left - 350);

		jQuery('#menuDetails-'+numRow).offset({ top: newTop, left: newLeft});			
	});	

	jQuery(".menuDetailsClose").each(function () {
		jQuery(this).click(function() {
			var idRow = jQuery(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			jQuery('#menuDetails-'+numRow).hide('slow');
		});
	});		
});
<?php
echo 'jQuery(".ricalcola").click(function() {';
echo "\n";
if(($hasTrasport=='N' && $hasCostMore=='N' && $hasCostLess=='N') || 
   ($orderResults['Order']['state_code']=='PROCESSED-ON-DELIVERY')) {
?>
	var key = jQuery(this).attr('id');
	
	jQuery(this).html("");
	jQuery(this).append("<img src='"+ app_img + "/ajax-loader.gif' />");
	var url = "/administrator/index.php?option=com_cake&controller=SummaryOrders&action=ajax_summary_orders_ricalcola&key="+key+"&format=notmpl";
	jQuery.ajax({
		type: "GET",
		url: url,
		data: "",
		success: function(response){
			/* console.log("key "+key+" response "+response); */
			jQuery('#summary-order-importo-'+key).html(response);
			jQuery('#color-'+key).css('background-color','green');
			jQuery('#'+key).html("");
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
		}
	});
<?php
}
else {

	/*
	 * ordine in mano al referente con Trasporto, etc etc, => gestione tipica dal modulo di gestione trasporto
	 */
	if($orderResults['Order']['state_code']=='PROCESSED-BEFORE-DELIVERY' || 
	   $orderResults['Order']['state_code']=='PROCESSED-POST-DELIVERY' || 
	   $orderResults['Order']['state_code']=='INCOMING-ORDER') {
			echo "\n".'jQuery("html, body").animate({scrollTop:0}, 500);'."\n";
			echo "apriPopUp('".Configure::read('App.server')."/administrator/index.php?option=com_cake&controller=PopUp&action=order_importi_aggregati_aggiorna&order_id=".$orderResults['Order']['id']."&format=notmpl');";    
	}
	else
	/*
	 * ordine in mano al tesoriere con Trasporto, etc etc, => gestione tipica => riportare al referente
	 */
	if($orderResults['Order']['state_code']=='WAIT-PROCESSED-TESORIERE') {
		echo "\n".'jQuery("html, body").animate({scrollTop:0}, 500);'."\n";
		echo "apriPopUp('".Configure::read('App.server')."/administrator/index.php?option=com_cake&controller=PopUp&action=order_importi_aggregati_return_tesoriere&order_id=".$orderResults['Order']['id']."&format=notmpl');";    
	} 
	else
	if($orderResults['Order']['state_code']=='PROCESSED-TESORIERE') {
		echo "\n".'jQuery("html, body").animate({scrollTop:jQuery("#intro").offset().top}, 500);'."\n";
	}
	/*
	 * ordine in mano al cassiere con Trasporto, etc etc, => gestione tipica => riportare al referente
	else
	if($orderResults['Order']['state_code']=='PROCESSED-ON-DELIVERY') {
		echo "\n".'jQuery("html, body").animate({scrollTop:0}, 500);'."\n";
		echo "apriPopUp('".Configure::read('App.server')."/administrator/index.php?option=com_cake&controller=PopUp&action=order_importi_aggregati_return_cassiere&order_id=".$orderResults['Order']['id']."&format=notmpl');";      
	}
	*/
}

echo 'return false;';
echo "\n";
//echo '});';
echo '});';
echo '</script>';
?>