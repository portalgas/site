<?php
App::uses('AppModel', 'Model');

class DesSupplier extends AppModel {
	
	/*
	 * setting SuppliersOrganization.owner_articles = DES 
	 * per il GAS titolare del produttore 
	 *		SuppliersOrganization.owner_articles = REFERENT
	 * per i GAS non titolari del produttore 
	 *		se ha un proprio listino articoli
	 *			SuppliersOrganization.owner_articles = REFERENT
	 *		se NON ha un proprio listino articoli
	 *		SuppliersOrganization.owner_articles = DES 
	 *
	 * supplier_id puo' essere 0 se voglio processare tutti i produttori di un DES
	 *
	*/              
	public function setSupplierOrganizationOwnerArticles($user, $supplier_id=0, $debug=false) {

		$debug_save = false;  // se false SAVE
		
		self::d("DesSupplier::setSuppliersOrganizationOwnerArticles()", $debug);

		if(empty($user->des_id))  {
			self::d($user, $debug);
			self::x("DesSupplier::setSuppliersOrganizationOwnerArticles() user->des_id non valorizzato");
		}
		
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization();
		
		App::import('Model', 'Article');
		$Article = new Article();
				
		/*
		 * estraggo i GAS associati al DES
		 */
		App::import('Model', 'DesOrganization');
		$DesOrganization = new DesOrganization();
	    
	    $options = [];
		$options['conditions'] = ['DesOrganization.des_id' => $user->des_id];
		$options['fields'] = ['DesOrganization.organization_id', 'Organization.name'];		
		$options['recursive'] = 0;
		$desOrganizationResults = $DesOrganization->find('all', $options);
		
		self::d("Tratto Des [$user->des_id]", $debug);
		self::d("GAS associati al DES ", $debug);
		self::d($desOrganizationResults, $debug);	

		
		/*
		 * estraggo i PRODUTTORI associati al DES per ottenere il titolare del produttore
		 */
		App::import('Model', 'DesSupplier');
		$DesSupplier = new DesSupplier();
		$DesSupplier->unbindModel(['belongsTo' => ['De']]);
		
		$options = [];
		$options['conditions'] = ['DesSupplier.des_id' => $user->des_id];
		if(!empty($supplier_id))
			$options['conditions'] += ['DesSupplier.supplier_id' => $supplier_id];
		$options['fields'] = ['DesSupplier.supplier_id', 'DesSupplier.own_organization_id', 'Supplier.name', 'OwnOrganization.name'];  
		$options['recursive'] = 0;
		$DesSupplierResults = $DesSupplier->find('all', $options);
		// self::d($options['conditions'], $debug);
		// self::d($DesSupplierResults, $debug);
		foreach($DesSupplierResults as $DesSupplierResult) {
		
			self::d("------------------------------------", $debug);
			self::d($DesSupplierResult, $debug);
			
			$supplier_id = $DesSupplierResult['DesSupplier']['supplier_id'];
			$supplier_name = $DesSupplierResult['Supplier']['name'];
			$own_organization_id = $DesSupplierResult['DesSupplier']['own_organization_id'];
			$own_organization_name = $DesSupplierResult['OwnOrganization']['name'];
			
			if(empty($own_organization_id)) {	
				/*
				 * il produttore non ha + un titolare => porto tutti i SuppliersOrganization.owner_articles == 'REFERENT'
				 */	
				self::d("DesSupplier::setSuppliersOrganizationOwnerArticles() il produttore non ha + un titolare => porto tutti i SuppliersOrganization.owner_articles == 'REFERENT'", $debug);
				$new_owner_articles = 'REFERENT';
				
				foreach($desOrganizationResults as $desOrganizationResult) {
					
					$organization_id = $desOrganizationResult['DesOrganization']['organization_id'];
					$organization_name = $desOrganizationResult['Organization']['name'];
						
					/*
					 * estraggo i dati del PRODUTTORI del GAS
					 */
					$options = [];
					$options['conditions'] = ['SuppliersOrganization.organization_id' => $organization_id,
											  'SuppliersOrganization.supplier_id' => $supplier_id];	    								   	   
					$options['recursive'] = -1;
					$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
					
					if(!empty($suppliersOrganizationResults)) {
						self::d("DesSupplier::setSuppliersOrganizationOwnerArticles() - DesSupplier.own_organization_id EMPTY - UPDATE GAS [".$organization_id." - ".$organization_name."] dati produttore [".$supplier_id." - ".$supplier_name."] SuppliersOrganization.owner_articles = '$new_owner_articles'", $debug);
								
						$suppliersOrganizationResults['SuppliersOrganization']['owner_articles']=$new_owner_articles;

						$suppliersOrganizationResults['SuppliersOrganization']['owner_organization_id'] = $suppliersOrganizationResults['SuppliersOrganization']['organization_id'];
						$suppliersOrganizationResults['SuppliersOrganization']['owner_supplier_organization_id'] = $suppliersOrganizationResults['SuppliersOrganization']['id'];
								
						$msg_errors = $SuppliersOrganization->getMessageErrorsToValidate($SuppliersOrganization, $suppliersOrganizationResults);
						if(!empty($msg_errors)) {
							self::d($msg_errors, $debug);	
							return $msg_errors;
						}
						else {
							if(!$debug_save) {
								$SuppliersOrganization->create();
								if (!$SuppliersOrganization->save($suppliersOrganizationResults)) {
									self::d("DesSupplier::setSuppliersOrganizationOwnerArticles() - DesSupplier.own_organization_id EMPTY - ERROR UPDATE GAS [".$organization_id." - ".$organization_name."] dati produttore [".$supplier_id." - ".$supplier_name."] NON TITOLARE SuppliersOrganization.owner_articles = '$new_owner_articles' !!!", $debug);
									return false;
								}
								else {
									self::d($suppliersOrganizationResults, $debug);
								}
							}
						}
					} // end if(!empty($suppliersOrganizationResults)) 
					else {
						self::d('Caso non previsto', $debug);
						self::d($options, $debug); 
					}
							
				} // end foreach($desOrganizationResults as $desOrganizationResult)				
			}
			else {  // if($own_organization_id!=0)
				/*
				 * estraggo i dati del PRODUTTORI del titolare (supplier_id)
				 */
				$options = [];
				$options['conditions'] = ['SuppliersOrganization.organization_id' => $own_organization_id,
										  'SuppliersOrganization.supplier_id' => $supplier_id];	    								   	   
				$options['recursive'] = -1;
				$suppliersOrganizationTitolareResults = $SuppliersOrganization->find('first', $options);

				self::d("DesSupplier::setSuppliersOrganizationOwnerArticles() - DesSupplier.own_organization_id NOT EMPTY - GAS [".$own_organization_id." - ".$own_organization_name."] dati produttore [".$supplier_id." - ".$supplier_name."] del TITOLARE", $debug);
				
				if(!empty($suppliersOrganizationTitolareResults)) {  // dati non coerenti
					
					/*
					 * UPDATE dati produttore del TITOLARE SuppliersOrganization.owner_articles = 'REFERENT'
					 */			
					if($suppliersOrganizationTitolareResults['SuppliersOrganization']['owner_articles']=='DES') {
						
						self::d("DesSupplier::setSuppliersOrganizationOwnerArticles() - DesSupplier.own_organization_id NOT EMPTY - UPDATE GAS [".$own_organization_id." - ".$own_organization_name."] dati produttore [".$supplier_id." - ".$supplier_name."] del TITOLARE SuppliersOrganization.owner_articles = 'REFERENT'", $debug);
						
						$suppliersOrganizationTitolareResults['SuppliersOrganization']['owner_articles']='REFERENT';
						$suppliersOrganizationTitolareResults['SuppliersOrganization']['owner_organization_id'] = $suppliersOrganizationTitolareResults['SuppliersOrganization']['organization_id'];
						$suppliersOrganizationTitolareResults['SuppliersOrganization']['owner_supplier_organization_id'] = $suppliersOrganizationTitolareResults['SuppliersOrganization']['id'];	
					}
					else
					if($suppliersOrganizationTitolareResults['SuppliersOrganization']['owner_articles']=='REFERENT') {
						$suppliersOrganizationTitolareResults['SuppliersOrganization']['owner_organization_id'] = $suppliersOrganizationTitolareResults['SuppliersOrganization']['organization_id'];
						$suppliersOrganizationTitolareResults['SuppliersOrganization']['owner_supplier_organization_id'] = $suppliersOrganizationTitolareResults['SuppliersOrganization']['id'];					
					}
				
					$msg_errors = $SuppliersOrganization->getMessageErrorsToValidate($SuppliersOrganization, $suppliersOrganizationTitolareResults);
					if(!empty($msg_errors)) {
						self::d($msg_errors, $debug);	
						return $msg_errors;
					}
					else {
						if(!$debug_save) {
							$SuppliersOrganization->create();
							if (!$SuppliersOrganization->save($suppliersOrganizationTitolareResults)) {
								self::d("DesSupplier::setSuppliersOrganizationOwnerArticles() - DesSupplier.own_organization_id NOT EMPTY - ERROR UPDATE GAS [".$own_organization_id." - ".$own_organization_name."] dati produttore [".$supplier_id." - ".$supplier_name."] del TITOLARE SuppliersOrganization.owner_articles = 'REFERENT' !!!", $debug);							
								return false;
							}
						}
					}
					
					/*
					 * UPDATE dati produttore NON TITOLARE SuppliersOrganization.owner_articles = 'DES'
					 */			
					foreach($desOrganizationResults as $desOrganizationResult) {
						
						$organization_id = $desOrganizationResult['DesOrganization']['organization_id'];
						$organization_name = $desOrganizationResult['Organization']['name'];
						
						if($organization_id!=$own_organization_id) {  // escludo il titolare
								
							/*
							 * estraggo i dati del PRODUTTORI NON titolare
							 */
							$options = [];
							$options['conditions'] = ['SuppliersOrganization.organization_id' => $organization_id,
													  'SuppliersOrganization.supplier_id' => $supplier_id];	    								   	   
							$options['recursive'] = -1;
							$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
	
							if(empty($suppliersOrganizationResults)) {
								self::d("DesSupplier::setSuppliersOrganizationOwnerArticles() GAS [".$organization_id." - ".$organization_name."] NON HA dati produttore [".$supplier_id."] NON TITOLARE", $debug);
							}
							else {
								self::d("DesSupplier::setSuppliersOrganizationOwnerArticles() GAS [".$organization_id." - ".$organization_name."] dati produttore [".$supplier_id."] NON TITOLARE", $debug);
								self::d($suppliersOrganizationResults, $debug);	
	
								/*
								 * ctrl se il GAS ha un proprio listino articoli
								 */
								$options = [];
								$options['conditions'] = ['Article.organization_id' => $organization_id,
														  'Article.supplier_organization_id' => $organization_id,];	    								   	   
								$options['recursive'] = -1;
								$articleResults = $Article->find('count', $options);
								self::d("DesSupplier::setSuppliersOrganizationOwnerArticles() GAS [".$organization_id." - ".$organization_name."] totale articles ".$articleResults, $debug);
								if($articleResults==0)
									$new_owner_articles = 'DES';
								else
									$new_owner_articles = 'REFERENT';
							
								/*
								 * imposto sempre $new_owner_articles = 'DES';
								 */
								$new_owner_articles = 'DES';
								
								self::d("DesSupplier::setSuppliersOrganizationOwnerArticles() UPDATE GAS [".$organization_id." - ".$organization_name."] dati produttore [".$supplier_id."] NON TITOLARE SuppliersOrganization.owner_articles = '$new_owner_articles'", $debug);
								
								$suppliersOrganizationResults['SuppliersOrganization']['owner_articles']=$new_owner_articles;
								/*
								 * prendo i dati del GAS titolare
								 */
								if($new_owner_articles == 'DES') {
									$suppliersOrganizationResults['SuppliersOrganization']['owner_organization_id'] = $suppliersOrganizationTitolareResults['SuppliersOrganization']['owner_organization_id'];
									$suppliersOrganizationResults['SuppliersOrganization']['owner_supplier_organization_id'] = $suppliersOrganizationTitolareResults['SuppliersOrganization']['owner_supplier_organization_id'];
								}
								else
								if($new_owner_articles == 'REFERENT') {
									$suppliersOrganizationResults['SuppliersOrganization']['owner_organization_id'] = $suppliersOrganizationResults['SuppliersOrganization']['organization_id'];								
									$suppliersOrganizationResults['SuppliersOrganization']['owner_supplier_organization_id'] = $suppliersOrganizationResults['SuppliersOrganization']['id'];
								}
								
								$msg_errors = $SuppliersOrganization->getMessageErrorsToValidate($SuppliersOrganization, $suppliersOrganizationResults);
								if(!empty($msg_errors)) {
									self::d($msg_errors, $debug);	
									return $msg_errors;
								}
								else {
									if(!$debug_save) {
										$SuppliersOrganization->create();
										if (!$SuppliersOrganization->save($suppliersOrganizationResults)) {
											self::d("DesSupplier::setSuppliersOrganizationOwnerArticles() ERROR UPDATE GAS [".$organization_id." - ".$organization_name."] dati produttore [".$supplier_id."] NON TITOLARE SuppliersOrganization.owner_articles = '$new_owner_articles' !!!", $debug);
											return false;
										}
										else {
											self::d($suppliersOrganizationResults, $debug);
										}
									}
								}
							} // end if(!empty($suppliersOrganizationTitolareResults)) 
						} // end if(empty($suppliersOrganizationResults))
					} // end if($organization_id==$own_organization_id) 
				} // end foreach($desOrganizationResults as $desOrganizationResult)
			} // if($own_organization_id!=0)
		} // end foreach($DesSupplierResults as $DesSupplierResult)
	
		return true;
	}
	
