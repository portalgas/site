<?php
$tmp = '';

$tmp .= $this->ExportDocs->delivery($results['Delivery'][0]);

$tmp .= '<div class="clearfix"></div>';
	
if($results['Delivery']['totOrders'] > 0) {
	$i=0;
	$tot_trasporto_percentuale=0;
	$tot_trasporto_importo=0;
	$tot_importo=0;
	foreach($results['Delivery'][0]['Order'] as $numOrder => $order) {	

		$trasport = $order['Order']['trasport'];

		$i++;
		
		/*
		 * S U P P L I E R S _ O R G A N I Z A T I O N
		 */
		$tmp .= $this->ExportDocs->suppliersOrganizationShort($order['SuppliersOrganization']);

		if(isset($order['SummaryOrderTrasport'])) {
					
			$tmp .= '	<table>';
			$tmp .= '		<tr>';
			$tmp .= '			<th rowspan="2">'.__('N').'</th>';
			$tmp .= '			<th rowspan="2">'.__('User').'</th>';
			$tmp .= '			<th rowspan="2" style="text-align:center;">'.__('importo_aggregato_user').'</th>';
			$tmp .= '			<th colspan="2" style="text-align:center;">'.__('Trasport').'</th>';
			$tmp .= '	<tr>';
			$tmp .= '			<th style="text-align:center;">'.__('Importo').'</th>';
			$tmp .= '			<th style="text-align:center;">%</th>';
			$tmp .= '	</tr>';		
			$tmp .= '	</tr>';		
						
			foreach($order['SummaryOrderTrasport'] as $numResult => $SummaryOrderTrasport) {
				
				$i++;
				$rowId = $SummaryOrderTrasport['SummaryOrderTrasport']['id'];
				
				if($SummaryOrderTrasport['SummaryOrderTrasport']['trasporto_importo']>0)
					$trasporto_importo = number_format($SummaryOrderTrasport['SummaryOrderTrasport']['trasporto_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				else 
					$trasporto_importo = '0,00';
					
				$tmp .= "\r\n";
				$tmp .= '<tr>';
				$tmp .= '	<td>'.($numResult+1).'</td>';
				$tmp .= '	<td>'.$SummaryOrderTrasport['User']['name'].'</td>';
				$tmp .= '	<td style="text-align:center;">'.$SummaryOrderTrasport['SummaryOrderTrasport']['importo_e'].'</td>';				
				$tmp .= "\n";
				$tmp .= '	<td style="text-align:center;">';							
				$tmp .= '	   <input tabindex="'.$i.'" type="text" value="'.$trasporto_importo.'" name="data[Data]['.$SummaryOrderTrasport['SummaryOrderTrasport']['user_id'].']" size="5" class="importo double trasporto_importo" />&nbsp;<span>&euro;</span>';
				$tmp .= '   </td>';
				$tmp .= '	<td style="text-align:center;" class="trasporto_percentuale">';
				if($SummaryOrderTrasport['SummaryOrderTrasport']['trasporto_percentuale']>0)
					$tmp .= $SummaryOrderTrasport['SummaryOrderTrasport']['trasporto_percentuale'].'&nbsp;%';
				$tmp .= '</td>';
				$tmp .= '</tr>';
		
				$tot_importo += $SummaryOrderTrasport['SummaryOrderTrasport']['importo'];
				$tot_trasporto_importo += $SummaryOrderTrasport['SummaryOrderTrasport']['trasporto_importo'];
				$tot_trasporto_percentuale += $SummaryOrderTrasport['SummaryOrderTrasport']['trasporto_percentuale'];
			}
		
			/*
			 * totali
			 */
			$tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$tot_trasporto_importo = number_format($tot_trasporto_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));			$trasport  = number_format($trasport,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			
			$tmp .= "\r\n";
			$tmp .= '<tr>';
			$tmp .= '	<td></td>';
			$tmp .= '	<td style="text-align:right;font-weight:bold;">Totale</td>';
			$tmp .= '	<td style="text-align:center;">'.$tot_importo.'&nbsp;&euro;</td>';
			$tmp .= '	<td style="text-align:center;"><span id="tot_trasporto_importo"></span>&nbsp;&euro;</td>'; // $tot_trasporto_importo lo calcola il js
			$tmp .= '	<td style="text-align:center;" class="tot_trasporto_percentuale">';
			if($SummaryOrderTrasport['SummaryOrderTrasport']['trasporto_percentuale']>0)
				$tmp .= $tot_trasporto_percentuale.'&nbsp;%';
			$tmp .= '</td>';
			$tmp .= '</tr>';

			/*
			 * differenza
			 */
			//if($tot_trasporto_importo != $trasport) {
				$diff_importo = round(((-1 * ($trasport - $tot_trasporto_importo))),2);
				$diff_percentuale = round((-1 * (100 - $tot_trasporto_percentuale)),2);
				
				$tmp .= "\r\n";				$tmp .= '<tr>';				$tmp .= '	<td></td>';				$tmp .= '	<td style="text-align:right;font-weight:bold;">Differenza</td>';				$tmp .= '	<td style="text-align:center;"></td>';				$tmp .= '	<td class="cake-debug" style="text-align:center;"><span id="diff_importo"></span>&nbsp;&euro;</td>';  // $diff_importo lo calcola il js				if($tot_trasporto_percentuale>0)
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

	jQuery('#trasport').focusout(function() {
		validateNumberPositiveField(this,'importo trasporto');});
		jQuery('.double').focusout(function() {validateNumberPositiveField(this,'importo trasporot');
	});
		
	jQuery('.trasporto_importo').focusout(function() {
		setTotTrasportoImporto();
		
		jQuery('.trasporto_percentuale').html("");
		jQuery('.tot_trasporto_percentuale').html("");
		jQuery('.diff_percentuale').html("");
	});
	
	setTotTrasportoImporto();
});

function setTotTrasportoImporto() {

	var order_trasport = numberToJs("<?php echo $trasport;?>");
	
	var tot_trasporto_importo = 0;
	jQuery(".trasporto_importo").each(function () {
		var importo = jQuery(this).val();
		
		importo = numberToJs(importo);   /* in 1000.50 */
			
		tot_trasporto_importo = (parseFloat(tot_trasporto_importo) + parseFloat(importo));
	});
	
	diff_importo = (-1 * (parseFloat(order_trasport) - parseFloat(tot_trasporto_importo)));
	/* console.log("tot_trasporto_importo "+tot_trasporto_importo); */
	/* console.log("diff_importo "+diff_importo); */

	tot_trasporto_importo = number_format(tot_trasporto_importo,2,',','.');  /* in 1.000,50 */
	diff_importo = number_format(diff_importo,2,',','.');  /* in 1.000,50 */
		
	jQuery('#tot_trasporto_importo').html(tot_trasporto_importo);	
	
	jQuery('#diff_importo').html(diff_importo);	
}
</script>