<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class ProdGasPromotionsController extends AppController {

   public $components = array('RequestHandler','ActionsProdGasPromotions','Documents');
   public $helpers = array('Html', 'Javascript', 'Ajax');
    
   public function beforeFilter() {
   		parent::beforeFilter();
   		
		/* ctrl ACL */
		if(empty($this->user->supplier['Supplier'])) {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}	
		/* ctrl ACL */ 
   }

   public function admin_tabs_ajax_ecomm_articles_order() {
        App::import('Model', 'ProdGasArticlesPromotion');
        $ProdGasArticlesPromotion = new ProdGasArticlesPromotion;
		
		$options =  array();
		$options['conditions'] = array('ProdGasArticlesPromotion.supplier_id' => (int)$this->user->supplier['Supplier']['id']);
		$options['recursive'] = 1;
		$options['order'] = array('ProdGasArticle.name ASC');
		$results = $ProdGasArticlesPromotion->find('all', $options); 		
		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
		$type_draw='COMPLETE'; // SIMPLE
		
		$this->set('type_draw', $type_draw);
		$this->set('results', $results);	   
   }
   
   public function admin_index() {
		
		$SqlLimit = 50;
		$conditions[] = array('ProdGasPromotion.supplier_id' => $this->user->supplier['Supplier']['id']);

		$this->ProdGasPromotion->unbindModel(array('hasMany' => array('ProdGasArticlesPromotion')));
		$this->ProdGasPromotion->recursive = 2; 
	    $this->paginate = array('conditions' => $conditions,'order'=>'ProdGasPromotion.data_inizio asc','limit' => $SqlLimit);
		$results = $this->paginate('ProdGasPromotion');
		
		/*
		 * per ogni GAS cerco eventuali ordini
 		 */
		App::import('Model', 'Order'); 
		foreach($results as $numResult => $result) {
			
			if(isset($result['ProdGasPromotionsOrganization']))
				foreach($result['ProdGasPromotionsOrganization'] as $numResult2 => $prodGasPromotionsOrganization) {
					
					$Order = new Order;
					
					$options = array();
					$options['conditions'] = array('Order.organization_id' => $prodGasPromotionsOrganization['organization_id'],
												   'Order.id' => $prodGasPromotionsOrganization['promotion_id']);
					$options['recursive'] = -1;
					$orderResults = $Order->find('first', $options);
					if(!empty($orderResults)) {
						$results[$numResult]['ProdGasPromotionsOrganization'][$numResult2]['Order'] = $orderResults['Order'];
					}
				}
		} 
		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
		$this->set('results', $results);
		$this->set('SqlLimit', $SqlLimit);
		
		/*
		 * legenda
		 */
		$group_id = Configure::read('prod_gas_manager');
		$prodGasPromotionStates = $this->ActionsProdGasPromotions->getProdGasPromotionStatesToLegenda($this->user, $group_id);
		$this->set('prodGasPromotionStates', $prodGasPromotionStates);		
	}
	
	public function admin_add() {
	
		$debug=false;
		
		$msg = "";

		/*
		 * setting fields
		*/
		if ($this->request->is('post') || $this->request->is('put')) {
			$data_inizio = $this->request->data['ProdGasPromotion']['data_inizio'];
			$data_inizio_db = $this->request->data['ProdGasPromotion']['data_inizio_db'];
			$data_fine = $this->request->data['ProdGasPromotion']['data_fine'];
			$data_fine_db = $this->request->data['ProdGasPromotion']['data_fine_db'];
			
			if(isset($this->request->data['ProdGasPromotion']['hasTrasport']))
				$hasTrasport = $this->request->data['ProdGasPromotion']['hasTrasport'];
			else
				$hasTrasport = 'N';

			if(isset($this->request->data['ProdGasPromotion']['hasCostMore']))
				$hasCostMore = $this->request->data['ProdGasPromotion']['hasCostMore'];
			else
				$hasCostMore = 'N';
		}
		else {
			$data_inizio = '';
			$data_inizio_db = '';
			$data_fine = '';
			$data_fine_db = '';
			$hasTrasport = 'N';
			$hasCostLess = 'N';
		}
		
		$this->set('data_inizio', $data_inizio);
		$this->set('data_inizio_db', $data_inizio_db);
		$this->set('data_fine', $data_fine);
		$this->set('data_fine_db', $data_fine_db);
		$this->set('hasTrasportDefault', $hasTrasport);
		$this->set('hasCostMoreDefault', $hasCostMore);

			
		if ($this->request->is('post') || $this->request->is('put')) {
			/*
			echo "<pre>";
			print_r($this->request->data);
			echo "</pre>";
			*/
			
			/*
			 * dati promozione
 			 */
			$continue = false;
			
			$this->request->data['ProdGasPromotion']['supplier_id'] = $this->user->supplier['Supplier']['id'];
			$this->request->data['ProdGasPromotion']['name'] = $this->request->data['ProdGasPromotion']['name'];
			$this->request->data['ProdGasPromotion']['data_inizio_db'] = $this->request->data['ProdGasPromotion']['data_inizio_db'];
			$this->request->data['ProdGasPromotion']['data_fine_db'] = $this->request->data['ProdGasPromotion']['data_fine_db'];
			$this->request->data['ProdGasPromotion']['importo_originale'] = $this->request->data['ProdGasPromotion']['importo_originale_totale'];
			$this->request->data['ProdGasPromotion']['importo_scontato'] = $this->request->data['ProdGasPromotion']['importo_scontato_totale'];
			$this->request->data['ProdGasPromotion']['nota'] = $this->request->data['ProdGasPromotion']['nota'];
			$this->request->data['ProdGasPromotion']['state_code'] = 'WORKING';
			$this->request->data['ProdGasPromotion']['stato'] = 'Y';
   
			$this->ProdGasPromotion->set($this->request->data);
			if(!$this->ProdGasPromotion->validates()) {
				$errors = $this->ProdGasPromotion->validationErrors;
				$continue = false;
				if($debug) {
					echo "<pre>";
					print_r($errors);
					echo "</pre>";	
				}
			}
			else {
				$this->ProdGasPromotion->create();
				if($this->ProdGasPromotion->save($this->request->data)) {
					$continue = true;
					$prod_gas_promotion_id = $this->ProdGasPromotion->getLastInsertId();
				}
				else 
					$continue = false;
			}

			
			/*
			 * immagine
			 */
			if($continue) {
				$arr_extensions = Configure::read('App.web.img.upload.extension');
				$arr_contentTypes = Configure::read('ContentType.img');		 
				$path_upload = Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$this->user->supplier['Supplier']['id'].DS;
				
				if(!empty($this->request->data['Document']['img1']['name'])){
					$esito = $this->Documents->genericUpload($this->user, $this->request->data['Document']['img1'], $path_upload, 'UPLOAD', $prod_gas_promotion_id, $arr_extensions, $arr_contentTypes, Configure::read('App.web.img.upload.width.prod_gas_promotion'), $debug);
					if(empty($esito['msg'])) {	
						$sql = "UPDATE
									".Configure::read('DB.prefix')."prod_gas_promotions
								SET
									img1 = '".$esito['fileNewName']."'
								WHERE
									supplier_id = ".$this->user->supplier['Supplier']['id']."
									and id = ".$prod_gas_promotion_id;
						if($debug) echo "UPDATE IMG ".$sql;
						$uploadResults = $this->ProdGasPromotion->query($sql);						
					}
					else
						$msg = $esito['msg'];
						if($debug)
							echo "<br  />msg UPLOAD ".$msg;
				}				
			}
			
			/*
			 * ProdGasArticlesPromotion
			 */
			if($continue) {
				if(!empty($this->request->data['ProdGasPromotion']['prod_gas_article_ids_selected'])) {
					
					App::import('Model', 'ProdGasArticlesPromotion');
					$ProdGasArticlesPromotion = new ProdGasArticlesPromotion;
		
					$prod_gas_article_ids_selected = explode(',', $this->request->data['ProdGasPromotion']['prod_gas_article_ids_selected']);
					if($debug) echo "<br />ProdGasArticles scelti ".$this->request->data['ProdGasPromotion']['prod_gas_article_ids_selected'];
					
					foreach($prod_gas_article_ids_selected as $prod_gas_article_id) {
						
						if(isset($this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$prod_gas_article_id])) {
							$prezzo_unita = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$prod_gas_article_id]['prezzo_unita'];
							$qta = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$prod_gas_article_id]['qta'];
							$importo = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$prod_gas_article_id]['importo_scontato'];
							if($debug) echo "<br />ProdGasArticlesPromotion $prod_gas_article_id - qta $qta - prezzo_unita $prezzo_unita - importo $importo";
											
							$this->request->data['ProdGasArticlesPromotion']['supplier_id'] = $this->user->supplier['Supplier']['id'];	
							$this->request->data['ProdGasArticlesPromotion']['prod_gas_promotion_id'] = $prod_gas_promotion_id;	
							$this->request->data['ProdGasArticlesPromotion']['prod_gas_article_id'] = $prod_gas_article_id;	
							$this->request->data['ProdGasArticlesPromotion']['prezzo_unita'] = $prezzo_unita;
							$this->request->data['ProdGasArticlesPromotion']['qta'] = $qta;
							$this->request->data['ProdGasArticlesPromotion']['importo'] = $importo;

							$ProdGasArticlesPromotion->create();
							if($ProdGasArticlesPromotion->save($this->request->data)) {
								
							}
						}	
					}	
				}
			} // end if($continue) 

  
			/*
			 * Organization
			 */
			if(!empty($this->request->data['ProdGasPromotion']['organization_ids_selected'])) {
				$organization_ids_selected = explode(',', $this->request->data['ProdGasPromotion']['organization_ids_selected']);
				if($debug) echo "<br />Organizations scelte ".$this->request->data['ProdGasPromotion']['organization_ids_selected'];
					
				App::import('Model', 'ProdGasPromotionsOrganization');
				$ProdGasPromotionsOrganization = new ProdGasPromotionsOrganization;
					
				foreach($organization_ids_selected as $organization_id) {
					
					if(isset($this->request->data['ProdGasPromotion']['Organization'][$organization_id])) {
						$trasport = $this->request->data['ProdGasPromotion']['Organization'][$organization_id]['trasport'];
						$cost_more = $this->request->data['ProdGasPromotion']['Organization'][$organization_id]['costMore'];
						if($debug) echo "<br />Organization $organization_id - trasport $trasport - cost_more $cost_more";
																	
						$this->request->data['ProdGasPromotionsOrganization']['supplier_id'] = $this->user->supplier['Supplier']['id'];	
						$this->request->data['ProdGasPromotionsOrganization']['prod_gas_promotion_id'] = $prod_gas_promotion_id;	
						$this->request->data['ProdGasPromotionsOrganization']['organization_id'] = $organization_id;	
						$this->request->data['ProdGasPromotionsOrganization']['order_id'] = 0;	
						if($trasport>0) {
							$this->request->data['ProdGasPromotionsOrganization']['hasTrasport'] = 'Y';								
							$this->request->data['ProdGasPromotionsOrganization']['trasport'] = $trasport;
						}
						else {
							$this->request->data['ProdGasPromotionsOrganization']['hasTrasport'] = 'N';
							$this->request->data['ProdGasPromotionsOrganization']['trasport'] = '0.00';							
						}
						if($cost_more>0) {
							$this->request->data['ProdGasPromotionsOrganization']['hasCostMore'] = 'Y';	
							$this->request->data['ProdGasPromotionsOrganization']['cost_more'] = $cost_more;
						}
						else {
							$this->request->data['ProdGasPromotionsOrganization']['hasCostMore'] = 'N';	
							$this->request->data['ProdGasPromotionsOrganization']['cost_more'] = '0.00';
						}
						$this->request->data['ProdGasPromotionsOrganization']['nota'] = "";

						$ProdGasPromotionsOrganization->create();
						if($ProdGasPromotionsOrganization->save($this->request->data)) {
							
						}						
					}	
				}	
			}

			
			
			if($continue) {
				
				$msg .= __('The ProdGasPromotion has been saved');
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=ProdGasPromotions&action=index&prod_gas_promotion_id='.$prod_gas_promotion_id;

				if($debug) {
					echo "<pre>";
					print_r($msg.' ');
					print_r($url);
					echo "</pre>";
					exit;
				}
				
				$this->Session->setFlash($msg);
				if(!empty($url) && !$debug) $this->myRedirect($url);	
			}
			else {
				$msg = __('The ProdGasPromotion could not be saved. Please, try again.');	
				$this->Session->setFlash($msg);			
			}
		} // end if ($this->request->is('post') || $this->request->is('put'))
							
		/*
		 * get elenco Organizations
		*/
		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;	
		
		$organizationResults = $ProdGasSupplier->getOrganizationsAssociate($this->user, 0, $debug);
		$organizationNotResults = $ProdGasSupplier->getOrganizationsNotAssociate($this->user, $debug);

		$this->set('organizationResults',$organizationResults);		
		$this->set('organizationNotResults',$organizationNotResults);	
		
		/*
		 * get elenco Article
		 */	
		App::import('Model', 'ProdGasArticle');
		$ProdGasArticle = new ProdGasArticle;
		
		$prodGasArticleResults = $ProdGasArticle->getArticles($this->user, 0, $debug);
		$this->set('prodGasArticleResults',$prodGasArticleResults);	 
	}
			
	public function admin_edit($prod_gas_promotion_id) {
	
		$debug=false;
		
		if (empty($prod_gas_promotion_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$msg = "";
		
		/*
		 * setting fields
		*/
		if ($this->request->is('post') || $this->request->is('put')) {
			/*
			echo "<pre>";
			print_r($this->request->data);
			echo "</pre>";
			*/
		
			/*
			 * dati promozione
 			 */
			$continue = false;
			
			$this->request->data['ProdGasPromotion']['id'] = $prod_gas_promotion_id;
			$this->request->data['ProdGasPromotion']['supplier_id'] = $this->user->supplier['Supplier']['id'];
			$this->request->data['ProdGasPromotion']['name'] = $this->request->data['ProdGasPromotion']['name'];
			$this->request->data['ProdGasPromotion']['data_inizio_db'] = $this->request->data['ProdGasPromotion']['data_inizio_db'];
			$this->request->data['ProdGasPromotion']['data_fine_db'] = $this->request->data['ProdGasPromotion']['data_fine_db'];
			$this->request->data['ProdGasPromotion']['importo_originale'] = $this->request->data['ProdGasPromotion']['importo_originale_totale'];
			$this->request->data['ProdGasPromotion']['importo_scontato'] = $this->request->data['ProdGasPromotion']['importo_scontato_totale'];
			$this->request->data['ProdGasPromotion']['nota'] = $this->request->data['ProdGasPromotion']['nota'];
   
			$this->ProdGasPromotion->set($this->request->data);
			if(!$this->ProdGasPromotion->validates()) {
				$errors = $this->ProdGasPromotion->validationErrors;
				$continue = false;
				if($debug) {
					echo "<pre>";
					print_r($errors);
					echo "</pre>";	
				}
			}
			else {
				$this->ProdGasPromotion->create();
				if($this->ProdGasPromotion->save($this->request->data)) {
					$continue = true;
				}
				else 
					$continue = false;
			}

			
			/*
			 * immagine
			 */
			if($continue) {
				$arr_extensions = Configure::read('App.web.img.upload.extension');
				$arr_contentTypes = Configure::read('ContentType.img');		 
				$path_upload = Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$this->user->supplier['Supplier']['id'].DS;
				
				if(!empty($this->request->data['Document']['img1']['name'])){
					$esito = $this->Documents->genericUpload($this->user, $this->request->data['Document']['img1'], $path_upload, 'UPLOAD', $prod_gas_promotion_id, $arr_extensions, $arr_contentTypes, Configure::read('App.web.img.upload.width.prod_gas_promotion'), $debug);
					if(empty($esito['msg'])) {	
						$sql = "UPDATE
									".Configure::read('DB.prefix')."prod_gas_promotions
								SET
									img1 = '".$esito['fileNewName']."'
								WHERE
									supplier_id = ".$this->user->supplier['Supplier']['id']."
									and id = ".$prod_gas_promotion_id;
						if($debug) echo "UPDATE IMG ".$sql;
						$uploadResults = $this->ProdGasPromotion->query($sql);						
					}
					else
						$msg = $esito['msg'];
						if($debug)
							echo "<br  />msg UPLOAD ".$msg;
				}				
			}
			
			/*
			 * ProdGasArticlesPromotion
			 */
			if($continue) {
				if(!empty($this->request->data['ProdGasPromotion']['prod_gas_article_ids_selected'])) {
					
					App::import('Model', 'ProdGasArticlesPromotion');
					$ProdGasArticlesPromotion = new ProdGasArticlesPromotion;
		
					$prod_gas_article_ids_selected = explode(',', $this->request->data['ProdGasPromotion']['prod_gas_article_ids_selected']);
					if($debug) echo "<br />ProdGasArticles scelti ".$this->request->data['ProdGasPromotion']['prod_gas_article_ids_selected'];
					
					foreach($prod_gas_article_ids_selected as $prod_gas_article_id) {
						
						if(isset($this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$prod_gas_article_id])) {
							$prezzo_unita = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$prod_gas_article_id]['prezzo_unita'];
							$qta = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$prod_gas_article_id]['qta'];
							$importo = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$prod_gas_article_id]['importo_scontato'];
							if($debug) echo "<br />ProdGasArticlesPromotion $prod_gas_article_id - qta $qta - prezzo_unita $prezzo_unita - importo $importo";
							
							$this->request->data['ProdGasArticlesPromotion']['id'] = $prod_gas_article_id;
							
							$this->request->data['ProdGasArticlesPromotion']['supplier_id'] = $this->user->supplier['Supplier']['id'];	
							$this->request->data['ProdGasArticlesPromotion']['prod_gas_promotion_id'] = $prod_gas_promotion_id;	
							$this->request->data['ProdGasArticlesPromotion']['prod_gas_article_id'] = $prod_gas_article_id;	
							$this->request->data['ProdGasArticlesPromotion']['prezzo_unita'] = $prezzo_unita;
							$this->request->data['ProdGasArticlesPromotion']['qta'] = $qta;
							$this->request->data['ProdGasArticlesPromotion']['importo'] = $importo;

							$ProdGasArticlesPromotion->create();
							if($ProdGasArticlesPromotion->save($this->request->data)) {
								
							}
						}	
					}	
				}
			} // end if($continue) 

  
			/*
			 * Organization
			 */
			if(!empty($this->request->data['ProdGasPromotion']['organization_ids_selected'])) {
				$organization_ids_selected = explode(',', $this->request->data['ProdGasPromotion']['organization_ids_selected']);
				if($debug) echo "<br />Organizations scelte ".$this->request->data['ProdGasPromotion']['organization_ids_selected'];
					
				App::import('Model', 'ProdGasPromotionsOrganization');
				$ProdGasPromotionsOrganization = new ProdGasPromotionsOrganization;
					
				foreach($organization_ids_selected as $organization_id) {
					
					if(isset($this->request->data['ProdGasPromotion']['Organization'][$organization_id])) {
						$trasport = $this->request->data['ProdGasPromotion']['Organization'][$organization_id]['trasport'];
						$cost_more = $this->request->data['ProdGasPromotion']['Organization'][$organization_id]['costMore'];
						if($debug) echo "<br />Organization $organization_id - trasport $trasport - cost_more $cost_more";
						
						/*
						 * cerco se esiste gia' un occorrenza  
						 */
						$options = array();
						$options['conditions'] = array('ProdGasPromotionsOrganization.organization_id' => (int)$organization_id,
														'ProdGasPromotionsOrganization.supplier_id' => $this->user->supplier['Supplier']['id'],
														'ProdGasPromotionsOrganization.prod_gas_promotion_id' => $prod_gas_promotion_id);
						$options['order'] = array('ProdGasPromotionsOrganization.id');
						$options['recursive'] = -1;
						$prodGasPromotionsOrganizationResults = $ProdGasPromotionsOrganization->find('first', $options);
						if(!empty($prodGasPromotionsOrganizationResults)) {
							$this->request->data['ProdGasPromotionsOrganization']['id'] = $prodGasPromotionsOrganizationResults['ProdGasPromotionsOrganization']['id'];
							$this->request->data['ProdGasPromotionsOrganization']['order_id'] = $prodGasPromotionsOrganizationResults['ProdGasPromotionsOrganization']['order_id'];
							$this->request->data['ProdGasPromotionsOrganization']['nota'] = $prodGasPromotionsOrganizationResults['ProdGasPromotionsOrganization']['nota'];
						}	
						else {
							$this->request->data['ProdGasPromotionsOrganization']['order_id'] = 0;	
							$this->request->data['ProdGasPromotionsOrganization']['nota'] = "";
						}							 				
										
						$this->request->data['ProdGasPromotionsOrganization']['supplier_id'] = $this->user->supplier['Supplier']['id'];	
						$this->request->data['ProdGasPromotionsOrganization']['prod_gas_promotion_id'] = $prod_gas_promotion_id;	
						$this->request->data['ProdGasPromotionsOrganization']['organization_id'] = $organization_id;	
						if($trasport>0) {
							$this->request->data['ProdGasPromotionsOrganization']['hasTrasport'] = 'Y';								
							$this->request->data['ProdGasPromotionsOrganization']['trasport'] = $trasport;
						}
						else {
							$this->request->data['ProdGasPromotionsOrganization']['hasTrasport'] = 'N';
							$this->request->data['ProdGasPromotionsOrganization']['trasport'] = '0.00';							
						}
						if($cost_more>0) {
							$this->request->data['ProdGasPromotionsOrganization']['hasCostMore'] = 'Y';	
							$this->request->data['ProdGasPromotionsOrganization']['cost_more'] = $cost_more;
						}
						else {
							$this->request->data['ProdGasPromotionsOrganization']['hasCostMore'] = 'N';	
							$this->request->data['ProdGasPromotionsOrganization']['cost_more'] = '0.00';
						}
						

						$ProdGasPromotionsOrganization->create();
						if($ProdGasPromotionsOrganization->save($this->request->data)) {
							
						}						
					}	
				}	
			}

			
			
			if($continue) {
				
				$msg .= __('The ProdGasPromotion has been saved');
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=ProdGasPromotions&action=index&prod_gas_promotion_id='.$prod_gas_promotion_id;

				if($debug) {
					echo "<pre>";
					print_r($msg.' ');
					print_r($url);
					echo "</pre>";
					exit;
				}
				
				$this->Session->setFlash($msg);
				if(!empty($url) && !$debug) $this->myRedirect($url);	
			}
			else {
				$msg = __('The ProdGasPromotion could not be saved. Please, try again.');	
				$this->Session->setFlash($msg);			
			}
		}
		
		/*
		 * get elenco Article
		 */	
		App::import('Model', 'ProdGasArticle');
		$ProdGasArticle = new ProdGasArticle;
		
		$prodGasArticleResults = $ProdGasArticle->getArticles($this->user, $prod_gas_promotion_id, $debug);
		$this->set('prodGasArticleResults',$prodGasArticleResults);	

		$this->request->data = $this->ProdGasPromotion->getProdGasPromotion($this->user, $this->user->supplier['Supplier']['id'], $prod_gas_promotion_id);	
			
		if(!empty($this->request->data['ProdGasPromotion']['img1']) && 
		   file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$this->request->data['ProdGasPromotion']['supplier_id'].DS.$this->request->data['ProdGasPromotion']['img1'])) {
			
			$file1 = new File(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$this->request->data['ProdGasPromotion']['supplier_id'].DS.$this->request->data['ProdGasPromotion']['img1']);
			$this->set('file1', $file1);
		}	

		/*
		 * get elenco Organizations
		*/
		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;	
		
		$organizationResults = $ProdGasSupplier->getOrganizationsAssociate($this->user, $prod_gas_promotion_id, $debug);
		$organizationNotResults = $ProdGasSupplier->getOrganizationsNotAssociate($this->user, $debug);
	
		$this->set('organizationResults',$organizationResults);		
		$this->set('organizationNotResults',$organizationNotResults);
		
	}

	public function admin_delete($prod_gas_promotion_id) {

		$debug = false;
		if($debug) {
			echo "<pre>";
			print_r($this->request->data);
			echo "</pre>";
		}
		
		if(isset($this->request->data['ProdGasPromotion']['prod_gas_promotion_id']))
			$prod_gas_promotion_id = $this->request->data['ProdGasPromotion']['prod_gas_promotion_id'];
		
		if (empty($prod_gas_promotion_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		 
		$results = $this->ProdGasPromotion->getProdGasPromotion($this->user, $this->user->supplier['Supplier']['id'], $prod_gas_promotion_id);
		$this->set('results', $results);
			
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->ProdGasPromotion->id = $prod_gas_promotion_id;
			if ($this->ProdGasPromotion->delete()) {
				$msg = __('Delete ProdGasPromotion');
				$this->Session->setFlash($msg);				
			}
			
			$this->myRedirect(array('action' => 'index'));
		}
	}
	
	/*
	 * stato da WORKING a TRASMISSION-TO-GAS
	 */
	public function admin_trasmission_to_gas($prod_gas_promotion_id) {

		$debug = false;
		if($debug) {
			echo "<pre>";
			print_r($this->request->data);
			echo "</pre>";
		}
		
		if (empty($prod_gas_promotion_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		 
		$results = $this->ProdGasPromotion->getProdGasPromotion($this->user, $this->user->supplier['Supplier']['id'], $prod_gas_promotion_id);
		$this->set('results', $results);
		$this->set('prod_gas_promotion_id', $prod_gas_promotion_id);
		
		/*
		 * get elenco Organizations
		*/
		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;	
		
		$organizationResults = $ProdGasSupplier->getOrganizationsAssociate($this->user, $prod_gas_promotion_id, $debug);
		
		$this->set('organizationResults',$organizationResults);	
		
		if ($this->request->is('post') || $this->request->is('put')) {
			$this->ProdGasPromotion->settingStateCode($this->user, $prod_gas_promotion_id, 'TRASMISSION-TO-GAS', $debug);
			$this->Session->setFlash(__('ProdGasPromotion in TRASMISSION-TO-GAS'));
			if(!$debug) $this->myRedirect(array('action' => 'index'));
		}
	}

	public function admin_change_state_code($prod_gas_promotion_id, $next_code) {

		$debug = false;
		
	//	if(isset($this->request->data['ProdGasPromotion']['prod_gas_promotion_id']))
	//		$prod_gas_promotion_id = $this->request->data['ProdGasPromotion']['prod_gas_promotion_id'];
		
		if (empty($prod_gas_promotion_id) || empty($next_code)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$msg = '';
		switch($next_code) {
			case "WORKING":
				$msg = __('ProdGasPromotion in WORKING');
			break;
			default:
				$this->Session->setFlash(__('msg_error_params'));
				$this->myRedirect(Configure::read('routes_msg_exclamation'));			
			break;
		}
		
		$this->ProdGasPromotion->settingStateCode($this->user, $prod_gas_promotion_id, $next_code, $debug);
		$this->Session->setFlash($msg);
		if(!$debug) $this->myRedirect(array('action' => 'index'));
	}	
}