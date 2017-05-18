<?php
App::uses('AppModel', 'Model');

	
	public $name = 'ProdCartsSplit';
		
	var $primaryKeyArray = array('organization_id', 'prod_delivery_id', 'article_organization_id', 'article_id', 'user_id', 'num_split');
	/*
	 * organization_id = 0 perche' exists richiamato in ModelVAlidation::errors()
	 * gli altri perche' nessun metodo puo' avere piu' parametri richiesti rispetto al suo metodo principale. 
	*/	
		if(empty($organization_id) || empty($prod_delivery_id) || empty($article_organization_id) || empty($article_id) || empty($user_id) || empty($num_split)) {
		
		$conditions = array(
						$this->alias . '.organization_id' => $organization_id,
						$this->alias . '.prod_delivery_id' => $prod_delivery_id,
						$this->alias . '.article_organization_id' => $article_organization_id,
						$this->alias . '.article_id' => $article_id,
						$this->alias . '.user_id' => $user_id,
						$this->alias . '.num_split' => $num_split
						);
		
		return (bool)$this->find('count', array(
		$conditions = array(
				$this->alias . '.article_organization_id' => $article_organization_id,
				$this->alias . '.article_id' => $article_id,
						$this->alias . '.num_split' => $num_split
				
		/*
		if(isset($this->data[$this->alias]['article_organization_id']))  $article_organization_id = $this->data[$this->alias]['article_organization_id'];
		if(isset($this->data[$this->alias]['article_id'])) 				 $article_id = $this->data[$this->alias]['article_id'];
		if(isset($this->data[$this->alias]['user_id'])) 				 $user_id = $this->data[$this->alias]['user_id'];
		if(isset($this->data[$this->alias]['num_split'])) 				 $num_split = $this->data[$this->alias]['num_split'];

		if($created) {
			$sql .= "
					".$article_organization_id.",
					".$article_id.",
					".$user_id.",
					".$num_split;
			else $sql .= ", 0.00";
			$sql .= ",'".date('Y-m-d H:i:s')."'";
			$sql .= ")";
				$success = true;
			$sql .= "
						AND article_organization_id = ".$article_organization_id."
						AND article_id = ".$article_id."
						AND user_id = ".$user_id."
						AND num_split = ".$num_split;
			// echo $sql;
	/*
	   					AND prod_delivery_id = ".(int)$prod_delivery_id."
	   					AND article_organization_id = ".(int)$article_organization_id."
	   					AND article_id = ".(int)$article_id."
	   					AND user_id = ".(int)$user_id;
		if(!empty($num_split)) $sql .= " AND num_split = ".(int)$num_split;
}