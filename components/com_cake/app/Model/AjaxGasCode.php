<?php
App::uses('AppModel', 'Model');


class AjaxGasCode extends AppModel {

	public $useTable = false;

	/*
	 * ctrl che i calcoli effettuati siano coerenti con il totale acquisti (non fatte modifiche successive)
	 * if($totImporto_ != $results['SummaryOrder...']['importo_']) 
	 */
	public function getSummaryOrderTrasportValidate($user, $orderResult, $debug=false) {
		
		$esito = [];
		$model = 'SummaryOrderTrasport';
		
		if(empty($orderResult)) {
			$esito['CODE'] = "500";
			$esito['MSG'] = "Parametri errati";
			return $esito; 
		}	

		if(!is_array($orderResult))
			$orderResult = $this->_getOrderById($user, $orderResult, $debug);

		if($orderResult['Order']['hasTrasport']=='Y' && floatval($orderResult['Order']['trasport'])>0) 
			$esito['results'] = $this->_getSummaryOrderPlus($model, $user, $orderResult, $options, $debug);
			
		return $esito; 
	}
	
	public function getSummaryOrderCostMoreValidate($user, $orderResult, $debug=false) {
	
		$esito = [];
		$model = 'SummaryOrderCostMore';
		
		if(empty($orderResult)) {
			$esito['CODE'] = "500";
			$esito['MSG'] = "Parametri errati";
			return $esito; 
		}	

		if(!is_array($orderResult))
			$orderResult = $this->_getOrderById($user, $orderResult, $debug);

		if($orderResult['Order']['hasCostMore']=='Y' && floatval($orderResult['Order']['cost_more'])>0) 
			$esito['results'] = $this->_getSummaryOrderPlus($model, $user, $orderResult, $options, $debug);
			
		return $esito; 
	}
	
	public function getSummaryOrderCostLessValidate($user, $orderResult, $debug=false) {
	
		$esito = [];
		$model = 'SummaryOrderCostMore';
		
		if(empty($orderResult)) {
			$esito['CODE'] = "500";
			$esito['MSG'] = "Parametri errati";
			return $esito; 
		}	

		if(!is_array($orderResult))
			$orderResult = $this->_getOrderById($user, $orderResult, $debug);

		if($orderResult['Order']['hasCostLess']=='Y' && floatval($orderResult['Order']['cost_less'])>0) 
			$esito['results'] = $this->_getSummaryOrderPlus($model, $user, $orderResult, $options, $debug);
			
		return $esito; 	
	}
			
