<?php echo $this->Session->flash(); // se cancello un elemento ho qui il msg ?>
<?php
if(isset($summary_des_orders_regenerated) && $summary_des_orders_regenerated) 
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => __('summary_des_orders_regenerated')));	

$tmp = '';


$i=0;
$tot_importo_orig=0;
$tot_importo=0;
$tmp .= '	<table class="selector">';
$tmp .= '		<tr>';
$tmp .= '			<th>'.__('N').'</th>';
$tmp .= '			<th colspan="2">'.__('Organization').'</th>';
$tmp .= '			<th>Importo originale</th>';
$tmp .= '			<th>% rispetto al totale</th>';
$tmp .= '			<th>Importo modificato</th>';
$tmp .= '			<th>Differenza</th>';
$tmp .= '			<th>Nota</th>';
$tmp .= '	</tr>';		
						
foreach($results as $numResult => $result) {
	
	$i++;
	$rowId = $result['SummaryDesOrder']['id'];
	
	$differenza = ($result['SummaryDesOrder']['importo_orig'] - $result['SummaryDesOrder']['importo']);
	$differenza = number_format($differenza,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					
	$tmp .= "\r\n";
	$tmp .= '<tr>';
	$tmp .= '	<td>'.($numResult+1).'</td>';
	$tmp .= '<td>';
	$tmp .= '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" />';
	$tmp .= '</td>';
	$tmp .= '<td>'.$result['Organization']['name'].'</td>';
			
	$tmp .= '<td>'.$result['SummaryDesOrder']['importo_orig_e'];
	$tmp .= $this->Form->hidden('importo_orig', array('id' => 'importo_orig-'.$rowId, 'value' => $result['SummaryDesOrder']['importo_orig']));
	$tmp .= '</td>';
	
        $tmp .= '<td>'.$result['SummaryDesOrder']['percentuale'].' %</td>';
        
	$tmp .= '<td>';	
	$tmp .= '<input tabindex="'.$i.'" type="text" value="'.$result['SummaryDesOrder']['importo_'].'" name="importo-'.$rowId.'" id="importo-'.$rowId.'" size="8" class="double noWidth importoSubmit" />&nbsp;<span>&euro;</span>';
	$tmp .= '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$rowId.'" class="buttonCarrello submitEcomm" />';
	$tmp .= '<div id="msgEcomm-'.$rowId.'" class="msgEcomm"></div>';
	$tmp .= '</td>';
	
	$tmp .= '<td><span id="differenza-'.$rowId.'">'.$differenza.'</span>&nbsp;&euro;</td>';
		
	$tmp .= '<td>';
	$tmp .= $this->Form->input('nota', array('tabindex'=>($i+1), 'id' => 'nota-'.$rowId, 'class' => 'nota noeditor', 'cols' => '50', 'value' => $result['SummaryDesOrder']['nota'], 'required' => 'false'));
	$tmp .= '</td>';
	$tmp .= '</tr>';

	$tot_importo_orig += $result['SummaryDesOrder']['importo_orig'];
	$tot_importo += $result['SummaryDesOrder']['importo'];
}
		

$tot_importo_orig = number_format($tot_importo_orig,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
// tot_importo, lo calcolo in modo dinamico
$tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')); 
	
$tmp .= "\r\n";
$tmp .= '<tr>';
$tmp .= '	<td></td>';
$tmp .= '	<td></td>';
$tmp .= '	<td style="font-size: 16px;text-align:right;font-weight: bold;">Totale</td>';
$tmp .= '	<td>'.$tot_importo_orig.'&nbsp;&euro;</td>';
$tmp .= '	<td></td>';
$tmp .= '	<td style="font-size: 16px;"><span id="tot_importo"></span>&nbsp;&euro;</td>';
$tmp .= '	<td></td>';
$tmp .= '	<td></td>';
$tmp .= '</tr>';
		
$tmp .= '</table>';
		
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
		var summary_des_orders_id = numRow;
		
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
			url: "/administrator/index.php?option=com_cake&controller=SummaryDesOrders&action=setImporto&row_id="+numRow+"&summary_des_order_id="+summary_des_orders_id+"&importo="+importo+"&format=notmpl",
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
	 * nota
	 */
	jQuery('.nota').change(function() {

		var idRow = jQuery(this).attr('id');
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		var summary_des_orders_id = numRow;
		
		var nota = jQuery(this).val();
		
		jQuery.ajax({
			type: "POST",
			url: "/administrator/index.php?option=com_cake&controller=SummaryDesOrders&action=setNota&row_id="+numRow+"&summary_des_order_id="+summary_des_orders_id+"&format=notmpl",
			data: "nota="+nota,
			success: function(response){
				jQuery('#msgEcomm-'+numRow).html(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				 jQuery('#msgEcomm-'+numRow).html(textStatus);
				 jQuery('#submitEcomm-'+numRow).attr('src',app_img+'/blank32x32.png');			
			}
		});
		return false;
	});

	<?php 
	if(isset($hide_summary_des_orders_options)) {
	?>
	jQuery('#summary-des_orders-options').hide();
	<?php 
	}
	?>	
	
	setTotImporto();	
});

function setTotImporto() {

	var tot_importo = 0;
	jQuery(".importoSubmit").each(function () {

		/*
		 * importo totale
		 */
		var importo = jQuery(this).val();
		importo = numberToJs(importo);   /* in 1000.50 */
			
		tot_importo = (parseFloat(tot_importo) + parseFloat(importo));
		
		/*
		 * differenza
		 */
		var idRow = jQuery(this).attr('id');  
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		var importo_orig = jQuery('#importo_orig-'+numRow).val();

		var differenza = (importo_orig - importo);
		differenza = (-1 * differenza);
		console.log("differenza "+differenza+" = ("+importo_orig+" - "+importo+")");
		differenza = number_format(differenza,2,',','.');  /* in 1.000,50 */
		jQuery('#differenza-'+numRow).html(differenza);				
	});
	
	tot_importo = number_format(tot_importo,2,',','.');  /* in 1.000,50 */

	jQuery('#tot_importo').html(tot_importo);		
}
</script>
