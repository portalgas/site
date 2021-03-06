<?php
$this->App->d($this->request->data, $debug);
$this->App->d($articleResults, $debug);
$this->App->d($organizationResults, $debug);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index_gas'));
$this->Html->addCrumb(__('Edit ProdGasPromotion'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="orders form">';

echo $this->Form->create('ProdGasPromotion',array('id'=>'formGas','enctype' => 'multipart/form-data'));
echo '<fieldset>';

	echo '<legend>'.__('Edit ProdGasPromotion').'</legend>';

	echo '<div class="tabs">';
	echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
	echo '<li class="active"><a href="#tabs-0" data-toggle="tab">'.__('ProdGasPromotionDati').'</a></li>';
	echo '<li><a href="#tabs-1" data-toggle="tab">'.__('ProdGasArticlesInPromotion').'</a></li>';
	echo '<li><a href="#tabs-2" data-toggle="tab">'.__('ProdGasPromotionOrganizationsAssociate').'</a></li>';
	echo '<li><a href="#tabs-3" data-toggle="tab">'.__('ProdGasPromotionOrganizationsNotAssociate').'</a></li>';			
	echo '</ul>';

	echo '<div class="tab-content">';
	echo '<div class="tab-pane fade active in" id="tabs-0">';

	echo $this->Form->input('name', ['id' => 'name']);
	
	echo $this->App->drawDate('ProdGasPromotion', 'data_inizio', __('DataInizio'), $this->Form->value('ProdGasPromotion.data_inizio'));
	
	echo $this->App->drawDate('ProdGasPromotion', 'data_fine', __('DataFine'), $this->Form->value('ProdGasPromotion.data_fine'));
	
	echo $this->element('boxMsg', ['class_msg' => 'info', 'msg' => __('msg_prodgas_promotion_date')]);		
	echo $this->Form->input('nota', ['label' => "Nota da aggiungere all'ordine"]);
		
	echo $this->Html->div('clearfix','');
	echo $this->element('boxMsg', ['class_msg' => 'info', 'msg' => __('msg_prodgas_contact')]);
	
	echo $this->Form->input('contact_name', ['value' => $this->Form->value('ProdGasPromotion.contact_name'), 'label' => __('Name'), 'required' => false]);
	echo $this->Form->input('contact_mail', ['value' => $this->Form->value('ProdGasPromotion.contact_mail'), 'label' => __('Email'), 'required' => false]);
	echo $this->Form->input('contact_phone', ['value' => $this->Form->value('ProdGasPromotion.contact_phone'), 'label' => __('Telephone'), 'required' => false]);
	
	/*		
	if(isset($file1)) {
		echo '<div class="input">';
		echo '<img class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_promotions').'/'.$this->request->data['ProdGasPromotion']['supplier_id'].'/'.$file1->name.'" />';	
		echo '&nbsp;&nbsp;&nbsp;'.$this->App->formatBytes($file1->size());
		echo '</div>';	
		// echo $this->Form->checkbox('file1_delete', array('label' => 'Cancella file', 'value' => 'Y'));
		// echo $this->Form->label('Cancella file');					
	}
						
	echo $this->Form->input('Document.img1', array(
	    'between' => '<br />',
	    'type' => 'file',
	     'label' => 'Carica una nuova immagine', 'tabindex'=>($i+1)
	));
		
	echo $this->element('legendaProdGasPromotionImg');
	*/

/*
 * elenco Articoli
 */
echo '</div>';
echo '<div class="tab-pane fade" id="tabs-1">';

	echo $this->element('boxMsg', ['class_msg' => 'info', 'msg' => __('msg_prodgas_articles_valid')]);

	if(!empty($articleResults) || !empty($this->request->data['ProdGasArticlesPromotion'])) { 

		echo '<div class="table-responsive"><table class="table table-hover table-striped">';	
		echo '<tr>';
		echo '<th>'.__('N').'</th>';	
		echo '<th>'.__('codice').'</th>';
		echo '<th colspan="2">'.__('Name').'</th>';
		echo '<th>'.__('Package').'</th>';
		echo '<th>'.__('qta_in_promozione').'</th>';
		echo '<th>'.__('PrezzoUnita').'</th>';
		echo '<th>'.__('prezzo_unita_in_promozione').'</th>';
		echo '<th>'.__('Importo_originale').'</th>';
		echo '<th>'.__('Importo_scontato').'</th>';
		echo '<th><input type="checkbox" id="article_id_selected_all" name="article_id_selected_all" value="ALL" /></th>';
		echo '</tr><tbody>';
		
		/*
		* articoli gia' in promozione
		*/
		$i=0;
		foreach ($this->request->data['ProdGasArticlesPromotion'] as $numResult => $result) {
	
			if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) 
				$img = true;
			else
				$img = false;

			$importo_originale = ($result['ProdGasArticlesPromotion']['qta'] * $result['Article']['prezzo']);
			$importo_originale_ = number_format($importo_originale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			
			echo '<tr class="view" id="row-'.$result['Article']['id'].'">';
			echo '<td>'.((int)$numResult+1).'</td>';
			
			echo '<td>'.$result['Article']['codice'].'</td>';
			
			echo '<td>';
			if($img) {
				echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
			}		
			echo '</td>';
			
			echo '<td>'.$result['Article']['name'].'&nbsp;';
			echo $this->App->drawArticleNota($i, strip_tags($result['Article']['nota']));
			echo '</td>';
			echo '<td>';
			echo $this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);
			echo '</td>';
			echo '<td>';
			echo '<input type="hidden" id="prezzo-'.$result['Article']['id'].'" value="'.$result['Article']['prezzo'].'" />';
			echo $this->Form->input('qta_in_promozione', ['id' => 'qta_in_promozione-'.$result['Article']['id'], 'name' => 'data[ProdGasPromotion][ProdGasArticlesPromotion]['.$result['Article']['id'].'][qta]', 
														  'value' => $result['ProdGasArticlesPromotion']['qta'],
														   'label' => false, 'type' => 'text','size' => '3', 'tabindex'=>($i+1),'class' => 'qta_in_promozione', 'required'=>'false']);
			echo '</td>';
			echo '<td>';
			echo $result['Article']['prezzo_e'];
			echo '</td>';
			echo '<td>';
			echo '<input type="hidden" id="prezzo_unita_in_promozione-'.$result['Article']['id'].'" value="'.$result['ProdGasArticlesPromotion']['prezzo_unita'].'" name="data[ProdGasPromotion][ProdGasArticlesPromotion]['.$result['Article']['id'].'][prezzo_unita]" />';
			echo '<span id="prezzo_unita_in_promozione_label-'.$result['Article']['id'].'">'.$result['ProdGasArticlesPromotion']['prezzo_unita_'].'</span><span>&nbsp;&euro;</span>';
			echo '</td>';
			echo '<td>';
			echo '<input type="hidden" class="importo_originale" id="importo_originale-'.$result['Article']['id'].'" value="'.$importo_originale.'" name="data[ProdGasPromotion][ProdGasArticlesPromotion]['.$result['Article']['id'].'][importo_originale]" />';
			echo '<span id="importo_originale_label-'.$result['Article']['id'].'">'.$importo_originale_.'</span><span>&nbsp;&euro;</span>';
			echo '</td>';
			echo '<td>';
			echo $this->Form->input('importo_scontato',['id' => 'importo_scontato-'.$result['Article']['id'], 'name' => 'data[ProdGasPromotion][ProdGasArticlesPromotion]['.$result['Article']['id'].'][importo_scontato]', 
														'value' => $result['ProdGasArticlesPromotion']['importo_'],
														'label' => false, 'type' => 'text','style' => 'display:inline', 'tabindex'=>($i+1), 'class' => 'importo_scontato double', 'after' => '&nbsp;&euro;', 'required'=>'false']);
			echo '</td>';			
			echo '<td style="white-space: nowrap;">';
			echo '<input type="checkbox" id="'.$result['Article']['id'].'" name="article_id_selected" value="'.$result['Article']['id'].'" ';
			if($result['ProdGasArticlesPromotion']['qta']!=0) echo ' checked';
			if(!$img)
				echo 'disabled="disabled"';
			echo '/>';
			echo '</td>';		
			echo '</tr>';
			
		} // loops articleResults
		
		/*
		* articoli non in promozione
		*/
	foreach ($articleResults as $numResult => $result) {
	
		$importo_originale = ($result['ProdGasArticlesPromotion']['qta'] * $result['Article']['prezzo']);
		$importo_originale_ = number_format($importo_originale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		
		echo '<tr class="view" id="row-'.$result['Article']['id'].'">';
		echo '<td>'.((int)$numResult+1).'</td>';
		
		echo '<td>'.$result['Article']['codice'].'</td>';
		
		echo '<td>';
		if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
		}		
		echo '</td>';
		
		echo '<td>'.$result['Article']['name'].'&nbsp;';
		echo $this->App->drawArticleNota($i, strip_tags($result['Article']['nota']));
		echo '</td>';
		echo '<td>';
		echo $this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);
		echo '</td>';
		echo '<td>';
		echo '<input type="hidden" id="prezzo-'.$result['Article']['id'].'" value="'.$result['Article']['prezzo'].'" />';
		echo $this->Form->input('qta_in_promozione', ['id' => 'qta_in_promozione-'.$result['Article']['id'], 'name' => 'data[ProdGasPromotion][ProdGasArticlesPromotion]['.$result['Article']['id'].'][qta]', 
													  'value' => $result['ProdGasArticlesPromotion']['qta'],
													   'label' => false, 'type' => 'text','size' => '3','default' => '0','tabindex'=>($i+1),'class' => 'qta_in_promozione', 'required'=>'false']);
		echo '</td>';
		echo '<td>';
		echo $result['Article']['prezzo_e'];
		echo '</td>';
		echo '<td>';
		echo '<input type="hidden" id="prezzo_unita_in_promozione-'.$result['Article']['id'].'" value="'.$result['ProdGasArticlesPromotion']['prezzo_unita'].'" name="data[ProdGasPromotion][ProdGasArticlesPromotion]['.$result['Article']['id'].'][prezzo_unita]" />';
		echo '<span id="prezzo_unita_in_promozione_label-'.$result['Article']['id'].'">'.$result['ProdGasArticlesPromotion']['prezzo_unita_'].'</span><span>&nbsp;&euro;</span>';
		echo '</td>';
		echo '<td>';
		echo '<input type="hidden" class="importo_originale" id="importo_originale-'.$result['Article']['id'].'" value="'.$importo_originale.'" name="data[ProdGasPromotion][ProdGasArticlesPromotion]['.$result['Article']['id'].'][importo_originale]" />';
		echo '<span id="importo_originale_label-'.$result['Article']['id'].'">'.$importo_originale_.'</span><span>&nbsp;&euro;</span>';
		echo '</td>';
		echo '<td>';
		echo $this->Form->input('importo_scontato',['id' => 'importo_scontato-'.$result['Article']['id'], 'name' => 'data[ProdGasPromotion][ProdGasArticlesPromotion]['.$result['Article']['id'].'][importo_scontato]', 
												    'value' => $result['ProdGasArticlesPromotion']['importo_'],
													'label' => false, 'type' => 'text','style' => 'display:inline','default' => '0,00','tabindex'=>($i+1), 'class' => 'importo_scontato double', 'after' => '&nbsp;&euro;', 'required'=>'false']);
		echo '</td>';			
		echo '<td style="white-space: nowrap;">';
		echo '<input type="checkbox" id="'.$result['Article']['id'].'" name="article_id_selected" value="'.$result['Article']['id'].'" ';
		if($result['ProdGasArticlesPromotion']['qta']!=0) echo ' checked';
		echo '/>';
		echo '</td>';		
		echo '</tr>';
		
	} // loops articleResults
	
	/*
	 * totali
	 */
	echo '<tr>';
	echo '<td colspan="8"></td>';
	echo '<td>';
	echo '<input type="hidden" id="importo_originale_totale" value="" name="data[ProdGasPromotion][importo_originale_totale]" />';
	echo '<span id="importo_originale_totale_label"></span><span>&nbsp;&euro;</span>';		
	echo '</td>';
	echo '<td>';
	echo '<input type="hidden" id="importo_scontato_totale" value="" name="data[ProdGasPromotion][importo_scontato_totale]" />';
	echo '<span id="importo_scontato_totale_label"></span><span>&nbsp;&euro;</span>';			
	echo '</td>';
	echo '<td></td>';	 
	echo '</tr>';
	
	echo '</tbody></table></div>';
	
	}
	else
		echo $this->element('boxMsg', ['class_msg' => 'danger', 'msg' => __('msg_prodgas_articles_valid')]);

