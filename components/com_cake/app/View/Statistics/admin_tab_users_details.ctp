<?php
if(!empty($results)) {
	
		
	echo '<h2 class="ico-statistic">Totali per utente<a name="utenti_totale_importo_qta"></a></h2>';
	
	echo '<table cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '<th>'.__('N').'</th>';
	echo '<th colspan="3">Utente</th>';
	echo '<th style="text-align:center;">Quantità totale</th>';
	echo '<th style="text-align:center;">Importo totale</th>';
	echo '</tr>';
	
	$totale = 0;
	$totale_qta = 0;		
	foreach ($results as $numRow => $result) {
	
		$totale += $result[0]['tot_importo'];
		$totale_qta += $result[0]['tot_qta'];
	
		echo '<tr>';
		echo '<td>'.($numRow+1).'</td>';
		echo '<td style="width:50px;">'.$this->App->drawUserAvatar($user, $result['User']['id'], $result['User']).'</td>';
		echo '<td>'.$result['User']['name'].'</td>';
		echo '<td>'.$result['User']['username'].'</td>';
		echo '<td style="text-align:center;">'.$result[0]['tot_qta'].'</td>';
		echo '<td style="text-align:center;">'.number_format($result[0]['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
		echo '</tr>';
	}
	
	$totale = number_format($totale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	
	echo '<tr>';
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