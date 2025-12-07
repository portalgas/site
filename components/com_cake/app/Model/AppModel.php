<?php
App::uses('Model', 'Model');
App::uses('UtilsCommons', 'Lib');
App::uses('CakeEmail', 'Network/Email');

class AppModel extends Model {

    public $actsAs = ['Enumerable', 'ServiceArticles'];
	public $utilsCommons;
	private $no_organization_id_tables = ['organizations', 'suppliers', 'des', 'des_duppliers'];
	
	public function __construct() {
    	parent::__construct();
    	$this->utilsCommons = new UtilsCommons();
	}    	

	public static function d($var, $debug=false) { // idem in AppController / AppModel / AppHelper
		if($debug) {
			if(is_array ($var)) {
				foreach($var as $k => $v) {
					echo "<pre>";
					print_r($k);
					echo '  ';
					print_r($v);
					echo "</pre>";
				}
			}
			else {			
				echo "<pre>";
				print_r($var);
				echo "</pre>";
			}
		}
	}
	
	public static function dd($var, $debug=true) { // idem in AppController / AppModel / AppHelper
		self::d($var, true);
	}
		
	public static function l($var, $debug=false) { // idem in AppController / AppModel / AppHelper
		if(Configure::read('developer.mode') || $debug) {
			if(is_array ($var)) 
				CakeLog::write('debug', print_r($var, true), ['myDebug']);
			else 
				CakeLog::write('debug', $var, ['myDebug']);
		}
	}
	
	public static function x($var) { // idem in AppController / AppModel / AppHelper
		die($var);
	}
		
	public function getLastQuery()
	{
	    $dbo = $this->getDatasource();
	    $logs = $dbo->getLog();
	    $lastLog = end($logs['log']);
	    return $lastLog['query'];
	}

    /*
     * SuppliersOrganizationOwnerArticles chi gestisce il listino in base a owner_articles
     */
    public function _getOrderById($user, $order_id, $debug=false) {

		/* 
		 * importo un Model perche' se e' richiamato da un Model con public $useTable = false; error
		 */
		App::import('Model', 'Order');
		$Order = new Order;

		$options = [];
		$options['conditions'] = ['Order.organization_id' => (int)$user->organization['Organization']['id'], 'Order.id' => $order_id];
		$options['recursive'] = 0;
        if($debug) debug($options);
		$orderResult = $Order->find('first', $options);
        if($debug) debug($orderResult);

        App::import('Model', 'Supplier');
        $Supplier = new Supplier;

        $options = [];
        $options['conditions'] = ['Supplier.id' => $orderResult['SuppliersOrganization']['supplier_id']];
        $options['fields'] = ['Supplier.img1'];
        $options['recursive'] = -1;
        $supplierResults = $Supplier->find('first', $options);
        $orderResult['Supplier'] = $supplierResults['Supplier'];

        /*
         * estraggo chi gestisce il listino articoli
         */
        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;

        $options = [];
        $options['conditions'] = ['organization_id' => $orderResult['Order']['owner_organization_id'],
                                  'id' => $orderResult['Order']['owner_supplier_organization_id']];
        $options['recursive'] = -1;
        $results = $SuppliersOrganization->find('first', $options);
        $orderResult['SuppliersOrganizationOwnerArticles'] = $results['SuppliersOrganization'];
        /*
         * faccio l'override di owner_articles / owner_organization_id / owner_supplier_organization_id con i dati dell'ordine
         * nel caso dopo la creazione dell'ordine e' cambiata la configurazione del produttore
         */
        $orderResult['SuppliersOrganizationOwnerArticles']['owner_articles'] = $orderResult['Order']['owner_articles'];
        $orderResult['SuppliersOrganizationOwnerArticles']['owner_organization_id'] = $orderResult['Order']['owner_organization_id'];
        $orderResult['SuppliersOrganizationOwnerArticles']['owner_supplier_organization_id'] = $orderResult['Order']['owner_supplier_organization_id'];

		self::d([$options, $orderResult], false);
		
		return $orderResult;
	}
	
