<?php		
$this->App->d($results);
		
if(!empty($results)) {
		
	$tmp .= '<div class="table-responsive"><table class="table table-hover">';
	$tmp .= '<tr>';
	$tmp .= '<th>'.__('N').'</th>';
	$tmp .= '<th colspan="2">'.__('Username').'</th>';
	$tmp .= '<th>'.__('Importo').'</th>';
	$tmp .= '<th>'.__('Importo_pagato').'</th>';
	$tmp .= '<th>'.__('Modality').'</th>';
	$tmp .= '<th colspan="2">'.__('Esito').'</th>';
	$tmp .= '</tr>';

	$tot_importo = 0;
	$tot_importo_pagato = 0;
	foreach($results as $numResult => $result) {
		
		$importo = number_format($result['SummaryOrder']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		$importo_pagato = number_format($result['SummaryOrder']['importo_pagato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		
		$tmp .= "\r\n";
		$tmp .= '<tr>';
		$tmp .= '	<td>'.($numResult+1).'</td>';
		$tmp .= '	<td>'.$this->App->drawUserAvatar($user, $result['User']['id'], $result['User']).'</td>';
		$tmp .= '	<td>'.$result['User']['name'];
		if(!empty($result['User']['email']))
		$tmp .= ' <a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a>';
		if(!empty($result['User']['Profile']['phone'])) $tmp .= ' '.$result['User']['Profile']['phone'].'<br />';
		if(!empty($result['User']['Profile']['phone2'])) $tmp .= ' '.$result['User']['Profile']['phone2'];
		$tmp .= '	</td>';
				
				
		$tmp .= '<td>'.$importo.'&nbsp;&euro;</td>';
		$tmp .= '<td>'.$importo_pagato.'&nbsp;&euro;</td>';
		$tmp .= '<td>';
		if($result['SummaryOrder']['modalita']!='DEFINED')
			$tmp .= $this->App->traslateEnum($result['SummaryOrder']['modalita']);
		$tmp .= '</td>';
		
		if($importo_pagato==0) {
			$tmp .= '<td style="width:10px;background-color:#006600"></td>';
			$tmp .= '<td>Da passare al tesoriere</td>';
		}
		else {
			$tmp .= '<td style="width:10px;background-color:#fff"></td>';
			$tmp .= '<td>Saldato</td>';		
		}
			
		$tmp .= '</tr>';

		$tot_importo += $result['SummaryOrder']['importo'];
		$tot_importo_pagato += $result['SummaryOrder']['importo_pagato'];
	}
		
	/*
	 * totali
	 */
	$tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_importo_pagato = number_format($tot_importo_pagato,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		
	$tmp .= "\r\n";
	$tmp .= '<tr>';
	$tmp .= '	<td></td>';
	$tmp .= '	<td></td>';
	$tmp .= '	<td style="font-size: 16px;text-align:right;font-weight: bold;">Totale</td>';
	$tmp .= '<td>'.$tot_importo.'&nbsp;&euro;</td>';
	$tmp .= '<td>'.$tot_importo_pagato.'&nbsp;&euro;</td>';
	$tmp .= '<td></td>';
	$tmp .= '<td></td>';
	$tmp .= '<td></td>';
	$tmp .= '</tr>';
			
	$tmp .= '</table></div>';
}
else
	$tmp .= $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "L'ordine non ha acquisti"));
			
echo $tmp;
?>