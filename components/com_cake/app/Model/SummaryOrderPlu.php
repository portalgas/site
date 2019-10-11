<?php
App::uses('AppModel', 'Model');

class SummaryOrderPlu extends AppModel {
  
    public $useTable = false;
   
	public function mySave($user, $model, $request, $debug=false) {
		
		self::d($request, $debug);
		
		switch ($model) {
			case 'SummaryOrderTrasport':
				App::import('Model', 'SummaryOrderTrasport');
				$Model = new SummaryOrderTrasport;
				$modelTable = Configure::read('DB.prefix')."summary_order_trasports";
				
				$prefix_db = 'trasport';	
				$prefix_order = 'trasport';	
				$prefix = 'trasport_importo';	// e' il valore calcolare a runtime
				
				$msg_insert = __('Insert Trasport');
				$msg_delete = __('Delete Trasport');
				$msg_saved = __('Trasport has been saved');
			break;
			case 'SummaryOrderCostMore':
				App::import('Model', 'SummaryOrderCostMore');
				$modelTable = Configure::read('DB.prefix')."summary_order_cost_mores";
				$Model = new SummaryOrderCostMore;
				
				$prefix_db = 'cost_more';	
				$prefix_order = 'cost_more';
				$prefix = 'cost_more_importo';	// e' il valore calcolare a runtime
				
				$msg_insert = __('Insert CostMore');
				$msg_delete = __('Delete CostMore');
				$msg_saved = __('CostMore has been saved');
			break;
			case 'SummaryOrderCostLess':
				App::import('Model', 'SummaryOrderCostLess');
				$modelTable = Configure::read('DB.prefix')."summary_order_cost_lesses";
				$Model = new SummaryOrderCostLess;
				
				$prefix_db = 'cost_less';
				$prefix_order = 'cost_less';	
				$prefix = 'cost_less_importo';	// e' il valore calcolare a runtime
				
				$msg_insert = __('Insert CostLess');
				$msg_delete = __('Delete CostLess');
				$msg_saved = __('CostLess has been saved');				
			break;
			default:
				self::x("AjaxGasCode::getData model [$model] non valido");
			break;			
		}
		
		$order_id = $request['data']['order_id'];
		$importo_order = $request['data'][$prefix_order];
		$options = $request['data']['summay-order-plus-options'];
		$actionSubmit = $request['data'][$model]['actionSubmit'];
							
		 /*
		  *  actionSubmit = submitImportoInsert   inserisce importo del ...
		  *  actionSubmit = submitImportoUpdate   aggiorna importo del ...
		  *  actionSubmit = submitImportoDelete   elimina importo del ...
		  *  actionSubmit = submitElabora		  salva per ogni utente la % di ... 
		 */
		try {
		
			switch ($actionSubmit) {
				case 'submitImportoInsert':	/* inserisce importo del trasporto */
					
					/*
					 * ripulisco SummaryOrderTrasport anche se gia' vuoto
					 */
					$Model->delete_to_order($user, $order_id, $debug);

					/*
					 * aggiorno SummaryOrder....
					 * 		importo_... = 0 (dettaglio per ogni utente)
					*/
					$Model->populate_to_order($user, $order_id, $debug);
					
					/*
					 * aggiorno Order
					*/	   					
					$sql ="UPDATE ".Configure::read('DB.prefix')."orders
						   SET
								".$prefix_db." = ".$this->importoToDatabase($importo_order).",
								".$prefix_db."_type = null,
								modified = '".date('Y-m-d H:i:s')."'
						  WHERE
								organization_id = ".(int)$user->organization['Organization']['id']." and id = ".$order_id;
					self::d($sql, $debug);
					$this->query($sql);
					
					return $msg_insert;	   					
				break;
				case 'submitImportoUpdate': /* aggiorna importo del ... */
							
					$Model->delete_to_order($user, $order_id, $debug);
					$Model->populate_to_order($user, $order_id, $debug);
						
					/*
					 * aggiorno Order
					*/
					$sql ="UPDATE `".Configure::read('DB.prefix')."orders`
						   SET
								".$prefix_db." = ".$this->importoToDatabase($importo_order).",
								".$prefix_db."_type = null,
								modified = '".date('Y-m-d H:i:s')."'
						  WHERE
								organization_id = ".(int)$user->organization['Organization']['id']." and id = ".$order_id;
					self::d($sql, $debug);
					$this->query($sql);
														
					return $msg_insert;
				break;
				case 'submitImportoDelete': /* elimina importo del .... */
					/*
					 * ripulisco SummaryOrderTrasport, .... anche se gia' vuoto
					*/
					$Model->delete_to_order($user, $order_id, $debug);
					
					/*
					 * ripulisco Order
					*/
					$Model->delete_importo_to_order($user, $order_id, $debug);
														
					return $msg_delete;
				break;
				case 'submitElabora': /* salva per ogni utente la % di trasporto... */
					
					/*
					 * popolo SummaryOrder...
					 * 		ho SummaryOrder....importo_trasport = 0 (dettaglio di ogni utente) => dopo lo popolo con i campi del form 
					*/
					$modelResults = $Model->select_to_order($user, $order_id, $debug);
					if(empty($modelResults))
						$Model->populate_to_order($user, $order_id, $debug);
					
					/*
					 * aggiorno Order
					 */
					$options_type_db = null;
					switch ($options) {
						case "options-qta":
							$options_type_db = 'QTA';
							break;
						case "options-weight":
							$options_type_db = 'WEIGHT';
							break;
						case "options-users":
							$options_type_db = 'USERS';
							break;
						deafult:
							self::x('SummaryOrderPlu valore options_type_db ['.$options_type_db.' inatteso!');
						break;
					}
					
					$sql ="UPDATE ".Configure::read('DB.prefix')."orders SET
								".$prefix_db." = ".$this->importoToDatabase($importo_order).",
								".$prefix_db."_type = '$options_type_db',
								modified = '".date('Y-m-d H:i:s')."'
							WHERE
								organization_id = ".(int)$user->organization['Organization']['id']." and id = ".$order_id;
					self::d($sql, $debug);
					$this->query($sql);
					
					if(isset($request['data']['Data']))
					foreach($request['data']['Data'] as $key => $value) {
						$user_id = $key;
						$summary_order_plus_importo = $this->importoToDatabase($value);
								
						self::d(['user_id '.$user_id, 'summary_order_plus_importo '.$summary_order_plus_importo], false);
																					
						$sql = "UPDATE ".$modelTable." SET
							importo_".$prefix_db." = '$summary_order_plus_importo',
							modified = '".date('Y-m-d H:i:s')."'
						WHERE
							organization_id = ".(int)$user->organization['Organization']['id']."
							and order_id = ".(int)$order_id." 
							and user_id = ".(int)$user_id;
						self::d($sql, $debug);
						$result = $Model->query($sql);
					}	
					
					return $msg_saved;
				break;
					
			} // end swicth
			
		}catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
			self::d($e, $debug);
		}		
	}
	
	
    /*
     * aggiunge ad un ordine le eventuali 
     *  SummaryOrder
     *  SummaryOrderAggregate 
     *  SummaryOrderTrasport spese di trasporto
     *  SummaryOrderMore spese generiche
     *  SummaryOrderLess sconti
     *
     *  call 
     *      ExportDocs::userCart
     *      Delivery::tabsAjaxUserCartDeliveries 
     *
     */
    public function addSummaryOrder($user, $order, $user_id, $debug=false) {
        
        $order_id = $order['Order']['id'];		

        /*
        * dati dell'ordine
        */
        $hasTrasport = $order['Order']['hasTrasport']; /* trasporto */
        $trasport = $order['Order']['trasport'];
        $hasCostMore = $order['Order']['hasCostMore']; /* spesa aggiuntiva */
        $cost_more = $order['Order']['cost_more'];
        $hasCostLess = $order['Order']['hasCostLess'];  /* sconto */
        $cost_less = $order['Order']['cost_less'];
        $typeGest = $order['Order']['typeGest'];   /* AGGREGATE / SPLIT */

        $resultsSummaryOrder = [];
        $resultsSummaryOrderAggregate = [];
        $resultsSummaryOrderTrasport = [];
        $resultsSummaryOrderCostMore = [];
        $resultsSummaryOrderCostLess = [];

		App::import('Model', 'SummaryOrder');
		$SummaryOrder = new SummaryOrder;

		$resultsSummaryOrder = $SummaryOrder->select_to_order($user, $order_id, $user_id);
			
        if($hasTrasport=='Y') {
            App::import('Model', 'SummaryOrderTrasport');
            $SummaryOrderTrasport = new SummaryOrderTrasport;

            $resultsSummaryOrderTrasport = $SummaryOrderTrasport->select_to_order($user, $order_id, $user_id);
        }
        if($hasCostMore=='Y') {
            App::import('Model', 'SummaryOrderCostMore');
            $SummaryOrderCostMore = new SummaryOrderCostMore;

            $resultsSummaryOrderCostMore = $SummaryOrderCostMore->select_to_order($user, $order_id, $user_id);
        }
        if($hasCostLess=='Y') {
            App::import('Model', 'SummaryOrderCostLess');
            $SummaryOrderCostLess = new SummaryOrderCostLess;

            $resultsSummaryOrderCostLess = $SummaryOrderCostLess->select_to_order($user, $order_id, $user_id);
        }

        App::import('Model', 'SummaryOrderAggregate');
        $SummaryOrderAggregate = new SummaryOrderAggregate;
        $resultsSummaryOrderAggregate = $SummaryOrderAggregate->select_to_order($user, $order_id, $user_id); // se l'ordine e' ancora aperto e' vuoto

        $results = [];
        $results['SummaryOrder']          = $resultsSummaryOrder;
        $results['SummaryOrderAggregate'] = $resultsSummaryOrderAggregate;
        $results['SummaryOrderTrasport']  = $resultsSummaryOrderTrasport;
        $results['SummaryOrderCostMore']  = $resultsSummaryOrderCostMore;
        $results['SummaryOrderCostLess']  = $resultsSummaryOrderCostLess;

		self::d($results, $debug);

        return $results;
    }	
}