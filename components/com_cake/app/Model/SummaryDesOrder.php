<?php
App::uses('AppModel', 'Model');


class SummaryDesOrder extends AppModel {
  
	/* 
	 *  estrae tutti i summarDesOrder di un des_supplier
	 */
	public function select_to_des_order($user, $des_order_id, $organization_id=0, $debug=false) {
		
		$options = [];
		$options['conditions'] = array('SummaryDesOrder.des_id' => $user->des_id,
									   'SummaryDesOrder.des_order_id' => $des_order_id);
		if($organization_id>0) 
			$options['conditions'] += array('SummaryDesOrder.organization_id' => $organization_id);	
		
		$options['recursive'] = 1;
		$options['order'] = array('Organization.name');

		$results = $this->find('all', $options);
		if( $debug) {
			echo "<pre>SummaryDesOrder::select_to_des_order() - cerco eventuali SummaryDesOrder \r ";
			print_r($options);
			print_r($results);
			echo "<pre>";
		}
		
		/*
		 * per ogni ordine calcolo il totale importo
		 */
		App::import('Model', 'Order');
		$Order = new Order;
		
		App::import('Model', 'DesOrdersOrganization');
		
		/*
		 * calcolo il totale degli importi degli acquisti dell'ordine per ogno GAS 
		 * perchè potrebbe essere modificato, se REFERENT-WORKING i referenti apportano modifiche
		*/
		foreach($results as $numResult => $result) {
		
			$organization_id = $result['SummaryDesOrder']['organization_id'];
			$des_order_id = $result['SummaryDesOrder']['des_order_id'];
			
			/*
			 * estraggo gli ordini associati
			 */ 
			$DesOrdersOrganization = new DesOrdersOrganization;			
	    	$options = [];
	    	$options['conditions'] = array('DesOrdersOrganization.des_id' => $user->des_id,
							    			'DesOrdersOrganization.organization_id' => $organization_id,
							    			'DesOrdersOrganization.des_order_id' => $des_order_id);
	    	$options['fields'] = array('DesOrdersOrganization.order_id');
	    	$options['recursive'] = -1;
	    	$desOrdersOrganizationResults = $DesOrdersOrganization->find('first', $options);
			
			$order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['order_id'];

			/*
			 * per ogni ordine calcolo il totale importo
			 */
			$tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $organization_id]);

			$importo_totale = $Order->getTotImporto($tmp_user, $order_id, $debug);
			
			try {
				$sql = "UPDATE ".Configure::read('DB.prefix')."summary_des_orders  
						SET importo_orig = $importo_totale
						WHERE 
							des_id = ".(int)$user->des_id."
					    	and organization_id = ".(int)$organization_id."
					    	and des_order_id = ".(int)$des_order_id;
				if($debug)
					echo "<br />".$sql;
				$sqlResults = $Order->query($sql);
			}
			catch (Exception $e) {
				CakeLog::write('error',$e);
				return false;
			}

			$results[$numResult]['SummaryDesOrder']['importo_orig'] = $importo_totale;
			$results[$numResult]['SummaryDesOrder']['importo_orig_'] = number_format($importo_totale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$results[$numResult]['SummaryDesOrder']['importo_orig_e'] = $results[$numResult]['SummaryDesOrder']['importo_orig_'].' &euro;';
			
		} // loop Order.importo_totale
					 
		if( $debug) {
			echo "<pre>SummaryDesOrder::select_to_des_order() - results \r ";
			print_r($results);
			echo "<pre>";
		}
		
