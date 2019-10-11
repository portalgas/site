<?php
if(!empty($results)) {
	
	echo '<h2 class="ico-statistic">Importi totale per ordine<a name="produttori_totale_importo"></a></h2>';
	
	echo '<div class="table"><table class="table table-hover">';
	echo '<tr>';
	echo '<th>'.__('N').'</th>';
	echo '<th>'.__('Delivery').'</th>';
	echo '<th>del</th>';
	echo '<th colspan="2">Produttore</th>';
	echo '<th>Inizio ordine</th>';
	echo '<th>Fine ordine</th>';
	echo '<th style="text-align:center;">Importo totale</th>';
	if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') {
		echo '<th style="text-align:center;">Pagamento</th>';
		echo '<th style="text-align:center;">'.__('request_payment_num_short').'</th>';
	}
	
	$totale = 0;
	$delivery_id_old = 0;
	foreach ($results as $numRow => $result) {

		$totale += $result['StatOrder']['importo'];
		$importo = number_format($result['StatOrder']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		$delivery_data = $this->Time->i18nFormat($result['StatDelivery']['data'],"%e %B %Y"); 
		$order_data_inizio = $this->Time->i18nFormat($result['StatOrder']['data_inizio'],"%e %B %Y"); 
		$order_data_fine = $this->Time->i18nFormat($result['StatOrder']['data_fine'],"%e %B %Y"); 
				
		echo '<tr>';
		echo '<td>'.($numRow+1).'</td>';
		if($delivery_id_old!=$result['StatDelivery']['id']) {
			echo '<td>'.$result['StatDelivery']['luogo'].'</td>';
			echo '<td>'.$delivery_data.'</td>';
		}
		else {
			echo '<td></td>';
			echo '<td></td>';
		}
		echo '<td>';
		if(!empty($result['StatOrder']['supplier_img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['StatOrder']['supplier_img1']))
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['StatOrder']['supplier_img1'].'" />';		
		echo '</td>';		
		echo '<td>'.$result['StatOrder']['supplier_organization_name'].'</td>';
		echo '<td>'.$order_data_inizio.'</td>';
		echo '<td>'.$order_data_fine.'</td>';
		echo '<td style="text-align:center;">'.$importo.'&nbsp;&euro;</td>';
		
		if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') { 
			echo '<td>';
			if(!empty($result['StatOrder']['tesoriere_doc1']) && file_exists(Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$user->organization['Organization']['id'].DS.$result['StatOrder']['tesoriere_doc1'])) {
				$ico = $this->App->drawDocumentIco($result['StatOrder']['tesoriere_doc1']);
				echo '<a alt="Scarica il documento" title="Scarica il documento" href="'.Configure::read('App.server').Configure::read('App.web.doc.upload.tesoriere').'/'.$user->organization['Organization']['id'].'/'.$result['StatOrder']['tesoriere_doc1'].'" target="_blank"><img src="'.$ico.'" /></a>';
			}
			
			if(!empty($result['StatOrder']['tesoriere_fattura_importo'])) {
				echo '<p>'.__('Tesoriere fattura importo').' ';
				echo number_format($result['StatOrder']['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
				echo '</p>';	
			}

			if(!empty($result['StatOrder']['tesoriere_importo_pay'])) {
				echo '<p>'.__('Tesoriere Importo Pay').' ';
				echo number_format($result['StatOrder']['tesoriere_importo_pay'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
				echo '</p>';
			}
			
			if(!empty($result['StatOrder']['tesoriere_data_pay'])) {
				echo '<p>'.__('Tesoriere Data Pay').' ';
				if($result['StatOrder']['tesoriere_data_pay']==Configure::read('DB.field.date.empty'))
					$tesoriere_data_pay = '';
				else
					$tesoriere_data_pay = $this->Time->i18nFormat($result['StatOrder']['tesoriere_data_pay'],"%e %B %Y");
								
				echo $tesoriere_data_pay;
				echo '</p>';
			}
			echo '</td>';
			echo '<td style="text-align:center;">'.$result['StatOrder']['request_payment_num'].'</td>';
		}
		
		
		echo '</tr>';
		
		$delivery_id_old=$result['StatDelivery']['id'];
	}

	$totale = number_format($totale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	echo '<tr>';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td style="text-align:center;"><b>'.$totale.'&nbsp;&euro;</b></td>';
	if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') 
		echo '<td></td><td></td>';
	echo '</tr>';
		
	echo '</table></div>';
	
	
	/*
	 *  grafici
	 */
	
	echo '<script type="text/javascript">';
	echo 'var data = [';
	echo "\n";
	$tmp = '';
	foreach ($results as $numRow => $result) {
	
		if($numRow%2==0)
			$color = "#7e3838";
		else
		if($numRow%2==3)
			$color = "#587e38";
		else
		if($numRow%2==4)
			$color = "#7c7e38";
		else
		if($numRow%2==5)
			$color = "#387e45";
		else
		if($numRow%2==6)
			$color = "#387e6a";
		else
			$color = "#386a7e";
			
		$label = $result['StatOrder']['supplier_organization_name'];
		$label = str_replace("\"", "", $label);
		$label = str_replace("&", "", $label);
		$label = str_replace("'", "", $label);
		$importo = $result['StatOrder']['importo'];
		
		$tmp .= '{';
		$tmp .= "\n";
		$tmp .= '"label": "'.$label.'",';
		$tmp .= "\n";
		$tmp .= '"value": '.$importo.',';  // 
		$tmp .= "\n";
		$tmp .= '"color": "'.$color.'"';
		$tmp .= "\n";
		$tmp .= '},';
	}
	$tmp = substr($tmp, 0, strlen($tmp)-1);
	echo $tmp;
	echo '];';	
	echo '</script>';	
}
else
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => __('msg_search_not_result')));	
?>