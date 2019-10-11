<?php 
if(!empty($results))
	echo $this->element('boxMsg', array('class_msg' => 'message', 'msg' => __('OrderToValidateError')));
?>
<div class="left label" style="width:125px !important;">Opzioni documento</div>
<div class="left radio">
	<p>
		<input type="radio" name="doc_options" id="to-users-all-modify" value="to-users-all-modify" /><label for="to-users-all-modify"><?php echo __('to_users_all_modify');?></label>
	</p>
	<p>
		<input type="radio" name="doc_options" id="to-users" value="to-users" /><label for="to-users"><?php echo __('to_users');?></label>
	</p>
	<p>
		<input type="radio" name="doc_options" id="to-users-label" value="to-users-label" /><label for="to-users-label"><?php echo __('to_users_label');?></label>
	</p>
	<?php
	/*
	 *  per report to-articles-monitoring 
	 *      Order.state_code = 'OPEN' OR Order.state_code = 'PROCESSED-BEFORE-DELIVERY'
	 */
	if($isToValidate || $toQtaMassima || $toQtaMinimaOrder) { 
		echo '<p>';
		echo' <input type="radio" name="doc_options" id="to-articles-monitoring" value="to-articles-monitoring" /><label for="to-articles-monitoring">'.__('to_articles_monitoring').'</label> ';
		echo '</p>';
	}
	?>
	<p>
		<input type="radio" name="doc_options" id="to-articles" value="to-articles" /><label for="to-articles"><?php echo __('to_articles');?></label>
	</p>
	<p>
		<input type="radio" name="doc_options" id="to-articles-details" value="to-articles-details" /><label for="to-articles-details"><?php echo __('to_articles_details');?></label>
	</p>
</div>

