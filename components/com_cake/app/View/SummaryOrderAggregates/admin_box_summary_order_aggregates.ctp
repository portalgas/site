<?php echo $this->Session->flash(); // se cancello un elemento ho qui il msg ?>
<?php
if(isset($summary_orders_regenerated) && $summary_orders_regenerated) 
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => __('summary_orders_regenerated')));	

$tmp = '';

$tmp .= $this->ExportDocs->delivery($results['Delivery'][0]);

$tmp .= '<div class="clearfix"></div>';


if($results['Delivery']['totOrders'] > 0) {
	$i=0;
	$tot_importo_originale=0;
	$tot_importo=0;
	foreach($results['Delivery'][0]['Order'] as $numOrder => $order) {	

		$i++;
		
		/*
		 * S U P P L I E R S _ O R G A N I Z A T I O N
		 */
		$tmp .= $this->ExportDocs->suppliersOrganizationShort($order['SuppliersOrganization']);

		if(isset($order['SummaryOrderAggregate'])) {
					
			$tmp .= '<div class="table-responsive"><table class="table table-hover table-striped">';
			$tmp .= '		<tr>';
			$tmp .= '			<th>'.__('N').'</th>';
			$tmp .= '			<th>'.__('User').'</th>';
			$tmp .= '			<th>'.__('importo_originale').'</th>';
			$tmp .= '			<th>'.__('importo_previous').'</th>';
			$tmp .= '			<th colspan="2">'.__('importo_change').'</th>';
			$tmp .= '			<th class="actions">'.__('Actions').'</th>';
			$tmp .= '	</tr>';		
			
			/*
			 *   inserisci  N E W 
			 * non lo gestisco + se no ho un user nuovo e x eventuali trasporti etc 
			$rowId = $order['Order']['id'];
			
			$tmp .= "\r\n";
			$tmp .= '<tr>';
			$tmp .= '	<td></td>';
			$tmp .= '	<td>'.$this->Form->input('user_id',array('id' => 'adduser_id-'.$rowId, 'value' => $users, 'label' => false)).'</td>';
			$tmp .= '	<td></td>';
			$tmp .= '	<td></td>';
			$tmp .= '	<td style="white-space:nowrap;padding-right:25px;">';
			$tmp .= '	<input tabindex="'.$i.'" type="text" value="" name="importo-'.$rowId.'" id="addimporto-'.$rowId.'" style="display:inline" class="importo importoAdd double form-control" />&nbsp;<span>&euro;</span>';
			$tmp .= '	</td>';
			
			 // non serve ma allinea l'input text con gli altri
			$tmp .= '<td>';
			$tmp .= '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$rowId.'" class="buttonCarrello submitEcomm" />';
			$tmp .= "\n";
			$tmp .= '<div id="msgEcomm-'.$rowId.'" class="msgEcomm"></div>';
			$tmp .= "\n";
			
			$tmp .= '</td>';
			$tmp .= '	<td class="actions-table-img">';
			$tmp .= $this->Html->link(null, '',array('id' => 'add-'.$rowId, 'class' => 'action actionAdd add', 'title' => __('Add')));
			$tmp .= '	</td>';
			$tmp .= '</tr>';
			* */
									 
			foreach($order['SummaryOrderAggregate'] as $numResult => $summaryOrderAggregate) {
				
				$i++;
				$rowId = $summaryOrderAggregate['SummaryOrderAggregate']['id'];
					
				if($summaryOrderAggregate['SummaryOrder']['saldato_a']==null)
					$saldato = false;
				else
					$saldato = true;
								
				$tmp .= "\r\n";
				$tmp .= '<tr>';
				$tmp .= '	<td>'.($numResult+1).'</td>';
				$tmp .= '	<td>'.$summaryOrderAggregate['User']['name'];
				if(!empty($summaryOrderAggregate['User']['email']))
				$tmp .= ' <a title="'.__('Email send').'" target="_blank" href="mailto:'.$summaryOrderAggregate['User']['email'].'">'.$summaryOrderAggregate['User']['email'].'</a>';
				if(!empty($summaryOrderAggregate['User']['Profile']['phone'])) $tmp .= ' '.$summaryOrderAggregate['User']['Profile']['phone'].'<br />';
				if(!empty($summaryOrderAggregate['User']['Profile']['phone2'])) $tmp .= ' '.$summaryOrderAggregate['User']['Profile']['phone2'];
				$tmp .= '	</td>';
						
				$tmp .= '<td style="text-align:center;">'.$summaryOrderAggregate['User']['totImporto_e'].'</td>';
				$tmp .= '<td style="text-align:center;">'.$summaryOrderAggregate['SummaryOrderAggregate']['importo_e'].'</td>';
					
				$tmp .= '<td style="white-space:nowrap;padding-right:25px;">';	
				if(!$saldato)
					$tmp .= '	<input tabindex="'.$i.'" type="text" value="'.$summaryOrderAggregate['SummaryOrderAggregate']['importo_'].'" name="importo-'.$rowId.'" id="importo-'.$rowId.'" style="display:inline" class="double importoSubmit form-control" />&nbsp;<span>&euro;</span>';
				else {
					$tmp .= '<div class="alert alert-info alert-dismissable">'; 
					$tmp .= sprintf(__('msg_summary_order_just_saldato_a'), strtolower($summaryOrderAggregate['SummaryOrder']['saldato_a']));
					$tmp .= '</div>';
				}
				$tmp .= '</td>';
				
				$tmp .= '<td>';
				$tmp .= '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$rowId.'" class="buttonCarrello submitEcomm" />';
				$tmp .= '<div id="msgEcomm-'.$rowId.'" class="msgEcomm"></div>';
				$tmp .= '</td>';
				
				//$tmp .= '	<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($summaryOrderAggregate['SummaryOrderAggregate']['created']).'</td>';
				//$tmp .= '	<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($summaryOrderAggregate['SummaryOrderAggregate']['modified']).'</td>';
				$tmp .= '	<td class="actions-table-img">';
				if(!$saldato)
					$tmp .= $this->Html->link(null, '',array('id' => 'delete-'.$rowId, 'class' => 'action actionDelete delete', 'title' => __('Delete')));			
				$tmp .= '	</td>';
				$tmp .= '</tr>';
		
				$tot_importo_originale += $summaryOrderAggregate['User']['totImporto'];
				$tot_importo += $summaryOrderAggregate['SummaryOrderAggregate']['importo'];
			}
		
			/*
			 * totali, lo calcolo in modo dinamico
			 */
			$tot_importo_originale_e = number_format($tot_importo_originale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$tot_importo_e = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				
			$tmp .= "\r\n";
			$tmp .= '<tr>';
			$tmp .= '	<td></td>';
			$tmp .= '	<td style="font-size: 16px;text-align:right;font-weight: bold;">Totale</td>';
			$tmp .= '	<td style="text-align:center;">'.$tot_importo_originale_e.'</td>';
			$tmp .= '	<td style="text-align:center;">'.$tot_importo_e.'</td>';
			$tmp .= '	<td style="font-size: 16px;"><span id="tot_importo"></span>&nbsp;&euro;</td>';
			$tmp .= '	<td></td>';
			$tmp .= '	<td></td>';
			$tmp .= '</tr>';
					
			$tmp .= '</table></div>';
			

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
$(document).ready(function() {

	/*
	 * importo
	 */
	$('.importoSubmit').change(function() {

		setNumberFormat(this);

		var idRow = $(this).attr('id');
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		var summary_order_aggregate_id = numRow;
		
		var importo = $(this).val();
		if(importo=='' || importo==undefined) {
			alert("Devi indicare l'importo");
			$(this).val("0,00");
			$(this).focus();
			return false;
		}	
		
		if(importo=='0,00') {
			alert("L'importo dev'essere indicato con un valore maggior di 0");
			$(this).focus();
			return false;
		}
					
		$.ajax({
			type: "GET",
			url: "/administrator/index.php?option=com_cake&controller=SummaryOrderAggregates&action=setImporto&row_id="+numRow+"&summary_order_aggregate_id="+summary_order_aggregate_id+"&importo="+importo+"&format=notmpl",
			data: "",
			success: function(response){
				 $('#msgEcomm-'+numRow).html(response);
				 
				 setTotImporto();
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				 $('#msgEcomm-'+numRow).html(textStatus);
				 $('#submitEcomm-'+numRow).attr('src',app_img+'/blank32x32.png');
			}
		});
		return false;
	});

	/*
	 * delete
	 */
	$('.delete').click(function() {

		if(!confirm("Sei sicuro di voler cancellare definitivamente il dettaglio dell'ordine?")) {
			return false;
		}
		
		var idRow = $(this).attr('id');
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		var summary_order_aggregate_id = numRow;
		var order_id = 0;
		
		/*
		 * l'ordine e' solo 1 dal menu a tendina
		 *		referente da Carts::managementCartsGroupByUsers 
		 */
		if($('#order_id').length>0) {
			order_id = $('#order_id').val(); 
		}
		else  {
			/*
			 * l'ordine anche + di 1, da checkbox
			 *		Tesoriere::admin_orders_in_processing_summary_orders  
			 */
		
			var order_id_selected = '';
			for(i = 0; i < $("input[name='order_id_selected']:checked").length; i++) {
				order_id_selected += $("input[name='order_id_selected']:checked").eq(i).val()+',';
			}
	
			if(order_id_selected=='') {
				alert("<?php echo __('jsAlertOrderToRunRequired');?>");
				return false;
			}

			order_id = order_id_selected.substring(0,order_id_selected.length-1);
		}		
			
		$('#doc-preview').css('display', 'block');
		$('#doc-preview').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');

		$.ajax({
			type: "get", 
			url : "/administrator/index.php?option=com_cake&controller=SummaryOrderAggregates&action=delete&order_id="+order_id+"&id="+summary_order_aggregate_id+"&format=notmpl",
			data: "",  
			success: function(response) {
				$('#doc-preview').css('background', 'none repeat scroll 0 0 transparent');
				$("#doc-preview").html(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				$('#doc-preview').css('background', 'none repeat scroll 0 0 transparent');
				$('#doc-preview').html(textStatus);
			}
		});
		
		return false;
	});	

	$('.importoAdd').change(function() {
		var idRow = $(this).attr('id');  /* indica order_id */
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		var order_id = numRow;
		
		var importo = $('#addimporto-'+numRow).val();
		
		if(!validateNumberField('#addimporto-'+numRow,'Importo')) return false;
		
		importo = numberToJs(importo);   /* in 1000.50 */
		importo = number_format(importo,2,',','.');  /* in 1.000,50 */
		$('#addimporto-'+numRow).val(importo);
		importo = $('#addimporto-'+numRow).val();

		return false;
	});	
			
	/*
	 * add
	 */
	$('.add').click(function() {
		
		var idRow = $(this).attr('id');  /* indica order_id */
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		var order_id_to_add = numRow;
		
		var user_id = $('#adduser_id-'+numRow).val();
		var importo = $('#addimporto-'+numRow).val();

		/*
		 * l'ordine e' solo 1 dal menu a tendina
		 *		referente da Carts::managementCartsGroupByUsers 
		 */
		if($('#order_id').length>0) {
			var order_id    = $('#order_id').val(); 
		}
		else  {
			/*
			 * l'ordine anche + di 1, da checkbox
			 *		Tesoriere::admin_orders_in_processing_summary_orders  
			 */
		
			var order_id_selected = '';
			for(i = 0; i < $("input[name='order_id_selected']:checked").length; i++) {
				order_id_selected += $("input[name='order_id_selected']:checked").eq(i).val()+',';
			}
	
			if(order_id_selected=='') {
				alert("<?php echo __('jsAlertOrderToRunRequired');?>");
				return false;
			}

			order_id = order_id_selected.substring(0,order_id_selected.length-1);
		}		
		
		if(user_id=='') {
			alert("<?php echo __('jsAlertUserRequired');?>");
			return false;
		}
		if(importo=='') {
			alert("<?php echo __('jsAlertImportRequired');?>");
			return false;
		}
		
		if(!validateNumberField('#addimporto-'+numRow,'Importo')) return false;
		
		importo = numberToJs(importo);   /* in 1000.50 */
		importo = number_format(importo,2,',','.');  /* in 1.000,50 */
		$('#addimporto-'+numRow).val(importo);
		importo = $('#addimporto-'+numRow).val();

		$('#doc-preview').css('display', 'block');
		$('#doc-preview').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');

		$.ajax({
			type: "get", 
			url : "/administrator/index.php?option=com_cake&controller=SummaryOrderAggregates&action=add&order_id="+order_id+"&order_id_to_add="+order_id_to_add+"&user_id="+user_id+"&importo="+importo+"&format=notmpl",
			data: "",  
			success: function(response) {
				$('#doc-preview').css('background', 'none repeat scroll 0 0 transparent');
				$("#doc-preview").html(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				$('#doc-preview').css('background', 'none repeat scroll 0 0 transparent');
				$('#doc-preview').html(textStatus);
			}
		});
		
		return false;
	});

	<?php 
	if(isset($hide_summary_orders_options)) {
	?>
	$('#summary-orders-options').hide();
	<?php 
	}
	?>	
	
	setTotImporto();	
});

function setTotImporto() {

	var tot_importo = 0;
	$(".importoSubmit").each(function () {
		var importo = $(this).val();
		
		importo = numberToJs(importo);   /* in 1000.50 */
			
		tot_importo = (parseFloat(tot_importo) + parseFloat(importo));
	});
	
	tot_importo = number_format(tot_importo,2,',','.');  /* in 1.000,50 */

	$('#tot_importo').html(tot_importo);		
}
</script>