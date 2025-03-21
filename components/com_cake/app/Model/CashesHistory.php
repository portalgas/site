<?php
App::uses('AppModel', 'Model');


class CashesHistory extends AppModel {

	public $useTable = 'cashes_histories';
		
	public function previousCashSave($user, $cash_id, $debug=false) {
	
		App::import('Model', 'Cash');
	    $Cash = new Cash;
		
	    $options = [];
	    $options['conditions'] = ['Cash.organization_id' => (int) $user->organization['Organization']['id'],
								  'Cash.id' => $cash_id];
		$options['recursive'] = -1;
		$previuosCash = $this->Cash->find('first', $options);
		
		if($debug) {
		  echo "<pre>CashesHistory::previousCashSave() - cassa precedente da salvare in History \n ";
		  print_r($previuosCash);
		  echo "</pre>";
		}
        
        $cashesHistory['CashesHistory'] = $previuosCash['Cash'];
        $cashesHistory['CashesHistory']['id'] = null;
        $cashesHistory['CashesHistory']['cash_id'] = $cash_id;
		// la data di creazione e' la medesima di quando e' stato creato il movimemto di cassa (Cash)
        // l'export della cassa per anno filtra per 'YEAR(CashesHistory.created) >= ' => $year
        // $cashesHistory['CashesHistory']['created'] = null;
        $cashesHistory['CashesHistory']['modified'] = $previuosCash['Cash']['created'];

		if($debug) {
		  echo "<pre>CashesHistory::previousCashSave() \n ";
		  print_r($cashesHistory);
		  echo "</pre>";
		}
		        
	    $this->create();
        if($this->save($cashesHistory))
        	return true;
       	else
            return false;
	}
	
	/*
     * call to Ajax::admin_view_cashes_histories / Ajax::view_cashes_histories
	 */	 
	public function getListCashHistoryByUser($user, $results) {
		
		$tot_histories = count($results);
		$importo = 0;
		$importo_old = 0;
	//	debug($results);
		/*
		 * calcolo dai saldi alle operazioni
		 */
        foreach($results as $numResult => $result) {
			if($numResult>0) {
				$importo_old = $results[$numResult-1]['CashesHistory']['importo'];
				$importo = $results[$numResult]['CashesHistory']['importo'];
				
				$operazione = (-1*($importo_old - $importo));
				$results[$numResult-1]['CashesHistory']['operazione'] = $operazione;
				$results[$numResult-1]['CashesHistory']['operazione_'] = number_format($operazione,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				$results[$numResult-1]['CashesHistory']['operazione_e'] = number_format($operazione,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
				
				$results[$numResult-1]['CashesHistory']['nota'] = $results[$numResult-1]['CashesHistory']['nota'];
				$results[$numResult-1]['CashesHistory']['created'] = $results[$numResult-1]['CashesHistory']['created'];
			}	
		}
		//debug($results);
		$results[$numResult]['CashesHistory']['operazione'] = '';
		$results[$numResult]['CashesHistory']['operazione_'] = '';
		$results[$numResult]['CashesHistory']['operazione_e'] = '';		

		/*
		 * aggiungo la prima riga con partenza saldo a 0
		 * porto nota / modified all'occorrenza dell'array precedente
		 */
		$newResults = []; 
		if(!empty($results)) { 		
			$newResults[0]['Cash']['importo'] = '0';
			$newResults[0]['Cash']['importo_'] = '0,00';
			$newResults[0]['Cash']['importo_e'] = '0,00 &euro;';
			$operazione = (-1*(0 - $results[0]['CashesHistory']['importo']));
			
			$newResults[0]['CashesHistory']['importo'] = '0';
			$newResults[0]['CashesHistory']['importo_'] = '0,00';
			$newResults[0]['CashesHistory']['importo_e'] = '0,00 &euro;';

			if($tot_histories>1)
				$newResults[0]['CashesHistory']['id'] = $results[0]['CashesHistory']['id'];
			$newResults[0]['CashesHistory']['nota'] = $results[0]['CashesHistory']['nota'];
            $newResults[0]['CashesHistory']['modified'] = $results[0]['CashesHistory']['modified'];
            $newResults[0]['CashesHistory']['created'] = $results[0]['CashesHistory']['created'];
            $newResults[0]['CashesHistory']['year'] = substr($results[0]['CashesHistory']['created'],0, 4);
				
			$newResults[0]['CashesHistory']['operazione'] = $operazione;
			$newResults[0]['CashesHistory']['operazione_'] = number_format($operazione,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$newResults[0]['CashesHistory']['operazione_e'] = number_format($operazione,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
		
			foreach($results as $numResult => $result) {
				$newResults[((int)$numResult+1)] = $result;
				
				if(isset($results[((int)$numResult+1)])) {
					
					/* 
					 * se $tot_histories = 1 non lo posso modificare perche' è la voce di cassa corrente 
					 */
					if($tot_histories>1 && ($numResult+2)<count($results))	
						$newResults[((int)$numResult+1)]['CashesHistory']['id'] = $results[((int)$numResult+1)]['CashesHistory']['id'];
					else
						$newResults[((int)$numResult+1)]['CashesHistory']['id'] = 0;

						$newResults[((int)$numResult+1)]['CashesHistory']['nota'] = $results[((int)$numResult+1)]['CashesHistory']['nota'];
                    $newResults[((int)$numResult+1)]['CashesHistory']['modified'] = $results[((int)$numResult+1)]['CashesHistory']['modified'];
                    $newResults[((int)$numResult+1)]['CashesHistory']['created'] = $results[((int)$numResult+1)]['CashesHistory']['created'];
                    $newResults[((int)$numResult+1)]['CashesHistory']['year'] = substr($results[((int)$numResult+1)]['CashesHistory']['created'], 0, 4);
				}
				else {
					$newResults[((int)$numResult+1)]['CashesHistory']['id'] = 0;
					$newResults[((int)$numResult+1)]['CashesHistory']['nota'] = "";					
				}
			}
		}
	
		if($debug) debug ($newResults);
		
		return $newResults;
	}
	
	public $validate = array(
		'organization_id' => array(
				'numeric' => array(
						'rule' => ['numeric'],
				),
		),
		'cash_id' => array(
				'numeric' => array(
						'rule' => ['numeric'],
				),
		),
		'user_id' => array(
				'numeric' => array(
						'rule' => ['numeric'],
				),
		),
	);

	public $belongsTo = array(
		'Cash' => array(
			'className' => 'Cash',
			'foreignKey' => 'cash_id',
			'conditions' => 'CashesHistory.organization_id = Cash.organization_id ',
			'fields' => '',
			'order' => ''
		),		
	);	
	
	public function afterFind($results, $primary = true) {
		
		foreach ($results as $key => $val) {
			if(!empty($val)) {				
				if (isset($val['CashesHistory']['importo'])) {
					$results[$key]['CashesHistory']['importo_'] = number_format($val['CashesHistory']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['CashesHistory']['importo_e'] = $results[$key]['CashesHistory']['importo_'].' &euro;';
				}
				else
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				*/
				if(isset($val['importo'])) {
					$results[$key]['importo_'] = number_format($val['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['importo_e'] = $results[$key]['importo_'].' &euro;';
				}					
			}
		}
		return $results;
	}
	
	public function beforeSave($options = []) {
		if (!empty($this->data['CashesHistory']['importo']))
			$this->data['CashesHistory']['importo'] = $this->importoToDatabase($this->data['CashesHistory']['importo']);
	
		return true;
	}
}