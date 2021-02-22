<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class ProdGasPromotionsController extends AppController {

   public $components = ['RequestHandler','ActionsProdGasPromotions','Documents'];
   public $helpers = ['Html', 'Javascript', 'Ajax'];
    
   public function beforeFilter() {
   		parent::beforeFilter();
   		
   		// debug($this->user->organization['Organization']);

		/* ctrl ACL */
		if($this->user->organization['Organization']['type']!='PRODGAS') {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}	

		if(!$this->ProdGasPromotion->canPromotions($this->user)) {
			$this->Session->setFlash(__('msg_prodgas_promotion_acl_no'));
			$this->myRedirect(Configure::read('routes_msg_stop'));		
		}		
		/* ctrl ACL */ 
   }

   public function admin_tabs_ajax_ecomm_articles_order() {
        App::import('Model', 'ProdGasArticlesPromotion');
        $ProdGasArticlesPromotion = new ProdGasArticlesPromotion;
		
		$options =  [];
		$options['conditions'] = ['ProdGasArticlesPromotion.organization_id' => (int)$this->user->organization['Organization']['id']];
		$options['recursive'] = 1;
		$options['order'] = ['ProdGasArticle.name ASC'];
		$results = $ProdGasArticlesPromotion->find('all', $options); 		
		self::d($results, $debug); 	
		
		$type_draw='COMPLETE'; // SIMPLE
		
		$this->set('type_draw', $type_draw);
		$this->set('results', $results);	   
   }
   
   /*
    * $type='GAS'        promozioni ai G.A.S.
    * $type='GAS-USERS'  promozioni ai singoli utenti
    */
   public function admin_index_gas() {

   		$type='GAS';

		if(!$this->user->organization['Organization']['hasPromotionGas']=='Y') {
			$this->Session->setFlash(__('msg_prodgas_promotion_acl_no'));
			$this->myRedirect(Configure::read('routes_msg_stop'));		
		}	

	   	/*
	   	 * aggiorno lo stato delle promozioni
	   	 * */
   		$utilsCrons = new UtilsCrons(new View(null));
   		if(Configure::read('developer.mode')) echo "<pre>";
   		$utilsCrons->prodGasPromotionsStatoElaborazione($this->user->organization['Organization']['id'], 0, (Configure::read('developer.mode')) ? true : false);
   		if(Configure::read('developer.mode')) echo "</pre>";

		$SqlLimit = 50;
		$conditions[] = ['ProdGasPromotion.organization_id' => $this->user->organization['Organization']['id'],
						 'ProdGasPromotion.type' => $type];

		$this->ProdGasPromotion->unbindModel(['hasMany' => ['ProdGasArticlesPromotion']]);
		$this->ProdGasPromotion->recursive = 2; 
	    $this->paginate = ['conditions' => $conditions, 'order '=> ['ProdGasPromotion.data_inizio asc'], 
	    					'maxLimit' => $SqlLimit, 'limit' => $SqlLimit];
		$results = $this->paginate('ProdGasPromotion');
		
		/*
		 * per ogni GAS cerco eventuali ordini
 		 */
		App::import('Model', 'Order'); 
		foreach($results as $numResult => $result) {
			
			if(isset($result['ProdGasPromotionsOrganization']))
				foreach($result['ProdGasPromotionsOrganization'] as $numResult2 => $prodGasPromotionsOrganization) {
					
					$Order = new Order;
					
					$options = [];
					$options['conditions'] = ['Order.organization_id' => $prodGasPromotionsOrganization['organization_id'],
										      'Order.id' => $prodGasPromotionsOrganization['promotion_id']];
					$options['recursive'] = -1;
					$orderResults = $Order->find('first', $options);
					if(!empty($orderResults)) {
						$results[$numResult]['ProdGasPromotionsOrganization'][$numResult2]['Order'] = $orderResults['Order'];
					}
				}
		} 
		self::d($results, $debug); 	

		$this->set('results', $results);
		$this->set('SqlLimit', $SqlLimit);
		$this->set('type', $type);
		
		/*
		 * legenda
		 */
		$group_id = Configure::read('prod_gas_supplier_manager');
		$prodGasPromotionStates = $this->ActionsProdGasPromotions->getProdGasPromotionStatesToLegenda($this->user, $group_id, $type);
		$this->set('prodGasPromotionStates', $prodGasPromotionStates);		
	}
	
   /*
    * $type='GAS'        promozioni ai G.A.S.
    * $type='GAS-USERS'  promozioni ai singoli utenti
    */
   public function admin_index_gas_users() {

   		$type='GAS-USERS';

		if(!$this->user->organization['Organization']['hasPromotionGasUsers']=='Y') {
			$this->Session->setFlash(__('msg_prodgas_promotion_acl_no'));
			$this->myRedirect(Configure::read('routes_msg_stop'));		
		}

	   	/*
	   	 * aggiorno lo stato delle promozioni
	   	 * */
   		$utilsCrons = new UtilsCrons(new View(null));
   		if(Configure::read('developer.mode')) echo "<pre>";
   		$utilsCrons->prodGasPromotionsStatoElaborazione($this->user->organization['Organization']['id'], 0, (Configure::read('developer.mode')) ? true : false);
   		if(Configure::read('developer.mode')) echo "</pre>";

		$SqlLimit = 50;
		$conditions[] = ['ProdGasPromotion.organization_id' => $this->user->organization['Organization']['id'],
						 'ProdGasPromotion.type' => $type];

		$this->ProdGasPromotion->unbindModel(['hasMany' => ['ProdGasArticlesPromotion']]);
		$this->ProdGasPromotion->recursive = 2; 
	    $this->paginate = ['conditions' => $conditions, 'order '=> ['ProdGasPromotion.data_inizio asc'], 
	    					'maxLimit' => $SqlLimit, 'limit' => $SqlLimit];
		$results = $this->paginate('ProdGasPromotion');
		
		self::d($results, $debug); 	

		$this->set('results', $results);
		$this->set('SqlLimit', $SqlLimit);
		$this->set('type', $type);
		
		/*
		 * legenda
		 */
		$group_id = Configure::read('prod_gas_supplier_manager');
		$prodGasPromotionStates = $this->ActionsProdGasPromotions->getProdGasPromotionStatesToLegenda($this->user, $group_id, $type);
		$this->set('prodGasPromotionStates', $prodGasPromotionStates);		
	}

	/*
	 * Elenco promozioni ai G.A.S.
	 */	
	public function admin_add_gas() {
	
		$debug=false;
		$type = 'GAS';

		if(!$this->user->organization['Organization']['hasPromotionGas']=='Y') {
			$this->Session->setFlash(__('msg_prodgas_promotion_acl_no'));
			$this->myRedirect(Configure::read('routes_msg_stop'));		
		}

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
		
		/*
		 * userprofile
		*/
		$userProfile = JUserHelper::getProfile($this->user->id);
		self::d($userProfile);

		$contact_name = $this->user->name;
		$contact_mail = $this->user->email;
		if(isset($userProfile->profile['phone']) && !empty($userProfile->profile['phone']))	
			$contact_phone = $userProfile->profile['phone']; // telefono dell'utente
		else
			$contact_phone = $this->user->organization['Supplier']['Supplier']['telefono']; // telefono del produttore
		$this->set(compact('contact_name', 'contact_mail', 'contact_phone'));

			
		if ($this->request->is('post') || $this->request->is('put')) {

			if($debug) debug($this->request->data); 
		
			/*
			 * dati promozione
 			 */
			$continue = false;
			
			$this->request->data['ProdGasPromotion']['organization_id'] = $this->user->organization['Organization']['id'];
			$this->request->data['ProdGasPromotion']['name'] = $this->request->data['ProdGasPromotion']['name'];
			$this->request->data['ProdGasPromotion']['data_inizio_db'] = $this->request->data['ProdGasPromotion']['data_inizio_db'];
			$this->request->data['ProdGasPromotion']['data_fine_db'] = $this->request->data['ProdGasPromotion']['data_fine_db'];
			$this->request->data['ProdGasPromotion']['importo_originale'] = $this->request->data['ProdGasPromotion']['importo_originale_totale'];
			$this->request->data['ProdGasPromotion']['importo_scontato'] = $this->request->data['ProdGasPromotion']['importo_scontato_totale'];
			$this->request->data['ProdGasPromotion']['nota'] = $this->request->data['ProdGasPromotion']['nota'];
			$this->request->data['ProdGasPromotion']['contact_name'] = $this->request->data['ProdGasPromotion']['contact_name'];
			$this->request->data['ProdGasPromotion']['contact_mail'] = $this->request->data['ProdGasPromotion']['contact_mail'];
			$this->request->data['ProdGasPromotion']['contact_phone'] = $this->request->data['ProdGasPromotion']['contact_phone'];
			$this->request->data['ProdGasPromotion']['type'] = $type;
			$this->request->data['ProdGasPromotion']['state_code'] = 'PRODGASPROMOTION-GAS-WORKING';
			$this->request->data['ProdGasPromotion']['stato'] = 'Y';
   
			$this->ProdGasPromotion->set($this->request->data);
			if(!$this->ProdGasPromotion->validates()) {
				$errors = $this->ProdGasPromotion->validationErrors;
				$continue = false;
				self::d($errors, $debug);
			}
			else {
				// debug($this->request->data);
				$this->ProdGasPromotion->create();
				if($this->ProdGasPromotion->save($this->request->data)) {
					$continue = true;
					$prod_gas_promotion_id = $this->ProdGasPromotion->getLastInsertId();
				}
				else 
					$continue = false;
			}

			/*
			 * UserProfile 
			 */
			if($continue) {
				App::import('Model', 'UserProfile');
				$userProfile = new UserProfile;	
				
				if(!isset($userProfile->profile['phone']) || empty($userProfile->profile['phone']))	
					$userProfile->setValue($this->user, $this->user->id, 'profile.phone', $this->request->data['ProdGasPromotion']['contact_phone'], $debug);
			}
			
			/*
			 * immagine non + gestita
			if($continue) {
				$arr_extensions = Configure::read('App.web.img.upload.extension');
				$arr_contentTypes = Configure::read('ContentType.img');		 
				$path_upload = Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$this->user->organization['Supplier']['Supplier']['id'].DS;
				
				if(!empty($this->request->data['Document']['img1']['name'])){
					$esito = $this->Documents->genericUpload($this->user, $this->request->data['Document']['img1'], $path_upload, 'UPLOAD', $prod_gas_promotion_id, $arr_extensions, $arr_contentTypes, Configure::read('App.web.img.upload.width.prod_gas_promotion'), $debug);
					if(empty($esito['msg'])) {	
						$sql = "UPDATE
									".Configure::read('DB.prefix')."prod_gas_promotions
								SET
									img1 = '".$esito['fileNewName']."'
								WHERE
									organization_id = ".$this->user->organization['Organization']['id']."
									and id = ".$prod_gas_promotion_id;
						if($debug) echo "UPDATE IMG ".$sql;
						$uploadResults = $this->ProdGasPromotion->query($sql);						
					}
					else
						$msg = $esito['msg'];
						self::d("msg UPLOAD ".$msg, $debug);
				}				
			}
			*/

			/*
			 * ProdGasArticlesPromotion
			 */
			if($continue) {
				if(!empty($this->request->data['ProdGasPromotion']['article_ids_selected'])) {
					
					App::import('Model', 'ProdGasArticlesPromotion');
					$ProdGasArticlesPromotion = new ProdGasArticlesPromotion;
		
					$article_ids_selected = explode(',', $this->request->data['ProdGasPromotion']['article_ids_selected']);
					self::d("ProdGasArticles scelti ".$this->request->data['ProdGasPromotion']['article_ids_selected'], $debug);
					
					foreach($article_ids_selected as $article_id) {
						
						if(isset($this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id])) {
							$prezzo_unita = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id]['prezzo_unita'];
							$qta = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id]['qta'];
							$importo = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id]['importo_scontato'];
							if($debug) echo "<br />ProdGasArticlesPromotion $article_id - qta $qta - prezzo_unita $prezzo_unita - importo $importo";
											
							$this->request->data['ProdGasArticlesPromotion']['organization_id'] = $this->user->organization['Organization']['id'];	
							$this->request->data['ProdGasArticlesPromotion']['prod_gas_promotion_id'] = $prod_gas_promotion_id;	
							$this->request->data['ProdGasArticlesPromotion']['article_id'] = $article_id;	
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
																	
						$this->request->data['ProdGasPromotionsOrganization']['organization_id'] = $this->user->organization['Organization']['id'];	
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
						$this->request->data['ProdGasPromotionsOrganization']['nota_supplier'] = '';
						$this->request->data['ProdGasPromotionsOrganization']['nota_user'] = '';
						$this->request->data['ProdGasPromotionsOrganization']['user_id'] = 0;
						$this->request->data['ProdGasPromotionsOrganization']['state_code'] = 'PRODGASPROMOTION-GAS-WORKING';

						$ProdGasPromotionsOrganization->create();
						if($ProdGasPromotionsOrganization->save($this->request->data)) {
							
						}						
					}	
				}	
			}

			/*
			 * Organization Delivery
			 */
			if(!empty($this->request->data['ProdGasPromotion']['delivery_ids_selected'])) {
				
				$delivery_ids_selected = explode(',', $this->request->data['ProdGasPromotion']['delivery_ids_selected']);
				if($debug) echo "<br />Deliveries scelte ".$this->request->data['ProdGasPromotion']['delivery_ids_selected'];
					
				App::import('Model', 'ProdGasPromotionsOrganizationsDelivery');
				$ProdGasPromotionsOrganizationsDelivery = new ProdGasPromotionsOrganizationsDelivery;
					
				foreach($delivery_ids_selected as $organization_delivery_id) {
					
					list($organization_id, $delivery_id) = explode('-', $organization_delivery_id);
					
					$data = [];
					$data['ProdGasPromotionsOrganizationsDelivery']['supplier_id'] = $this->user->organization['Supplier']['Supplier']['id'];
					$data['ProdGasPromotionsOrganizationsDelivery']['prod_gas_promotion_id'] = $prod_gas_promotion_id;	
					$data['ProdGasPromotionsOrganizationsDelivery']['organization_id'] = $organization_id;	
					$data['ProdGasPromotionsOrganizationsDelivery']['delivery_id'] = $delivery_id;	
					$data['ProdGasPromotionsOrganizationsDelivery']['isConfirmed'] = 'N';	

					$ProdGasPromotionsOrganizationsDelivery->create();
					if($ProdGasPromotionsOrganizationsDelivery->save($data)) {
						
					}	
				}	
			} // end if(!empty($this->request->data['ProdGasPromotion']['delivery_ids_selected']))
			
			
			if($continue) {
				
				$msg .= __('The ProdGasPromotion has been saved');
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=ProdGasPromotions&action=index_gas';

				self::d($url, $debug);
				
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
		
		$organizationResults = $ProdGasSupplier->getOrganizationsAssociateWithDeliveries($this->user, 0, $debug);
		$organizationNotResults = $ProdGasSupplier->getOrganizationsNotAssociate($this->user, $debug);

		$this->set('organizationResults',$organizationResults);		
		$this->set('organizationNotResults',$organizationNotResults);	
		
		/*
		 * get elenco Article
		 */	
		App::import('Model', 'Article');
		$Article = new Article;
		
		$Article->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$Article->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$Article->unbindModel(['hasAndBelongsToMany' => ['Order', 'ArticlesType']]);
		
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
								  'SuppliersOrganization.id' => $this->user->organization['Supplier']['SuppliersOrganization']['id'],
								  'Article.img1 != ' => ''];
		$options['order'] = ['Article.name' => 'asc']; 
		$options['recursive'] = 0;					  		
		$articleResults = $Article->find('all', $options);
		self::d($options, $debug);
		self::d($articleResults, $debug);
		$this->set(compact('articleResults'));

		$this->set('type', $type);	 
	}
	
	/*
	 * Elenco promozioni ai singoli utenti
	 */
	public function admin_add_gas_users() {
	
		$debug=false;
		
		$type = 'GAS-USERS';
		$msg = "";

		if(!$this->user->organization['Organization']['hasPromotionGasUsers']=='Y') {
			$this->Session->setFlash(__('msg_prodgas_promotion_acl_no'));
			$this->myRedirect(Configure::read('routes_msg_stop'));		
		}

		/*
		 * setting fields
		*/
		if ($this->request->is('post') || $this->request->is('put')) {
			$data_inizio = $this->request->data['ProdGasPromotion']['data_inizio'];
			$data_inizio_db = $this->request->data['ProdGasPromotion']['data_inizio_db'];
			$data_fine = $this->request->data['ProdGasPromotion']['data_fine'];
			$data_fine_db = $this->request->data['ProdGasPromotion']['data_fine_db'];
			
			$hasTrasport = 'N';
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

			if($debug) debug($this->request->data); 
		
			/*
			 * dati promozione
 			 */
			$continue = false;
			
			$this->request->data['ProdGasPromotion']['organization_id'] = $this->user->organization['Organization']['id'];
			$this->request->data['ProdGasPromotion']['name'] = $this->request->data['ProdGasPromotion']['name'];
			$this->request->data['ProdGasPromotion']['data_inizio_db'] = $this->request->data['ProdGasPromotion']['data_inizio_db'];
			$this->request->data['ProdGasPromotion']['data_fine_db'] = $this->request->data['ProdGasPromotion']['data_fine_db'];
			$this->request->data['ProdGasPromotion']['importo_originale'] = $this->request->data['ProdGasPromotion']['importo_originale_totale'];
			$this->request->data['ProdGasPromotion']['importo_scontato'] = $this->request->data['ProdGasPromotion']['importo_scontato_totale'];
			$this->request->data['ProdGasPromotion']['nota'] = $this->request->data['ProdGasPromotion']['nota'];
			
			$this->request->data['ProdGasPromotion']['type'] = $type;
			$this->request->data['ProdGasPromotion']['state_code'] = 'PRODGASPROMOTION-GAS-USERS-WORKING';
			$this->request->data['ProdGasPromotion']['stato'] = 'Y';
  
			$this->ProdGasPromotion->set($this->request->data);
			if(!$this->ProdGasPromotion->validates()) {
				$errors = $this->ProdGasPromotion->validationErrors;
				$continue = false;
				self::d($errors, $debug);
			}
			else {
				// debug($this->request->data);
				$this->ProdGasPromotion->create();
				if($this->ProdGasPromotion->save($this->request->data)) {
					$continue = true;
					$prod_gas_promotion_id = $this->ProdGasPromotion->getLastInsertId();
				}
				else 
					$continue = false;
			}

			/*
			 * ProdGasArticlesPromotion 
			 * ArticlesOrder del produttore
			 */
			if($continue) {
				if(!empty($this->request->data['ProdGasPromotion']['article_ids_selected'])) {
					
					App::import('Model', 'ProdGasArticlesPromotion');
					$ProdGasArticlesPromotion = new ProdGasArticlesPromotion;

					App::import('Model', 'Article');
					$Article = new Article;

					App::import('Model', 'ArticlesOrder');
					$ArticlesOrder = new ArticlesOrder;
		
					$article_ids_selected = explode(',', $this->request->data['ProdGasPromotion']['article_ids_selected']);
					if($debug) debug("ProdGasArticles scelti ".$this->request->data['ProdGasPromotion']['article_ids_selected']);
					
					foreach($article_ids_selected as $article_id) {
						
						if(isset($this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id])) {
							$prezzo_unita = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id]['prezzo_unita'];
							$qta = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id]['qta'];
							$importo = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id]['importo_scontato'];
							if($debug) echo "<br />ProdGasArticlesPromotion $article_id - qta $qta - prezzo_unita $prezzo_unita - importo $importo";
											
							$this->request->data['ProdGasArticlesPromotion']['organization_id'] = $this->user->organization['Organization']['id'];	
							$this->request->data['ProdGasArticlesPromotion']['prod_gas_promotion_id'] = $prod_gas_promotion_id;	
							$this->request->data['ProdGasArticlesPromotion']['article_id'] = $article_id;	
							$this->request->data['ProdGasArticlesPromotion']['prezzo_unita'] = $prezzo_unita;
							$this->request->data['ProdGasArticlesPromotion']['qta'] = $qta;
							$this->request->data['ProdGasArticlesPromotion']['importo'] = $importo;

							$ProdGasArticlesPromotion->create();
							if($ProdGasArticlesPromotion->save($this->request->data)) {
								
							}

							/* 
							 * ArticlesOrder del produttore
							 */
							$options = [];
							$options['conditions'] = ['Article.organization_id' => $this->user->organization['Organization']['id'], 
													  'Article.id' => $article_id];
							$options['recursive'] = -1;							  
							$articleResult = $Article->find('first', $options);

							$data = [];
							$data['ArticlesOrder']['organization_id'] = $this->user->organization['Organization']['id'];
							$data['ArticlesOrder']['order_id'] = $prod_gas_promotion_id;
				            $data['ArticlesOrder']['article_organization_id'] = $articleResult['Article']['organization_id'];
				            $data['ArticlesOrder']['article_id'] = $articleResult['Article']['id'];
				            $data['ArticlesOrder']['name'] = $articleResult['Article']['name'];
				            $data['ArticlesOrder']['prezzo'] = $articleResult['Article']['prezzo'];
				            $data['ArticlesOrder']['qta_cart'] = 0;
				            $data['ArticlesOrder']['pezzi_confezione'] = $articleResult['Article']['pezzi_confezione'];
				            $data['ArticlesOrder']['qta_minima'] = $articleResult['Article']['qta_minima'];
				            $data['ArticlesOrder']['qta_massima'] = $articleResult['Article']['qta_massima'];
				            $data['ArticlesOrder']['qta_minima_order'] = $articleResult['Article']['qta_minima_order'];
				            $data['ArticlesOrder']['qta_massima_order'] = $articleResult['Article']['qta_massima_order'];
				            $data['ArticlesOrder']['qta_multipli'] = $articleResult['Article']['qta_multipli'];
				            $data['ArticlesOrder']['flag_bookmarks'] = 'N';
				            if ($this->user->organization['Organization']['hasFieldArticleAlertToQta'] == 'N')
				                $data['ArticlesOrder']['alert_to_qta'] = 0;
				            else
				                $data['ArticlesOrder']['alert_to_qta'] = $articleResult['Article']['alert_to_qta'];
				            $data['ArticlesOrder']['stato'] = 'Y';

							$ArticlesOrder->set($data);
							if($ArticlesOrder->save($data)) {
								
							}				            
						} // if(isset($this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id])) 
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
																	
						$this->request->data['ProdGasPromotionsOrganization']['organization_id'] = $this->user->organization['Organization']['id'];	
						$this->request->data['ProdGasPromotionsOrganization']['prod_gas_promotion_id'] = $prod_gas_promotion_id;	
						$this->request->data['ProdGasPromotionsOrganization']['organization_id'] = $organization_id;	
						$this->request->data['ProdGasPromotionsOrganization']['order_id'] = $prod_gas_promotion_id;	
						$this->request->data['ProdGasPromotionsOrganization']['hasTrasport'] = 'N';
						$this->request->data['ProdGasPromotionsOrganization']['trasport'] = '0.00';							
						$this->request->data['ProdGasPromotionsOrganization']['hasCostMore'] = 'N';	
						$this->request->data['ProdGasPromotionsOrganization']['cost_more'] = '0.00';
						
						$this->request->data['ProdGasPromotionsOrganization']['nota_supplier'] = '';
						$this->request->data['ProdGasPromotionsOrganization']['nota_user'] = '';
						$this->request->data['ProdGasPromotionsOrganization']['user_id'] = 0;
						$this->request->data['ProdGasPromotionsOrganization']['state_code'] = 'PRODGASPROMOTION-GAS-WORKING';

						$ProdGasPromotionsOrganization->create();
						if($ProdGasPromotionsOrganization->save($this->request->data)) {
							
						}						
					}	
				}	
			}

			if($continue) {
				
				$msg .= __('The ProdGasPromotion has been saved');
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=ProdGasPromotions&action=index_gas_users';

				self::d($url, $debug);
				
				$this->Session->setFlash($msg);
				if(!empty($url) && !$debug) $this->myRedirect($url);	
			}
			else {
				$msg = __('The ProdGasPromotion could not be saved. Please, try again.');	
				$this->Session->setFlash($msg);			
			}
		} // end if ($this->request->is('post') || $this->request->is('put'))
			
		/*
		 * nota con i riferimenti della consegna
		*/
		// $userProfile = JUserHelper::getProfile($this->user->id);
		// self::d($userProfile);

		$nota = 'Potrai ritirare gli articoli prenotati presso la nostra sede <br>
				'.$this->user->organization['Supplier']['Supplier']['indirizzo'].' '.$this->user->organization['Supplier']['Supplier']['localita'].' <br>
				nei seguenti orari<br >
				.....<br >
				.....<br >
				.....<br ><br>
				Se desideri contattarci telefoninamente '.$this->user->organization['Supplier']['Supplier']['telefono'].'<br >
				'.$this->user->organization['Supplier']['Supplier']['mail'].' <br><br>
				'.$contact_name = $this->user->name;
		$this->set(compact('nota'));

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
		App::import('Model', 'Article');
		$Article = new Article;
		
		$Article->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$Article->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$Article->unbindModel(['hasAndBelongsToMany' => ['Order', 'ArticlesType']]);
		
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
								  'SuppliersOrganization.id' => $this->user->organization['Supplier']['SuppliersOrganization']['id'],
								  'Article.img1 != ' => ''];
		$options['order'] = ['Article.name' => 'asc']; 
		$options['recursive'] = 0;					  		
		$articleResults = $Article->find('all', $options);
		self::d($options, $debug);
		self::d($articleResults, $debug);
		$this->set(compact('articleResults'));

		$this->set('type', $type);	 
	}

	public function admin_edit_gas($prod_gas_promotion_id) {
	
		$debug=false;
		$type = 'GAS';

		if(!$this->user->organization['Organization']['hasPromotionGas']=='Y') {
			$this->Session->setFlash(__('msg_prodgas_promotion_acl_no'));
			$this->myRedirect(Configure::read('routes_msg_stop'));		
		}

		if (empty($prod_gas_promotion_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$msg = "";
		
		/*
		 * ctrl state_code
		 */
		$options = [];
		$options['conditions'] = ['ProdGasPromotion.organization_id' => $this->user->organization['Organization']['id'],
								   'ProdGasPromotion.id' => $prod_gas_promotion_id];
		$options['recursive'] = -1;
		$prodGasPromotionResults = $this->ProdGasPromotion->find('first', $options);
		
		self::d($options, $debug);
		self::d($prodGasPromotionResults, $debug);
		
		if($prodGasPromotionResults['ProdGasPromotion']['state_code']=='PRODGASPROMOTION-GAS-TRASMISSION-TO-GAS') {
			$this->Session->setFlash(__('msg_not_promotion_state'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));			
		}
			
		/*
		 * setting fields
		*/
		if ($this->request->is('post') || $this->request->is('put')) {
			
			self::d($this->request->data, $debug);
			
			/*
			 * dati promozione
 			 */
			$continue = false;
			
			$data = [];
			$data['ProdGasPromotion'] = $prodGasPromotionResults['ProdGasPromotion'];
			$data['ProdGasPromotion']['id'] = $prod_gas_promotion_id;
			$data['ProdGasPromotion']['organization_id'] = $this->user->organization['Organization']['id'];
			$data['ProdGasPromotion']['name'] = $this->request->data['ProdGasPromotion']['name'];
			$data['ProdGasPromotion']['data_inizio_db'] = $this->request->data['ProdGasPromotion']['data_inizio_db'];
			$data['ProdGasPromotion']['data_fine_db'] = $this->request->data['ProdGasPromotion']['data_fine_db'];
			$data['ProdGasPromotion']['importo_originale'] = $this->request->data['ProdGasPromotion']['importo_originale_totale'];
			$data['ProdGasPromotion']['importo_scontato'] = $this->request->data['ProdGasPromotion']['importo_scontato_totale'];
			$data['ProdGasPromotion']['nota'] = $this->request->data['ProdGasPromotion']['nota'];
			$data['ProdGasPromotion']['nota'] = $this->request->data['ProdGasPromotion']['nota'];
			$data['ProdGasPromotion']['contact_name'] = $this->request->data['ProdGasPromotion']['contact_name'];
			$data['ProdGasPromotion']['contact_mail'] = $this->request->data['ProdGasPromotion']['contact_mail'];
			$data['ProdGasPromotion']['contact_phone'] = $this->request->data['ProdGasPromotion']['contact_phone'];	  			
			// debug($data); 
			$this->ProdGasPromotion->set($data);
			if(!$this->ProdGasPromotion->validates()) {
				$errors = $this->ProdGasPromotion->validationErrors;
				$continue = false;
				self::d($errors, $debug);
			}
			else {
				$this->ProdGasPromotion->create();
				if($this->ProdGasPromotion->save($data)) {
					$continue = true;
				}
				else 
					$continue = false;
			}

			/*
			 * UserProfile 
			 */
			if($continue) {
				App::import('Model', 'UserProfile');
				$userProfile = new UserProfile;	
				
				if(!isset($userProfile->profile['phone']) || empty($userProfile->profile['phone']))	
					$userProfile->setValue($this->user, $this->user->id, 'profile.phone', $this->request->data['ProdGasPromotion']['contact_phone'], $debug);
			}
			
			/*
			 * immagine non + gestita
			if($continue) {
				$arr_extensions = Configure::read('App.web.img.upload.extension');
				$arr_contentTypes = Configure::read('ContentType.img');		 
				$path_upload = Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$this->user->organization['Supplier']['Supplier']['id'].DS;
				
				if(!empty($this->request->data['Document']['img1']['name'])){
					$esito = $this->Documents->genericUpload($this->user, $this->request->data['Document']['img1'], $path_upload, 'UPLOAD', $prod_gas_promotion_id, $arr_extensions, $arr_contentTypes, Configure::read('App.web.img.upload.width.prod_gas_promotion'), $debug);
					if(empty($esito['msg'])) {	
						$sql = "UPDATE
									".Configure::read('DB.prefix')."prod_gas_promotions
								SET
									img1 = '".$esito['fileNewName']."'
								WHERE
									organization_id = ".$this->user->organization['Organization']['id']."
									and id = ".$prod_gas_promotion_id;
						self::d("UPDATE IMG ".$sql, $debug);
						$uploadResults = $this->ProdGasPromotion->query($sql);						
					}
					else
						$msg = $esito['msg'];
						self::d("msg UPLOAD ".$msg, $debug);
				}				
			}
			*/
			
			/*
			 * ProdGasArticlesPromotion
			 */
			if($continue) {
				if(!empty($this->request->data['ProdGasPromotion']['article_ids_selected'])) {
					
					App::import('Model', 'ProdGasArticlesPromotion');
					$ProdGasArticlesPromotion = new ProdGasArticlesPromotion;
		
					$article_ids_selected = explode(',', $this->request->data['ProdGasPromotion']['article_ids_selected']);
					self::d("ProdGasArticles scelti ".$this->request->data['ProdGasPromotion']['article_ids_selected'], $debug);
					
					$delete_not_ids = [];
					foreach($article_ids_selected as $article_id) {
						
						if(isset($this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id])) {
							
							$data = [];
							
							/*
							 * ctrl se insert / update 
							 */
							$options = [];
							$options['conditions'] = ['ProdGasArticlesPromotion.organization_id' => $this->user->organization['Organization']['id'],
													  'ProdGasArticlesPromotion.prod_gas_promotion_id' => $prod_gas_promotion_id,
												      'ProdGasArticlesPromotion.article_id' => $article_id];
							$options['recursive'] = -1;
							$prodGasArticlesPromotionResults = $ProdGasArticlesPromotion->find('first', $options);
							if(!empty($prodGasArticlesPromotionResults)) 
								$data['ProdGasArticlesPromotion']['id'] = $prodGasArticlesPromotionResults['ProdGasArticlesPromotion']['id'];
							else
								$data['ProdGasArticlesPromotion']['id'] = '';
							
							$prezzo_unita = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id]['prezzo_unita'];
							$qta = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id]['qta'];
							$importo = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id]['importo_scontato'];
							self::d("ProdGasArticlesPromotion $article_id - qta $qta - prezzo_unita $prezzo_unita - importo $importo - ID ".$data['ProdGasArticlesPromotion']['id'], $debug);
														
							$data['ProdGasArticlesPromotion']['organization_id'] = $this->user->organization['Organization']['id'];	
							$data['ProdGasArticlesPromotion']['prod_gas_promotion_id'] = $prod_gas_promotion_id;	
							$data['ProdGasArticlesPromotion']['article_id'] = $article_id;	
							$data['ProdGasArticlesPromotion']['prezzo_unita'] = $prezzo_unita;
							$data['ProdGasArticlesPromotion']['qta'] = $qta;
							$data['ProdGasArticlesPromotion']['importo'] = $importo;

							$ProdGasArticlesPromotion->create();
							if($ProdGasArticlesPromotion->save($data)) {
								if(!empty($prodGasArticlesPromotionResults)) 
									array_push($delete_not_ids, $data['ProdGasArticlesPromotion']['id']);
								else
									array_push($delete_not_ids, $order_id = $ProdGasArticlesPromotion->getLastInsertId());				
							}
						}	
					}

					/*
				     * delete id esclusi
					 */
					if(!empty($delete_not_ids)) {
						try {
							$sql = "DELETE FROM ".Configure::read('DB.prefix')."prod_gas_articles_promotions WHERE id NOT IN (".implode(',', $delete_not_ids).")";
							$sql .= " AND organization_id = ".$this->user->organization['Organization']['id']." AND prod_gas_promotion_id = ".$prod_gas_promotion_id; 

							self::d($sql, $debug);
							$deleteResults = $ProdGasArticlesPromotion->query($sql);
						}
						catch (Exception $e) {
							CakeLog::write('error',$sql);
						} 	
					}
				}
			} // end if($continue) 

			/*
			 * Organization
			 */
			if(!empty($this->request->data['ProdGasPromotion']['organization_ids_selected'])) {
				$organization_ids_selected = explode(',', $this->request->data['ProdGasPromotion']['organization_ids_selected']);
				self::d("Organizations scelte ".$this->request->data['ProdGasPromotion']['organization_ids_selected'], $debug);
					
				App::import('Model', 'ProdGasPromotionsOrganization');
				$ProdGasPromotionsOrganization = new ProdGasPromotionsOrganization;
					
				$delete_not_ids = [];
				foreach($organization_ids_selected as $organization_id) {
					
					if(isset($this->request->data['ProdGasPromotion']['Organization'][$organization_id])) {
					
						$data = [];
						
						$trasport = $this->request->data['ProdGasPromotion']['Organization'][$organization_id]['trasport'];
						$cost_more = $this->request->data['ProdGasPromotion']['Organization'][$organization_id]['costMore'];
						self::d("Organization $organization_id - trasport $trasport - cost_more $cost_more", $debug);
						
						/*
						 * cerco se esiste gia' un occorrenza  
						 */
						$options = [];
						$options['conditions'] = ['ProdGasPromotionsOrganization.organization_id' => (int)$organization_id,  // e' quello del GAS
												  'ProdGasPromotionsOrganization.prod_gas_promotion_id' => $prod_gas_promotion_id];
						$options['order'] = ['ProdGasPromotionsOrganization.id'];
						$options['recursive'] = -1;
						$prodGasPromotionsOrganizationResults = $ProdGasPromotionsOrganization->find('first', $options);
						self::d($prodGasPromotionsOrganizationResults, $debug);
						if(!empty($prodGasPromotionsOrganizationResults)) {
							$data['ProdGasPromotionsOrganization']['id'] = $prodGasPromotionsOrganizationResults['ProdGasPromotionsOrganization']['id'];
							$data['ProdGasPromotionsOrganization']['order_id'] = $prodGasPromotionsOrganizationResults['ProdGasPromotionsOrganization']['order_id'];
							$data['ProdGasPromotionsOrganization']['nota_supplier'] = '';
							$data['ProdGasPromotionsOrganization']['nota_user'] = '';
							$data['ProdGasPromotionsOrganization']['user_id'] = 0;
						}	
						else {
							$data['ProdGasPromotionsOrganization']['order_id'] = 0;	
							$data['ProdGasPromotionsOrganization']['nota_supplier'] = '';
							$data['ProdGasPromotionsOrganization']['nota_user'] = '';
							$data['ProdGasPromotionsOrganization']['user_id'] = 0;
						}							 				
										
						$data['ProdGasPromotionsOrganization']['prod_gas_promotion_id'] = $prod_gas_promotion_id;	
						$data['ProdGasPromotionsOrganization']['organization_id'] = $organization_id;    // e' quello del GAS
						if($trasport>0) {
							$data['ProdGasPromotionsOrganization']['hasTrasport'] = 'Y';								
							$data['ProdGasPromotionsOrganization']['trasport'] = $trasport;
						}
						else {
							$data['ProdGasPromotionsOrganization']['hasTrasport'] = 'N';
							$data['ProdGasPromotionsOrganization']['trasport'] = '0.00';							
						}
						if($cost_more>0) {
							$data['ProdGasPromotionsOrganization']['hasCostMore'] = 'Y';	
							$data['ProdGasPromotionsOrganization']['cost_more'] = $cost_more;
						}
						else {
							$data['ProdGasPromotionsOrganization']['hasCostMore'] = 'N';	
							$data['ProdGasPromotionsOrganization']['cost_more'] = '0.00';
						}
						
						self::d($data, $debug);
						$ProdGasPromotionsOrganization->create();
						if($ProdGasPromotionsOrganization->save($data)) {
							if(!empty($prodGasPromotionsOrganizationResults)) 
								array_push($delete_not_ids, $data['ProdGasPromotionsOrganization']['id']);
							else
								array_push($delete_not_ids, $order_id = $ProdGasPromotionsOrganization->getLastInsertId());				
						}						
					}	
				} // end loop Organization

				/*
				 * delete id esclusi
				 */
				if(!empty($delete_not_ids)) {
					try {
						$sql = "DELETE FROM ".Configure::read('DB.prefix')."prod_gas_promotions_organizations WHERE id NOT IN (".implode(',', $delete_not_ids).") AND prod_gas_promotion_id = ".$prod_gas_promotion_id; 
						self::d($sql, $debug);
						$deleteResults = $ProdGasArticlesPromotion->query($sql);
					}
					catch (Exception $e) {
						CakeLog::write('error',$sql);
					} 	
				}	
				
				
				/*
				 * Organization Delivery
				 */
				$delete_not_ids = []; 
				if(!empty($this->request->data['ProdGasPromotion']['delivery_ids_selected'])) {
					
					$delivery_ids_selected = explode(',', $this->request->data['ProdGasPromotion']['delivery_ids_selected']);
					self::d("Deliveries scelte ".$this->request->data['ProdGasPromotion']['delivery_ids_selected'], $debug);
						
					App::import('Model', 'ProdGasPromotionsOrganizationsDelivery');
					$ProdGasPromotionsOrganizationsDelivery = new ProdGasPromotionsOrganizationsDelivery;
						
					foreach($delivery_ids_selected as $organization_delivery_id) {
						
						list($organization_id, $delivery_id) = explode('-', $organization_delivery_id);
						
						/*
						 * cerco se esiste gia' un occorrenza  
						 */
						$options = [];
						$options['conditions'] = [ 'ProdGasPromotionsOrganizationsDelivery.prod_gas_promotion_id' => $prod_gas_promotion_id,
												   'ProdGasPromotionsOrganizationsDelivery.organization_id' => (int)$organization_id,  // e' quello del GAS
												   'ProdGasPromotionsOrganizationsDelivery.delivery_id' => $delivery_id];
						$options['recursive'] = -1;
						$prodGasPromotionsOrganizationsDeliveryResults = $ProdGasPromotionsOrganizationsDelivery->find('first', $options);
						self::d($prodGasPromotionsOrganizationsDeliveryResults, $debug);
						if(empty($prodGasPromotionsOrganizationsDeliveryResults)) {

							$data = [];
							$data['ProdGasPromotionsOrganizationsDelivery']['supplier_id'] = $this->user->organization['Supplier']['Supplier']['id'];
							$data['ProdGasPromotionsOrganizationsDelivery']['prod_gas_promotion_id'] = $prod_gas_promotion_id;	
							$data['ProdGasPromotionsOrganizationsDelivery']['organization_id'] = $organization_id;	 // e' quello del GAS
							$data['ProdGasPromotionsOrganizationsDelivery']['delivery_id'] = $delivery_id;	
							$data['ProdGasPromotionsOrganizationsDelivery']['isConfirmed'] = 'N';	
	
						
							$ProdGasPromotionsOrganizationsDelivery->create();
							if($ProdGasPromotionsOrganizationsDelivery->save($data)) {
								$delete_not_ids[$organization_id][] = $ProdGasPromotionsOrganizationsDelivery->getLastInsertId();							
							}
						}
						else {
							$delete_not_ids[$organization_id][] = $prodGasPromotionsOrganizationsDeliveryResults['ProdGasPromotionsOrganizationsDelivery']['id'];
						}
							
					} // end loop
	
					/*
					 * delete id esclusi
					 */
					if(!empty($delete_not_ids)) {
							
						foreach($delete_not_ids as $organization_id => $ids) {
					
							try {
								$sql = "DELETE FROM ".Configure::read('DB.prefix')."prod_gas_promotions_organizations_deliveries WHERE id NOT IN (".implode(',', $ids).") AND organization_id = ".$organization_id." AND prod_gas_promotion_id = ".$prod_gas_promotion_id; 
		
								self::d($sql, $debug);
								$deleteResults = $ProdGasArticlesPromotion->query($sql);
							}
							catch (Exception $e) {
								CakeLog::write('error',$sql);
							} 	
						}
					}
											
				} // end if(!empty($this->request->data['ProdGasPromotion']['delivery_ids_selected']))					
			}

			
			
			if($continue) {
				
				$msg .= __('The ProdGasPromotion has been saved');
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=ProdGasPromotions&action=index_gas';

				self::d([$msg, $url], $debug);
				
				$this->Session->setFlash($msg);
				if(!empty($url) && !$debug) $this->myRedirect($url);	
			}
			else {
				$msg = __('The ProdGasPromotion could not be saved. Please, try again.');	
				$this->Session->setFlash($msg);			
			}
		}

		$this->request->data = $this->ProdGasPromotion->getProdGasPromotion($this->user, $prod_gas_promotion_id);
		
		/*
		 * get elenco Article esludendo quelli gia' in promozione
		 */	
		/*
		 * get elenco Article
		 */	
		App::import('Model', 'Article');
		$Article = new Article;
		
		$Article->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$Article->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$Article->unbindModel(['hasAndBelongsToMany' => ['Order', 'ArticlesType']]);
		
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
								  'SuppliersOrganization.id' => $this->user->organization['Supplier']['SuppliersOrganization']['id']];
		$options['order'] = ['Article.name' => 'asc']; 
		$options['recursive'] = 0;					  		
		$articleResults = $Article->find('all', $options);
		self::d($options, $debug);
		self::d($articleResults, $debug);

		if(!empty($this->request->data['ProdGasArticlesPromotion'])) {
			
			$prodGasArticlesPromotion = $this->request->data['ProdGasArticlesPromotion'];  // copio perche' dopo faccio unset
			
			foreach($articleResults as $numResults => $articleResult) {
				
				foreach($this->request->data['ProdGasArticlesPromotion'] as $numResults2 => $prodGasArticlesPromotion) {
					if($articleResult['Article']['organization_id']==$prodGasArticlesPromotion['Article']['organization_id'] && $articleResult['Article']['id']==$prodGasArticlesPromotion['Article']['id']) {
						unset($articleResults[$numResults]);
						unset($prodGasArticlesPromotion[$numResults]);
					}
				}
			}
		} // end if(!empty($this->request->data['ProdGasArticlesPromotion']))
		
		$this->set(compact('articleResults'));	 

		/*
		 * non + gestito
		if(!empty($this->request->data['ProdGasPromotion']['img1']) && 
		   file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$this->request->data['ProdGasPromotion']['supplier_id'].DS.$this->request->data['ProdGasPromotion']['img1'])) {
			
			$file1 = new File(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$this->request->data['ProdGasPromotion']['supplier_id'].DS.$this->request->data['ProdGasPromotion']['img1']);
			$this->set('file1', $file1);
		}	
		*/
		
		/*
		 * get elenco Organizations
		*/
		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;	
		
		$organizationResults = $ProdGasSupplier->getOrganizationsAssociateWithDeliveries($this->user, $prod_gas_promotion_id, $debug);
		$organizationNotResults = $ProdGasSupplier->getOrganizationsNotAssociate($this->user, $debug);
	
		$this->set('organizationResults',$organizationResults);		
		$this->set('organizationNotResults',$organizationNotResults);
		
	}

	public function admin_edit_gas_users($prod_gas_promotion_id) {
	
		$debug = false;
		$type = 'GAS-USERS';

		if(!$this->user->organization['Organization']['hasPromotionGasUsers']=='Y') {
			$this->Session->setFlash(__('msg_prodgas_promotion_acl_no'));
			$this->myRedirect(Configure::read('routes_msg_stop'));		
		}

		if (empty($prod_gas_promotion_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$msg = "";
		
		/*
		 * ctrl state_code
		 */
		$options = [];
		$options['conditions'] = ['ProdGasPromotion.organization_id' => $this->user->organization['Organization']['id'],
								   'ProdGasPromotion.id' => $prod_gas_promotion_id];
		$options['recursive'] = -1;
		$prodGasPromotionResults = $this->ProdGasPromotion->find('first', $options);
		
		self::d($options, $debug);
		self::d($prodGasPromotionResults, $debug);

		/*
		 * setting fields
		*/
		if ($this->request->is('post') || $this->request->is('put')) {
			
			self::d($this->request->data, $debug);
			
			/*
			 * dati promozione
 			 */
			$continue = false;
			
			$data = [];
			$data['ProdGasPromotion'] = $prodGasPromotionResults['ProdGasPromotion'];
			$data['ProdGasPromotion']['id'] = $prod_gas_promotion_id;
			$data['ProdGasPromotion']['organization_id'] = $this->user->organization['Organization']['id'];
			$data['ProdGasPromotion']['name'] = $this->request->data['ProdGasPromotion']['name'];
			$data['ProdGasPromotion']['data_inizio_db'] = $this->request->data['ProdGasPromotion']['data_inizio_db'];
			$data['ProdGasPromotion']['data_fine_db'] = $this->request->data['ProdGasPromotion']['data_fine_db'];
			$data['ProdGasPromotion']['importo_originale'] = $this->request->data['ProdGasPromotion']['importo_originale_totale'];
			$data['ProdGasPromotion']['importo_scontato'] = $this->request->data['ProdGasPromotion']['importo_scontato_totale'];
			$data['ProdGasPromotion']['nota'] = $this->request->data['ProdGasPromotion']['nota'];
   			// debug($data); 
			$this->ProdGasPromotion->set($data);
			if(!$this->ProdGasPromotion->validates()) {
				$errors = $this->ProdGasPromotion->validationErrors;
				$continue = false;
				self::d($errors, $debug);
			}
			else {
				$this->ProdGasPromotion->create();
				if($this->ProdGasPromotion->save($data)) {
					$continue = true;
				}
				else 
					$continue = false;
			}

			/*
			 * UserProfile 
			 */
			if($continue) {
				App::import('Model', 'UserProfile');
				$userProfile = new UserProfile;	
				
				if(!isset($userProfile->profile['phone']) || empty($userProfile->profile['phone']))	
					$userProfile->setValue($this->user, $this->user->id, 'profile.phone', $this->request->data['ProdGasPromotion']['contact_phone'], $debug);
			}
			
			/*
			 * immagine non + gestita
			if($continue) {
				$arr_extensions = Configure::read('App.web.img.upload.extension');
				$arr_contentTypes = Configure::read('ContentType.img');		 
				$path_upload = Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$this->user->organization['Supplier']['Supplier']['id'].DS;
				
				if(!empty($this->request->data['Document']['img1']['name'])){
					$esito = $this->Documents->genericUpload($this->user, $this->request->data['Document']['img1'], $path_upload, 'UPLOAD', $prod_gas_promotion_id, $arr_extensions, $arr_contentTypes, Configure::read('App.web.img.upload.width.prod_gas_promotion'), $debug);
					if(empty($esito['msg'])) {	
						$sql = "UPDATE
									".Configure::read('DB.prefix')."prod_gas_promotions
								SET
									img1 = '".$esito['fileNewName']."'
								WHERE
									organization_id = ".$this->user->organization['Organization']['id']."
									and id = ".$prod_gas_promotion_id;
						self::d("UPDATE IMG ".$sql, $debug);
						$uploadResults = $this->ProdGasPromotion->query($sql);						
					}
					else
						$msg = $esito['msg'];
						self::d("msg UPLOAD ".$msg, $debug);
				}				
			}
			*/

			/*
			 * ProdGasArticlesPromotion
			 * ArticlesOrder del produttore
			 */
			if($continue) {
				if(!empty($this->request->data['ProdGasPromotion']['article_ids_selected'])) {
					
					App::import('Model', 'ProdGasArticlesPromotion');
					$ProdGasArticlesPromotion = new ProdGasArticlesPromotion;
		
					App::import('Model', 'Article');
					$Article = new Article;

					App::import('Model', 'ArticlesOrder');
					$ArticlesOrder = new ArticlesOrder;

					$article_ids_selected = explode(',', $this->request->data['ProdGasPromotion']['article_ids_selected']);
					self::d("ProdGasArticles scelti ".$this->request->data['ProdGasPromotion']['article_ids_selected'], $debug);
					
					$delete_not_ids = [];
					foreach($article_ids_selected as $article_id) {
						
						if(isset($this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id])) {
							
							$data = [];
							
							/*
							 * ctrl se insert / update 
							 */
							$options = [];
							$options['conditions'] = ['ProdGasArticlesPromotion.organization_id' => $this->user->organization['Organization']['id'],
													  'ProdGasArticlesPromotion.prod_gas_promotion_id' => $prod_gas_promotion_id,
												      'ProdGasArticlesPromotion.article_id' => $article_id];
							$options['recursive'] = -1;
							$prodGasArticlesPromotionResults = $ProdGasArticlesPromotion->find('first', $options);
							if(!empty($prodGasArticlesPromotionResults)) 
								$data['ProdGasArticlesPromotion']['id'] = $prodGasArticlesPromotionResults['ProdGasArticlesPromotion']['id'];
							else
								$data['ProdGasArticlesPromotion']['id'] = '';
							
							$prezzo_unita = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id]['prezzo_unita'];
							$qta = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id]['qta'];
							$importo = $this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id]['importo_scontato'];
							self::d("ProdGasArticlesPromotion $article_id - qta $qta - prezzo_unita $prezzo_unita - importo $importo - ID ".$data['ProdGasArticlesPromotion']['id'], $debug);
														
							$data['ProdGasArticlesPromotion']['organization_id'] = $this->user->organization['Organization']['id'];	
							$data['ProdGasArticlesPromotion']['prod_gas_promotion_id'] = $prod_gas_promotion_id;	
							$data['ProdGasArticlesPromotion']['article_id'] = $article_id;	
							$data['ProdGasArticlesPromotion']['prezzo_unita'] = $prezzo_unita;
							$data['ProdGasArticlesPromotion']['qta'] = $qta;
							$data['ProdGasArticlesPromotion']['importo'] = $importo;

							$ProdGasArticlesPromotion->create();
							if($ProdGasArticlesPromotion->save($data)) {
								if(!empty($prodGasArticlesPromotionResults)) 
									array_push($delete_not_ids, $data['ProdGasArticlesPromotion']['id']);
								else
									array_push($delete_not_ids, $order_id = $ProdGasArticlesPromotion->getLastInsertId());				
							}

							/* 
							 * ArticlesOrder del produttore
							 */
							$options = [];
							$options['conditions'] = ['Article.organization_id' => $this->user->organization['Organization']['id'], 
													  'Article.id' => $article_id];
							$options['recursive'] = -1;							  
							$articleResult = $Article->find('first', $options);

							$data = [];
							$data['ArticlesOrder']['organization_id'] = $this->user->organization['Organization']['id'];
							$data['ArticlesOrder']['order_id'] = $prod_gas_promotion_id;
				            $data['ArticlesOrder']['article_organization_id'] = $articleResult['Article']['organization_id'];
				            $data['ArticlesOrder']['article_id'] = $articleResult['Article']['id'];
				            $data['ArticlesOrder']['name'] = $articleResult['Article']['name'];
				            $data['ArticlesOrder']['prezzo'] = $articleResult['Article']['prezzo'];
				            $data['ArticlesOrder']['qta_cart'] = 0;
				            $data['ArticlesOrder']['pezzi_confezione'] = $articleResult['Article']['pezzi_confezione'];
				            $data['ArticlesOrder']['qta_minima'] = $articleResult['Article']['qta_minima'];
				            $data['ArticlesOrder']['qta_massima'] = $articleResult['Article']['qta_massima'];
				            $data['ArticlesOrder']['qta_minima_order'] = $articleResult['Article']['qta_minima_order'];
				            $data['ArticlesOrder']['qta_massima_order'] = $articleResult['Article']['qta_massima_order'];
				            $data['ArticlesOrder']['qta_multipli'] = $articleResult['Article']['qta_multipli'];
				            $data['ArticlesOrder']['flag_bookmarks'] = 'N';
				            if ($this->user->organization['Organization']['hasFieldArticleAlertToQta'] == 'N')
				                $data['ArticlesOrder']['alert_to_qta'] = 0;
				            else
				                $data['ArticlesOrder']['alert_to_qta'] = $articleResult['Article']['alert_to_qta'];
				            $data['ArticlesOrder']['stato'] = 'Y';

							$ArticlesOrder->set($data);
							if($ArticlesOrder->save($data)) {
								
							}					            
						} // if(isset($this->request->data['ProdGasPromotion']['ProdGasArticlesPromotion'][$article_id])) 
					}

					/*
				     * delete id esclusi
					 */
					if(!empty($delete_not_ids)) {
						try {
							$sql = "DELETE FROM ".Configure::read('DB.prefix')."prod_gas_articles_promotions WHERE id NOT IN (".implode(',', $delete_not_ids).")";
							$sql .= " AND organization_id = ".$this->user->organization['Organization']['id']." AND prod_gas_promotion_id = ".$prod_gas_promotion_id; 

							self::d($sql, $debug);
							$deleteResults = $ProdGasArticlesPromotion->query($sql);
						}
						catch (Exception $e) {
							CakeLog::write('error',$sql);
						} 	
					}
				}
			} // end if($continue) 

			/*
			 * Organization
			 */
			if(!empty($this->request->data['ProdGasPromotion']['organization_ids_selected'])) {
				$organization_ids_selected = explode(',', $this->request->data['ProdGasPromotion']['organization_ids_selected']);
				if($debug) debug("Organizations scelte ".$this->request->data['ProdGasPromotion']['organization_ids_selected']);
					
				App::import('Model', 'ProdGasPromotionsOrganization');
				$ProdGasPromotionsOrganization = new ProdGasPromotionsOrganization;
					
				$delete_not_ids = [];
				foreach($organization_ids_selected as $organization_id) {
					if($debug) debug($this->request->data['ProdGasPromotion']);
					if(isset($this->request->data['ProdGasPromotion']['Organization'][$organization_id])) {
					
						$data = [];
						
						$trasport = $this->request->data['ProdGasPromotion']['Organization'][$organization_id]['trasport'];
						$cost_more = $this->request->data['ProdGasPromotion']['Organization'][$organization_id]['costMore'];
						if($debug) debug("Organization $organization_id - trasport $trasport - cost_more $cost_more");
						
						/*
						 * cerco se esiste gia' un occorrenza  
						 */
						$options = [];
						$options['conditions'] = ['ProdGasPromotionsOrganization.organization_id' => (int)$organization_id,  // e' quello del GAS
												  'ProdGasPromotionsOrganization.prod_gas_promotion_id' => $prod_gas_promotion_id];
						$options['order'] = ['ProdGasPromotionsOrganization.id'];
						$options['recursive'] = -1;
						$prodGasPromotionsOrganizationResults = $ProdGasPromotionsOrganization->find('first', $options);
						if($debug) debug($prodGasPromotionsOrganizationResults);
						if(!empty($prodGasPromotionsOrganizationResults)) {
							$data['ProdGasPromotionsOrganization']['id'] = $prodGasPromotionsOrganizationResults['ProdGasPromotionsOrganization']['id'];
							$data['ProdGasPromotionsOrganization']['order_id'] = $prodGasPromotionsOrganizationResults['ProdGasPromotionsOrganization']['order_id'];
							$data['ProdGasPromotionsOrganization']['nota_supplier'] = '';
							$data['ProdGasPromotionsOrganization']['nota_user'] = '';
							$data['ProdGasPromotionsOrganization']['user_id'] = 0;
						}	
						else {
							$data['ProdGasPromotionsOrganization']['order_id'] = 0;	
							$data['ProdGasPromotionsOrganization']['nota_supplier'] = '';
							$data['ProdGasPromotionsOrganization']['nota_user'] = '';
							$data['ProdGasPromotionsOrganization']['user_id'] = 0;
						}							 				
										
						$data['ProdGasPromotionsOrganization']['prod_gas_promotion_id'] = $prod_gas_promotion_id;	
						$data['ProdGasPromotionsOrganization']['organization_id'] = $organization_id;    // e' quello del GAS
						$data['ProdGasPromotionsOrganization']['hasTrasport'] = 'N';
						$data['ProdGasPromotionsOrganization']['trasport'] = '0.00';							
						$data['ProdGasPromotionsOrganization']['hasCostMore'] = 'N';	
						$data['ProdGasPromotionsOrganization']['cost_more'] = '0.00';
						
						if($debug) debug($data);
						$ProdGasPromotionsOrganization->create();
						if($ProdGasPromotionsOrganization->save($data)) {
							if(!empty($prodGasPromotionsOrganizationResults)) 
								array_push($delete_not_ids, $data['ProdGasPromotionsOrganization']['id']);
							else
								array_push($delete_not_ids, $order_id = $ProdGasPromotionsOrganization->getLastInsertId());				
						}						
					}	
				} // end loop Organization

				/*
				 * delete id esclusi
				 */
				if(!empty($delete_not_ids)) {
					try {
						$sql = "DELETE FROM ".Configure::read('DB.prefix')."prod_gas_promotions_organizations WHERE id NOT IN (".implode(',', $delete_not_ids).") AND prod_gas_promotion_id = ".$prod_gas_promotion_id; 
						self::d($sql, $debug);
						$deleteResults = $ProdGasArticlesPromotion->query($sql);
					}
					catch (Exception $e) {
						CakeLog::write('error',$sql);
					} 	
				}					
			}

			
			if($continue) {
				
				$msg .= __('The ProdGasPromotion has been saved');
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=ProdGasPromotions&action=index_gas_users';

				self::d([$msg, $url], $debug);
				
				$this->Session->setFlash($msg);
				if(!empty($url) && !$debug) $this->myRedirect($url);	
			}
			else {
				$msg = __('The ProdGasPromotion could not be saved. Please, try again.');	
				$this->Session->setFlash($msg);			
			}
		}

		$this->request->data = $this->ProdGasPromotion->getProdGasPromotion($this->user, $prod_gas_promotion_id);
		
		/*
		 * get elenco Article esludendo quelli gia' in promozione
		 */	
		/*
		 * get elenco Article
		 */	
		App::import('Model', 'Article');
		$Article = new Article;
		
		$Article->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$Article->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$Article->unbindModel(['hasAndBelongsToMany' => ['Order', 'ArticlesType']]);
		
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
								  'SuppliersOrganization.id' => $this->user->organization['Supplier']['SuppliersOrganization']['id']];
		$options['order'] = ['Article.name' => 'asc']; 
		$options['recursive'] = 0;					  		
		$articleResults = $Article->find('all', $options);
		self::d($options, $debug);
		self::d($articleResults, $debug);

		if(!empty($this->request->data['ProdGasArticlesPromotion'])) {
			
			$prodGasArticlesPromotion = $this->request->data['ProdGasArticlesPromotion'];  // copio perche' dopo faccio unset
			
			foreach($articleResults as $numResults => $articleResult) {
				
				foreach($this->request->data['ProdGasArticlesPromotion'] as $numResults2 => $prodGasArticlesPromotion) {
					if($articleResult['Article']['organization_id']==$prodGasArticlesPromotion['Article']['organization_id'] && $articleResult['Article']['id']==$prodGasArticlesPromotion['Article']['id']) {
						unset($articleResults[$numResults]);
						unset($prodGasArticlesPromotion[$numResults]);
					}
				}
			}
		} // end if(!empty($this->request->data['ProdGasArticlesPromotion']))
		
		$this->set(compact('articleResults'));	 

		/*
		 * non + gestito
		if(!empty($this->request->data['ProdGasPromotion']['img1']) && 
		   file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$this->request->data['ProdGasPromotion']['supplier_id'].DS.$this->request->data['ProdGasPromotion']['img1'])) {
			
			$file1 = new File(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$this->request->data['ProdGasPromotion']['supplier_id'].DS.$this->request->data['ProdGasPromotion']['img1']);
			$this->set('file1', $file1);
		}	
		*/
		
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

	public function admin_view_gas($prod_gas_promotion_id) {
	
		$debug=false;
		
		if(!$this->user->organization['Organization']['hasPromotionGas']=='Y') {
			$this->Session->setFlash(__('msg_prodgas_promotion_acl_no'));
			$this->myRedirect(Configure::read('routes_msg_stop'));		
		}

		if (empty($prod_gas_promotion_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$this->request->data = $this->ProdGasPromotion->getProdGasPromotion($this->user, $prod_gas_promotion_id);
		
		/*
		 * get elenco Article
		 */	
		App::import('Model', 'Article');
		$Article = new Article;
		
		$Article->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$Article->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$Article->unbindModel(['hasAndBelongsToMany' => ['Order', 'ArticlesType']]);
		
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
								  'SuppliersOrganization.id' => $this->user->organization['Supplier']['SuppliersOrganization']['id']];
		$options['order'] = ['Article.name' => 'asc']; 
		$options['recursive'] = 0;					  		
		$articleResults = $Article->find('all', $options);
		self::d($options, $debug);
		self::d($articleResults, $debug);

		if(!empty($this->request->data['ProdGasArticlesPromotion'])) {
			
			$prodGasArticlesPromotion = $this->request->data['ProdGasArticlesPromotion'];  // copio perche' dopo faccio unset
			
			foreach($articleResults as $numResults => $articleResult) {
				
				foreach($this->request->data['ProdGasArticlesPromotion'] as $numResults2 => $prodGasArticlesPromotion) {
					if($articleResult['Article']['organization_id']==$prodGasArticlesPromotion['Article']['organization_id'] && $articleResult['Article']['id']==$prodGasArticlesPromotion['Article']['id']) {
						unset($articleResults[$numResults]);
						unset($prodGasArticlesPromotion[$numResults]);
					}
				}
			}
		} // end if(!empty($this->request->data['ProdGasArticlesPromotion']))
		
		$this->set(compact('articleResults'));	 

		/*
		 * non + gestito
		if(!empty($this->request->data['ProdGasPromotion']['img1']) && 
		   file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$this->request->data['ProdGasPromotion']['supplier_id'].DS.$this->request->data['ProdGasPromotion']['img1'])) {
			
			$file1 = new File(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$this->request->data['ProdGasPromotion']['supplier_id'].DS.$this->request->data['ProdGasPromotion']['img1']);
			$this->set('file1', $file1);
		}	
		*/
		
		/*
		 * get elenco Organizations
		*/
		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;	
		
		$organizationResults = $ProdGasSupplier->getOrganizationsAssociateWithDeliveries($this->user, $prod_gas_promotion_id, $debug);
		$organizationNotResults = $ProdGasSupplier->getOrganizationsNotAssociate($this->user, $debug);
	
		$this->set('organizationResults',$organizationResults);		
		$this->set('organizationNotResults',$organizationNotResults);	
	}

	public function admin_delete($prod_gas_promotion_id, $type='') {

		$debug = false;
		self::d($this->request->data, $debug);
		
		if(isset($this->request->data['ProdGasPromotion']['prod_gas_promotion_id']))
			$prod_gas_promotion_id = $this->request->data['ProdGasPromotion']['prod_gas_promotion_id'];

		if(isset($this->request->data['ProdGasPromotion']['type']))
			$type = $this->request->data['ProdGasPromotion']['type'];
		
		if (empty($prod_gas_promotion_id) || empty($type)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		 
		$results = $this->ProdGasPromotion->getProdGasPromotion($this->user, $prod_gas_promotion_id);
		$this->set('results', $results);
		$this->set('type', $type);

		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->ProdGasPromotion->id = $prod_gas_promotion_id;
			if ($this->ProdGasPromotion->delete()) {
				$msg = __('Delete ProdGasPromotion');
				$this->Session->setFlash($msg);				
			}
			
			if($type=='GAS')
				$this->myRedirect(['action' => 'index_gas']);
			else
			if($type=='GAS-USERS')
				$this->myRedirect(['action' => 'index_gas_users']);
		}
	}
	
	/*
	 * stato da WORKING a TRASMISSION-TO-GAS
	 */
	public function admin_trasmission_to_gas($prod_gas_promotion_id) {

		if(!$this->user->organization['Organization']['hasPromotionGas']=='Y') {
			$this->Session->setFlash(__('msg_prodgas_promotion_acl_no'));
			$this->myRedirect(Configure::read('routes_msg_stop'));		
		}

		$debug = false;
		$continua=true;
		
		if($debug) debug($this->request->data);
		
		if (empty($prod_gas_promotion_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		 
		$results = $this->ProdGasPromotion->getProdGasPromotion($this->user, $prod_gas_promotion_id);
		$this->set(compact('results', 'prod_gas_promotion_id'));
		
		/*
		 * get elenco Organizations
		*/
		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;	
		
		App::import('Model', 'MailsProdGasPromotionSend'); 
		$MailsProdGasPromotionSend = new MailsProdGasPromotionSend;

		App::import('Model', 'Organization'); 
		$Organization = new Organization;

		$organizationResults = $ProdGasSupplier->getOrganizationsAssociate($this->user, $prod_gas_promotion_id, $debug);
			
		$this->set(compact('organizationResults'));	

		if ($this->request->is('post') || $this->request->is('put')) {

			// debug($this->request->data['ProdGasPromotionsOrganization']);

			App::import('Model', 'ProdGasPromotionsOrganization');
			$ProdGasPromotionsOrganization = new ProdGasPromotionsOrganization;			

			if(isset($this->request->data['ProdGasPromotionsOrganization']['nota_supplier']))
			foreach($this->request->data['ProdGasPromotionsOrganization']['nota_supplier'] as $organization_id => $nota_supplier) {
				
				if(!empty($nota_supplier)) {
					$options = [];
					$options['conditions'] = ['ProdGasPromotionsOrganization.organization_id' => $organization_id,
											   'ProdGasPromotionsOrganization.prod_gas_promotion_id' => $prod_gas_promotion_id];
					$options['recursive'] = -1;
					$prodGasPromotionsOrganizationResults = $ProdGasPromotionsOrganization->find('first', $options);
					
					$prodGasPromotionsOrganizationResults['ProdGasPromotionsOrganization']['nota_supplier'] = $nota_supplier;

					if($debug) debug($options);
					if($debug) debug($prodGasPromotionsOrganizationResults);

					$ProdGasPromotionsOrganization->create();
					if(!$ProdGasPromotionsOrganization->save($prodGasPromotionsOrganizationResults)) {
						$continua=false;
					}
				} // end if(!empty($nota_supplier)) 

				/*
				 * invio mail al super-referente e referente GAS
				 */
				if($continua) {
					$tmp_user = $Organization->getOrganization($organization_id);
					$options = [];
					$options['nota_supplier'] = $nota_supplier;
					$results = $MailsProdGasPromotionSend->trasmissionToGas($tmp_user, $organization_id, $prod_gas_promotion_id, $options, $debug);
				}				

			} // loops nota_supplier
			
			$this->ProdGasPromotion->settingStateCode($this->user, $prod_gas_promotion_id, 'PRODGASPROMOTION-GAS-TRASMISSION-TO-GAS', $debug);
			$this->Session->setFlash(__('ProdGasPromotion in TRASMISSION-TO-GAS'));
			if(!$debug) $this->myRedirect(['action' => 'index_gas']);
		} // end post
	}

	public function admin_change_state_code($prod_gas_promotion_id, $next_code='') {

		$debug = false;

		if(!$this->user->organization['Organization']['hasPromotionGas']=='Y') {
			$this->Session->setFlash(__('msg_prodgas_promotion_acl_no'));
			$this->myRedirect(Configure::read('routes_msg_stop'));		
		}

		if($debug) debug($this->request->data);
		
		if(isset($this->request->data['ProdGasPromotion']['prod_gas_promotion_id']))
			$prod_gas_promotion_id = $this->request->data['ProdGasPromotion']['prod_gas_promotion_id'];
		
		if(isset($this->request->data['ProdGasPromotion']['next_code']))
			$next_code = $this->request->data['ProdGasPromotion']['next_code'];
		
		if (empty($prod_gas_promotion_id) || empty($next_code)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		 
		$results = $this->ProdGasPromotion->getProdGasPromotion($this->user, $prod_gas_promotion_id);
		$this->set('results', $results);
		
		$this->set('next_code', $next_code);
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$msg = '';
			switch($next_code) {
				case "PRODGASPROMOTION-GAS-WORKING":
				case "PRODGASPROMOTION-GAS-USERS-WORKING":
					$msg = __('ProdGasPromotion in WORKING');
				break;
				case "PRODGASPROMOTION-GAS-USERS-OPEN":
					$msg = __('ProdGasPromotion in OPEN');
				break;
				default:
					$this->Session->setFlash(__('msg_error_params'));
					$this->myRedirect(Configure::read('routes_msg_exclamation'));			
				break;
			}
		
			$this->ProdGasPromotion->settingStateCode($this->user, $prod_gas_promotion_id, $next_code, $debug);
			$this->Session->setFlash($msg);


			$type = $results['ProdGasPromotion']['type'];

			if($type=='GAS')
				if(!$debug) $this->myRedirect(['action' => 'index_gas']);
			if($type=='GAS-USERS')
				if(!$debug) $this->myRedirect(['action' => 'index_gas_users']);
		}
	}	
}