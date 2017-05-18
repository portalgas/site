<?php echo $this->Session->flash(); // se cancello un elemento ho qui il msg ?>
<?php
if(isset($carts_splits_regenerated) && $carts_splits_regenerated) 
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => __('carts_splits_regenerated')));	

$tmp = '';
$tmp .= '<div class="clearfix"></div>';

$tmp .= '<table class="localSelector">';	
$tmp .= "\r\n";
$tmp .= '<tr>';
$tmp .= '	<th><input type="checkbox" id="box-user-all" name="box-user-all" value="ALL" /></th>';
$tmp .= '	<th colspan="3">Utente</th>';
$tmp .= '	<th>Prezzo</th>';
$tmp .= '	<th colspan="2">'.__('importo_forzato').'</th>';
$tmp .= '</tr>';	

$debug = false;
$user_id_old = 0;
$article_organization_id_old = 0;
$article_id_old = 0;

$tot_importo=0;
$tot_prezzo_user=0;
$tot_prezzo_impostato_user=0;
$tot_user=0;
$rowId_old = 0;
$i=0;
foreach($results as $numResult => $result) {

	$i++;

	if($debug) $tmpDebug = 'User '.$result['ProdCartsSplit']['user_id'].' ('.$user_id_old.') Article '.$result['ProdCartsSplit']['article_organization_id'].'/'.$result['ProdCartsSplit']['article_id'].' ('.$article_organization_id_old.'/'.$article_id_old.') '.$result['Article']['name'].' '.$result['ArticlesOrder']['prezzo'].' &euro;';


	$rowId = $result['ProdCartsSplit']['prod_delivery_id'].'_'.$result['ProdCartsSplit']['user_id'].'_'.$result['ProdCartsSplit']['article_organization_id'].'_'.$result['ProdCartsSplit']['article_id'];
	$rowId2 = $result['ProdCartsSplit']['prod_delivery_id'].'_'.$result['ProdCartsSplit']['user_id'].'_'.$result['ProdCartsSplit']['article_organization_id'].'_'.$result['ProdCartsSplit']['article_id'].'_'.$result['ProdCartsSplit']['num_split'];
	
	/*
	 * riga totale utente 
	 */
	if($user_id_old>0 && 
	   ($result['ProdCartsSplit']['article_organization_id'] != $article_organization_id_old || 
	    $result['ProdCartsSplit']['article_id'] != $article_id_old || 
	    $result['ProdCartsSplit']['user_id'] != $user_id_old)) {
		$tmp .= "\r\n";
		$tmp .= '<tr class="box-user-content'.$user_id_old.' user-totale" style="display:none;">';
		$tmp .= '	<td></td>';
		$tmp .= '	<td></td>';
		$tmp .= '	<td></td>';
		$tmp .= '	<td></td>';
		$tmp .= '	<th style="text-align:right;">'.number_format($tot_prezzo_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;</th>';
		
		$tmp .= '<th style="text-align:right;"><span id="importo-'.$rowId_old.'">'.number_format($tot_prezzo_impostato_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'</span>&nbsp;&euro;</th>';
			
		$tot_prezzo_user = 0;
		$tot_prezzo_impostato_user = 0;
			
		$tmp .= '<th>';
		if($debug)	$tmp .= $tmpDebug.' - '.$tot_prezzo_impostato_user;		
		$tmp .= '</th>';
		$tmp .= '</tr>';
	}
	
	
	/*
	 * riga user label
	 */
	if($result['ProdCartsSplit']['user_id'] != $user_id_old) {
		$tot_user++;
		
		$tmp .= "\r\n";
		$tmp .= '<tr class="box-user close" id="'.$result['ProdCartsSplit']['user_id'].'">';
		$tmp .= '	<td class="" style=" height: 32px;width: 32px;"></td>';
		$tmp .= '	<td colspan="4">'.$result['User']['name']; // $tot_user
		if(!empty($result['User']['email']))
			$tmp .= ' <a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a>';
		if(!empty($result['User']['Profile']['phone'])) $tmp .= ' '.$result['User']['Profile']['phone'].'<br />';
		if(!empty($result['User']['Profile']['phone2'])) $tmp .= ' '.$result['User']['Profile']['phone2'];
		$tmp .= '	</td>';	
		$tmp .= '	<td></td>';
		$tmp .= '	<td>';
		if($debug)	$tmp .= $tmpDebug.' - '.$tot_prezzo_impostato_user;		
		$tmp .= '</td>';
		$tmp .= '</tr>';
		
		$article_organization_id_old = 0;
		$article_id_old = 0;
	}
	
	
	/*
	 * riga article label
	 */
	if($result['ProdCartsSplit']['article_organization_id'] != $article_organization_id_old || 
	   $result['ProdCartsSplit']['article_id'] != $article_id_old) {
	   	   
		$tmp .= "\r\n";
		$tmp .= '<tr class="box-user-content'.$result['ProdCartsSplit']['user_id'].'" style="display:none;">';
		$tmp .= '	<td></td>';
		$tmp .= '	<td></td>';
		$tmp .= '	<td colspan="2">'.$result['Article']['name'].'</td>';
		$tmp .= '	<td style="text-align:right;">'.$result['ArticlesOrder']['prezzo_e'].'</td>';
		$tmp .= '	<td></td>';
		$tmp .= '	<td>';
		if($debug)	$tmp .= $tmpDebug.' - '.$tot_prezzo_impostato_user;
		$tmp .= '</td>';
		$tmp .= '</tr>';
	}

	/*
	 * riga setta importo_forzato 
	 */	
	$tmp .= "\r\n";
	$tmp .= '<tr class="box-user-content'.$result['ProdCartsSplit']['user_id'].'" style="display:none;">';
	$tmp .= '	<td></td>';
	$tmp .= '	<td></td>';
	$tmp .= '	<td></td>';
	$tmp .= '	<td></td>';
	$tmp .= '	<td></td>';

	$tmp .= '<td style="width: 100px;text-align:right;">';	
	$tmp .= '	<input tabindex="'.$i.'" type="text" value="'.$result['ProdCartsSplit']['importo_forzato_'].'" name="importo-'.$rowId2.'" id="importo-'.$rowId2.'" size="5" class="double importoSubmit" />&nbsp;<span>&euro;</span>';
	$tmp .= '</td>';

	$tot_prezzo_user += $result['ArticlesOrder']['prezzo'];
	$tot_prezzo_impostato_user += $result['ProdCartsSplit']['importo_forzato'];
	$tot_importo += $result['ArticlesOrder']['prezzo'];
	
	$user_id_old = $result['ProdCartsSplit']['user_id'];
	$article_organization_id_old = $result['ProdCartsSplit']['article_organization_id'];
	$article_id_old = $result['ProdCartsSplit']['article_id'];
			
	$tmp .= '<td>';	
	if($debug)	$tmp .= $tmpDebug.' - '.$tot_prezzo_impostato_user;
	$tmp .= '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$rowId2.'" class="buttonCarrello submitEcomm" />';
	$tmp .= '<div id="msgEcomm-'.$rowId2.'" class="msgEcomm"></div>';
	$tmp .= '</td>';
	$tmp .= '</tr>';
	
	$rowId_old = $rowId;
} // end foreach($results as $numResult => $result) 


/*
 * totale dell'ultimo utente
 */
$tmp .= "\r\n";
$tmp .= '<tr class="box-user-content'.$user_id_old.'" style="display:none;">';
$tmp .= '	<td></td>';
$tmp .= '	<td></td>';
$tmp .= '	<td></td>';
$tmp .= '	<td></td>';
$tmp .= '	<th style="text-align:right;">'.number_format($tot_prezzo_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;</th>';

$tmp .= '<th style="text-align:right;"><span id="importo-'.$rowId_old.'">'.number_format($tot_prezzo_impostato_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'</span>&nbsp;&euro;</th>';
	
$tmp .= '<th>';
if($debug)	$tmp .= $tmpDebug;		
$tmp .= '</th>';
$tmp .= '</tr>';

 
/*
 * totali, lo calcolo in modo dinamico
 */
$tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	
$tmp .= "\r\n";
$tmp .= '<tr>';
$tmp .= '	<td></td>';
$tmp .= '	<td></td>';
$tmp .= '	<td></td>';
$tmp .= '	<td style="font-size: 16px;text-align:right;font-weight: bold;">Totale</td>';
$tmp .= '	<td style="font-size: 16px;text-align:right;">'.number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;</td>';
$tmp .= '	<td style="font-size: 16px;text-align:right;"><span id="tot_importo"></span>&nbsp;&euro;</td>';
$tmp .= '	<td></td>';
$tmp .= '</tr>';
		
		
$tmp .= '</table>';
		
$tmp .= $this->Form->end(__('Submit'));

echo $tmp;
?>
<style type="text/css">
.cakeContainer table.localSelector tr:hover {
    background-color: #F9FFCC; 
}
.cakeContainer table.localSelector tr.user-totale:hover {
    background-color: #FFFFFF; 
}
.cakeContainer table.localSelector tr.box-user.open {
    background-color: #F9FFAA; 
}
.cakeContainer .box-user {cursor:pointer;}
</style>
<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('.box-user').click(function() {
		var idRow = jQuery(this).attr('id');
		
		if(jQuery('.box-user-content'+idRow).css('display')=='none')  {
			jQuery(this).removeClass('close');
			jQuery(this).addClass('open');
			jQuery('.box-user-content'+idRow).show();
		}	
		else {
			jQuery(this).removeClass('open');
			jQuery(this).addClass('close');
			jQuery('.box-user-content'+idRow).hide();
		}
	});

	jQuery('#prod_deliveries_article_key_selected_all').click(function () {
		
		if(checked=='ALL')
			jQuery('input[name=prod_deliveries_article_key_selected]').prop('checked',true);
		else
			jQuery('input[name=prod_deliveries_article_key_selected]').prop('checked',false);
	});


	jQuery('#box-user-all').click(function() {
		var checked = jQuery("input[name='box-user-all']:checked").val();
		
		if(checked=='ALL')  {
			jQuery('.box-user').removeClass('close');
			jQuery('.box-user').addClass('open');
			jQuery('[class^="box-user-content"]').show();
		}	
		else {
			jQuery('.box-user').removeClass('open');
			jQuery('.box-user').addClass('close');
			jQuery('[class^="box-user-content"]').hide();
		}
	});
		
	/*
	 * importo
	 * key prod_delivery_id-user_id-article_organization_id-article_id-num_split
	 */
	jQuery('.importoSubmit').change(function() {

		setNumberFormat(this);
		var idRow = jQuery(this).attr('id');
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		var key = numRow;
		
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
			url: "/administrator/index.php?option=com_cake&controller=ProdCartsSplits&action=setImporto&row_id="+numRow+"&key="+key+"&importo="+importo+"&format=notmpl",
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

	<?php 
	if(isset($hide_carts_splits_options)) {
	?>
	jQuery('#carts-splits-options').hide();
	<?php 
	}
	?>	
	
	setTotImporto();	
});

<?php
echo "function setTotImporto() {";
echo "\r\n";
echo "var tot_importo_article = 0;";
echo "\r\n";
echo "var tot_importo = 0;";

$user_id_old = 0;
$article_organization_id_old = 0;
$article_id_old = 0;
$rowId_old = 0;
foreach($results as $numResult => $result) {

	$rowId2 = $result['ProdCartsSplit']['prod_delivery_id'].'_'.$result['ProdCartsSplit']['user_id'].'_'.$result['ProdCartsSplit']['article_organization_id'].'_'.$result['ProdCartsSplit']['article_id'].'_'.$result['ProdCartsSplit']['num_split'];
	$rowId = $result['ProdCartsSplit']['prod_delivery_id'].'_'.$result['ProdCartsSplit']['user_id'].'_'.$result['ProdCartsSplit']['article_organization_id'].'_'.$result['ProdCartsSplit']['article_id'];

	if($user_id_old>0 && 
	   ($result['ProdCartsSplit']['article_organization_id'] != $article_organization_id_old || 
	    $result['ProdCartsSplit']['article_id'] != $article_id_old || 
	    $result['ProdCartsSplit']['user_id'] != $user_id_old)) {
		echo "\r\n";
		echo "tot_importo_article = number_format(tot_importo_article,2,',','.');  // in 1.000,50";
		echo "\r\n";
		echo "jQuery('#importo-".$rowId_old."').html(tot_importo_article);";
		echo "\r\n";
		echo "tot_importo_article = 0;";
		echo "\r\n";
		echo "\r\n";	    
	}
	
	echo "\r\n";
	echo "var importo = jQuery('#importo-".$rowId2."').val();";
	echo "\r\n";
	echo "importo = numberToJs(importo);   // in 1000.50";
	echo "\r\n";
	echo "tot_importo_article = (parseFloat(tot_importo_article) + parseFloat(importo));";
	echo "\r\n";
	echo "tot_importo = (parseFloat(tot_importo) + parseFloat(importo));";
	
	$user_id_old = $result['ProdCartsSplit']['user_id'];
	$article_organization_id_old = $result['ProdCartsSplit']['article_organization_id'];
	$article_id_old = $result['ProdCartsSplit']['article_id'];
	$rowId_old = $rowId;	
}
echo "\r\n";
echo "tot_importo = number_format(tot_importo,2,',','.');  // in 1.000,50";
echo "\r\n";
echo "jQuery('#tot_importo').html(tot_importo);";
echo "\r\n";
echo "}";
?>
</script>