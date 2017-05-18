<?php
App::uses('AppModel', 'Model');

/* * override perche' $primaryKeyArray = array('organization_id', 'id');*/
class ArticleMultiKey extends AppModel {

   public $name = 'Article';
   
   var $primaryKeyArray = array('organization_id', 'id');   var $organization_id = 0;
   var $id = 0;
	
	/*
	 * organization_id = 0 perche' exists richiamato in ModelVAlidation::errors()
	 * gli altri perche' nessun metodo puo' avere piu' parametri richiesti rispetto al suo metodo principale. 
	*/	
   public function exists($organization_id=0, $article_id=0) {      	if(empty($organization_id) || empty($article_id)) {   		return false;   	}
   	return (bool)$this->find('count', array(   			'conditions' => array(   					$this->alias . '.id' => $article_id,   					$this->alias . '.organization_id' => $organization_id   			),   			'recursive' => -1,   			'callbacks' => false   	));   }      public function read($organization_id=0, $article_id=0, $fields = NULL, $id = NULL) {   		   	$this->validationErrors = array();   		   	$this->data = $this->find('first', array(   			'conditions' => array($this->alias . '.organization_id' => $organization_id,   								  $this->alias . '.id' => $article_id,   			),   			'recursive' => -1   	));      	return $this->data;   }      public function save($data = null, $validate = true, $fieldList = array()) {      	$success = false;      	$defaults = array(   			'validate' => true, 'fieldList' => array(),   			'callbacks' => true, 'counterCache' => true   	);   	$_whitelist = $this->whitelist;   	$fields = array();      	if (!is_array($validate)) {   		$options = array_merge($defaults, compact('validate', 'fieldList'));   	} else {   		$options = array_merge($defaults, $validate);   	}      	$this->set($data);      	/*   		* ctrl se UPDATE o INSERT   	*/   	if(isset($this->data[$this->alias]['organization_id'])) $this->organization_id = $this->data[$this->alias]['organization_id'];   	if(isset($this->data[$this->alias]['id'])) 				$this->id = $this->data[$this->alias]['id'];      	if(!$this->exists($this->organization_id, $this->id))   		$created = true;   	else   		$created = false;      	if($created) {   		/*   		 * insert   		*/   		$sql = "INSERT INTO					".Configure::read('DB.prefix')."articles 					(organization_id, id ";
   		if(isset($this->data[$this->alias]['supplier_organization_id'])) $sql .= ",supplier_organization_id";
   		if(isset($this->data[$this->alias]['supplier_id'])) $sql .= ",supplier_id";
   		if(isset($this->data[$this->alias]['prod_gas_article_id'])) $sql .= ",prod_gas_article_id";   		if(isset($this->data[$this->alias]['category_article_id']))   $sql .= ",category_article_id";   		if(isset($this->data[$this->alias]['name'])) $sql .= ",name";
   		if(isset($this->data[$this->alias]['codice'])) $sql .= ",codice";
   		if(isset($this->data[$this->alias]['nota'])) $sql .= ",nota";
   		if(isset($this->data[$this->alias]['ingredienti'])) $sql .= ",ingredienti";
   		if(isset($this->data[$this->alias]['prezzo'])) $sql .= ",prezzo";
   		if(isset($this->data[$this->alias]['qta'])) $sql .= ",qta";
   		if(isset($this->data[$this->alias]['um'])) $sql .= ",um";
   		if(isset($this->data[$this->alias]['um_riferimento'])) $sql .= ",um_riferimento";
   		if(isset($this->data[$this->alias]['pezzi_confezione'])) $sql .= ",pezzi_confezione";
   		if(isset($this->data[$this->alias]['qta_minima'])) $sql .= ",qta_minima";
   		if(isset($this->data[$this->alias]['qta_massima'])) $sql .= ",qta_massima";
   		if(isset($this->data[$this->alias]['qta_minima_order'])) $sql .= ",qta_minima_order";
   		if(isset($this->data[$this->alias]['qta_massima_order'])) $sql .= ",qta_massima_order";   		if(isset($this->data[$this->alias]['qta_multipli'])) $sql .= ",qta_multipli";   		if(isset($this->data[$this->alias]['alert_to_qta'])) $sql .= ",alert_to_qta";
   		if(isset($this->data[$this->alias]['bio'])) $sql .= ",bio";
   		if(isset($this->data[$this->alias]['img1'])) $sql .= ",img1";
   		if(isset($this->data[$this->alias]['stato'])) $sql .= ",stato";
   		if(isset($this->data[$this->alias]['flag_presente_articlesorders'])) $sql .= ",flag_presente_articlesorders";
   		   		$sql .= ",created)					VALUES (					".$this->organization_id.",					".$this->id;
   		if(isset($this->data[$this->alias]['supplier_organization_id'])) $sql .= ",".$this->data[$this->alias]['supplier_organization_id'];
   		if(isset($this->data[$this->alias]['supplier_id'])) $sql .= ",".$this->data[$this->alias]['supplier_id'];
   		if(isset($this->data[$this->alias]['prod_gas_article_id'])) $sql .= ",".$this->data[$this->alias]['prod_gas_article_id'];   		if(isset($this->data[$this->alias]['category_article_id']))   $sql .= ",".$this->data[$this->alias]['category_article_id'];   		if(isset($this->data[$this->alias]['name'])) $sql .= ",'".addslashes($this->data[$this->alias]['name'])."'";   		if(isset($this->data[$this->alias]['codice'])) $sql .= ",'".addslashes($this->data[$this->alias]['codice'])."'";   		if(isset($this->data[$this->alias]['nota'])) $sql .= ",'".addslashes($this->data[$this->alias]['nota'])."'";   		if(isset($this->data[$this->alias]['ingredienti'])) $sql .= ",'".addslashes($this->data[$this->alias]['ingredienti'])."'";
   		if(isset($this->data[$this->alias]['prezzo'])) $sql .= ",".$this->importoToDatabase($this->data[$this->alias]['prezzo']);
   		if(isset($this->data[$this->alias]['qta'])) $sql .= ",".$this->importoToDatabase($this->data[$this->alias]['qta']);
   		if(isset($this->data[$this->alias]['um'])) $sql .= ",'".$this->data[$this->alias]['um']."'";   		if(isset($this->data[$this->alias]['um_riferimento'])) $sql .= ",'".$this->data[$this->alias]['um_riferimento']."'";
   		if(isset($this->data[$this->alias]['pezzi_confezione'])) $sql .= ",".$this->data[$this->alias]['pezzi_confezione'];
   		if(isset($this->data[$this->alias]['qta_minima'])) $sql .= ",".$this->data[$this->alias]['qta_minima'];
   		if(isset($this->data[$this->alias]['qta_massima'])) $sql .= ",".$this->data[$this->alias]['qta_massima'];
   		if(isset($this->data[$this->alias]['qta_minima_order'])) $sql .= ",".$this->data[$this->alias]['qta_minima_order'];
   		if(isset($this->data[$this->alias]['qta_massima_order'])) $sql .= ",".$this->data[$this->alias]['qta_massima_order'];
   		if(isset($this->data[$this->alias]['qta_multipli'])) $sql .= ",".$this->data[$this->alias]['qta_multipli'];
   		if(isset($this->data[$this->alias]['alert_to_qta'])) $sql .= ",".$this->data[$this->alias]['alert_to_qta'];
   		if(isset($this->data[$this->alias]['bio'])) $sql .= ",'".$this->data[$this->alias]['bio']."'";
   		if(isset($this->data[$this->alias]['img1'])) $sql .= ",'".$this->data[$this->alias]['img1']."'";
   		if(isset($this->data[$this->alias]['stato'])) $sql .= ",'".$this->data[$this->alias]['stato']."'";
   		if(isset($this->data[$this->alias]['flag_presente_articlesorders'])) $sql .= ",'".$this->data[$this->alias]['flag_presente_articlesorders']."'";
   		   		 $sql .= ",'".date('Y-m-d H:i:s')."'";   		 $sql .= ")";   		// echo '<br />'.$sql;
   		   		try {   			   			$this->query($sql);
   			$success = true;   		}   		catch (Exception $e) {   			CakeLog::write('error',$sql);   			CakeLog::write('error',$e);   			$success = false;   		}   	}   	else {   		/*   		 * update   		*/   		$sql = "UPDATE						".Configure::read('DB.prefix')."articles					SET ";
   		if(isset($this->data[$this->alias]['supplier_organization_id'])) $sql .= " supplier_organization_id = ".$this->data[$this->alias]['supplier_organization_id'].",";
   		if(isset($this->data[$this->alias]['supplier_id'])) $sql .= " supplier_id = ".$this->data[$this->alias]['supplier_id'].",";
   		if(isset($this->data[$this->alias]['prod_gas_article_id'])) $sql .= " prod_gas_article_id = ".$this->data[$this->alias]['prod_gas_article_id'].",";   		if(isset($this->data[$this->alias]['category_article_id']))   $sql .= " category_article_id = ".$this->data[$this->alias]['category_article_id'].",";   		if(isset($this->data[$this->alias]['name'])) $sql .= " name = '".addslashes($this->data[$this->alias]['name'])."',";   		if(isset($this->data[$this->alias]['codice'])) $sql .= " codice = '".addslashes($this->data[$this->alias]['codice'])."',";   		if(isset($this->data[$this->alias]['nota'])) $sql .= " nota = '".addslashes($this->data[$this->alias]['nota'])."',";   		if(isset($this->data[$this->alias]['ingredienti'])) $sql .= " ingredienti = '".addslashes($this->data[$this->alias]['ingredienti'])."',";   		if(isset($this->data[$this->alias]['prezzo'])) $sql .= " prezzo = ".$this->importoToDatabase($this->data[$this->alias]['prezzo']).",";   		if(isset($this->data[$this->alias]['qta'])) $sql .= " qta = ".$this->importoToDatabase($this->data[$this->alias]['qta']).",";   		if(isset($this->data[$this->alias]['um'])) $sql .= " um = '".$this->data[$this->alias]['um']."',";   		if(isset($this->data[$this->alias]['um_riferimento'])) $sql .= " um_riferimento = '".$this->data[$this->alias]['um_riferimento']."',";   		if(isset($this->data[$this->alias]['pezzi_confezione'])) $sql .= " pezzi_confezione = ".$this->data[$this->alias]['pezzi_confezione'].",";
   		if(isset($this->data[$this->alias]['qta_minima'])) $sql .= " qta_minima = ".$this->data[$this->alias]['qta_minima'].",";
   		if(isset($this->data[$this->alias]['qta_massima'])) $sql .= " qta_massima = ".$this->data[$this->alias]['qta_massima'].",";
   		if(isset($this->data[$this->alias]['qta_minima_order'])) $sql .= " qta_minima_order = ".$this->data[$this->alias]['qta_minima_order'].",";
   		if(isset($this->data[$this->alias]['qta_massima_order'])) $sql .= " qta_massima_order = ".$this->data[$this->alias]['qta_massima_order'].",";   		if(isset($this->data[$this->alias]['qta_multipli'])) $sql .= " qta_multipli = ".$this->data[$this->alias]['qta_multipli'].",";   		if(isset($this->data[$this->alias]['alert_to_qta'])) $sql .= " alert_to_qta = ".$this->data[$this->alias]['alert_to_qta'].",";   		if(isset($this->data[$this->alias]['bio'])) $sql .= " bio = '".$this->data[$this->alias]['bio']."',";
   		if(isset($this->data[$this->alias]['img1'])) $sql .= " img1 = '".$this->data[$this->alias]['img1']."',";
   		if(isset($this->data[$this->alias]['stato'])) $sql .= " stato = '".$this->data[$this->alias]['stato']."',";
   		if(isset($this->data[$this->alias]['flag_presente_articlesorders'])) $sql .= " flag_presente_articlesorders = '".$this->data[$this->alias]['flag_presente_articlesorders']."',";
   		   		$sql .= " modified = '".date('Y-m-d H:i:s')."'					WHERE						organization_id = ".$this->organization_id."						AND id = ".$this->id;
   		// echo '<br />'.$sql;
   		      		try {   			$this->query($sql);   			$success = true;   		}   		catch (Exception $e) {   			CakeLog::write('error',$sql);   			CakeLog::write('error',$e);   			$success = false;   		}      	}      	if ($success) {      		if ($options['callbacks'] === true || $options['callbacks'] === 'after') {   			$event = new CakeEvent('Model.afterSave', $this, array($created, $options));   			$this->getEventManager()->dispatch($event);   		}      		if (!empty($this->data)) {   			$success = $this->data;   		}      		$this->data = false;   		$this->_clearCache();   		$this->validationErrors = array();   	}      	return $success;   }    
   public function delete($organization_id=0, $article_id=0) {   	   	$sql = "DELETE					FROM						".Configure::read('DB.prefix')."articles	   				WHERE	   					organization_id = ".(int)$organization_id."	   					AND id = ".(int)$article_id."	   					";	   	// echo '<br />'.$sql;	   	try {	   		$results = $this->query($sql);	   		$success=true;	   	}
	   	catch (Exception $e) {	   		CakeLog::write('error',$sql);	   		CakeLog::write('error',$e);	   		$success=false;	   	}	   	return $success;   }
}