	public function getOrganizationIdTitolare($user, $des_order_id, $debug = false) {

		$own_organiation_id = 0;
		
		App::import('Model', 'DesOrdersOrganization');
		$DesOrdersOrganization = new DesOrdersOrganization();
	    $DesOrdersOrganization->unbindModel(['belongsTo' => ['Organization', 'De', 'Order']]);
	    
	    $options = [];
		$options['conditions'] = ['DesOrdersOrganization.organization_id' => $user->organization['Organization']['id'],
								  'DesOrdersOrganization.des_order_id' => (int)$des_order_id];
		if(!empty($user->des_id))
			$options['conditions'] += ['DesOrdersOrganization.des_id' => $user->des_id];	    								   	   
		$options['recursive'] = 2;
		$results = $DesOrdersOrganization->find('first', $options);
		
		self::d("DesSupplier::getOrganizationIdTitolare()", $debug);
		self::d($options, $debug);
		self::d($results, $debug);
		
		if(!empty($results)) {
			$own_organiation_id = $results['DesOrder']['DesSupplier']['own_organization_id'];	
		}
		
		self::d("DesSupplier->getOrganizationIdTitolare(): $own_organiation_id", $debug);
			
	    return $own_organiation_id;
	}

	/*
	 * ctrl se il GAS e' superReferenteTitolareDes
	 * se non lo e' ctrl che lo sia qualcun'altro, se no potrebbe diventarlo
	 */
	public function isOrganizationTitolare($user, $des_supplier_id, $debug = false) {

		/*
		 * ctrl se ancora nessuno e' superReferenteTitolareDes DesSupplier.own_organization_id = 0
	 	*/	
	   $options = [];
	   $options['conditions'] = ['DesSupplier.des_id' => $user->des_id,
								  'DesSupplier.own_organization_id' => 0,
								   'DesSupplier.id' => (int)$des_supplier_id];
	   $total = $this->find('count', $options);
		if($debug) { 
			echo "<pre>DesSupplier->isOrganizationTitolare() ";
			print_r($options);
			echo "</pre>";
			echo '<br />totali '.$total.' se 1 non c\'e\' ancora un titolare';
	    }
	    	   
	   if($total==1)
	   		return true;
	   	
	   	/*
	   	 * qualcuno e' superReferenteTitolareDes, ctrl che sia il mio GAS
	   	 */
	    $options = [];
	    $options['conditions'] = array('DesSupplier.des_id' => $user->des_id,
	    							   'DesSupplier.own_organization_id' => (int)$user->organization['Organization']['id'],
									   'DesSupplier.id' => (int)$des_supplier_id);
	    $total = $this->find('count', $options);
		if($debug) { 
			echo "<pre>";
			print_r($options);
			echo "</pre>";
			echo '<br />isOrganizationTitolare '.$total;
	    }
	    
	 	if($total==1)
			return true;
		else
			return false;
	}

