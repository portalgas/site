<?php

App::uses('AppModel', 'Model');
/* * override perche' $primaryKeyArray = array('organization_id', 'prod_delivery_id', 'article_id');*/
class ProdDeliveriesArticleMultiKey extends AppModel {

	public $name = 'ProdDeliveriesArticle';
	private $debug = false;
	
	var $primaryKeyArray = array('organization_id', 'prod_delivery_id', 'article_organization_id', 'article_id'); 
	var $organization_id = 0;
	var $prod_delivery_id = 0;
	var $article_id = 0;
	
	/*
	 * organization_id = 0 perche' exists richiamato in ModelVAlidation::errors()
	 * gli altri perche' nessun metodo puo' avere piu' parametri richiesti rispetto al suo metodo principale. 
	*/	
	public function exists($organization_id=0, $prod_delivery_id=0, $article_organization_id=0, $article_id=0) {		
		if(empty($organization_id) || empty($prod_delivery_id) || empty($article_organization_id) || empty($article_id)) {			return false;		}
		
		$options['conditions'] = array($this->alias . '.organization_id' => $organization_id,
									   $this->alias . '.prod_delivery_id'  => $prod_delivery_id,
									   $this->alias . '.article_organization_id' => $article_organization_id,
									   $this->alias . '.article_id' => $article_id
									   );
		
		if($this->debug) {
			echo "<pre>exists() ";
			print_r($options);
			echo "</pre>";
		}
		
		$options['recursive'] = -1;
		$options['callbacks'] = false;		return (bool)$this->find('count', $options);	}	
	public function read($organization_id=0, $prod_delivery_id=0, $article_organization_id=0, $article_id=0, $fields = NULL, $id = NULL) {		 		$this->validationErrors = [];
		$options['conditions'] = array($this->alias . '.organization_id' => $organization_id,										$this->alias . '.prod_delivery_id'  => $prod_delivery_id,
									    $this->alias . '.article_organization_id' => $article_organization_id,										$this->alias . '.article_id' => $article_id								);		$options['recursive'] = 1;		$this->data = $this->find('first', $options);		
		return $this->data;	}
	