<style type="text/css">
.box-options {
	border:1px solid #DEDEDE;
	border-radius:8px;
	margin:10px;
	padding:8px; 
	display:none;
}
</style>
<div class="left setting" style="width:30%;">
		<?php
		if($hasTrasport=='Y') {
			
			echo '<div id="setting-to-users-all-modify" class="box-options">';
			
			$id = '1';
			
			echo '<p>';
			echo '<label for="trasport'.$id.'" style="width:auto !important;">Visualizzo l\'importo del <b>trasporto</b></label>&nbsp;&nbsp;';
			echo '<input type="radio" name="trasport'.$id.'" id="trasport'.$id.'_N" value="N" ';
			if($trasport=='0.00') echo 'checked';
			echo '/> No';
			echo '<input type="radio" name="trasport'.$id.'" id="trasport'.$id.'_Y" value="Y" ';
			if($trasport!='0.00') echo 'checked';
			echo '/> Si';
			echo '</p>';
			
			echo '</div>';
		}
		?>
	<div id="setting-to-users" class="box-options">
		<?php
		if($hasTrasport=='Y') {
			$id = '2';
							
			echo '<p>';
			echo '<label for="trasport'.$id.'" style="width:auto !important;">Visualizzo l\'importo del <b>trasporto</b></label>&nbsp;&nbsp;';
			echo '<input type="radio" name="trasport'.$id.'" id="trasport'.$id.'_N" value="N" ';
			if($trasport=='0.00') echo 'checked';
			echo '/> No';
			echo '<input type="radio" name="trasport'.$id.'" id="trasport'.$id.'_Y" value="Y" ';
			if($trasport!='0.00') echo 'checked';
			echo '/> Si';
			echo '</p>';
		}
		?>	
		<p>
			<label for="user_phone1" style="width:auto !important;">Visualizzo il <b>telefono</b> degli utenti</label>&nbsp;&nbsp;
			<input type="radio" name="user_phone1" id="user_phone1_N" value="N" /> No
			<input type="radio" name="user_phone1" id="user_phone1_Y" value="Y" checked /> Si
		</p>
		<p>
			<label for="user_email1" style="width:auto !important;">Visualizzo la <b>mail</b> degli utenti</label>&nbsp;&nbsp;
			<input type="radio" name="user_email1" id="user_email1_N" value="N" checked /> No
			<input type="radio" name="user_email1" id="user_email1_Y" value="Y" /> Si
		</p>
		<p>
			<label for="user_address1" style="width:auto !important;">Visualizzo l'<b>indirizzo</b> degli utenti</label>&nbsp;&nbsp;
			<input type="radio" name="user_address1" id="user_address1_N" value="N" checked /> No
			<input type="radio" name="user_address1" id="user_address1_Y" value="Y" /> Si
		</p>	
		<p>
			<label for="totale_per_utente" style="width:auto !important;">Visualizzo il <b>totale</b> per ogni utente</label>&nbsp;&nbsp;
			<input type="radio" name="totale_per_utente" id="totale_per_utente_N" value="N" /> No
			<input type="radio" name="totale_per_utente" id="totale_per_utente_Y" value="Y" checked /> Si
		</p>
		<p>
			<label for="dettaglio_per_utente" style="width:auto !important;">Visualizzo il <b>dettaglio</b> per ogni utente</label>&nbsp;&nbsp;
			<input type="radio" name="dettaglio_per_utente" id="dettaglio_per_utente_N" value="N" /> No
			<input type="radio" name="dettaglio_per_utente" id="dettaglio_per_utente_Y" value="Y" checked /> Si
		</p>
		<p>
			<label for="user_avatar1" style="width:auto !important;">Visualizzo la <b>foto</b> dell'utente</label>&nbsp;&nbsp;
			<input type="radio" name="user_avatar1" id="user_avatar1_N" value="N" checked /> No
			<input type="radio" name="user_avatar1" id="user_avatar1_Y" value="Y" /> Si
		</p>
		<p>
			<label for="note1" style="width:auto !important;">Visualizzo colonna <b>note</b></label>&nbsp;&nbsp;
			<input type="radio" name="note1" id="note1_N" value="N" /> No
			<input type="radio" name="note1" id="note1_Y" value="Y" checked /> Si
		</p>
		<p>
			<label for="delete_to_referent1" style="width:auto !important;">Visualizzo gli articoli <b>cancellati</b></label>&nbsp;&nbsp;
			<input type="radio" name="delete_to_referent1" id="delete_to_referent1_N" value="N" checked /> No
			<input type="radio" name="delete_to_referent1" id="delete_to_referent1_Y" value="Y" /> Si
		</p>	
	</div> 
	<div id="setting-to-users-label" class="box-options">
		<?php
		if($hasTrasport=='Y') {
			$id = '3';
				
			echo '<p>';
			echo '<label for="trasport'.$id.'" style="width:auto !important;">Visualizzo l\'importo del <b>trasporto</b></label>&nbsp;&nbsp;';
			echo '<input type="radio" name="trasport'.$id.'" id="trasport'.$id.'_N" value="N" ';
			if($trasport=='0.00') echo 'checked';
			echo '/> No';
			echo '<input type="radio" name="trasport'.$id.'" id="trasport'.$id.'_Y" value="Y" ';
			if($trasport!='0.00') echo 'checked';
			echo '/> Si';
			echo '</p>';
		}
		?>
		<p>
			<label for="user_phone" style="width:auto !important;">Visualizzo il <b>telefono</b> degli utenti</label>&nbsp;&nbsp;
			<input type="radio" name="user_phone" id="user_phone_N" value="N" /> No
			<input type="radio" name="user_phone" id="user_phone_Y" value="Y" checked /> Si
		</p>
		<p>
			<label for="user_email" style="width:auto !important;">Visualizzo la <b>mail</b> degli utenti</label>&nbsp;&nbsp;
			<input type="radio" name="user_email" id="user_email_N" value="N" checked /> No
			<input type="radio" name="user_email" id="user_email_Y" value="Y" /> Si
		</p>
		<p>
			<label for="user_address" style="width:auto !important;">Visualizzo l'<b>indirizzo</b> degli utenti</label>&nbsp;&nbsp;
			<input type="radio" name="user_address" id="user_address_N" value="N" checked /> No
			<input type="radio" name="user_address" id="user_address_Y" value="Y" /> Si
		</p>
		<p>
			<label for="user_avatar2" style="width:auto !important;">Visualizzo la <b>foto</b> dell'utente</label>&nbsp;&nbsp;
			<input type="radio" name="user_avatar2" id="user_avatar2_N" value="N" checked /> No
			<input type="radio" name="user_avatar2" id="user_avatar2_Y" value="Y" /> Si
		</p>	
		<p>
			<label for="delete_to_referent2" style="width:auto !important;">Visualizzo gli articoli <b>cancellati</b></label>&nbsp;&nbsp;
			<input type="radio" name="delete_to_referent2" id="delete_to_referent2_N" value="N" checked /> No
			<input type="radio" name="delete_to_referent2" id="delete_to_referent2_Y" value="Y" /> Si
		</p>			
	</div>
	
	<div id="setting-to-articles" class="box-options">
		<?php
		if($hasTrasport=='Y') {
			$id = '4';
		
			echo '<p>';
			echo '<label for="trasport'.$id.'" style="width:auto !important;">Visualizzo l\'importo del <b>trasporto</b></label>&nbsp;&nbsp;';
			echo '<input type="radio" name="trasport'.$id.'" id="trasport'.$id.'_N" value="N" ';
			if($trasport=='0.00') echo 'checked';
			echo '/> No';
			echo '<input type="radio" name="trasport'.$id.'" id="trasport'.$id.'_Y" value="Y" ';
			if($trasport!='0.00') echo 'checked';
			echo '/> Si';
			echo '</p>';
	
			echo '';
		}
		
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
		?>	
			<p>
				<label for="codice2" style="width:auto !important;">Visualizzo il <b>codice</b> dell'articolo</label>&nbsp;&nbsp;
				<input type="radio" name="codice2" id="codice2_N" value="N" checked /> No
				<input type="radio" name="codice2" id="codice2_Y" value="Y" /> Si
			</p>
		<?php 
		}
		?>
	</div>
		
	<div id="setting-to-articles-details" class="box-options">
		<?php
		if($hasTrasport=='Y') {
			$id = '5';
		
			echo '<p>';
			echo '<label for="trasport'.$id.'" style="width:auto !important;">Visualizzo l\'importo del <b>trasporto</b></label>&nbsp;&nbsp;';
			echo '<input type="radio" name="trasport'.$id.'" id="trasport'.$id.'_N" value="N" ';
			if($trasport=='0.00') echo 'checked';
			echo '/> No';
			echo '<input type="radio" name="trasport'.$id.'" id="trasport'.$id.'_Y" value="Y" ';
			if($trasport!='0.00') echo 'checked';
			echo '/> Si';
			echo '</p>';			
		}
		?>	
		<p>
			<label for="acquistato_il" style="width:auto !important;">Visualizzo il campo "<b>Acquistato il</b>"</label>&nbsp;&nbsp;
			<input type="radio" name="acquistato_il" id="acquistato_il_N" value="N" checked /> No
			<input type="radio" name="acquistato_il" id="acquistato_il_Y" value="Y" /> Si
		</p>
		<p>
			<label for="article_img" style="width:auto !important;">Visualizzo la <b>foto</b> dell'articolo</label>&nbsp;&nbsp;
			<input type="radio" name="article_img" id="article_img_N" value="N" checked /> No
			<input type="radio" name="article_img" id="article_img_Y" value="Y" /> Si
		</p>
		
		<p>
			<label for="totale_per_articolo" style="width:auto !important;">Visualizzo il <b>totale</b> per ogni articolo</label>&nbsp;&nbsp;
			<input type="radio" name="totale_per_articolo" id="totale_per_articolo_N" value="N" /> No
			<input type="radio" name="totale_per_articolo" id="totale_per_articolo_Y" value="Y" checked /> Si
		</p>
		
		<p>
			<label for="codice1" style="width:auto !important;">Visualizzo il <b>codice</b> dell'articolo</label>&nbsp;&nbsp;
			<input type="radio" name="codice1" id="codice1_N" value="N" checked /> No
			<input type="radio" name="codice1" id="codice1_Y" value="Y" /> Si
		</p>		
	</div>
