<?php echo $this->Session->flash(); // se cancello un elemento ho qui il msg ?>
<?php
if(isset($summary_orders_regenerated) && $summary_orders_regenerated) 
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => __('summary_orders_regenerated')));	

$tmp = '';

$tmp .= $this->ExportDocs->delivery($results['Delivery'][0]);

$tmp .= '<div class="clearfix"></div>';


if($results['Delivery']['totOrders'] > 0) {
	$i=0;
	$tot_importo=0;
	foreach($results['Delivery'][0]['Order'] as $numOrder => $order) {	

		$i++;
		
		/*
		 * S U P P L I E R S _ O R G A N I Z A T I O N
		 */
		$tmp .= $this->ExportDocs->suppliersOrganizationShort($order['SuppliersOrganization']);

		if(isset($order['SummaryOrder'])) {
					
			$tmp .= '	<table class="selector">';
			$tmp .= '		<tr>';
			$tmp .= '			<th>'.__('N').'</th>';
			$tmp .= '			<th>'.__('User').'</th>';
			$tmp .= '			<th>Importo originale</th>';
			$tmp .= '			<th>Importo modificato</th>';
			$tmp .= '			<th class="actions">'.__('Actions').'</th>';
			$tmp .= '	</tr>';		
			
			/*
			 *   inserisci  N E W 
			 * */
			$rowId = $order['Order']['id'];
			
			$tmp .= "\r\n";
			$tmp .= '<tr>';
			$tmp .= '	<td></td>';
			$tmp .= '	<td>'.$this->Form->input('user_id',array('id' => 'adduser_id-'.$rowId, 'value' => $users, 'label' => false)).'</td>';
			$tmp .= '	<td></td>';
			$tmp .= '	<td>';
			$tmp .= '	<input tabindex="'.$i.'" type="text" value="" name="importo-'.$rowId.'" id="addimporto-'.$rowId.'" size="5" class="importo importoAdd double" />&nbsp;<span>&euro;</span>';
		
			/*
			 * non serve ma allinea l'input text con gli altri
			 */
			$tmp .= "\n";
			$tmp .= '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$rowId.'" class="buttonCarrello submitEcomm" />';
			$tmp .= "\n";
			$tmp .= '<div id="msgEcomm-'.$rowId.'" class="msgEcomm"></div>';
			$tmp .= "\n";
			
			$tmp .= '</td>';
			$tmp .= '	<td class="actions-table-img">';
			$tmp .= $this->Html->link(null, '',array('id' => 'add-'.$rowId, 'class' => 'action actionAdd add', 'title' => __('Add')));
			$tmp .= '	</td>';
			$tmp .= '</tr>';
						
			foreach($order['SummaryOrder'] as $numResult => $summaryOrder) {
				
				$i++;
				$rowId = $summaryOrder['SummaryOrder']['id'];
								
				$tmp .= "\r\n";
				$tmp .= '<tr>';
				$tmp .= '	<td>'.($numResult+1).'</td>';
				$tmp .= '	<td>'.$summaryOrder['User']['name'];
				if(!empty($summaryOrder['User']['email']))
				$tmp .= ' <a title="'.__('Email send').'" target="_blank" href="mailto:'.$summaryOrder['User']['email'].'">'.$summaryOrder['User']['email'].'</a>';
				if(!empty($summaryOrder['User']['Profile']['phone'])) $tmp .= ' '.$summaryOrder['User']['Profile']['phone'].'<br />';
				if(!empty($summaryOrder['User']['Profile']['phone2'])) $tmp .= ' '.$summaryOrder['User']['Profile']['phone2'];
				$tmp .= '	</td>';
						
				$tmp .= '<td>'.$summaryOrder['SummaryOrder']['importo_e'].'</td>';
					
				$tmp .= '<td>';	
				$tmp .= '	<input tabindex="'.$i.'" type="text" value="'.$summaryOrder['SummaryOrder']['importo_'].'" name="importo-'.$rowId.'" id="importo-'.$rowId.'" size="5" class="double importoSubmit" />&nbsp;<span>&euro;</span>';
				$tmp .= '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$rowId.'" class="buttonCarrello submitEcomm" />';
				$tmp .= '<div id="msgEcomm-'.$rowId.'" class="msgEcomm"></div>';
				$tmp .= '</td>';
				
				//$tmp .= '	<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($summaryOrder['SummaryOrder']['created']).'</td>';
				//$tmp .= '	<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($summaryOrder['SummaryOrder']['modified']).'</td>';
				$tmp .= '	<td class="actions-table-img">';
				$tmp .= $this->Html->link(null, '',array('id' => 'delete-'.$rowId, 'class' => 'action actionDelete delete', 'title' => __('Delete')));			
				$tmp .= '	</td>';
				$tmp .= '</tr>';
		
				$tot_importo += $summaryOrder['SummaryOrder']['importo'];
			}
		
			/*
			 * totali, lo calcolo in modo dinamico
			 */
			$tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				
			$tmp .= "\r\n";
			$tmp .= '<tr>';
			$tmp .= '	<td></td>';
			$tmp .= '	<td style="font-size: 16px;text-align:right;font-weight: bold;">Totale</td>';
			$tmp .= '	<td>'.$tot_importo.'</td>';
			$tmp .= '	<td style="font-size: 16px;"><span id="tot_importo"></span>&nbsp;&euro;</td>';
			$tmp .= '	<td></td>';
			$tmp .= '</tr>';
					
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

	/*
	 * importo
	 */
	jQuery('.importoSubmit').change(function() {

		setNumberFormat(this);

		var idRow = jQuery(this).attr('id');
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		var summary_orders_id = numRow;
		
		var importo = jQuery(this).val();
		if(importo=='' || importo==undefined) {
			alert("Devi indicare l'importo");
			jQuery(this).val("0,00");
			jQuery(this).focus();
			return false;
		}	
		
		if(importo=='0,00') {
			alert("L'importo dev'essere indicato con un valore maggior di 0");
			jQuery(this).focus();
			return false;
		}
					
		jQuery.ajax({
			type: "GET",
			url: "/administrator/index.php?option=com_cake&controller=SummaryOrders&action=setImporto&row_id="+numRow+"&summary_order_id="+summary_orders_id+"&importo="+importo+"&format=notmpl",
			data: "",
			success: function(response){
				 jQuery('#msgEcomm-'+numRow).html(response);
				 
				 setTotImporto();
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				 jQuery('#msgEcomm-'+numRow).html(textStatus);
				 jQuery('#submitEcomm-'+numRow).attr('src',app_img+'/blank32x32.png');
			}
		});
		return false;
	});

	/*
	 * delete
	 */
	jQuery('.delete').click(function() {

		if(!confirm("Sei sicuro di voler cancellare definitivamente il dettaglio dell'ordine?")) {
			return false;
		}
		
		var idRow = jQuery(this).attr('id');
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		var summary_orders_id = numRow;
		
		var delivery_id = jQuery('#delivery_id').val();

		/*
		 * l'ordine e' solo 1 dal menu a tendina
		 *		referente da Carts::managementCartsGroupByUsers 
		 */
		if(jQuery('#order_id').length>0) {
			var order_id    = jQuery('#order_id').val(); 
		}
		else  {
			/*
			 * l'ordine anche + di 1, da checkbox
			 *		Tesoriere::admin_orders_in_processing_summary_orders  
			 */
		
			var order_id_selected = '';
			for(i = 0; i < jQuery("input[name='order_id_selected']:checked").length; i++) {
				order_id_selected += jQuery("input[name='order_id_selected']:checked").eq(i).val()+',';
			}
	
			if(delivery_id=='') {
				alert("<?php echo __('jsAlertDeliveryRequired');?>");
				return false;
			}
			if(order_id_selected=='') {
				alert("<?php echo __('jsAlertOrderToRunRequired');?>");
				return false;
			}

			order_id = order_id_selected.substring(0,order_id_selected.length-1);
		}		
			
		jQuery('#doc-preview').css('display', 'block');
		jQuery('#doc-preview').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');

		jQuery.ajax({
			type: "get", 
			url : "/administrator/index.php?option=com_cake&controller=SummaryOrders&action=delete&delivery_id="+delivery_id+"&order_id="+order_id+"&id="+summary_orders_id+"&format=notmpl",
			data: "",  
			success: function(response) {
				jQuery('#doc-preview').css('background', 'none repeat scroll 0 0 transparent');
				jQuery("#doc-preview").html(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				jQuery('#doc-preview').css('background', 'none repeat scroll 0 0 transparent');
				jQuery('#doc-preview').html(textStatus);
			}
		});
		
		return false;
	});	

	jQuery('.importoAdd').change(function() {
		var idRow = jQuery(this).attr('id');  /* indica order_id */
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		var order_id = numRow;
		
		var importo = jQuery('#addimporto-'+numRow).val();
		
		if(!validateNumberField('#addimporto-'+numRow,'Importo')) return false;
		
		importo = numberToJs(importo);   /* in 1000.50 */
		importo = number_format(importo,2,',','.');  /* in 1.000,50 */
		jQuery('#addimporto-'+numRow).val(importo);
		importo = jQuery('#addimporto-'+numRow).val();

		return false;
	});	
			
	/*
	 * add
	 */
	jQuery('.add').click(function() {
		
		var idRow = jQuery(this).attr('id');  /* indica order_id */
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		var order_id_to_add = numRow;
		
		var user_id = jQuery('#adduser_id-'+numRow).val();
		var importo = jQuery('#addimporto-'+numRow).val();
		var delivery_id = jQuery('#delivery_id').val();

		/*
		 * l'ordine e' solo 1 dal menu a tendina
		 *		referente da Carts::managementCartsGroupByUsers 
		 */
		if(jQuery('#order_id').length>0) {
			var order_id    = jQuery('#order_id').val(); 
		}
		else  {
			/*
			 * l'ordine anche + di 1, da checkbox
			 *		Tesoriere::admin_orders_in_processing_summary_orders  
			 */
		
			var order_id_selected = '';
			for(i = 0; i < jQuery("input[name='order_id_selected']:checked").length; i++) {
				order_id_selected += jQuery("input[name='order_id_selected']:checked").eq(i).val()+',';
			}
	
			if(delivery_id=='') {
				alert("<?php echo __('jsAlertDeliveryRequired');?>");
				return false;
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
		jQuery('#addimporto-'+numRow).val(importo);
		importo = jQuery('#addimporto-'+numRow).val();

		jQuery('#doc-preview').css('display', 'block');
		jQuery('#doc-preview').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');

		jQuery.ajax({
			type: "get", 
			url : "/administrator/index.php?option=com_cake&controller=SummaryOrders&action=add&delivery_id="+delivery_id+"&order_id="+order_id+"&order_id_to_add="+order_id_to_add+"&user_id="+user_id+"&importo="+importo+"&format=notmpl",
			data: "",  
			success: function(response) {
				jQuery('#doc-preview').css('background', 'none repeat scroll 0 0 transparent');
				jQuery("#doc-preview").html(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				jQuery('#doc-preview').css('background', 'none repeat scroll 0 0 transparent');
				jQuery('#doc-preview').html(textStatus);
			}
		});
		
		return false;
	});

	<?php 
	if(isset($hide_summary_orders_options)) {
	?>
	jQuery('#summary-orders-options').hide();
	<?php 
	}
	?>	
	
	setTotImporto();	
});

function setTotImporto() {

	var tot_importo = 0;
	jQuery(".importoSubmit").each(function () {
		var importo = jQuery(this).val();
		
		importo = numberToJs(importo);   /* in 1000.50 */
			
		tot_importo = (parseFloat(tot_importo) + parseFloat(importo));
	});
	
	tot_importo = number_format(tot_importo,2,',','.');  /* in 1.000,50 */

	jQuery('#tot_importo').html(tot_importo);		
}
</script>