	/*
	 * override perche' $primaryKeyArray = array('organization_id', 'prod_delivery_id', 'article_organization_id', 'article_id'); 
	 */
	public function save($data = null, $validate = true, $fieldList = []) {
		
		$success = false;
		
		$defaults = array(				'validate' => true, 'fieldList' => [],				'callbacks' => true, 'counterCache' => true		);		$_whitelist = $this->whitelist;		$fields = [];				if (!is_array($validate)) {			$options = array_merge($defaults, compact('validate', 'fieldList'));		} else {			$options = array_merge($defaults, $validate);		}
		
		$this->set($data);
		
		if($this->debug) {
			echo "<pre>save() ";
			print_r($data);
			echo "</pre>";
		}
		
		/*		* ctrl se UPDATE o INSERT		*/		if(isset($this->data[$this->alias]['organization_id']))         $this->organization_id = $this->data[$this->alias]['organization_id'];		if(isset($this->data[$this->alias]['prod_delivery_id'])) 		        $this->prod_delivery_id = $this->data[$this->alias]['prod_delivery_id'];
		if(isset($this->data[$this->alias]['article_organization_id'])) $this->article_organization_id = $this->data[$this->alias]['article_organization_id'];
		if(isset($this->data[$this->alias]['article_id'])) 		        $this->article_id = $this->data[$this->alias]['article_id'];				if(!$this->exists($this->organization_id, $this->prod_delivery_id, $this->article_organization_id, $this->article_id))
			$created = true;
		else
			$created = false;
	
		if($created) {
			/*
			 * insert
			 */
			$sql = "INSERT INTO 
					".Configure::read('DB.prefix')."prod_deliveries_articles 
					(organization_id, prod_delivery_id, article_organization_id, article_id ";
			if(isset($this->data[$this->alias]['qta_cart'])) $sql .= ",qta_cart";
			if(isset($this->data[$this->alias]['prezzo']))   $sql .= ",prezzo";
			if(isset($this->data[$this->alias]['pezzi_confezione'])) $sql .= ",pezzi_confezione";
			if(isset($this->data[$this->alias]['qta_minima'])) $sql .= ",qta_minima";
			if(isset($this->data[$this->alias]['qta_massima'])) $sql .= ",qta_massima";
			if(isset($this->data[$this->alias]['qta_minima_order'])) $sql .= ",qta_minima_order";
			if(isset($this->data[$this->alias]['qta_massima_order'])) $sql .= ",qta_massima_order";
			if(isset($this->data[$this->alias]['qta_multipli'])) $sql .= ",qta_multipli";
			if(isset($this->data[$this->alias]['alert_to_qta'])) $sql .= ",alert_to_qta";
			if(isset($this->data[$this->alias]['stato'])) $sql .= ",stato";
			$sql .= ",created)	
					VALUES (
					".$this->data[$this->alias]['organization_id'].",
					".$this->data[$this->alias]['prod_delivery_id'].",
					".$this->data[$this->alias]['article_organization_id'].",
					".$this->data[$this->alias]['article_id'];
			if(isset($this->data[$this->alias]['qta_cart'])) $sql .= ",".$this->data[$this->alias]['qta_cart'];
			if(isset($this->data[$this->alias]['prezzo']))   $sql .= ",".$this->importoToDatabase($this->data[$this->alias]['prezzo']);
			if(isset($this->data[$this->alias]['pezzi_confezione'])) $sql .= ",".$this->data[$this->alias]['pezzi_confezione'];
			if(isset($this->data[$this->alias]['qta_minima'])) $sql .= ",".$this->data[$this->alias]['qta_minima'];
			if(isset($this->data[$this->alias]['qta_massima'])) $sql .= ",".$this->data[$this->alias]['qta_massima'];
			if(isset($this->data[$this->alias]['qta_minima_order'])) $sql .= ",".$this->data[$this->alias]['qta_minima_order'];
			if(isset($this->data[$this->alias]['qta_massima_order'])) $sql .= ",".$this->data[$this->alias]['qta_massima_order'];
			if(isset($this->data[$this->alias]['qta_multipli'])) $sql .= ",".$this->data[$this->alias]['qta_multipli'];
			if(isset($this->data[$this->alias]['alert_to_qta'])) $sql .= ",".$this->data[$this->alias]['alert_to_qta'];
			if(isset($this->data[$this->alias]['stato'])) $sql .= ",'".$this->data[$this->alias]['stato']."'";
			$sql .= ",'".date('Y-m-d H:i:s')."'";			
			$sql .= ")";
			if($this->debug) {
				echo "<pre>save:INSERT() ";
				print_r($sql);
				echo "</pre>";
			}
			
			try {
				$this->query($sql);	
				$success = true;
			}			catch (Exception $e) {				CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
				$success = false;			}						
		}
		else {
			/*
			 * update
			 */
			$sql = "UPDATE 						".Configure::read('DB.prefix')."prod_deliveries_articles
					SET ";
			if(isset($this->data[$this->alias]['qta_cart'])) $sql .= " qta_cart = ".$this->data[$this->alias]['qta_cart'].",";			if(isset($this->data[$this->alias]['prezzo']))   $sql .= " prezzo = ".$this->importoToDatabase($this->data[$this->alias]['prezzo']).",";			if(isset($this->data[$this->alias]['pezzi_confezione'])) $sql .= " pezzi_confezione = ".$this->data[$this->alias]['pezzi_confezione'].",";
			if(isset($this->data[$this->alias]['qta_minima'])) $sql .= " qta_minima = ".$this->data[$this->alias]['qta_minima'].",";
			if(isset($this->data[$this->alias]['qta_massima'])) $sql .= " qta_massima = ".$this->data[$this->alias]['qta_massima'].",";
			if(isset($this->data[$this->alias]['qta_minima_order'])) $sql .= " qta_minima_order = ".$this->data[$this->alias]['qta_minima_order'].",";
			if(isset($this->data[$this->alias]['qta_massima_order'])) $sql .= " qta_massima_order = ".$this->data[$this->alias]['qta_massima_order'].",";			if(isset($this->data[$this->alias]['qta_multipli'])) $sql .= " qta_multipli = ".$this->data[$this->alias]['qta_multipli'].",";			if(isset($this->data[$this->alias]['alert_to_qta'])) $sql .= " alert_to_qta = ".$this->data[$this->alias]['alert_to_qta'].",";			if(isset($this->data[$this->alias]['stato'])) $sql .= " stato = '".$this->data[$this->alias]['stato']."',";			$sql .= " modified = '".date('Y-m-d H:i:s')."'
					WHERE 						organization_id = ".$this->data[$this->alias]['organization_id']."
						AND prod_delivery_id = ".$this->data[$this->alias]['prod_delivery_id']."
						AND article_organization_id = ".$this->data[$this->alias]['article_organization_id']."
						AND article_id = ".$this->data[$this->alias]['article_id'];
			if($this->debug) {
				echo "<pre>save:UPDATE() ";
				print_r($sql);
				echo "</pre>";
			}						try {				$this->query($sql);				$success = true;			}			catch (Exception $e) {				CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
				$success = false;			}	
		}

		if ($success) {				if ($options['callbacks'] === true || $options['callbacks'] === 'after') {				$event = new CakeEvent('Model.afterSave', $this, array($created, $options));				$this->getEventManager()->dispatch($event);			}				if (!empty($this->data)) {				$success = $this->data;			}				$this->data = false;			$this->_clearCache();			$this->validationErrors = [];		}	
		if($this->debug) 
			exit;
				return $success;	}
		
	/*	 * se article_id cancello tutti gli articoli di un ordine	*/	
	public function delete($organization_id=0, $prod_delivery_id=0, $article_organization_id=0, $article_id=0) {
		
		$sql = "DELETE 				FROM					".Configure::read('DB.prefix')."prod_deliveries_articles     				WHERE
   					organization_id = ".(int)$organization_id." 
   					AND prod_delivery_id = ".(int)$prod_delivery_id;
		if(!empty($article_organization_id) && !empty($article_id)) $sql .= " AND article_organization_id ".(int)$article_organization_id." AND article_id = ".(int)$article_id;		self::d($sql, false);		try {
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