/*
 * Gas già Associati
 */
echo '</div>';
echo '<div class="tab-pane fade" id="tabs-2">';


	if(!empty($organizationResults)) {

	echo '<div class="table-responsive"><table class="table table-hover table-striped">';
		echo '<tr>';
		echo '<th colspan="2">'.__('Name').'</th>';
		echo '<th style="text-align: center;">'.__('TotaleUsersActive').'</th>';
		echo '<th>'.__('Trasport').'</th>';
		echo '<th>'.__('CostMore').'</th>';
		echo '<th>'.__('ProdGasSupplierDeliveriesBooking').'</th>';
		echo '<th><input type="checkbox" id="organization_id_selected_all" name="organization_id_selected_all" value="ALL" /></th>';
		echo '</tr>';
		
		foreach ($organizationResults as $numResult => $result) {
		
			if(isset($result['ProdGasPromotionsOrganization'])) {
				$trasport = $result['ProdGasPromotionsOrganization']['trasport_'];
				$costMore = $result['ProdGasPromotionsOrganization']['cost_more_'];
			}
			else {
				$trasport = '0,00';
				$costMore = '0,00'; 
			}				
			
			echo '<tr class="view" id="row-org-'.$result['Organization']['id'].'">';
			
			echo '<td>';
			echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" /> ';	
			echo '</td>';
			
			echo '<td>';
				echo $result['Organization']['name']; 
				if(!empty($result['Organization']['descrizione'])) echo '<div class="small">'.$result['Organization']['descrizione'].'</div>';
			echo '</td>';
			echo '<td style="text-align: center;">';
			echo $result['Organization']['totUsers']; 
			echo '</td>';
			
			echo '<td style="white-space: nowrap;">';
			if(isset($result['Delivery']) && !empty($result['Delivery']))
				echo $this->Form->input('trasport',array('id' => 'trasport-'.$result['Organization']['id'], 'name' => 'data[ProdGasPromotion][Organization]['.$result['Organization']['id'].'][trasport]', 'label' => false, 'type' => 'text', 'style' => 'display:inline', 'default' => $trasport,'tabindex'=>($i+1),'class' => 'double', 'after' => '&nbsp;&euro;', 'required'=>'false'));
			echo '</td>';
			echo '<td style="white-space: nowrap;">';
			if(isset($result['Delivery']) && !empty($result['Delivery']))
				echo $this->Form->input('costMore',array('id' => 'costMore-'.$result['Organization']['id'], 'name' => 'data[ProdGasPromotion][Organization]['.$result['Organization']['id'].'][costMore]', 'label' => false, 'type' => 'text', 'style' => 'display:inline', 'default' => $costMore,'tabindex'=>($i+1),'class' => 'double', 'after' => '&nbsp;&euro;', 'required'=>'false'));
			echo '</td>';
			
			echo '<td>';
			if(isset($result['Delivery']) && !empty($result['Delivery'])) {
				foreach($result['Delivery'] as $delivery_id => $delivery_name) {
					echo '<input type="checkbox" id="delivery-'.$delivery_id.'" name="'.$result['Organization']['id'].'-delivery_id_selected" value="'.$delivery_id.'" " ';
					
					if(isset($result['ProdGasPromotionsOrganizationsDelivery']))
					foreach($result['ProdGasPromotionsOrganizationsDelivery'] as $numResult => $prodGasPromotionsOrganizationsDelivery) {
						if($delivery_id==$prodGasPromotionsOrganizationsDelivery['ProdGasPromotionsOrganizationsDelivery']['delivery_id']) {
							echo ' checked';
							unset($result['ProdGasPromotionsOrganizationsDelivery'][$numResult]);
							break;
						}
					}
					echo '/>'.$delivery_name.'<br />';
				}
			}
			else
			if($result['SuppliersOrganization']['can_promotions']=='N')
				echo '<span class="label label-warning">Non abilitato alle promozioni</span>';
			else
			if(!isset($result['Delivery']) || empty($result['Delivery'])) 
			 	echo '<span class="label label-warning">Il G.A.S. non ha consegne aperte</span>';				
			echo '</td>';
			
			echo '<td>';
			if(isset($result['Delivery']) && !empty($result['Delivery'])) {
				echo '<input type="checkbox" id="org-'.$result['Organization']['id'].'" name="organization_id_selected" value="'.$result['Organization']['id'].'" ';
				if(isset($result['ProdGasPromotionsOrganization'])) echo ' checked';
				echo '/>';	

				echo '<input type="hidden" value="'.$result['Organization']['id'].'" name=data[ProdGasPromotion][Organization]['.$result['Organization']['id'].'][id]" />';						
			}
			echo '</td>';
			echo '</tr>';
		}		
		echo '</table></div>';
	}
	else
		echo $this->element('boxMsg', ['class_msg' => 'danger', 'msg' => __('msg_search_not_result')]);

