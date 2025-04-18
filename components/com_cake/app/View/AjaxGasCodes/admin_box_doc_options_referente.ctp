<?php 
if(!empty($results))
	echo $this->element('boxMsg', ['class_msg' => 'message', 'msg' => __('OrderToValidateError')]);
?>
<div class="col-md-7">
<label class="control-label">Opzioni</label>

	<div class="radio">
		<label><input type="radio" name="doc_options" id="to-users-all-modify" value="to-users-all-modify" /><?php echo __('to_users_all_modify');?></label>
	</div>
	<div class="radio">
		<label><input type="radio" name="doc_options" id="to-users" value="to-users" /><?php echo __('to_users');?></label>
	</div>
	<div class="radio">
		<label><input type="radio" name="doc_options" id="to-users-label" value="to-users-label" /><?php echo __('to_users_label');?></label>
	</div>
	<div class="radio">
		<label><input type="radio" name="doc_options" id="to-users-articles-label" value="to-users-articles-label" /><?php echo __('to_users_articles_label');?></label>
	</div>
	<?php
	/*
	 *  per report to-articles-monitoring 
	 *      Order.state_code = 'OPEN' OR Order.state_code = 'PROCESSED-BEFORE-DELIVERY'
	 */
	if($isToValidate || $toQtaMassima || $toQtaMinimaOrder) { 
		echo '<div class="radio"><label><input type="radio" name="doc_options" id="to-articles-monitoring" value="to-articles-monitoring" />'.__('to_articles_monitoring').'</label></div>';
	}
	?>
	<div class="radio">
		<label><input type="radio" name="doc_options" id="to-articles" value="to-articles" /><?php echo __('to_articles');?></label>
	</div>
	<div class="radio">
		<label><input type="radio" name="doc_options" id="to-articles-details" value="to-articles-details" /><?php echo __('to_articles_details');?></label>
	</div>
    <div class="radio">
        <label><input type="radio" name="doc_options" id="to-articles-weight" value="to-articles-weight" /><?php echo __('to_articles_weight');?></label>
    </div>
    <div class="radio">
        <label><input type="radio" name="doc_options" id="to-users-schema" value="to-users-schema" /><?php echo __('to_users_schema');?></label>
    </div>
