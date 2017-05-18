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
$this->Html->addCrumb(__('List Request Payments'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="request_payment">
	<h2 class="ico-pay">
		<?php echo __('List Request Payments');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('New Request Payment'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('New Request Payment'))); ?></li>
			</ul>
		</div>
	</h2>
	
	<?php
	if(!empty($requestPayments)) {
	?>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th></th>
			<th><?php echo $this->Paginator->sort('num', __('request_payment_num_short')); ?></th>
			<th><?php echo $this->Paginator->sort('user_id','Creata da'); ?></th>
			<th>Nota</th>
			<th>Totale Importo</th>
			<th>Totale utenti</th>
			<th>Totale ordini</th>
			<?php 
			if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y')
				echo '<th>Totale dispense</th>';
			?>				
			<th>Totale voci di spesa</th>
			<th colspan="2"><?php echo $this->Paginator->sort('stato_elaborazione'); ?></th>
			<th><?php echo $this->Paginator->sort('data_send'); ?></th>
			<th><?php echo $this->Paginator->sort('Created'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($requestPayments as $requestPayment): ?>
	<tr class="view">
		<td><a action="request_payment-<?php echo $requestPayment['RequestPayment']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>	
		<td><?php echo $requestPayment['RequestPayment']['num']; ?>&nbsp;</td>
		<td><?php echo $requestPayment['User']['username']; ?></td>
		<?php
			/*
			 *  campo nota
			 */
			echo '<td>';
				
			if(!empty($requestPayment['RequestPayment']['nota'])) 
				echo '<img style="cursor:pointer;" class="tesoriere_nota" id="'.$requestPayment['RequestPayment']['id'].'" src="'.Configure::read('App.img.cake').'/icon-28-info.png" title="Leggi la nota del tesoriere" border="0" />';
			else
				echo '<img style="cursor:pointer;opacity:0.5;" class="tesoriere_nota" id="'.$requestPayment['RequestPayment']['id'].'" src="'.Configure::read('App.img.cake').'/icon-28-info.png" title="Nessuna nota da parte del tesoriere" border="0" />';
		
			echo '<div id="dialog-nota-'.$requestPayment['RequestPayment']['id'].'" data-attr-id="'.$requestPayment['RequestPayment']['id'].'" title="Nota del tesoriere">';
			echo '<p>';
			echo '<div style="color:red;font-size:20px;" id="esito-'.$requestPayment['RequestPayment']['id'].'"></div>';
			echo '<textarea class="noeditor" id="notaText-'.$requestPayment['RequestPayment']['id'].'" name="nota" style="width: 100%;" rows="20">'.$requestPayment['RequestPayment']['nota'].'</textarea>';
			echo '</p>';
			echo '</div>';
			
			echo '<script type="text/javascript">';
			echo 'jQuery("#dialog-nota-'.$requestPayment['RequestPayment']['id'].'" ).dialog({';
			echo "\r\n";
			echo '	autoOpen: false,';
			echo "\r\n";
			echo '	height: 550,';
			echo "\r\n";
			echo '	width: 800,';
			echo "\r\n";
			echo '	modal: true,';
			echo "\r\n";
			echo '	buttons: {';
			echo "\r\n";
			echo '		"Chiudi": function() {';
			echo '			jQuery( this ).dialog( "close" );';
			echo '		},';

			echo '"'.__('Submit').'": function(event, ui) {';
			echo '		var request_payment_id = jQuery(this).attr("data-attr-id");';
			echo "\r\n";
			echo '	var notaText = jQuery("#notaText-"+request_payment_id).val();';
			echo "\r\n";
			echo '	jQuery.ajax({';
			echo '		type: "POST",';
			echo '		url: "/administrator/index.php?option=com_cake&controller=RequestPayments&action=setNota&request_payment_id="+request_payment_id+"&format=notmpl",';
			echo '		data: "notaText="+notaText,';
			echo "\r\n";
			echo '		success: function(response){';
			echo "\r\n";
			echo '			if(notaText=="") {';
			echo '				jQuery("#"+request_payment_id).css("opacity","0.5");	';
			echo '				jQuery("#esito-"+request_payment_id).html("'.__('The request payments note has been saved').'");';
			echo '			}';
			echo "\r\n";
			echo '			else {';
			echo '				jQuery("#"+request_payment_id).css("opacity","1");';
			echo '				jQuery("#esito-"+request_payment_id).html("'.__('The request payments note has been saved').'");';
			echo '			}';
			echo "\r\n";
			echo '		},';
			echo '		error:function (XMLHttpRequest, textStatus, errorThrown) {';
			echo '		}';
			echo '	});';
			echo '	return false;';
			echo "\r\n";
			echo '	}';			
			echo "\r\n";
			echo '	},';
			echo '});';
			echo '</script>';
	
			echo '</td>';
			?>
		<td><?php echo number_format($requestPayment['RequestPayment']['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;'; ?>&nbsp;</td>
		<td style="text-align:center;"><?php echo count($requestPayment['SummaryPayment']); ?>&nbsp;</td>
		<td style="text-align:center;"><?php echo count($requestPayment['RequestPaymentsOrder']); ?>&nbsp;</td>
		<?php 
		if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y')
			echo '<td style="text-align:center;">'.$requestPayment['RequestPaymentsStoreroom']['totRequestPaymentsStoreroom'].'&nbsp;</td>';
		?>	
		<td style="text-align:center;"><?php echo $requestPayment['RequestPaymentsGeneric']['totRequestPaymentsGeneric']; ?>&nbsp;</td>
		<td title="<?php echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPayment['RequestPayment']['stato_elaborazione']);?>" class="stato_<?php echo strtolower($requestPayment['RequestPayment']['stato_elaborazione']); ?>"></td>
		<td><?php echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPayment['RequestPayment']['stato_elaborazione']);?></td>
		<td><?php echo $this->App->formatDateCreatedModifier($requestPayment['RequestPayment']['data_send']); ?>&nbsp;</td>
		<td><?php echo $this->App->formatDateCreatedModifier($requestPayment['RequestPayment']['created']); ?>&nbsp;</td>
		<td class="actions-table-img-4">
			<?php echo $this->Html->link(null, array('action' => 'edit_stato_elaborazione', $requestPayment['RequestPayment']['id']),array('class' => 'action actionOpen','title' => __('Edit Stato Elaborazione'))); ?>
			<?php echo $this->Html->link(null, array('action' => 'edit', $requestPayment['RequestPayment']['id']),array('class' => 'action actionEdit','title' => __('Edit RequestPayment'))); ?>
			<?php echo $this->Html->link(null, array('controller' => 'ExportDocs', 'action' => 'tesoriere_request_payment', $requestPayment['RequestPayment']['id'], 'doc_formato=EXCEL'),array('target' => '_blank', 'class' => 'action actionExcel','title' => __('Export RequestPayment'), 'alt' => __('Export RequestPayment'))); ?>
			<?php echo $this->Html->link(null, array('action' => 'delete', $requestPayment['RequestPayment']['id']),array('class' => 'action actionDelete','title' => __('Delete'))); ?>
		</td>
	</tr>
	<tr class="trView" id="trViewId-<?php echo $requestPayment['RequestPayment']['id'];?>">
		<td colspan="2"></td> 
		<td colspan="<?php echo ($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') ? '12' :'11';?>" id="tdViewId-<?php echo $requestPayment['RequestPayment']['id'];?>"></td>
	</tr>	
	<?php endforeach; 
	
		echo '</table>';
		echo '<p>';
		
		echo $this->Paginator->counter(array(
		'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
		));
		
		echo '</p>';
	
		echo '<div class="paging">';

		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
		
		echo '</div>';

	} 
	else  
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => "Non ci sono ancora richieste di pagamento registrate"));

echo '</div>';

echo $this->element('legendaRequestPaymentStato');

echo $this->element('menuTesoriereLaterale');
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(".actionMenu").each(function () {
		jQuery(this).click(function() {

			jQuery('.menuDetails').css('display','none');
			
			var idRow = jQuery(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			jQuery('#menuDetails-'+numRow).show();

			viewOrderSottoMenu(numRow,"bgLeft");

			var offset = jQuery(this).offset();
			var newTop = (offset.top - 100);
			var newLeft = (offset.left - 350);

			jQuery('#menuDetails-'+numRow).offset({ top: newTop, left: newLeft});			
		});
	});	

	jQuery(".menuDetailsClose").each(function () {
		jQuery(this).click(function() {
			var idRow = jQuery(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			jQuery('#menuDetails-'+numRow).hide('slow');
		});
	});	

	jQuery('.tesoriere_nota').click(function() {
		var id = jQuery(this).attr('id');
		jQuery("#esito-"+id).html("");
		jQuery("#dialog-nota-"+id ).dialog("open");
	});

});
</script>