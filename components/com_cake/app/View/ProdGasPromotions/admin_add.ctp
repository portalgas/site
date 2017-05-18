<?php
/*
echo "<pre>";
print_r($prodGasArticleResults);
echo "</pre>";
*/
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index'));
$this->Html->addCrumb(__('Add ProdGasPromotion'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="orders form">';

echo $this->Form->create('ProdGasPromotion',array('id'=>'formGas','enctype' => 'multipart/form-data'));
echo '<fieldset>';

	echo '<legend>'.__('Add ProdGasPromotion').'</legend>';

echo '<div class="tabs">';
echo '<ul>';
echo '<li><a href="#tabs-0"><span>'.__('Dati promozione').'</span></a></li>';
echo '<li><a href="#tabs-1"><span>'.__('Articoli della promozione').'</span></a></li>';
echo '<li><a href="#tabs-2"><span>'.__('Gas già associati').'</span></a></li>';
echo '<li><a href="#tabs-3"><span>'.__('Gas non associati').'</span></a></li>';
echo '</ul>';

echo '<div id="tabs-0">';

	echo $this->Form->input('name', array('id' => 'name'));
	
	echo $this->Form->input('data_inizio',array('type' => 'text','size'=>'30','label' => __('Data inizio'), 'value' => $data_inizio, 'required'=>'false'));
	echo $this->Ajax->datepicker('ProdGasPromotionDataInizio',array('dateFormat' => 'DD, d MM yy','altField' => '#ProdGasPromotionDataInizioDb', 'altFormat' => 'yy-mm-dd'));
	echo '<input type="hidden" id="ProdGasPromotionDataInizioDb" name="data[ProdGasPromotion][data_inizio_db]" value="'.$data_inizio_db.'" />';
	
	echo $this->Form->input('data_fine',array('type' => 'text','size'=>'30','label' => __('Data fine'), 'value' => $data_fine, 'required'=>'false'));
	echo $this->Ajax->datepicker('ProdGasPromotionDataFine',array('dateFormat' => 'DD, d MM yy','altField' => '#ProdGasPromotionDataFineDb', 'altFormat' => 'yy-mm-dd'));
	echo '<input type="hidden" id="ProdGasPromotionDataFineDb" name="data[ProdGasPromotion][data_fine_db]" value="'.$data_fine_db.'" />';
	
	echo $this->Form->input('nota');
	
	echo $this->Html->div('clearfix','');
	
	echo $this->Form->input('Document.img1', array(
	    'between' => '<br />',
	    'type' => 'file',
	     'label' => 'Carica una nuova immagine', 'tabindex'=>($i+1)
	));
		
	echo $this->element('legendaProdGasPromotionImg');
	
echo '</div>';

/*
 * elenco Articoli
 */
echo '<div id="tabs-1">';

	if(!empty($prodGasArticleResults)) { 

		echo '<table cellpadding="0" cellspacing="0">';	
		echo '<tr>';	
			echo '<th>'.__('N').'</th>';	
			echo '<th>'.__('codice').'</th>';
			?>
			<th colspan="2"><?php echo __('Name');?></th>
			<th><?php echo __('confezione');?></th>
			<th><?php echo __('qta_in_promozione');?></th>
			<th><?php echo __('PrezzoUnita');?></th>
			<th><?php echo __('prezzo_unita_in_promozione');?></th>
			<th><?php echo __('importo_originale');?></th>
			<th><?php echo __('importo_scontato');?></th>
			<th><?php echo '<input type="checkbox" id="prod_gas_article_id_selected_all" name="prod_gas_article_id_selected_all" value="ALL" />';?></th>
	</tr>
	<?php
	$i=0;
	foreach ($prodGasArticleResults as $numResult => $result):
	
		echo '<tr class="view" id="row-'.$result['ProdGasArticle']['id'].'">';
		echo '<td>'.($numResult+1).'</td>';
		
		echo '<td>'.$result['ProdGasArticle']['codice'].'</td>';
		
		echo '<td>';
		if(!empty($result['ProdGasArticle']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$result['ProdGasArticle']['supplier_id'].DS.$result['ProdGasArticle']['img1'])) {
			echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$result['ProdGasArticle']['supplier_id'].'/'.$result['ProdGasArticle']['img1'].'" />';
		}		
		echo '</td>';
		
		echo '<td>'.$result['ProdGasArticle']['name'].'&nbsp;';
		echo $this->App->drawArticleNota($i, strip_tags($result['ProdGasArticle']['nota']));
		echo '</td>';
		echo '<td>';
		echo $this->App->getArticleConf($result['ProdGasArticle']['qta'], $result['ProdGasArticle']['um']);
		echo '</td>';
		echo '<td>';
		echo '<input type="hidden" id="prezzo-'.$result['ProdGasArticle']['id'].'" value="'.$result['ProdGasArticle']['prezzo'].'" />';
		echo $this->Form->input('qta_in_promozione',array('id' => 'qta_in_promozione-'.$result['ProdGasArticle']['id'], 'name' => 'data[ProdGasPromotion][ProdGasArticlesPromotion]['.$result['ProdGasArticle']['id'].'][qta]', 'label' => false, 'type' => 'text','size' => '3','default' => '0','tabindex'=>($i+1),'class' => 'qta_in_promozione noWidth', 'required'=>'false'));
		echo '</td>';
		echo '<td>';
		echo $result['ProdGasArticle']['prezzo_e'];
		echo '</td>';
		echo '<td>';
		echo '<input type="hidden" id="prezzo_unita_in_promozione-'.$result['ProdGasArticle']['id'].'" value="'.$result['ProdGasArticlesPromotion']['prezzo_unita'].'" name="data[ProdGasPromotion][ProdGasArticlesPromotion]['.$result['ProdGasArticle']['id'].'][prezzo_unita]" />';
		echo '<span id="prezzo_unita_in_promozione_label-'.$result['ProdGasArticle']['id'].'">'.$result['ProdGasArticlesPromotion']['prezzo_unita_'].'</span><span> &euro;</span>';
		echo '</td>';
		echo '<td>';
		echo '<input type="hidden" class="importo_originale" id="importo_originale-'.$result['ProdGasArticle']['id'].'" value="0.00" name="data[ProdGasPromotion][ProdGasArticlesPromotion]['.$result['ProdGasArticle']['id'].'][importo_originale]" />';
		echo '<span id="importo_originale_label-'.$result['ProdGasArticle']['id'].'">0,00</span><span> &euro;</span>';
		echo '</td>';
		echo '<td>';
		echo $this->Form->input('importo_scontato',array('id' => 'importo_scontato-'.$result['ProdGasArticle']['id'], 'name' => 'data[ProdGasPromotion][ProdGasArticlesPromotion]['.$result['ProdGasArticle']['id'].'][importo_scontato]', 'label' => false, 'type' => 'text','size' => '5','default' => '0,00','tabindex'=>($i+1), 'class' => 'importo_scontato noWidth double', 'after' => '€', 'required'=>'false'));
		echo '</td>';			
		echo '<td>';
		echo '<input type="checkbox" id="'.$result['ProdGasArticle']['id'].'" name="prod_gas_article_id_selected" value="'.$result['ProdGasArticle']['id'].'" />';
		echo '</td>';		
		echo '</tr>';
		
	endforeach;
	
	/*
	 * totali
	 */
	echo '<tr>';
	echo '<td colspan="8"></td>';
	echo '<td>';
	echo '<input type="hidden" id="importo_originale_totale" value="" name="data[ProdGasPromotion][importo_originale_totale]" />';
	echo '<span id="importo_originale_totale_label"></span><span> &euro;</span>';		
	echo '</td>';
	echo '<td>';
	echo '<input type="hidden" id="importo_scontato_totale" value="" name="data[ProdGasPromotion][importo_scontato_totale]" />';
	echo '<span id="importo_scontato_totale_label"></span><span> &euro;</span>';			
	echo '</td>';
	echo '<td></td>';	 
	echo '</tr>';
	
	echo '</table>';
	
	}
	else
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud'));
echo '</div>';

/*
 * Gas già Associati
 */
echo '<div id="tabs-2">';

	if(!empty($organizationResults)) {

	echo '<table cellpadding="0" cellspacing="0">';
		echo '<tr>';
		echo '<th colspan="2">'.__('Name').'</th>';
		echo '<th>Localit&agrave;</th>';
		echo '<th>'.__('Trasport').'</th>';
		echo '<th>'.__('CostMore').'</th>';
		echo '<th><input type="checkbox" id="organization_id_selected_all" name="organization_id_selected_all" value="ALL" /></th>';
		echo '</tr>';
		
		foreach ($organizationResults as $numResult => $result):
			
			echo '<tr class="view" id="row-org-'.$result['Organization']['id'].'">';
			
			echo '<td>';
			echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" /> ';	
			echo '</td>';
			
			echo '<td>';
				echo $result['Organization']['name']; 
				if(!empty($result['Organization']['descrizione'])) echo '<div class="small">'.$result['Organization']['descrizione'].'</div>';
			echo '</td>';
			echo '<td>';
				   if(!empty($result['Organization']['localita'])) echo $result['Organization']['localita'].'&nbsp;';
				   if(!empty($result['Organization']['cap'])) echo $result['Organization']['cap'].'&nbsp;';
				   if(!empty($result['Organization']['provincia'])) echo '('.h($result['Organization']['provincia']).')'; 
			echo '</td>';

			echo '<td>';
			echo $this->Form->input('trasport',array('id' => 'trasport-'.$result['Organization']['id'], 'name' => 'data[ProdGasPromotion][Organization]['.$result['Organization']['id'].'][trasport]', 'label' => false, 'type' => 'text','size' => '6','default' => '0,00','tabindex'=>($i+1),'class' => 'noWidth double', 'after' => '€', 'required'=>'false'));
			echo '</td>';	
			echo '<td>';
			echo $this->Form->input('costMore',array('id' => 'costMore-'.$result['Organization']['id'], 'name' => 'data[ProdGasPromotion][Organization]['.$result['Organization']['id'].'][costMore]', 'label' => false, 'type' => 'text','size' => '6','default' => '0,00','tabindex'=>($i+1),'class' => 'noWidth double', 'after' => '€', 'required'=>'false'));
			echo '</td>';
			
			echo '<td>';
			echo '<input type="checkbox" id="org-'.$result['Organization']['id'].'" name="organization_id_selected" value="'.$result['Organization']['id'].'" />';
			echo '</td>';
			echo '</tr>';
		endforeach;		
		echo '</table>';
	}
	else
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud'));
		
echo '</div>';


/*
 * Gas non associati
 */
echo '<div id="tabs-3">';

	if(!empty($organizationNotResults)) {
?>	
	<table cellpadding="0" cellspacing="0">
		<tr>
				<th colspan="2"><?php echo __('Name');?></th>
				<th>Localit&agrave;</th>
		</tr>
		<?php
		foreach ($organizationNotResults as $result):
			echo '<tr valign="top">';
			
			echo '<td>';
			echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" /> ';	
			echo '</td>';
			
			echo '<td>';
				echo $result['Organization']['name']; 
				if(!empty($result['Organization']['descrizione'])) echo '<div class="small">'.$result['Organization']['descrizione'].'</div>';
			echo '</td>';
			echo '<td>';
				   if(!empty($result['Organization']['localita'])) echo $result['Organization']['localita'].'&nbsp;';
				   if(!empty($result['Organization']['cap'])) echo $result['Organization']['cap'].'&nbsp;';
				   if(!empty($result['Organization']['provincia'])) echo '('.h($result['Organization']['provincia']).')'; 
			echo '</td>';
			?>
		</tr>
		<?php endforeach; ?>
		
	</table>
<?php
	}
	else
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud'));

echo '</div>';

echo '</fieldset>';

echo $this->Form->hidden('prod_gas_article_ids_selected',array('id' =>'prod_gas_article_ids_selected', 'name' =>'data[ProdGasPromotion][prod_gas_article_ids_selected]', 'value'=>''));
echo $this->Form->hidden('organization_ids_selected',array('id' =>'organization_ids_selected', 'name' =>'data[ProdGasPromotion][organization_ids_selected]', 'value'=>''));
echo $this->Form->end(__('Submit'));

echo '</div>';

echo '<div class="actions">';
echo '<h3>'.__('Actions').'</h3>';
echo '<ul>';
echo '<li>'.$this->Html->link(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index'),array('class'=>'action actionReload')).'</li>';
echo '</ul>';
echo '</div>';
?>

<script type="text/javascript">
function calcola_importo_originale_totale() {
	var importo_originale_totale = 0;
	jQuery(".importo_originale").each(function () {
		var importo_originale = $(this).val();
		console.log("calcola_importo_originale_totale - importo_originale:"+importo_originale+" => importo_originale_totale:"+importo_originale_totale);
		
		importo_originale_totale = (parseFloat(importo_originale) + parseFloat(importo_originale_totale)); 
	});
	
	jQuery("#importo_originale_totale").val(number_format(importo_originale_totale,2));
	importo_originale_totale = number_format(importo_originale_totale,2,',','.');
	jQuery("#importo_originale_totale_label").html(importo_originale_totale);	
}
function calcola_importo_scontato_totale() {
	var importo_scontato_totale = 0;
	jQuery(".importo_scontato").each(function () {
		var importo_scontato = $(this).val();
		importo_scontato = numberToJs(importo_scontato);
		console.log("calcola_importo_scontato_totale - importo_scontato:"+importo_scontato+" => importo_scontato_totale:"+importo_scontato_totale);
		
		importo_scontato_totale = (parseFloat(importo_scontato) + parseFloat(importo_scontato_totale));
	});
	
	jQuery("#importo_scontato_totale").val(number_format(importo_scontato_totale,2));
	importo_scontato_totale = number_format(importo_scontato_totale,2,',','.');
	jQuery("#importo_scontato_totale_label").html(importo_scontato_totale);		
}

/*
 * in base all'importo scontato calcolo i lnuovo prezzo per unita'
 */
function setta_prezzo_unita_in_promozione(idProdGasArticle) {
	var importo_scontato =  jQuery("#importo_scontato-"+idProdGasArticle).val();
	var qta =  jQuery("#qta_in_promozione-"+idProdGasArticle).val();
	
	var prezzo_unita_in_promozione = (parseFloat(importo_scontato) / parseFloat(qta));
	prezzo_unita_in_promozione = number_format(prezzo_unita_in_promozione,2,',','.');
	jQuery("#prezzo_unita_in_promozione-"+idProdGasArticle).val(prezzo_unita_in_promozione);
	jQuery("#prezzo_unita_in_promozione_label-"+idProdGasArticle).html(prezzo_unita_in_promozione);	
}  

function abilitaDisabilitaRowArticles(idRow, checked) {
	if(checked==false) {
		jQuery('#row-'+idRow).css('opacity', '0.5');
		jQuery('#qta_in_promozione-'+idRow).prop('disabled', true);
		jQuery('#qta_in_promozione-'+idRow).val("0");	
		jQuery('#importo_originale-'+idRow).val("0.00");
		jQuery('#prezzo_unita_in_promozione_label-'+idRow).html("0,00");
		jQuery('#importo_originale_label-'+idRow).html("0,00");
		jQuery('#importo_scontato-'+idRow).val("0,00");	
		jQuery('#importo_scontato-'+idRow).prop('disabled', true);		
	}
	else {
		jQuery('#row-'+idRow).css('opacity', '1');
		jQuery('#qta_in_promozione-'+idRow).prop('disabled', false);
		jQuery('#importo_scontato-'+idRow).prop('disabled', false);
	}
}

function abilitaDisabilitaRowOrganizations(idRow, checked) {
	if(checked==false) {
		jQuery('#row-org-'+idRow).css('opacity', '0.5');
		jQuery('#trasport-'+idRow).prop('disabled', true);
		jQuery('#costMore-'+idRow).prop('disabled', true);	
		jQuery('#trasport-'+idRow).val("0,00");
		jQuery('#costMore-'+idRow).val("0,00");		
	}
	else {
		jQuery('#row-org-'+idRow).css('opacity', '1');
		jQuery('#trasport-'+idRow).prop('disabled', false);
		jQuery('#costMore-'+idRow).prop('disabled', false);
	}	
}

jQuery(document).ready(function() {	
	
	jQuery(function() {
		jQuery( ".tabs" ).tabs({
			event: "click"
		});
	});	
	
	jQuery('.qta_in_promozione').focusout(function() {validateNumberField(this,'quantita\' massima');});
	jQuery('.double').focusout(function() {validateNumberField(this,'importo');});
	
	/*
	 * articles
	 */
	jQuery("input[name='prod_gas_article_id_selected']").each(function( index ) {
		var idRow = jQuery(this).attr('id');
		var checked = jQuery(this).is(":checked");
		abilitaDisabilitaRowArticles(idRow, checked);
	});

	/* seleziona tutti */
	jQuery('#prod_gas_article_id_selected_all').click(function () {
		var checked = jQuery("input[name='prod_gas_article_id_selected_all']:checked").val();
		if(checked=='ALL')
			jQuery('input[name=prod_gas_article_id_selected]').prop('checked',true);
		else
			jQuery('input[name=prod_gas_article_id_selected]').prop('checked',false);
		
		jQuery("input[name='prod_gas_article_id_selected']").each(function( index ) {
			var idRow = jQuery(this).attr('id');
			var checked = jQuery(this).is(":checked");
			abilitaDisabilitaRowArticles(idRow, checked);
		});		
	});

	/* seleziona uno */ 
	jQuery("input[name='prod_gas_article_id_selected']").click(function () {
		var idRow = jQuery(this).attr('id');
		var checked = jQuery(this).is(":checked");
		
		abilitaDisabilitaRowArticles(idRow, checked);
		
		calcola_importo_originale_totale();
		calcola_importo_scontato_totale();
	});
	
	/*
	 * organizations
	 */
	jQuery("input[name='organization_id_selected']").each(function( index ) {
		var arr = jQuery(this).attr('id').split("-"); 
		var idRow = arr[1]; 
		var checked = jQuery(this).is(":checked");
		abilitaDisabilitaRowOrganizations(idRow, checked);
	});
	
	/* seleziona tutti */
	jQuery('#organization_id_selected_all').click(function () {
		var checked = jQuery("input[name='organization_id_selected_all']:checked").val();
		if(checked=='ALL')
			jQuery('input[name=organization_id_selected]').prop('checked',true);
		else
			jQuery('input[name=organization_id_selected]').prop('checked',false);
		
		jQuery("input[name='organization_id_selected']").each(function( index ) {
			var arr = jQuery(this).attr('id').split("-"); 
			var idRow = arr[1]; 
			var checked = jQuery(this).is(":checked");
			abilitaDisabilitaRowOrganizations(idRow, checked);
		});		
	});
	 
	/* seleziona uno */ 
	jQuery("input[name='organization_id_selected']").click(function () {
		var arr = jQuery(this).attr('id').split("-"); 
		var idRow = arr[1]; 
		var checked = jQuery(this).is(":checked");
		
		abilitaDisabilitaRowOrganizations(idRow, checked);
	});
	
	
	jQuery('.qta_in_promozione').change(function() {

		var importo_originale = 0;
		var arr = jQuery(this).attr('id').split("-"); 
		var idProdGasArticle = arr[1]; 
		
		var prezzo = jQuery("#prezzo-"+idProdGasArticle).val();
		var qta_in_promozione = jQuery(this).val();
		console.log(".qta_in_promozione.change() idProdGasArticle "+idProdGasArticle+" qta_in_promozione:"+qta_in_promozione+" prezzo:"+prezzo);
		if(qta_in_promozione>0) {
			qta_in_promozione = numberToJs(qta_in_promozione);
			importo_originale += (parseFloat(prezzo) * parseFloat(qta_in_promozione));
		}
		
		jQuery("#importo_originale-"+idProdGasArticle).val(number_format(importo_originale,2));
		importo_originale = number_format(importo_originale,2,',','.');
		jQuery("#importo_originale_label-"+idProdGasArticle).html(importo_originale);
		
		calcola_importo_originale_totale();
	});
	
	jQuery('.importo_scontato').change(function() {
		var arr = jQuery(this).attr('id').split("-"); 
		idProdGasArticle = arr[1]; 
		
		setta_prezzo_unita_in_promozione(idProdGasArticle);
		
		calcola_importo_scontato_totale();
	});
	
	jQuery('#formGas').submit(function() {
	
		var name = jQuery('#name').val();
		if(name=='' || name==undefined) {
			alert("<?php echo __('jsAlertNameRequired');?>");
			jQuery('.tabs').tabs('option', 'active',0);
			jQuery('#name').focus();
			return false;
		}
		
		var ProdGasPromotionDataInizioDb = jQuery('#ProdGasPromotionDataInizioDb').val();
		if(ProdGasPromotionDataInizioDb=='' || ProdGasPromotionDataInizioDb==undefined) {
			jQuery('.tabs').tabs('option', 'active',0);
			alert("Devi indicare la data di apertura della promozione");
			return false;
		}	
		
		var ProdGasPromotionDataFineDb = jQuery('#ProdGasPromotionDataFineDb').val();
		if(ProdGasPromotionDataFineDb=='' || ProdGasPromotionDataFineDb==undefined) {
			jQuery('.tabs').tabs('option', 'active',0);
			alert("Devi indicare la data di chiusura della promozione");
			return false;
		}	
		
		/*
		 * articoli scelti
		 */
		var prod_gas_article_id_selected = '';
		for(i = 0; i < jQuery("input[name='prod_gas_article_id_selected']:checked").length; i++) {
			var elem = jQuery("input[name='prod_gas_article_id_selected']:checked").eq(i);
			prod_gas_article_id_selected += elem.val()+',';
		}
				
		if(prod_gas_article_id_selected!='') {
			prod_gas_article_id_selected = prod_gas_article_id_selected.substring(0,prod_gas_article_id_selected.length-1);		
			jQuery('#prod_gas_article_ids_selected').val(prod_gas_article_id_selected);
		}
		else {
			jQuery('.tabs').tabs('option', 'active',1);
			alert("Scegli quali articoli associare alla promozione");
			return false;
		}
		
		/*
		 * GAS scelti
		 */
		var organization_id_selected = '';
		for(i = 0; i < jQuery("input[name='organization_id_selected']:checked").length; i++) {
			var elem = jQuery("input[name='organization_id_selected']:checked").eq(i);
			organization_id_selected += elem.val()+',';
		}		
		if(organization_id_selected!='') {
			organization_id_selected = organization_id_selected.substring(0,organization_id_selected.length-1);		
			jQuery('#organization_ids_selected').val(organization_id_selected);
		}
		else {
			jQuery('.tabs').tabs('option', 'active',1);
			alert("Scegli quali GAS associare alla promozione");
			return false;
		}	 
		
		return true;
	});
	
	calcola_importo_originale_totale();
	calcola_importo_scontato_totale();

});
</script>