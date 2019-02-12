<?php
App::uses('AppController', 'Controller');

class ProdGasMonitoringArticlesController extends AppController {
						
	private $userOrganization; // ottengo i dati del GAS, x es per sapere se Organization.hasDes
	private $organizationsResults;
		
	public function beforeFilter() {
		parent::beforeFilter();
		
		/* ctrl ACL */
		if($this->user->organization['Organization']['type']!='PRODGAS') {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}

		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;
			
		/*
		 * se il produttore non ha neppure un GAS owner_articles = 'SUPPLIER' lo blocco
		 */
		$this->organizationsResults = $ProdGasSupplier->getOrganizationsArticlesSupplierList($this->user, false);
		if(empty($this->organizationsResults)) {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}		
	}
	
	/*
	 * creo array con tutti gli articoli del produttore e li confronto con articoli gas e articoli ordinati del gas
	 */
	public function admin_index() { 
	
		$debug = false;

		App::import('Model', 'Article');
		$Article = new Article;

		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;

		App::import('Model', 'ProdGasArticle');
		$ProdGasArticle = new ProdGasArticle;

		$options = [];
		$options['conditions'] = ['ProdGasArticle.supplier_id' => $this->user->organization['Supplier']['Supplier']['id']];
		
		// $options['conditions'] += ['ProdGasArticle.id' => 62];
		
		$options['order'] = ['ProdGasArticle.name'];
		$options['recursive'] = -1;
		$prodGasArticlesResults = $ProdGasArticle->find('all', $options);	

		$newResults = [];
		foreach($this->organizationsResults as $organization_id => $organizationName) {

			if($debug)
				echo '<br />Tratto li GAS ('.$organization_id.') '.$organizationName;
			
			$newResults[$organization_id]['Organization']['id'] = $organization_id;
			$newResults[$organization_id]['Organization']['name'] = $this->organizationsResults[$organization_id]; 
			
			foreach($prodGasArticlesResults as $prodGasArticlesResult) {
					
				$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['Esito']['article_found'] = true;
				$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['Esito'][0]['articles_order_found'] = true;
				$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['Esito']['syncronize_article'] = true;	
				$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['Esito'][0]['syncronize_articles_order'] = true;	
				
				/*
				 * Article del GAS
				 */												
				$options = [];
				$options['conditions'] = ['Article.organization_id' => $organization_id,
										  'Article.prod_gas_article_id' => $prodGasArticlesResult['ProdGasArticle']['id'],
										  'Article.supplier_id' => $this->user->organization['Supplier']['Supplier']['id']];
				$options['recursive'] = -1;
				$articlesResults = $Article->find('first', $options);
				/*
				if($debug) {
	 				echo "<pre>";
					print_r($options);
					print_r($articlesResults);
					echo "</pre>";
				}
				*/
				if(!empty($articlesResults['Article'])) {
					
					$articles_order_found = true;	
					$syncronize_article = true;
					
					if($debug)
						echo '<br />Articolo '.$articlesResults['Article']['name'].' (id '.$articlesResults['Article']['id'].' - prod_gas_article_id '.$articlesResults['Article']['prod_gas_article_id'].') <b>SI</b> trovato, ctrl se sincronizzato ';					
						
					/*
					 * verifico se ci sono differenze tra articoli 
					 */
					if($prodGasArticlesResult['ProdGasArticle']['name']!=$articlesResults['Article']['name']) {
						if($debug)
							echo '<br />'.$i.') '.$prodGasArticlesResult['ProdGasArticle']['name'].' differente con Article GAS';
			
						$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['Article']['Diff']['name'] = $articlesResults['Article']['name'];
						
						$syncronize_article = false;					
					}  
					
					if($prodGasArticlesResult['ProdGasArticle']['prezzo']!=$articlesResults['Article']['prezzo']) {
						if($debug)
							echo '<br />'.$i.') '.$prodGasArticlesResult['ProdGasArticle']['name'].' differente con Article GAS';
			
						$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['Article']['Diff']['prezzo_e'] = $articlesResults['Article']['prezzo_e'];					

						$syncronize_article = false;					
					}  
					
					if($prodGasArticlesResult['ProdGasArticle']['qta']!=$articlesResults['Article']['qta']) {
						if($debug)
							echo '<br />'.$i.') '.$prodGasArticlesResult['ProdGasArticle']['name'].' differente con Article GAS';
			
						$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['Article']['Diff']['qta'] = $articlesResults['Article']['qta'];					

						$syncronize_article = false;					
					}  
					
					if($prodGasArticlesResult['ProdGasArticle']['um']!=$articlesResults['Article']['um']) {
						if($debug)
							echo '<br />'.$i.') '.$prodGasArticlesResult['ProdGasArticle']['name'].' differente con Article GAS';
			
						$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['Article']['Diff']['um'] = $articlesResults['Article']['um'];						

						$syncronize_article = false;					
					}  
					
					if($prodGasArticlesResult['ProdGasArticle']['pezzi_confezione']!=$articlesResults['Article']['pezzi_confezione']) {
						if($debug)
							echo '<br />'.$i.') '.$prodGasArticlesResult['ProdGasArticle']['name'].' differente con Article GAS';
			
						$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['Article']['Diff']['pezzi_confezione'] = $articlesResults['Article']['pezzi_confezione'];						

						$syncronize_article = false;					
					}  
					
					if($prodGasArticlesResult['ProdGasArticle']['qta_minima']!=$articlesResults['Article']['qta_minima']) {
						if($debug)
							echo '<br />'.$i.') '.$prodGasArticlesResult['ProdGasArticle']['name'].' differente con Article GAS';
			
						$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['Article']['Diff']['qta_minima'] = $articlesResults['Article']['qta_minima'];						

						$syncronize_article = false;					
					}  
					
					if($prodGasArticlesResult['ProdGasArticle']['qta_multipli']!=$articlesResults['Article']['qta_multipli']) {
						if($debug)
							echo '<br />'.$i.') '.$prodGasArticlesResult['ProdGasArticle']['name'].' differente con Article GAS';
			
						$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['Article']['Diff']['qta_multipli'] = $articlesResults['Article']['qta_multipli'];						

						$syncronize_article = false;					
					}  
				   		   
					/*
				     * ArticlesOrders
					 */
					$options = [];
					$options['conditions'] = ['ArticlesOrder.organization_id' => $articlesResults['Article']['organization_id'],
											   'ArticlesOrder.article_id' => $articlesResults['Article']['id'],
											   'ArticlesOrder.stato != ' => 'N',
											   'Order.state_code' => ['OPEN', 'OPEN-NEXT', 'RI-OPEN-VALIDATE', 'PROCESSED-BEFORE-DELIVERY', 'PROCESSED-POST-DELIVERY', 'INCOMING-ORDER']];
					$options['recursive'] = 0;
					$ArticlesOrder->unbindModel(array('belongsTo' => array('Cart', 'Article')));
					$articlesOrderResults = $ArticlesOrder->find('all', $options);
					/*
					echo "<pre>";
					print_r($options);
					print_r($articlesOrderResults);
					echo "</pre>";
					*/
					if(!empty($articlesOrderResults)) {
										
						$i=0;
						foreach($articlesOrderResults as $numResult => $articlesOrderResult) {
							
							$articles_order_found = true;
							$syncronize_articles_order = true;
							
							if($debug)
								echo '<br />Articolo '.$articlesResults['Article']['name'].' (id '.$articlesResults['Article']['id'].' - prod_gas_article_id '.$articlesResults['Article']['prod_gas_article_id'].') <b>SI</b> in ArticlesOrder, ctrl se synconizzati ';	
							
							/*
							 * verifico se ci sono differenze tra articoli 
							 */
							if($prodGasArticlesResult['ProdGasArticle']['name']!=$articlesOrderResult['ArticlesOrder']['name']) {
								
								if($debug)
									echo '<br />'.$i.') '.$prodGasArticlesResult['ProdGasArticle']['name'].' differente con ArticlesOrder.name GAS ordine '.$articlesOrderResult['Order']['id'];
						
								$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['ProdGasArticle'] = $prodGasArticlesResult['ProdGasArticle'];
								$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['ArticlesOrder'][$i]['Diff']['name'] = $articlesOrderResult['ArticlesOrder']['name'];	
	
								$syncronize_articles_order = false;
							}
							
							if($prodGasArticlesResult['ProdGasArticle']['prezzo']!=$articlesOrderResult['ArticlesOrder']['prezzo']) {
								
								if($debug)
									echo '<br />'.$i.') '.$prodGasArticlesResult['ProdGasArticle']['name'].' differente con ArticlesOrder.prezzo GAS ordine '.$articlesOrderResult['Order']['id'];
						
								$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['ProdGasArticle'] = $prodGasArticlesResult['ProdGasArticle'];
								$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['ArticlesOrder'][$i]['Diff']['prezzo_e'] = $articlesOrderResult['ArticlesOrder']['prezzo_e'];
	
								$syncronize_articles_order = false;
							}
							
							if($prodGasArticlesResult['ProdGasArticle']['pezzi_confezione']!=$articlesOrderResult['ArticlesOrder']['pezzi_confezione']) {
								
								if($debug)
									echo '<br />'.$i.') '.$prodGasArticlesResult['ProdGasArticle']['name'].' differente con ArticlesOrder.pezzi_confezione GAS ordine '.$articlesOrderResult['Order']['id'];
						
								$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['ProdGasArticle'] = $prodGasArticlesResult['ProdGasArticle'];
								$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['ArticlesOrder'][$i]['Diff']['pezzi_confezione'] = $articlesOrderResult['ArticlesOrder']['pezzi_confezione'];
	
								$syncronize_articles_order = false;
							}
							
							if($prodGasArticlesResult['ProdGasArticle']['qta_minima']!=$articlesOrderResult['ArticlesOrder']['qta_minima']) {
								
								if($debug)
									echo '<br />'.$i.') '.$prodGasArticlesResult['ProdGasArticle']['name'].' differente con ArticlesOrder.qta_minima GAS ordine '.$articlesOrderResult['Order']['id'];
						
								$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['ProdGasArticle'] = $prodGasArticlesResult['ProdGasArticle'];
								$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['ArticlesOrder'][$i]['Diff']['qta_minima'] = $articlesOrderResult['ArticlesOrder']['qta_minima'];
								
								$syncronize_articles_order = false;
							}
							
							if($prodGasArticlesResult['ProdGasArticle']['qta_multipli']!=$articlesOrderResult['ArticlesOrder']['qta_multipli']) {
								
								if($debug)
									echo '<br />'.$i.') '.$prodGasArticlesResult['ProdGasArticle']['name'].' differente con ArticlesOrder.qta_multipli GAS ordine '.$articlesOrderResult['Order']['id'];
						
								$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['ProdGasArticle'] = $prodGasArticlesResult['ProdGasArticle'];
								$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['ArticlesOrder'][$i]['Diff']['qta_multipli'] = $articlesOrderResult['ArticlesOrder']['qta_multipli'];
								
								$syncronize_articles_order = false;
							}
						   								
							if($articles_order_found && !$syncronize_articles_order) {
									
								if($debug)
									echo '<br />Articolo '.$articlesResults['Article']['name'].' (id '.$articlesResults['Article']['id'].' - prod_gas_article_id '.$articlesResults['Article']['prod_gas_article_id'].') <b>SI</b> in ArticlesOrder, <b>NO</b> sincronizzato ';	
							
								$options = [];
								$options['conditions'] = ['Delivery.organization_id' => $articlesOrderResult['Order']['organization_id'],
														   'Delivery.id' => $articlesOrderResult['Order']['delivery_id']];
								$options['recursive'] = 0;								
								$deliveryResults = $Delivery->find('first', $options);

								$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']][$i]['syncronize_articles_order'] = false;	
								
								$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['ProdGasArticle'] = $prodGasArticlesResult['ProdGasArticle'];	
								$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['Article']['name'] = $articlesResults['Article']['name'];	
								$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['Article']['id'] = $articlesResults['Article']['id'];			
								$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['Article']['organization_id'] = $articlesResults['Article']['organization_id'];	
								
								$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['ArticlesOrder'][$i]['Order'] = $articlesOrderResult['Order'];
								$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['ArticlesOrder'][$i]['Delivery'] = $deliveryResults['Delivery'];
								
								$i++;	
							}
						} // loop foreach($articlesOrderResults as $numResult => $articlesOrderResult)
					} // if(!empty$articlesOrderResults))
					else {
						if($debug)
							echo '<br />Articolo '.$articlesResults['Article']['name'].' (id '.$articlesResults['Article']['id'].' - prod_gas_article_id '.$articlesResults['Article']['prod_gas_article_id'].') <b>NO</b> in ArticlesOrder ';		
	
					    $newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['Esito'][0]['articles_order_found'] = false;				
					}
					
					if($article_found && !$syncronize_article) {
						
						if($debug)
							echo '<br />Articolo '.$articlesResults['Article']['name'].' (id '.$articlesResults['Article']['id'].' - prod_gas_article_id '.$articlesResults['Article']['prod_gas_article_id'].') <b>SI</b> trovato, <b>No</b> sincronizzato ';	
								
						$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['Esito']['syncronize_article'] = false;	
						
						$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['ProdGasArticle'] = $prodGasArticlesResult['ProdGasArticle'];	
						$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['Article']['name'] = $articlesResults['Article']['name'];	
						$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['Article']['id'] = $articlesResults['Article']['id'];			
						$newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['Article']['organization_id'] = $articlesResults['Article']['organization_id'];		
				   	}
					else {
						if($debug)
							echo '<br />Articolo '.$articlesResults['Article']['name'].' (id '.$articlesResults['Article']['id'].' - prod_gas_article_id '.$articlesResults['Article']['prod_gas_article_id'].') trovato, SI sincronizzato ';							
					}
				   						
				} // end if(empty($articlesResults['Article']))
				else {
				   $newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['ProdGasArticle']['ProdGasArticle'] = $prodGasArticlesResult['ProdGasArticle'];
				   $newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['Esito']['article_found'] = false;
				   $newResults[$organization_id][$prodGasArticlesResult['ProdGasArticle']['id']]['Esito'][0]['articles_order_found'] = false;
				}
				   
			} // loop ProdGasArticle	
		}	// loop Organizations
		
		if($debug) {
			echo "<pre>";
			print_r($newResults);
			echo "</pre>";
		}
			
		$this->set('results', $newResults);
	}	
}