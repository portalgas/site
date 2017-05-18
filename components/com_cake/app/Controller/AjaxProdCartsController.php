<?php
App::uses('AppController', 'Controller');

class AjaxProdCartsController extends AppController {
	
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
     * richiamata da genericFrontEndProd.js front-end
     * user_id in Session
     */
    public function managementCartSimple($rowId, $prod_delivery_id=0, $article_organization_id=0, $article_id=0, $qta=0) {
    	$this->__managementCart($rowId, $prod_delivery_id, $article_organization_id, $article_id, $qta);
    	 
    	$this->layout = 'ajax';
    	$this->render('/Layouts/AjaxProd/rowecomm_prod_frontend_simple');
    }
     
    public function managementCartComplete($rowId, $prod_delivery_id=0, $article_organization_id=0, $article_id=0, $qta=0) {
    	$this->__managementCart($rowId, $prod_delivery_id, $article_organization_id, $article_id, $qta);
    
    	$this->layout = 'ajax';
    	$this->render('/Layouts/AjaxProd/rowecomm_prod_frontend_complete');
    }

    private function __managementCart($rowId, $prod_delivery_id=0, $article_organization_id=0, $article_id=0, $qta=0) {
    	 
    	$user_id = $this->user->get('id');
    
    	if(empty($prod_delivery_id) || empty($article_organization_id) || empty($article_id) || empty($user_id)) {
    		$this->Session->setFlash(__('msg_error_params'));
    		$this->myRedirect(Configure::read('routes_msg_exclamation'));
    	}
    
    	$resultsJS = $this->AjaxProdCart->managementCart($this->user, $prod_delivery_id, $article_organization_id, $article_id, $user_id, $qta, $backOffice=false);
    	 
    	/*
    	 * gestione JavaScript
    	* */
    	$resultsJS = '<script type="text/javascript">'.sprintf($resultsJS,$rowId).'</script>';
    	$this->set('resultsJS',$resultsJS);
    
    
    	/*
    	 * rileggo i dati della riga
    	*/
    	$results = array();
    
    	/*
    	 * dati della consegna
    	*/
    	App::import('Model', 'ProdDelivery');
    	$ProdDelivery = new ProdDelivery;
    	 
    	$options['conditions'] = array('ProdDelivery.organization_id' => $this->user->organization['Organization']['id'],
						    			'ProdDelivery.id' => $prod_delivery_id,
						    			'ProdDelivery.prod_delivery_state_id' => Configure::read('OPEN'));
    	$options['recursive'] = -1;
    	$prodDeliveryResults = $ProdDelivery->find('first', $options);
    	$this->set('prodDeliveryResults', $prodDeliveryResults);
    
    	if(!empty($prodDeliveryResults)) {
    		App::import('Model', 'ProdDeliveriesArticle');
    		$ProdDeliveriesArticle = new ProdDeliveriesArticle;
    		 
    		$options['conditions'] = array('ProdCart.user_id' => $user_id,
						    				'ProdDeliveriesArticle.prod_delivery_id' => $prod_delivery_id,
						    				'ProdDeliveriesArticle.organization_id' => $article_organization_id,
						    				'ProdDeliveriesArticle.id' => $article_id);
    		$results = current($ProdDeliveriesArticle->getArticoliEventualiAcquistiInConsegna($this->user, $options));
    	} // end if(!empty($order))
    	$this->set('results',$results);
    
    	/*
    	 * rowId creato in RowEcommHelper::__setRowId()
    	* 			articlesOrder.order_id articlesOrder.article_id articlesOrder.stato numProdDeliveriesArticle
    	*/
    	list($prod_delivery_id, $article_organization_id, $article_id, $article_stato, $numProdDeliveriesArticle) = explode("_", $rowId);
    	$this->set('numProdDeliveriesArticle',$numProdDeliveriesArticle);
    }

    /*
     * richiamata da ecommRows.js back-office
    * reportOptions 		   report-users-cart (ALL oppure $user_id) report-users-all report-articles-details
    * reportOptionsSub     se report-users-cart = ALL
    */
    public function admin_managementCart($rowId, $prod_delivery_id, $article_organization_id, $article_id, $user_id=0, $qta=0, $reportOptions, $reportOptionsSub=null) {

    	$user_id = $this->user->get('id');

    	if(empty($prod_delivery_id) || empty($article_organization_id) || empty($article_id) || empty($user_id)) {
    		$this->Session->setFlash(__('msg_error_params'));
    		$this->myRedirect(Configure::read('routes_msg_exclamation'));
    	}

    	$resultsJS = $this->AjaxProdCart->managementCart($this->user, $prod_delivery_id, $article_organization_id, $article_id, $user_id, $qta);
    	
    	/*
    	 * gestione JavaScript
    	* */
    	$resultsJS = '<script type="text/javascript">'.sprintf($resultsJS,$rowId).'</script>';
    	$this->set('resultsJS',$resultsJS);
    	 
    	/*
    	 * gestione RowEcomm
    	* */
    	$this->set('results',$this->__prepareResultsToRowEcomm($prod_delivery_id, $article_organization_id, $article_id, $user_id, $qta));    	     	
    	/*
    	 * rowId creato in RowEcommHelper::__setRowId()
    	 * 			prodDeliveriesArticle.prod_delivery_id prodDeliveriesArticle.article_id  prodDeliveriesArticle.stato numProdDeliveriesArticle	 
    	 */ 
    	list($prod_delivery_id, $article_organization_id, $article_id, $article_stato, $numProdDeliveriesArticle) = explode("_", $rowId);
    	$this->set('numProdDeliveriesArticle',$numProdDeliveriesArticle);
    	
    	$this->layout = 'ajax';
    	if($reportOptions=='report-articles-details')
    		$this->render('/Layouts/AjaxProd/rowecomm_backoffice_report_articles_details');
    	else
    	if($reportOptions=='report-users-cart' && $reportOptionsSub=='ALL')
    		$this->render('/Layouts/AjaxProd/rowecomm_backoffice_report_articles_details');
    	else
    	if($reportOptions=='report-users-cart')
    		$this->render('/Layouts/AjaxProd/rowecomm_backoffice_report_users');
    	else
    	if($reportOptions=='report-users-all')
    		$this->render('/Layouts/AjaxProd/rowecomm_backoffice_report_users');    	
    }
    