		return $results;
	}

	/*
	 *  calcolare il totale degli importi di un des_ordine
	*/
	public function select_totale_importo_to_des_order($user, $des_order_id) {
		$sql = "SELECT
					sum(importo) as totale_importo
				FROM
					".Configure::read('DB.prefix')."summary_des_orders as SummaryDesOrder
				WHERE
					SummaryDesOrder.des_id = ".(int)$user->des_id."
					AND SummaryDesOrder.des_order_id = ".(int)$des_order_id."
				ORDER BY SummaryDesOrder.organization_id";
		self::d($sql, false);
		try {
			$result = current($this->query($sql));
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}		
		return $result;
	}

	public function delete_to_des_order($user, $des_order_id, $debug = false) {
		$sql = "DELETE
				FROM
					".Configure::read('DB.prefix')."summary_des_orders
				WHERE
					des_id = ".(int)$user->des_id."
					AND des_order_id = ".(int)$des_order_id;
		if($debug) echo '<br />SummaryDesOrder::delete_to_des_order() '.$sql;
		try {
			$result = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
			if($debug) echo '<br />'.$e;
		}
	}
	
	public function populate_to_des_order($user, $des_order_id, $debug = false) {
	
		try {
			/*
			 * ctrl che l'ordine sia in stato valido per creare i dati aggragati
			 */
			App::import('Model', 'DesOrder');
			$DesOrder = new DesOrder();			 

			$options = [];
			$options['conditions'] = array('DesOrder.des_id' => $user->des_id,
										   'DesOrder.id' => $des_order_id
										);
			$options['fields'] = array('DesOrder.state_code');
			$options['recursive'] = -1;
			$desSupplierResults = $DesOrder->find('first', $options);
			if($debug) echo '<br />SummaryDesOrder::populate_to_des_order() - DesOrder.state_code '.$desSupplierResults['DesOrder']['state_code'];
			/*
			if($desSupplierResults['DesOrder']['state_code'] != 'SEND-TO-SUPPLIER') {
				if($debug)
					echo '<br />SummaryDesOrder::populate_to_des_order() - DesOrder.state_code != SEND-TO-SUPPLIER => exit ';
				return false;
			}
			*/	
			
			
		   /*
			*  tutti i dati del DesOrder
			*/
		   App::import('Model', 'De');
		   $De = new De;
		   
		   App::import('Model', 'DesOrder');
		   $DesOrder = new DesOrder;
		
		   $desDesOrderResults = $DesOrder->getDesOrder($user, $des_order_id);
			/*
			echo "<pre>";
			print_r($desDesOrderResults);
			echo "</pre>";
			*/
			
			/*
			 * estraggo tutto di Orders dei diversi GAS legati al DesOrders
			 */
			$order_ids = '';
			foreach ($desDesOrderResults['DesOrdersOrganizations'] as $numResult => $resultDesOrdersOrganization) 
				$order_ids .= $resultDesOrdersOrganization['DesOrdersOrganization']['order_id'].',';
			
			$order_ids = substr($order_ids, 0, strlen($order_ids)-1);

			$conditions = array('Cart.order_id' => $order_ids);
			$orderBy = array('Organization.id');
							
			$results = $De->getArticoliAcquistatiDaUtenteInDesOrdine($user, $conditions, $orderBy);
			/*
			echo "<pre>";
			print_r($results);
			echo "</pre>";
			*/	
			
			/*
			 * loop per aggregare gli importi degli acquisti per GAS
			 */
			$summaryCarts = [];
			$organization_id_old=0;
			$importo=0;
			$i=0;	
			foreach($results as $result) {
		
				if($debug) echo "<br />$i) tratto l'organization ".$result['Organization']['name'].' ('.$result['Organization']['id'].") (precedente $organization_id_old)";
		
				if($organization_id_old>0 && $result['Organization']['id']!=$organization_id_old) {
					if($debug) echo "<br />$i) organization diversa => memorizzo in ";
		
					$summaryCarts[$i]['organization_id'] = $organization_id_old;
					$summaryCarts[$i]['importo'] = $importo;
					$importo = 0;
			
					if($debug)  {
						echo "<pre>";
						print_r($summaryCarts[$i]);
						echo "</pre>";
					}
		
					$i++;
				}
		
				/*
				 * importo, somma di tutti gli importi di un GAS
				 */
				if($result['Cart']['importo_forzato']>0)
					$importo += $result['Cart']['importo_forzato'];
				else
					if($result['Cart']['qta_forzato']>0)
					$importo += ($result['ArticlesOrder']['prezzo'] * $result['Cart']['qta_forzato']);
				else
					$importo += ($result['ArticlesOrder']['prezzo'] * $result['Cart']['qta']);  
		
				if($debug) echo "<br />$i)     importo del GAS ".$result['Organization']['id'].": $importo";
	
				$organization_id_old = $result['Organization']['id'];
			} // end foreach($articlesOrders as $articlesOrder)
			
			$summaryCarts[$i]['organization_id'] = $result['Organization']['id'];
			$summaryCarts[$i]['importo'] = $importo;
			
			if($debug)  {
				echo "<br />$i) ultimo GAS => memorizzo in ";
				echo "<pre>";
				print_r($summaryCarts[$i]);
				echo "</pre>";
			}
				
			/*
			 * inserisco in SummaryDesOders aggiungendo trasporto / spese generiche, sconti
			 */
			$summaryDesOrderData = [];
			foreach($summaryCarts as $summaryCart) {
				$summaryDesOrderData['SummaryDesOrder']['des_id'] = $user->des_id;
				$summaryDesOrderData['SummaryDesOrder']['des_order_id'] = $des_order_id;
				$summaryDesOrderData['SummaryDesOrder']['organization_id'] = $summaryCart['organization_id'];
				$summaryDesOrderData['SummaryDesOrder']['importo'] = $summaryCart['importo'];
				$summaryDesOrderData['SummaryDesOrder']['importo_pagato'] = '0.00';
				$summaryDesOrderData['SummaryDesOrder']['modalita'] = 'DEFINED';
				
				/*
				 * aggiungo eventuale spesa di trasporto
				
				if($results['DesOrder']['hasTrasport']=='Y' && $results['DesOrder']['trasport']>0) {					
					foreach($resultsSummaryOrderTrasport as $numSummaryOrderTrasport => $resultSummaryOrderTrasport) {
						if($resultSummaryOrderTrasport['SummaryOrderTrasport']['user_id']==$summaryDesOrderData['SummaryDesOrder']['user_id']) {
							
							if($debug) echo "<br />SummaryOrder.importo ".$summaryDesOrderData['SummaryDesOrder']['importo'].' - dopo la spesa di trasporto '.($summaryDesOrderData['SummaryDesOrder']['importo'] + $resultSummaryOrderTrasport['SummaryOrderTrasport']['importo_trasport']);
							
							$summaryDesOrderData['SummaryDesOrder']['importo'] = ($summaryDesOrderData['SummaryDesOrder']['importo'] + $resultSummaryOrderTrasport['SummaryOrderTrasport']['importo_trasport']);
							break;
						}
					}
				} 
				*/
				
				/*
				 * aggiungo eventuale costo aggiuntivo
				
				if($results['DesOrder']['hasCostMore']=='Y' && $results['DesOrder']['cost_more']>0) {
					foreach($resultsSummaryOrderCostMore as $numSummaryOrderCostMore => $resultSummaryOrderCostMore) {
						if($resultSummaryOrderCostMore['SummaryOrderCostMore']['user_id']==$summaryDesOrderData['SummaryDesOrder']['user_id']) {
				
							if($debug) echo "<br />SummaryOrder.importo ".$summaryDesOrderData['SummaryDesOrder']['importo'].' - dopo il costo aggiuntivo '.($summaryDesOrderData['SummaryDesOrder']['importo'] + $resultSummaryOrderCostMore['SummaryOrderCostMore']['importo_cost_more']);
				
							$summaryDesOrderData['SummaryDesOrder']['importo'] = ($summaryDesOrderData['SummaryDesOrder']['importo'] + $resultSummaryOrderCostMore['SummaryOrderCostMore']['importo_cost_more']);
							break;
						}
					}
				} 
				*/	
				/*
				 * aggiungo eventuale sconto
				
				if($results['DesOrder']['hasCostLess']=='Y' && $results['DesOrder']['cost_less']>0) {
					foreach($resultsSummaryOrderCostLess as $numSummaryOrderCostLess => $resultSummaryOrderCostLess) {
						if($resultSummaryOrderCostLess['SummaryOrderCostLess']['user_id']==$summaryDesOrderData['SummaryDesOrder']['user_id']) {
								
							if($debug) echo "<br />SummaryOrder.importo ".$summaryDesOrderData['SummaryDesOrder']['importo'].' - dopo lo sconto '.($summaryDesOrderData['SummaryDesOrder']['importo'] + $resultSummaryOrderCostLess['SummaryOrderCostLess']['importo_cost_less']);
								
							$summaryDesOrderData['SummaryDesOrder']['importo'] = ($summaryDesOrderData['SummaryDesOrder']['importo'] + ($resultSummaryOrderCostLess['SummaryOrderCostLess']['importo_cost_less']));
							break;
						}
					}
				}
				*/
				
				/*
				 * importo originale, sommo degli acquisti di un GAS
				 * importo, importo reale perche' puo' essere modificato dal titolare 
				 */
				$summaryDesOrderData['SummaryDesOrder']['importo_orig'] = $summaryDesOrderData['SummaryDesOrder']['importo'];
				
				if($debug)  {
					echo "<pre>salvo il record ";
					print_r($summaryDesOrderData);
					echo "</pre>";
				}
								
				$this->create();
				$this->save($summaryDesOrderData);
			}	// loop summaryCarts (dati aggregati per GAS)				
			// if($debug) exit;
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}			
	}
	
	public $validate = array(
		'des_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'des_order_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'organization_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
	
	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Organization' => array(
			'className' => 'Organization',
			'foreignKey' => 'organization_id',
			'conditions' => 'Organization.id = SummaryDesOrder.organization_id',
			'fields' => '',
			'order' => ''
		),
		'De' => array(
			'className' => 'De',
			'foreignKey' => 'des_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'DesOrder' => array(
			'className' => 'DesOrder',
			'foreignKey' => 'des_order_id',
			'conditions' => 'DesOrder.des_id = SummaryDesOrder.des_id',
			'fields' => '',
			'order' => ''
		)
	);
	
	/*
	 * il save lo faccio in populate_to_order() ed e' gia' corretto
	public function beforeSave($options = []) {
		if(!empty($this->data['SummaryDesOrder']['importo'])) {
			$this->data['SummaryDesOrder']['importo'] =  $this->importoToDatabase($this->data['SummaryDesOrder']['importo']);
		}
		return true;
	}*/
		
	public function afterFind($results, $primary = false) {
		foreach ($results as $key => $val) {
			if(!empty($val)) {
				if(isset($val['SummaryDesOrder']['importo_orig'])) {
					$results[$key]['SummaryDesOrder']['importo_orig_'] = number_format($val['SummaryDesOrder']['importo_orig'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['SummaryDesOrder']['importo_orig_e'] = $results[$key]['SummaryDesOrder']['importo_orig_'].' &euro;';
				}
				if(isset($val['SummaryDesOrder']['importo'])) {
					$results[$key]['SummaryDesOrder']['importo_'] = number_format($val['SummaryDesOrder']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['SummaryDesOrder']['importo_e'] = $results[$key]['SummaryDesOrder']['importo_'].' &euro;';
				}
			}
		}
		return $results;
	}	
}