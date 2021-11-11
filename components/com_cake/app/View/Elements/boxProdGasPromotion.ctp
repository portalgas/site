<?php
$this->App->d($results);

if(!isset($prodGasArticlesPromotionShow))
	$prodGasArticlesPromotionShow = false;

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
	
	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '<th colspan="2">'.__('Supplier').'</th>';
	echo '<th>'.__('Name').'</th>';
	echo '<th>'.__('DataFineMax').'</th>';
	echo '<th>'.__('Importo_scontato').'</th>';	
	echo '<th>'.__('Trasport').'</th>';	
	echo '<th>'.__('CostMore').'</th>';			
	echo '</tr>';
	
	echo '<tr class="view-2">';
		
	echo '<td>';
	if(!empty($results['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$results['Supplier']['img1']))
		echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$results['Supplier']['img1'].'" />';	
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
	echo '</table></div>'; 
	
	/* 
	 * articoli in promozione
	 */
	 if(isset($results['ProdGasArticlesPromotion'])) {
		 echo '<h2 style="cursor:pointer;" class="ico-orders" id="dati_articles_header">'.__('ProdGasArticlesPromotions').'</h2>';
		 echo '<div id="dati_articles" style="display:none;">';
		 
		 echo '<div class="table-responsive"><table class="table table-hover">';	
		 echo '<tr>';	
		 echo '<th colspan="2">'.__('Name').'</th>';	
		 echo '<th>'.__('Package').'</th>';	
		 echo '<th style="text-align:center;">'.__('qta_in_promozione').'</th>';	
		 echo '<th style="text-align:center;">'.__('PrezzoUnita').'</th>';		
		 // echo '<th style="text-align:center;">'.__('Importo_originale').'</th>';	
		 echo '<th style="text-align:center;">'.__('prezzo_unita_in_promozione').'</th>';
		 echo '<th style="text-align:center;">'.__('Importo_totale_scontato').'</th>';	
		 echo '</tr>';
		 
		 foreach ($results['ProdGasArticlesPromotion'] as $numResult => $prodGasArticlesPromotion) {
			
			$importo_originale = ($prodGasArticlesPromotion['ProdGasArticlesPromotion']['qta'] * $prodGasArticlesPromotion['Article']['prezzo']);
			$importo_originale = number_format($importo_originale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			
			echo '<tr class="view">';
			echo '<td>';
			if(!empty($prodGasArticlesPromotion['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$prodGasArticlesPromotion['Article']['organization_id'].DS.$prodGasArticlesPromotion['Article']['img1'])) {
				echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$prodGasArticlesPromotion['Article']['organization_id'].'/'.$prodGasArticlesPromotion['Article']['img1'].'" />';
			}		
			echo '</td>';			
			echo '<td>'.$prodGasArticlesPromotion['Article']['name'].'&nbsp;';
			echo $this->App->drawArticleNota($i, strip_tags($prodGasArticlesPromotion['Article']['nota']));
			echo '</td>';
			echo '<td style="text-align:center;">'.$this->App->getArticleConf($prodGasArticlesPromotion['Article']['qta'], $prodGasArticlesPromotion['Article']['um']).'</td>';
			echo '<td style="text-align:center;">'.$prodGasArticlesPromotion['ProdGasArticlesPromotion']['qta'].'</td>';
			echo '<td style="text-align:center;"><span style="text-decoration: line-through;">'.$prodGasArticlesPromotion['Article']['prezzo_e'].'</span></td>';
			// echo '<td style="text-align:center;"><span style="text-decoration: line-through;">'.$importo_originale.'&nbsp;&euro;</span></td>';
			echo '<td style="text-align:center;">'.$prodGasArticlesPromotion['ProdGasArticlesPromotion']['prezzo_unita_e'].'</td>';
			echo '<td style="text-align:center;">';
			echo '<span style="text-decoration: line-through;">'.$importo_originale.'&nbsp;&euro;</span><br />';
			echo $prodGasArticlesPromotion['ProdGasArticlesPromotion']['importo_e'].'</td>';
			echo '</tr>';
						
		}
		
		echo '</table></div>';
		 
		echo '</div>';	
	 } // end isset($results['ProdGasArticlesPromotion'])	 
	 	 
	echo '</div>';
	echo '<div class="clearfix"></div>';
}
?>
<script>
$(document).ready(function() {
	$('#dati_articles_header').click(function() {
		$("#dati_articles").toggle('slow');
	});
	
	<?php 
	if($prodGasArticlesPromotionShow)
		echo '$("#dati_articles").toggle();';
	?>
});
</script>