<?php
$tmp = '';

$tmp .= $this->ExportDocs->delivery($results['Delivery'][0]);

$tmp .= '<div class="clearfix"></div>';
	
if($results['Delivery']['totOrders'] > 0) {
	$i=0;
	$tot_cost_more_percentuale=0;
	$tot_cost_more_importo=0;
	$tot_importo=0;
	foreach($results['Delivery'][0]['Order'] as $numOrder => $order) {	

		$trasport = $order['Order']['cost_more'];

		$i++;
		
		/*
		 * S U P P L I E R S _ O R G A N I Z A T I O N
		 */
		$tmp .= $this->ExportDocs->suppliersOrganizationShort($order['SuppliersOrganization']);

		if(isset($order['SummaryOrderCostMore'])) {
					
			$tmp .= '	<table>';
			$tmp .= '		<tr>';
			$tmp .= '			<th rowspan="2">'.__('N').'</th>';
			$tmp .= '			<th rowspan="2">'.__('User').'</th>';
			$tmp .= '			<th rowspan="2" style="text-align:center;">'.__('importo_aggregato_user').'</th>';
			$tmp .= '			<th colspan="2" style="text-align:center;">'.__('CostMore').'</th>';
			$tmp .= '	<tr>';
			$tmp .= '			<th style="text-align:center;">'.__('Importo').'</th>';
			$tmp .= '			<th style="text-align:center;">%</th>';
			$tmp .= '	</tr>';		
			$tmp .= '	</tr>';		
						
			foreach($order['SummaryOrderCostMore'] as $numResult => $SummaryOrderCostMore) {
				
				$i++;
				$rowId = $SummaryOrderCostMore['SummaryOrderCostMore']['id'];
				
				if($SummaryOrderCostMore['SummaryOrderCostMore']['cost_more_importo']>0)
					$cost_more_importo = number_format($SummaryOrderCostMore['SummaryOrderCostMore']['cost_more_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				else 
					$cost_more_importo = '0,00';
					
				$tmp .= "\r\n";
				$tmp .= '<tr>';
				$tmp .= '	<td>'.($numResult+1).'</td>';
				$tmp .= '	<td>'.$SummaryOrderCostMore['User']['name'].'</td>';
				$tmp .= '	<td style="text-align:center;">'.$SummaryOrderCostMore['SummaryOrderCostMore']['importo_e'].'</td>';				
				$tmp .= "\n";
				$tmp .= '	<td style="text-align:center;">';							
				$tmp .= '	   <input tabindex="'.$i.'" type="text" value="'.$cost_more_importo.'" name="data[Data]['.$SummaryOrderCostMore['SummaryOrderCostMore']['user_id'].']" size="5" class="importo double cost_more_importo" />&nbsp;<span>&euro;</span>';
				$tmp .= '   </td>';
				$tmp .= '	<td style="text-align:center;" class="cost_more_percentuale">';
				if($SummaryOrderCostMore['SummaryOrderCostMore']['cost_more_percentuale']>0)
					$tmp .= $SummaryOrderCostMore['SummaryOrderCostMore']['cost_more_percentuale'].'&nbsp;%';
				$tmp .= '</td>';
				$tmp .= '</tr>';
		
				$tot_importo += $SummaryOrderCostMore['SummaryOrderCostMore']['importo'];
				$tot_cost_more_importo += $SummaryOrderCostMore['SummaryOrderCostMore']['cost_more_importo'];
				$tot_cost_more_percentuale += $SummaryOrderCostMore['SummaryOrderCostMore']['cost_more_percentuale'];
			}
		
			/*
			 * totali
			 */
			$tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$tot_cost_more_importo = number_format($tot_cost_more_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));			$cost_more  = number_format($cost_more,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			
			$tmp .= "\r\n";
			$tmp .= '<tr>';
			$tmp .= '	<td></td>';
			$tmp .= '	<td style="text-align:right;font-weight:bold;">Totale</td>';
			$tmp .= '	<td style="text-align:center;">'.$tot_importo.'&nbsp;&euro;</td>';
			$tmp .= '	<td style="text-align:center;"><span id="tot_cost_more_importo"></span>&nbsp;&euro;</td>'; // $tot_cost_more_importo lo calcola il js
			$tmp .= '	<td style="text-align:center;" class="tot_cost_more_percentuale">';
			if($SummaryOrderCostMore['SummaryOrderCostMore']['cost_more_percentuale']>0)
				$tmp .= $tot_cost_more_percentuale.'&nbsp;%';
			$tmp .= '</td>';
			$tmp .= '</tr>';

			/*
			 * differenza
			 */
			//if($tot_cost_more_importo != $cost_more) {
				$diff_importo = round(((-1 * ($cost_more - $tot_cost_more_importo))),2);
				$diff_percentuale = round((-1 * (100 - $tot_cost_more_percentuale)),2);
				
				$tmp .= "\r\n";				$tmp .= '<tr>';				$tmp .= '	<td></td>';				$tmp .= '	<td style="text-align:right;font-weight:bold;">Differenza</td>';				$tmp .= '	<td style="text-align:center;"></td>';				$tmp .= '	<td class="cake-debug" style="text-align:center;"><span id="diff_importo"></span>&nbsp;&euro;</td>';  // $diff_importo lo calcola il js				if($tot_cost_more_percentuale>0)
					$tmp .= '	<td class="cake-debug diff_percentuale" style="text-align:center;">'.$diff_percentuale.'&nbsp;%</td>';
				else
					$tmp .= '	<td></td>';
				$tmp .= '</tr>';			//}
							
			$tmp .= '</table>';
			

			/*
			 * S U P P L I E R S _ O R G A N I Z A T I O N S _ R E F E R E N T S
			*/
			$tmp .= $this->ExportDocs->suppliersOrganizationsReferent($order['SuppliersOrganizationsReferent']);
	
		}
		else
			$tmp .= $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "L'ordine non ha acquisti"));
		
	} // end ciclo orders
}

echo $tmp;
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */

	jQuery('#cost_more').focusout(function() {
		validateNumberPositiveField(this,'importo costo aggiuntivo');});
		jQuery('.double').focusout(function() {validateNumberPositiveField(this,'importo costo aggiuntivo');
	});
		
	jQuery('.cost_more_importo').focusout(function() {
		setTotCostMoreImporto();
		
		jQuery('.cost_more_percentuale').html("");
		jQuery('.tot_cost_more_percentuale').html("");
		jQuery('.diff_percentuale').html("");
	});
	
	setTotCostMoreImporto();
});

function setTotCostMoreImporto() {

	var order_cost_more = numberToJs("<?php echo $cost_more;?>");
	/*console.log("order_cost_more "+order_cost_more); */
	
	var tot_cost_more_importo = 0;
	jQuery(".cost_more_importo").each(function () {
		var importo = jQuery(this).val();
		
		importo = numberToJs(importo);   /* in 1000.50 */
			
		tot_cost_more_importo = (parseFloat(tot_cost_more_importo) + parseFloat(importo));
	});
	
	diff_importo = (-1 * (parseFloat(order_cost_more) - parseFloat(tot_cost_more_importo)));
	/*console.log("tot_cost_more_importo "+tot_cost_more_importo); */
	/*console.log("diff_importo "+diff_importo); */

	tot_cost_more_importo = number_format(tot_cost_more_importo,2,',','.');  /* in 1.000,50 */
	diff_importo = number_format(diff_importo,2,',','.');  /* in 1.000,50 */
		
	jQuery('#tot_cost_more_importo').html(tot_cost_more_importo);	
	
	jQuery('#diff_importo').html(diff_importo);	
}
</script>