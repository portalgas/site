<?php
App::uses('AppModel', 'Model');


/*
 * override perche' $primaryKeyArray = array('organization_id', 'user_id', 'group_id', 'supplier_organization_id', 'type');
*/
class SuppliersOrganizationsReferentMultiKey extends AppModel {
	
	var $primaryKeyArray = array('organization_id', 'user_id', 'group_id', 'supplier_organization_id', 'type');
	var $organization_id = 0;
	var $user_id = 0;
	var $group_id = 0;
	var $supplier_organization_id = 0;
	var $type = '';  // REFERENTE COREFERENTE
	
	/*
	 * organization_id = 0 perche' exists richiamato in ModelVAlidation::errors()
	 * gli altri perche' nessun metodo puo' avere piu' parametri richiesti rispetto al suo metodo principale. 
	*/
	public function exists($organization_id=0, $user_id=0, $group_id=0, $supplier_organization_id=0, $type='') {
		 
		if(empty($organization_id) || empty($user_id) || empty($group_id) || empty($supplier_organization_id) || empty($type)) {
			return false;
		}
		
		$conditions = array($this->alias . '.organization_id' => $organization_id,
							$this->alias . '.user_id' => $user_id,
							$this->alias . '.group_id' => $group_id,
							$this->alias . '.supplier_organization_id' => $supplier_organization_id,
							$this->alias . '.type' => $type);
		/*
		echo "<pre>";
		print_r($conditions);
		echo "</pre>";
		*/
		return (bool)$this->find('count', array(
				'conditions' => $conditions,
				'recursive' => -1,
				'callbacks' => false
		));
	}
	 
	public function read($organization_id=0, $user_id=0, $group_id=0, $supplier_organization_id=0, $type='', $fields = NULL, $id = NULL) {
		 
		$this->validationErrors = [];
		 
		$this->data = $this->find('first', array(
				'conditions' => array($this->alias . '.organization_id' => $organization_id,
									  $this->alias . '.user_id' => $user_id,
									  $this->alias . '.group_id' => $group_id,
									  $this->alias . '.supplier_organization_id' => $supplier_organization_id,
									  $this->alias . '.type' => $type,
				),
				'recursive' => -1
		));
		 
		return $this->data;
	}
	
	/*
	 * override perche' $primaryKeyArray = array('organization_id', 'user_id', 'group_id', 'supplier_organization_id');
	*/
	public function save($data = null, $validate = true, $fieldList = []) {
		 
		$success = false;
		 
		$defaults = array(
				'validate' => true, 'fieldList' => [],
				'callbacks' => true, 'counterCache' => true
		);
		$_whitelist = $this->whitelist;
		$fields = [];
		 
		if (!is_array($validate)) {
			$options = array_merge($defaults, compact('validate', 'fieldList'));
		} else {
			$options = array_merge($defaults, $validate);
		}
		 
		$this->set($data);
		 
		/*
		 * ctrl se UPDATE o INSERT
		*/
		if(isset($this->data[$this->alias]['organization_id'])) 		  $this->organization_id = $this->data[$this->alias]['organization_id'];
		if(isset($this->data[$this->alias]['user_id'])) 				  $this->user_id = $this->data[$this->alias]['user_id'];
		if(isset($this->data[$this->alias]['group_id'])) 				  $this->group_id = $this->data[$this->alias]['group_id'];
		if(isset($this->data[$this->alias]['supplier_organization_id']))  $this->supplier_organization_id = $this->data[$this->alias]['supplier_organization_id'];
		if(isset($this->data[$this->alias]['type']))  $this->type = $this->data[$this->alias]['type'];
		
		if(!$this->exists($this->organization_id, $this->user_id, $this->group_id, $this->supplier_organization_id, $this->type))
			$created = true;
		else
			$created = false;
		 
		if($created) {
			/*
			 * insert
			*/
			$sql = "INSERT INTO
					".Configure::read('DB.prefix')."suppliers_organizations_referents
					(organization_id, user_id, group_id, supplier_organization_id, type";
			$sql .= ",created)
					VALUES (
					".$this->data[$this->alias]['organization_id'].",
					".$this->data[$this->alias]['user_id'].",
					".$this->data[$this->alias]['group_id'].",
					".$this->data[$this->alias]['supplier_organization_id'].",
					'".addslashes($this->data[$this->alias]['type'])."',
					'".date('Y-m-d H:i:s')."' )";
			try {
				$this->query($sql);
				$success = true;
			}
			catch (Exception $e) {
				CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
				$success = false;
			}
		}
		else {
			/*
			 * update
			*/
			$sql = "UPDATE
						".Configure::read('DB.prefix')."suppliers_organizations_referents
					SET 
						user_id = ".$this->data[$this->alias]['user_id'].",
						group_id = ".$this->data[$this->alias]['group_id'].",
						supplier_organization_id = ".$this->data[$this->alias]['supplier_organization_id'].",
						type = '".addslashes($this->data[$this->alias]['type'])."',
						modified = '".date('Y-m-d H:i:s')."'
					WHERE
						organization_id = ".$this->data[$this->alias]['organization_id']."
						AND user_id = ".$this->data[$this->alias]['user_id']."
						AND group_id = ".$this->data[$this->alias]['group_id']."
						AND supplier_organization_id = ".$this->data[$this->alias]['supplier_organization_id']."
						AND type = ".$this->data[$this->alias]['type'];
			try {
				$this->query($sql);
				$success = true;
			}
			catch (Exception $e) {
				CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
				$success = false;
			}
			 
		}
		 
		if ($success) {
			 
			if ($options['callbacks'] === true || $options['callbacks'] === 'after') {
				$event = new CakeEvent('Model.afterSave', $this, array($created, $options));
				$this->getEventManager()->dispatch($event);
			}
			 
			if (!empty($this->data)) {
				$success = $this->data;
			}
			 
			$this->data = false;
			$this->_clearCache();
			$this->validationErrors = [];
		}
		 
		return $success;
	}
	
	public function myDelete($organization_id=0, $user_id=0, $group_id=0, $supplier_organization_id=0, $type) {
		 
		$sql = "DELETE
					FROM
						".Configure::read('DB.prefix')."suppliers_organizations_referents
	   				WHERE
	   					organization_id = ".(int)$organization_id."
	   					AND user_id = ".(int)$user_id."
	   					AND group_id = ".(int)$group_id."
	   					AND supplier_organization_id = ".(int)$supplier_organization_id."
	   					AND type = '".$type."'
	   					";
		self::d($sql, false);
		try {
			$results = $this->query($sql);
			$success=true;
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
			$success=false;
		}
		return $success;
	}
}