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

$this->App->d([$results, $storeroomResults], $debug);

if(!empty($results) || !empty($storeroomResults)) {
	
	echo $this->element('boxCashUserTotaleImporto', array('cashResults' => $cashResults));
	
	echo '<div class="table-responsive"><table class="table table-hover table-striped">';
	echo '	<tr>';
	echo '		<th>'.__('N').'</th>';
	echo '		<th style="width:20%">'.__('Stato').'</th>';
	echo '		<th style="width:20%">'.__('Supplier').'</th>';
	echo '		<th>'.__('Importo_dovuto').'</th>';
	echo '		<th>'.__('Modality').'&nbsp;';
	echo '<select name="modalita_all" size="1" class="form-control">';
	echo '<option value="" selected>Applica la modalit&agrave; a tutti</option>';
	foreach($modalita as $key => $value)
		echo '<option value="'.$key.'">'.$this->App->traslateEnum($key).'</option>';
	echo '</select>';	
	echo '</th>';
	echo '</tr>';

	$tot_importo_dovuto = 0; // totale importo degli ordini da pagare
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
			echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
		echo $result['SuppliersOrganization']['name'];
		echo '</td>';

		/*
		 *  ordine gia' pagato 
		 */		
		if($result['SummaryOrder']['importo']==$result['SummaryOrder']['importo_pagato']) 
			echo '<td>0,00&nbsp;&euro;</td>';
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
				 * echo '<input tabindex="2" name="data[Cassiere]['.$rowId.'][importo_pagato]" id="importo_pagato-'.$rowId.'" type="text" value="'.$result['SummaryOrder']['importo_pagato_'].'" class="double importoSubmit form-control" />&nbsp;<span>&euro;</span>';		
				 * echo '</td>';
				 */
				echo '	<td>';
				
				echo '<input type="hidden" name="data[Cassiere]['.$rowId.'][order_id]" id="order_id-'.$rowId.'" value="'.$result['Order']['id'].'" />';
				echo '<input type="hidden" name="data[Cassiere]['.$rowId.'][importo]" value="'.$result['SummaryOrder']['importo'].'" />';
				
				if($result['SummaryOrder']['modalita']=='DEFINED')
					$result['SummaryOrder']['modalita'] = 'CONTANTI';
				foreach($modalita as $key => $value) {
					echo '<label class="radio-inline">';
					echo '<input type="radio" class="modalita" name="data[Cassiere]['.$rowId.'][modalita]" id="modalita'.$key.'-'.$rowId.'" value="'.$key.'" ';
					if($key==$result['SummaryOrder']['modalita']) echo ' checked="checked" ';
					echo '/> ';
					echo $this->App->traslateEnum($key).'</label> ';
				}
				echo '</td>';
			}	// end if ordine gia' saldato	
		}
		else {
			echo '<td></td>';
		}
					
		echo '</tr>';	

		if($result['SummaryOrder']['importo']!=$result['SummaryOrder']['importo_pagato']) // sommo solo se non pagato
			$tot_importo_dovuto += $result['SummaryOrder']['importo'];

	} // end foreach($results as $numResult => $result)

	/*
	 * D I S P E N S A
	 */
	$tot_qta_storeroom = 0;
	$tot_importo_storeroom = 0;
	if (isset($storeroomResults['Delivery'][0])) {

		$delivery = $storeroomResults['Delivery'][0];

		if ($delivery['totStorerooms']) {

			foreach ($delivery['Storeroom'] as $numStoreroom => $storeroom) {
						
				$tot_qta_storeroom = ($tot_qta_storeroom + $storeroom['qta']);
				$tot_importo_storeroom = ($tot_importo_storeroom + ($storeroom['prezzo'] * $storeroom['qta']));
				$importo_completo_all_orders += $tot_importo_storeroom;			
			}
			
			echo '<tr>';
			echo '	<td></td>';
			echo '	<td></td>';
			echo '	<td><img class="img-responsive-disabled" src="/images/cake/apps/32x32/kwin4.png" /> <b>'.__('Storeroom').'</b></td>';
			echo '	<td>';
			echo number_format($tot_importo_storeroom,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			echo '<input type="hidden" name="data[Cassiere][Storerrom][importo]" value="'.$tot_importo_storeroom.'" />';
			echo '</td>';
			echo '	<td>';
			
			foreach($modalita as $key => $value) {
				echo '<label class="radio-inline">';
				echo '<input type="radio" class="modalita" name="data[Cassiere][Storerrom][modalita]" id="modalita_storeroom" value="'.$key.'" ';
				if($key==$result['SummaryOrder']['modalita']) echo ' checked="checked" ';
				echo '/> ';
				echo $this->App->traslateEnum($key).'</label> ';
			}
			echo '</td>';
			echo '</tr>';
			
		}
		
	} // end loop storeroomResults


	
	/*
	 * totali, lo calcolo in modo dinamico
	*/ 
	$tot_importo_dovuto = ($tot_importo_dovuto + $tot_importo_storeroom);
	$tot_importo_da_pagare = ($tot_importo_dovuto - ($tot_importo_cassa));
	if($tot_importo_da_pagare < 0)
            $tot_importo_da_pagare = $tot_importo_dovuto;
        
	if($tot_orders_validi>0 || isset($storeroomResults['Delivery'][0])) {
		echo '<tr>';
		echo '	<td> </td>';
		echo '	<td colspan="2" style="font-size: 16px;text-align:right;font-weight: bold;">Totale</td>';
		echo '	<td>'.number_format($tot_importo_dovuto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
		
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

		echo '<label class="">';
		echo '  <input type="checkbox" checked="checked" name="cash_options" id="cash_options" value="Y" class="form-control" /> Prendi in considerazione la cassa';
		echo '</label>';
		
		echo '</td>';
		echo '</tr>';	

		
		echo '<tr class="cassa_nota">';
		echo '	<td> </td>';
		echo '	<td colspan="2" style="font-size: 16px;text-align:right;font-weight: bold;">Nota Cassa</td>';
		echo '	<td colspan="2">';
		echo '<textarea rows="3" cols="75" name="cash_text" id="cash_text" tabindex="1"></textarea>';
		echo '	</td>';
		echo '</tr>';
		
		/*
		 * totale completo
		*/
		echo '<tr>';
		echo '	<td></td>';
		echo '	<td colspan="2" style="font-size: 16px;text-align:right;font-weight: bold;">Totale da pagare</td>';
		echo '	<td style="white-space: nowrap;">';
				 	
		if($debug) echo '<br />tot_importo_dovuto'; 
		echo '<input type="'.$inputType.'" name="tot_importo_dovuto" id="tot_importo_dovuto" value="'.number_format($tot_importo_dovuto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'" class="form-control" />';
		
		if($debug) echo '<br />tot_importo_cassa';
		echo '<input type="'.$inputType.'" name="tot_importo_cassa" id="tot_importo_cassa" value="'.number_format($tot_importo_cassa,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'" class="form-control" />';
				
		if($debug) echo '<br />differenza_in_cassa';
		echo '<input type="'.$inputType.'" name="differenza_in_cassa" id="differenza_in_cassa" value="'.$differenza_in_cassa.'" class="form-control" />';
		
		
		if($debug) echo '<br />CAMPO INPUT TEXT tot_importo_da_pagare';
		echo '<input tabindex="2" name="data[Cassiere][tot_importo_da_pagare]" id="tot_importo_da_pagare" type="text" value="'.number_format($tot_importo_da_pagare,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'" class="double importoSubmit form-control" style="display:inline" />&nbsp;<span>&euro;</span>';
		echo '</td>';
		echo '<td style="text-align:right;">';
		if($tot_importo_cassa>=0)
			echo '<button id="user_no_money" type="button" class="btn btn-primary">Il gasista non paga in contanti</button>';
		echo '</td>';
		echo '</tr>';
		
		/*
		 * differenza cassa
		*/
		echo '<tr>';
		echo '	<td> </td>';
		echo '	<td colspan="2" style="font-size: 16px;text-align:right;font-weight: bold;">';
		echo '<span class="cassa">Movimento di cassa</span></td>';
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
				echo '<input type="text" class="double form-control" name="data[Cassiere]['.$rowId.'][paymentPos]" id="data[Cassiere]['.$rowId.'][paymentPos]" value="'.$user->organization['Organization']['paymentPos'].'" />&nbsp;&euro;';
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

			
	echo '</table></div>';
	
	if($tot_orders_validi==0) {
		echo '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
		echo 'Non ci sono ordini "In carico al cassiere durante la consegna" da poter gestire';
		echo '</div>';
	}
	
}
?>
<script type="text/javascript">
$(document).ready(function() {
	
	$('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
	
	choiceUserCash();

	$('#user_no_money').on("click", function (event) {
        event.preventDefault();
		$('#cash_options').prop('checked', true);
		$('#tot_importo_da_pagare').val('0,00');
		
		$('.cassa').show();
		$('.cassa_nota').show();
		//setImportoConCassa();		
		setDifferenzaCassa();		
	});
	
	$('.actionCopy_disabled').click(function() {

		var id = $(this).attr('id');
		var importo = $(this).attr('importo');

		if(id=='totale_importo_pagato') {
			$("#tot_importo_pagato").html(importo);
			
			$(".actionCopy").each(function () {
				var id = $(this).attr('id');

				if(id!='totale_importo_pagato') {
					var importo = $(this).attr('importo');
					$("#importo_pagato-"+id).val(importo);
				}
			});
		}
		else {
			$("#importo_pagato-"+id).val(importo);
			setTotImportoPagato();	
		}
		
		return false;
	});
	
	/*
	 * importo
	 */
	$('#tot_importo_da_pagare').focusout(function() {

		setNumberFormat(this);
		var importo = $(this).val();
		/* console.log("tot_importo_da_pagare.focusout() "+importo); */
		if(importo=='' || importo==undefined) {
			alert("Devi indicare l'importo");
			$(this).val("0,00");
			$(this).focus();
			return false;
		}	
		
		/*
		 * tolgo il vincolo cosi' posso avere importi che creano un debito in cassa
		if(importo=='0,00') {
			alert("L'importo dev'essere indicato con un valore maggior di 0");
			$(this).focus();
			return false;
		}
		*/
		setDifferenzaCassa();
		
		$('#cash_text').focus();
	});

	$("select[name='modalita_all']").change(function () {
		var modalita = $(this).val();

		if(modalita!="") settaModalita(modalita);
	});

	<?php
	if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
	?>
	$(".modalita").change(function () {
		var modalita = $(this).val();
		
		if(modalita=="BANCOMAT") 
			$('.bancomat').show();
		else
			$('.bancomat').hide();
	});
	<?php
	}
	?>	
		
	$("input[name='cash_options']").change(function() {	

		var debug = <?php echo $debugJs;?>;
		
		var cash_options = $("input[name='cash_options']:checked").val();
		var tot_importo_dovuto = $('#tot_importo_dovuto').val();
		var tot_importo_cassa = $('#tot_importo_cassa').val();
		
		if(debug) {
			console.log("tot_importo_dovuto "+tot_importo_dovuto);
			console.log("tot_importo_cassa "+tot_importo_cassa);
		}
			
		if(cash_options!='Y') {
			$('.cassa').hide();
			$('.cassa_nota').hide();
			$('#tot_importo_da_pagare').val(tot_importo_dovuto);
		}
		else {
			$('.cassa').show();
			$('.cassa_nota').show();
			
			setImportoConCassa();
			
			setDifferenzaCassa();
		}
	});	
	
	setDifferenzaCassa();
	
	$('#formGas').submit(function() {
	
		var tot_importo_da_pagare = $('#tot_importo_da_pagare').val();
		/*
		 * tolgo il vincolo cosi' posso avere importi che creano un debito in cassa
		 * if(tot_importo_da_pagare=='' || tot_importo_da_pagare==undefined || tot_importo_da_pagare=='0.00' || tot_importo_da_pagare=='0,00') 
		 */ 
		console.log("tot_importo_da_pagare.submit() "+tot_importo_da_pagare); 
		if(tot_importo_da_pagare=='' || tot_importo_da_pagare==undefined) 
		{
			alert("Devi indicare l'importo");
			$(this).val("0,00");
			$(this).focus();
			return false;
		}	
			 
		return true;
	});	
});

function setImportoConCassa() {

		var debug = <?php echo $debugJs;?>;
		
		var cash_options = $("input[name='cash_options']:checked").val();
		var tot_importo_dovuto = $('#tot_importo_dovuto').val();
		var tot_importo_cassa = $('#tot_importo_cassa').val();
		
		tot_importo_dovuto = numberToJs(tot_importo_dovuto);  /* in 1000.50 */
		tot_importo_cassa = numberToJs(tot_importo_cassa);  /* in 1000.50 */
		
		if(debug) {
			console.log("setImportoConCassa() - tot_importo_dovuto "+tot_importo_dovuto);
			console.log("setImportoConCassa() - tot_importo_cassa "+tot_importo_cassa);
		}
		
		var tot_importo_da_pagare = 0;
		if(parseFloat(tot_importo_cassa) > parseFloat(tot_importo_dovuto)) {
			if(debug) 
				console.log("setImportoConCassa() - tot_importo_cassa > tot_importo_dovuto");
			tot_importo_da_pagare = tot_importo_dovuto; 
		}
		else {
			if(debug) 
				console.log("setImportoConCassa() - tot_importo_cassa < tot_importo_dovuto");		
			tot_importo_da_pagare = (tot_importo_dovuto - (tot_importo_cassa)); 
		}
		
		if(debug) 
			console.log("setImportoConCassa() - tot_importo_da_pagare "+tot_importo_da_pagare);
		
		tot_importo_da_pagare = number_format(tot_importo_da_pagare,2,',','.');  /* in 1.000,50 */ 

		if(debug) 
			console.log("setImportoConCassa() - tot_importo_da_pagare "+tot_importo_da_pagare);

		$('#tot_importo_da_pagare').val(tot_importo_da_pagare);
}

function settaModalita(modalita) {
	$(".modalita").each(function () {
		if($(this).attr('value')==modalita) 
			$(this).prop('checked',true);
		else
			$(this).prop('checked',false);
	});
}

/* 
 * tot_importo_dovuto         quanto lo user avrebbe dovuto pagare
 * tot_importo_da_pagare      quanto lo user paga effettivamente
 */
function setDifferenzaCassa() {
	
	var debug = true; // <?php echo $debugJs;?>;

	var tot_importo_cassa = numberToJs($("#tot_importo_cassa").val());   /* in 1000.50 */
	if(debug) console.log('setDifferenzaCassa() - tot_importo_cassa '+tot_importo_cassa);
	
	/*
	 * quanto avrebbe dovuto pagare
	 */
	var tot_importo_dovuto = numberToJs($("#tot_importo_dovuto").val());   /* in 1000.50 */
	if(debug) console.log('setDifferenzaCassa() - tot_importo_dovuto '+tot_importo_dovuto);
	
	/*
	 * quanto paga in realta'
	 */
	var tot_importo_da_pagare = numberToJs($("#tot_importo_da_pagare").val());   /* in 1000.50 */
	if(debug) console.log('setDifferenzaCassa() - tot_importo_da_pagare '+tot_importo_da_pagare);
	
	var differenza = (tot_importo_dovuto - tot_importo_da_pagare).toFixed(2);
	if(debug) console.log('setDifferenzaCassa() - differenza (tot_importo_dovuto-tot_importo_da_pagare) = '+differenza);

	var tot_importo_cassa_nuovo = 0;
	if(differenza==0) {
		if(debug) console.log('setDifferenzaCassa() - pago tutto ');
		if(tot_importo_cassa==0) {
			tot_importo_cassa_nuovo = tot_importo_cassa;
			if(debug) console.log('setDifferenzaCassa() - non ho movimenti di cassa perche cassa 0');
		}
		else {
			if(parseFloat(tot_importo_cassa) >= parseFloat(tot_importo_da_pagare)) {
				tot_importo_cassa_nuovo = (tot_importo_cassa - tot_importo_da_pagare).toFixed(2);
				if(debug) console.log('setDifferenzaCassa() - pago tutto con la cassa e ne rimane ancora ');
			}
			else {
				tot_importo_cassa_nuovo = 0;
				if(debug) console.log('setDifferenzaCassa() - pago tutto con la cassa e la finisco ');				
			}
		}
	}	 
	else 
	if(differenza>0) {
		/*
		 * pago meno => DEBITI
		 */
		if(debug) console.log('setDifferenzaCassa() - pago meno => DEBITO di cassa, in cassa '+tot_importo_cassa);
		
		if(parseFloat(tot_importo_cassa) < 0)
			tot_importo_cassa_nuovo = (parseFloat(tot_importo_cassa) - parseFloat(tot_importo_da_pagare) - parseFloat(tot_importo_dovuto)).toFixed(2);
		else
		if(parseFloat(tot_importo_cassa) > 0) {
			tot_importo_cassa_nuovo = (tot_importo_cassa - differenza).toFixed(2);
		}	
		else
		if(parseFloat(tot_importo_cassa) == 0)
			tot_importo_cassa_nuovo = (parseFloat(tot_importo_da_pagare) - parseFloat(tot_importo_dovuto)).toFixed(2);
			
	}	 
	else
	if(differenza<0) {
		
		if(tot_importo_cassa<0) {
			/*
			 * ho cassa negativa => DEBITO
			 */
			tot_importo_cassa = (-1 * tot_importo_da_pagare); 
			tot_importo_cassa_nuovo = (-1 * tot_importo_da_pagare); 
			if(debug) console.log('setDifferenzaCassa() ho cassa negativa => DEBITO in cassa '+tot_importo_cassa);	
		}
		else {
			/*
			 * pago + => CREDITO
			 */
			if(debug) console.log('setDifferenzaCassa() - pago + => CREDITO di cassa, in cassa '+tot_importo_cassa);
			
			tot_importo_cassa_nuovo = (parseFloat(tot_importo_cassa) + (parseFloat(tot_importo_da_pagare) - parseFloat(tot_importo_dovuto))).toFixed(2);
		}
	}	
	
	if(debug) console.log('setDifferenzaCassa() - tot_importo_cassa_nuovo (differenza) '+tot_importo_cassa_nuovo);
	
	differenza = number_format(tot_importo_cassa_nuovo,2,',','.');
	if(differenza=='-0,00')
		differenza = '0,00';
	
	$('#differenza').html(differenza+'&nbsp;&euro;');
	$('#differenza_in_cassa').val(differenza);	 
}

function setTotImportoPagato_disabled() {

	return;

	
	var tot_importo_pagato = 0;
	$(".importoSubmit").each(function () {
		var importo_pagato = $(this).val();
		
		importo_pagato = numberToJs(importo_pagato);   /* in 1000.50 */
			
		tot_importo_pagato = (parseFloat(tot_importo_pagato) + parseFloat(importo_pagato));
	});
	
	tot_importo_pagato = number_format(tot_importo_pagato,2,',','.');  /* in 1.000,50 */

	$('#tot_importo_pagato').html(tot_importo_pagato);		
}
</script>