</div>
<div class="col-md-5">
		<?php
		$id = '1';
		if($hasTrasport=='Y' || $hasCostMore=='Y' || $hasCostLess=='Y') {
			
			echo '<div id="setting-to-users-all-modify" class="box-options">';
			echo '<div class="doc-options">';
			echo '<label class="control-label">Visualizzo le <b>spese aggiuntive</b> o gli <b>sconti</b></label> ';
			echo '<label class="radio-inline"><input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_N" value="N" ';
			if($trasport=='0.00' && $cost_more=='0.00' && $cost_less=='0.00') echo 'checked';
			echo '/>No</label> ';
			echo '<label class="radio-inline"><input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_Y" value="Y" ';
			if($trasport!='0.00' || $cost_more!='0.00' || $cost_less!='0.00') echo 'checked';
			echo '/>Si</label> ';
			echo '</div>';
			
			echo '</div>';
		}
		?>
	<div id="setting-to-users" class="box-options">
		<?php
		$id = '2';
		if($hasTrasport=='Y' || $hasCostMore=='Y' || $hasCostLess=='Y') {
			echo '<div class="doc-options">';
			echo '<label class="control-label">Visualizzo le <b>spese aggiuntive</b> o gli <b>sconti</b></label> ';
			echo '<label class="radio-inline"><input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_N" value="N" ';
			if($trasport=='0.00' && $cost_more=='0.00' && $cost_less=='0.00') echo 'checked';
			echo '/>No</label> ';
			echo '<label class="radio-inline"><input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_Y" value="Y" ';
			if($trasport!='0.00' || $cost_more!='0.00' || $cost_less!='0.00') echo 'checked';
			echo '/>Si</label> ';
			echo '</div>';
		}
		?>	
		<div class="doc-options">
			<label class="control-label">Visualizzo il <b>telefono</b> degli utenti</label>
			<label class="radio-inline"><input type="radio" name="user_phone1" id="user_phone1_N" value="N" />No</label>
			<label class="radio-inline"><input type="radio" name="user_phone1" id="user_phone1_Y" value="Y" checked />Si</label>
		</div>
		<div class="doc-options">
			<label class="control-label">Visualizzo la <b>mail</b> degli utenti</label>
			<label class="radio-inline"><input type="radio" name="user_email1" id="user_email1_N" value="N" checked />No</label>
			<label class="radio-inline"><input type="radio" name="user_email1" id="user_email1_Y" value="Y" />Si</label>
		</div>
		<div class="doc-options">
			<label class="control-label">Visualizzo l'<b>indirizzo</b> degli utenti</label>
			<label class="radio-inline"><input type="radio" name="user_address1" id="user_address1_N" value="N" checked />No</label>
			<label class="radio-inline"><input type="radio" name="user_address1" id="user_address1_Y" value="Y" />Si</label>
		</div>	
		<div class="doc-options">
			<label class="control-label">Visualizzo il <b>totale</b> per ogni utente</label>
			<label class="radio-inline"><input type="radio" name="totale_per_utente" id="totale_per_utente_N" value="N" />No</label>
			<label class="radio-inline"><input type="radio" name="totale_per_utente" id="totale_per_utente_Y" value="Y" checked />Si</label>
		</div>
		<div class="doc-options">
			<label class="control-label">Visualizzo il <b>dettaglio</b> per ogni utente</label>
			<label class="radio-inline"><input type="radio" name="dettaglio_per_utente" id="dettaglio_per_utente_N" value="N" />No</label>
			<label class="radio-inline"><input type="radio" name="dettaglio_per_utente" id="dettaglio_per_utente_Y" value="Y" checked />Si</label>
		</div>
		<div class="doc-options">
			<label class="control-label">Visualizzo la <b>foto</b> dell'utente</label>
			<label class="radio-inline"><input type="radio" name="user_avatar1" id="user_avatar1_N" value="N" checked />No</label>
			<label class="radio-inline"><input type="radio" name="user_avatar1" id="user_avatar1_Y" value="Y" />Si</label>
		</div>
		<div class="doc-options">
			<label class="control-label">Visualizzo colonna <b>note</b></label>
			<label class="radio-inline"><input type="radio" name="note1" id="note1_N" value="N" />No</label>
			<label class="radio-inline"><input type="radio" name="note1" id="note1_Y" value="Y" checked />Si</label>
		</div>
		<div class="doc-options">
			<label class="control-label">Visualizzo gli articoli <b>cancellati</b></label>
			<label class="radio-inline"><input type="radio" name="delete_to_referent1" id="delete_to_referent1_N" value="N" checked />No</label>
			<label class="radio-inline"><input type="radio" name="delete_to_referent1" id="delete_to_referent1_Y" value="Y" />Si</label>
		</div>		
	</div>
	<div id="setting-to-users-label" class="box-options">
		<?php
		$id = '3';
		if($hasTrasport=='Y' || $hasCostMore=='Y' || $hasCostLess=='Y') {
				
			echo '<div class="doc-options">';
			echo '<label class="control-label">Visualizzo le <b>spese aggiuntive</b> o gli <b>sconti</b></label> ';
			echo '<label class="radio-inline"><input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_N" value="N" ';
			if($trasport=='0.00' && $cost_more=='0.00' && $cost_less=='0.00') echo 'checked';
			echo '/>No</label> ';
			echo '<label class="radio-inline"><input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_Y" value="Y" ';
			if($trasport!='0.00' || $cost_more!='0.00' || $cost_less!='0.00') echo 'checked';
			echo '/>Si</label> ';
			echo '</div>';			
		}
		?>
		<div class="doc-options">
			<label class="control-label">Visualizzo il <b>telefono</b> degli utenti</label>
			<label class="radio-inline"><input type="radio" name="user_phone<?php echo $id;?>" id="user_phone<?php echo $id;?>_N" value="N" />No</label>
			<label class="radio-inline"><input type="radio" name="user_phone<?php echo $id;?>" id="user_phone<?php echo $id;?>_Y" value="Y" checked />Si</label>
		</div>
		<div class="doc-options">
			<label class="control-label">Visualizzo la <b>mail</b> degli utenti</label>
			<label class="radio-inline"><input type="radio" name="user_email<?php echo $id;?>" id="user_email<?php echo $id;?>_N" value="N" checked />No</label>
			<label class="radio-inline"><input type="radio" name="user_email<?php echo $id;?>" id="user_email<?php echo $id;?>_Y" value="Y" />Si</label>
		</div>
		<div class="doc-options">
			<label class="control-label">Visualizzo l'<b>indirizzo</b> degli utenti</label>
			<label class="radio-inline"><input type="radio" name="user_address<?php echo $id;?>" id="user_address<?php echo $id;?>_N" value="N" checked />No</label>
			<label class="radio-inline"><input type="radio" name="user_address<?php echo $id;?>" id="user_address<?php echo $id;?>_Y" value="Y" />Si</label>
		</div>
		<div class="doc-options">
			<label class="control-label">Visualizzo la <b>foto</b> dell'utente</label>
			<label class="radio-inline"><input type="radio" name="user_avatar<?php echo $id;?>" id="user_avatar<?php echo $id;?>_N" value="N" checked />No</label>
			<label class="radio-inline"><input type="radio" name="user_avatar<?php echo $id;?>" id="user_avatar<?php echo $id;?>_Y" value="Y" />Si</label>
		</div>
		<div class="doc-options">
			<label class="control-label">Visualizzo gli articoli <b>cancellati</b></label>
			<label class="radio-inline"><input type="radio" name="delete_to_referent<?php echo $id;?>" id="delete_to_referent<?php echo $id;?>_N" value="N" checked/>No</label>
			<label class="radio-inline"><input type="radio" name="delete_to_referent<?php echo $id;?>" id="delete_to_referent<?php echo $id;?>_Y" value="Y" />Si</label>
		</div>	
		<?php
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
		?>
			<div class="doc-options">
				<label class="control-label">Visualizzo il <b>codice</b> dell'articolo</label>
				<label class="radio-inline"><input type="radio" name="codice<?php echo $id;?>" id="codice<?php echo $id;?>_N" value="N" checked />No</label>
				<label class="radio-inline"><input type="radio" name="codice<?php echo $id;?>" id="codice<?php echo $id;?>_Y" value="Y" />Si</label>
			</div>
		<?php
		}
		?>								
	</div>

	<div id="setting-to-users-articles-label" class="box-options">
		<?php
		$id = '30';		
		if($hasTrasport=='Y' || $hasCostMore=='Y' || $hasCostLess=='Y') {		
			echo '<div class="doc-options">';
			echo '<label class="control-label">Visualizzo le <b>spese aggiuntive</b> o gli <b>sconti</b></label> ';
			echo '<label class="radio-inline"><input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_N" value="N" ';
			if($trasport=='0.00' && $cost_more=='0.00' && $cost_less=='0.00') echo 'checked';
			echo '/>No</label> ';
			echo '<label class="radio-inline"><input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_Y" value="Y" ';
			if($trasport!='0.00' || $cost_more!='0.00' || $cost_less!='0.00') echo 'checked';
			echo '/>Si</label> ';
			echo '</div>';
		}
		?>
		<div class="doc-options">
			<label class="control-label">Visualizzo il <b>telefono</b> degli utenti</label>
			<label class="radio-inline"><input type="radio" name="user_phone<?php echo $id;?>" id="user_phone<?php echo $id;?>_N" value="N" />No</label>
			<label class="radio-inline"><input type="radio" name="user_phone<?php echo $id;?>" id="user_phone<?php echo $id;?>_Y" value="Y" checked />Si</label>
		</div>
		<div class="doc-options">
			<label class="control-label">Visualizzo la <b>mail</b> degli utenti</label>
			<label class="radio-inline"><input type="radio" name="user_email<?php echo $id;?>" id="user_email<?php echo $id;?>_N" value="N" checked />No</label>
			<label class="radio-inline"><input type="radio" name="user_email<?php echo $id;?>" id="user_email<?php echo $id;?>_Y" value="Y" />Si</label>
		</div>
		<div class="doc-options">
			<label class="control-label">Visualizzo l'<b>indirizzo</b> degli utenti</label>
			<label class="radio-inline"><input type="radio" name="user_address<?php echo $id;?>" id="user_address<?php echo $id;?>_N" value="N" checked />No</label>
			<label class="radio-inline"><input type="radio" name="user_address<?php echo $id;?>" id="user_address<?php echo $id;?>_Y" value="Y" />Si</label>
		</div>
		<div class="doc-options">
			<label class="control-label">Visualizzo la <b>foto</b> dell'utente</label>
			<label class="radio-inline"><input type="radio" name="user_avatar<?php echo $id;?>" id="user_avatar<?php echo $id;?>_N" value="N" checked />No</label>
			<label class="radio-inline"><input type="radio" name="user_avatar<?php echo $id;?>" id="user_avatar<?php echo $id;?>_Y" value="Y" />Si</label>
		</div>
		<div class="doc-options">
			<label class="control-label">Visualizzo gli articoli <b>cancellati</b></label>
			<label class="radio-inline"><input type="radio" name="delete_to_referent<?php echo $id;?>" id="delete_to_referent<?php echo $id;?>_N" value="N" checked/>No</label>
			<label class="radio-inline"><input type="radio" name="delete_to_referent<?php echo $id;?>" id="delete_to_referent<?php echo $id;?>_Y" value="Y" />Si</label>
		</div>	
		<?php
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
		?>
			<div class="doc-options">
				<label class="control-label">Visualizzo il <b>codice</b> dell'articolo</label>
				<label class="radio-inline"><input type="radio" name="codice<?php echo $id;?>" id="codice<?php echo $id;?>_N" value="N" checked />No</label>
				<label class="radio-inline"><input type="radio" name="codice<?php echo $id;?>" id="codice<?php echo $id;?>_Y" value="Y" />Si</label>
			</div>
		<?php
		}
		?>								
	</div>	
	
	<div id="setting-to-articles-monitoring" class="box-options">
		<div class="doc-options">
			<label for="codice2" style="width:auto !important;">Visualizzo i colli a 1</label>&nbsp;&nbsp;
			<input type="radio" name="colli1" id="colli1_N" value="N" checked /> No
			<input type="radio" name="colli1" id="colli1_Y" value="Y" /> Si
		</div>
	</div>
		
	<div id="setting-to-articles" class="box-options">
		<?php
		$id = '4';
		if($hasTrasport=='Y' || $hasCostMore=='Y' || $hasCostLess=='Y') {
			echo '<div class="doc-options">';
			echo '<label class="control-label">Visualizzo le <b>spese aggiuntive</b> o gli <b>sconti</b></label> ';
			echo '<label class="radio-inline"><input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_N" value="N" ';
			if($trasport=='0.00' && $cost_more=='0.00' && $cost_less=='0.00') echo 'checked';
			echo '/>No</label> ';
			echo '<label class="radio-inline"><input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_Y" value="Y" ';
			if($trasport!='0.00' || $cost_more!='0.00' || $cost_less!='0.00') echo 'checked';
			echo '/>Si</label> ';
			echo '</div>';
	
			echo '';
		}
		
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
		?>	
			<div class="doc-options">
				<label class="control-label">Visualizzo il <b>codice</b> dell'articolo</label>
				<label class="radio-inline"><input type="radio" name="codice2" id="codice2_N" value="N" checked />No</label>
				<label class="radio-inline"><input type="radio" name="codice2" id="codice2_Y" value="Y" />Si</label>
			</div>
		<?php 
		}
		?>
		
		<div class="doc-options">
			<label class="control-label">Visualizzo i <b>colli</b> degli articoli</label>
			<label class="radio-inline"><input type="radio" name="pezzi_confezione1" id="pezzi_confezione1_N" value="N" checked />No</label>
			<label class="radio-inline"><input type="radio" name="pezzi_confezione1" id="pezzi_confezione1_Y" value="Y" />Si</label>
		</div>
	</div>
		
	<div id="setting-to-articles-details" class="box-options">
		<?php
		$id = '5';
		if($hasTrasport=='Y' || $hasCostMore=='Y' || $hasCostLess=='Y') {		
			echo '<div class="doc-options">';
			echo '<label class="control-label">Visualizzo le <b>spese aggiuntive</b> o gli <b>sconti</b></label> ';
			echo '<label class="radio-inline"><input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_N" value="N" ';
			if($trasport=='0.00' && $cost_more=='0.00' && $cost_less=='0.00') echo 'checked';
			echo '/>No</label> ';
			echo '<label class="radio-inline"><input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_Y" value="Y" ';
			if($trasport!='0.00' || $cost_more!='0.00' || $cost_less!='0.00') echo 'checked';
			echo '/>Si</label> ';
			echo '</div>';		
		}
		?>	
		<div class="doc-options">
			<label class="control-label">Visualizzo il campo "<b>Acquistato il</b>"</label>
			<label class="radio-inline"><input type="radio" name="acquistato_il" id="acquistato_il_Y" value="N" checked>No</label>
			<label class="radio-inline"><input type="radio" name="acquistato_il" id="acquistato_il_Y" value="Y">Si</label>
			
		</div>
		<div class="doc-options">
			<label class="control-label">Visualizzo la <b>foto</b> dell'articolo</label>
			<label class="radio-inline"><input type="radio" name="article_img" id="article_img_N" value="N" checked />No</label>
			<label class="radio-inline"><input type="radio" name="article_img" id="article_img_Y" value="Y" />Si</label>
		</div>
		
		<div class="doc-options">
			<label class="control-label">Visualizzo il <b>totale</b> per ogni articolo</label>
			<label class="radio-inline"><input type="radio" name="totale_per_articolo" id="totale_per_articolo_N" value="N" />No</label>
			<label class="radio-inline"><input type="radio" name="totale_per_articolo" id="totale_per_articolo_Y" value="Y" checked />Si</label>
		</div>
		
		<div class="doc-options">
			<label class="control-label">Visualizzo il <b>codice</b> dell'articolo</label>
			<label class="radio-inline"><input type="radio" name="codice1" id="codice1_N" value="N" checked />No</label>
			<label class="radio-inline"><input type="radio" name="codice1" id="codice1_Y" value="Y" />Si</label>
		</div>		
	</div>

    <div id="setting-to-users-shema" class="box-options">
        <div class="doc-options">
            <label class="control-label">Visualizzo il <b>telefono</b> degli utenti</label>
            <label class="radio-inline"><input type="radio" name="user_phone1" id="user_phone1_N" value="N" />No</label>
            <label class="radio-inline"><input type="radio" name="user_phone1" id="user_phone1_Y" value="Y" checked />Si</label>
        </div>
        <div class="doc-options">
            <label class="control-label">Visualizzo la <b>mail</b> degli utenti</label>
            <label class="radio-inline"><input type="radio" name="user_email1" id="user_email1_N" value="N" />No</label>
            <label class="radio-inline"><input type="radio" name="user_email1" id="user_email1_Y" value="Y" checked />Si</label>
        </div>
        <div class="doc-options">
            <label class="control-label">Visualizzo l'<b>indirizzo</b> degli utenti</label>
            <label class="radio-inline"><input type="radio" name="user_address1" id="user_address1_N" value="N" checked />No</label>
            <label class="radio-inline"><input type="radio" name="user_address1" id="user_address1_Y" value="Y" />Si</label>
        </div>
        <div class="doc-options">
            <label class="control-label">Visualizzo la <b>foto</b> dell'utente</label>
            <label class="radio-inline"><input type="radio" name="user_avatar1" id="user_avatar1_N" value="N" checked />No</label>
            <label class="radio-inline"><input type="radio" name="user_avatar1" id="user_avatar1_Y" value="Y" />Si</label>
        </div>
    </div>
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

