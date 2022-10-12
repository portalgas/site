<?php
$this->App->d($results);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
if($isReferenteTesoriere)  {
	$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
	if(isset($order_id))
		$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
}
else {
	if(!isset($delivery_id)) $delivery_id = 0;
		$this->Html->addCrumb(__('Tesoriere'),array('controller' => 'Tesoriere', 'action' => 'home', $delivery_id));
}
$this->Html->addCrumb(__('List Request Payments'), array('controller' => 'RequestPayments', 'action' => 'index'));
$this->Html->addCrumb(__('Edit Request Payments'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

    $tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

	echo '<h2 class="ico-pay">';
	echo __('request_payment_num').' '.$requestPaymentResults['RequestPayment']['num'].' di '.$tot_importo.' &euro; ('.$this->Time->i18nFormat($requestPaymentResults['RequestPayment']['created'],"%A %e %B %Y").')';
	echo '<span style="float:right;">';
	echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']).' <span style="padding-left: 20px;" title="'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']).'" class="stato_'.strtolower($requestPaymentResults['RequestPayment']['stato_elaborazione']).'"></span>';
	echo '</span>';
	echo '</h2>';

	include('box_detail.ctp');
		
  if(!empty($results['SummaryPayment'])) {	

	echo $this->Form->create('FilterRequestPayment',array('id'=>'formGasFilter','type'=>'get'));
	?>
		<fieldset class="filter">
			<legend><?php echo __('Filter RequestPayment'); ?></legend>	
			<table>
				<tr>
					<td style="width:60%;">
					<?php 
					echo $this->Ajax->autoComplete('FilterRequestPaymentName', 
									   Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteRequestPayment_name&format=notmpl',
										array('label' => 'Nome','name' => 'FilterRequestPaymentName','value' => $FilterRequestPaymentName,'escape' => false));
					echo '</td>';
					echo '<td>';
					echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); 
					echo '</td>';
				echo '</tr>';
			echo '</table>';
		echo '</fieldset>';

		
echo $this->Form->create('RequestPayment',array('id' => 'formGas'));
echo '<fieldset>';
echo '<div class="submit" style="float:right;"><input type="submit" value="'.__('Submit').'" /></div>';
?>		
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th></th>
			<th><?php echo __('N');?></th>
			<th><?php echo __('Name');?></th>
			<th>Utente ha in cassa</th>
			<th><?php echo __('Importo_dovuto');?></th>
			<th><?php echo __('Cash');?></th>
			<th><?php echo __('Importo_richiesto');?></th>
			<th style="padding-left:25px;">
				<div class="checkbox"><label><?php echo '<input type="checkbox" id="checkbox_includi_cash_all" name="checkbox_includi_cash_all" value="ALL" />';?> Includi cassa<label></div>
			</th>
	</tr>			
	<?php 
	$tot_importo_cash = 0;
	$tot_importo_dovuto = 0;	
	$tabindex = 1;

	foreach($results['SummaryPayment'] as $num => $summaryPayment) {
	
			echo '<tr class="stato'.$summaryPayment['SummaryPayment']['stato'].'">';
			
			echo '<td><a action="request_payment_referent_to_users-'.$requestPaymentResults['RequestPayment']['id'].'_'.$summaryPayment['User']['id'].'" class="actionTrView openTrView" href="#"  title="'.__('Href_title_expand').'"></a></td>';
			echo '<td>'.($num+1).'</td>';
			echo '<td>';
			echo $summaryPayment['User']['name'].'<br />';
			echo $summaryPayment['User']['email'];
			echo '</td>';
		 
			/*
			 *  C A S H - A T T U A L E
			 */
			echo '<td style="border-right: 1px solid #fff;color: white;" id="box_cash-'.$summaryPayment['SummaryPayment']['id'].'">';
			echo '<span id="importo_cash_label-'.$summaryPayment['SummaryPayment']['id'].'">'.$summaryPayment['Cash']['importo_e'].'</span>';
			echo '</td>';
			
			echo '<td>'.$summaryPayment['SummaryPayment']['importo_dovuto_e'].'</td>';
			
			/*
			 *  calcolo con C A S H 
			 */			
			echo '<td>';
			echo '<span id="importo_to_cash-'.$summaryPayment['SummaryPayment']['id'].'"></span>';
			echo '</td>';

			/*
			 * I M P O R T O _ R I C H I E S T O
			 */
			echo '<td style="white-space: nowrap;">';
				
				echo $this->Form->input('importo_richiesto',array('type' => 'text', 'label'=>false,'name' => 'data[RequestPayment][importo_richiesto]['.$summaryPayment['SummaryPayment']['id'].']',
																								'id' => 'importo_richiesto-'.$summaryPayment['SummaryPayment']['id'],
																								'value' => $summaryPayment['SummaryPayment']['importo_richiesto_'],
																								'style' => 'display:inline' ,'tabindex'=>($tabindex+1),'after'=>'&nbsp;&euro;','class'=>'double importo_richiesto'));

				echo $this->Form->hidden('importo_richiesto_orig',array('type' => 'text', 'label'=>false,'name' => 'data[Cash][importo_richiesto_orig]['.$summaryPayment['SummaryPayment']['importo_richiesto_'].']',
																								 'id' => 'importo_richiesto_orig-'.$summaryPayment['SummaryPayment']['id'],
																								 'value' => $summaryPayment['SummaryPayment']['importo_richiesto_']));
				
				echo $this->Form->hidden('importo_dovuto',array('type' => 'text', 'label'=>false,'name' => 'data[RequestPayment][importo_dovuto]['.$summaryPayment['SummaryPayment']['id'].']',
																								 'id' => 'importo_dovuto-'.$summaryPayment['SummaryPayment']['id'],
																								 'value' => $summaryPayment['SummaryPayment']['importo_dovuto_']));	

				echo $this->Form->hidden('importo_dovuto_orig',array('type' => 'text', 'label'=>false,'name' => 'data[RequestPayment][importo_dovuto_orig]['.$summaryPayment['SummaryPayment']['id'].']',
																								 'id' => 'importo_dovuto_orig-'.$summaryPayment['SummaryPayment']['id'],
																								 'value' => $summaryPayment['SummaryPayment']['importo_dovuto_']));	
								
				echo $this->Form->hidden('cash',array('type' => 'text', 'label'=>false,'name' => 'data[Cash][importo]['.$summaryPayment['Cash']['importo'].']',
																								 'id' => 'importo_cash-'.$summaryPayment['SummaryPayment']['id'],
																								 'class' => 'importo_cash',
																								 'value' => $summaryPayment['Cash']['importo_']));
				
				echo $this->Form->hidden('cash_orig',array('type' => 'text', 'label'=>false,'name' => 'data[Cash][importo]['.$summaryPayment['Cash']['importo'].']',
																								 'id' => 'importo_cash_orig-'.$summaryPayment['SummaryPayment']['id'],
																								 'value' => $summaryPayment['Cash']['importo_']));
																								 
				echo $this->Form->hidden('user_id',array('type' => 'text', 'label'=>false,'name' => 'data[RequestPayment][user_id]['.$summaryPayment['SummaryPayment']['id'].']',
																								 'id' => 'user_id-'.$summaryPayment['SummaryPayment']['id'],
																								 'value' => $summaryPayment['SummaryPayment']['user_id']));																								 
			echo '</td>';

			/*
			 *  gestione del calcolo automatico
			 */
			echo '<td style="padding-left:25px;">';
			echo '<input type="checkbox" ';
			if($summaryPayment['Cash']['importo'] != 0)
				echo ' style="display:block" ';
			else
				echo ' style="display:none" '; 
			echo 'class="checkbox_includi_cash" name="data[RequestPayment][richiesto]['.$summaryPayment['SummaryPayment']['id'].']" id="checkbox_includi_cash-'.$summaryPayment['SummaryPayment']['id'].'" value="'.$summaryPayment['SummaryPayment']['id'].'" />';
			echo '</td>';
			
		echo '</tr>';
		echo '<tr class="trView" id="trViewId-'.$requestPaymentResults['RequestPayment']['id'].'_'.$summaryPayment['User']['id'].'">';
		echo '<td colspan="2"></td>';
		echo '<td colspan="6" id="tdViewId-'.$requestPaymentResults['RequestPayment']['id'].'_'.$summaryPayment['User']['id'].'"></td>';
		echo '</tr>';
		
		$tot_importo_cash += $summaryPayment['Cash']['importo'];
		$tot_importo_dovuto += $summaryPayment['SummaryPayment']['importo_dovuto'];	
	}

	/* 
	 * totali
	 */
	$tot_importo_cash = number_format($tot_importo_cash,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
    $tot_importo_dovuto = number_format($tot_importo_dovuto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			 
	echo '<tr>';
	echo '<td colspan="2"></td>';
	echo '<td style="font-weith:bold;text-align:right">Totali</td>';
	echo '<td>'.$tot_importo_cash.'&nbsp;&euro;</td>';
	echo '<td>'.$tot_importo_dovuto.'&nbsp;&euro;</td>';
	echo '<td><span id="tot_importo_cash"></span></td>';
	echo '<td><span id="tot_importo_richiesto"></span></td>';
	echo '<td></td>';
	echo '</tr>';

	echo '</table>';
	echo '</fieldset>';
	
echo $this->Form->hidden('request_payment_id',array('value' => $requestPaymentResults['RequestPayment']['id']));
echo $this->Form->hidden('delivery_id',array('value' => $delivery_id));
echo $this->Form->hidden('stato_elaborazione',array('value' => $requestPaymentResults['RequestPayment']['stato_elaborazione']));

echo $this->Form->end(__('Submit'));


} // end if(!empty($results['SummaryPayment'])) 

echo '</div>'; // end contentMenuLaterale

$options = [];
echo $this->MenuRequestPayment->drawWrapper($requestPaymentResults['RequestPayment']['id'], $options);
?>
<script type="text/javascript">
var debug = false;
	
function disabledCash(numRow) {
	var importo_cash_orig = $('#importo_cash_orig-'+numRow).val();

	$('#checkbox_includi_cash-'+numRow).prop('checked',false);
	$('#checkbox_includi_cash-'+numRow).css('display','none');

	$('#importo_to_cash-'+numRow).html('');
	$('#importo_cash_label-'+numRow).html(importo_cash_orig +' €');
	$('#importo_cash_orig-'+numRow).val(importo_cash_orig);

}	
function enbledCash(numRow) {
	$('#checkbox_includi_cash-'+numRow).css('display','block');
}	

function inizialState(numRow) {
	var importo_cash = $('#importo_cash_orig-'+numRow).val();
	$('#importo_cash-'+numRow).val(importo_cash);
	$('#importo_cash_label-'+numRow).html(importo_cash+" €");

	var importo_richiesto = $('#importo_richiesto_orig-'+numRow).val();
	$('#importo_richiesto-'+numRow).val(importo_richiesto);	

	gestRow(numRow);
}

function gestRow(numRow) {			
	var importo_dovuto = $('#importo_dovuto-'+numRow).val();
	var importo_richiesto = $('#importo_richiesto-'+numRow).val();
	var importo_cash = $('#importo_cash-'+numRow).val();
	var importo_cash_orig = $('#importo_cash_orig-'+numRow).val();
	
	var importo_dovutoJS = parseFloat(numberToJs(importo_dovuto));
	var importo_richiestoJS = parseFloat(numberToJs(importo_richiesto));
	var importo_cashJS = parseFloat(numberToJs(importo_cash));
	var importo_cash_origJS = parseFloat(numberToJs(importo_cash_orig));
	
	var checkbox_includi_cash = $('#checkbox_includi_cash-'+numRow+':checked').val();

	/*
	console.log('gestRow() importo_dovuto '+ importo_dovuto);
	console.log('gestRow() importo_richiesto '+ importo_richiesto);
	console.log('gestRow() importo_cash '+ importo_cash);
  	console.log('gestRow() importo_cashJS '+ importo_cashJS);
	console.log('gestRow() checkbox_includi_cash '+ checkbox_includi_cash);
	*/

	if(importo_richiestoJS == importo_dovutoJS) {
		if(debug) console.log('gestRow('+numRow+') importi = ');
		
		if(importo_cashJS == 0)
			disabledCash(numRow);
		else
			enbledCash(numRow);
			
		$('#importo_to_cash-'+numRow).html('');
	}
	else
	if(importo_dovutoJS > importo_richiestoJS) {
		enbledCash(numRow);
		$('#checkbox_includi_cash-'+numRow).prop('checked',true);
			
		var debito_verso_la_cassa = (importo_richiestoJS - importo_dovutoJS);
		debito_verso_la_cassa = arrotondaNumero(debito_verso_la_cassa, 2);		
		if(debug) console.log('gestRow('+numRow+') importo_richiesto INFERIORE => debito verso cassa '+debito_verso_la_cassa);

		/* 
		 * aggiorno cassa corrente
 		 */
		var importo_cash_new = (importo_cash_origJS - (-1 * parseFloat(debito_verso_la_cassa)));
		importo_cash_new = number_format(importo_cash_new,2,',','.');
		$('#importo_cash_label-'+numRow).html(importo_cash_new+' €');
		$('#importo_cash-'+numRow).val(importo_cash_new);

		debito_verso_la_cassa =	number_format(debito_verso_la_cassa,2,',','.');
		$('#importo_to_cash-'+numRow).html(debito_verso_la_cassa+' €');

	}
	else   
	if(importo_dovutoJS < importo_richiestoJS) {
		enbledCash(numRow);
		$('#checkbox_includi_cash-'+numRow).prop('checked',true);
		
		var credito_verso_la_cassa = (importo_richiestoJS - importo_dovutoJS); 
		credito_verso_la_cassa = arrotondaNumero(credito_verso_la_cassa, 2);
		if(debug) console.log('gestRow('+numRow+') importo_richiesto SUPERIORE => credito verso cassa '+credito_verso_la_cassa);

		/* 
		 * aggiorno cassa corrente
 		 */
		var importo_cash_new = (importo_cash_origJS + (credito_verso_la_cassa));
		importo_cash_new = number_format(importo_cash_new,2,',','.');
		$('#importo_cash_label-'+numRow).html(importo_cash_new+' €');
		$('#importo_cash-'+numRow).val(importo_cash_new);
	
		credito_verso_la_cassa = number_format(credito_verso_la_cassa,2,',','.');
		$('#importo_to_cash-'+numRow).html(credito_verso_la_cassa +' €');	
	
	}

	/*
	 *  colore Cash corrente
	 */
	importo_cash  = $('#importo_cash-'+numRow).val();
	importo_cashJS = numberToJs(importo_cash);
	if(importo_cashJS==0) {
		$('#box_cash-'+numRow).css('background-color','white').css('color','black');
	}
	else
	if(importo_cashJS > 0) {
		$('#box_cash-'+numRow).css('background-color','green').css('color','white');
	}
	else
	if(importo_cashJS < 0) {
		$('#box_cash-'+numRow).css('background-color','red').css('color','white');
	}
}		

function checkboxIncludiCash(numRow) {
	
	var importo_dovuto = $('#importo_dovuto-'+numRow).val();
	var importo_richiesto = $('#importo_richiesto-'+numRow).val();
	var importo_cash = $('#importo_cash-'+numRow).val();
	var importo_cash_orig = $('#importo_cash_orig-'+numRow).val();
	
	var importo_dovutoJS = parseFloat(numberToJs(importo_dovuto));
	var importo_richiestoJS = parseFloat(numberToJs(importo_richiesto));
	var importo_cashJS = parseFloat(numberToJs(importo_cash));
	
	var checkbox_includi_cash = $('#checkbox_includi_cash-'+numRow+':checked').val();
	if(debug) console.log("checkboxIncludiCash() checkbox_includi_cash "+checkbox_includi_cash);
	if(checkbox_includi_cash!=undefined)	{
	
		if(debug) console.log("checkboxIncludiCash() importo_cashJS "+importo_cashJS);
		if(debug) console.log("checkboxIncludiCash() importo_dovutoJS "+importo_dovutoJS);
		
		/*
		 *  credito in cassa > del dovuto
		 */
		if(importo_cashJS > 0 && (importo_cashJS > importo_dovutoJS)) {
			if(debug) console.log("checkboxIncludiCash() credito in cassa > del dovuto");

			importo_richiesto_newJS = 0;
			
			importo_cashJS = (importo_cashJS - importo_dovutoJS);
			importo_cash = number_format(importo_cashJS,2,',','.');
			$('#importo_cash-'+numRow).val(importo_cash);
			$('#importo_cash_label-'+numRow).html(importo_cash+" €");	
			
		}
		else {
			importo_richiesto_newJS = (importo_dovutoJS + (-1 * importo_cashJS));
			if(debug) console.log("checkboxIncludiCash() soldi cassa < del dovuto");
		}
		
		importo_richiesto_new = number_format(importo_richiesto_newJS,2,',','.');
		$('#importo_richiesto-'+numRow).val(importo_richiesto_new);	
		
		gestRow(numRow);
	}
}

function tot_importo_cash() {
	var tot_importo_cash = 0;
	$('.importo_cash').each(function( index ) {

		var importo_cash = $(this).val();
		importo_cash = parseFloat(numberToJs(importo_cash));
		tot_importo_cash += importo_cash;
	});
	tot_importo_cash = arrotondaNumero(tot_importo_cash, 2);
	tot_importo_cash =	number_format(tot_importo_cash,2,',','.');
	$('#tot_importo_cash').html(tot_importo_cash+' €');

	if(debug) console.log('tot_importo_cash() '+tot_importo_cash);
}

function tot_importo_richiesto() {
	var tot_importo_richiesto = 0;
	$('.importo_richiesto').each(function( index ) {

		var importo_richiesto = $(this).val();
		importo_richiesto = parseFloat(numberToJs(importo_richiesto));
		tot_importo_richiesto += importo_richiesto;
	});
	tot_importo_richiesto = arrotondaNumero(tot_importo_richiesto, 2);
	tot_importo_richiesto =	number_format(tot_importo_richiesto,2,',','.');
	$('#tot_importo_richiesto').html(tot_importo_richiesto+' €');

	if(debug) console.log('tot_importo_richiesto '+tot_importo_richiesto);
}

$(document).ready(function() {

	var debug = false;

	$('#checkbox_includi_cash_all').click(function () {
		var checked_pagato_all = $("input[name='checkbox_includi_cash_all']:checked").val();
		
		if(checked_pagato_all=='ALL') {
			$('.checkbox_includi_cash').prop('checked',true);
			$(".importo_richiesto").each(function () {

				var idRow = $(this).attr('id');
				numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);

				checkboxIncludiCash(numRow);
			});
		}
		else {
			$('.checkbox_includi_cash').prop('checked',false);
			$(".importo_richiesto").each(function () {
				
				var idRow = $(this).attr('id');
				numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
				
				inizialState(numRow);
			});
		}

		tot_importo_richiesto();

		tot_importo_cash();			
	});

	/*
	 *  tasto checkbox includi CASH
	 */
	$('.checkbox_includi_cash').click(function () {
		var idRow = $(this).attr('id');
		numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		
		var checkbox_includi_cash = $('#checkbox_includi_cash-'+numRow+':checked').val();
		console.log("checkbox_includi_cash "+checkbox_includi_cash);
		if(checkbox_includi_cash==undefined) { 
			inizialState(numRow);
			checkboxIncludiCash(numRow);
		}	
		else 
			checkboxIncludiCash(numRow);
			
		tot_importo_richiesto();

		tot_importo_cash();				
	});
		
	$('#formGas').submit(function() {

		/*
		 *  controllo che se ho inserito l'importo devo aver scelto la modalita di pagamento
		 */
		 var importo_selected = false;
		 var modalita_selected = true;
		 $(".importo_richiesto").each(function () {

			/* get id dell'oggetto  xxx-1  */
			var idRow = $(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);

			var importo_richiesto = $('#importo_richiesto-'+numRow).val();
			if(importo_richiesto>0) {
				importo_selected = true;
				modalita_selected = $("input[name='data[RequestPayment][modalita]["+numRow+"]']:checked").length;
				if(modalita_selected==0)  modalita_selected = false;
			}
		});

		 /*
		 if(!importo_selected) {
			alert("Non hai indicato alcun 'importo pagato'");
			return false;
		 }
		 else
		 
		 if(!modalita_selected) {
			alert("Hai indicato un 'importo pagato' senza indicare la modalità di pagamento");
			return false;
		 }
		 else
		 */
			return true;
	});

	$('.importo_richiesto').each(function( index ) {
		var idRow = $(this).attr('id');
		numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
	
		gestRow(numRow);
	});
	
	$('.importo_richiesto').change(function () {
		var idRow = $(this).attr('id');
		numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);

		gestRow(numRow);
		
		tot_importo_richiesto();

		tot_importo_cash();		
	});

	tot_importo_richiesto();

	tot_importo_cash();	
});
</script>