/*
 * Gas non associati
 */
echo '</div>';
echo '<div class="tab-pane fade" id="tabs-3">';


	if(!empty($organizationNotResults)) {
?>	
	<div class="table-responsive"><table class="table table-hover table-striped">
		<tr>
				<th colspan="2"><?php echo __('Name');?></th>
				<th>Localit&agrave;</th>
		</tr>
		<?php
		foreach ($organizationNotResults as $result):
			echo '<tr valign="top">';
			
			echo '<td>';
			echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" /> ';	
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
		
	</table></div>
<?php
	}
	else
		echo $this->element('boxMsg', ['class_msg' => 'danger', 'msg' => __('msg_search_not_result')]);

echo '</div>';
echo '</div>'; // tab-content
echo '</div>';
echo '</fieldset>';

echo $this->Form->hidden('article_ids_selected',array('id' =>'article_ids_selected', 'name' =>'data[ProdGasPromotion][article_ids_selected]', 'value'=>''));
echo $this->Form->hidden('delivery_ids_selected',array('id' =>'delivery_ids_selected', 'name' =>'data[ProdGasPromotion][delivery_ids_selected]', 'value'=>''));
echo $this->Form->hidden('organization_ids_selected',array('id' =>'organization_ids_selected', 'name' =>'data[ProdGasPromotion][organization_ids_selected]', 'value'=>''));
echo $this->Form->end(__('Submit'));

