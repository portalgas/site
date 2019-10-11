<?php
echo '<div class="old-menu">';

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
$this->Html->addCrumb(__('List Request Payments'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

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
	<div class="table-responsive"><table class="table table-hover">
	<tr>
			<th></th>
			<th><?php echo $this->Paginator->sort('num', __('request_payment_num_short')); ?></th>
			<th><?php echo $this->Paginator->sort('user_id','Creata da'); ?></th>
			<th>Nota</th>
			<th style="text-align:center;"><?php echo __('Importo_totale');?></th>
			<th style="text-align:center;"><?php echo __('TotaleUsers');?></th>
			<th style="text-align:center;"><?php echo __('TotaleOrders');?></th>
			<?php 
			if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y')
				echo '<th style="text-align:center;">'.__('TotaleStorerooms').'</th>';
			?>				
			<th style="text-align:center;"><?php echo __('TotaleRequestPaymentGenerics');?></th>
			<th colspan="2"><?php echo $this->Paginator->sort('StatoElaborazione'); ?></th>
			<th><?php echo $this->Paginator->sort('data_send', __('DataSend')); ?></th>
			<th><?php echo $this->Paginator->sort('Created'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php 
	foreach ($requestPayments as $requestPayment) { 
		
		echo '<tr class="view">';
		echo '<td>';
		echo '<a data-toggle="collapse" href="#ajax_details-'.$requestPayment['RequestPayment']['id'].'" title="'.__('Href_title_expand').'"><i class="fa fa-3x fa-search-plus" aria-hidden="true"></i></a>';
	
	//	<a action="request_payment-'.$requestPayment['RequestPayment']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a>
		echo '</td>';
		echo '<td style="text-align:center;">'.$requestPayment['RequestPayment']['num'].'&nbsp;</td>';
		echo '<td>'.$requestPayment['User']['username'].'</td>';
			/*
			 *  campo nota
			 */
			echo '<td>';
				
			if(!empty($requestPayment['RequestPayment']['nota'])) 
				echo '<button type="button" class="btn btn-info" data-toggle="modal" data-target="#request_payment_nota_'.$requestPayment['RequestPayment']['id'].'" id="request_payment_btn_'.$requestPayment['RequestPayment']['id'].'"><i class="fa fa-2x fa-info-circle" aria-hidden="true" title="Leggi la nota del tesoriere"></i></button>';
			else
				echo '<button style="opacity:0.5;" type="button" class="btn btn-info" data-toggle="modal" data-target="#request_payment_nota_'.$requestPayment['RequestPayment']['id'].'" id="request_payment_btn_'.$requestPayment['RequestPayment']['id'].'"><i class="fa fa-2x fa-info-circle" aria-hidden="true" title="Nessuna nota da parte del tesoriere"></i></button>';
						
			echo '<div id="request_payment_nota_'.$requestPayment['RequestPayment']['id'].'" class="modal fade" role="dialog">';
			echo '<div class="modal-dialog">';
			echo '<div class="modal-content">';
			echo '<div class="modal-header">';
			echo '<button type="button" class="close" data-dismiss="modal">&times;</button>';
			echo '<h4 class="modal-title">Nota del tesoriere</h4>';
			echo '</div>';
			echo '<div class="modal-body clearfix">';
			// echo '<div style="color:red;font-size:20px;" id="esito-'.$requestPayment['RequestPayment']['id'].'"></div>';
			echo '<textarea class="form-control" id="notaText-'.$requestPayment['RequestPayment']['id'].'" name="nota" rows="20">'.$requestPayment['RequestPayment']['nota'].'</textarea>';
			echo '</p>';			
			echo '</p>';
			echo '</div>';
			echo '<div class="modal-footer">';
			echo '<button type="button" class="btn btn-primary" data-dismiss="modal" data-attr-id="'.$requestPayment['RequestPayment']['id'].'" id="request_payment_submit_'.$requestPayment['RequestPayment']['id'].'">'.__('Submit').'</button>';
			echo '<button type="button" class="btn btn-primary" data-dismiss="modal">'.__('Close').'</button>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
			echo '</div>';	

			echo '<script type="text/javascript">';
			echo "$('#request_payment_submit_".$requestPayment['RequestPayment']['id']."').click(function () {";
			echo "\r\n";
			echo '	var request_payment_id = $(this).attr("data-attr-id");';
			echo "\r\n";
			echo '	var notaText = escape($("#notaText-"+request_payment_id).val());';
			echo "\r\n";
			echo '	$.ajax({';
			echo '		type: "POST",';
			echo '		url: "/administrator/index.php?option=com_cake&controller=RequestPayments&action=setNota&request_payment_id="+request_payment_id+"&format=notmpl",';
			echo '		data: "notaText="+notaText,';
			echo "\r\n";
			echo '		success: function(response){';
			echo "\r\n";
			echo '			if(notaText=="") {';
			echo '				$("#request_payment_btn_"+request_payment_id).css("opacity","0.5");	';
			// echo '				$("#esito-"+request_payment_id).html("'.__('The request payments note has been saved').'");';
			echo '			}';
			echo "\r\n";
			echo '			else {';
			echo '				$("#request_payment_btn_"+request_payment_id).css("opacity","1");';
			// echo '				$("#esito-"+request_payment_id).html("'.__('The request payments note has been saved').'");';
			echo '			}';
			echo "\r\n";
			echo '		},';
			echo '		error:function (XMLHttpRequest, textStatus, errorThrown) {';
			echo '		}';
			echo '	});';
			echo '});';
			echo '</script>';
	
		echo '</td>';
		echo '<td style="text-align:center;">'.number_format($requestPayment['RequestPayment']['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;&nbsp;</td>';
		echo '<td style="text-align:center;">'.count($requestPayment['SummaryPayment']).'&nbsp;</td>';
		echo '<td style="text-align:center;">'.count($requestPayment['RequestPaymentsOrder']).'&nbsp;</td>';
		 
		if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y')
			echo '<td style="text-align:center;">'.$requestPayment['RequestPaymentsStoreroom']['totRequestPaymentsStoreroom'].'&nbsp;</td>';
		
		echo '<td style="text-align:center;">'.$requestPayment['RequestPaymentsGeneric']['totRequestPaymentsGeneric'].'&nbsp;</td>';
		echo '<td title="'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPayment['RequestPayment']['stato_elaborazione']).'" class="stato_'.strtolower($requestPayment['RequestPayment']['stato_elaborazione']).'"></td>';
		echo '<td>'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPayment['RequestPayment']['stato_elaborazione']).'</td>';
		echo '<td>'.$this->App->formatDateCreatedModifier($requestPayment['RequestPayment']['data_send']).'&nbsp;</td>';
		echo '<td>'.$this->App->formatDateCreatedModifier($requestPayment['RequestPayment']['created']).'&nbsp;</td>';
		echo '<td>';
		
		$modal_url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Tesoriere&action=sotto_menu_tesoriere_request_payment&request_payment_id='.$requestPayment['RequestPayment']['id'].'&position_img=bgLeft&format=notmpl'; 
		$modal_size = 'md'; // sm md lg
		$modal_header = __('request_payment_num_short').' '.$requestPayment['RequestPayment']['num'];
		if(!empty($requestPayment['RequestPayment']['tot_importo'])) 
			$modal_header .= ' di '.number_format($requestPayment['RequestPayment']['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		echo '<button type="button" class="btn btn-primary btn-menu" data-attr-url="'.$modal_url.'" data-attr-size="'.$modal_size.'" data-attr-header="'.$modal_header.'" ><i class="fa fa-2x fa-navicon"></i></button>';
		
		/*
		 * precedente gestione menu
		echo $this->Html->link(null, array('action' => 'edit_stato_elaborazione', $requestPayment['RequestPayment']['id']),array('class' => 'action actionOpen','title' => __('Edit Stato Elaborazione')));
		echo $this->Html->link(null, array('action' => 'edit', $requestPayment['RequestPayment']['id']),array('class' => 'action actionEdit','title' => __('Edit RequestPayment')));
		echo $this->Html->link(null, array('controller' => 'ExportDocs', 'action' => 'tesoriere_request_payment', $requestPayment['RequestPayment']['id'], 'doc_formato=EXCEL'),array('target' => '_blank', 'class' => 'action actionExcel','title' => __('Export RequestPayment'), 'alt' => __('Export RequestPayment'))); 
		echo $this->Html->link(null, array('action' => 'delete', $requestPayment['RequestPayment']['id']),array('class' => 'action actionDelete','title' => __('Delete')));
		*/
		echo '</td>';
		echo '</tr>';
		
		echo '<tr data-attr-action="request_payment-'.$requestPayment['RequestPayment']['id'].'" class="collapse ajax_details" id="ajax_details-'.$requestPayment['RequestPayment']['id'].'">';
		echo '	<td colspan="2"></td>'; 
		echo '	<td colspan="';
		echo ($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') ? '13' :'12';
		echo '" id="ajax_details_content-'.$requestPayment['RequestPayment']['id'].'"></td>';
		echo '</tr>';		

	} // end loop 
	
		echo '</table></div>';
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
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono ancora richieste di pagamento registrate"));

echo '</div>';

echo $this->element('legendaRequestPaymentStato');

echo $this->element('menuTesoriereLaterale');
?>
<script type="text/javascript">
$(document).ready(function() {
	$(".actionMenu").each(function () {
		$(this).click(function() {

			$('.menuDetails').css('display','none');
			
			var idRow = $(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			$('#menuDetails-'+numRow).show();

			viewOrderSottoMenu(numRow,"bgLeft");

			var offset = $(this).offset();
			var newTop = (offset.top - 100);
			var newLeft = (offset.left - 350);

			$('#menuDetails-'+numRow).offset({ top: newTop, left: newLeft});			
		});
	});	

	$(".menuDetailsClose").each(function () {
		$(this).click(function() {
			var idRow = $(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			$('#menuDetails-'+numRow).hide('slow');
		});
	});	

	$('.tesoriere_nota').click(function() {
		var id = $(this).attr('id');
		$("#esito-"+id).html("");
		$("#dialog-nota-"+id ).modal();
	});
});
</script>