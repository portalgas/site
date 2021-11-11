<?php 
// $this->App->dd($results);
echo $this->Session->flash(); // se cancello un elemento ho qui il msg 

if(isset($carts_splits_regenerated) && $carts_splits_regenerated) 
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => __('carts_splits_regenerated')));	

$tmp = '';
$tmp .= '<div class="clearfix"></div>';

$tmp .= '<div class="table-responsive"><table class="table table-hover table-striped localSelector">';	
$tmp .= "\r\n";
$tmp .= '<tr>';
//$tmp .= '	<th><input class="form-control" type="checkbox" id="box-user-all" name="box-user-all" value="ALL" /></th>';
$tmp .= '	<th colspan="4">'.__('Users').'</th>';
$tmp .= '	<th>'.__('Importo').'</th>';
$tmp .= '	<th colspan="2">'.__('Importo_forzato').'</th>';
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

	if($debug) $tmpDebug = 'User '.$result['CartsSplit']['user_id'].' ('.$user_id_old.') Article '.$result['CartsSplit']['article_organization_id'].'/'.$result['CartsSplit']['article_id'].' ('.$article_organization_id_old.'/'.$article_id_old.') '.$result['ArticlesOrder']['name'].' '.$result['ArticlesOrder']['prezzo'].'&nbsp;&euro;';


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
		$tmp .= '<tr class="box-user-content'.$user_id_old.' user-totale">'; // style="display:none;"
		$tmp .= '	<td></td>';
		$tmp .= '	<td></td>';
		$tmp .= '	<td></td>';
		$tmp .= '	<td></td>';
		$tmp .= '	<th style="text-align:left;">'.number_format($tot_prezzo_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
		
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
		$tmp .= '<tr class="box-user close-disabled" id="'.$result['CartsSplit']['user_id'].'">';
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
		$tmp .= '<tr class="box-user-content'.$result['CartsSplit']['user_id'].'" >'; // style="display:none;"
		$tmp .= '	<td></td>';
		$tmp .= '	<td></td>';
		$tmp .= '	<td colspan="2">'.$result['ArticlesOrder']['name'].'</td>';
		$tmp .= '	<td style="text-align:left;">'.$result['ArticlesOrder']['prezzo_e'].'</td>';
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
	$tmp .= '<tr class="box-user-content'.$result['CartsSplit']['user_id'].'" >'; // style="display:none;"
	$tmp .= '	<td></td>';
	$tmp .= '	<td></td>';
	$tmp .= '	<td></td>';
	$tmp .= '	<td></td>';
	$tmp .= '	<td></td>';

	$tmp .= '<td style="width: 100px;text-align:left;white-space: nowrap;">';	
	$tmp .= '	<input tabindex="'.$i.'" type="text" value="'.$result['CartsSplit']['importo_forzato_'].'" name="importo-'.$rowId2.'" id="importo-'.$rowId2.'" style="display:inline" class="double importoSubmit form-control" />&nbsp;<span>&euro;</span>';
	$tmp .= '</td>';

	$tot_prezzo_user += $result['ArticlesOrder']['prezzo'];
	$tot_prezzo_impostato_user += $result['CartsSplit']['importo_forzato'];
	$tot_importo += $result['ArticlesOrder']['prezzo'];
	
	$user_id_old = $result['CartsSplit']['user_id'];
	$article_organization_id_old = $result['CartsSplit']['article_organization_id'];
	$article_id_old = $result['CartsSplit']['article_id'];
			
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
$tmp .= '<tr class="box-user-content'.$user_id_old.'" >'; // style="display:none;"
$tmp .= '	<td></td>';
$tmp .= '	<td></td>';
$tmp .= '	<td></td>';
$tmp .= '	<td></td>';
$tmp .= '	<th style="text-align:left;">'.number_format($tot_prezzo_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';

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
$tmp .= '	<td style="font-size: 16px;text-align:left;">'.number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
$tmp .= '	<td style="font-size: 16px;text-align:right;"><span id="tot_importo"></span>&nbsp;&euro;</td>';
$tmp .= '	<td></td>';
$tmp .= '</tr>';
		
		
$tmp .= '</table></div>';
		
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
$(document).ready(function() {

	$('.box-user').click(function() {
		var idRow = $(this).attr('id');
		
		if($('.box-user-content'+idRow).css('display')=='none')  {
			$(this).removeClass('close');
			$(this).addClass('open');
			$('.box-user-content'+idRow).show();
		}	
		else {
			$(this).removeClass('open');
			$(this).addClass('close');
			$('.box-user-content'+idRow).hide();
		}
	});

	$('#article_order_key_selected_all').click(function () {
		
		if(checked=='ALL')
			$('input[name=article_order_key_selected]').prop('checked',true);
		else
			$('input[name=article_order_key_selected]').prop('checked',false);
	});


	$('#box-user-all').click(function() {
		var checked = $("input[name='box-user-all']:checked").val();
		
		if(checked=='ALL')  {
			$('.box-user').removeClass('close');
			$('.box-user').addClass('open');
			$('[class^="box-user-content"]').show();
		}	
		else {
			$('.box-user').removeClass('open');
			$('.box-user').addClass('close');
			$('[class^="box-user-content"]').hide();
		}
	});
		
	/*
	 * importo
	 * key order_id-user_id-article_organization_id-article_id-num_split
	 */
	$('.importoSubmit').change(function() {

		setNumberFormat(this);
		var idRow = $(this).attr('id');
		var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
		var key = numRow;
		
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
			url: "/administrator/index.php?option=com_cake&controller=CartsSplits&action=setImporto&row_id="+numRow+"&key="+key+"&importo="+importo+"&format=notmpl",
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

	<?php 
	if(isset($hide_carts_splits_options)) {
	?>
	$('#carts-splits-options').hide();
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

	$rowId2 = $result['CartsSplit']['order_id'].'_'.$result['CartsSplit']['user_id'].'_'.$result['CartsSplit']['article_organization_id'].'_'.$result['CartsSplit']['article_id'].'_'.$result['CartsSplit']['num_split'];
	$rowId = $result['CartsSplit']['order_id'].'_'.$result['CartsSplit']['user_id'].'_'.$result['CartsSplit']['article_organization_id'].'_'.$result['CartsSplit']['article_id'];

	if($user_id_old>0 && 
	   ($result['CartsSplit']['article_organization_id'] != $article_organization_id_old || 
	    $result['CartsSplit']['article_id'] != $article_id_old || 
	    $result['CartsSplit']['user_id'] != $user_id_old)) {
		echo "\r\n";
		echo "tot_importo_article = number_format(tot_importo_article,2,',','.');  // in 1.000,50";
		echo "\r\n";
		echo "$('#importo-".$rowId_old."').html(tot_importo_article);";
		echo "\r\n";
		echo "tot_importo_article = 0;";
		echo "\r\n";
		echo "\r\n";	    
	}
	
	echo "\r\n";
	echo "var importo = $('#importo-".$rowId2."').val();";
	echo "\r\n";
	echo "importo = numberToJs(importo);   // in 1000.50";
	echo "\r\n";
	echo "tot_importo_article = (parseFloat(tot_importo_article) + parseFloat(importo));";
	echo "\r\n";
	echo "tot_importo = (parseFloat(tot_importo) + parseFloat(importo));";
	
	$user_id_old = $result['CartsSplit']['user_id'];
	$article_organization_id_old = $result['CartsSplit']['article_organization_id'];
	$article_id_old = $result['CartsSplit']['article_id'];
	$rowId_old = $rowId;	
}
echo "\r\n";
echo "tot_importo = number_format(tot_importo,2,',','.');  // in 1.000,50";
echo "\r\n";
echo "$('#tot_importo').html(tot_importo);";
echo "\r\n";
echo "}";
?>
</script>