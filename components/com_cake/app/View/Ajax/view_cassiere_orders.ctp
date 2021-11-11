<?php		
$this->App->d($results);
	
if(!empty($results) {
						
			foreach($results as $numResult => $result) {
				
				$i++;
				$rowId = $result['SummaryOrder']['id'];
								
				$tmp .= "\r\n";
				$tmp .= '<tr>';
				$tmp .= '	<td>'.((int)$numResult+1).'</td>';
				$tmp .= '	<td>'.$result['User']['name'];
				if(!empty($result['User']['email']))
				$tmp .= ' <a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a>';
				if(!empty($result['User']['Profile']['phone'])) $tmp .= ' '.$result['User']['Profile']['phone'].'<br />';
				if(!empty($result['User']['Profile']['phone2'])) $tmp .= ' '.$result['User']['Profile']['phone2'];
				$tmp .= '	</td>';
						
				$tmp .= '<td>'.$result['SummaryOrder']['importo_e'].'</td>';
					
				$tmp .= '<td style="white-space: nowrap;">';	
				$tmp .= '	<input tabindex="'.$i.'" type="text" value="'.$result['SummaryOrder']['importo_'].'" name="importo-'.$rowId.'" id="importo-'.$rowId.'" style="display:inline" class="double importoSubmit" />&nbsp;<span>&euro;</span>';
				$tmp .= '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$rowId.'" class="buttonCarrello submitEcomm" />';
				$tmp .= '<div id="msgEcomm-'.$rowId.'" class="msgEcomm"></div>';
				$tmp .= '</td>';
				
				//$tmp .= '	<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['SummaryOrder']['created']).'</td>';
				//$tmp .= '	<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['SummaryOrder']['modified']).'</td>';
				$tmp .= '	<td class="actions-table-img">';
				$tmp .= $this->Html->link(null, '',array('id' => 'delete-'.$rowId, 'class' => 'action actionDelete delete', 'title' => __('Delete')));			
				$tmp .= '	</td>';
				$tmp .= '</tr>';
		
				$tot_importo += $result['SummaryOrder']['importo'];
			}
		
			/*
			 * totali, lo calcolo in modo dinamico
			 */
			$tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				
			$tmp .= "\r\n";
			$tmp .= '<tr>';
			$tmp .= '	<td></td>';
			$tmp .= '	<td style="font-size: 16px;text-align:right;font-weight: bold;">Totale</td>';
			$tmp .= '	<td>'.$tot_importo.'</td>';
			$tmp .= '	<td style="font-size: 16px;"><span id="tot_importo"></span>&nbsp;&euro;</td>';
			$tmp .= '	<td></td>';
			$tmp .= '</tr>';
					
			$tmp .= '</table>';
		}
}
else
	$tmp .= $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "L'ordine non ha acquisti"));
			
echo $tmp;
?>