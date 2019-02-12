<?php
App::uses('AppController', 'Controller');

class AjaxCartsController extends AppController {
	
	public $helpers = array('App',
							'Html',
							'Form',
							'Time',
							'Ajax',
							'Tabs');
	
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
    	$this->render('/Layouts/ajax_rowecomm_frontend_simple');
    }

    public function managementCartValidationSimple($rowId, $order_id=0, $article_organization_id=0, $article_id=0, $qta=0) {
    	$this->_managementCart($rowId, $order_id, $article_organization_id, $article_id, $qta);
    	$this->_readRowValidation($rowId, $order_id, $article_organization_id, $article_id);
    	
    	$this->layout = 'ajax';
    	$this->render('/Layouts/ajax_rowecomm_frontend_validation_simple');
    }
    
    public function managementCartComplete($rowId, $order_id=0, $article_organization_id=0, $article_id=0, $qta=0) {
    	$this->_managementCart($rowId, $order_id, $article_organization_id, $article_id, $qta);
    	$this->_readRow($rowId, $order_id, $article_organization_id, $article_id);
    	
    	$this->layout = 'ajax';
    	$this->render('/Layouts/ajax_rowecomm_frontend_complete');
    }    
    
    private function _managementCart($rowId, $order_id=0, $article_organization_id=0, $article_id=0, $qta=0) {
   
    	$user_id = $this->user->get('id');

    	if(empty($order_id) || empty($article_organization_id) || empty($article_id) || empty($user_id)) {
    		$this->Session->setFlash(__('msg_error_params'));
    		$this->myRedirect(Configure::read('routes_msg_exclamation'));
    	}
    
    	$resultsJS = $this->AjaxCart->managementCart($this->user, $order_id, $article_organization_id, $article_id, $user_id, $qta, $backOffice=false);
    	
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
     * Order.order_state_id = OPEN
    */
    private function _readRow($rowId, $order_id, $article_organization_id, $article_id) {
    	
    	$user_id = $this->user->get('id');
    	
    	$results = [];
    
    	/*
    	 * dati dell'ordine
    	*/
    	App::import('Model', 'Order');
    	$Order = new Order;
    	
    	$options = [];
    	$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
    			'Order.id' => $order_id,
    			'Order.order_state_id' => Configure::read('OPEN'));
    	$options['recursive'] = -1;
    	$order = $Order->find('first', $options);
    	$this->set('order', $order);
    	 
    	if(!empty($order)) {
    		App::import('Model', 'ArticlesOrder');
    		$ArticlesOrder = new ArticlesOrder;
    
    		$options = [];
    		$options['conditions'] = array('Cart.user_id' => $user_id,
    				'ArticlesOrder.order_id' => $order_id,
    				'Article.organization_id' => $article_organization_id,
    				'Article.id' => $article_id);
    		$results = current($ArticlesOrder->getArticlesOrderAndCart($this->user, $options));
    	} // end if(!empty($order))
    	$this->set('results',$results);
    }


    /*
     * rileggo i dati della riga 
     * Order.order_state_id = PROCESSED-REFERENTE-BEFORE-DELIVERY
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
    	$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
    			'Order.id' => $order_id,
    			'Order.order_state_id' => Configure::read('PROCESSED-REFERENTE-BEFORE-DELIVERY'));
    	$options['recursive'] = -1;
    	$order = $Order->find('first', $options);
    	$this->set('order', $order);
    
    	if(!empty($order)) {
    		App::import('Model', 'ArticlesOrder');
    		$ArticlesOrder = new ArticlesOrder;
    
    		$options['conditions'] = array('Cart.user_id' => $user_id,
    				'ArticlesOrder.order_id' => $order_id,
    				'Article.organization_id' => $article_organization_id,
    				'Article.id' => $article_id);
    		$results = current($ArticlesOrder->getArticlesOrderAndCart($this->user, $options));
    		
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

    	$resultsJS = $this->AjaxCart->managementCart($this->user, $order_id, $article_organization_id, $article_id, $user_id, $qta, $backOffice=true);
    	
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
		$permissions = array('isReferentGeneric' => $this->isReferentGeneric(),
							 'isTesoriereGeneric' => $this->isTesoriereGeneric());
		$this->set('permissions',$permissions);		
		
    	$this->layout = 'ajax';
    	if($reportOptions=='report-articles-details')
	    	$this->render('/Layouts/ajax_rowecomm_backoffice_report_articles_details');
    	else
   		if($reportOptions=='report-users-cart' && $reportOptionsSub=='ALL')
    		$this->render('/Layouts/ajax_rowecomm_backoffice_report_articles_details');
    	else
    	if($reportOptions=='report-users-cart')
    		$this->render('/Layouts/ajax_rowecomm_backoffice_report_users');
    	else 
    	if($reportOptions=='report-users-all')
    		$this->render('/Layouts/ajax_rowecomm_backoffice_report_users');	 
    }   
    
    /*
     * utilizzata solo da back-office
     */
    private function _prepareResultsToRowEcomm($order_id, $article_organization_id, $article_id, $user_id, $qta) {

    	/*
	   	 * rileggo la riga dal database aggiornata ([Order] [Article] [ArticlesOrder] [Cart] [User])
	   	*/
    	App::import('Model', 'ArticlesOrder');    	$ArticlesOrder = new ArticlesOrder;    		
	   	$conditions = array('Cart.user_id' => $user_id,
	   						'Cart.order_id' => $order_id,
    						'Cart.article_organization_id' => $article_organization_id,
    						'Cart.article_id' => $article_id);
	   	$results = $ArticlesOrder->getArticlesOrdersInOrderAndCartsByUserId($this->user ,$conditions);
	   	$results = current($results);

	   	/*
	   	 * oggetto $order formattato per $this->RowEcomm->drawRowEcomm...
	   	 * */
	   	$order = array('Order' => $results['Order'],
		    		   'ArticlesOrder' => $results['ArticlesOrder'],
		    		   'Article' => $results['Article'],
		    		   'Cart' => $results['Cart'],
		    		   'User' => $results['User']);
    	
    	self::d($order, false);
    	
    	return $order;
    }
}  