	/*
	 * calcolo i dati
	 * calcolo il totale
	 * $model = SummaryOrderTrasport SummaryOrderCostMore SummaryOrderCostLess
	 */
	public function getData($user, $model, $orderResult, $userOptions, $debug=false) {
		
		$esito = [];
						
		if(empty($model) || empty($orderResult)) {
			$esito['CODE'] = "500";
			$esito['MSG'] = "Parametri errati";
			return $esito; 
		}	

		if(!is_array($orderResult))
			$orderResult = $this->_getOrderById($user, $orderResult, $debug);
		
		$totali = $this->_getTotali($user, $model, $orderResult, $userOptions, $debug);
		
        $importo_type_db = null;
        switch ($userOptions) {
            case "options-qta":
                $importo_type_db = 'QTA';
                break;
            case "options-weight":
                $importo_type_db = 'WEIGHT';
                break;
            case "options-users":
                $importo_type_db = 'USERS';
                break;
        }
		
		switch ($model) {
			case 'SummaryOrderTrasport':
				App::import('Model', 'SummaryOrderTrasport');
				$Model = new SummaryOrderTrasport;
				$modelTable = Configure::read('DB.prefix')."summary_order_trasports";
				$prefix = 'trasport';	
			break;
			case 'SummaryOrderCostMore':
				App::import('Model', 'SummaryOrderCostMore');
				$modelTable = Configure::read('DB.prefix')."summary_order_cost_mores";
				$Model = new SummaryOrderCostMore;
				$prefix = 'cost_more';
			break;
			case 'SummaryOrderCostLess':
				App::import('Model', 'SummaryOrderCostLess');
				$modelTable = Configure::read('DB.prefix')."summary_order_cost_lesses";
				$Model = new SummaryOrderCostLess;
				$prefix = 'cost_less';					
			break;
			default:
				self::x("AjaxGasCode::getData model [$model] non valido");
			break;			
		}
		
        App::import('Model', 'Cart');
        $Cart = new Cart;

        App::import('Model', 'SummaryOrderAggregate');
        $SummaryOrderAggregate = new SummaryOrderAggregate;

        App::import('Model', 'SummaryOrder');
        $SummaryOrder = new SummaryOrder;
        		
        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        $conditions = ['Delivery' => ['Delivery.isVisibleBackOffice' => 'Y', 'Delivery.id' => $orderResult['Order']['delivery_id']],
	                   'Order' => ['Order.isVisibleBackOffice' => 'Y','Order.id' => $orderResult['Order']['id']],
	                   'Cart' => ['Cart.deleteToReferent' => 'N']];
        $orderBy = ['User' => 'User.name'];

        $options = ['orders' => false, 'storerooms' => false, 'summaryOrders' => false,
            		'articlesOrdersInOrder' => true,'suppliers' => true, 'referents' => true];
            		
		switch ($model) {
			case 'SummaryOrderTrasport':
				$options += ['summaryOrderTrasports' => true];	
			break;
			case 'SummaryOrderCostMore':
				$options += ['summaryOrderCostMores' => true];	
			break;
			case 'SummaryOrderCostLess':
				$options += ['summaryOrderCostLess' => true];	
			break;
			default:
				self::x("AjaxGasCode::getData model [$model] non valido");
			break;			
		}
		
        $results = $Delivery->getDataWithoutTabs($user, $conditions, $options, $orderBy);
		self::d($results, false);
		
        $importo = 0;
        if ($results['Delivery']['totOrders'] > 0)
        foreach ($results['Delivery'][0]['Order'] as $numOrder => $order) {

            $importo = $order['Order'][$prefix];
			
			/*
			 * all'importo del trasporto / spesa / sconto escludo le somme gia' saldate a cassiere / tesoriere
			 */
            $sql = "SELECT sum(".$model.".importo_".$prefix.") as totaleOrdineGiaSaldati 
					FROM
						".$modelTable." as ".$model.", 
						".Configure::read('DB.prefix')."summary_orders as SummaryOrder
					WHERE
						SummaryOrder.organization_id = ".$user->organization['Organization']['id'] . "
						and ".$model.".organization_id = ".$user->organization['Organization']['id'] . "
					    and ".$model.".order_id = SummaryOrder.order_id and ".$model.".user_id = SummaryOrder.user_id and SummaryOrder.saldato_a is NOT null
						and ".$model.".order_id = ".$orderResult['Order']['id'];
            self::d($sql, $debug);
            $result = $Model->query($sql);
            $totaleOrdineGiaSaldati = 0;
            if (!empty($result)) {
                $result = current($result);
                $totaleOrdineGiaSaldati = $result[0]['totaleOrdineGiaSaldati'];
            }
			$importo = ($importo - $totaleOrdineGiaSaldati);
	
            $importo_type = $order['Order'][$prefix.'_type'];

            if (isset($order[$model]))
                foreach ($order[$model] as $numResult => $summaryOrderPlu) {
                
			        /*
					 * per ogni user estraggo il totale degli acquisti originale o importo dati aggregato
					 */                
                	$totImporto = 0;
                	
					$summaryOrderAggregateResults = $SummaryOrderAggregate->select_to_order($user, $summaryOrderPlu[$model]['order_id'], $summaryOrderPlu['User']['id']);
					self::d($summaryOrderAggregateResults, false);
					if(!empty($summaryOrderAggregateResults)) {
						$totImporto = $summaryOrderAggregateResults[0]['SummaryOrderAggregate']['importo'];
					}
					else {
						$conditions = []; 
						$conditions['Cart.user_id'] = $summaryOrderPlu['User']['id'];
						$conditions['Order.id'] = $summaryOrderPlu[$model]['order_id'];
						$totImporto = $Cart->getTotImporto($user, $conditions, $debug);
					}
					
			        /*
					 * per ogni user ctrl se ha gia' saldato al cassiere / tesoriere SummaryOrder.saldato_a
					 */					
					$summaryOrderResults = $SummaryOrder->select_to_order($user, $summaryOrderPlu[$model]['order_id'], $summaryOrderPlu['User']['id']);
					if(!empty($summaryOrderResults) && $summaryOrderResults['SummaryOrder']['saldato_a']!=null) { 
						$results['Delivery'][0]['Order'][$numOrder][$model][$numResult]['SummaryOrder']['importo_pagato'] = $summaryOrderResults['SummaryOrder']['importo_pagato'];
						$results['Delivery'][0]['Order'][$numOrder][$model][$numResult]['SummaryOrder']['importo_pagato_'] = number_format($summaryOrderResults['SummaryOrder']['importo_pagato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						$results['Delivery'][0]['Order'][$numOrder][$model][$numResult]['SummaryOrder']['importo_pagato_e'] = number_format($summaryOrderResults['SummaryOrder']['importo_pagato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						$results['Delivery'][0]['Order'][$numOrder][$model][$numResult]['SummaryOrder']['saldato_a'] = $summaryOrderResults['SummaryOrder']['saldato_a'];
					}
					else {
						$results['Delivery'][0]['Order'][$numOrder][$model][$numResult]['SummaryOrder']['importo_pagato'] = null;
						$results['Delivery'][0]['Order'][$numOrder][$model][$numResult]['SummaryOrder']['importo_pagat_o'] = null;
						$results['Delivery'][0]['Order'][$numOrder][$model][$numResult]['SummaryOrder']['importo_pagato_e'] = null;
						$results['Delivery'][0]['Order'][$numOrder][$model][$numResult]['SummaryOrder']['saldato_a'] = null;
					}
					
					/*
					 * Importo originale
					 */
					$results['Delivery'][0]['Order'][$numOrder][$model][$numResult]['User']['totImporto'] = $totImporto;
					$results['Delivery'][0]['Order'][$numOrder][$model][$numResult]['User']['totImporto_'] = number_format($totImporto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results['Delivery'][0]['Order'][$numOrder][$model][$numResult]['User']['totImporto_e'] = number_format($totImporto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		
                    /*
                     * dati gia' inseriti
                     */
                    /*
                    if ($importo_type == $importo_type_db) {
                        $results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_percentuale'] = 0;
                        $results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_importo'] = $summaryOrderPlu[$model]['importo_'.$prefix];
                    } 
                    else */
                    if(!empty($summaryOrderResults) && $summaryOrderResults['SummaryOrder']['saldato_a']!=null) {
						$results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_percentuale'] = 0;
                        $results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_importo'] = $summaryOrderPlu[$model]['importo_'.$prefix];  
                        $results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_importo_'] = number_format($summaryOrderPlu[$model]['importo_'.$prefix],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
                        $results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_importo_e'] = number_format($summaryOrderPlu[$model]['importo_'.$prefix],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';                   
                    }
                    else {

                        /*
                         * dati nuovi
                         */
                        switch ($userOptions) {
                            case "options-qta":
                                $percentualeRispettoAlTotale = round(($summaryOrderPlu[$model]['importo'] * 100 / $totali), 2);
                                $importo_new = round(($importo * $percentualeRispettoAlTotale / 100), 2);
                                
								if($model=='SummaryOrderCostLess') // e' uno sconto => negativo
                                	$importo_new = (-1 * $importo_new);
								
                                $results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_percentuale'] = $percentualeRispettoAlTotale;
                                $results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_importo'] = $importo_new;
                                $results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_importo_'] = number_format($importo_new,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
                                $results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_importo_e'] = number_format($importo_new,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
                                break;
                            case "options-weight":
                                $percentualeRispettoAlTotale = round(($summaryOrderPlu[$model]['peso'] * 100 / $totali), 2);
                                $importo_new = round(($importo * $percentualeRispettoAlTotale / 100), 2);
								
								if($model=='SummaryOrderCostLess') // e' uno sconto => negativo
                                	$importo_new = (-1 * $importo_new);
									
                                $results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_percentuale'] = $percentualeRispettoAlTotale;
                                $results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_importo'] = $importo_new;
                                $results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_importo_'] = number_format($importo_new,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
                                $results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_importo_e'] = number_format($importo_new,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
                                break;
                            case "options-users":
                                $percentualeRispettoAlTotale = round((100 / $totali), 2);
                                $importo_new = round(($importo / $totali), 2);
								
								if($model=='SummaryOrderCostLess') // e' uno sconto => negativo
                                	$importo_new = (-1 * $importo_new);
									
                                $results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_percentuale'] = $percentualeRispettoAlTotale;
                                $results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_importo'] = $importo_new;
                                $results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_importo_'] = number_format($importo_new,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
                                $results['Delivery'][0]['Order'][$numOrder][$model][$numResult][$model][$prefix.'_importo_e'] = number_format($importo_new,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
                                break;
                        } // end switch
                    }
                }
        } // foreach ($results['Delivery'][0]['Order'] as $numOrder => $order) 

		$esito['importo'] = $importo;
		$esito['totaleOrdineGiaSaldati'] = $totaleOrdineGiaSaldati;
		$esito['results'] = $results;

		self::d($results, false);
		
 		return $esito;		
	}	
	
	/*
	 * in base alla scelta dell'utente 
	 * ex Divido il trasporto in base all'importo di ogni utente / Divido il trasporto in base al peso di ogni acquisto / Divido il trasporto per ogni utente 
	 * calcolo il totale
	 * $model = SummaryOrderTrasport SummaryOrderCostMore SummaryOrderCostLess
	 */
	public function _getTotali($user, $model, $orderResult, $userOptions, $debug=false) {
		
		$esito = 0;
						
		if(empty($model) || empty($orderResult)) {
			$esito['CODE'] = "500";
			$esito['MSG'] = "Parametri errati";
			return $esito; 
		}	

		if(!is_array($orderResult))
			$orderResult = $this->_getOrderById($user, $orderResult, $debug);
		
		switch ($model) {
			case 'SummaryOrderTrasport':
				App::import('Model', 'SummaryOrderTrasport');
				$Model = new SummaryOrderTrasport;
				$modelTable = Configure::read('DB.prefix')."summary_order_trasports";		
			break;
			case 'SummaryOrderCostMore':
				App::import('Model', 'SummaryOrderCostMore');
				$modelTable = Configure::read('DB.prefix')."summary_order_cost_mores";
				$Model = new SummaryOrderCostMore;					
			break;
			case 'SummaryOrderCostLess':
				App::import('Model', 'SummaryOrderCostLess');
				$modelTable = Configure::read('DB.prefix')."summary_order_cost_lesses";
				$Model = new SummaryOrderCostLess;					
			break;
			default:
				self::x("AjaxGasCode::getTotali model [$model] non valido");
			break;			
		}

        switch ($userOptions) {
            case "options-qta":
                /*
                 * ottengo il TOTALE dell'IMPORTO dell'ordine
                 * 	totale importo utente : x = totale importo ordine : 100%
				 * escludo eventuali Summaryorder gia' pagati al cassiere / tesoriere
                 */
                $sql = "SELECT sum(t.importo) as totale FROM (
            			SELECT ".$model.".*, SummaryOrder.saldato_a 
						FROM
							".$modelTable." as ".$model."  
							LEFT JOIN ".Configure::read('DB.prefix')."summary_orders as SummaryOrder ON 
							(SummaryOrder.organization_id = ".$user->organization['Organization']['id']." and ".$model.".order_id = SummaryOrder.order_id and ".$model.".user_id = SummaryOrder.user_id and SummaryOrder.saldato_a is NOT null) 
						WHERE
							".$model.".organization_id = ".$user->organization['Organization']['id']."
							and ".$model.".order_id = ".$orderResult['Order']['id']." ) as t WHERE t.saldato_a is null";
                break;
            case "options-weight":
                /*
                 * ottengo il TOTALE del peso dell'ordine
                 * 	totale peso utente : x = totale peso ordine : 100%
                 */
                $sql = "SELECT sum(t.peso) as totale FROM (
            			SELECT ".$model.".*, SummaryOrder.saldato_a 
						FROM
							".$modelTable." as ".$model."  
							LEFT JOIN ".Configure::read('DB.prefix')."summary_orders as SummaryOrder ON 
							(SummaryOrder.organization_id = ".$user->organization['Organization']['id']." and ".$model.".order_id = SummaryOrder.order_id and ".$model.".user_id = SummaryOrder.user_id and SummaryOrder.saldato_a is NOT null) 
						WHERE
							".$model.".organization_id = ".$user->organization['Organization']['id'] . "
							and ".$model.".order_id = ".$orderResult['Order']['id'].") as t WHERE t.saldato_a is null";
                break;
            case "options-users":
                /*
                 * ottengo il TOTALE degli UTENTI dell'ordine
                 * 	totale utente : x = totale ordine : 100%
                 */
                $sql = "SELECT count(t.user_id) as totale FROM (
            			SELECT ".$model.".*, SummaryOrder.saldato_a 
						FROM
							".$modelTable." as ".$model."  
							LEFT JOIN ".Configure::read('DB.prefix')."summary_orders as SummaryOrder ON 
							(SummaryOrder.organization_id = ".$user->organization['Organization']['id']." and ".$model.".order_id = SummaryOrder.order_id and ".$model.".user_id = SummaryOrder.user_id and SummaryOrder.saldato_a is NOT null) 
						WHERE
							".$model.".organization_id = ".$user->organization['Organization']['id'] . "
							and ".$model.".order_id = ".$orderResult['Order']['id'].") as t WHERE t.saldato_a is null";
                break;
			default:
				self::x("AjaxGasCode::getTotali userOptions $userOptions non valido");
			break;
        }
		
		self::d($sql, $debug);
		
		$result = $Model->query($sql);
		if (!empty($result)) {
			$result = current($result);
			$esito = $result[0]['totale'];
			if(empty($esito)) $esito = 0;
		}
		
		self::d('esito '.$esito, $debug);		
		
		return $esito;
	}
		
	private function _getSummaryOrderPlus($model, $user, $orderResult, $options, $debug) {
		
		$esito = [];
		$i=0;

		App::import('Model', 'Cart');
		$Cart = new Cart;
		
		App::import('Model', 'SummaryOrder');
		$SummaryOrder = new SummaryOrder;
		
		App::import('Model', 'SummaryOrderAggregate');
		$SummaryOrderAggregate = new SummaryOrderAggregate;
		
		switch ($model) {
			case 'SummaryOrderTrasport':
				App::import('Model', 'SummaryOrderTrasport');
				$Model = new SummaryOrderTrasport;					
			break;
			case 'SummaryOrderCostMore':
				App::import('Model', 'SummaryOrderCostMore');
				$Model = new SummaryOrderCostMore;					
			break;
			case '':
				App::import('Model', 'SummaryOrderCostLess');
				$Model = new SummaryOrderCostLess;					
			break;
		}

		$Model->unbindModel(['belongsTo' => ['Delivery','Order']]);
		
		
		$options = [];
		$options['conditions'] = [$model.'.organization_id' => $user->organization['Organization']['id'],
								  $model.'.order_id' => $orderResult['Order']['id']];
		$options['order'] = Configure::read('orderUser');
		$options['recursive'] = 1; 
		$modelResults = $Model->find('all', $options);

		if(!empty($modelResults)) 
		foreach ($modelResults as $modelResult) {

			$summaryOrderResults = $SummaryOrder->select_to_order($user, $modelResult[$model]['order_id'], $modelResult['User']['id']);
			
			if(empty($summaryOrderResults) || $summaryOrderResults['SummaryOrder']['saldato_a']==null) {
				
				/*
				 * non saldato
				 *
				 * per ogni user estraggo il totale degli acquisti originale o importo dati aggregato
				 */
            	$totImporto = 0;
            	
				$summaryOrderAggregateResults = $SummaryOrderAggregate->select_to_order($user, $modelResult[$model]['order_id'], $modelResult['User']['id']);
				self::d($summaryOrderAggregateResults, false);
				if(!empty($summaryOrderAggregateResults)) {
					$totImporto = $summaryOrderAggregateResults[0]['SummaryOrderAggregate']['importo'];
				}
				else {
					$conditions = []; 
					$conditions['Cart.user_id'] = $modelResult['User']['id'];
					$conditions['Order.id'] = $modelResult[$model]['order_id'];
					$totImporto = $Cart->getTotImporto($user, $conditions, $debug);
				}
				$totImporto_ = number_format($totImporto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				$totImporto_e = number_format($totImporto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
				
				if($totImporto_ != $modelResult[$model]['importo_']) {
					$esito[$i] = $modelResult;
					$esito[$i]['totImporto'] = $totImporto;
					$esito[$i]['totImporto_'] = $totImporto_;
					$esito[$i]['totImporto_e'] = $totImporto_e;
				}
			} // end if(empty($summaryOrderResults) || $summaryOrderResults['SummaryOrder']['saldato_a']==null) 
		} // foreach ($modelResults as $modelResult) 
		
		return $esito;
	}	
}