<script type="text/javascript">
$(document).ready(function() {
	$("input[name='doc_options']").change(function() {	

		$('#setting-to-users-all-modify').hide();
		$('#setting-to-users').hide();
		$('#setting-to-users-label').hide();
		$('#setting-to-users-articles-label').hide();
		$('#setting-to-articles-monitoring').hide();
		$('#setting-to-articles').hide();
        $('#setting-to-articles-details').hide();
        $('#setting-to-users-shema').hide();

		var doc_options = $("input[name='doc_options']:checked").val();

		if(doc_options=='to-users-all-modify')
			$('#setting-to-users-all-modify').show();
		else
        if(doc_options=='to-users')
            $('#setting-to-users').show();
        else
        if(doc_options=='to-users-schema')
            $('#setting-to-users-shema').show();
        else
		if(doc_options=='to-users-label')
			$('#setting-to-users-label').show();
		else
		if(doc_options=='to-users-articles-label')
			$('#setting-to-users-articles-label').show();
		else	
		if(doc_options=='to-articles-monitoring')
			$('#setting-to-articles-monitoring').show();		
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
	$("input[name='user_phone3']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_phone30']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_email3']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_email30']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_address3']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_address30']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_avatar1']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_avatar2']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_avatar3']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_avatar30']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='delete_to_referent2']").change(function() {			
		choiceDocOptions();
	});	
	$("input[name='delete_to_referent3']").change(function() {			
		choiceDocOptions();
	});	
	$("input[name='delete_to_referent30']").change(function() {			
		choiceDocOptions();
	});	
	$("input[name='acquistato_il']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='article_img']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='trasportAndCost1']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='trasportAndCost2']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='trasportAndCost3']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='trasportAndCost30']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='trasportAndCost4']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='trasportAndCost5']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='trasportAndCost5']").change(function() {			
		choiceDocOptions();
	});	
	$("input[name='totale_per_articolo']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='colli1']").change(function() {			
		choiceDocOptions();
	});		
	$("input[name='codice1']").change(function() {			
		choiceDocOptions();
	});	
	$("input[name='codice2']").change(function() {			
		choiceDocOptions();
	});	
	$("input[name='codice3']").change(function() {			
		choiceDocOptions();
	});	
	$("input[name='pezzi_confezione1']").change(function() {			
		choiceDocOptions();
	});	
	$("input[name='note1']").change(function() {			
		choiceDocOptions();
	});	
	$("input[name='delete_to_referent1']").change(function() {			
		choiceDocOptions();
	});	
});
</script> 