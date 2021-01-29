<?php
$this->App->d($this->request->data, $debug);
$this->App->d($articleResults, $debug);
$this->App->d($organizationResults, $debug);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index'));
$this->Html->addCrumb(__('View ProdGasPromotion'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="orders form">';

echo $this->Form->create('ProdGasPromotion');
echo '<fieldset>';

	echo '<legend>'.__('View ProdGasPromotion').'</legend>';

	echo '<div class="tabs">';
	echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
	echo '<li class="active"><a href="#tabs-0" data-toggle="tab">'.__('ProdGasPromotionDati').'</a></li>';
	echo '<li><a href="#tabs-1" data-toggle="tab">'.__('ProdGasArticlesInPromotion').'</a></li>';
	echo '<li><a href="#tabs-2" data-toggle="tab">'.__('ProdGasPromotionOrganizationsAssociate').'</a></li>';		
	echo '</ul>';

	echo '<div class="tab-content">';
	echo '<div class="tab-pane fade active in" id="tabs-0">';

	echo '<div class="input text ">';
	echo '<label>'.__('Name').'</label> ';
	echo $this->Form->value('ProdGasPromotion.name');
	echo '</div>';
	
	echo '<div class="input text ">';
	echo '<label>'.__('DataInizio').'</label> ';
	echo $this->Time->i18nFormat($this->Form->value('ProdGasPromotion.data_inizio'),"%A, %e %B %Y");
	echo '</div>';

	echo '<div class="input text ">';
	echo '<label>'.__('DataFine').'</label> ';
	echo $this->Time->i18nFormat($this->Form->value('ProdGasPromotion.data_fine'),"%A, %e %B %Y");
	echo '</div>';
	
	echo '<div class="input text ">';
	echo '<label>'.__('Nota da aggiungere all\'ordine').'</label> ';
	echo '<p>'.$this->Form->value('ProdGasPromotion.nota').'</p>';
	echo '</div>';
	
	echo '<div class="input text ">';
	echo '<label>'.__('Name').'</label> ';
	echo $this->Form->value('ProdGasPromotion.contact_name');
	echo '</div>';

	echo '<div class="input text ">';
	echo '<label>'.__('Email').'</label> ';
	echo $this->Form->value('ProdGasPromotion.contact_mail');
	echo '</div>';
	
	echo '<div class="input text ">';
	echo '<label>'.__('Telephone').'</label> ';
	echo $this->Form->value('ProdGasPromotion.contact_phone');
	echo '</div>';
				
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
			
			echo '<tr class="view">';
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
			echo '<td style="text-align:center;">';
			echo $this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);
			echo '</td>';
			echo '<td style="text-align:center;">';
			echo $result['ProdGasArticlesPromotion']['qta'];
			echo '</td>';
			echo '<td style="text-align:center;">';
			echo $result['Article']['prezzo_e'];
			echo '</td>';
			echo '<td style="text-align:center;">';
			echo $result['ProdGasArticlesPromotion']['prezzo_unita'].'&nbsp;&euro;';
			echo '</td>';
			echo '<td style="text-align:center;">';
			echo $importo_originale_.'&nbsp;&euro;';
			echo '</td>';
			echo '<td style="text-align:center;">';
			echo $result['ProdGasArticlesPromotion']['importo_'].'&nbsp;&euro;';
			echo '</td>';			
			echo '</tr>';
			
		} // loops articleResults

	/*
	 * totali
	 */
	echo '<tr>';
	echo '<td colspan="7"></td>';
	echo '<td>';
	echo '</td>';
	echo '<td>';
	echo '</td>';
	echo '<td></td>';	 
	echo '</tr>';
	
	echo '</tbody></table></div>';
	
	}
	else
		echo $this->element('boxMsg', ['class_msg' => 'danger', 'msg' => __('msg_prodgas_articles_not_found')]);

/*
 * Gas già Associati
 */
echo '</div>';
echo '<div class="tab-pane fade" id="tabs-2">';


	if(!empty($organizationResults)) {

	echo '<div class="table-responsive"><table class="table table-hover table-striped">';
		echo '<tr>';
		echo '<th colspan="2">'.__('Name').'</th>';
		echo '<th>'.__('Trasport').'</th>';
		echo '<th>'.__('CostMore').'</th>';
		echo '<th>'.__('ProdGasSupplierDeliveriesBooking').'</th>';
		echo '</tr>';
		
		foreach ($organizationResults as $numResult => $result) {
		
			if(isset($result['ProdGasPromotionsOrganization'])) {
				$trasport = $result['ProdGasPromotionsOrganization']['trasport_'];
				$costMore = $result['ProdGasPromotionsOrganization']['cost_more_'];
			
				echo '<tr class="view">';
				
				echo '<td>';
				echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" /> ';	
				echo '</td>';
				
				echo '<td>';
					echo $result['Organization']['name']; 
					if(!empty($result['Organization']['descrizione'])) echo '<div class="small">'.$result['Organization']['descrizione'].'</div>';
				echo '</td>';

				echo '<td style="white-space: nowrap;text-align:center;"">';
				if(isset($result['Delivery']) && !empty($result['Delivery']))
					echo $trasport.'&nbsp;&euro;';
				echo '</td>';
				echo '<td style="white-space: nowrap;text-align:center;">';
				if(isset($result['Delivery']) && !empty($result['Delivery']))
					echo $costMore.'&nbsp;&euro;';
				echo '</td>';
				
				echo '<td>';
				if(isset($result['Delivery']) && !empty($result['Delivery'])) {
					foreach($result['Delivery'] as $delivery_id => $delivery_name) {
						
						if(isset($result['ProdGasPromotionsOrganizationsDelivery']))
						foreach($result['ProdGasPromotionsOrganizationsDelivery'] as $numResult => $prodGasPromotionsOrganizationsDelivery) {
							if($delivery_id==$prodGasPromotionsOrganizationsDelivery['ProdGasPromotionsOrganizationsDelivery']['delivery_id']) {
								
								echo $delivery_name.'<br />';

								unset($result['ProdGasPromotionsOrganizationsDelivery'][$numResult]);
								break;
							}
						}
					}
				}
				else
				if($result['SuppliersOrganization']['can_promotions']=='N')
					echo '<span class="label label-warning">Non abilitato alle promozioni</span>';
				else
				if(!isset($result['Delivery']) || empty($result['Delivery'])) 
				 	echo '<span class="label label-warning">Il G.A.S. non ha consegne aperte</span>';				
				echo '</td>';
				echo '</tr>';
			} // end if(isset($result['ProdGasPromotionsOrganization']))				
		}		
		echo '</table></div>';
	}
	else
		echo $this->element('boxMsg', ['class_msg' => 'danger', 'msg' => __('msg_search_not_result')]);

echo '</div>'; // tab-content
echo '</div>';
echo '</fieldset>';

echo $this->Form->end();

echo '</div>';

echo '<div class="actions">';
echo '<h3>'.__('Actions').'</h3>';
echo '<ul>';
echo '<li>'.$this->Html->link(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index'),array('class'=>'action actionReload')).'</li>';
echo '</ul>';
echo '</div>';
?>