	/*
	 * ctrl per il produttore qual'è il GAS titolare
	 */
	public function aggiornaOwnOrganizationId($user, $des_supplier_id, $debug = false) {

		App::import('Model', 'DesSuppliersReferent');
		$DesSuppliersReferent = new DesSuppliersReferent;
		
	    $options = [];
	    $options['conditions'] = ['DesSuppliersReferent.des_id' => $user->des_id,
								  'DesSuppliersReferent.des_supplier_id' => $des_supplier_id,
								  'DesSuppliersReferent.group_id' => Configure::read('group_id_titolare_des_supplier')];
	    $options['fields'] = ['DesSuppliersReferent.organization_id']; 
	    $options['recursive'] = -1;							  
	    $desSuppliersReferentResults = $DesSuppliersReferent->find('first', $options);
	    
		if($debug) { 
			echo "<pre>DesSupplier->aggiornaOwnOrganizationId() \n ";
			print_r($options);
			print_r($desSuppliersReferentResults);
			echo "</pre>";
	    }

		if(empty($desSuppliersReferentResults))
			$own_organization_id = 0;
		else
			$own_organization_id = $desSuppliersReferentResults['DesSuppliersReferent']['organization_id'];
		
		try {
			$sql = "UPDATE
						`".Configure::read('DB.prefix')."des_suppliers`
					SET
						own_organization_id = ".$own_organization_id."
					WHERE
					  des_id = ".$user->des_id." 
					  and id = ".(int)$des_supplier_id;
			self::d($sql, $debug);
			$result = $this->query($sql);
		
		} catch (Exception $e) {
			echo '<br />DesSupplier->aggiornaOwnOrganizationId()<br />'.$e;
		}
		
		if($debug) exit;
	}	
	
