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
    echo __('request_payment_num').' '.$requestPaymentResults['RequestPayment']['num'].' di '.$tot_importo.' &euro;';
    echo '(';
    echo 'creata '.$this->Time->i18nFormat($requestPaymentResults['RequestPayment']['created'],"%A %e %B %Y");
    if($requestPaymentResults['RequestPayment']['data_send']!==Configure::read('DB.field.date.empty', '1970-01-01'))
        echo ' - inviata '.$this->Time->i18nFormat($requestPaymentResults['RequestPayment']['data_send']);
    echo ')';
echo '<span style="float:right;">';
	echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']).' <span style="padding-left: 20px;" title="'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']).'" class="stato_'.strtolower($requestPaymentResults['RequestPayment']['stato_elaborazione']).'"></span>';
	echo '</span>';
	echo '</h2>';

	include('box_detail.ctp');
		

	echo $this->Form->create('FilterRequestPayment', ['id'=>'formGasFilter','type'=>'get']);
	echo '<fieldset class="filter" style="margin: 0;padding: 0;">';
	echo '<legend>'.__('Filter RequestPayment').'</legend>';
	echo '<div class="row">';
	echo '<div class="col-md-6">';
	foreach($summaryPaymentStato as $key => $value) {
		echo '<label class="checkbox-inline"><input type="checkbox" name="stato_selected" value="'.$key.'" checked="checked" />'.$this->App->traslateEnum($key).'</label>';
	}	
	echo '</div>';
	echo '<div class="col-md-5">';
	echo $this->Ajax->autoComplete('FilterRequestPaymentName', 
					   Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteRequestPayment_name&format=notmpl',
						['label' => 'Nome','name' => 'FilterRequestPaymentName','value' => $FilterRequestPaymentName,'escape' => false]);
	echo '</div>';
	echo '<div class="col-md-1">';
	echo $this->Form->submit(__('Filter'), ['class' => 'filter', 'div' => ['class' => 'submit filter', 'style' => 'display:none']]); 
	echo '</div>';
	echo '</div>';
	echo '</fieldset>';
	echo $this->Form->end(); 
	//echo $this->Form->end(['label' => __('Filter'), 'class' => 'filter', 'div' => ['class' => 'submit filter', 'style' => 'display:none']]);

		
