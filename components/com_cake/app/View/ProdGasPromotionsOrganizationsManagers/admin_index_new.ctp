<?php
$this->App->d($results);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
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

	foreach ($results as $numResult => $result) {
		
		echo '<div class="table-responsive"><table class="table">';
		echo '<tr>';
		echo '<td>';
		if(!empty($result['ProdGasSupplier']['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['ProdGasSupplier']['Supplier']['img1']))
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['ProdGasSupplier']['Supplier']['img1'].'" />';	
		echo '</td>';
		echo '<td style="white-space: nowrap;">';
		echo $result['ProdGasSupplier']['Supplier']['name'];
		echo '</td>';		
		
		/*
		echo '	<td';
		if(!empty($result['ProdGasPromotion']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$result['ProdGasPromotion']['supplier_id'].DS.$result['ProdGasPromotion']['img1'])) {
			echo 'style="background:url(\''.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_promotions').'/'.$result['ProdGasPromotion']['supplier_id'].'/'.$result['ProdGasPromotion']['img1'].'\') no-repeat 0 0;height: 150px;"';
		}
		echo '>';
		*/
		echo '<td>';
		echo '<div class="promotion_title">'.$result['ProdGasPromotion']['name'].'</div>';
		echo '<div class="promotion_date">termina il '.$this->Time->i18nFormat($result['ProdGasPromotion']['data_fine'],"%A %e %B %Y").'</div>';
		echo '</td>';
		
		if($result['Acl']) {
			echo '<td>';	
			if(!empty($result['ProdGasPromotionsOrganizationsManager']['nota_supplier'])) {
				
				echo '<button type="button" class="btn btn-info" data-toggle="modal" data-target="#nota_supplier'.$result['ProdGasPromotionsOrganizationsManager']['id'].'"><i class="fa fa-2x fa-info-circle" aria-hidden="true"></i></button>';
				echo '<div id="nota_supplier'.$result['ProdGasPromotionsOrganizationsManager']['id'].'" class="modal fade" role="dialog">';
				echo '<div class="modal-dialog">';
				echo '<div class="modal-content">';
				echo '<div class="modal-header">';
				echo '<button type="button" class="close" data-dismiss="modal">&times;</button>';
				echo '<h4 class="modal-title">'.__('ProdGasPromotionNotaFromSupplier').'</h4>';
				echo '</div>';
				echo '<div class="modal-body"><p>'.$result['ProdGasPromotionsOrganizationsManager']['nota_supplier'].'</p>';
				echo '</div>';
				echo '<div class="modal-footer">';
				echo '<button type="button" class="btn btn-primary" data-dismiss="modal">'.__('Close').'</button>';
				echo '</div>';
				echo '</div>';
				echo '</div>';
				echo '</div>';			
				
			} // end if(!empty($result['nota_supplier']))
			echo '</td>';						
			echo '<td class="actions-table-img">';
			echo '<div class="actions-img">';		
			echo '	<ul>';
			echo '<li>'.$this->Html->link(__('Contact PromotionOrganizationManager'), ['action' => 'contact', null, 'prod_gas_promotion_id='.$result['ProdGasPromotion']['id']], ['class' => 'action actionPhone','title' => __('Contact PromotionOrganizationManager')]).'</li>';
			// echo '<li>'.$this->Html->link(__('Add PromotionOrganizationManager'), ['action' => 'add', null, 'prod_gas_promotion_id='.$result['ProdGasPromotion']['id']], ['class' => 'action actionAdd','title' => __('Add PromotionOrganizationManager')]).'</li>';
			$query = 'c_to=admin/orders&a_to=add&order_type_id='.Configure::read('Order.type.promotion').'&prod_gas_promotion_id='.$result['ProdGasPromotion']['id'];
			echo '<li>'.$this->Html->link(__('Add PromotionOrganizationManager'), ['controller' => 'Connects', 'action' => 'index', null, $query], ['class' => 'action actionAdd','title' => __('Add PromotionOrganizationManager')]).'</li>';
			echo '<li>'.$this->Html->link(__('Reject PromotionOrganizationManager'), ['action' => 'reject', null, 'prod_gas_promotion_id='.$result['ProdGasPromotion']['id']], ['class' => 'action actionDelete','title' => __('Reject PromotionOrganizationManager')]).'</li>';
			echo '	</ul>';
			echo '</div>';		
			echo '</td>';
		}
		else {
			echo '<td colspan="2">';
			echo $this->element('boxMsg', ['class_msg' => 'danger', 'msg' => __('msg_prodgas_organization_manager_no_acl')]);
			echo '</td>';
		}
		echo '</tr>';
		echo '</table></div>';

		
		 echo '<div class="table-responsive"><table class="table table-hover">';	
		 echo '<tr>';	
		 echo '<th colspan="2">'.__('Name').'</th>';	
		 echo '<th>'.__('Package').'</th>';	
		 echo '<th style="text-align:center;">'.__('prezzo_unita_in_promozione').'</th>';
		 echo '<th style="text-align:center;">'.__('qta_in_promozione').'</th>';
		 echo '<th style="text-align:center;">'.__('Importo_totale_scontato').'</th>';	
		 echo '</tr>';
		
		/*
		 * articoli in promozione
		 */
		if(isset($result['Article'])) {
			
			foreach($result['Article'] as $numResult2 => $prodGasArticlesPromotion) {
				$sub_totale_originale = ($prodGasArticlesPromotion['ProdGasArticlesPromotion']['qta'] * $prodGasArticlesPromotion['Article']['prezzo']);  // prezzo_originale
				$sub_totale_scontato = ($prodGasArticlesPromotion['ProdGasArticlesPromotion']['qta'] * $prodGasArticlesPromotion['ProdGasArticlesPromotion']['prezzo_unita']);  // prezzo_scontato
								
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
				
				echo '<td style="text-align:center;">';
				echo '<span style="text-decoration: line-through;">'.$prodGasArticlesPromotion['Article']['prezzo_e'].'</span><br />';
				echo $prodGasArticlesPromotion['ProdGasArticlesPromotion']['prezzo_unita_e'];
				echo '</td>';

				echo '<td style="text-align:center;">'.$prodGasArticlesPromotion['ProdGasArticlesPromotion']['qta'].'</td>';

				echo '<td style="text-align:center;">';
				echo '<span style="text-decoration: line-through;">'.$importo_originale.'&nbsp;&euro;</span><br />';
				echo $prodGasArticlesPromotion['ProdGasArticlesPromotion']['importo_e'];
				echo '</td>';
				echo '</tr>';

			} // end loop prodGasArticlesPromotion
		}
		
		echo '<tr>';
		echo '	<td></td>';
		echo '	<td></td>';
		echo '	<td></td>';
		echo '	<td></td>';
		echo '	<td></td>';
		echo '	<td style="text-align:center;">';
		echo '	<div>';
		echo '		<span style="text-decoration: line-through;">'.$result['ProdGasPromotion']['importo_originale_e'].'</span>';
		echo '	</div>';		
		echo $result['ProdGasPromotion']['importo_scontato_e'].'<br />';		
		if($result['ProdGasPromotionsOrganizationsManager']['trasport']!='0.00')
			echo ' + '.$result['ProdGasPromotionsOrganizationsManager']['trasport_e'].' '.__('Trasport');
		if($result['ProdGasPromotionsOrganizationsManager']['cost_more']!='0.00')
			echo ' + '.$result['ProdGasPromotionsOrganizationsManager']['cost_more_e'].' '.__('CostMore');
		echo '	</td>';	
		echo '	</tr>';		
		echo '</table></div>';		
	}
	
}	 
else  
	echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFound', 'msg' => "Non ci sono ancora nuove promozioni"));

echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {

});
</script>
<style>
.promotion_title {
	font-size:32px;
	margin-left:30%
}
.promotion_date {
	font-size:18px;
	margin-left:30%

}
</style>