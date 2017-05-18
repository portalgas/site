<?php
App::uses('AppModel', 'Model');

	public $name = 'Cart';
		
	var $primaryKeyArray = array('organization_id', 'order_id', 'article_organization_id', 'article_id', 'user_id');
	/*
	 * organization_id = 0 perche' exists richiamato in ModelVAlidation::errors()
	 * gli altri perche' nessun metodo puo' avere piu' parametri richiesti rispetto al suo metodo principale. 
	*/	
		
		$conditions = array(
						$this->alias . '.organization_id' => $organization_id,
						$this->alias . '.order_id' => $order_id,
						$this->alias . '.article_organization_id' => $article_organization_id,
						$this->alias . '.article_id' => $article_id,
						$this->alias . '.user_id' => $user_id
						);

		return (bool)$this->find('count', array(
		$conditions = array(
				$this->alias . '.article_organization_id' => $article_organization_id,
				$this->alias . '.article_id' => $article_id,
				
		/*
		if(isset($this->data[$this->alias]['article_organization_id']))  $article_organization_id = $this->data[$this->alias]['article_organization_id'];
		if(isset($this->data[$this->alias]['article_id'])) 				 $article_id = $this->data[$this->alias]['article_id'];
			
			/*
			 * il campo Cart.date si aggiorna in automatico ON UPDATE CURRENT_TIMESTAMP
			 */
			$sql .= ",deleteToReferent, stato, created)
					".$article_organization_id.",
					".$article_id.",
			if(isset($this->data[$this->alias]['deleteToReferent']))
				$sql .= ",'".$this->data[$this->alias]['deleteToReferent']."'";
			else
				$sql .= ",'".$this->data[$this->alias]['deleteToReferent']."'";
				
			if(isset($this->data[$this->alias]['stato'])) 
				$sql .= ",'".$this->data[$this->alias]['stato']."'";
			else 
				$sql .= ",'Y'";
			
			$sql .= ",'".date('Y-m-d H:i:s')."'";
			$sql .= ")";
				$success = true;
			if(isset($this->data[$this->alias]['qta']))             $sql .= " qta = ".$this->importoToDatabase($this->data[$this->alias]['qta']).",";
			if(isset($this->data[$this->alias]['deleteToReferent'])) $sql .= " deleteToReferent = '".$this->data[$this->alias]['deleteToReferent']."',";
			else $sql .= " deleteToReferent = 'Y',";
			/*
			 * il campo Cart.date si aggiorna in automatico ON UPDATE CURRENT_TIMESTAMP
			*/
			$sql = substr($sql, 0, strlen($sql)-1);
			$sql .= "
						AND article_organization_id = ".$article_organization_id."
						AND article_id = ".$article_id."
	/*
	   					AND order_id = ".(int)$order_id."
	   					AND article_organization_id = ".(int)$article_organization_id."
		if(!empty($user_id)) $sql .= " AND user_id = ".(int)$user_id;
}