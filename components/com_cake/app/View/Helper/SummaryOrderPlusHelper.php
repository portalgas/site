<?php
class SummaryOrderPlusHelper extends AppHelper {
	
	var $helpers = array('Html','Time','ExportDocs');
	
	public function draw($user, $model, $results, $debug=false) {

		self::d($results, false);
		
		switch ($model) {
			case 'SummaryOrderTrasport':
				$prefix_order = 'trasport';	
				$prefix = 'trasport_importo';  // e' il valore calcolare a runtime
				$prefix_percentuali = 'trasport_percentuale';
				$label_importo_calcolato_user = __('importo_calcolato_user_trasport');
				$label_summary_order_plus = __('Trasport');					
			break;
			case 'SummaryOrderCostMore':
				$prefix_order = 'cost_more';
				$prefix = 'cost_more_importo';  // e' il valore calcolare a runtime
				$prefix_percentuali = 'cost_more_percentuale';
				$label_importo_calcolato_user = __('importo_calcolato_user_cost_more');
				$label_summary_order_plus = __('CostMore');				
			break;
			case 'SummaryOrderCostLess':
				$prefix_order = 'cost_less';					
				$prefix = 'cost_less_importo';  // e' il valore calcolare a runtime
				$prefix_percentuali = 'cost_less_percentuale';
				$label_importo_calcolato_user = __('importo_calcolato_user_cost_less');
				$label_summary_order_plus = __('CostLess');
			break;
			default:
				die("SummaryOrderPlusHelper::draw model $model non valido");
			break;			
		}
				
		$tmp  = '';

		if($results['Delivery']['totOrders'] > 0) {
			$i=0;
			$tot_importo_originale=0;
			$tot_summary_order_plus_percentuale=0;
			$tot_summary_order_plus_importo=0;
			$tot_importo=0;
	
			foreach($results['Delivery'][0]['Order'] as $numOrder => $order) {	

				$importo_order = $order['Order'][$prefix_order];

				$i++;
		
				/*
		 		 * S U P P L I E R S _ O R G A N I Z A T I O N
		 		 */
				$tmp .= $this->ExportDocs->suppliersOrganizationShort($order['SuppliersOrganization']);

				if(isset($order[$model])) {
					
					$tmp .= '<div class="table-responsive"><table class="table table-hover table-striped">';
					$tmp .= '		<tr>';
					$tmp .= '			<th rowspan="2">'.__('N').'</th>';
					$tmp .= '			<th rowspan="2">'.__('User').'</th>';
					$tmp .= '			<th rowspan="2" style="text-align:center;">'.__('importo_originale').'</th>';
					$tmp .= '			<th rowspan="2" style="width:1px;"></th>';
					$tmp .= '			<th rowspan="2" style="text-align:center;">'.$label_importo_calcolato_user.'</th>';
					$tmp .= '			<th colspan="2" style="text-align:center;">'.$label_summary_order_plus.'</th>';
					$tmp .= '	<tr>';
					$tmp .= '			<th style="text-align:center;">'.__('Importo').'</th>';
					$tmp .= '			<th style="text-align:center;">%</th>';
					$tmp .= '	</tr>';		
					$tmp .= '	</tr>';		
			
					$tot_importo_non_aggiornati = 0;
					foreach($order[$model] as $numResult => $result) {
				
						if($numResult==0) 
							self::d($result, false);
						
						$i++;
						$rowId = $result[$model]['id'];
						
						if($result['SummaryOrder']['saldato_a']==null)
							$saldato = false;
						else
							$saldato = true;
							
						/*
						 * visualizzo l'importo calcolato a runtime o quello salvato
						 */
						$importo = number_format($result[$model][$prefix],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
							
						$tmp .= "\r\n";
						$tmp .= '<tr>';
						$tmp .= '	<td>'.((int)$numResult+1).'</td>';
						$tmp .= '	<td>'.$result['User']['name'].'</td>';
						$tmp .= '	<td style="text-align:center;">'.$result['User']['totImporto_e'].'</td>';
						$tmp .= '	<td style="';
						if($result['User']['totImporto_']!=$result[$model]['importo_']) {
							$tmp .= 'background-color:red;';
							$tot_importo_non_aggiornati++;
						}	
						$tmp .= '"></td>';
						$tmp .= '	<td style="text-align:center;">'.$result[$model]['importo_e'].'</td>';
						$tmp .= "\n";
						$tmp .= '	<td style="text-align:center;white-space: nowrap;">';
						if(!$saldato) {
							if($model=='SummaryOrderCostLess')
								$css_class = '';
							else
								$css_class = 'double';
							$tmp .= '<input tabindex="'.$i.'" type="text" value="'.$importo.'" name="data[Data]['.$result[$model]['user_id'].']" class="importo '.$css_class.' summary_order_plus_importo form-control" style="display:inline" />&nbsp;<span>&euro;</span>';
						}
						else {
							$tmp .= '<div class="alert alert-info alert-dismissable">'; 
							$tmp .= sprintf(__('msg_summary_order_just_saldato_a'), strtolower($result['SummaryOrder']['saldato_a'])).' '.$result['SummaryOrder']['importo_pagato_e'];
							$tmp .= '</div>';
						}										
						$tmp .= '   </td>';
						
						$tmp .= '	<td style="text-align:center;" class="summary_order_plus_percentuale">';
						if(!$saldato)
							if($result[$model][$prefix_percentuali ]>0)
								$tmp .= $result[$model][$prefix_percentuali ].'&nbsp;%';
						$tmp .= '</td>';
						$tmp .= '</tr>';
		
					$tot_importo_originale += $result['User']['totImporto'];
					$tot_importo += $result[$model]['importo'];
					$tot_summary_order_plus_importo += $result[$model][$prefix];
					$tot_summary_order_plus_percentuale += $result[$model][$prefix_percentuali];
			} // end foreach
		
			/*
			 * totali
			 */
			$tot_importo_originale_e = number_format($tot_importo_originale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'; 
			$tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$tot_summary_order_plus_importo = number_format($tot_summary_order_plus_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$importo_order  = number_format($importo_order,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

			self::d('tot_summary_order_plus_importo '.$tot_summary_order_plus_importo,false);
			self::d('tot_importo '.$tot_importo,false);
			self::d('importo_order '.$importo_order,false);
			
			$tmp .= "\r\n";
			$tmp .= '<tr>';
			$tmp .= '	<td></td>';
			$tmp .= '	<td style="text-align:right;font-weight:bold;">Totale</td>';
			$tmp .= '	<td style="text-align:center;">'.$tot_importo_originale_e.'</td>';
			$tmp .= '	<td style="text-align:center;"></td>';
			$tmp .= '	<td style="text-align:center;">'.$tot_importo.'&nbsp;&euro;</td>';
			$tmp .= '	<td style="text-align:center;"><span id="tot_summary_order_plus_importo"></span>&nbsp;&euro;</td>'; // $tot_summary_order_plus_importo lo calcola il js
			$tmp .= '	<td style="text-align:center;" class="tot_summary_order_plus_percentuale">';
			if($result[$model][$prefix_percentuali]>0)
				$tmp .= $tot_summary_order_plus_percentuale.'&nbsp;%';
			$tmp .= '</td>';
			$tmp .= '</tr>';

			/*
			 * differenza
			 */
			//if($tot_summary_order_plus_importo != $importo_order) {
				$diff_importo = round(((-1 * ($importo_order - $tot_summary_order_plus_importo))),2);
				$diff_percentuale = round((-1 * (100 - $tot_summary_order_plus_percentuale)),2);
				
				$tmp .= "\r\n";
				$tmp .= '<tr>';
				$tmp .= '	<td></td>';
				$tmp .= '	<td style="text-align:right;font-weight:bold;">Differenza</td>';
				$tmp .= '	<td style="text-align:center;"></td>';
				$tmp .= '	<td style="text-align:center;"></td>';
				$tmp .= '	<td style="text-align:center;"></td>';
				$tmp .= '	<td class="cake-debug" style="text-align:center;"><span id="diff_importo"></span>&nbsp;&euro;</td>';  // $diff_importo lo calcola il js
				if($tot_summary_order_plus_percentuale>0)
					$tmp .= '	<td class="cake-debug diff_percentuale" style="text-align:center;">'.$diff_percentuale.'&nbsp;%</td>';
				else
					$tmp .= '	<td></td>';
				$tmp .= '</tr>';
			//}
							
			$tmp .= '</table></div>';
			
			if($tot_importo_non_aggiornati>0) {
				$tmp .= $this->_View->element('boxMsg',array('class_msg' => 'danger', 'msg' => "Alcuni calcoli (quelli evidenziati in rosso) si riferiscono a dati che sono stati modificati, clicca su 'Aggiorna l'importo del ".$label_summary_order_plus."' per ricalcolarli."));
			}

			/*
			 * S U P P L I E R S _ O R G A N I Z A T I O N S _ R E F E R E N T S
			*/
			$tmp .= $this->ExportDocs->suppliersOrganizationsReferent($order['SuppliersOrganizationsReferent']);
	
		}
		else
			$tmp .= $this->_View->element('boxMsg',array('class_msg' => 'message', 'msg' => "L'ordine non ha acquisti"));
		
	} // end ciclo orders
} // end if($results['Delivery']['totOrders'] > 0) 

		$tmp .= '<script type="text/javascript">';
		$tmp .= '$(document).ready(function() {';
		$tmp .= '	$(".double").focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */';
		$tmp .= '   $("#importo_order").focusout(function() {';
		$tmp .= '		validateNumberPositiveField(this,"importo");});';
		$tmp .= '		$(".double").focusout(function() {validateNumberPositiveField(this,"importo");';
		$tmp .= '	});'."\n";
		$tmp .= '	$(".importo").focusout(function() {';
		$tmp .= '		setTotSummaryOrderPlusImporto();';
		$tmp .= '		$(".summary_order_plus_percentuale").html("");';
		$tmp .= '		$(".tot_summary_order_plus_percentuale").html("");';
		$tmp .= '		$(".diff_percentuale").html("");';
		$tmp .= '	});'."\n";
		$tmp .= '	setTotSummaryOrderPlusImporto();';
		$tmp .= '});'."\n";
		$tmp .= 'function setTotSummaryOrderPlusImporto() {';
		$tmp .= '	var importo_order = numberToJs("'.$importo_order.'");';
		$tmp .= '	var tot_summary_order_plus_importo = 0;';
		$tmp .= '	$(".summary_order_plus_importo").each(function () {';
		$tmp .= '		var importo = $(this).val();';
		$tmp .= '		importo = numberToJs(importo);   /* in 1000.50 */	';
		$tmp .= '		tot_summary_order_plus_importo = (parseFloat(tot_summary_order_plus_importo) + parseFloat(importo));';
		$tmp .= '	});'."\n";
		
		if($model=='SummaryOrderCostLess') // e' uno sconto => negativo
			$tmp .= 'tot_summary_order_plus_importo = (-1 * tot_summary_order_plus_importo);'."\n";
		
		$tmp .= '	diff_importo = (-1 * (parseFloat(importo_order) - parseFloat(tot_summary_order_plus_importo)));';
		if($debug) {
			$tmp .= '	console.log("tot_summary_order_plus_importo "+tot_summary_order_plus_importo);'."\n";
			$tmp .= '	console.log("diff_importo "+diff_importo);'."\n";
		}
		$tmp .= '   tot_summary_order_plus_importo = number_format(tot_summary_order_plus_importo,2,\',\',\'.\');  /* in 1.000,50 */';
		$tmp .= '   diff_importo = number_format(diff_importo,2,\',\',\'.\');  /* in 1.000,50 */'."\n";
		$tmp .= '	$("#tot_summary_order_plus_importo").html(tot_summary_order_plus_importo);	';
		$tmp .= '   $("#diff_importo").html(diff_importo);'."\n";
		$tmp .= '}'."\n";
		$tmp .= '</script>';

		return $tmp;
	}
}
?>