echo '</div>';

echo '<div class="actions">';
echo '<h3>'.__('Actions').'</h3>';
echo '<ul>';
echo '<li>'.$this->Html->link(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index_gas'),array('class'=>'action actionReload')).'</li>';
echo '</ul>';
echo '</div>';
?>

<script type="text/javascript">
function calcola_importo_originale_totale() {
	var importo_originale_totale = 0;
	$(".importo_originale").each(function () {
		var importo_originale = $(this).val();
		console.log("calcola_importo_originale_totale - importo_originale:"+importo_originale+" => importo_originale_totale:"+importo_originale_totale);
		
		importo_originale_totale = (parseFloat(importo_originale) + parseFloat(importo_originale_totale)); 
	});
	
	$("#importo_originale_totale").val(number_format(importo_originale_totale,2));
	importo_originale_totale = number_format(importo_originale_totale,2,',','.');
	$("#importo_originale_totale_label").html(importo_originale_totale);	
}
function calcola_importo_scontato_totale() {
	var importo_scontato_totale = 0;
	$(".importo_scontato").each(function () {
		var importo_scontato = $(this).val();
		importo_scontato = numberToJs(importo_scontato);
		console.log("calcola_importo_scontato_totale - importo_scontato:"+importo_scontato+" => importo_scontato_totale:"+importo_scontato_totale);
		
		importo_scontato_totale = (parseFloat(importo_scontato) + parseFloat(importo_scontato_totale));
	});
	
	$("#importo_scontato_totale").val(number_format(importo_scontato_totale,2));
	importo_scontato_totale = number_format(importo_scontato_totale,2,',','.');
	$("#importo_scontato_totale_label").html(importo_scontato_totale);		
}

/*
 * in base all'importo scontato calcolo i lnuovo prezzo per unita'
 */
function setta_prezzo_unita_in_promozione(idProdGasArticle) {
	var importo_scontato =  $("#importo_scontato-"+idProdGasArticle).val();
	var qta =  $("#qta_in_promozione-"+idProdGasArticle).val();
	
	var prezzo_unita_in_promozione = (parseFloat(importo_scontato) / parseFloat(qta));
	prezzo_unita_in_promozione = number_format(prezzo_unita_in_promozione,2,',','.');
	$("#prezzo_unita_in_promozione-"+idProdGasArticle).val(prezzo_unita_in_promozione);
	$("#prezzo_unita_in_promozione_label-"+idProdGasArticle).html(prezzo_unita_in_promozione);	
}  

function abilitaDisabilitaRowArticles(idRow, checked) {
	if(checked==false) {
		$('#row-'+idRow).css('opacity', '0.5');
		$('#qta_in_promozione-'+idRow).prop('disabled', true);
		$('#qta_in_promozione-'+idRow).val("0");	
		$('#importo_originale-'+idRow).val("0.00");
		$('#prezzo_unita_in_promozione_label-'+idRow).html("0,00");
		$('#importo_originale_label-'+idRow).html("0,00");
		$('#importo_scontato-'+idRow).val("0,00");	
		$('#importo_scontato-'+idRow).prop('disabled', true);		
	}
	else {
		$('#row-'+idRow).css('opacity', '1');
		$('#qta_in_promozione-'+idRow).prop('disabled', false);
		$('#importo_scontato-'+idRow).prop('disabled', false);
	}
}

function abilitaDisabilitaRowOrganizations(idRow, checked) {
	if(checked==false) {
		$('#row-org-'+idRow).css('opacity', '0.5');
		$('#trasport-'+idRow).prop('disabled', true);
		$('#costMore-'+idRow).prop('disabled', true);	
		$('#trasport-'+idRow).val("0,00");
		$('#costMore-'+idRow).val("0,00");
		$("input[name='"+idRow+"-delivery_id_selected']").prop('disabled', true);
	}
	else {
		$('#row-org-'+idRow).css('opacity', '1');
		$('#trasport-'+idRow).prop('disabled', false);
		$('#costMore-'+idRow).prop('disabled', false);
		$("input[name='"+idRow+"-delivery_id_selected']").prop('disabled', false);
	}	
}

$(document).ready(function() {	

	$('.qta_in_promozione').focusout(function() {validateNumberField(this,'quantita\' in promozione');});
	$('.double').focusout(function() {validateNumberField(this,'importo');});
	
	/*
	 * articles
	 */
	$("input[name='article_id_selected']").each(function( index ) {
		var idRow = $(this).attr('id');
		var checked = $(this).is(":checked");
		abilitaDisabilitaRowArticles(idRow, checked);
	});

	/* seleziona tutti */
	$('#article_id_selected_all').click(function () {
		var checked = $("input[name='article_id_selected_all']:checked").val();
		if(checked=='ALL')
			$('input[name=article_id_selected]:not(:disabled)').prop('checked',true);
		else
			$('input[name=article_id_selected]:not(:disabled)').prop('checked',false);
		
		$("input[name='article_id_selected']").each(function( index ) {
			var idRow = $(this).attr('id');
			var checked = $(this).is(":checked");
			abilitaDisabilitaRowArticles(idRow, checked);
		});		
	});

	/* seleziona uno */ 
	$("input[name='article_id_selected']:not(:disabled)").click(function () {
		var idRow = $(this).attr('id');
		var checked = $(this).is(":checked");
		
		abilitaDisabilitaRowArticles(idRow, checked);
		
		calcola_importo_originale_totale();
		calcola_importo_scontato_totale();
	});
	
	/*
	 * organizations
	 */
	$("input[name='organization_id_selected']").each(function( index ) {
		var arr = $(this).attr('id').split("-"); 
		var idRow = arr[1]; 
		var checked = $(this).is(":checked");
		abilitaDisabilitaRowOrganizations(idRow, checked);
	});
	
	/* seleziona tutti */
	$('#organization_id_selected_all').click(function () {
		var checked = $("input[name='organization_id_selected_all']:checked").val();
		if(checked=='ALL')
			$('input[name=organization_id_selected]').prop('checked',true);
		else
			$('input[name=organization_id_selected]').prop('checked',false);
		
		$("input[name='organization_id_selected']").each(function( index ) {
			var arr = $(this).attr('id').split("-"); 
			var idRow = arr[1]; 
			var checked = $(this).is(":checked");
			abilitaDisabilitaRowOrganizations(idRow, checked);
		});		
	});
	 
	/* seleziona uno */ 
	$("input[name='organization_id_selected']").click(function () {
		var arr = $(this).attr('id').split("-"); 
		var idRow = arr[1]; 
		var checked = $(this).is(":checked");
		
		abilitaDisabilitaRowOrganizations(idRow, checked);
	});
	
	
	$('.qta_in_promozione').change(function() {

		var importo_originale = 0;
		var arr = $(this).attr('id').split("-"); 
		idProdGasArticle = arr[1]; 
		
		var prezzo = $("#prezzo-"+idProdGasArticle).val();
		var qta_in_promozione = $(this).val();
		console.log(".qta_in_promozione.change() idProdGasArticle "+idProdGasArticle+" qta_in_promozione:"+qta_in_promozione+" prezzo:"+prezzo);
		if(qta_in_promozione>0) {
			qta_in_promozione = numberToJs(qta_in_promozione);
			importo_originale += (parseFloat(prezzo) * parseFloat(qta_in_promozione));
		}
		
		$("#importo_originale-"+idProdGasArticle).val(number_format(importo_originale,2));
		importo_originale = number_format(importo_originale,2,',','.');
		$("#importo_originale_label-"+idProdGasArticle).html(importo_originale);
		
		calcola_importo_originale_totale();
	});
	
	$('.importo_scontato').change(function() {
		var arr = $(this).attr('id').split("-"); 
		idProdGasArticle = arr[1]; 
		
		setta_prezzo_unita_in_promozione(idProdGasArticle);
		
		calcola_importo_scontato_totale();
	});
	
	$('#formGas').submit(function() {
	
		var contact_name = $("input[name='data[ProdGasPromotion][contact_name]']").val();
		if(contact_name=='' || contact_name==undefined) {
			alert("Indica il nome del contatto");
			$('.tabs li:eq(0) a').tab('show');
			$("input[name='data[ProdGasPromotion][contact_name]']").focus();
			return false;
		}		
		var contact_mail = $("input[name='data[ProdGasPromotion][contact_mail]']").val();
		if(contact_mail=='' || contact_mail==undefined) {
			alert("Indica la mail del contatto");
			$('.tabs li:eq(0) a').tab('show');
			$("input[name='data[ProdGasPromotion][contact_mail]']").focus();
			return false;
		}		
		var contact_phone = $("input[name='data[ProdGasPromotion][contact_phone]']").val();
		if(contact_phone=='' || contact_phone==undefined) {
			alert("Indica il telefono del contatto");
			$('.tabs li:eq(0) a').tab('show');
			$("input[name='data[ProdGasPromotion][contact_phone]']").focus();
			return false;
		}		
	
		$('#article_ids_selected').val("");
		$('#organization_ids_selected').val("");
	    $('#delivery_ids_selected').val("");
	
		var name = $('#name').val();
		if(name=='' || name==undefined) {
			alert("<?php echo __('jsAlertNameRequired');?>");
			$('.tabs li:eq(0) a').tab('show');
			$('#name').focus();
			return false;
		}
		
		var ProdGasPromotionDataInizioDb = $('#ProdGasPromotionDataInizioDb').val();
		if(ProdGasPromotionDataInizioDb=='' || ProdGasPromotionDataInizioDb==undefined) {
			$('.tabs li:eq(0) a').tab('show');
			alert("Devi indicare la data di apertura della promozione");
			return false;
		}	
		
		var ProdGasPromotionDataFineDb = $('#ProdGasPromotionDataFineDb').val();
		if(ProdGasPromotionDataFineDb=='' || ProdGasPromotionDataFineDb==undefined) {
			$('.tabs li:eq(0) a').tab('show');
			alert("Devi indicare la data di chiusura della promozione");
			return false;
		}	
		
		/*
		 * articoli scelti
		 */
		var article_id_selected = '';
		for(i = 0; i < $("input[name='article_id_selected']:checked").length; i++) {
			var elem = $("input[name='article_id_selected']:checked").eq(i);
			article_id_selected += elem.val()+',';
			
			var qta_in_promozione = $('#qta_in_promozione-'+elem.val()).val();
			// console.log('qta_in_promozione '+qta_in_promozione);
			if(qta_in_promozione=='' || qta_in_promozione==0 || qta_in_promozione=='0,00' || qta_in_promozione=='0.00') {
				alert("La quantità in promozione dev'essere un valore maggiore di 0!");
				return false;
			}
			var importo_scontato = $('#importo_scontato-'+elem.val()).val();
			// console.log('importo_scontato '+qta_in_promozione);
			if(importo_scontato=='' || importo_scontato==0 || importo_scontato=='0,00' || importo_scontato=='0.00') {
				alert("L'importo scontato dev'essere un valore maggiore di 0!");
				return false;
			}
		}
				
		if(article_id_selected!='') {
			article_id_selected = article_id_selected.substring(0,article_id_selected.length-1);		
			$('#article_ids_selected').val(article_id_selected);
		}
		else {
			$('.tabs li:eq(1) a').tab('show');
			alert("Scegli quali articoli associare alla promozione");
			return false;
		}
		
		/*
		 * GAS scelti + deliveries
		 */
		var organization_id_selected = '';
		var delivery_id_selected = '';
		for(i = 0; i < $("input[name='organization_id_selected']:checked").length; i++) {
			var elem = $("input[name='organization_id_selected']:checked").eq(i);
			var organization_id = elem.val();
			organization_id_selected += elem.val()+',';
			
			/* console.log("Scelto GAS "+organization_id); */
			
			var checked_delivery_to_gas = false;
			for(ii = 0; ii < $("input[name='"+organization_id+"-delivery_id_selected']:checked").length; ii++) {
			
				checked_delivery_to_gas = true;
				var elem_delivery = $("input[name='"+organization_id+"-delivery_id_selected']:checked").eq(ii);
				
				delivery_id_selected += organization_id+'-'+elem_delivery.val()+',';
			}
			if(!checked_delivery_to_gas) {
				$('.tabs li:eq(2) a').tab('show');
				alert("Indica per il GAS scelto per quali consegne potresti consegnare");
				return false;
			}			
		}		
		if(organization_id_selected!='') {
			organization_id_selected = organization_id_selected.substring(0,organization_id_selected.length-1);		
			$('#organization_ids_selected').val(organization_id_selected);
		}
		else {
			$('.tabs li:eq(2) a').tab('show');
			alert("Scegli quali GAS associare alla promozione");
			return false;
		}	 
		
		if(delivery_id_selected!='') {
			delivery_id_selected = delivery_id_selected.substring(0,delivery_id_selected.length-1);		
			$('#delivery_ids_selected').val(delivery_id_selected);
		}		
				
		return true;
	});
	
	calcola_importo_originale_totale();
	calcola_importo_scontato_totale();

});
</script>