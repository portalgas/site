<?php
App::uses('AppController', 'Controller');

class AjaxGasCartsController extends AppController {
	
	public $helpers = ['App',
						'Html',
						'Form',
						'Time',
						'Ajax',
						'Tabs'];
	
	public function beforeFilter() {
    	$this->ctrlHttpReferer();
		
    	parent::beforeFilter();
    }

    /*
     * richiamate da genericFrontEnd.js front-end
    * user_id in Session
    */
    public function managementCartSimple($rowId, $order_id=0, $article_organization_id=0, $article_id=0, $qta=0) {
    	$this->_managementCart($rowId, $order_id, $article_organization_id, $article_id, $qta);
    	$this->_readRow($rowId, $order_id, $article_organization_id, $article_id);
    	
    	$this->layout = 'ajax';
    	$this->render('/Layouts/AjaxGas/rowecomm_frontend_simple');
    }

    public function managementCartValidationSimple($rowId, $order_id=0, $article_organization_id=0, $article_id=0, $qta=0) {
    	
		$debug = false;
		$continua = true;
		    
		$user_id = $this->user->get('id');
			
		/*
		 * concorrenza tra users, ctrl che non sia gia' completato il collo
	 	 */
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;

		$options = [];
		$options['conditions'] = ['ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
								   'ArticlesOrder.order_id' => $order_id,
								   'ArticlesOrder.article_organization_id' => $article_organization_id,
								   'ArticlesOrder.article_id' => $article_id,
								   'ArticlesOrder.stato != ' => 'N',
									'Cart.organization_id' => $this->user->organization['Organization']['id'],
									'Cart.order_id' => $order_id,
									'Cart.article_organization_id' => $article_organization_id,
									'Cart.article_id' => $article_id,
									'Cart.user_id' => $user_id];
		$options['recursive'] = 0;
		$ArticlesOrder->unbindModel(array('belongsTo' => array('Order', 'Article')));
		$results = $ArticlesOrder->find('first', $options);
		
		self::d($results, $debug);
		
		if(!empty($results)) {
			$qta_prima_modifica = $this->AjaxGasCart->getQtaPrimaModifica($user, $results);
			
			self::d(" qta $qta - qta_prima_modifica $qta_prima_modifica", $debug);
			
			/*
			 * solo se aggiungo in acquisto
			 */
			if($qta > $qta_prima_modifica) {
				$qta_cart = $results['ArticlesOrder']['qta_cart'];
				$pezzi_confezione = $results['ArticlesOrder']['pezzi_confezione'];

				self::d(" qta_cart $qta_cart - pezzi_confezione $pezzi_confezione", $debug);
				
				if($qta_cart >= $pezzi_confezione) {
					/*
					 * ctrl se collo completato
					 */					
					$delta = ($qta_cart % $pezzi_confezione); 
					self::d(" delta (qta_cart % pezzi_confezione) $delta", $debug);
					
					if($delta==0)
						$continua = false;
					
				} // end if($qta_cart >= $pezzi_confezione)
			} // if($qta > $qta_prima_modifica)
		}	 
	
		if(!$continua) {
			$resultsJS = '<script type="text/javascript">alert("Il collo è stato completato da un\'altro gasista!");jQuery("#row-'.$rowId.'").hide("slow");</script>';
			$this->set('resultsJS',$resultsJS);					
		}
		else {
			$this->_managementCart($rowId, $order_id, $article_organization_id, $article_id, $qta);
			$this->_readRowValidation($rowId, $order_id, $article_organization_id, $article_id);
		}
		
    	$this->layout = 'ajax';
    	$this->render('/Layouts/AjaxGas/rowecomm_frontend_validation_simple');
    }
    
    public function managementCartComplete($rowId, $order_id=0, $article_organization_id=0, $article_id=0, $qta=0) {
    	$this->_managementCart($rowId, $order_id, $article_organization_id, $article_id, $qta);
    	$this->_readRow($rowId, $order_id, $article_organization_id, $article_id);
    	
    	$this->layout = 'ajax';
    	$this->render('/Layouts/AjaxGas/rowecomm_frontend_complete');
    }   
	