    /*
     * richiamata da ecommRows.js back-office
     * reportOptions 		   report-users-cart (ALL oppure $user_id) report-users-all report-articles-details
     * reportOptionsSub     se report-users-cart = ALL 
     */
    public function admin_managementCart2($rowId, $prod_delivery_id, $article_organization_id, $article_id, $user_id=0, $qta=0, $reportOptions, $reportOptionsSub=null) {
 
    	if(empty($prod_delivery_id) || empty($article_id) || empty($article_organization_id) || empty($user_id)) {
    		$this->Session->setFlash(__('msg_error_params'));
    		$this->myRedirect(Configure::read('routes_msg_exclamation'));
    	}

    	$resultsJS = $this->AjaxProdCart->managementCart($this->user, $prod_delivery_id, $article_organization_id, $article_id, $user_id, $qta);
    	
    	/*
    	 * gestione JavaScript
    	 * */
    	$resultsJS = '<script type="text/javascript">'.sprintf($resultsJS,$rowId).'</script>';
     	$this->set('resultsJS',$resultsJS);
    	
     	/*
     	 * gestione RowEcomm
     	 */
    	$this->set('results',$this->__prepareResultsToRowEcomm($prod_delivery_id, $article_organization_id, $article_id, $user_id, $qta));

		/*
    	 * rowId creato in RowEcommHelper::__setRowId()
    	 * 			prodDeliveriesArticle.prod_delivery_id prodDeliveriesArticle.article_id  prodDeliveriesArticle.stato numProdDeliveriesArticle	 
    	 */ 
    	list($prod_delivery_id, $article_organization_id, $article_id, $article_stato, $numProdDeliveriesArticle) = explode("_", $rowId);
    	$this->set('numProdDeliveriesArticle',$numProdDeliveriesArticle);
    	
    	/*
    	 * permission per abilitazione modifica del carrello
    	 */
		$permissions = array('isReferentGeneric' => $this->isReferentGeneric(),
							 'isTesoriereGeneric' => $this->isTesoriereGeneric());
		$this->set('permissions',$permissions);
    	
    	$this->layout = 'ajax';
    	if($reportOptions=='report-articles-details')
	    	$this->render('/Layouts/AjaxProd/rowecomm_backoffice_report_articles_details');
    	else
   		if($reportOptions=='report-users-cart' && $reportOptionsSub=='ALL')
    		$this->render('/Layouts/AjaxProd/rowecomm_backoffice_report_articles_details');
    	else
    	if($reportOptions=='report-users-cart')
    		$this->render('/Layouts/AjaxProd/rowecomm_backoffice_report_users');
    	else 
    	if($reportOptions=='report-users-all')
    		$this->render('/Layouts/AjaxProd/rowecomm_backoffice_report_users');	 
    }   
    
    private function __prepareResultsToRowEcomm($prod_delivery_id, $article_organization_id, $article_id, $user_id, $qta) {
    	    	/*
    	 * rileggo la riga dal database aggiornata ([ProdDelivery] [Article] [ProdDeliveriesArticle])
    	*/
    	if($qta==0) {

    		App::import('Model', 'Article');    		$Article = new Article;    		
    		$results = $Article->getArticleDataAnagrProdDeliveriesArticle($this->user, $article_organization_id, $article_id, $prod_delivery_id);    		

    		$results['ProdCart']['qta'] = 0;
    		$results['ProdCart']['qta_forzato'] = 0;
    		$results['ProdCart']['importo'] = 0;
    		$results['User']['id'] = 0;
    	}
    	else {
	    	/*
	    	 * rileggo la riga dal database aggiornata ([ProdDelivery] [Article] [ProdDeliveriesArticle] [ProdCart] [User])
	    	*/
    		App::import('Model', 'ProdDeliveriesArticle');    		$ProdDeliveriesArticle = new ProdDeliveriesArticle;    		
	    	$conditions = array('ProdCart.user_id' => $user_id,
	    						'ProdCart.prod_delivery_id' => $prod_delivery_id,
    							'ProdCart.article_organization_id' => $article_organization_id,
    							'ProdCart.article_id' => $article_id);
	    	$results = $ProdDeliveriesArticle->getArticoliDellUtenteInConsegna($this->user ,$conditions);
	    	$results = current($results);
    	}
    	
    	/*
    	 echo "<pre>AjaxProdCartsController::__prepareResultsToRowEcomm ";
    	print_r($results);
    	echo "</pre>";
    	*/
    	
    	return $results;
    }
}  