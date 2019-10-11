<?php 
if(!empty($results))
	echo $this->element('boxMsg', array('class_msg' => 'message', 'msg' => __('OrderToValidateError')));
?>
<div class="col-md-7">
<label class="control-label">Opzioni</label>

	<?php
	if($can_view_orders_users=='Y') {
	?>
		<div class="radio">
			<label><input type="radio" name="doc_options" id="to-users" value="to-users" /><?php echo __('to_users');?></label>
		</div>
	<?php 
	}

	/*
	 *  per report to-articles-monitoring 
	 *      Order.state_code = 'OPEN' OR Order.state_code = 'PROCESSED-BEFORE-DELIVERY'
	 */
	if($isToValidate || $toQtaMassima || $toQtaMinimaOrder) { 
	?>
		<div class="radio">
			<label><input type="radio" name="doc_options" id="to-articles-monitoring" value="to-articles-monitoring" /><?php echo __('to_articles_monitoring');?></label>
		</div>	
	<?php
	}	
	?>
		<div class="radio">
			<label><input type="radio" name="doc_options" id="to-articles" value="to-articles" /><?php echo __('to_articles');?></label>
		</div>	
	<?php
	if($can_view_orders_users=='Y') {	
	?>
		<div class="radio">
			<label><input type="radio" name="doc_options" id="to-articles-details" value="to-articles-details" /><?php echo __('to_articles_details');?></label>
		</div>	
	<?php
	}
	?>
	<!--
	<p>
		<input type="radio" name="doc_options" id="to-articles-weight" value="to-articles-weight" /><label for="to-articles-weight"><?php echo __('to_articles_weight');?></label>
	</p>
	-->
</div>
<div class="col-md-5">

		<?php
	echo '<div id="setting-to-users" class="box-options">';		

		if($hasTrasport=='Y' || $hasCostMore=='Y' || $hasCostLess=='Y') {
			$id = '2';
						
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
			<label class="control-label">Visualizzo colonna <b>note</b></label>
			<label class="radio-inline"><input type="radio" name="note1" id="note1_N" value="N" />No</label>
			<label class="radio-inline"><input type="radio" name="note1" id="note1_Y" value="Y" checked />Si</label>
		</div>
						
		<div class="doc-options">
			<label class="control-label">Visualizzo gli articoli <b>cancellati</b></label>
			<label class="radio-inline"><input type="radio" name="delete_to_referent1" id="delete_to_referent1_N" value="N" />No</label>
			<label class="radio-inline"><input type="radio" name="delete_to_referent1" id="delete_to_referent1_Y" value="Y" checked />Si</label>
		</div>
				
	</div>

	<div id="setting-to-articles" class="box-options">
		<?php
		if($hasTrasport=='Y' || $hasCostMore=='Y' || $hasCostLess=='Y') {
			$id = '4';
		
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
		
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
		?>	
			<div class="doc-options">
				<label class="control-label">Visualizzo il <b>codice</b> dell'articolo</label>
				<label class="radio-inline"><input type="radio" name="codice2" id="codice2_N" value="N" />No</label>
				<label class="radio-inline"><input type="radio" name="codice2" id="codice2_Y" value="Y" checked />Si</label>
			</div>
		<?php 
		}
		?>

		<div class="doc-options">
			<label class="control-label">Visualizzo i <b>colli</b> degli articoli</label>
			<label class="radio-inline"><input type="radio" name="pezzi_confezione1" id="pezzi_confezione1_N" value="N" />No</label>
			<label class="radio-inline"><input type="radio" name="pezzi_confezione1" id="pezzi_confezione1_Y" value="Y" checked />Si</label>
		</div>		

	</div>
		
	<div id="setting-to-articles-details" class="box-options">
		<?php
		if($hasTrasport=='Y' || $hasCostMore=='Y' || $hasCostLess=='Y') {
			$id = '5';
		
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
			<label class="control-label">Visualizzo il campo "<b>Acquistato il</b></label>
			<label class="radio-inline"><input type="radio" name="acquistato_il" id="acquistato_il_N" value="N" />No</label>
			<label class="radio-inline"><input type="radio" name="acquistato_il" id="acquistato_il_Y" value="Y" checked />Si</label>
		</div>	
					
		<div class="doc-options">
			<label class="control-label">Visualizzo la <b>foto</b> dell'articolo</label>
			<label class="radio-inline"><input type="radio" name="article_img" id="article_img_N" value="N" />No</label>
			<label class="radio-inline"><input type="radio" name="article_img" id="article_img_Y" value="Y" checked />Si</label>
		</div>	
					
		<div class="doc-options">
			<label class="control-label">Visualizzo il <b>totale</b> per ogni articolo</label>
			<label class="radio-inline"><input type="radio" name="totale_per_articolo" id="totale_per_articolo_N" value="N" />No</label>
			<label class="radio-inline"><input type="radio" name="totale_per_articolo" id="totale_per_articolo_Y" value="Y" checked />Si</label>
		</div>	
						
		<div class="doc-options">
			<label class="control-label">Visualizzo il <b>codice</b> dell'articolo</label>
			<label class="radio-inline"><input type="radio" name="codice1" id="codice1_N" value="N" />No</label>
			<label class="radio-inline"><input type="radio" name="codice1" id="codice1_Y" value="Y" checked />Si</label>
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

		$('#setting-to-users').hide();
		$('#setting-to-articles').hide();
		$('#setting-to-articles-details').hide();
	
		var doc_options = $("input[name='doc_options']:checked").val();

		if(doc_options=='to-users')
			$('#setting-to-users').show();
		else
		if(doc_options=='to-articles')
			$('#setting-to-articles').show();
		else
		if(doc_options=='to-articles-details')
			$('#setting-to-articles-details').show();
			
		choiceDocOptions();
	});

	$("input[name='user_phone1']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='user_email1']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='totale_per_utente']").change(function() {			
		choiceDocOptions();
	});
	$("input[name='dettaglio_per_utente']").change(function() {			
		choiceDocOptions();
	});

	$("input[name='delete_to_referent2']").change(function() {			
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
	$("input[name='codice1']").change(function() {			
		choiceDocOptions();
	});	
	$("input[name='codice2']").change(function() {			
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