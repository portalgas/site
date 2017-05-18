<?php
$debug = false; 
if($debug) {
	$inputType = 'text';
	$debugJs = 'true';
}	
else { 
	$inputType = 'hidden';
	$debugJs = 'false';
}

/*
echo "<pre>";
print_r($results);
echo "</pre>";
*/
if(!empty($results)) {
	
	echo $this->element('boxCashUserTotaleImporto', array('cashResults' => $cashResults));
	
	echo '<table class="selector">';
	echo '	<tr>';
	echo '		<th>'.__('N').'</th>';
	echo '		<th style="width:20%">'.__('Stato').'</th>';
	echo '		<th style="width:20%">'.__('Supplier').'</th>';
	echo '		<th>Importo dovuto</th>';
	echo '		<th>Modalit&agrave;&nbsp;';
	echo '<select name="modalita_all" size="1">';
	echo '<option value="" selected>Applica la modalit&agrave; a tutti</option>';
	foreach($modalita as $key => $value)
		echo '<option value="'.$key.'">'.$this->App->traslateEnum($key).'</option>';
	echo '</select>';	
	echo '</th>';
	echo '</tr>';

	$tot_importo_degli_ordini_da_pagare = 0;
	$differenza_in_cassa = 0;
	if(empty($cashResults['Cash']['importo']))
		$tot_importo_cassa = '0.00';
	else
		$tot_importo_cassa = $cashResults['Cash']['importo'];
	
	$tot_orders_validi = 0; /* si possono gestire con la cassa solo Order.state_code = 'PROCESSED-ON-DELIVERY' */
	foreach($results as $numResult => $result) {
		
		$i++;
		$rowId = $result['SummaryOrder']['id'];
		
		echo '<tr>';

		echo '<td>'.($numResult+1).'</td>';
		echo '<td>'.$this->App->drawOrdersStateDiv($result).'&nbsp;'.__($result['Order']['state_code'].'-label').'</td>';
		echo '<td>';
		if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
			echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
		echo $result['SuppliersOrganization']['name'];
		echo '</td>';

		/*
		 *  ordine gia' pagato 
		 */		
		if($result['SummaryOrder']['importo']==$result['SummaryOrder']['importo_pagato']) 
			echo '<td>0,00 &euro;</td>';
		else
			echo '<td>'.$result['SummaryOrder']['importo_e'].'</td>';

		if($result['Order']['state_code']=='PROCESSED-ON-DELIVERY') {
			
			/*
			 *  ordine gia' pagato 
			 */
			if($result['SummaryOrder']['importo']==$result['SummaryOrder']['importo_pagato']) {
				echo '	<td>';
				echo 'Saldato '.$result['SummaryOrder']['importo_pagato_e'].' in '.$this->App->traslateEnum($result['SummaryOrder']['modalita']);
				echo '</td>';			
			}
			else {
				$tot_orders_validi++;
				
				/*
				 * echo '<td style="width:32px;">';
				 * echo $this->Html->link(null, array() ,array('class' => 'action actionCopy', 'title' => __('Copy'), 'id' => $rowId, 'importo' => $result['SummaryOrder']['importo_']));
				 * echo '</td>';
				 */
	
				/*
				 * echo '<td>';
				 * echo '<input tabindex="'.$i.'" name="data[Cassiere]['.$rowId.'][importo_pagato]" id="importo_pagato-'.$rowId.'" type="text" value="'.$result['SummaryOrder']['importo_pagato_'].'" size="5" class="double importoSubmit" />&nbsp;<span>&euro;</span>';		
				 * echo '</td>';
				 */
				echo '	<td>';
				
				echo '<input type="hidden" name="data[Cassiere]['.$rowId.'][order_id]" id="order_id-'.$rowId.'" value="'.$result['Order']['id'].'" />';
				echo '<input type="hidden" name="data[Cassiere]['.$rowId.'][importo]" value="'.$result['SummaryOrder']['importo'].'" />';
				
				if($result['SummaryOrder']['modalita']=='DEFINED')
					$result['SummaryOrder']['modalita'] = 'CONTANTI';
				foreach($modalita as $key => $value) {
					echo '<label for="modalita'.$key.'-'.$rowId.'" style="width:auto !important;">'.$this->App->traslateEnum($key).'</label>';
					echo '<input type="radio" class="modalita" name="data[Cassiere]['.$rowId.'][modalita]" id="modalita'.$key.'-'.$rowId.'" value="'.$key.'" ';
					if($key==$result['SummaryOrder']['modalita']) echo ' checked="checked" ';
					echo '/>';
				}
				echo '</td>';
			}	// end if ordine gia' saldato	
		}
		else {
			echo '<td></td>';
		}
					
		echo '</tr>';	

		$tot_importo_degli_ordini_da_pagare += $result['SummaryOrder']['importo'];

	} // end foreach($results as $numResult => $result)
	
	/*
	 * totali, lo calcolo in modo dinamico
	*/ 
	$tot_importo_da_pagare = ($tot_importo_degli_ordini_da_pagare - ($tot_importo_cassa));
	if($tot_importo_da_pagare < 0)
            $tot_importo_da_pagare = $tot_importo_degli_ordini_da_pagare;
        
	if($tot_orders_validi>0) {
		echo '<tr>';
		echo '	<td> </td>';
		echo '	<td colspan="2" style="font-size: 16px;text-align:right;font-weight: bold;">Totale</td>';
		echo '	<td>'.number_format($tot_importo_degli_ordini_da_pagare,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
		
			/*
			echo '<td style="width:32px;">';
			echo $this->Html->link(null, array() ,array('class' => 'action actionCopy', 'title' => __('Copy'), 'id' => 'totale_importo_pagato', 'importo' => $tot_importo));
			echo '</td>';	
			echo '	<td>';
			echo '<span id="tot_importo_pagato"></span>&nbsp;&euro;';
			echo '</td>';
			*/
		echo '	<td></td>';
		echo '</tr>';
		
		/*
		 * cassa
		 */
		echo '<tr>';
		echo '	<td> </td>';
		echo '	<td colspan="2" style="font-size: 16px;text-align:right;font-weight: bold;">Cassa</td>';
		echo '	<td>'.number_format($tot_importo_cassa,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
		echo '	<td>';
		echo '<input type="checkbox" checked="checked" name="cash_options" id="cash_options" value="Y" /> Prendi in considerazione la cassa';
		echo '</td>';
		echo '</tr>';	

		
		echo '<tr class="cassa_nota">';
		echo '	<td> </td>';
		echo '	<td colspan="2" style="font-size: 16px;text-align:right;font-weight: bold;">Nota Cassa</td>';
		echo '	<td colspan="2">';
		echo '<textarea rows="3" cols="75" name="cash_text" id="cash_text"></textarea>';
		echo '	</td>';
		echo '</tr>';
		
		/*
		 * totale completo
		*/
		echo '<tr>';
		echo '	<td> </td>';
		echo '	<td colspan="2" style="font-size: 16px;text-align:right;font-weight: bold;">Totale da pagare</td>';
		echo '	<td>';
				 	
		if($debug) echo '<br />tot_importo_degli_ordini_da_pagare'; 
		echo '<input type="'.$inputType.'" name="tot_importo_degli_ordini_da_pagare" id="tot_importo_degli_ordini_da_pagare" value="'.number_format($tot_importo_degli_ordini_da_pagare,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'" />';
		
		if($debug) echo '<br />tot_importo_cassa';
		echo '<input type="'.$inputType.'" name="tot_importo_cassa" id="tot_importo_cassa" value="'.number_format($tot_importo_cassa,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'" />';
		
		if($debug) echo '<br />tot_importo_da_pagare_orig';
		echo '<input type="'.$inputType.'" name="tot_importo_da_pagare_orig" id="tot_importo_da_pagare_orig" value="'.number_format($tot_importo_da_pagare,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'" />';
		
		if($debug) echo '<br />differenza_in_cassa';
		echo '<input type="'.$inputType.'" name="differenza_in_cassa" id="differenza_in_cassa" value="'.$differenza_in_cassa.'" />';
		
		
			
		echo '<input tabindex="'.$i.'" name="data[Cassiere][tot_importo_da_pagare]" id="tot_importo_da_pagare" type="text" value="'.number_format($tot_importo_da_pagare,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'" size="7" class="double importoSubmit" />&nbsp;<span>&euro;</span>';
		echo '</td>';
		echo '<td></td>';
		echo '</tr>';
		
		/*
		 * differenza cassa
		*/
		echo '<tr>';
		echo '	<td> </td>';
		echo '	<td colspan="2" style="font-size: 16px;text-align:right;font-weight: bold;">';
		echo '<span class="cassa">Differenza in cassa</span></td>';
		echo '	<td>';
		echo '<span id="differenza" class="cassa"></span>';
		echo '</td>';
		echo '	<td> </td>';
		echo '</tr>';

		/*
		 * POS
		 */
		if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
	
			if(!empty($summaryDeliveriesPosResults['SummaryDeliveriesPos']['importo']))  {
				echo '<tr>';
				echo '	<td> </td>';
				echo '	<td colspan="2" style="font-size: 16px;text-align:right;font-weight: bold;">';
				echo "Commissione POS";
				echo '<td>';
				echo ' ('.sprintf(Configure::read('label_payment_pos'), $summaryDeliveriesPosResults['SummaryDeliveriesPos']['importo_e']).')';
				echo '</td>';
				echo '	<td> </td>';
				echo '</tr>';
			}
			else {
				echo '<tr style="display:none;" class="bancomat">';
				echo '	<td> </td>';
				echo '	<td colspan="2" style="font-size: 16px;text-align:right;font-weight: bold;">';
				echo "Commissione POS";
				echo '<td>';
				echo '<input type="text" class="double" size="7" name="data[Cassiere]['.$rowId.'][paymentPos]" id="data[Cassiere]['.$rowId.'][paymentPos]" value="'.$user->organization['Organization']['paymentPos'].'" /> &euro;';
				echo '</td>';
				echo '	<td> </td>';
				echo '</tr>';
			}
		}
		
		echo '<tr>';
		echo '<td></td>';
		echo '<td colspan="4">';
		echo '	<div class="submit"><input type="submit" value="'.__('Submit').'" /></div>';
		echo '</td>';
		echo '</tr>';
				
	} // end if($tot_orders_validi>0) 

			
	echo '</table>';
	
	if($tot_orders_validi==0) {
		echo '<div class="box-message">';
		echo '<div class="message" id="flashMessage">Non ci sono ordini "In carico al cassiere durante la consegna" da poter gestire</div>';
		echo '</div>';
	}
	
}
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	
	jQuery('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
	
	choiceUserCash();

	jQuery('.actionCopy_disabled').click(function() {

		var id = jQuery(this).attr('id');
		var importo = jQuery(this).attr('importo');

		if(id=='totale_importo_pagato') {
			jQuery("#tot_importo_pagato").html(importo);
			
			jQuery(".actionCopy").each(function () {
				var id = jQuery(this).attr('id');

				if(id!='totale_importo_pagato') {
					var importo = jQuery(this).attr('importo');
					jQuery("#importo_pagato-"+id).val(importo);
				}
			});
		}
		else {
			jQuery("#importo_pagato-"+id).val(importo);
			setTotImportoPagato();	
		}
		
		return false;
	});
	
	/*
	 * importo
	 */
	jQuery('#tot_importo_da_pagare').focusout(function() {

		setNumberFormat(this);
		var importo = jQuery(this).val();
		console.log("tot_importo_da_pagare.focusout() "+importo);
		if(importo=='' || importo==undefined) {
			alert("Devi indicare l'importo");
			jQuery(this).val("0,00");
			jQuery(this).focus();
			return false;
		}	
		
		/*
		 * tolgo il vincolo cosi' posso avere importi che creano un debito in cassa
		if(importo=='0,00') {
			alert("L'importo dev'essere indicato con un valore maggior di 0");
			jQuery(this).focus();
			return false;
		}
		*/
		setDifferenzaCassa();
	});

	jQuery("select[name='modalita_all']").change(function () {
		var modalita = jQuery(this).val();

		if(modalita!="") settaModalita(modalita);
	});

	<?php
	if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
	?>
	jQuery(".modalita").change(function () {
		var modalita = jQuery(this).val();
		
		if(modalita=="BANCOMAT") 
			jQuery('.bancomat').show();
		else
			jQuery('.bancomat').hide();
	});
	<?php
	}
	?>	
		
	jQuery("input[name='cash_options']").change(function() {	

		var debug = <?php echo $debugJs;?>;
		
		var cash_options = jQuery("input[name='cash_options']:checked").val();
		var tot_importo_da_pagare = jQuery('#tot_importo_degli_ordini_da_pagare').val();
		var tot_importo_cassa = jQuery('#tot_importo_cassa').val();
		
		if(debug) {
			console.log("tot_importo_da_pagare "+tot_importo_da_pagare);
			console.log("tot_importo_cassa "+tot_importo_cassa);
		}
			
		if(cash_options!='Y') {
			jQuery('.cassa').hide();
			jQuery('.cassa_nota').hide();
			jQuery('#tot_importo_da_pagare').val(tot_importo_da_pagare);
		}
		else {
			jQuery('.cassa').show();
			jQuery('.cassa_nota').show();
			
			setImportoConCassa();
			
			setDifferenzaCassa();
		}
	});	
	
	setDifferenzaCassa();
	
	jQuery('#formGas').submit(function() {
	
		var tot_importo_da_pagare = jQuery('#tot_importo_da_pagare').val();
		/*
		 * tolgo il vincolo cosi' posso avere importi che creano un debito in cassa
		 * if(tot_importo_da_pagare=='' || tot_importo_da_pagare==undefined || tot_importo_da_pagare=='0.00' || tot_importo_da_pagare=='0,00') 
		 */ 
		console.log("tot_importo_da_pagare.submit() "+tot_importo_da_pagare); 
		if(tot_importo_da_pagare=='' || tot_importo_da_pagare==undefined) 
		{
			alert("Devi indicare l'importo");
			jQuery(this).val("0,00");
			jQuery(this).focus();
			return false;
		}	
			 
		return true;
	});	
});

function setImportoConCassa() {

		var debug = <?php echo $debugJs;?>;
		
		var cash_options = jQuery("input[name='cash_options']:checked").val();
		var tot_importo_da_pagare = jQuery('#tot_importo_degli_ordini_da_pagare').val();
		var tot_importo_cassa = jQuery('#tot_importo_cassa').val();
		
		if(debug) {
			console.log("setImportoConCassa() - tot_importo_da_pagare "+tot_importo_da_pagare);
			console.log("setImportoConCassa() - tot_importo_cassa "+tot_importo_cassa);
		}
		
		tot_importo_da_pagare = numberToJs(tot_importo_da_pagare);  /* in 1000.50 */
		tot_importo_cassa = numberToJs(tot_importo_cassa);  /* in 1000.50 */
		
		if(debug) {
			console.log("setImportoConCassa() - tot_importo_da_pagare "+tot_importo_da_pagare);
			console.log("setImportoConCassa() - tot_importo_cassa "+tot_importo_cassa);
		}
		
		var tot_importo_da_pagare = 0;
		if(tot_importo_cassa > tot_importo_da_pagare)
			tot_importo_da_pagare = tot_importo_da_pagare; 
		else
			tot_importo_da_pagare = (tot_importo_da_pagare - (tot_importo_cassa)); 
		
		if(debug) 
			console.log("setImportoConCassa() - tot_importo_da_pagare "+tot_importo_da_pagare);
		
		tot_importo_da_pagare = number_format(tot_importo_da_pagare,2,',','.');  /* in 1.000,50 */ 

		if(debug) 
			console.log("setImportoConCassa() - tot_importo_da_pagare "+tot_importo_da_pagare);

		jQuery('#tot_importo_da_pagare').val(tot_importo_da_pagare);
}

function settaModalita(modalita) {
	jQuery(".modalita").each(function () {
		if(jQuery(this).attr('value')==modalita) 
			jQuery(this).prop('checked',true);
		else
			jQuery(this).prop('checked',false);
	});
}

function setDifferenzaCassa() {
	
	var debug = <?php echo $debugJs;?>;
	
	var tot_importo_da_pagare_orig = numberToJs(jQuery("#tot_importo_da_pagare_orig").val());   /* in 1000.50 */
	if(debug) console.log('setDifferenzaCassa() - numberToJs(tot_importo_da_pagare_orig) '+tot_importo_da_pagare_orig);
	
	var tot_importo_da_pagare = numberToJs(jQuery("#tot_importo_da_pagare").val());   /* in 1000.50 */
	if(debug) console.log('setDifferenzaCassa() - numberToJs(tot_importo_da_pagare) '+tot_importo_da_pagare);
	
	var tot_importo_cassa = numberToJs(jQuery("#tot_importo_cassa").val());   /* in 1000.50 */
	if(debug) console.log('setDifferenzaCassa() - tot_importo_cassa '+tot_importo_cassa);
	
	var tot_importo_degli_ordini_da_pagare = numberToJs(jQuery("#tot_importo_degli_ordini_da_pagare").val());   /* in 1000.50 */
	if(debug) console.log('setDifferenzaCassa() - tot_importo_degli_ordini_da_pagare '+tot_importo_degli_ordini_da_pagare);
		
	var differenza = '';
	if(parseFloat(tot_importo_degli_ordini_da_pagare) < parseFloat(tot_importo_cassa)) {
		differenza = (-1 * (tot_importo_degli_ordini_da_pagare - tot_importo_cassa));
	}
	else {
		if(tot_importo_da_pagare_orig!=tot_importo_da_pagare) {
			differenza = (-1 * sottrazione(tot_importo_da_pagare_orig, tot_importo_da_pagare));
			if(debug) console.log('setDifferenzaCassa() - differenza tot_importo_da_pagare_orig - tot_importo_da_pagare '+differenza);
		}
		else {
			differenza = (-1 * sottrazione(tot_importo_da_pagare_orig, tot_importo_da_pagare));
			if(debug) console.log('setDifferenzaCassa() - differenza tot_importo_da_pagare_orig - tot_importo_da_pagare '+differenza);
		}	
	}

	
	
	differenza = number_format(differenza,2,',','.');
	jQuery('#differenza').html(differenza+' &euro;');
	jQuery('#differenza_in_cassa').val(differenza);	
}

function setTotImportoPagato_disabled() {

	return;

	
	var tot_importo_pagato = 0;
	jQuery(".importoSubmit").each(function () {
		var importo_pagato = jQuery(this).val();
		
		importo_pagato = numberToJs(importo_pagato);   /* in 1000.50 */
			
		tot_importo_pagato = (parseFloat(tot_importo_pagato) + parseFloat(importo_pagato));
	});
	
	tot_importo_pagato = number_format(tot_importo_pagato,2,',','.');  /* in 1.000,50 */

	jQuery('#tot_importo_pagato').html(tot_importo_pagato);		
}
</script>