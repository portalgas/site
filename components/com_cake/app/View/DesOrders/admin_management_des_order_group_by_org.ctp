<?php
$this->App->d($results);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('DesOrganizations'),array('controller' => 'DesOrganizations', 'action' => 'index', $user->des_id));
$this->Html->addCrumb(__('List DesOrders'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<h2 class="ico-management-carts-group-by-users">
	<?php echo __('Management Des Order Group By Gas');?>
</h2>

<?php 
/*
 *   inserisci  N E W 
 * */
$rowId = 0;
$html = '';
$html .= '	<table cellpadding="0" cellspacing="0">';
$html .= "\r\n";
$html .= '<tr>';
$html .= '	<td></td>';
$html .= '	<td>'.$this->Form->input('article_id',array('id' => 'addarticle_id-'.$rowId, 'value' => $articles, 'label' => false)).'</td>';
$html .= '	<td></td>';
$html .= '	<td>';
$html .= '	<input tabindex="'.$i.'" type="text" value="" name="qta-'.$rowId.'" id="addqta-'.$rowId.'" class="qta qtaAdd" />';

/*
 * non serve ma allinea l'input text con gli altri
 */
$html .= "\n";
$html .= '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$rowId.'" class="buttonCarrello submitEcomm" />';
$html .= "\n";
$html .= '<div id="msgEcomm-'.$rowId.'" class="msgEcomm"></div>';
$html .= "\n";

$html .= '</td>';
$html .= '	<td class="actions-table-img">';
$html .= $this->Html->link(null, '',array('id' => 'add-'.$rowId, 'class' => 'action actionAdd add', 'title' => __('Add')));
$html .= '	</td>';
$html .= '</tr>';
$html .= '</table>';
echo $html;


$html = '';
$html .= '	<table cellpadding="0" cellspacing="0">';
$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
$html .= '		<tr>';
$html .= '			<th width="">'.__('N').'</th>';
$html .= '			<th width="">'.__('Bio').'</th>';
	
$html .= '			<th width="">'.__('Codice').'</th>';
$html .= '			<th width="">'.__('Name').'</th>';


	
$html .= '			<th width="" style="text-align:center;">'.__('GAS').'</th>';
$html .= '			<th width="" style="text-align:center;">Quantit&agrave originale</th>';
$html .= '			<th width="" style="text-align:center;">Quantit&agrave modificata</th>';
$html .= '			<th width="" style="text-align:center;">'.__('PrezzoUnita').'</th>';
$html .= '			<th width="" style="text-align:right;">'.__('Importo').'</th>';
$html .= '	</tr>';
$html .= '	</thead><tbody>';



			
$tot_qta = 0;
$tot_importo = 0;			
$i=0;
foreach($results as $numResult => $result) {

		/*
		 *  ARTICOLO
		 */
		$html .= '<tr>';
		$html .= '	<td width="">'.($i+1).'</td>';
		$html .= '	<td width="">';
		if($result['Article']['bio']=='Y') $html .= 'Bio';
		$html .= '</td>';

		$html .= '			<td width="">'.$result['Article']['codice'].'</td>';
		$html .= '			<td width="">'.$result['ArticlesOrder']['name'].'</td>';
		
		$html .= '			<td width="" style="text-align:center;"></td>';							
		$html .= '			<td width="" style="text-align:center;"></td>';							
		$html .= '			<td width="" style="text-align:center;"></td>';				
		$html .= '			<td width="" style="text-align:center;">'.$this->App->getArticlePrezzo($result['ArticlesOrder']['prezzo']).'</td>';
		$html .= '			<td width="" style="text-align:right;"></td>';
		$html .= '</tr>';

		/*
		 *  GAS 
		 */		
		foreach($result['Article']['Organization'] as $numResult2 => $organizationResult) {
		
				$rowId = $organizationResult['Organization']['id'].'-'.$organizationResult['Article']['id'];
				
				$html .= '<tr>';
				$html .= '	<td width=""></td>';
				$html .= '	<td width=""></td>';
				$html .= '<td width=""></td>';
				$html .= '<td width=""></td>';

				
				$html .= '			<td width="" style="text-align:center;">'.$organizationResult['Organization']['name'].'</td>';				
					
				$html .= '			<td width="" style="text-align:center;">'.$organizationResult['Organization']['tot_qta'].'</td>';

				$html .= '<td>';	
				$html .= '	<input tabindex="'.$i.'" type="text" value="'.$organizationResult['Organization']['tot_qta'].'" name="qta-'.$rowId.'" id="qta-'.$rowId.'" class="qtaSubmit" />';
				$html .= '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$rowId.'" class="buttonCarrello submitEcomm" />';
				$html .= '<div id="msgEcomm-'.$rowId.'" class="msgEcomm"></div>';
				$html .= '</td>';
				
				$html .= '			<td width="" style="text-align:center;"></td>';  // '.$this->App->getArticlePrezzo($organizationResult['ArticlesOrder']['prezzo']).'
				$html .= '			<td width="" style="text-align:right;">'.number_format($organizationResult['Organization']['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
				$html .= '</tr>';
				
				$i++;
		}
		           
		/*
		 * sub totale
		 */
		$html .= '<tr>';
		$html .= '	<th width=""></th>';
		$html .= '	<th colspan="4" style="text-align:right;">'.__('qta_tot').'</th>';
		$html .= '	<th width="" style="text-align:center;">&nbsp;'.$result['Article']['tot_qta_sub'].'</th>';
		$html .= '	<th width="" colspan="3" style="text-align:right;">'.__('Importo_totale').'&nbsp;'.number_format($result['Article']['tot_importo_sub'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
		$html .= '</tr>';
		
		$tot_qta += $result['Article']['tot_qta_sub'];
		$tot_importo += $result['Article']['tot_importo_sub'];		
		
} // loop Articles
 
// totale
$html .= '<tr>';
$html .= '	<th width=""></th>';
$html .= '	<th colspan="4" style="text-align:right;">'.__('qta_tot').'</th>';
$html .= '	<th width="" style="text-align:center;">&nbsp;'.$tot_qta.'</th>';
$html .= '	<th width="" colspan="3" style="text-align:right;">'.__('Importo_totale').'&nbsp;'.number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';			
$html .= '</tr>';

$html .= '</tbody></table>';

echo $html;
?>
<script type="text/javascript">
$(document).ready(function() {

	/*
	 * qtaSubmit
	 */
	$('.qtaSubmit').change(function() {

		setNumberFormat(this);

		var idRow = $(this).attr('id');
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		var summary_des_orders_id = numRow;
		
		var qtaSubmit = $(this).val();
		if(qtaSubmit=='' || qtaSubmit==undefined) {
			alert("Devi indicare la quantità");
			$(this).val("0");
			$(this).focus();
			return false;
		}	
		
		if(qtaSubmit=='0') {
			alert("L'quantità dev'essere indicato con un valore maggior di 0");
			$(this).focus();
			return false;
		}
					
		$.ajax({
			type: "GET",
			url: "/administrator/index.php?option=com_cake&controller=SummaryDesOrders&action=setqta&row_id="+numRow+"&summary_des_order_id="+summary_des_orders_id+"&qtaSubmit="+qtaSubmit+"&format=notmpl",
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
		var summary_des_orders_id = numRow;
		
		$('#doc-preview').css('display', 'block');
		$('#doc-preview').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');

		$.ajax({
			type: "get", 
			url : "/administrator/index.php?option=com_cake&controller=SummaryDesOrders&action=delete&id="+summary_des_orders_id+"&format=notmpl",
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

	$('.qtaSubmitAdd').change(function() {
		var idRow = $(this).attr('id');  /* indica order_id */
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		var order_id = numRow;
		
		var qtaSubmit = $('#addqtaSubmit-'+numRow).val();
		
		if(!validateNumberField('#addqtaSubmit-'+numRow,'Importo')) return false;
		
		qtaSubmit = numberToJs(qtaSubmit);   /* in 1000.50 */
		qtaSubmit = number_format(qtaSubmit,2,',','.');  /* in 1.000,50 */
		$('#addqtaSubmit-'+numRow).val(qtaSubmit);
		qtaSubmit = $('#addqtaSubmit-'+numRow).val();

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
		var qtaSubmit = $('#addqtaSubmit-'+numRow).val();
		var delivery_id = $('#delivery_id').val();

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
	
			if(delivery_id=='') {
				alert("<?php echo __('jsAlertDeliveryRequired');?>");
				return false;
			}
			if(order_id_selected=='') {
				alert("<?php echo __('jsAlertOrderToRunRequired');?> ");
				return false;
			}

			order_id = order_id_selected.substring(0,order_id_selected.length-1);
		}		
		
		if(user_id=='') {
			alert("<?php echo __('jsAlertUserRequired');?>");
			return false;
		}
		if(qtaSubmit=='') {
			alert("<?php echo __('jsAlertQtaRequired');?>");
			return false;
		}
		
		if(!validateNumberField('#addqtaSubmit-'+numRow,'Importo')) return false;
		
		qtaSubmit = numberToJs(qtaSubmit);   /* in 1000.50 */
		qtaSubmit = number_format(qtaSubmit,2,',','.');  /* in 1.000,50 */
		$('#addqtaSubmit-'+numRow).val(qtaSubmit);
		qtaSubmit = $('#addqtaSubmit-'+numRow).val();

		$('#doc-preview').css('display', 'block');
		$('#doc-preview').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');

		$.ajax({
			type: "get", 
			url : "/administrator/index.php?option=com_cake&controller=SummaryDesOrders&action=add&delivery_id="+delivery_id+"&order_id="+order_id+"&order_id_to_add="+order_id_to_add+"&user_id="+user_id+"&qtaSubmit="+qtaSubmit+"&format=notmpl",
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
	
	setTotImporto();	
});

function setTotImporto() {

	var tot_importoSubmit = 0;
	$(".qtaSubmitSubmit").each(function () {
		var qtaSubmit = $(this).val();
		
		qtaSubmit = numberToJs(qtaSubmit);   /* in 1000.50 */
			
		tot_importoSubmit = (parseFloat(tot_importoSubmit) + parseFloat(qtaSubmit));
	});
	
	tot_importoSubmit = number_format(tot_importoSubmit,2,',','.');  /* in 1.000,50 */

	$('#tot_importoSubmit').html(tot_importoSubmit);		
}
</script>