	public function _getSuppliersOrganizationById($user, $supplier_organization_id, $debug=false) {
	
		/* 
		 * importo un Model perche' se e' richiamato da un Model con public $useTable = false; error
		 */
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
				
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => (int)$user->organization['Organization']['id'], 'SuppliersOrganization.id' => $supplier_organization_id];
		$options['recursive'] = 0;	
		$suppliersOrganizationResult = $SuppliersOrganization->find('first', $options);
        $suppliersOrganizationResult['SuppliersOrganizationOwnerArticles'] = $suppliersOrganizationResult['SuppliersOrganization'];

		self::d([$options, $suppliersOrganizationResult], false);
		
		return $suppliersOrganizationResult;	
	}
	
	public function getMessageErrorsToValidate($model, $data) {

		$msg = '';
		
		$model->set($data);
		if(!$model->validates()) {
		
			$errors = $model->validationErrors;
			$flatErrors = Set::flatten($errors);
			if(count($errors) > 0) { 
				$msg_tmp = '';
				foreach($flatErrors as $key => $value) 
					$msg_tmp .= $value.'<br />';
			}

			$msg .= "<br />Dati non validi:<br />$msg_tmp";
		}	
		
		return $msg;
	}
	
    public function getMaxIdOrganizationId($organization_id=0) {
    	
    	$maxId = 1;
    	
     	$options['fields'] = ['MAX('.$this->alias.'.id)+1 AS maxId'];
    	$options['conditions'] = [$this->alias.'.organization_id' => $organization_id];
    	$options['recursive'] = -1;
    	$results = $this->find('first', $options);
    	if(!empty($results)) {
    		$results = current($results);
    		$maxId = $results['maxId'];
    		if(empty($maxId)) $maxId = 1;
    	}

    	return $maxId;
    }

    /*
     * Organization, Supplier avranno $organization_id=0
	 * passo id per compatibilita' con Model::exists($id)
     */
    public function exists($id=0, $organization_id=0) {
    	$id = $this->getID();
    	if ($id === false) {
    		return false;
    	}
		
    	if(empty($organization_id) || in_array($this->useTable, $this->no_organization_id_tables))
	    	$conditions = [$this->alias.'.'.$this->primaryKey => $id];
    	else 
    		$conditions = [
    				$this->alias.'.'.$this->primaryKey => $id,
    				$this->alias.'.organization_id' => $organization_id
    		];
			
	    self::d($conditions); 
		
    	return (bool)$this->find('count', [
    			'conditions' => $conditions,
    			'recursive' => -1,
    			'callbacks' => false
    	]);
    }      

    /*
     * Organization, Supplier avranno $organization_id=0
    */
    public function read($id=0, $organization_id=0, $fields = null) {
    	
    	$this->validationErrors = [];
    	
    	if ($id) {
    		$this->id = $id;
    	}
    
    	$id = $this->id;
    
    	if (is_array($this->id)) {
    		$id = $this->id[0];
    	}
    	
		if(empty($organization_id) || in_array($this->useTable, $this->no_organization_id_tables))
    		$conditions = [$this->alias . '.' . $this->primaryKey => $id];
    	else
    		$conditions = [
	    				$this->alias.'.'.$this->primaryKey => $id,
	    				$this->alias.'.organization_id' => $organization_id
	    				];    	 
    	
    	if ($id !== null && $id !== false) {    		
    		$this->data = $this->find('first', [
    				'conditions' => $conditions,
    				'fields' => $fields,
    				'recursive' => -1
    		]);
    		
    		return $this->data;
    	}
    	return false;
    }
    
    private function _unbindModelAll() {
    	foreach([
    			'hasOne' => array_keys($this->hasOne),
    			'hasMany' => array_keys($this->hasMany),
    			'belongsTo' => array_keys($this->belongsTo),
    			'hasAndBelongsToMany' => array_keys($this->hasAndBelongsToMany)
    	] as $relation => $model) {
    	 	$this->unbindModel([$relation => $model]);
    	}
    }
    
   /*
    * in View (.ctp,.js) tutti gli importi in 1.000,50
    * 	php  number_format(num,2,separatoreDecimali,separatoreMigliaia) = da 1000.50 in 1.000,50
    * 	js   number_format(num,2,separatoreDecimali,separatoreMigliaia) = da 1000.50 in 1.000,50
    *
    * per il database o .js tutti gli importi in 1000.50
    * 	js  numberToJs         = da 1.000,50 in 1000.50
    * 	php importoToDatabase  = da 1.000,50 in 1000.50
    *
    *  php number_format     call /Helper/TabsHelper.php input[text][importo_forzato]
    *  					  call ArticlesController::admin_edit() per data['prezzo'] da visualizzare in admin_edit.ctp
    *  js  number_format     call View/Storerooms/admin_storeromm_to_user.ctp per input[text][prezzo]
    *  					  call ecommRowsBackOffice.js jQuery(this).find('.importo_forzato').change(function()
    *  php importoToDatabase call ArticlesController::admin_edit() per data['prezzo'] da inserire in database
    *
    */
    public function importoToDatabase($importo, $debug=false) {
    	
    	self::l('importoToDatabase PRE '.$importo, $debug);
    	
    	/*
    	 * se non c'e' la , e' gia' formattato correttamente 
    	 */
    	if(strpos($importo,',') === false)
    		return $importo;
    	
    	// elimino le migliaia
    	$importo = str_replace('.','',$importo);
    
    	// converto eventuali decimanali
    	$importo = str_replace(',','.',$importo);
    
    	if(strpos($importo, '.')===false)  $importo = $importo.'.00';

    	self::l('importoToDatabase POST '.$importo, $debug);
    	    	 
    	return $importo;
    }
	
	public function _getMail($template='default') {
		$Email = new CakeEmail(Configure::read('EmailConfig'));
		$Email->helpers(['Html', 'Text']);
		$Email->template($template);
		$Email->emailFormat('html');
		
		$Email->replyTo(Configure::read('Mail.no_reply_mail'), Configure::read('Mail.no_reply_name'));
		$Email->from([Configure::read('SOC.mail') => Configure::read('SOC.name')]);
		$Email->sender(Configure::read('SOC.mail'), Configure::read('SOC.name'));
		
		$Email->viewVars(['content_info' => $this->_getContentInfo()]);
		
		return $Email;
	}
	
	public function _getContentInfo() {
  		App::import('Model', 'Msg');
		$Msg = new Msg;	

		$results = $Msg->getRandomMsg();
		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
		if(!empty($results)) 
			$content_info = $results['Msg']['testo'];
		else
			$content_info = '';
		
		return $content_info;
	}

	public function _organizationNameError($organization) {
		if($organization['Organization']['id']==10)
			$organization_name = "Colibrì";
		else
			$organization_name = $organization['Organization']['name'];
		
		return $organization_name;
	}
	
	/*
	 * stesso codice AppController, AppHelper
	*/
	public function _traslateWww($str) {
			
    	if(strpos($str,'http://')===false && strpos($str,'https://')===false)
    		$str = 'http://'.$str;
			
		return $str;
	}
	
    public function _getObjUserLocal($organization_id, $debug=false) {

		App::import('Model', 'Organization');
		$Organization = new Organization;

		$options = [];
		$options['conditions'] = ['Organization.id' => (int) $organization_id];
		$options['recursive'] = 0;
		$results = $Organization->find('first', $options);

		$user = new UserLocal();
		$user->organization = $results;

		$paramsConfig = json_decode($results['Organization']['paramsConfig'], true);
		$paramsFields = json_decode($results['Organization']['paramsFields'], true);

		/*
		 * configurazione preso dal template
		 */
		$paramsConfig['payToDelivery'] = $results['Template']['payToDelivery'];
		$paramsConfig['orderForceClose'] = $results['Template']['orderForceClose'];
		$paramsConfig['orderUserPaid'] = $results['Template']['orderUserPaid'];
		$paramsConfig['orderSupplierPaid'] = $results['Template']['orderSupplierPaid'];
		$paramsConfig['ggArchiveStatics'] = $results['Template']['ggArchiveStatics'];

		$user->organization['Organization'] += $paramsConfig;
		$user->organization['Organization'] += $paramsFields;
	
		if($debug)
			echo "_getObjUserLocal() per il GAS ".$organization_id." \n";
		
		return $user;
    }
    
    /*
     * verifica se un utente ha la gestione degli articoli sugli ordini
     * dipende da 
     * 		- Organization.hasArticlesOrder
     * 		- User.hasArticlesOrder
     * 
     * anche in AppController, AppModel
     */     
    public function isUserPermissionArticlesOrder($user) {
        if ($user->organization['Organization']['hasArticlesOrder'] == 'Y' && $user->user['User']['hasArticlesOrder'] == 'Y')
            return true;
        else
            return false;
    } 	       
}