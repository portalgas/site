<?php
App::uses('AppController', 'Controller');

class ProdExportDocsController extends AppController {
	
	public $components = array('RequestHandler'); // Include the RequestHandler, it makes sure the proper layout and views files are used
	
	public $helpers = array('App',
							'Html',
							'Form',
							'Time',
							'Ajax',
						    'ExportDocs',
							'PhpExcel');
		
    public function beforeFilter() {
    	$this->ctrlHttpReferer();
    	    	
    	parent::beforeFilter();
    }

    /* 
     * $doc_options = to-prod-users-group, to-users, to-users-label, to-users-all-modify, to-articles, to-articles-monitoring, to-articles-details
     * parametri di Setting
     * 		se  $doc_options=to-prod-users-group $a = user_phone, $b = user_email, $c = user_address, 
     * 		se  $doc_options=to-users-all-modify    $a = trasportAndCost
     * 		se  $doc_options=to-users            $a = user_phone, $b = user_email, $c = user_address, $d = totale_per_utente, $e = trasportAndCost
     * 		se  $doc_options=to-users-label      $a = user_phone, $b = user_email, $c = user_address, $d = trasportAndCost
     * 		se  $doc_options=to-articles         $a = trasportAndCost
     * 		se  $doc_options=to-articles-details $a = acquistato_il, $b = article_img, $c = trasportAndCost
     * $doc_formato = PREVIEW, PDF, CSV, EXCEL
     */
	public function admin_exportToProduttore($prod_delivery_id=0 ,$doc_options=null, $doc_formato=null, $a=null, $b=null, $c=null, $d=null, $e=null) {

		if(empty($this->prod_delivery_id)) {			$this->Session->setFlash(__('msg_error_params'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}
				
		$this->ctrlHttpReferer();
		
		Configure::write('debug',0);

		if($doc_options==null || $doc_formato==null)  {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		/* 
		 * dati consegna
		 */
		App::import('Model', 'ProdDelivery');
		$ProdDelivery = new ProdDelivery;	

		$options = array();
		$options['conditions'] = array('ProdDelivery.organization_id' => (int)$this->user->organization['Organization']['id'],
									   'ProdDelivery.id' => $prod_delivery_id);
		$options['recursive'] = 0;
		$ProdDelivery->unbindModel(array('belongsTo' => array('ProdGroup', 'SuppliersOrganization')));
		$prodDeliveryResults = $ProdDelivery->find('first', $options);
		
		$this->set('prodDeliveryResults', $prodDeliveryResults);
		
		if($doc_options=='to-prod-users-group') {
			
			$prod_group_id = $prodDeliveryResults['ProdDelivery']['prod_group_id'];
			
			/*
			 * estraggo gli utenti della consegna ordinati con la priorita'
			 */	
			App::import('Model', 'ProdUsersGroup');
			$ProdUsersGroup = new ProdUsersGroup;
			
			$options = array();
			$options['conditions'] = array('ProdUsersGroup.organization_id' => (int)$this->user->organization['Organization']['id'],
										   'ProdUsersGroup.prod_group_id' => $prod_group_id,
										   'User.block' => 0);
			$options['order'] = array('ProdUsersGroup.sort');
			$options['recursive'] = 0;
			$ProdUsersGroup->unbindModel(array('belongsTo' => array('ProdGroup', 'SuppliersOrganization')));
			$prodUsersGroupResults = $ProdUsersGroup->find('all', $options);	

			/*
			 * elenco user_id per ordinamento
			 */
			$user_ids = "";
			foreach ($prodUsersGroupResults as $prodUsersGroupResult) 
				$user_ids .= $prodUsersGroupResult['ProdUsersGroup']['user_id'].',';
			$user_ids = substr($user_ids, 0, (strlen($user_ids)-1));
		} // end if($doc_options=='to-prod-users-group') 

		/*
		 * dati acquisti
		 */
		App::import('Model', 'ProdCart');
		$ProdCart = new ProdCart;
		
		$options = array();
		$options['conditions'] = array('ProdCart.organization_id' => (int)$this->user->organization['Organization']['id'],
									   'ProdCart.isVisibleBackOffice' => 'Y',
									   'ProdCart.prod_delivery_id' => $prod_delivery_id);
		
		if($doc_options=='to-users-all-modify' && ($doc_formato=='PREVIEW' || $doc_formato=='PDF')) {}
		else
			$options['conditions'] = array('ProdCart.deleteToReferent' => 'N');		
		$options['recursive'] = 1;
	
		
		if($doc_options=='to-prod-users-group')
			$options['order'] = array('FIELD (ProdCart.user_id, '.$user_ids.')');
		else
		if($doc_options=='to-users' || $doc_options=='to-users-label' || $doc_options=='to-users-all-modify')
			$options['order'] = array('User' => Configure::read('orderUser').', Article.name, Article.id');
		else
		if($doc_options=='to-articles' || $doc_options=='to-articles-monitoring')
			$options['order'] = array('Article.name, Article.id, '.Configure::read('orderUser'));
		else
		if($doc_options=='to-articles-details') {
			if($a=='Y') // acquistato_il
				$options['order'] = array('Article.name, Article.id, Cart.created, '.Configure::read('orderUser'));
			else
				$options['order'] = array('Article.name, Article.id, '.Configure::read('orderUser'));
		}			
		
		$ProdCart->unbindModel(array('belongsTo' => array('ProdDelivery')));
		$results = $ProdCart->find('all', $options);
		
		/*
		 * dati profilo utenti
		 */
		jimport( 'joomla.user.helper' );
		$newResults = array();
		foreach ($results as $numResult => $result) {
			$newResults[$numResult] = $result;
			

			$userTmp = JFactory::getUser($result['User']['id']);
			$userProfile = JUserHelper::getProfile($userTmp->id);
				
			$newResults[$numResult]['User']['Profile'] = $userProfile->profile;
		}
		
		$results = $this->ProdExportDoc->getCartComplite($this->prod_delivery_id, $newResults);
		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
		$this->set('results', $results);
				
		$params = array('prod_delivery_id' => $this->prod_delivery_id);
		$this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options, $params, $user_target='PRODUTTORE'));
		$this->set('organization',$this->user->organization);
		
		/*
		 * setting 
		 */ 
		switch ($doc_options) {
			case 'to-prod-users-group':
				$this->set('user_phone', $a);
				$this->set('user_email', $b);
				$this->set('user_address', $c);
			break;
			case 'to-users-all-modify':
				$this->set('trasportAndCost', $a);
			break;
			case 'to-users':
				$this->set('user_phone', $a);
				$this->set('user_email', $b);
				$this->set('user_address', $c);
				$this->set('totale_per_utente', $d);
				$this->set('trasportAndCost', $e);
			break;
			case 'to-users-label':
				$this->set('user_phone', $a);
				$this->set('user_email', $b);
				$this->set('user_address', $c);
				$this->set('trasportAndCost', $d);
			break;
			case 'to-articles':
				$this->set('trasportAndCost', $a);
			break;
			case 'to-articles-details':
				$this->set('acquistato_il', $a);
				$this->set('article_img', $b);
				$this->set('trasportAndCost', $c);
			break;
		}	
		
		switch ($doc_formato) {
			case 'PREVIEW':
				$this->layout = 'ajax';
				if($doc_options=='to-prod-users-group')
					$this->render('produttore_to_prod_users_group');
				else
				if($doc_options=='to-users')
					$this->render('produttore_to_users');
				else
				if($doc_options=='to-users-label')
					$this->render('produttore_to_users_label');
				else
				if($doc_options=='to-users-all-modify')
					$this->render('produttore_to_users_all_modify');
				else
				if($doc_options=='to-articles')
					$this->render('produttore_to_articles');
				else
				if($doc_options=='to-articles-monitoring')
					$this->render('produttore_to_articles_monitoring');
				else
				if($doc_options=='to-articles-details')
					$this->render('produttore_to_articles_details');
			break;
			case 'PDF':
				$this->layout = 'pdf';
				if($doc_options=='to-prod-users-group')
					$this->render('produttore_to_prod_users_group');
				else				
				if($doc_options=='to-users')
					$this->render('produttore_to_users');
				else
				if($doc_options=='to-users-label')
					$this->render('produttore_to_users_label');
				else
				if($doc_options=='to-users-all-modify')
					$this->render('produttore_to_users_all_modify');				
				else
				if($doc_options=='to-articles')
					$this->render('produttore_to_articles');
				else
				if($doc_options=='to-articles-monitoring')
					$this->render('produttore_to_articles_monitoring');
				else
				if($doc_options=='to-articles-details')
					$this->render('produttore_to_articles_details');
			break;
			case 'CSV':
				$this->layout = 'csv';
				if($doc_options=='to-prod-users-group')
					$this->render('produttore_to_prod_users_group');
				else				
				if($doc_options=='to-users')
					$this->render('produttore_to_users_csv');
				else
				if($doc_options=='to-users-label')
					$this->render('produttore_to_users_label_csv');
				else
				if($doc_options=='to-users-all-modify')
					$this->render('produttore_to_users_all_modify_csv');
				else
				if($doc_options=='to-articles')
					$this->render('produttore_to_articles_csv');
				else
				if($doc_options=='to-articles-monitoring')
					$this->render('produttore_to_articles_monitoring_csv');
				else
				if($doc_options=='to-articles-details')
					$this->render('produttore_to_articles_details_csv');
			break;	
			case 'EXCEL':
				$this->layout = 'excel';
				if($doc_options=='to-prod-users-group')
					$this->render('produttore_to_prod_users_group');
				else				
				if($doc_options=='to-users')
					$this->render('produttore_to_users_excel');
				else
				if($doc_options=='to-users-label')
					$this->render('produttore_to_users_label_excel');
				else
				if($doc_options=='to-users-all-modify')
					$this->render('produttore_to_users_excel');
				else
				if($doc_options=='to-articles')
					$this->render('produttore_to_articles_excel');
				else
				if($doc_options=='to-articles-monitoring')
					$this->render('produttore_to_articles_monitoring_excel');
				else
				if($doc_options=='to-articles-details')
					$this->render('produttore_to_articles_details_excel');
			break;								
		}			
	} 	
}