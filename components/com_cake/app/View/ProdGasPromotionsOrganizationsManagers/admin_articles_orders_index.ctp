<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
$this->Html->addCrumb(__('View ProdGasPromotion ArticlesOrder Short'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="orders form">';

echo $this->Form->create('ProdGasPromotionsOrganizationsManager',array('id' => 'formGas'));
echo '<fieldset>';

echo '<legend>'.__('View ProdGasPromotion ArticlesOrder Short').'</legend>';
	
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
		 echo '<div id="dati_articles">';
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
	 
echo '</fieldset>';

echo $this->Form->end();

echo '</div>';

echo '<div class="actions">';
echo '<h3>'.__('Actions').'</h3>';
echo '<ul>';
echo '<li>'.$this->Html->link(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index'),array('class'=>'action actionReload')).'</li>';
echo '<li>'.$this->Html->link(__('Order home'), array('controller' => 'Orders', 'action' => 'home', $order_id),array('class'=>'action actionWorkflow')).'</li>';
echo '</ul>';
echo '</div>';
?>