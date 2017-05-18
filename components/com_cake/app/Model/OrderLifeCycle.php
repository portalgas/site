<?php
App::uses('AppModel', 'Model');

class OrderLifeCycle extends AppModel {

	public $useTable = 'orders';
	
	public $belongsTo = array(
		'SuppliersOrganization' => array(
			'className' => 'SuppliersOrganization',
			'foreignKey' => 'supplier_organization_id',
			'conditions' => 'SuppliersOrganization.organization_id = Order.organization_id',
			'fields' => '',
			'order' => ''
		),
		'Delivery' => array(
			'className' => 'Delivery',
			'foreignKey' => 'delivery_id',
			'conditions' => 'Delivery.organization_id = Order.organization_id',
			'fields' => '',
			'order' => ''
		)						
	);

	public function stateCodeUpdate($user, $order_id, $state_code_next, $options=array(), $debug=false) {
	
		$esito = array();
		
		if(empty($order_id) || empty($state_code_next)) {
			$esito['CODE'] = "500";
			$esito['MSG'] = "Parametri errati";
			return $esito; 
		}	
	
		if($debug)
			echo "<br />state_code_next ".$state_code_next;
			
        try {
        
 			$esito = $this->sqlWhereSetting($state_code_next, $options, $debug);
 			if(isset($esito['CODE'])) 
				return $esito; 
 				
 			if(isset($esito['SQL_ADD']))
	 			$sql_add = $esito['SQL_ADD'];
 				
 	    	switch($state_code_next) {
		 		case 'RI-OPEN-VALIDATE':
		 			// $this->Order->riapriOrdine($this->user, $this->order_id, $debug);
		 		break;
		 		case 'WAIT-PROCESSED-TESORIERE':
		 			// $Tesoriere->sendMailToUpload($this->user, $this->request->data, $results, 'REFERENTE', $debug);		
		 		break;
		 		case 'PROCESSED-POST-DELIVERY':
		 		
		 		break;
		 		case 'INCOMING-ORDER':  // merce arrivata
					/*
					 * 	da 'PROCESSED-BEFORE-DELIVERY' a 'INCOMING-ORDER'   da "prima della consegna" a "merce è arrivata"
					 * oppure
					 * 	da 'PROCESSED-ON-DELIVERY' a 'INCOMING-ORDER'  da "ordine confermato" a "merce è arrivata"
		 			*/
		 		break;
		 		case 'CLOSE':		 			
		 			// $SummaryOrder->populate_to_order($this->user, $this->order_id, 0);		 			
		 		break;
		 	}
        	
	 		$sql = "UPDATE `".Configure::read('DB.prefix')."orders` SET ".$sql_add."
						state_code = '".$state_code_next."',
						modified = '".date('Y-m-d H:i:s')."'
					WHERE
						organization_id = ".(int)$user->organization['Organization']['id']."
						and id = ".(int)$order_id;
				if($debug) echo '<br />'.$sql;
				$updateResults = $this->query($sql);
				
        } catch (Exception $e) {
			CakeLog::write('error', $sql);
            CakeLog::write('error', $e);        
			
			$esito['CODE'] = "500";
			$esito['MSG'] = $e->getMessage();
			return $esito;
        }
        
		$esito['CODE'] = "200";
		
		return $esito;         
    }		
    
    private function sqlWhereSetting($state_code_next, $options=array(), $debug) {
       	
       	$esito = array();
    	
    	switch($state_code_next) {
	 		case 'RI-OPEN-VALIDATE':
	 			if(!isset($options['data_fine_validation'])) {
					$esito['CODE'] = "500";
					$esito['MSG'] = "data_fine_validation non valorizzato";
					return $esito; 
				}	
	 				
	 			$esito['SQL_ADD'] .= "data_fine_validation = '".$options['data_fine_validation']."',";	 			
	 		break;
	 		case 'WAIT-PROCESSED-TESORIERE':
	 			if(isset($options['tesoriere_doc1']))
		 			$esito['SQL_ADD'] .= "tesoriere_doc1 = '".$options['tesoriere_doc1']."',";
	 		break;
	 		case 'PROCESSED-POST-DELIVERY':
	 		break;
	 		case 'INCOMING-ORDER':  // merce arrivata
	 			if(!isset($options['data_incoming_order'])) {
					$esito['CODE'] = "500";
					$esito['MSG'] = "data_incoming_order non valorizzato";
					return $esito; 
				}	
	 				
	 			$esito['SQL_ADD'] .= "data_incoming_order = '".$options['data_incoming_order']."',";	 			

	 		break;	 		
	 		case 'CLOSE':
	 			if(!isset($options['tot_importo'])) {
					$esito['CODE'] = "500";
					$esito['MSG'] = "tot_importo non valorizzato";
					return $esito; 
				}
	 			if(!isset($options['tesoriere_sorce'])) {
					$esito['CODE'] = "500";
					$esito['MSG'] = "tesoriere_sorce non valorizzato";
					return $esito; 
				}	
	 				
	 			$esito['SQL_ADD'] .= "tot_importo = ".$options['tot_importo'].",
	 						 tesoriere_sorce = '".$options['tesoriere_sorce']."',";
	 						 
	 			if(isset($options['tesoriere_data_pay']))
		 			$esito['SQL_ADD'] .= "tesoriere_data_pay = '".$options['tesoriere_data_pay']."',";
	 			if(isset($options['tesoriere_importo_pay']))
		 			$esito['SQL_ADD'] .= "tesoriere_importo_pay = '".$options['tesoriere_importo_pay']."',";
	 			if(isset($options['tesoriere_fattura_importo']))
		 			$esito['SQL_ADD'] .= "tesoriere_fattura_importo = '".$options['tesoriere_fattura_importo']."',";
	 			if(isset($options['tesoriere_stato_pay']))
		 			$esito['SQL_ADD'] .= "tesoriere_stato_pay = '".$options['tesoriere_stato_pay']."',";
	 		break;
	 	}
		
		return $esito;
    }		
}