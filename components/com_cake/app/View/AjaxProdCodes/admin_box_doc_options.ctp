<div class="left label" style="width:125px !important;">Opzioni documento</div>
<div class="left radio">
	<p>
		<input type="radio" name="doc_options" id="to-prod-users-group" value="to-prod-users-group" /><label for="to-prod-users-group"><?php echo __('to_prod_users_group');?></label>
	</p>
	<p>
		<input type="radio" name="doc_options" id="to-users-all-modify" value="to-users-all-modify" /><label for="to-users-all-modify"><?php echo __('to_users_all_modify');?></label>
	</p>
	<p>
		<input type="radio" name="doc_options" id="to-users" value="to-users" /><label for="to-users"><?php echo __('to_users');?></label>
	</p>
	<p>
		<input type="radio" name="doc_options" id="to-users-label" value="to-users-label" /><label for="to-users-label"><?php echo __('to_users_label');?></label>
	</p>
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

	<div id="setting-to-users" class="box-options">	
		<p>
			<label for="user_phone1" style="width:auto !important;">Visualizzo il telefono degli utenti</label>&nbsp;&nbsp;
			<input type="radio" name="user_phone1" id="user_phone1_N" value="N" /> No
			<input type="radio" name="user_phone1" id="user_phone1_Y" value="Y" checked /> Si
		</p>
		<p>
			<label for="user_email1" style="width:auto !important;">Visualizzo la mail degli utenti</label>&nbsp;&nbsp;
			<input type="radio" name="user_email1" id="user_email1_N" value="N" checked /> No
			<input type="radio" name="user_email1" id="user_email1_Y" value="Y" /> Si
		</p>
		<p>
			<label for="user_address1" style="width:auto !important;">Visualizzo l'indirizzo degli utenti</label>&nbsp;&nbsp;
			<input type="radio" name="user_address1" id="user_address1_N" value="N" checked /> No
			<input type="radio" name="user_address1" id="user_address1_Y" value="Y" /> Si
		</p>	
		<p>
			<label for="totale_per_utente" style="width:auto !important;">Visualizzo il totale per ogni utente</label>&nbsp;&nbsp;
			<input type="radio" name="totale_per_utente" id="totale_per_utente_N" value="N" /> No
			<input type="radio" name="totale_per_utente" id="totale_per_utente_Y" value="Y" checked /> Si
		</p>	
	</div>
	<div id="setting-to-users-label" class="box-options">
		<p>
			<label for="user_phone" style="width:auto !important;">Visualizzo il telefono degli utenti</label>&nbsp;&nbsp;
			<input type="radio" name="user_phone" id="user_phone_N" value="N" /> No
			<input type="radio" name="user_phone" id="user_phone_Y" value="Y" checked /> Si
		</p>
		<p>
			<label for="user_email" style="width:auto !important;">Visualizzo la mail degli utenti</label>&nbsp;&nbsp;
			<input type="radio" name="user_email" id="user_email_N" value="N" checked /> No
			<input type="radio" name="user_email" id="user_email_Y" value="Y" /> Si
		</p>
		<p>
			<label for="user_address" style="width:auto !important;">Visualizzo l'indirizzo degli utenti</label>&nbsp;&nbsp;
			<input type="radio" name="user_address" id="user_address_N" value="N" checked /> No
			<input type="radio" name="user_address" id="user_address_Y" value="Y" /> Si
		</p>	
	</div>	
	<div id="setting-to-articles-details" class="box-options">	
		<p>
			<label for="acquistato_il" style="width:auto !important;">Visualizzo il campo "Acquistato il"</label>&nbsp;&nbsp;
			<input type="radio" name="acquistato_il" id="acquistato_il_N" value="N" checked /> No
			<input type="radio" name="acquistato_il" id="acquistato_il_Y" value="Y" /> Si
		</p>
		<p>
			<label for="article_img" style="width:auto !important;">Visualizzo l'immagine dell'articolo</label>&nbsp;&nbsp;
			<input type="radio" name="article_img" id="article_img_N" value="N" checked /> No
			<input type="radio" name="article_img" id="article_img_Y" value="Y" /> Si
		</p>		
	</div>
	<div id="setting-to-prod-users-group" class="box-options">	
		<p>
			<label for="user_phone2" style="width:auto !important;">Visualizzo il telefono degli utenti</label>&nbsp;&nbsp;
			<input type="radio" name="user_phone2" id="user_phone2_N" value="N" /> No
			<input type="radio" name="user_phone2" id="user_phone2_Y" value="Y" checked /> Si
		</p>
		<p>
			<label for="user_email2" style="width:auto !important;">Visualizzo la mail degli utenti</label>&nbsp;&nbsp;
			<input type="radio" name="user_email2" id="user_email2_N" value="N" checked /> No
			<input type="radio" name="user_email2" id="user_email2_Y" value="Y" /> Si
		</p>
		<p>
			<label for="user_address2" style="width:auto !important;">Visualizzo l'indirizzo degli utenti</label>&nbsp;&nbsp;
			<input type="radio" name="user_address2" id="user_address2_N" value="N" checked /> No
			<input type="radio" name="user_address2" id="user_address2_Y" value="Y" /> Si
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
		$('#setting-to-prod-users-group').hide();
		
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
		else
		if(doc_options=='to-prod-users-group')
			$('#setting-to-articles-details').show();
			
		choiceDocOptions();
	});
	
	$("input[name='totale_per_utente']").change(function() {			
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
	$("input[name='user_phone2']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_email2']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_address2']").change(function() {			
		choiceDocOptions();
	});	
});
</script>