<?php
if(!empty($results)) {
	
	echo '<h2 class="ico-statistic">Consegne</a></h2>';
	
	echo '<table cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '<th>'.__('N').'</th>';
	echo '<th>'.__('Delivery').'</th>';
	echo '<th>del</th>';
	echo '<th style="text-align:center;">Importo totale</th>';
	
	$totale = 0;
	foreach ($results as $numRow => $result) {
	
		$totale += $result[0]['tot_importo'];
		$importo = number_format($result[0]['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		$delivery_data = $this->Time->i18nFormat($result['StatDelivery']['data'],"%e %B %Y"); 
				
		echo '<tr>';
		echo '<td>'.($numRow+1).'</td>';
		echo '<td>'.$result['StatDelivery']['luogo'].'</td>';
		echo '<td>'.$delivery_data.'</td>';
		echo '<td style="text-align:center;">'.$importo.'&nbsp;&euro;</td>';
		echo '</tr>';		
	}

	$totale = number_format($totale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	echo '<tr>';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td style="text-align:center;"><b>'.$totale.'&nbsp;&euro;</b></td>';
	echo '</tr>';
		
	echo '</table>';
	
	
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

			
		$importo = $result[0]['tot_importo'];
		$delivery_data = $this->Time->i18nFormat($result['StatDelivery']['data'],"%e %b %y"); 
		//$label = $result['StatDelivery']['luogo'].' '.$delivery_data;	
		//$label = str_replace("\"", "", $label);
		$label = $delivery_data;
		
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