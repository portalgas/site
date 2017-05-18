<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
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

echo '<div class="requestPayment">';
?>
<style>
#legendaOrderStateContent {
	display:none;
	z-index:15;
	width:350px;
	position:fixed;
	right:50px;
	background-color: #fff;
}
#box-account-close {
   background: url("/images/cake/close-popup-red.png") no-repeat scroll 0 0 rgba(0, 0, 0, 0);
    cursor: pointer;
    float: right;
    height: 32px;
    position: absolute;
    right: -15px;
    top: -15px;
    width: 32px;	
}
</style>
<?php 
	echo '<h2 class="ico-pay">';
	echo __('request_payment_num').' '.$requestPaymentResults['RequestPayment']['num'].' di '.$this->Time->i18nFormat($results['RequestPayment']['created'],"%A %e %B %Y");
	echo '<span style="float:right;">'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$results['RequestPayment']['stato_elaborazione']).' <span style="padding-left: 20px;" title="'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$results['RequestPayment']['stato_elaborazione']).'" class="stato_'.strtolower($results['RequestPayment']['stato_elaborazione']).'"></span>';
	echo $this->Html->link(" ", array('controller' => 'ExportDocs', 'action' => 'tesoriere_request_payment', $requestPaymentResults['RequestPayment']['id'], 'doc_formato=EXCEL'),array('target' => '_blank', 'class' => 'action actionExcel','title' => __('Export RequestPayment'), 'alt' => __('Export RequestPayment')));
	echo '</span>';
	echo '</h2>';

	include('box_detail.ctp');
		

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
										array('label' => 'Nome','name' => 'FilterRequestPaymentName','value' => $FilterRequestPaymentName,'size'=>'75','escape' => false));
					echo '</td>';
					echo '<td>';
					echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); 
					echo '</td>';
					echo '<td>';
						/*
						 * legenda con gli SummaryPayment.state
						 */
						echo '<div style="float:right;">';
						echo '	<a id="legendaOrderState" href="#" title="'.__('Href_title_expand').'"><img src="/images/cake/actions/32x32/viewmag+.png" /> Visualizza/Nascondi le richieste di pagamento</a>';
						echo '<div id="legendaOrderStateContent" class="legenda">';
						echo '<div id="box-account-close"></div>';
						foreach($summaryPaymentStato as $key => $value) {
						
							echo '<div>';
							echo '<span class="action stato'.$key.'" title="'.$this->App->traslateEnum($key).'"></span>';
							echo '&nbsp;';
							echo '<input style="clear: none;float: none;" type="checkbox" name="stato_selected" value="'.$key.'" checked="checked" />';
							echo '&nbsp;';		
							echo $this->App->traslateEnum($key);
							echo '</div>';
						}
						echo '</div>';
						echo '</div>';
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
			<th>Importo dovuto</th>
			<th>Importo richiesto</th>
			<th>Cassa</th>
			<th>Importo pagato</th>
			<th><?php echo '<input type="checkbox" id="checkbox_saldato_all" name="checkbox_saldato_all" value="ALL" />';?></th>
			<th>Stato
				<?php 
				echo '<select name="stato_all" size="1">';
				echo '<option value="" selected>Applica lo stato a tutti</option>';
				foreach($summaryPaymentStato as $key => $value) 
					echo '<option value="'.$key.'">'.$this->App->traslateEnum($key).'</option>';
				echo '</select>';
				?>
			</th>
			<th>Modalit&agrave;
				<?php 
				echo '<select name="modalita_all" size="1">';
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
	$tabindex = 1;
	foreach($results['SummaryPayment'] as $num => $summaryPayment) {
	
			echo '<tr class="stato'.$summaryPayment['SummaryPayment']['stato'].'">';
			
			echo '<td><a action="request_payment_referent_to_users-'.$requestPaymentResults['RequestPayment']['id'].'_'.$summaryPayment['User']['id'].'" class="actionTrView openTrView" href="#"  title="'.__('Href_title_expand').'"></a></td>';
			echo '<td>'.($num+1).'</td>';
			echo '<td>';
			echo $summaryPayment['User']['name'].'<br />';
			echo $summaryPayment['User']['email'];
			echo '</td>';
			echo '<td>'.$summaryPayment['SummaryPayment']['importo_dovuto_e'].'</td>';
			echo '<td>'.$summaryPayment['SummaryPayment']['importo_richiesto_e'].'</td>';
			 		 
			/*
			 *  C A S H 
			 */ 
			 $importo_cash = (floatval($summaryPayment['SummaryPayment']['importo_dovuto'])- floatval($summaryPayment['SummaryPayment']['importo_richiesto']));
			 $tot_importo_cash += $importo_cash;
			 
			 echo '<td>';
			 $importo_cash = number_format($importo_cash,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			 echo $importo_cash.' &euro;';									
		 	 echo '</td>';			
			
			/*
			 * I M P O R T O _ P A G A T O
			 */
			echo '<td>';
			
			/*
			 * non permetto modifiche perche' gia' sottratto/aggiunto in Cash
			 */
			if($summaryPayment['SummaryPayment']['stato']=='PAGATO') 
				echo $summaryPayment['SummaryPayment']['importo_pagato_e'];
			else {	
				/*
				 *  l'importo lo gestisco solo se DAPAGARE
				 */
				echo $this->Form->input('importo_pagato',array('type' => 'text', 'label'=>false,'name' => 'data[RequestPayment][importo_pagato]['.$summaryPayment['SummaryPayment']['id'].']',
																								'id' => 'importo_pagato-'.$summaryPayment['SummaryPayment']['id'],
																								'value' => $summaryPayment['SummaryPayment']['importo_pagato_'],
																								'size'=> 5 ,'tabindex'=>($tabindex+1),'after'=>'&nbsp;&euro;','class'=>'double importo_pagato noWidth'));
																									 
				echo $this->Form->hidden('user_id',array('type' => 'text', 'label'=>false,'name' => 'data[RequestPayment][user_id]['.$summaryPayment['SummaryPayment']['id'].']',
																								 'id' => 'user_id-'.$summaryPayment['SummaryPayment']['id'],
																								 'value' => $summaryPayment['SummaryPayment']['user_id']));
																								 
				echo $this->Form->hidden('importo_richiesto',array('type' => 'text', 'label'=>false,'name' => 'data[RequestPayment][importo_richiesto]['.$summaryPayment['SummaryPayment']['id'].']',
																								 'id' => 'importo_richiesto-'.$summaryPayment['SummaryPayment']['id'],
																								 'value' => $summaryPayment['SummaryPayment']['importo_richiesto_']));
	
				echo $this->Form->hidden('importo_dovuto',array('type' => 'text', 'label'=>false,'name' => 'data[RequestPayment][importo_dovuto]['.$summaryPayment['SummaryPayment']['id'].']',
																								 'id' => 'importo_dovuto-'.$summaryPayment['SummaryPayment']['id'],
																								 'value' => $summaryPayment['SummaryPayment']['importo_dovuto_']));
						
				/*
				 *  se != rispetto al nuovo aggiorno il DB
				 */
				echo $this->Form->hidden('stato_orig',array('type' => 'text', 'label'=>false,'name' => 'data[RequestPayment][stato_orig]['.$summaryPayment['SummaryPayment']['id'].']',
																								 'id' => 'stato_orig-'.$summaryPayment['SummaryPayment']['id'],
																								 'value' => $summaryPayment['SummaryPayment']['stato']));
			}
			echo '</td>';

		/*
		 *  saldato
		 */
		echo '<td>';
		
		if($summaryPayment['SummaryPayment']['stato']!='PAGATO') {
			echo '<input type="checkbox" ';
			echo 'class="checkbox_saldato" name="data[RequestPayment][saldato]['.$summaryPayment['SummaryPayment']['id'].']" id="checkbox_saldato-'.$summaryPayment['SummaryPayment']['id'].'" value="'.$summaryPayment['SummaryPayment']['id'].'" /> Saldato';
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
				
				echo '<label for="stato'.$key.'-'.$summaryPayment['SummaryPayment']['id'].'" style="width:auto !important;margin-right:3px">'.$this->App->traslateEnum($key).'</label>';
				echo '<input type="radio" class="stato" name="data[RequestPayment][stato]['.$summaryPayment['SummaryPayment']['id'].']" id="stato'.$key.'-'.$summaryPayment['SummaryPayment']['id'].'" value="'.$key.'" ';
				if($key==$summaryPayment['SummaryPayment']['stato']) echo ' checked="checked" ';
				echo '/>';
				
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
				echo '<label for="modalita'.$key.'-'.$summaryPayment['SummaryPayment']['id'].'" style="width:auto !important;">'.$this->App->traslateEnum($key).'</label>';
				echo '<input type="radio" class="modalita" name="data[RequestPayment][modalita]['.$summaryPayment['SummaryPayment']['id'].']" id="modalita'.$key.'-'.$summaryPayment['SummaryPayment']['id'].'" value="'.$key.'" ';
				if($key==$summaryPayment['SummaryPayment']['modalita']) echo ' checked="checked" ';
				echo '/>';
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
			 
	echo '<tr>';
	echo '<td colspan="2"></td>';
	echo '<td style="font-weith:bold;text-align:right">Totali</td>';
	echo '<td>'.$tot_importo_dovuto.' &euro;</td>';
	echo '<td>'.$tot_importo_richiesto.' &euro;</td>';
	echo '<td>'.$tot_importo_cash.' &euro;</td>';
	echo '<td colspan="4"></td>';
	echo '</tr>';
		
	echo '</table>';
	echo '</fieldset>';
	
echo $this->Form->hidden('request_payment_id',array('value' => $requestPaymentResults['RequestPayment']['id']));
echo $this->Form->hidden('delivery_id',array('value' => $delivery_id));
echo $this->Form->hidden('stato_elaborazione',array('value' => $requestPaymentResults['RequestPayment']['stato_elaborazione']));


echo $this->Form->end(__('Submit'));

echo '</div>';

echo $this->element('menuTesoriereRequestPaymentLaterale');
?>
<script type="text/javascript">
var debug = false;

function settaModalita(modalita) {
	jQuery(".modalita").each(function () {
		if(jQuery(this).attr('value')==modalita) 
			jQuery(this).prop('checked',true);
		else
			jQuery(this).prop('checked',false);
	});

}

function settaStato(stato) {
	jQuery(".stato").each(function () {
		if(jQuery(this).attr('value')==stato) 
			jQuery(this).prop('checked',true);
		else
			jQuery(this).prop('checked',false);
			
		var idRow = jQuery(this).attr('id');
		numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		gestStatoColor(numRow);
		
		if(stato=='PAGATO') {
			jQuery('#checkbox_saldato-'+numRow).prop('checked',true);		
			settaSaldato(numRow);
		}				
	});
}
	
function settaSaldato(numRow) {
	var checkbox_saldato = jQuery('#checkbox_saldato-'+numRow+':checked').val();
	var importo_richiesto = jQuery('#importo_richiesto-'+numRow).val();
	
	if(debug) {
		console.log('numRow '+numRow);
		console.log('importo_richiesto '+importo_richiesto);
		console.log('importo_pagato '+jQuery('#importo_pagato-'+numRow).val());
		console.log('checkbox_saldato '+checkbox_saldato);
	}
	
	if(checkbox_saldato==undefined) 
		inizialState(numRow);
	else {
		jQuery('#importo_pagato-'+numRow).val(importo_richiesto);
		jQuery('#statoPAGATO-'+numRow).prop('checked',true);
	}
		
	gestStatoColor(numRow);	
}
	
function gestStatoColor(numRow) {

	var stato = jQuery("input[name='data[RequestPayment][stato]["+numRow+"]']:checked").val()
			
	switch (stato)
	{
		case 'DAPAGARE':
			jQuery('#color-'+numRow).css('background-color', 'red').css('color', 'white');
		break;
		case 'SOLLECITO1':
		case 'SOLLECITO2':
			jQuery('#color-'+numRow).css('background-color', 'yellow').css('color', 'black');
		break;
		case 'SOSPESO':
			jQuery('#color-'+numRow).css('background-color', 'gray').css('color', 'white');
		break;
		case 'PAGATO':
			jQuery('#color-'+numRow).css('background-color', 'green').css('color', 'white');
		break;
		default:
			jQuery('#color-'+numRow).css('background-color', 'white').css('color', 'black');
		break;
	}	
}

function inizialState(numRow) {
	
	jQuery('#importo_pagato-'+numRow).val('0,00');	
	
	jQuery('#checkbox_saldato-'+numRow).prop('checked',false);
	jQuery('#statoPAGATO-'+numRow).prop('checked',false);
	jQuery('#statoSOLLECITO1-'+numRow).prop('checked',false);
	jQuery('#statoSOLLECITO2-'+numRow).prop('checked',false);
	jQuery('#statoSOSPESO-'+numRow).prop('checked',false);
	
	gestStatoColor(numRow);
}

jQuery(document).ready(function() {

	var debug = false;

	jQuery('#checkbox_saldato_all').click(function () {
		var checked_pagato_all = jQuery("input[name='checkbox_saldato_all']:checked").val();
		
		if(checked_pagato_all=='ALL') {
			jQuery('.checkbox_saldato').prop('checked',true);
			jQuery(".importo_pagato").each(function () {

				var idRow = jQuery(this).attr('id');
				numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);

				settaSaldato(numRow);
			});
		}
		else {
			jQuery('.checkbox_saldato').prop('checked',false);
			jQuery(".importo_pagato").each(function () {
				
				var idRow = jQuery(this).attr('id');
				numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
				
				jQuery('#importo_pagato-'+numRow).val('0,00');
				
				inizialState(numRow);
			});
		}	
	});
	
	/*
	 *  tasto checkbox SALDATO
	 */
	jQuery('.checkbox_saldato').click(function () {

		/* get id dell'oggetto  xxx-1  */
		var idRow = jQuery(this).attr('id');
		numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		
		settaSaldato(numRow);
	});
		
	jQuery("select[name='modalita_all']").change(function () {
		var modalita = jQuery(this).val();

		if(modalita!="") settaModalita(modalita);
	});

	jQuery("select[name='stato_all']").change(function () {
		var stato = jQuery(this).val();

		if(stato!="") settaStato(stato);
	});

	jQuery(".stato").change(function () {
		var idRow = jQuery(this).attr('id');
		numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);

		var stato = jQuery("input[name='data[RequestPayment][stato]["+numRow+"]']:checked").val()
			
		if(stato=='PAGATO') {
			jQuery('#checkbox_saldato-'+numRow).prop('checked', true);
			settaSaldato(numRow);
		}
		else {
			jQuery('#checkbox_saldato-'+numRow).prop('checked', false);
			jQuery('#importo_pagato-'+numRow).val('0,00');
		}
				
		gestStatoColor(numRow);
	});
	
	jQuery('#formGas').submit(function() {

		/*
		 *  controllo che se ho inserito l'importo devo aver scelto la modalita di pagamento
		 */
		 var importo_selected = false;
		 var modalita_selected = true;
		 jQuery(".importo_pagato").each(function () {

			var idRow = jQuery(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);

			var importo_pagato = jQuery('#importo_pagato-'+numRow).val();
			if(importo_pagato>0) {
				importo_selected = true;
				modalita_selected = jQuery("input[name='data[RequestPayment][modalita]["+numRow+"]']:checked").length;
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

	<?php 
	/*
	 * devo ripulire il campo hidden che inizia per page perche' dopo la prima pagina sbaglia la ricerca con filtri
	 */
	?>
	jQuery('.filter').click(function() {
		jQuery("input[name^='page']").val('');
	});
	
	jQuery('#box-account-close').click(function() {
		if(jQuery('#legendaOrderStateContent').css('display')=='block')  {
			jQuery('#legendaOrderStateContent').hide();
		}
		else 
			jQuery('#legendaOrderStateContent').show();

		return false;
	});

	jQuery('#legendaOrderState').click(function() {
		if(jQuery('#legendaOrderStateContent').css('display')=='block')  {
			jQuery('#legendaOrderStateContent').hide();
		}
		else 
			jQuery('#legendaOrderStateContent').show();

		return false;
	});	
	
	jQuery("input[name='stato_selected']").click(function() {
		var stato = jQuery(this).val();
		if(jQuery(this).is(':checked')) 
			jQuery('.stato'+stato).css('display','table-row');
		else
			jQuery('.stato'+stato).css('display','none');
	});	
});		
	</script>