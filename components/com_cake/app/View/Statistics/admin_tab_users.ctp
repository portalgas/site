<?php
if(!empty($results)) {
	
	echo '<h2 class="ico-statistic">Acquisti totali per utente<a name="utenti"></a></h2>';
	
	echo '<table cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '<th>'.__('N').'</th>';
	echo '<th colspan="2">Produttore</th>';
	echo '<th>Articolo</th>';
	echo '<th colspan="3">Utente</th>';
	echo '<th style="text-align:center;">Quantità totale<br />acquistata</th>';
	echo '<th style="text-align:center;">Importo totale</th>';
	echo '</tr>';
	
	$totale = 0;
	$totale_qta = 0;	
	$supplier_organization_id_old=0;
	foreach ($results as $numRow => $result) {
	
		$totale += $result['StatCart']['importo'];
		$totale_qta += $result['StatCart']['qta'];
	
		echo '<tr>';
		echo '<td>'.($numRow+1).'</td>';
		if($supplier_organization_id_old!=$result['StatOrder']['supplier_organization_id']) {
			echo '<td width="50">';
			if(!empty($result['StatOrder']['supplier_img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['StatOrder']['supplier_img1']))
				echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['StatOrder']['supplier_img1'].'" />';	
			echo '</td>';		
			echo '<td>'.$result['StatOrder']['supplier_organization_name'].'</td>';
		}	
		else {
			echo '<td></td>';
			echo '<td></td>';
		}
		echo '<td>'.$result['StatArticlesOrder']['name'].'</td>';
		echo '<td style="width:50px;">'.$this->App->drawUserAvatar($user, $result['User']['id'], $result['User']).'</td>';
		echo '<td>'.$result['User']['name'].'</td>';
		echo '<td>'.$result['User']['username'].'</td>';
		echo '<td style="text-align:center;">'.$result['StatCart']['qta'].'</td>';
		echo '<td style="text-align:center;">'.number_format($result['StatCart']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
		echo '</tr>';
		
		$supplier_organization_id_old=$result['StatOrder']['supplier_organization_id'];
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
	echo '<td style="text-align:center;"><b>'.$totale_qta.'</b></td>';
	echo '<td style="text-align:center;"><b>'.$totale.'&nbsp;&euro;</b></td>';
	echo '</tr>';	
	echo '</table>';	
		
}
else
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => __('msg_search_not_result')));	
?>