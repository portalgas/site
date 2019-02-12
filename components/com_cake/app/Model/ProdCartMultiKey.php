<?php
App::uses('AppModel', 'Model');

/* * override perche' $primaryKeyArray = array('organization_id', 'prod_delivery_id', 'article_organization_id', 'article_id', 'user_id');*/class ProdCartMultiKey extends AppModel {
	public $name = 'ProdCart';
		
	var $primaryKeyArray = array('organization_id', 'prod_delivery_id', 'article_organization_id', 'article_id', 'user_id');	
	/*
	 * organization_id = 0 perche' exists richiamato in ModelVAlidation::errors()
	 * gli altri perche' nessun metodo puo' avere piu' parametri richiesti rispetto al suo metodo principale. 
	*/		public function exists($organization_id=0, $prod_delivery_id=0, $article_organization_id=0, $article_id=0, $user_id=0) {				if(empty($organization_id) || empty($prod_delivery_id) || empty($article_organization_id) ||  empty($article_id) || empty($user_id)) {			return false;		}
		
		$conditions = array(
						$this->alias . '.organization_id' => $organization_id,
						$this->alias . '.prod_delivery_id' => $prod_delivery_id,
						$this->alias . '.article_organization_id' => $article_organization_id,
						$this->alias . '.article_id' => $article_id,
						$this->alias . '.user_id' => $user_id
						);
		
		return (bool)$this->find('count', array(				'conditions' => $conditions,				'recursive' => -1,				'callbacks' => false		));	}		public function read($organization_id=0, $prod_delivery_id=0, $article_organization_id=0, $article_id=0, $user_id=0, $fields = NULL, $id = NULL) {					$this->validationErrors = [];
		$conditions = array(				$this->alias . '.organization_id' => $organization_id,				$this->alias . '.prod_delivery_id' => $prod_delivery_id,
				$this->alias . '.article_organization_id' => $article_organization_id,
				$this->alias . '.article_id' => $article_id,				$this->alias . '.user_id' => $user_id		);
						$this->data = $this->find('first', array(				'conditions' => $conditions,				'recursive' => -1		));					return $this->data;	}		public function save($data = null, $validate = true, $fieldList = []) {					$success = false;					$defaults = array(				'validate' => true, 'fieldList' => [],				'callbacks' => true, 'counterCache' => true		);		$_whitelist = $this->whitelist;		$fields = [];					if (!is_array($validate)) {			$options = array_merge($defaults, compact('validate', 'fieldList'));		} else {			$options = array_merge($defaults, $validate);		}				$this->set($data);
		/*		 * ctrl se UPDATE o INSERT		*/		if(isset($this->data[$this->alias]['organization_id']))          $organization_id = $this->data[$this->alias]['organization_id'];		if(isset($this->data[$this->alias]['prod_delivery_id'])) 		 $prod_delivery_id = $this->data[$this->alias]['prod_delivery_id'];
		if(isset($this->data[$this->alias]['article_organization_id']))  $article_organization_id = $this->data[$this->alias]['article_organization_id'];
		if(isset($this->data[$this->alias]['article_id'])) 				 $article_id = $this->data[$this->alias]['article_id'];		if(isset($this->data[$this->alias]['user_id'])) 				 $user_id = $this->data[$this->alias]['user_id'];					if(!$this->exists($organization_id, $prod_delivery_id, $article_organization_id, $article_id, $user_id))			$created = true;		else			$created = false;					if($created) {			/*			 * insert			*/			$sql = "INSERT INTO					".Configure::read('DB.prefix')."prod_carts					(organization_id, prod_delivery_id, article_organization_id, article_id, user_id ";			if(isset($this->data[$this->alias]['qta'])) $sql .= ",qta";			if(isset($this->data[$this->alias]['qta_forzato']))   $sql .= ",qta_forzato";			if(isset($this->data[$this->alias]['importo_forzato'])) $sql .= ",importo_forzato";			if(isset($this->data[$this->alias]['nota'])) $sql .= ",nota";
			/*
			 * il campo Cart.datesi aggiorna in automatico ON UPDATE CURRENT_TIMESTAMP
			 */
			$sql .= ",stato, created)					VALUES (					".$organization_id.",					".$prod_delivery_id.",
					".$article_organization_id.",
					".$article_id.",					".$user_id;			if(isset($this->data[$this->alias]['qta'])) $sql .= ",".$this->importoToDatabase($this->data[$this->alias]['qta']);			if(isset($this->data[$this->alias]['qta_forzato'])) $sql .= ",".$this->importoToDatabase($this->data[$this->alias]['qta_forzato']);			if(isset($this->data[$this->alias]['importo_forzato'])) $sql .= ",".$this->importoToDatabase($this->data[$this->alias]['importo_forzato']);			if(isset($this->data[$this->alias]['nota'])) $sql .= ",'".addslashes($this->data[$this->alias]['nota'])."'";			if(isset($this->data[$this->alias]['stato'])) $sql .= ",'".$this->data[$this->alias]['stato']."'";			else $sql .= ",'Y'";
			$sql .= ",'".date('Y-m-d H:i:s')."'";
			$sql .= ")";				try {				$this->query($sql);
				$success = true;			}			catch (Exception $e) {				CakeLog::write('error',$sql);				CakeLog::write('error',$e);				$success = false;			}		}		else {			/*			 * update			*/			$sql = "UPDATE						".Configure::read('DB.prefix')."prod_carts					SET  ";			if(isset($this->data[$this->alias]['qta_forzato']))     $sql .= " qta_forzato = ".$this->importoToDatabase($this->data[$this->alias]['qta_forzato']).",";			if(isset($this->data[$this->alias]['importo_forzato'])) $sql .= " importo_forzato = ".$this->importoToDatabase($this->data[$this->alias]['importo_forzato']).",";			if(isset($this->data[$this->alias]['nota']))            $sql .= " nota = '".addslashes($this->data[$this->alias]['nota'])."',";			if(isset($this->data[$this->alias]['stato']))           $sql .= " stato = '".$this->data[$this->alias]['stato']."',";
			if(isset($this->data[$this->alias]['qta']))             $sql .= " qta = ".$this->importoToDatabase($this->data[$this->alias]['qta']).",";
			/*
			 * il campo Cart.date si aggiorna in automatico ON UPDATE CURRENT_TIMESTAMP
			*/
			$sql = substr($sql, 0, strlen($sql)-1);
			$sql .= "					WHERE						organization_id = ".$organization_id."						AND prod_delivery_id = ".$prod_delivery_id."
						AND article_organization_id = ".$article_organization_id."
						AND article_id = ".$article_id."						AND user_id = ".$user_id;			try {				$this->query($sql);				$success = true;			}			catch (Exception $e) {				CakeLog::write('error',$sql);				CakeLog::write('error',$e);				$success = false;			}		}					if ($success) {				if ($options['callbacks'] === true || $options['callbacks'] === 'after') {				$event = new CakeEvent('Model.afterSave', $this, array($created, $options));				$this->getEventManager()->dispatch($event);			}				if (!empty($this->data)) {				$success = $this->data;			}				$this->data = false;			$this->_clearCache();			$this->validationErrors = [];		}					return $success;	}
	/*	 * se user_id cancello tutti gli acquisti di un ordine	*/	public function delete($organization_id=0, $prod_delivery_id=0, $article_organization_id=0, $article_id=0, $user_id=0) {					$sql = "DELETE					FROM						".Configure::read('DB.prefix')."prod_carts	   				WHERE	   					organization_id = ".(int)$organization_id."
	   					AND prod_delivery_id = ".(int)$prod_delivery_id."
	   					AND article_organization_id = ".(int)$article_organization_id."	   					AND article_id = ".(int)$article_id;
		if(!empty($user_id)) $sql .= " AND user_id = ".(int)$user_id;		self::d($sql, false);		try {			$results = $this->query($sql);			$success=true;		}		catch (Exception $e) {			CakeLog::write('error',$sql);			CakeLog::write('error',$e);			$success=false;		}		return $success;	}	
}