echo $this->Form->create('RequestPayment', ['id' => 'formGas']);
echo '<fieldset style="margin: 0;padding: 0;">';
echo '<div class="submit" style="float:right;"><input type="submit" value="'.__('Submit').'" /></div>';
?>		
	<div class="table-responsive"><table class="table table-hover">
		<tr>
			<th></th>
			<th><?php echo __('N');?></th>
			<th><?php echo __('Name');?></th>
			<th style="text-align:center;"><?php echo __('Importo_dovuto');?></th> 
			<th style="text-align:center;"><?php echo __('Importo_richiesto');?></th>
			<th><?php echo __('Cash');?></th>
			<th style="width:100px;"><?php echo __('Importo_pagato');?></th>
			<th>
				<div class="checkbox">
					<label><input type="checkbox" id="checkbox_saldato_all" name="checkbox_saldato_all" value="ALL" /></label>
				</div>				
			</th>
			<th>Stato
				<?php 
				echo '<select name="stato_all" size="1" class="form-control">';
				echo '<option value="" selected>Applica lo stato a tutti</option>';
				foreach($summaryPaymentStato as $key => $value) 
					echo '<option value="'.$key.'">'.$this->App->traslateEnum($key).'</option>';
				echo '</select>';
				?>
			</th>
			<th>
				<?php 
				echo __('Modality');
				echo '<select name="modalita_all" size="1" class="form-control">';
				echo '<option value="" selected>Applica la modalit&agrave; a tutti</option>';
				foreach($modalita as $key => $value) 
					echo '<option value="'.$key.'">'.$this->App->traslateEnum($key).'</option>';
				echo '</select>';
				?>
			</th>
	</tr>			
	<?php 
	$tot_importo_cash = 0;
	$tot_importo_dovuto = 0;	
	$tot_importo_richiesto = 0;	
	$tot_da_pagare_rimanente = 0; // (tot_importo_dovuto - tot_importo_pagato)		
	$tabindex = 1;
	foreach($results['SummaryPayment'] as $num => $summaryPayment) {
	
			echo '<tr class="stato'.$summaryPayment['SummaryPayment']['stato'].'">';
			
			echo '<td><a action="request_payment_referent_to_users-'.$requestPaymentResults['RequestPayment']['id'].'_'.$summaryPayment['User']['id'].'" class="actionTrView openTrView" href="#"  title="'.__('Href_title_expand').'"></a></td>';
			echo '<td>'.($num+1).'</td>';
			echo '<td>';
			echo $summaryPayment['User']['name'].'<br />';
			echo $summaryPayment['User']['email'];
			echo '</td>';
			echo '<td style="text-align:center;">'.$summaryPayment['SummaryPayment']['importo_dovuto_e'].'</td>';
			echo '<td style="text-align:center;">'.$summaryPayment['SummaryPayment']['importo_richiesto_e'].'</td>';
			 		 
			/*
			 *  C A S H  $summaryPayment['Cash']['importo_e'];
			 */ 
			 $importo_cash = (floatval($summaryPayment['SummaryPayment']['importo_dovuto'])- floatval($summaryPayment['SummaryPayment']['importo_richiesto']));
			 $tot_importo_cash += $importo_cash;
			 
			 echo '<td>';
			 $importo_cash = number_format($importo_cash,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			 echo $importo_cash.'&nbsp;&euro;';
		 	 echo '</td>';			
			
			/*
			 * I M P O R T O _ P A G A T O
			 */
			echo '<td style="white-space: nowrap;">';
			
			/*
			 * non permetto modifiche perche' gia' sottratto/aggiunto in Cash
			 */
			if($summaryPayment['SummaryPayment']['stato']=='PAGATO') {
				echo $summaryPayment['SummaryPayment']['importo_pagato_e'];
			}
			else {	
			
				$tot_da_pagare_rimanente += ($summaryPayment['SummaryPayment']['importo_dovuto'] - $summaryPayment['SummaryPayment']['importo_pagato']);
			
				/*
				 *  l'importo lo gestisco solo se DAPAGARE
				 */
				echo $this->Form->input('importo_pagato',['type' => 'text', 'label'=>false,'name' => 'data[RequestPayment][importo_pagato]['.$summaryPayment['SummaryPayment']['id'].']',
					'id' => 'importo_pagato-'.$summaryPayment['SummaryPayment']['id'],
					'value' => $summaryPayment['SummaryPayment']['importo_pagato_'],
					'style' => 'display:inline' ,'tabindex'=>($tabindex+1),'after'=>'&nbsp;&euro;','class'=>'double importo_pagato']);
																									 
				echo $this->Form->hidden('user_id',['type' => 'text', 'label'=>false,'name' => 'data[RequestPayment][user_id]['.$summaryPayment['SummaryPayment']['id'].']',
					'id' => 'user_id-'.$summaryPayment['SummaryPayment']['id'],
					'value' => $summaryPayment['SummaryPayment']['user_id']]);
																								 
				echo $this->Form->hidden('importo_richiesto',['type' => 'text', 'label'=>false,'name' => 'data[RequestPayment][importo_richiesto]['.$summaryPayment['SummaryPayment']['id'].']',
					'id' => 'importo_richiesto-'.$summaryPayment['SummaryPayment']['id'],
					'value' => $summaryPayment['SummaryPayment']['importo_richiesto_']]);
	
				echo $this->Form->hidden('importo_dovuto',['type' => 'text', 'label'=>false,'name' => 'data[RequestPayment][importo_dovuto]['.$summaryPayment['SummaryPayment']['id'].']',
					'id' => 'importo_dovuto-'.$summaryPayment['SummaryPayment']['id'],
					'value' => $summaryPayment['SummaryPayment']['importo_dovuto_']]);
						
				/*
				 *  se != rispetto al nuovo aggiorno il DB
				 */
				echo $this->Form->hidden('stato_orig',['type' => 'text', 'label'=>false,'name' => 'data[RequestPayment][stato_orig]['.$summaryPayment['SummaryPayment']['id'].']',
					'id' => 'stato_orig-'.$summaryPayment['SummaryPayment']['id'],
					'value' => $summaryPayment['SummaryPayment']['stato']]);
			}
			echo '</td>';

		/*
		 *  saldato
		 */
		echo '<td style="padding-left:15px;">';
		
		if($summaryPayment['SummaryPayment']['stato']!='PAGATO') {
			echo '<div class="checkbox">';
			echo '	<label><input type="checkbox" class="checkbox_saldato" id="checkbox_saldato-'.$summaryPayment['SummaryPayment']['id'].'" name="data[RequestPayment][saldato]['.$summaryPayment['SummaryPayment']['id'].']" value="'.$summaryPayment['SummaryPayment']['id'].'" /> Saldato</label>';
			echo '</div>';
		}
		echo '</td>';
		
		echo '<td id="color-'.$summaryPayment['SummaryPayment']['id'].'" style="white-space:nowrap;';
		switch ($summaryPayment['SummaryPayment']['stato']) {
			case 'DAPAGARE':
				echo 'background-color:red;color:white;';
			break;
			case 'SOLLECITO1':
				echo 'background-color:yellow;color:black;';
			break;
			case 'SOLLECITO2':
				echo 'background-color:yellow;color:black;';
			break;
			case 'SOSPESO':
				echo 'background-color:gray;color:white;';
			break;
			case 'PAGATO':
				echo 'background-color:green;color:white;';
			break;
			default:
				echo 'background-color:white;color:blank;';
			break;
		}
		echo '">';
		
		if($summaryPayment['SummaryPayment']['stato']=='PAGATO') 
			echo $this->App->traslateEnum($summaryPayment['SummaryPayment']['stato']);
		else {
			$i=0;
			foreach($summaryPaymentStato as $key => $value) {
			
				if($i==2) echo '<br />';
				
				echo '<label for="stato'.$key.'-'.$summaryPayment['SummaryPayment']['id'].'" class="radio-inline">';
				echo '<input type="radio" class="stato" name="data[RequestPayment][stato]['.$summaryPayment['SummaryPayment']['id'].']" id="stato'.$key.'-'.$summaryPayment['SummaryPayment']['id'].'" value="'.$key.'" ';
				if($key==$summaryPayment['SummaryPayment']['stato']) echo ' checked="checked" ';
				echo '/> ';
				echo $this->App->traslateEnum($key).'</label> ';
				
				$i++;
			}
		}		
		echo '</td>';
		
		/*
		 *  modalita
		 */
		echo '<td>';
		if($summaryPayment['SummaryPayment']['stato']=='PAGATO') 
			echo $this->App->traslateEnum($summaryPayment['SummaryPayment']['modalita']);
		else {
			foreach($modalita as $key => $value) {
				echo '<div class="radio"><label>';
				echo '<input type="radio" class="modalita" name="data[RequestPayment][modalita]['.$summaryPayment['SummaryPayment']['id'].']" id="modalita'.$key.'-'.$summaryPayment['SummaryPayment']['id'].'" value="'.$key.'" ';
				if($key==$summaryPayment['SummaryPayment']['modalita']) echo ' checked="checked" ';
				echo '/> ';
				echo $this->App->traslateEnum($key).'</label></div>';
			}
		}			
		echo '</td>';
		
		echo '</tr>';
		echo '<tr class="trView" id="trViewId-'.$requestPaymentResults['RequestPayment']['id'].'_'.$summaryPayment['User']['id'].'">';
		echo '<td colspan="2"></td>';
		echo '<td colspan="8" id="tdViewId-'.$requestPaymentResults['RequestPayment']['id'].'_'.$summaryPayment['User']['id'].'"></td>';
		echo '</tr>';
		
		$tot_importo_dovuto += $summaryPayment['SummaryPayment']['importo_dovuto'];
		$tot_importo_richiesto += $summaryPayment['SummaryPayment']['importo_richiesto'];	
	}
	
	/* 
	 * totali
	 */
    $tot_importo_dovuto = number_format($tot_importo_dovuto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
    $tot_importo_richiesto = number_format($tot_importo_richiesto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_importo_cash = number_format($tot_importo_cash,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_da_pagare_rimanente = number_format($tot_da_pagare_rimanente,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	 
	echo '<tr>';
	echo '<td colspan="2"></td>';
	echo '<td style="font-weith:bold;text-align:right">Totali</td>';
	echo '<td style="text-align:center;">'.$tot_importo_dovuto.'&nbsp;&euro;</td>';
	echo '<td style="text-align:center;">'.$tot_importo_richiesto.'&nbsp;&euro;</td>';
	echo '<td>'.$tot_importo_cash.'&nbsp;&euro;</td>';
	echo '<td colspan="4">'.$tot_da_pagare_rimanente.'&nbsp;&euro;&nbsp;<span class="label label-info">Totale da incassare</span></td>';
	echo '</tr>';
		
	echo '</table></div>';
	echo '</fieldset>';
	
echo $this->Form->hidden('request_payment_id',['value' => $requestPaymentResults['RequestPayment']['id']]);
echo $this->Form->hidden('delivery_id',['value' => $delivery_id]);
echo $this->Form->hidden('stato_elaborazione',['value' => $requestPaymentResults['RequestPayment']['stato_elaborazione']]);


echo $this->Form->end(__('Submit'));

echo '</div>'; // end contentMenuLaterale

$options = [];
echo $this->MenuRequestPayment->drawWrapper($requestPaymentResults['RequestPayment']['id'], $options);
?>
<script type="text/javascript">
var debug = false;

function settaModalita(modalita) {
	$(".modalita").each(function () {
		if($(this).attr('value')==modalita) 
			$(this).prop('checked',true);
		else
			$(this).prop('checked',false);
	});

}

function settaStato(stato) {
	$(".stato").each(function () {
		if($(this).attr('value')==stato) 
			$(this).prop('checked',true);
		else
			$(this).prop('checked',false);
			
		var idRow = $(this).attr('id');
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		gestStatoColor(numRow);
		
		if(stato=='PAGATO') {
			$('#checkbox_saldato-'+numRow).prop('checked',true);		
			settaSaldato(numRow);
		}				
	});
}
	
function settaSaldato(numRow) {
	var checkbox_saldato = $('#checkbox_saldato-'+numRow+':checked').val();
	var importo_richiesto = $('#importo_richiesto-'+numRow).val();
	
	if(debug) {
		console.log('numRow '+numRow);
		console.log('importo_richiesto '+importo_richiesto);
		console.log('importo_pagato '+$('#importo_pagato-'+numRow).val());
		console.log('checkbox_saldato '+checkbox_saldato);
	}
	
	if(checkbox_saldato==undefined) 
		inizialState(numRow);
	else {
		$('#importo_pagato-'+numRow).val(importo_richiesto);
		$('#statoPAGATO-'+numRow).prop('checked',true);
	}
		
	gestStatoColor(numRow);	
}
	
function gestStatoColor(numRow) {

	var stato = $("input[name='data[RequestPayment][stato]["+numRow+"]']:checked").val();
	
	switch (stato)
	{
		case 'DAPAGARE':
			$('#color-'+numRow).css('background-color', 'red').css('color', 'white');
		break;
		case 'SOLLECITO1':
		case 'SOLLECITO2':
			$('#color-'+numRow).css('background-color', 'yellow').css('color', 'black');
		break;
		case 'SOSPESO':
			$('#color-'+numRow).css('background-color', 'gray').css('color', 'white');
		break;
		case 'PAGATO':
			$('#color-'+numRow).css('background-color', 'green').css('color', 'white');
		break;
		default:
			$('#color-'+numRow).css('background-color', 'white').css('color', 'black');
		break;
	}	
}

function inizialState(numRow) {
	
	$('#importo_pagato-'+numRow).val('0,00');	
	
	$('#checkbox_saldato-'+numRow).prop('checked',false);
	$('#statoDAPAGARE-'+numRow).prop('checked',true);
	$('#statoPAGATO-'+numRow).prop('checked',false);
	$('#statoSOLLECITO1-'+numRow).prop('checked',false);
	$('#statoSOLLECITO2-'+numRow).prop('checked',false);
	$('#statoSOSPESO-'+numRow).prop('checked',false);
	
	gestStatoColor(numRow);
}

var debug = false;

$(document).ready(function() {

	$('#checkbox_saldato_all').click(function () {
		var checked_pagato_all = $("input[name='checkbox_saldato_all']:checked").val();
		
		if(checked_pagato_all=='ALL') {
			$('.checkbox_saldato').prop('checked',true);
			$(".importo_pagato").each(function () {

				var idRow = $(this).attr('id');
				numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);

				settaSaldato(numRow);
			});
		}
		else {
			$('.checkbox_saldato').prop('checked',false);
			$(".importo_pagato").each(function () {
				
				var idRow = $(this).attr('id');
				numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
				
				$('#importo_pagato-'+numRow).val('0,00');
				
				inizialState(numRow);
			});
		}	
	});
	
	/*
	 *  tasto checkbox SALDATO
	 */
	$('.checkbox_saldato').click(function () {

		/* get id dell'oggetto  xxx-1  */
		var idRow = $(this).attr('id');
		numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		
		settaSaldato(numRow);
	});
		
	$("select[name='modalita_all']").change(function () {
		var modalita = $(this).val();

		if(modalita!="") settaModalita(modalita);
	});

	$("select[name='stato_all']").change(function () {
		var stato = $(this).val();

		if(stato!="") settaStato(stato);
	});

	$(".stato").change(function () {
		var idRow = $(this).attr('id');
		if(typeof idRow !== "undefined") {
			var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			
			var stato = $("input[name='data[RequestPayment][stato]["+numRow+"]']:checked").val();
			if(stato=='PAGATO') {
				$('#checkbox_saldato-'+numRow).prop('checked', true);
				settaSaldato(numRow);
			}
			else {
				$('#checkbox_saldato-'+numRow).prop('checked', false);
				$('#importo_pagato-'+numRow).val('0,00');
			}
				
			gestStatoColor(numRow);
		}
	});
	
	$('#formGas').submit(function() {

		/*
		 *  controllo che se ho inserito l'importo devo aver scelto la modalita di pagamento
		 */
		 var importo_selected = false;
		 var modalita_selected = true;
		 $(".importo_pagato").each(function () {

			var idRow = $(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);

			var importo_pagato = $('#importo_pagato-'+numRow).val();
			if(importo_pagato>0) {
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
		
	$("input[name='stato_selected']").click(function() {
		var stato = $(this).val();
		if($(this).is(':checked')) 
			$('.stato'+stato).css('display','table-row');
		else
			$('.stato'+stato).css('display','none');
	});	
});		
</script>