    public function managementCartPromotion($rowId, $order_id=0, $article_organization_id=0, $article_id=0, $qta=0) {
    	
		$debug = false;
		$continua = true;
		    
		$user_id = $this->user->get('id');
			
		/*
		 * concorrenza tra users, ctrl che non sia gia' completata la promozione
	 	 */
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;

		$options = [];
		$options['conditions'] = ['ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
								   'ArticlesOrder.order_id' => $order_id,
								   'ArticlesOrder.article_organization_id' => $article_organization_id,
								   'ArticlesOrder.article_id' => $article_id,
								   'ArticlesOrder.stato != ' => 'N',
									'Cart.organization_id' => $this->user->organization['Organization']['id'],
									'Cart.order_id' => $order_id,
									'Cart.article_organization_id' => $article_organization_id,
									'Cart.article_id' => $article_id,
									'Cart.user_id' => $user_id];
		$options['recursive'] = 0;
		$ArticlesOrder->unbindModel(array('belongsTo' => array('Order', 'Article')));
		$results = $ArticlesOrder->find('first', $options);
		
		self::d($results, $debug);
				
		if(!empty($results)) {
			$qta_prima_modifica = $this->AjaxGasCart->getQtaPrimaModifica($user, $results);
			
			self::d(" qta $qta - qta_prima_modifica $qta_prima_modifica", $debug);
				
			/*
			 * solo se aggiungo in acquisto
			 */
			if($qta > $qta_prima_modifica) {
				
				 /*
				 * dati dell'ordine
				 */
				App::import('Model', 'Order');
				$Order = new Order;

				$options = [];
				$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
											'Order.id' => $order_id,
											'Order.state_code' => 'OPEN'];
				$options['recursive'] = -1;
				$order = $Order->find('first', $options);
				$prod_gas_promotion_id = $order['Order']['prod_gas_promotion_id'];
				$prodGasArticlesPromotionQta = 0;	
				self::d(" prod_gas_promotion_id $prod_gas_promotion_id", $debug);
							
				if(!empty($prod_gas_promotion_id)) {
					App::import('Model', 'ProdGasArticlesPromotion');
					$ProdGasArticlesPromotion = new ProdGasArticlesPromotion;		

					$sql = "SELECT ProdGasArticlesPromotion.qta FROM 
							".Configure::read('DB.prefix')."prod_gas_articles_promotions as ProdGasArticlesPromotion,
							".Configure::read('DB.prefix')."articles as Article 
							WHERE ProdGasArticlesPromotion.prod_gas_promotion_id = ".$prod_gas_promotion_id." 
							AND ProdGasArticlesPromotion.prod_gas_article_id = Article.prod_gas_article_id 
							AND Article.organization_id = ".$article_organization_id." 
							AND Article.id = ".$article_id;
					self::d($sql, $debug);
					$prodGasArticlesPromotionResults = $Order->query($sql);	

					if(!empty($prodGasArticlesPromotionResults))
						$prodGasArticlesPromotionQta = $prodGasArticlesPromotionResults[0]['ProdGasArticlesPromotion']['qta'];	
					
				} // end if(!empty($prod_gas_promotion_id))

				$qta_cart = $results['ArticlesOrder']['qta_cart'];
				
				self::d(" prodGasArticlesPromotionQta $prodGasArticlesPromotionQta", $debug);
				
				if($qta_cart >= $prodGasArticlesPromotionQta) 
					$continua = false;
				
			} // if($qta > $qta_prima_modifica)
		}	 
	
		if(!$continua) {
			$resultsJS = '<script type="text/javascript">alert("La promozione è stata completata da un\'altro gasista!");jQuery("#row-'.$rowId.'").hide("slow");</script>';
			$this->set('resultsJS',$resultsJS);					
		}
		else {		
			$this->_managementCart($rowId, $order_id, $article_organization_id, $article_id, $qta);
			$this->_readRowPromotion($rowId, $order_id, $article_organization_id, $article_id);
		}
		
