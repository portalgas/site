<?php
App::uses('AppModel', 'Model');

/*
class DesSuppliersReferentMultiKey extends AppModel {
	
	var $primaryKeyArray = array('des_id', 'organization_id', 'user_id', 'group_id', 'des_supplier_id');
	var $des_id = 0;
	var $organization_id = 0;
	var $user_id = 0;
	var $group_id = 0;
	
	/*
	 * organization_id = 0 perche' exists richiamato in ModelValidation::errors()
	 * gli altri perche' nessun metodo puo' avere piu' parametri richiesti rispetto al suo metodo principale. 
	*/
		
		$conditions = array($this->alias . '.des_id' => $des_id,
							$this->alias . '.organization_id' => $organization_id,
							$this->alias . '.user_id' => $user_id,
							$this->alias . '.group_id' => $group_id,
							$this->alias . '.des_supplier_id' => $des_supplier_id);
		/*
		echo "<pre>";
		print_r($conditions);
		echo "</pre>";
		*/
									  $this->alias . '.organization_id' => $organization_id,
									  $this->alias . '.user_id' => $user_id,
									  $this->alias . '.group_id' => $group_id,
									  $this->alias . '.des_supplier_id' => $des_supplier_id
	/*
		if(isset($this->data[$this->alias]['organization_id']))  $this->organization_id = $this->data[$this->alias]['organization_id'];
		if(isset($this->data[$this->alias]['user_id'])) 		 $this->user_id = $this->data[$this->alias]['user_id'];
		if(isset($this->data[$this->alias]['group_id'])) 		 $this->group_id = $this->data[$this->alias]['group_id'];
		if(isset($this->data[$this->alias]['des_supplier_id']))  $this->des_supplier_id = $this->data[$this->alias]['des_supplier_id'];

		if(!$this->exists($this->des_id, $this->organization_id, $this->user_id, $this->group_id, $this->des_supplier_id))
					".$this->data[$this->alias]['des_id'].",
					".$this->data[$this->alias]['organization_id'].",
					".$this->data[$this->alias]['user_id'].",
					".$this->data[$this->alias]['group_id'].",
					'".date('Y-m-d H:i:s')."' )";
			// echo '<br />'.$sql;		
						user_id = ".$this->data[$this->alias]['user_id'].",
						group_id = ".$this->data[$this->alias]['group_id'].",
						des_supplier_id = ".$this->data[$this->alias]['des_supplier_id'].",
						AND organization_id = ".$this->data[$this->alias]['organization_id']."
						AND user_id = ".$this->data[$this->alias]['user_id']."
						AND group_id = ".$this->data[$this->alias]['group_id']."
						AND des_supplier_id = ".$this->data[$this->alias]['des_supplier_id'];
			// echo '<br />'.$sql;	
	   					AND organization_id = ".(int)$organization_id."
	   					AND user_id = ".(int)$user_id."
	   					AND group_id = ".(int)$group_id."
	   					AND des_supplier_id = ".(int)$des_supplier_id."
}