	public function hasOrganizationSupplier($user, $organization_id, $des_supplier_id, $debug=false) {
		
	    $options = [];
	    $options['conditions'] = ['DesSupplier.des_id' => $user->des_id,
									    'DesSupplier.id' => $des_supplier_id];
	    $options['fields'] = ['DesSupplier.supplier_id'];
	    $options['recursive'] = -1;
	    $results = $this->find('first', $options);
	
		if($debug) echo '<br />DesSupplier->hasOrganizationSupplier() - supplier_id '.$results['DesSupplier']['supplier_id'];	
	
   		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$tmp_user->organization['Organization']['id'] = $organization_id;
		$conditions = array('SuppliersOrganization.supplier_id' => $results['DesSupplier']['supplier_id']);
		$results = $SuppliersOrganization->getSuppliersOrganization($tmp_user, $conditions);
		
		if($debug) {
			echo "<pre>DesSupplier->hasOrganizationSupplier() - SuppliersOrganization ";
			print_r($results);
			echo "</pre>";
		}
						
		if(empty($results))
			return false;
		else	
			return true;
	}
	
	/*
	 * lista di tutti i DesSuppliers , per superReferenteDes
	 */
	public function getListDesSuppliers($user) {
	    
	    $options = [];
	    $options['conditions'] = ['DesSupplier.des_id' => $user->des_id,
	    							"(Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG')"];
	    $options['fields'] = ['DesSupplier.id', 'Supplier.name'];
	    $options['order'] = ['Supplier.name'];
	    $options['recursive'] = 1;
	    $results = $this->find('list', $options);
		
		return $results;
	}
	
