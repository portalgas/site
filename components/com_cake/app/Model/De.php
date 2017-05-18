<?php
App::uses('AppModel', 'Model');

class De extends AppModel {
	
	public function getArticoliAcquistatiDaUtenteInDesOrdine($user, $conditions, $orderBy) {
		
		App::import('Model', 'Cart');
		$Cart = new Cart();
		
		$belongsTo = array(
				'className' => 'Organization',
				'foreignKey' => '',
				'conditions' => array('Organization.id = Cart.organization_id'),
				'fields' => '',
				'order' => '');
		$Cart->bindModel(array('belongsTo' => array('Organization' => $belongsTo)));				
		$Cart->unbindModel(array('belongsTo' => array('Order')));
		
		$options['conditions'] = array('ArticlesOrder.stato != ' => 'N',
										'Article.stato' => 'Y'
										);
		/*
		 * filtro per tutti gli ordini in DesOrder
		 */								
		if(isset($conditions['Cart.order_id']))       	  $options['conditions'] += array('Cart.order_id IN ('.$conditions['Cart.order_id'].')');
		if(isset($conditions['Cart.stato']))          	  $options['conditions'] += array('Cart.stato' => $conditions['Cart.stato']);
		if(isset($conditions['Cart.deleteToReferent']))   $options['conditions'] += array('Cart.deleteToReferent' => $conditions['Cart.deleteToReferent']);
				
		$options['recursive'] = 0;
		$options['order'] = $orderBy;
		
		$results = $Cart->find('all', $options);
		
		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
		 		 		
		return $results;
	}

	/*
	 *  creo array aggregando gli articoli, NON distinguo per GAS
	 *  	ExportDoc::des_referent_to_supplier / des_referent_to_supplier_monitoring
	 */	
	public function getAggregateArticoli($user, $results, $debug=false) {		

		// $debug=true;		
		
		foreach($results as $numResult => $result) {
		
			$newResults[$result['Article']['id']]['Article'] = $result['Article'];

			$newResults[$result['Article']['id']]['ArticlesOrder']['article_organzation__id'] = $result['ArticlesOrder']['article_organzation__id'];
			$newResults[$result['Article']['id']]['ArticlesOrder']['article_id'] = $result['Article']['ArticlesOrder'];
			$newResults[$result['Article']['id']]['ArticlesOrder']['pezzi_confezione'] = $result['ArticlesOrder']['pezzi_confezione'];
			$newResults[$result['Article']['id']]['ArticlesOrder']['prezzo'] = $result['ArticlesOrder']['prezzo'];
			
			/*
			 * gestione qta e importi
			 * */
			if($result['Cart']['qta_forzato']>0) 
				$newResults[$result['Article']['id']]['Cart']['qta'] += $result['Cart']['qta_forzato'];
			else 
				$newResults[$result['Article']['id']]['Cart']['qta'] += $result['Cart']['qta'];
			
			if($result['Cart']['importo_forzato']==0) {
				if($result['Cart']['qta_forzato']>0) 
					$newResults[$result['Article']['id']]['Cart']['importo'] += ($result['Cart']['qta_forzato'] * $result['ArticlesOrder']['prezzo']);
				else 
					$newResults[$result['Article']['id']]['Cart']['importo'] += ($result['Cart']['qta'] * $result['ArticlesOrder']['prezzo']);	
			}	
			else 
				$newResults[$result['Article']['id']]['Cart']['importo'] += $result['Cart']['importo_forzato'];
		}	
		

		if($debug) {
			echo "<pre>";
			print_r($newResults);
			echo "</pre>";
		}
			
		return $newResults;			
	}
	
	/*
	 *  creo array aggregando gli articoli e il GAS
	 *  	ExportDoc::des_referent_to_supplier_details
	 */	
	public function getAggregateArticoliOrganization($user, $results, $debug=false) {		
	
		//$debug=true;	
		
		/*
		 * aggrego per Articles
		 */
		$newResults = array();
		foreach($results as $numResult => $result) {
			$newResults[$result['Article']['id']]['Article'] = $result['Article'];
		}

		if($debug) 
			echo '<br />De::getAggregateArticoliOrganization() - totale articoli trovati '.count($newResults);
		
		/*
		 * per ogni articolo, aggrego per GAS
		 */	
		 foreach($newResults as $article_id => $newResult) {
			//echo "<br />".$article_id;
			foreach($results as $numResult2 => $result) {
				if($newResult['Article']['id'] == $result['Article']['id']) {
			
					/*
					 * gestione qta e importi
					 * */
					if($result['Cart']['qta_forzato']>0) {
						$qta = $result['Cart']['qta_forzato'];
						$qta_modificata = true;
					}	
					else {
						$qta = $result['Cart']['qta'];
						$qta_modificata = false;
					}
					$importo_modificato = false;
					if($result['Cart']['importo_forzato']==0) {
						if($result['Cart']['qta_forzato']>0) 
							$importo = ($result['Cart']['qta_forzato'] * $result['ArticlesOrder']['prezzo']);
						else {
							$importo = ($result['Cart']['qta'] * $result['ArticlesOrder']['prezzo']);
						}	
					}	
					else {
						$importo = $result['Cart']['importo_forzato'];
						$importo_modificato = true;
					}

					$newResults[$article_id]['Article']['Organization'][$result['Organization']['id']]['Organization'] = $result['Organization'];
					$newResults[$article_id]['Article']['Organization'][$result['Organization']['id']]['ArticlesOrder'] = $result['ArticlesOrder'];								
					$newResults[$article_id]['Article']['Organization'][$result['Organization']['id']]['tot_importo'] += $importo;
					$newResults[$article_id]['Article']['Organization'][$result['Organization']['id']]['tot_qta'] += $qta;

					if($debug) echo '<br />Tratto '.$result['Article']['name'].' ('.$result['Article']['id'].') '.$result['Organization']['name'].' - tot_importo '.$newResults[$article_id]['Article']['Organization'][$result['Organization']['id']]['tot_importo'].' - '.$importo.' - '.$qta;
				} // medesimo articolo
			} //  loop vecchio records
		}	//  loop nuovo records	

		if($debug) {
			echo "<pre>";
			print_r($newResults);
			echo "</pre>";
		}
			
		return $newResults;
	}
	
	public $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);


	public $hasMany = array(
			'DesOrganization' => array(
					'className' => 'DesOrganization',
					'foreignKey' => 'des_id',
					'dependent' => false,
					'conditions' => '',
					'fields' => '',
					'order' => '',
					'limit' => '',
					'offset' => '',
					'exclusive' => '',
					'finderQuery' => '',
					'counterQuery' => ''
			)
	);
}