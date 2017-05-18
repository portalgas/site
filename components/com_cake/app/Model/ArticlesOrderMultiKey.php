<?php
App::uses('AppModel', 'Model');

class ArticlesOrderMultiKey extends AppModel {

	public $name = 'ArticlesOrder';
	private $debug = false;
	
	var $primaryKeyArray = array('organization_id', 'order_id', 'article_organization_id', 'article_id'); 
	var $organization_id = 0;
	var $order_id = 0;
	var $article_id = 0;
	
	/*
	 * organization_id = 0 perche' exists richiamato in ModelVAlidation::errors()
	 * gli altri perche' nessun metodo puo' avere piu' parametri richiesti rispetto al suo metodo principale. 
	*/	
	public function exists($organization_id=0, $order_id=0, $article_organization_id=0, $article_id=0) {
		if(empty($organization_id) || empty($order_id) || empty($article_organization_id) || empty($article_id)) {
		
		$options['conditions'] = array($this->alias . '.organization_id' => $organization_id,
									   $this->alias . '.order_id'  => $order_id,
									   $this->alias . '.article_organization_id' => $article_organization_id,
									   $this->alias . '.article_id' => $article_id
									   );
		
		if($this->debug) {
			echo "<pre>exists() ";
			print_r($options);
			echo "</pre>";
		}
		
		$options['recursive'] = -1;
		$options['callbacks'] = false;
	public function read($organization_id=0, $order_id=0, $article_organization_id=0, $article_id=0, $fields = NULL, $id = NULL) {
		$options['conditions'] = array($this->alias . '.organization_id' => $organization_id,
									    $this->alias . '.article_organization_id' => $article_organization_id,
		return $this->data;
	
	/*
	 * override perche' $primaryKeyArray = array('organization_id', 'order_id', 'article_organization_id', 'article_id'); 
	 */
	public function save($data = null, $validate = true, $fieldList = array()) {
		
		$success = false;
		
		$defaults = array(
		
		$this->set($data);
		
		if($this->debug) {
			echo "<pre>save() ";
			print_r($data);
			echo "</pre>";
		}
		
		/*
		if(isset($this->data[$this->alias]['article_organization_id'])) $this->article_organization_id = $this->data[$this->alias]['article_organization_id'];
		if(isset($this->data[$this->alias]['article_id'])) 		        $this->article_id = $this->data[$this->alias]['article_id'];
			$created = true;
		else
			$created = false;
	
		if($created) {
			/*
			 * insert
			 */
			$sql = "INSERT INTO 
					".Configure::read('DB.prefix')."articles_orders 
					(organization_id, order_id, article_organization_id, article_id ";
			if(isset($this->data[$this->alias]['qta_cart'])) $sql .= ",qta_cart";
			if(isset($this->data[$this->alias]['name']))   $sql .= ",name";
			if(isset($this->data[$this->alias]['prezzo']))   $sql .= ",prezzo";
			if(isset($this->data[$this->alias]['pezzi_confezione'])) $sql .= ",pezzi_confezione";
			if(isset($this->data[$this->alias]['qta_minima'])) $sql .= ",qta_minima";
			if(isset($this->data[$this->alias]['qta_massima'])) $sql .= ",qta_massima";
			if(isset($this->data[$this->alias]['qta_minima_order'])) $sql .= ",qta_minima_order";
			if(isset($this->data[$this->alias]['qta_massima_order'])) $sql .= ",qta_massima_order";
			if(isset($this->data[$this->alias]['qta_multipli'])) $sql .= ",qta_multipli";
			if(isset($this->data[$this->alias]['alert_to_qta'])) $sql .= ",alert_to_qta";
			if(isset($this->data[$this->alias]['send_mail'])) $sql .= ",send_mail";
			if(isset($this->data[$this->alias]['stato'])) $sql .= ",stato";
			$sql .= ",created)	
					VALUES (
					".$this->data[$this->alias]['organization_id'].",
					".$this->data[$this->alias]['order_id'].",
					".$this->data[$this->alias]['article_organization_id'].",
					".$this->data[$this->alias]['article_id'];
			if(isset($this->data[$this->alias]['qta_cart'])) $sql .= ",".$this->data[$this->alias]['qta_cart'];
			if(isset($this->data[$this->alias]['name'])) $sql .= ",'".addslashes($this->data[$this->alias]['name'])."'";
			if(isset($this->data[$this->alias]['prezzo']))   $sql .= ",".$this->importoToDatabase($this->data[$this->alias]['prezzo']);
			if(isset($this->data[$this->alias]['pezzi_confezione'])) $sql .= ",".$this->data[$this->alias]['pezzi_confezione'];
			if(isset($this->data[$this->alias]['qta_minima'])) $sql .= ",".$this->data[$this->alias]['qta_minima'];
			if(isset($this->data[$this->alias]['qta_massima'])) $sql .= ",".$this->data[$this->alias]['qta_massima'];
			if(isset($this->data[$this->alias]['qta_minima_order'])) $sql .= ",".$this->data[$this->alias]['qta_minima_order'];
			if(isset($this->data[$this->alias]['qta_massima_order'])) $sql .= ",".$this->data[$this->alias]['qta_massima_order'];
			if(isset($this->data[$this->alias]['qta_multipli'])) $sql .= ",".$this->data[$this->alias]['qta_multipli'];
			if(isset($this->data[$this->alias]['alert_to_qta'])) $sql .= ",".$this->data[$this->alias]['alert_to_qta'];
			if(isset($this->data[$this->alias]['send_mail'])) $sql .= ",'".$this->data[$this->alias]['send_mail']."'";
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
			}
				CakeLog::write('error',$e);
				$success = false;
			}						
		}
		else {
			/*
			 * update
			 */
			$sql = "UPDATE 
					SET ";
			if(isset($this->data[$this->alias]['qta_cart'])) $sql .= " qta_cart = ".$this->data[$this->alias]['qta_cart'].",";
			if(isset($this->data[$this->alias]['prezzo']))   $sql .= " prezzo = ".$this->importoToDatabase($this->data[$this->alias]['prezzo']).",";
			if(isset($this->data[$this->alias]['qta_minima'])) $sql .= " qta_minima = ".$this->data[$this->alias]['qta_minima'].",";
			if(isset($this->data[$this->alias]['qta_massima'])) $sql .= " qta_massima = ".$this->data[$this->alias]['qta_massima'].",";
			if(isset($this->data[$this->alias]['qta_massima_order'])) $sql .= " qta_massima_order = ".$this->data[$this->alias]['qta_massima_order'].",";
			if(isset($this->data[$this->alias]['alert_to_qta'])) $sql .= " alert_to_qta = ".$this->data[$this->alias]['alert_to_qta'].",";
			if(isset($this->data[$this->alias]['send_mail'])) $sql .= " send_mail = '".$this->data[$this->alias]['send_mail']."',";
					WHERE 
						AND order_id = ".$this->data[$this->alias]['order_id']."
						AND article_organization_id = ".$this->data[$this->alias]['article_organization_id']."
						AND article_id = ".$this->data[$this->alias]['article_id'];
			if($this->debug) {
				echo "<pre>save:UPDATE() ";
				print_r($sql);
				echo "</pre>";
			}			
				CakeLog::write('error',$e);
				$success = false;
		}

		if ($success) {
		if($this->debug) 
			exit;
		
		
	/*
	public function delete($organization_id=0, $order_id=0, $article_organization_id=0, $article_id=0) {
		
		$sql = "DELETE 
   					organization_id = ".(int)$organization_id." 
   					AND order_id = ".(int)$order_id;
		if(!empty($article_organization_id) && !empty($article_id)) $sql .= " AND article_organization_id = ".(int)$article_organization_id." AND article_id = ".(int)$article_id;
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