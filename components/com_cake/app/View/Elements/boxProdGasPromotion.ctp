<?php
/*
echo "<pre>";
print_r($results);
echo "</pre>";
*/


if(!empty($results)) {
	echo '<div class="legenda legenda-ico-info">';
	echo '<h2 class="ico-bookmarkes-articles">'.__('ProdGasPromotion').'</h2>';
	
	if(!empty($results['ProdGasPromotion']['nota'])) {
		echo '<p style="padding-left: 45px;background-color:#fff;" ';
		echo 'class="nota_evidenza_'.strtolower($results['ProdGasPromotion']['nota_evidenza']).'"';
		echo '>';
		echo $results['ProdGasPromotion']['nota'];
		echo '</p>';
	}
	
	echo '<table cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '<th colspan="2">'.__('Supplier').'</th>';
	echo '<th>'.__('Name').'</th>';
	echo '<th>'.__('Data fine max').'</th>';
	echo '<th>'.__('importo_scontato').'</th>';	
	echo '<th>'.__('Trasport').'</th>';	
	echo '<th>'.__('CostMore').'</th>';			
	echo '</tr>';
	
	echo '<tr class="view-2">';
		
	echo '<td>';
	if(!empty($results['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$results['Supplier']['img1']))
		echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$results['Supplier']['img1'].'" />';	
	echo '</td>';
	echo '<td>';
	echo $results['Supplier']['name'];
	echo '</td>';
	echo '<td>';
	echo $results['ProdGasPromotion']['name'];
	echo '</td>';
	echo '<td>';
	echo $this->Time->i18nFormat($results['ProdGasPromotion']['data_fine'],"%A %e %B %Y");
	echo '</td>';
	echo '<td>';
	echo '<span style="text-decoration: line-through;">'.$results['ProdGasPromotion']['importo_originale_e'].'</span><br />'.$results['ProdGasPromotion']['importo_scontato_e'];
	echo '</td>';
	echo '<td>';
	if($results['ProdGasPromotionsOrganization']['hasTrasport']=='Y')	
		echo $results['ProdGasPromotionsOrganization']['trasport_e'];
	else
		echo "Nessun costo di trasporto";
	echo '</td>';
	echo '<td>';
	if($results['ProdGasPromotionsOrganization']['hasCostMore']=='Y')	
		echo $results['ProdGasPromotionsOrganization']['cost_more_e'];
	else
		echo "Nessun costo agguntivo";
	echo '</td>';	
	echo '</tr>';
	echo '</table>'; 
	
	/* 
	 * articoli in promozione
	 */
	 if(isset($results['ProdGasArticlesPromotion'])) {
		 echo '<h2 style="cursor:pointer;" class="ico-orders" id="dati_articles_header">'.__('ProdGasArticlesPromotions').'</h2>';
		 echo '<div id="dati_articles" style="display:none;">';
		 echo '<table cellpadding="0" cellspacing="0">';	
		 echo '<tr>';	
		 echo '<th colspan="2">'.__('Name').'</th>';	
		 echo '<th>'.__('confezione').'</th>';	
		 echo '<th>'.__('PrezzoUnita').'</th>';		
		 echo '<th>'.__('qta_in_promozione').'</th>';	
		 echo '<th>'.__('prezzo_unita_in_promozione').'</th>';
		 echo '<th>'.__('importo_originale').'</th>';	
		 echo '<th>'.__('importo_scontato').'</th>';	
		 echo '</tr>';
		 foreach ($results['ProdGasArticlesPromotion'] as $numResult => $prodGasArticlesPromotion):
			
			$importo_originale = ($prodGasArticlesPromotion['ProdGasArticlesPromotion']['qta'] * $prodGasArticlesPromotion['ProdGasArticle']['prezzo']);
			$importo_originale = number_format($importo_originale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			
			echo '<tr class="view">';
			echo '<td>';
			if(!empty($prodGasArticlesPromotion['ProdGasArticle']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$prodGasArticlesPromotion['ProdGasArticle']['supplier_id'].DS.$prodGasArticlesPromotion['ProdGasArticle']['img1'])) {
				echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$prodGasArticlesPromotion['ProdGasArticle']['supplier_id'].'/'.$prodGasArticlesPromotion['ProdGasArticle']['img1'].'" />';
			}		
			echo '</td>';			
			echo '<td>'.$prodGasArticlesPromotion['ProdGasArticle']['name'].'&nbsp;';
			echo $this->App->drawArticleNota($i, strip_tags($prodGasArticlesPromotion['ProdGasArticle']['nota']));
			echo '</td>';
			echo '<td>'.$this->App->getArticleConf($prodGasArticlesPromotion['ProdGasArticle']['qta'], $prodGasArticlesPromotion['ProdGasArticle']['um']).'</td>';
			echo '<td>'.$prodGasArticlesPromotion['ProdGasArticle']['prezzo_e'].'</td>';
			echo '<td style="text-align:center;">'.$prodGasArticlesPromotion['ProdGasArticlesPromotion']['qta'].'</td>';
			echo '<td>'.$prodGasArticlesPromotion['ProdGasArticlesPromotion']['prezzo_unita_e'].'</td>';
			echo '<td>'.$importo_originale.' &euro;</td>';
			echo '<td>'.$prodGasArticlesPromotion['ProdGasArticlesPromotion']['importo_e'].'</td>';
			echo '</tr>';
						
		endforeach;
		
		echo '</table>';
		 
		echo '</div>';	
	 } // end isset($results['ProdGasArticlesPromotion'])	 
	 	 
	echo '</div>';
}
?>
<script>
jQuery(document).ready(function() {
	jQuery('#dati_articles_header').click(function() {
		jQuery("#dati_articles").toggle('slow');
	});
});
</script>