    	$this->layout = 'ajax';
    	$this->render('/Layouts/AjaxGas/rowecomm_frontend_promotion');
    }    
    
    private function _managementCart($rowId, $order_id=0, $article_organization_id=0, $article_id=0, $qta=0) {
   
    	$user_id = $this->user->get('id');

    	if(empty($order_id) || empty($article_organization_id) || empty($article_id) || empty($user_id)) {
    		$this->Session->setFlash(__('msg_error_params'));
    		$this->myRedirect(Configure::read('routes_msg_exclamation'));
    	}
    
    	$resultsJS = $this->AjaxGasCart->managementCart($this->user, $order_id, $article_organization_id, $article_id, $user_id, $qta, $backOffice=false);
    	
    	/*
    	 * gestione JavaScript
    	* */
    	$resultsJS = '<script type="text/javascript">'.sprintf($resultsJS,$rowId).'</script>';
    	$this->set('resultsJS',$resultsJS);
    	 
    	/*
    	 * rowId creato in RowEcommHelper::__setRowId()
    	 * 			articlesOrder.order_id articlesOrder.article_id articlesOrder.stato numArticlesOrder	 
    	 */ 
    	list($order_id, $article_organization_id, $article_id, $article_stato, $numArticlesOrder) = explode("_", $rowId);
    	$this->set('numArticlesOrder',$numArticlesOrder);
    }
    
    /*
     * rileggo i dati della riga
     * Order.state_code = OPEN
    */
    private function _readRow($rowId, $order_id, $article_organization_id, $article_id, $debug=false) {
    
		self::l('AjaxGasCartController::_readRow', $debug);
		
    	$user_id = $this->user->get('id');
    	
    	$results = [];
    
    	/*
    	 * dati dell'ordine
    	*/
    	App::import('Model', 'Order');
    	$Order = new Order;
    	
    	$options = [];
    	$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
								  'Order.id' => $order_id,
								  'Order.state_code' => 'OPEN'];
    	$options['recursive'] = -1;
    	$order = $Order->find('first', $options);
		self::l($order, $debug);
    	$this->set('order', $order);
    	 
    	if(!empty($order)) {
    		App::import('Model', 'ArticlesOrder');
    		$ArticlesOrder = new ArticlesOrder;
    
    		$options = [];
    		$options['conditions'] = ['Cart.user_id' => $user_id, 
									'Article.id' => $article_id];
    		$results = current($ArticlesOrder->getArticoliEventualiAcquistiInOrdine($this->user, $order_id, $article_organization_id, $options));
    	} // end if(!empty($order))
    	$this->set('results',$results);
	
	   self::l($options['conditions'], $debug);
	   self::l($results, $debug);
    }

    /*
     * rileggo i dati della riga per le PROMOTION
    */
    private function _readRowPromotion($rowId, $order_id, $article_organization_id, $article_id) {
    	
    	$user_id = $this->user->get('id');
    	
    	$results = [];
    
    	/*
    	 * dati dell'ordine
    	*/
    	App::import('Model', 'Order');
    	$Order = new Order;
    	
    	$options = [];
    	$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
									'Order.id' => $order_id,
									'Order.state_code' => 'OPEN'];
    	$options['recursive'] = -1;
    	$order = $Order->find('first', $options);
    	$this->set('order', $order);
    	 
    	if(!empty($order)) {
			$prod_gas_promotion_id = $order['Order']['prod_gas_promotion_id']; 
			
    		App::import('Model', 'ArticlesOrder');
    		$ArticlesOrder = new ArticlesOrder;
    
    		$options = [];
    		$options['conditions'] = ['Cart.user_id' => $user_id,
										'ArticlesOrder.order_id' => $order_id,
										'Article.organization_id' => $article_organization_id,
										'Article.id' => $article_id];
    		$results = current($ArticlesOrder->getArticoliEventualiAcquistiInOrdinePromotion($this->user, $order_id, $prod_gas_promotion_id, $options));
    	} // end if(!empty($order))
    	$this->set('results',$results);
    }
	
    /*
     * rileggo i dati della riga 
     * Order.state_code = RI-OPEN-VALIDATE
    */
    private function _readRowValidation($rowId, $order_id, $article_organization_id, $article_id) {
    	 
    	$user_id = $this->user->get('id');
    	 
    	$results = [];
    
    	/*
    	 * dati dell'ordine
    	*/
    	App::import('Model', 'Order');
    	$Order = new Order;
    
    	$options = [];
    	$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
								'Order.id' => $order_id,
								'Order.state_code' => 'RI-OPEN-VALIDATE'];
    	$options['recursive'] = -1;
    	$order = $Order->find('first', $options);
    	$this->set('order', $order);
    
    	if(!empty($order)) {
    		App::import('Model', 'ArticlesOrder');
    		$ArticlesOrder = new ArticlesOrder;
    
    		$options['conditions'] = ['Cart.user_id' => $user_id,
    								  'Article.id' => $article_id];
    		$results = current($ArticlesOrder->getArticoliEventualiAcquistiInOrdine($this->user, $order_id, $article_organization_id, $options));
    		
    		/*
    		 * differenza da ordinare 
    		 * da $Cart->getCartToValidateFrontEnd($this->user, $delivery_id, $order_id);
    		 */
    		$differenza_da_ordinare = ($results['ArticlesOrder']['qta_cart'] % $results['ArticlesOrder']['pezzi_confezione']);
    		
    		if($differenza_da_ordinare>0) {
    			$differenza_da_ordinare = ($results['ArticlesOrder']['pezzi_confezione'] - $differenza_da_ordinare);
    			$differenza_importo = ($differenza_da_ordinare * $results['ArticlesOrder']['prezzo']);
    							
    			$results['ArticlesOrder']['differenza_da_ordinare'] = $differenza_da_ordinare;
    			$results['ArticlesOrder']['differenza_importo'] = $differenza_importo;   		
    		}
    		else {
    			$results['ArticlesOrder']['differenza_da_ordinare'] = 0;
    			$results['ArticlesOrder']['differenza_importo'] = 0;    			
    		}
    	} // end if(!empty($order))
    	$this->set('results',$results);
    }
    
    /*
     * richiamata da ecommRows.js back-office
     * reportOptions 		   report-users-cart (ALL oppure $user_id) report-users-all report-articles-details
     * reportOptionsSub     se report-users-cart = ALL 
     */
    public function admin_managementCart($rowId, $order_id, $article_organization_id, $article_id, $user_id=0, $qta=0, $reportOptions, $reportOptionsSub=null) {
   	 
    	if(empty($order_id) || empty($article_id) || empty($article_organization_id) || empty($user_id)) {
    		$this->Session->setFlash(__('msg_error_params'));
    		$this->myRedirect(Configure::read('routes_msg_exclamation'));
    	}

    	$resultsJS = $this->AjaxGasCart->managementCart($this->user, $order_id, $article_organization_id, $article_id, $user_id, $qta, $backOffice=true);
    	
    	/*
    	 * gestione JavaScript
    	 * */
    	$resultsJS = '<script type="text/javascript">'.sprintf($resultsJS,$rowId).'</script>';
     	$this->set('resultsJS',$resultsJS);
    	
     	/*
     	 * gestione RowEcomm
     	 */
    	$this->set('results',$this->_prepareResultsToRowEcomm($order_id, $article_organization_id, $article_id, $user_id, $qta));

		/*
    	 * rowId creato in RowEcommHelper::__setRowId()
    	 * 			articlesOrder.order_id articlesOrder.article_id  articlesOrder.stato numArticlesOrder	 
    	 */ 
    	list($order_id, $article_organization_id, $article_id, $article_stato, $numArticlesOrder) = explode("_", $rowId);
    	$this->set('numArticlesOrder',$numArticlesOrder);

    	/*
    	 * permission per abilitazione modifica del carrello
    	 */
		$permissions = ['isReferentGeneric' => $this->isReferentGeneric(),
						'isTesoriereGeneric' => $this->isTesoriereGeneric()];
		$this->set('permissions',$permissions);		
		
		/*
		 * ricalcolo SummaryOrders se esiste, NON + utilizzato, function vuota
		 */
		App::import('Model', 'SummaryOrder');
		$SummaryOrder = new SummaryOrder;
		$SummaryOrder->ricalcolaPerSingoloUtente($this->user, $order_id, $user_id);
		
    	$this->layout = 'ajax';
    	if($reportOptions=='report-articles-details')
	    	$this->render('/Layouts/AjaxGas/rowecomm_backoffice_report_articles_details');
    	else
   		if($reportOptions=='report-users-cart' && $reportOptionsSub=='ALL')
    		$this->render('/Layouts/AjaxGas/rowecomm_backoffice_report_articles_details');
    	else
    	if($reportOptions=='report-users-cart')
    		$this->render('/Layouts/AjaxGas/rowecomm_backoffice_report_users');
    	else 
    	if($reportOptions=='report-users-all')
    		$this->render('/Layouts/AjaxGas/rowecomm_backoffice_report_users');	 
    }   
    
    /*
     * utilizzata solo da back-office
     */
    private function _prepareResultsToRowEcomm($order_id, $article_organization_id, $article_id, $user_id, $qta) {

    	/*
	   	 * rileggo la riga dal database aggiornata ([Order] [Article] [ArticlesOrder] [Cart] [User])
	   	*/
    	App::import('Model', 'ArticlesOrder');    	$ArticlesOrder = new ArticlesOrder;    		
	   	$conditions = ['Cart.user_id' => $user_id,
						'Cart.order_id' => $order_id,
						'Cart.article_organization_id' => $article_organization_id,
						'Cart.article_id' => $article_id];
	   	$results = $ArticlesOrder->getArticoliDellUtenteInOrdine($this->user ,$conditions);
	   	$results = current($results);

	   	/*
	   	 * oggetto $order formattato per $this->RowEcomm->drawRowEcomm...
	   	 * */
	   	$order = ['Order' => $results['Order'],
				   'ArticlesOrder' => $results['ArticlesOrder'],
				   'Article' => $results['Article'],
				   'Cart' => $results['Cart'],
				   'User' => $results['User']];
    	
    	self::d($order, false);
    	
    	return $order;
    }
}  