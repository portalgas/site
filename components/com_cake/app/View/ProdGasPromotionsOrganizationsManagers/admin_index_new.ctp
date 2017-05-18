<?php
/*
echo "<pre>";
print_r($results);
echo "</pre>";
*/
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List ProdGasPromotions New'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="prodgaspromotion">';
echo '<h2 class="ico-bookmarkes-articles">';		
echo __('List ProdGasPromotions New');
echo '</h2>';


/*
 * promozioni da associare ad un ordine di GAS
 */
if(!empty($results)) {

	echo '<table cellpadding="0" cellspacing="0">';
	
	foreach ($results as $numResult => $result) {
		echo '<tr>';
		echo '	<td colspan="2" ';
		if(!empty($result['ProdGasPromotion']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$result['ProdGasPromotion']['supplier_id'].DS.$result['ProdGasPromotion']['img1'])) {
			echo 'style="background:url(\''.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_promotions').'/'.$result['ProdGasPromotion']['supplier_id'].'/'.$result['ProdGasPromotion']['img1'].'\') no-repeat 0 0;height: 150px;"';
		}
		echo '>';
		echo '<div class="promotion_title">'.$result['ProdGasPromotion']['name'].'</div>';
		echo '<div class="promotion_date">termina il '.$this->Time->i18nFormat($result['ProdGasPromotion']['data_fine'],"%A %e %B %Y").'</div>';
		echo '	</td>';				
		echo '<td class="actions-table-img">';
		echo $this->Html->link(null, array('action' => 'add', null, 'supplier_id='.$result['ProdGasPromotion']['supplier_id'].'&prod_gas_promotion_id='.$result['ProdGasPromotion']['id']), array('class' => 'action actionAdd','title' => __('Add PromotionOrganizationManager')));
		echo '</td>';
		echo '</tr>';

		echo '<tr class="view">';
		echo '	<td></td>';	
		echo '	<th>'.__('Articles').'</th>';
		echo '	<th>'.__('importo_scontato').'</th>';				
		echo '</tr>';
		
		/*
		 * articoli in promozione
		 */
		if(isset($result['ProdGasArticles'])) {

			echo '<tr class="view">';
			echo '	<td>';	
			echo '	</td>';	
			echo '	<td>';	
			
			echo '<table cellpadding="0" cellspacing="0">';
			foreach($result['ProdGasArticles'] as $numResult2 => $prodGasArticle) {
				$sub_totale_originale = ($prodGasArticle['ProdGasArticlesPromotion']['qta'] * $prodGasArticle['ProdGasArticle']['prezzo']);  // prezzo_originale
				$sub_totale_scontato = ($prodGasArticle['ProdGasArticlesPromotion']['qta'] * $prodGasArticle['ProdGasArticlesPromotion']['prezzo_unita']);  // prezzo_scontato
				
				echo '<tr>';
				echo '<td>';
				if(!empty($prodGasArticle['ProdGasArticle']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$prodGasArticle['ProdGasArticle']['supplier_id'].DS.$prodGasArticle['ProdGasArticle']['img1'])) {
					echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$prodGasArticle['ProdGasArticle']['supplier_id'].'/'.$prodGasArticle['ProdGasArticle']['img1'].'" />';
				}		
				echo '</td>';				
				echo '<td width="20%">';
				echo '<b>'.$prodGasArticle['ProdGasArticlesPromotion']['qta'].'</b> di ';
				echo $this->App->getArticleConf($prodGasArticle['ProdGasArticle']['qta'], $prodGasArticle['ProdGasArticle']['um']);
				echo '</td>';
				echo '<td>';
				echo $prodGasArticle['ProdGasArticle']['name'].' per un totale di <b>'.number_format($sub_totale_originale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'</b> &euro;,';
				echo '</td>';
				echo '<td width="20%">';
				echo 'scontato a <b>'.number_format($sub_totale_scontato,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'</b> &euro;';
				echo '</td>';
				echo '</tr>';
			}
			echo '</table>';
		}
		echo '</td>';	
		
		
		echo '	<td>';
		echo '<span style="text-decoration: line-through;">'.$result['ProdGasPromotion']['importo_originale_e'].'</span><br />'.$result['ProdGasPromotion']['importo_scontato_e'].'<br />';		
		if($result['ProdGasPromotionsOrganizationsManager']['trasport']!='0.00')
			echo ' + '.$result['ProdGasPromotionsOrganizationsManager']['trasport_e'].' '.__('Trasport');
		if($result['ProdGasPromotionsOrganizationsManager']['cost_more']!='0.00')
			echo ' + '.$result['ProdGasPromotionsOrganizationsManager']['cost_more_e'].' '.__('CostMore');
		echo '	</td>';	
			
		echo '	</tr>';	
	}
	echo '</table>';
}	 
else  
	echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFonud', 'msg' => "Non ci sono ancora nuove promozioni"));

echo '</div>';
?>
<script type="text/javascript">
jQuery(document).ready(function() {

});
</script>
<style>
.promotion_title {
	font-size:22px;
	margin-left:30%
}
.promotion_date {
	font-size:18px;
	margin-left:30%

}
</style>