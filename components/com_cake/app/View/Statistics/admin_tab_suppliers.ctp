<?php
if(!empty($results)) {

	echo '<h2 class="ico-statistic">Importi totale per produttore<a name="totale_importo_produttore"></a></h2>';
		
	echo '<table cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '<th>'.__('N').'</th>';
	echo '<th colspan="2">Produttore</th>';
	echo '<th style="text-align:center;">Importo</th>';
	echo '</tr>';
	$totale = 0;
	$supplier_organization_id_old=0;
	foreach ($results as $numRow => $result) {
		
		$totale += $result[0]['tot_importo'];
		$tot_importo = number_format($result[0]['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	
		echo '<tr>';
		echo '<td>'.($numRow+1).'</td>';
		if($supplier_organization_id_old!=$result['StatOrder']['supplier_organization_id']) {
			echo '<td width="50">';
			if(!empty($result['StatOrder']['supplier_img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['StatOrder']['supplier_img1']))
				echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['StatOrder']['supplier_img1'].'" />';	
			echo '</td>';		
			echo '<td>'.$result['StatOrder']['supplier_organization_name'].'</td>';
		}	
		else {
			echo '<td></td>';
			echo '<td></td>';
		}
		echo '<td style="text-align:center;">'.$tot_importo.' &euro;</td>';
		echo '</tr>';
		
		$supplier_organization_id_old=$result['StatOrder']['supplier_organization_id'];
	}
	
	$totale = number_format($totale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	echo '<tr>';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td style="text-align:center;">'.$totale.' &euro;</td>';
	echo '</tr>';	
	echo '</table>';

	/*
	 *  grafici
	 */
		
	echo '<script type="text/javascript">';
	echo 'var data = [';
	echo "\n";
	$tmp = '';
	$supplier_organization_id_old=0;
	foreach ($results as $numRow => $result) {
	
		if($supplier_organization_id_old!=$result['StatOrder']['supplier_organization_id']) {
		
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
			$importo = $result[0]['tot_importo'];
			
			$tmp .= '{';
			$tmp .= "\n";
			$tmp .= '"label": "'.$label.'",';
			$tmp .= "\n";
			$tmp .= '"value": '.$importo.',';   
			$tmp .= "\n";
			$tmp .= '"color": "'.$color.'"';
			$tmp .= "\n";
			$tmp .= '},';
		}
		
		$supplier_organization_id_old=$result['StatOrder']['supplier_organization_id'];
	}
	$tmp = substr($tmp, 0, strlen($tmp)-1);
	echo $tmp;
	echo '];';	
	echo '</script>';
	
	echo $this->Html->script('d3/myGrafics', array('date' => uniqid()));		
}
else
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud'));	
?>