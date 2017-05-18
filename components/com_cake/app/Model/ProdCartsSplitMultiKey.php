<?php
App::uses('AppModel', 'Model');
/* * override perche' $primaryKeyArray = array('organization_id', 'prod_delivery_id', 'article_id', 'user_id', 'num_split');*/class ProdCartsSplitMultiKey extends AppModel {
	
	public $name = 'ProdCartsSplit';
		
	var $primaryKeyArray = array('organization_id', 'prod_delivery_id', 'article_organization_id', 'article_id', 'user_id', 'num_split');	
	/*
	 * organization_id = 0 perche' exists richiamato in ModelVAlidation::errors()
	 * gli altri perche' nessun metodo puo' avere piu' parametri richiesti rispetto al suo metodo principale. 
	*/		public function exists($organization_id=0, $prod_delivery_id=0, $article_organization_id=0, $article_id=0, $user_id=0, $num_split=0) {
		if(empty($organization_id) || empty($prod_delivery_id) || empty($article_organization_id) || empty($article_id) || empty($user_id) || empty($num_split)) {			return false;		}
		
		$conditions = array(
						$this->alias . '.organization_id' => $organization_id,
						$this->alias . '.prod_delivery_id' => $prod_delivery_id,
						$this->alias . '.article_organization_id' => $article_organization_id,
						$this->alias . '.article_id' => $article_id,
						$this->alias . '.user_id' => $user_id,
						$this->alias . '.num_split' => $num_split
						);
		
		return (bool)$this->find('count', array(				'conditions' => $conditions,				'recursive' => -1,				'callbacks' => false		));	}		public function read($organization_id=0, $prod_delivery_id=0, $article_organization_id=0, $article_id=0, $user_id=0, $num_split=0, $fields = NULL, $id = NULL) {					$this->validationErrors = array();
		$conditions = array(				$this->alias . '.organization_id' => $organization_id,				$this->alias . '.prod_delivery_id' => $prod_delivery_id,
				$this->alias . '.article_organization_id' => $article_organization_id,
				$this->alias . '.article_id' => $article_id,				$this->alias . '.user_id' => $user_id,
						$this->alias . '.num_split' => $num_split		);
						$this->data = $this->find('first', array(				'conditions' => $conditions,				'recursive' => -1		));					return $this->data;	}		public function save($data = null, $validate = true, $fieldList = array()) {					$success = false;					$defaults = array(				'validate' => true, 'fieldList' => array(),				'callbacks' => true, 'counterCache' => true		);		$_whitelist = $this->whitelist;		$fields = array();					if (!is_array($validate)) {			$options = array_merge($defaults, compact('validate', 'fieldList'));		} else {			$options = array_merge($defaults, $validate);		}				$this->set($data);
		/*		 * ctrl se UPDATE o INSERT		*/		if(isset($this->data[$this->alias]['organization_id']))          $organization_id = $this->data[$this->alias]['organization_id'];		if(isset($this->data[$this->alias]['prod_delivery_id'])) 		         $prod_delivery_id = $this->data[$this->alias]['prod_delivery_id'];
		if(isset($this->data[$this->alias]['article_organization_id']))  $article_organization_id = $this->data[$this->alias]['article_organization_id'];
		if(isset($this->data[$this->alias]['article_id'])) 				 $article_id = $this->data[$this->alias]['article_id'];
		if(isset($this->data[$this->alias]['user_id'])) 				 $user_id = $this->data[$this->alias]['user_id'];
		if(isset($this->data[$this->alias]['num_split'])) 				 $num_split = $this->data[$this->alias]['num_split'];					if(!$this->exists($organization_id, $prod_delivery_id, $article_organization_id, $article_id, $user_id, $num_split))			$created = true;		else			$created = false;

		if($created) {			/*			 * insert			*/			$sql = "INSERT INTO					".Configure::read('DB.prefix')."prod_carts_splits					(organization_id, prod_delivery_id, article_organization_id, article_id, user_id, num_split, importo_forzato, created)";
			$sql .= "					VALUES (					".$organization_id.",					".$prod_delivery_id.",
					".$article_organization_id.",
					".$article_id.",
					".$user_id.",
					".$num_split;			if(isset($this->data[$this->alias]['importo_forzato'])) $sql .= ",".$this->importoToDatabase($this->data[$this->alias]['importo_forzato']);
			else $sql .= ", 0.00";
			$sql .= ",'".date('Y-m-d H:i:s')."'";
			$sql .= ")";				try {				$this->query($sql);
				$success = true;			}			catch (Exception $e) {				CakeLog::write('error',$sql);				CakeLog::write('error',$e);				$success = false;			}		}		else {			/*			 * update			*/			$sql = "UPDATE						".Configure::read('DB.prefix')."prod_carts_splits					SET  modified = '".date('Y-m-d H:i:s')."',";			if(isset($this->data[$this->alias]['importo_forzato'])) $sql .= " importo_forzato = ".$this->importoToDatabase($this->data[$this->alias]['importo_forzato']).",";			$sql = substr($sql, 0, strlen($sql)-1);
			$sql .= "					WHERE						organization_id = ".$organization_id."						AND prod_delivery_id = ".$prod_delivery_id."
						AND article_organization_id = ".$article_organization_id."
						AND article_id = ".$article_id."
						AND user_id = ".$user_id."
						AND num_split = ".$num_split;
			// echo $sql;			try {				$this->query($sql);				$success = true;			}			catch (Exception $e) {				CakeLog::write('error',$sql);				CakeLog::write('error',$e);				$success = false;			}		}					if ($success) {				if ($options['callbacks'] === true || $options['callbacks'] === 'after') {				$event = new CakeEvent('Model.afterSave', $this, array($created, $options));				$this->getEventManager()->dispatch($event);			}				if (!empty($this->data)) {				$success = $this->data;			}				$this->data = false;			$this->_clearCache();			$this->validationErrors = array();		}					return $success;	}
	/*	 * se user_id cancello tutti gli acquisti di una consegna	*/	public function delete($organization_id=0, $prod_delivery_id=0, $article_organization_id=0, $article_id=0, $user_id=0, $num_split=0) {					$sql = "DELETE					FROM						".Configure::read('DB.prefix')."prod_carts_splits	   				WHERE	   					organization_id = ".(int)$organization_id."
	   					AND prod_delivery_id = ".(int)$prod_delivery_id."
	   					AND article_organization_id = ".(int)$article_organization_id."
	   					AND article_id = ".(int)$article_id."
	   					AND user_id = ".(int)$user_id;
		if(!empty($num_split)) $sql .= " AND num_split = ".(int)$num_split;		// echo '<br />'.$sql;		try {			$results = $this->query($sql);			$success=true;		}		catch (Exception $e) {			CakeLog::write('error',$sql);			CakeLog::write('error',$e);			$success=false;		}		return $success;	}	
}