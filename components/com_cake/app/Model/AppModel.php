<?php
App::uses('Model', 'Model');
App::uses('UtilsCommons', 'Lib');
App::uses('CakeEmail', 'Network/Email');

class AppModel extends Model {
    public $actsAs = array('Enumerable');
   
	public $utilsCommons;
	
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
		
    public function _getOrderById($user, $order_id, $debug) {

		/* 
		 * importo un Model perche' se e' richiamato da un Model con public $useTable = false; error
		 */
		App::import('Model', 'Order');
		$Order = new Order;
				
		$options = [];
		$options['conditions'] = ['Order.organization_id' => (int)$user->organization['Organization']['id'],
								  'Order.id' => $order_id];
		$options['recursive'] = 0;	
		$orderResult = $Order->find('first', $options);

		// self::d([$options, $orderResult], false);

		return $orderResult;
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
    	
     	$options['fields'] = array('MAX('.$this->alias.'.id)+1 AS maxId');
    	$options['conditions'] = array($this->alias.'.organization_id' => $organization_id);
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
     */
    public function exists($organization_id=0) {
    	$id = $this->getID();
    	if ($id === false) {
    		return false;
    	}
    	
    	if(empty($organization_id))
	    	$conditions = array($this->alias . '.' . $this->primaryKey => $id);
    	else 
    		$conditions = array(
    				$this->alias . '.' . $this->primaryKey => $id,
    				$this->alias . '.organization_id' => $organization_id
    		);
   
    	return (bool)$this->find('count', array(
    			'conditions' => $conditions,
    			'recursive' => -1,
    			'callbacks' => false
    	));
    }    

    /*
     * Organization, Supplier avranno $organization_id=0
    */
    public function read($organization_id=0, $fields = null, $id = null) {
    	
    	$this->validationErrors = array();
    	
    	if ($id) {
    		$this->id = $id;
    	}
    
    	$id = $this->id;
    
    	if (is_array($this->id)) {
    		$id = $this->id[0];
    	}
    	if(empty($organization_id))
    		$conditions = array($this->alias . '.' . $this->primaryKey => $id);
    	else
    		$conditions = array(
	    				$this->alias . '.' . $this->primaryKey => $id,
	    				$this->alias . '.organization_id' => $organization_id
	    				);    	 
    	
    	if ($id !== null && $id !== false) {    		
    		$this->data = $this->find('first', array(
    				'conditions' => $conditions,
    				'fields' => $fields,
    				'recursive' => -1
    		));
    		
    		return $this->data;
    	}
    	return false;
    }
    
    private function _unbindModelAll() {
    	foreach(array(
    			'hasOne' => array_keys($this->hasOne),
    			'hasMany' => array_keys($this->hasMany),
    			'belongsTo' => array_keys($this->belongsTo),
    			'hasAndBelongsToMany' => array_keys($this->hasAndBelongsToMany)
    	) as $relation => $model) {
    	 	$this->unbindModel(array($relation => $model));
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
    public function importoToDatabase($importo) {
    	
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
    	 
    	return $importo;
    }
	
	public function _getMail($template='default') {
		$Email = new CakeEmail(Configure::read('EmailConfig'));
		$Email->helpers(array('Html', 'Text'));
		$Email->template($template);
		$Email->emailFormat('html');
		
		$Email->replyTo(Configure::read('Mail.no_reply_mail'), Configure::read('Mail.no_reply_name'));
		$Email->from(array(Configure::read('SOC.mail') => Configure::read('SOC.name')));
		$Email->sender(Configure::read('SOC.mail'), Configure::read('SOC.name'));
		
		$Email->viewVars(array('content_info' => $this->_getContentInfo()));
		
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
		if($organization['Organization']['id']==41)
			$organization_name = "Bio C'è";
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
	
	public function _getUsers($organization_id) {
            
			App::import('Model', 'User');
            $User = new User;

            $options = [];
            $options['conditions'] = array('User.organization_id'=>(int)$organization_id,
                                            'User.block'=> 0);
            $options['fields'] = array('User.id','User.name','User.email','User.username');
            $options['order'] = Configure::read('orderUser');
            $options['recursive'] = 0;

            $users = $User->find('all', $options);

            /*
            echo "<pre>";
            print_r($users;
            echo "</pre>";
            */

            echo "getUsers(): trovati ".count($users)." utenti\n";

            return $users;
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
}