</div>
<script type="text/javascript">
$(document).ready(function() {
	$("input[name='doc_options']").change(function() {	

		$('#setting-to-users-all-modify').hide();
		$('#setting-to-users').hide();
		$('#setting-to-users-label').hide();
		$('#setting-to-articles').hide();
		$('#setting-to-articles-details').hide();
	
		var doc_options = $("input[name='doc_options']:checked").val();

		if(doc_options=='to-users-all-modify')
			$('#setting-to-users-all-modify').show();
		else		
		if(doc_options=='to-users')
			$('#setting-to-users').show();
		else
		if(doc_options=='to-users-label')
			$('#setting-to-users-label').show();
		else
		if(doc_options=='to-articles')
			$('#setting-to-articles').show();
		else
		if(doc_options=='to-articles-details')
			$('#setting-to-articles-details').show();
			
		choiceDocOptions();
	});

	$("input[name='totale_per_utente']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='dettaglio_per_utente']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_phone1']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_email1']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_address1']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_phone']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_email']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_address']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_avatar1']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_avatar2']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='delete_to_referent2']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='note1']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='delete_to_referent1']").change(function() {			
		choiceDocOptions();
	});	
	$("input[name='acquistato_il']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='article_img']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='trasport1']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='trasport2']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='trasport3']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='trasport4']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='trasport5']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='trasport5']").change(function() {			
		choiceDocOptions();
	});	
	$("input[name='totale_per_articolo']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='codice1']").change(function() {			
		choiceDocOptions();
	});	
	$("input[name='codice2']").change(function() {			
		choiceDocOptions();
	});	
});
</script>