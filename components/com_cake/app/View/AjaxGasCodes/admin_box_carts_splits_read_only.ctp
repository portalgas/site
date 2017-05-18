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

	if($debug) $tmpDebug = 'User '.$result['CartsSplit']['user_id'].' ('.$user_id_old.') Article '.$result['CartsSplit']['article_organization_id'].'/'.$result['CartsSplit']['article_id'].' ('.$article_organization_id_old.'/'.$article_id_old.') '.$result['Article']['name'].' '.$result['ArticlesOrder']['prezzo'].' &euro;';


	$rowId = $result['CartsSplit']['order_id'].'_'.$result['CartsSplit']['user_id'].'_'.$result['CartsSplit']['article_organization_id'].'_'.$result['CartsSplit']['article_id'];
	$rowId2 = $result['CartsSplit']['order_id'].'_'.$result['CartsSplit']['user_id'].'_'.$result['CartsSplit']['article_organization_id'].'_'.$result['CartsSplit']['article_id'].'_'.$result['CartsSplit']['num_split'];
	
	/*
	 * riga totale utente 
	 */
	if($user_id_old>0 && 
	   ($result['CartsSplit']['article_organization_id'] != $article_organization_id_old || 
	    $result['CartsSplit']['article_id'] != $article_id_old || 
	    $result['CartsSplit']['user_id'] != $user_id_old)) {
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
	if($result['CartsSplit']['user_id'] != $user_id_old) {
		$tot_user++;
		
		$tmp .= "\r\n";
		$tmp .= '<tr class="box-user close" id="'.$result['CartsSplit']['user_id'].'">';
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
	if($result['CartsSplit']['article_organization_id'] != $article_organization_id_old || 
	   $result['CartsSplit']['article_id'] != $article_id_old) {
	   	   
		$tmp .= "\r\n";
		$tmp .= '<tr class="box-user-content'.$result['CartsSplit']['user_id'].'" style="display:none;">';
		$tmp .= '	<td></td>';
		$tmp .= '	<td></td>';
		$tmp .= '	<td colspan="2">'.$result['ArticlesOrder']['name'].'</td>';
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
	$tmp .= '<tr class="box-user-content'.$result['CartsSplit']['user_id'].'" style="display:none;">';
	$tmp .= '	<td></td>';
	$tmp .= '	<td></td>';
	$tmp .= '	<td></td>';
	$tmp .= '	<td></td>';
	$tmp .= '	<td></td>';

	$tmp .= '<td style="width: 100px;text-align:right;">';	
	$tmp .= $result['CartsSplit']['importo_forzato_e'];
	$tmp .= '</td>';

	$tot_prezzo_user += $result['ArticlesOrder']['prezzo'];
	$tot_prezzo_impostato_user += $result['CartsSplit']['importo_forzato'];
	$tot_importo += $result['ArticlesOrder']['prezzo'];
	
	$user_id_old = $result['CartsSplit']['user_id'];
	$article_organization_id_old = $result['CartsSplit']['article_organization_id'];
	$article_id_old = $result['CartsSplit']['article_id'];
			
	$tmp .= '<td>';	
	if($debug)	$tmp .= $tmpDebug.' - '.$tot_prezzo_impostato_user;
	$tmp .= number_format($tot_prezzo_impostato_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
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
		
$tmp .= $this->Form->end();

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

	jQuery('#article_order_key_selected_all').click(function () {
		
		if(checked=='ALL')
			jQuery('input[name=article_order_key_selected]').prop('checked',true);
		else
			jQuery('input[name=article_order_key_selected]').prop('checked',false);
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
		
	<?php 
	if(isset($hide_carts_splits_options)) {
	?>
	jQuery('#carts-splits-options').hide();
	<?php 
	}
	?>	
});
</script>