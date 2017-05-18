<?php 
if(!empty($results))
	echo $this->element('boxMsg', array('class_msg' => 'message', 'msg' => __('OrderToValidateError')));
?>
<div class="left label" style="width:100px !important;">Opzioni</div>
<div class="left radio">

	<?php
	if($can_view_orders_users=='Y') {
		echo '<p>';
		echo '<input type="radio" name="doc_options" id="to-users" value="to-users" /><label for="to-users">'.__('to_users').'</label>';
		echo '</p>';
	}

	/*
	 *  per report to-articles-monitoring 
	 *      Order.state_code = 'OPEN' OR Order.state_code = 'PROCESSED-BEFORE-DELIVERY'
	 */
	if($isToValidate || $toQtaMassima || $toQtaMinimaOrder) { 
		echo '<p>';
		echo' <input type="radio" name="doc_options" id="to-articles-monitoring" value="to-articles-monitoring" /><label for="to-articles-monitoring">'.__('to_articles_monitoring').'</label>';
		echo '</p>';
	}
	
	echo '<p>';
	echo '<input type="radio" name="doc_options" id="to-articles" value="to-articles" /><label for="to-articles">'.__('to_articles').'</label>';
	echo '</p>';
	
	if($can_view_orders_users=='Y') {
		echo '<p>';
		echo '<input type="radio" name="doc_options" id="to-articles-details" value="to-articles-details" /><label for="to-articles-details">'.__('to_articles_details').'</label>';
		echo '</p>';
	}
	?>
	<!--
	<p>
		<input type="radio" name="doc_options" id="to-articles-weight" value="to-articles-weight" /><label for="to-articles-weight"><?php echo __('to_articles_weight');?></label>
	</p>
	-->
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
<div class="left setting" style="width:35%;">
	<div id="setting-to-users" class="box-options">
		<?php
		if($hasTrasport=='Y' || $hasCostMore=='Y' || $hasCostLess=='Y') {
			$id = '2';
							
			echo '<p>';
			echo '<label for="trasportAndCost'.$id.'" style="width:auto !important;">Visualizzo le <b>spese aggiuntive</b> o gli <b>sconti</b></label>&nbsp;&nbsp;';
			echo '<input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_N" value="N" ';
			if($trasport=='0.00' && $cost_more=='0.00' && $cost_less=='0.00') echo 'checked';
			echo '/> No';
			echo '<input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_Y" value="Y" ';
			if($trasport!='0.00' || $cost_more!='0.00' || $cost_less!='0.00') echo 'checked';
			echo '/> Si';
			echo '</p>';
		}
		?>		
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

	<div id="setting-to-articles" class="box-options">
		<?php
		if($hasTrasport=='Y' || $hasCostMore=='Y' || $hasCostLess=='Y') {
			$id = '4';
		
			echo '<p>';
			echo '<label for="trasportAndCost'.$id.'" style="width:auto !important;">Visualizzo le <b>spese aggiuntive</b> o gli <b>sconti</b></label>&nbsp;&nbsp;';
			echo '<input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_N" value="N" ';
			if($trasport=='0.00' && $cost_more=='0.00' && $cost_less=='0.00') echo 'checked';
			echo '/> No';
			echo '<input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_Y" value="Y" ';
			if($trasport!='0.00' || $cost_more!='0.00' || $cost_less!='0.00') echo 'checked';
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
		
		<p>
			<label for="pezzi_confezione1" style="width:auto !important;">Visualizzo i <b>colli</b> degli articoli</label>&nbsp;&nbsp;
			<input type="radio" name="pezzi_confezione1" id="pezzi_confezione1_N" value="N" checked /> No
			<input type="radio" name="pezzi_confezione1" id="pezzi_confezione1_Y" value="Y" /> Si
		</p>
	</div>
		
	<div id="setting-to-articles-details" class="box-options">
		<?php
		if($hasTrasport=='Y' || $hasCostMore=='Y' || $hasCostLess=='Y') {
			$id = '5';
		
			echo '<p>';
			echo '<label for="trasportAndCost'.$id.'" style="width:auto !important;">Visualizzo le <b>spese aggiuntive</b> o gli <b>sconti</b></label>&nbsp;&nbsp;';
			echo '<input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_N" value="N" ';
			if($trasport=='0.00' && $cost_more=='0.00' && $cost_less=='0.00') echo 'checked';
			echo '/> No';
			echo '<input type="radio" name="trasportAndCost'.$id.'" id="trasportAndCost'.$id.'_Y" value="Y" ';
			if($trasport!='0.00' || $cost_more!='0.00' || $cost_less!='0.00') echo 'checked';
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
jQuery(document).ready(function() {
	jQuery("input[name='doc_options']").change(function() {	

		jQuery('#setting-to-users').hide();
		jQuery('#setting-to-articles').hide();
		jQuery('#setting-to-articles-details').hide();
	
		var doc_options = jQuery("input[name='doc_options']:checked").val();

		if(doc_options=='to-users')
			jQuery('#setting-to-users').show();
		else
		if(doc_options=='to-articles')
			jQuery('#setting-to-articles').show();
		else
		if(doc_options=='to-articles-details')
			jQuery('#setting-to-articles-details').show();
			
		choiceDocOptions();
	});

	jQuery("input[name='totale_per_utente']").change(function() {			
		choiceDocOptions();
	});
	jQuery("input[name='dettaglio_per_utente']").change(function() {			
		choiceDocOptions();
	});

	jQuery("input[name='delete_to_referent2']").change(function() {			
		choiceDocOptions();
	});
	jQuery("input[name='acquistato_il']").change(function() {			
		choiceDocOptions();
	});
	jQuery("input[name='article_img']").change(function() {			
		choiceDocOptions();
	});
	jQuery("input[name='trasportAndCost1']").change(function() {			
		choiceDocOptions();
	});
	jQuery("input[name='trasportAndCost2']").change(function() {			
		choiceDocOptions();
	});
	jQuery("input[name='trasportAndCost3']").change(function() {			
		choiceDocOptions();
	});
	jQuery("input[name='trasportAndCost4']").change(function() {			
		choiceDocOptions();
	});
	jQuery("input[name='trasportAndCost5']").change(function() {			
		choiceDocOptions();
	});
	jQuery("input[name='trasportAndCost5']").change(function() {			
		choiceDocOptions();
	});	
	jQuery("input[name='totale_per_articolo']").change(function() {			
		choiceDocOptions();
	});
	jQuery("input[name='codice1']").change(function() {			
		choiceDocOptions();
	});	
	jQuery("input[name='codice2']").change(function() {			
		choiceDocOptions();
	});	
	jQuery("input[name='pezzi_confezione1']").change(function() {			
		choiceDocOptions();
	});	
	jQuery("input[name='note1']").change(function() {			
		choiceDocOptions();
	});	
	jQuery("input[name='delete_to_referent1']").change(function() {			
		choiceDocOptions();
	});	
});
</script>