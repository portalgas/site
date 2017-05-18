<?php
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Model', 'Model');
App::uses('UtilsCommons', 'Lib');
App::uses('CakeEmail', 'Network/Email');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {
    public $actsAs = array('Enumerable');
   
	public $utilsCommons;
	
	public function __construct() {
    	parent::__construct();
    	$this->utilsCommons = new UtilsCommons();
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
     */    public function exists($organization_id=0) {    	$id = $this->getID();    	if ($id === false) {    		return false;    	}
    	
    	if(empty($organization_id))
	    	$conditions = array($this->alias . '.' . $this->primaryKey => $id);
    	else 
    		$conditions = array(    				$this->alias . '.' . $this->primaryKey => $id,    				$this->alias . '.organization_id' => $organization_id    		);
       	return (bool)$this->find('count', array(    			'conditions' => $conditions,    			'recursive' => -1,    			'callbacks' => false    	));    }    

    /*     * Organization, Supplier avranno $organization_id=0    */    public function read($organization_id=0, $fields = null, $id = null) {
    	    	$this->validationErrors = array();    	    	if ($id) {    		$this->id = $id;    	}        	$id = $this->id;        	if (is_array($this->id)) {    		$id = $this->id[0];    	}
    	if(empty($organization_id))    		$conditions = array($this->alias . '.' . $this->primaryKey => $id);    	else    		$conditions = array(	    				$this->alias . '.' . $this->primaryKey => $id,	    				$this->alias . '.organization_id' => $organization_id
	    				);    	 
    	    	if ($id !== null && $id !== false) {    		
    		$this->data = $this->find('first', array(    				'conditions' => $conditions,    				'fields' => $fields,
    				'recursive' => -1    		));
    		
    		return $this->data;    	}    	return false;    }    
    private function __unbindModelAll() {    	foreach(array(    			'hasOne' => array_keys($this->hasOne),    			'hasMany' => array_keys($this->hasMany),    			'belongsTo' => array_keys($this->belongsTo),    			'hasAndBelongsToMany' => array_keys($this->hasAndBelongsToMany)    	) as $relation => $model) {
    	 	$this->unbindModel(array($relation => $model));    	}    }
    
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
	
	/*
	 * mi serve per UtilsCrons
	 */
	protected function getOrganization($organization_id) {
	
		App::import('Model', 'Organization');
		$Organization = new Organization;
		
		$options = array();
		$options['conditions'] = array('Organization.id'=>(int)$organization_id);
		$options['recursive'] = -1;
		$options['fields'] = array('gcalendar_id', 
									'id', 'name', 'descrizione', 'indirizzo', 'localita','cap','provincia',
									'telefono', 'telefono2', 'mail', 'www', 'www2',
						            'cf', 'piva', 'banca', 'banca_iban','lat', 'lng', 'img1',
            						'template_id', 'j_group_registred', 'j_seo', 'type',
									'paramsConfig','paramsFields', 'paramsPay');
		$organization = $Organization->find('first', $options);
		/*
		echo "<pre>";
		print_r($options);
		print_r($organization);
		echo "</pre>";
		*/		
		$paramsConfig = json_decode($organization['Organization']['paramsConfig'], true);
		$paramsFields = json_decode($organization['Organization']['paramsFields'], true);
		
		$organization['Organization'] += $paramsConfig;
		$organization['Organization'] += $paramsFields;
		
		return $organization;
	}

	/*
	 * mi serve per UtilsCrons
	 */
	protected function getMail() {
		$Email = new CakeEmail(Configure::read('EmailConfig'));
		$Email->helpers(array('Html', 'Text'));
		$Email->template('default');
		$Email->emailFormat('html');
		
		$Email->replyTo(Configure::read('Mail.no_reply_mail'), Configure::read('Mail.no_reply_name'));
		$Email->from(array(Configure::read('SOC.mail') => Configure::read('SOC.name')));
		$Email->sender(Configure::read('SOC.mail'), Configure::read('SOC.name'));
		
		return $Email;
	}
}