	/*
	 * get elenco dei desSupplier
	 * 	1, 3, 4, 56
	 */
	public function getDesSuppliersIds($user, $debug=false) {

		$options = [];
		$options['conditions'] = ['DesSupplier.des_id' => $user->des_id,
								 "(Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG')"];
		$options['recursive'] = 1;
	    $options['fields'] = ['DesSupplier.id'];
	    $options['order'] = ['DesSupplier.id'];
		$this->unBindModel(array('belongsTo' => array('De')));
		$this->unBindModel(array('hasMany' => array('DesOrder')));
		
		$results = $this->find('all', $options);

		if($debug) {
			echo "<pre>getDesSuppliersIds ";
			print_r($results);
			echo "</pre>";
		}
				
		/*
		 * converto results in una stringa 1, 3, 4, 56
		*/
		if(!empty($results)) {
			$tmp = "";
			foreach ($results as $result) 
				$tmp .= $result['DesSupplier']['id'].',';
		
			$results = substr($tmp, 0, (strlen($tmp)-1));
		}
		else
			$results = 0;
			
		if($debug) echo '<br />'.$results;

		return $results;
	}
	
	public function getSuppliersOrganization($user, $des_supplier_id, $debug= false) {
	
		$options = [];
		$options['conditions'] = ['DesSupplier.des_id' => $user->des_id,
								  'DesSupplier.id' => $des_supplier_id];
		$options['recursive'] = -1;
		$options['fields'] = ['DesSupplier.supplier_id'];
		$results = $this->find('first', $options);
		
		self::d("DesSupplier->getSuppliersOrganization ", $debug);
		self::d($options['conditions'], $debug);
		self::d($results, $debug);
		
		$supplier_id = $results['DesSupplier']['supplier_id'];
		
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;		
		$SuppliersOrganization->unBindModel(['belongsTo' => ['Organization', 'CategoriesSupplier']]);

		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => (int)$user->organization['Organization']['id'],
								  'SuppliersOrganization.supplier_id' => $supplier_id];
		$options['recursive'] = 0;		
		$results = $SuppliersOrganization->find('first', $options);
		self::d("DesSupplier->getSuppliersOrganization ", $debug);
		self::d($options, $debug);
		self::d($results, $debug);
				
