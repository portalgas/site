<?php
App::uses('AppModel', 'Model');

/* * override perche' $primaryKeyArray = array('organization_id', 'user_id', 'group_id', 'des_supplier_id');*/
class DesSuppliersReferentMultiKey extends AppModel {
	
	var $primaryKeyArray = array('des_id', 'organization_id', 'user_id', 'group_id', 'des_supplier_id');
	var $des_id = 0;
	var $organization_id = 0;
	var $user_id = 0;
	var $group_id = 0;	var $des_supplier_id = 0;
	
	/*
	 * organization_id = 0 perche' exists richiamato in ModelValidation::errors()
	 * gli altri perche' nessun metodo puo' avere piu' parametri richiesti rispetto al suo metodo principale. 
	*/	public function exists($des_id=0, $organization_id=0, $user_id=0, $group_id=0, $des_supplier_id=0) {		 		if(empty($des_id) || empty($organization_id) || empty($user_id) || empty($group_id) || empty($des_supplier_id)) {			return false;		}
		
		$conditions = array($this->alias . '.des_id' => $des_id,
							$this->alias . '.organization_id' => $organization_id,
							$this->alias . '.user_id' => $user_id,
							$this->alias . '.group_id' => $group_id,
							$this->alias . '.des_supplier_id' => $des_supplier_id);
		/*
		echo "<pre>";
		print_r($conditions);
		echo "</pre>";
		*/		return (bool)$this->find('count', array(				'conditions' => $conditions,				'recursive' => -1,				'callbacks' => false		));	}	 	public function read($des_id=0, $organization_id=0, $user_id=0, $group_id=0, $des_supplier_id=0, $fields = NULL, $id = NULL) {		 		$this->validationErrors = array();		 		$this->data = $this->find('first', array(				'conditions' => array($this->alias . '.des_id' => $des_id,
									  $this->alias . '.organization_id' => $organization_id,
									  $this->alias . '.user_id' => $user_id,
									  $this->alias . '.group_id' => $group_id,
									  $this->alias . '.des_supplier_id' => $des_supplier_id				),				'recursive' => -1		));		 		return $this->data;	}	
	/*	 * override perche' $primaryKeyArray = array('des_id', 'organization_id', 'user_id', 'group_id', 'des_supplier_id');	*/	public function save($data = null, $validate = true, $fieldList = array()) {		 		$success = false;		 		$defaults = array(				'validate' => true, 'fieldList' => array(),				'callbacks' => true, 'counterCache' => true		);		$_whitelist = $this->whitelist;		$fields = array();		 		if (!is_array($validate)) {			$options = array_merge($defaults, compact('validate', 'fieldList'));		} else {			$options = array_merge($defaults, $validate);		}		 		$this->set($data);		 		/*		 * ctrl se UPDATE o INSERT		*/		if(isset($this->data[$this->alias]['des_id'])) 		  	 $this->des_id = $this->data[$this->alias]['des_id'];
		if(isset($this->data[$this->alias]['organization_id']))  $this->organization_id = $this->data[$this->alias]['organization_id'];
		if(isset($this->data[$this->alias]['user_id'])) 		 $this->user_id = $this->data[$this->alias]['user_id'];
		if(isset($this->data[$this->alias]['group_id'])) 		 $this->group_id = $this->data[$this->alias]['group_id'];
		if(isset($this->data[$this->alias]['des_supplier_id']))  $this->des_supplier_id = $this->data[$this->alias]['des_supplier_id'];

		if(!$this->exists($this->des_id, $this->organization_id, $this->user_id, $this->group_id, $this->des_supplier_id))			$created = true;		else			$created = false;		 		if($created) {			/*			 * insert			*/			$sql = "INSERT INTO					".Configure::read('DB.prefix')."des_suppliers_referents					(des_id, organization_id, user_id, group_id, des_supplier_id";			$sql .= ",created)					VALUES (
					".$this->data[$this->alias]['des_id'].",
					".$this->data[$this->alias]['organization_id'].",
					".$this->data[$this->alias]['user_id'].",
					".$this->data[$this->alias]['group_id'].",					".$this->data[$this->alias]['des_supplier_id'].",
					'".date('Y-m-d H:i:s')."' )";
			// echo '<br />'.$sql;					try {				$this->query($sql);				$success = true;			}			catch (Exception $e) {				CakeLog::write('error',$sql);				CakeLog::write('error',$e);				$success = false;			}		}		else {			/*			 * update			*/			$sql = "UPDATE						".Configure::read('DB.prefix')."des_suppliers_referents					SET 
						user_id = ".$this->data[$this->alias]['user_id'].",
						group_id = ".$this->data[$this->alias]['group_id'].",
						des_supplier_id = ".$this->data[$this->alias]['des_supplier_id'].",						modified = '".date('Y-m-d H:i:s')."'					WHERE						des_id = ".$this->data[$this->alias]['des_id']."
						AND organization_id = ".$this->data[$this->alias]['organization_id']."
						AND user_id = ".$this->data[$this->alias]['user_id']."
						AND group_id = ".$this->data[$this->alias]['group_id']."
						AND des_supplier_id = ".$this->data[$this->alias]['des_supplier_id'];
			// echo '<br />'.$sql;				try {				$this->query($sql);				$success = true;			}			catch (Exception $e) {				CakeLog::write('error',$sql);				CakeLog::write('error',$e);				$success = false;			}			 		}		 		if ($success) {			 			if ($options['callbacks'] === true || $options['callbacks'] === 'after') {				$event = new CakeEvent('Model.afterSave', $this, array($created, $options));				$this->getEventManager()->dispatch($event);			}			 			if (!empty($this->data)) {				$success = $this->data;			}			 			$this->data = false;			$this->_clearCache();			$this->validationErrors = array();		}		 		return $success;	}		public function delete($des_id=0, $organization_id=0, $user_id=0, $group_id=0, $des_supplier_id=0) {		 		$sql = "DELETE					FROM						".Configure::read('DB.prefix')."des_suppliers_referents	   				WHERE	   					des_id = ".(int)$des_id."
	   					AND organization_id = ".(int)$organization_id."
	   					AND user_id = ".(int)$user_id."
	   					AND group_id = ".(int)$group_id."
	   					AND des_supplier_id = ".(int)$des_supplier_id."	   					";		// echo '<br />'.$sql;		try {			$results = $this->query($sql);			$success=true;		}		catch (Exception $e) {			CakeLog::write('error',$sql);			CakeLog::write('error',$e);			$success=false;		}		return $success;	}
}