		return $results;
    }
    	
    /*
     * dato un produttore di un GAS (SuppliersOrganization) cerco il GAS titolare
     *	se nella ricerca degli articoli (Articles::context_articles_index) non li trovo faccio vedere in lettura quelli del titolare 
     */ 
    public function getDesSupplierTitolare($user, $supplier_organization_id, $debug=false) {
    	
    	$results = [];
    	
    	if($user->organization['Organization']['hasDes']=='N') 
    		return false;		
    	
    	if(empty($supplier_organization_id))
    		return false;
    		
    	/*
    	 * dal un produttore di un GAS (SuppliersOrganization) ottengo il produttore (Supplier)
    	 */
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;		
	
		$suppliersOrganizationResults = $SuppliersOrganization->getSupplier($user, $supplier_organization_id);
		if(empty($suppliersOrganizationResults))
			return false;
			
		$supplier_id = $suppliersOrganizationResults['SuppliersOrganization']['supplier_id'];

    	/*
    	 * estratto tutti i DES di cui fa parte il GAS
    	 */
		App::import('Model', 'DesOrganization');
		$DesOrganization = new DesOrganization;		
	
		$desOrganizationResults = $DesOrganization->getAllDes($user, $debug);
		if(empty($desOrganizationResults))
			return false;
			
		/*
		 * estraggo il primo Gas titolare di quel produttore
		 */	
		 $own_organization_id = 0; 
		foreach($desOrganizationResults as $desOrganizationResult) {
			
			$options = [];
			$options['conditions'] = array('DesSupplier.des_id' => $desOrganizationResult['DesOrganization']['des_id'],
										   'DesSupplier.supplier_id' => $supplier_id);
		//	$options['fields'] = ['DesSupplier.own_organization_id'];	
			$options['recursive'] = 0;	
			$desSupplierResults = $this->find('first', $options);
			if(!empty($desSupplierResults)) {
				if($debug) {
					echo "<pre>DesSupplier->getArticlesDesSupplierTitolare \n ";
					print_r($options);
					print_r($desSupplierResults);
					echo "</pre>";
				}
				
				$own_organization_id = $desSupplierResults['DesSupplier']['own_organization_id'];
				$results['DesSupplier'] = $desSupplierResults['DesSupplier'];
				$results['De'] = $desSupplierResults['De'];
				$results['OwnOrganization'] = $desSupplierResults['OwnOrganization'];
				break;
			}
		
		} // loop $desOrganizationResults
		
		/*
		 * estraggo gli articoli del titolare 
		 */
		if(!empty($own_organization_id)) {
			
			/*
			 * estraggo il produttore di un GAS titolare (SuppliersOrganization) 
			 */
			$conditions = []; 
			$conditions['SuppliersOrganization.supplier_id'] = $supplier_id; 
			$tmp->user->organization['Organization']['id'] = $own_organization_id; 
			$suppliersOrganizationResults = $SuppliersOrganization->getSuppliersOrganization($tmp->user, $conditions);
			if(empty($suppliersOrganizationResults))
				return false;
				
			$results += $suppliersOrganizationResults[0];
		}			
	
		return $results;
    }
     
	public $validate = array(
		'des_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'supplier_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'own_organization_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	public $belongsTo = array(
			'De' => array(
					'className' => 'De',
					'foreignKey' => 'des_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			),
			'Supplier' => array(
					'className' => 'Supplier',
					'foreignKey' => 'supplier_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			),
			'OwnOrganization' => array(
					'className' => 'Organization',
					'foreignKey' => 'own_organization_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			)			
	);	
	
	public $hasMany = array(
			'DesOrder' => array(
					'className' => 'DesOrder',
					'foreignKey' => 'des_supplier_id',
					'dependent' => false,
					'conditions' => '',
					'fields' => '',
					'order' => '',
					'limit' => '',
					'offset' => '',
					'exclusive' => '',
					'finderQuery' => '',
					'counterQuery' => ''
			)
	);	
}