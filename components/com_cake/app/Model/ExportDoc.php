<?php
App::uses('AppModel', 'Model');


class ExportDoc extends AppModel {

	public $useTable = 'deliveries';
	public $name = 'Delivery'; 
	public $alias = 'Delivery'; 	
	public $actsAs = ['Data'];
	public $virtualFields = ['luogoData' => "CONCAT_WS(' - ',Delivery.luogo,DATE_FORMAT(Delivery.data, '%W, %e %M %Y'))"];
	
	public $exportRowsNum;
	public $exportRows;
	
	/*
	 * li definisco globali cosi' faccio unset 
	 */
	public $summaryOrderAggregateResults=[];
	public $summaryOrderTrasportResults=[];
	public $summaryOrderCostMoreResults=[];
	public $summaryOrderCostLessResults=[];
	
	public $hasMany = [
			'Order' => [
					'className' => 'Order',
					'foreignKey' => 'delivery_id',
					'dependent' => false,
					'conditions' => '',
					'fields' => '',
					'order' => '',
					'limit' => '',
					'offset' => '',
					'exclusive' => '',
					'finderQuery' => '',
					'counterQuery' => ''
			]
	];
	
	/*
	 * crea un oggetto con tutte le modifiche di un acquisto (qta e importo dell'utente o del referente) in base ad un ordine
	 * 
	 *  [TRDATA] => Array (
     *               [NUM] => 5
     *               [CODICE] => 001
     *               [NAME] => Bimba - Camicia da notte estiva 10 anni
     *               [PREZZO] => 18.00 €
     *               [ARTICLEQTA] => 1.00
     *               [UMRIF] => PZ
     *               [PREZZO_UMRIF] => 33,00/Kg (prezzo / qta)
     *               [QTA] => 5
     *               [IMPORTO] => 90,00 €
     *               [ISQTAMOD] => 1
     *               [ISIMPORTOMOD] => 
     *               [QTAUSER] => -
     *               [IMPORTOUSER] => -
     *               [QTAREF] => 5
     *               [IMPORTOREF] => 90,00 €
     *               [IMPORTOFORZATO] => -
     *           )
     *           
     * 	sotto [$results['Delivery'][$numDelivery]['Order'][$numOrder] accodo ExportRows
     *  e cancello Article, ArticlesOrder, Cart, User
	 *		
     *   gli passo 
     *   	- tutti gli acquisti degli utenti          
     *   			$results = $Delivery->getDataWithoutTabs(... articlesOrdersInOrderAndCartsAllUsers()
     *   	- dati aggregati (SummaryOrderAggregate) in SummaryOrderAggregate::managementCartsGroupByUsers 
     *   			$summaryOrderAggregateResults = $SummaryOrderAggregate->select_to_order($this->user, $order_id) 
	 * 		- spese di trasporto  (SummaryOrderTrasport)
	 * 				$summaryOrderTrasportResults = $SummaryOrderTrasport->select_to_order($this->user, $order_id) 
	 * 		- costi aggiuntivi (SummaryOrderCostMore)
	 * 				$summaryOrderCostMoreResults = $SummaryOrderCostMore->select_to_order($this->user, $order_id) 
	 * 		- sconti (SummaryOrderCostLess)
	 * 				$summaryOrderCostLessResults = $SummaryOrderCostLess->select_to_order($this->user, $order_id) 
	 */
	public function getCartCompliteOrder($order_id, $results, $summaryOrderAggregateResults=[], $summaryOrderTrasportResults=[], $summaryOrderCostMoreResults=[], $summaryOrderCostLessResults=[], $debug = false) {
//$debug = true;

		$this->summaryOrderAggregateResults = $summaryOrderAggregateResults;
		$this->summaryOrderTrasportResults = $summaryOrderTrasportResults;
		$this->summaryOrderCostMoreResults = $summaryOrderCostMoreResults;
		$this->summaryOrderCostLessResults = $summaryOrderCostLessResults;
	
		foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {
			
		    /*
			 * lo commento se no mi escludo gli eventuali dati inseriti ex-novo da SummaryOrderAggregate
			 * if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0)
			 */
		    if(isset($result['Delivery']['Order']))
			foreach($result['Delivery']['Order'] as $numOrder => $order) {
			
				if($order['Order']['id']==$order_id) {
					
					/*
					 * debug ini
					 */
					if($debug) {
						echo '<h1>ExportDoc::getCartCompliteOrder() - ordine '.$order['Order']['id'].'</h1>';
						
						echo '<h2>Trasporto</h2>';
						if($order['Order']['hasTrasport']=='Y')	{
							echo 'Ordine con TRASPORTO - importo '.$order['Order']['trasport'];
							if(empty($this->summaryOrderTrasportResults))
								echo ' => importo del trasporto non suddiviso per gli utenti (summaryOrderTrasportResults NON valorizzato)';
							else 
								echo ' => importo del trasporto suddiviso per gli utenti (summaryOrderTrasportResults valorizzato)';
						}
						else 	
						if($order['Order']['hasTrasport']=='N' && $order['Order']['trasport']>0)	echo '<br />Ordine senza TRASPORTO, importo del trasporto '.$order['Order']['trasport'].' => dato INCONGRUO';
						else 
							echo 'Ordine senza TRASPORTO';
						
						echo '<h2>Costo aggiuntivi</h2>';
						if($order['Order']['hasCostMore']=='Y')	{
							echo 'Ordine con Costo Aggiuntivo - importo '.$order['Order']['cost_more'];
							if(empty($this->summaryOrderCostMoreResults))
								echo ' => importo del costo aggiuntivo non suddiviso per gli utenti (summaryOrderCostMoreResults NON valorizzato)';
							else
								echo ' => importo del costo aggiuntivo suddiviso per gli utenti (summaryOrderCostMoreResults valorizzato)';
						}
						else
						if($order['Order']['hasCostMore']=='N' && $order['Order']['cost_more']>0)	echo '<br />Ordine senza COSTO AGGIUNTIVO, importo del costo aggiuntivo '.$order['Order']['cost_more'].' => dato INCONGRUO';
						else
							echo 'Ordine senza COSTO AGGIUNTIVO';
						
						echo '<h2>Sconto</h2>';
						if($order['Order']['hasCostLess']=='Y')	{
							echo 'Ordine con Sconto - importo '.$order['Order']['cost_less'];
							if(empty($summaryOrderCostLessResults))
								echo ' => importo dello sconto non suddiviso per gli utenti (summaryOrderCostLessResults NON valorizzato)';
							else
								echo ' => importo dello sconto aggiuntivo suddiviso per gli utenti (summaryOrderCostLessResults valorizzato)';
						}
						else
						if($order['Order']['hasCostLess']=='N' && $order['Order']['cost_less']>0)	echo '<br />Ordine senza SCONTO, importo dello sconto '.$order['Order']['cost_less'].' => dato INCONGRUO';
						else
							echo 'Ordine senza SCONTO';
												
						echo '<h2>Dati del carrello aggregati per ogni utente</h2>';
						if(empty($summaryOrderAggregateResults)) 
							echo 'nessuna aggregazione (summaryOrderAggregateResults NON valorizzato)';
						else
							echo ' effettuata aggregazione (summaryOrderAggregateResults valorizzato)';
					}
					/*
					 * debug end
					 */					
					
					$tot_qta_single_user = 0;
					$tot_importo_single_user = 0;
					$tot_qta = 0;
					$tot_importo = 0;
					$summary_order_aggregate_tot_importo = 0;
					$user_id_old = 0;
					
					$this->exportRowsNum = -1;
					$this->exportRows = [];
					if(isset($order['ArticlesOrder']))
					foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {
				
						/*
						 * per l'UTENTE trattato calcolo TOTALI
						 */
						if($user_id_old != $order['User'][$numArticlesOrder]['id']) {
			
							if($numArticlesOrder>0) {
								
								$summary_order_aggregate_tot_importo += $this->_calcolaSubTotaleUser($user_id_old, $tot_qta_single_user, $tot_importo_single_user, $debug);
									
								$tot_qta_single_user = 0;
								$tot_importo_single_user = 0;
									
							} // if($numArticlesOrder>0)
								
								
							/*
							 * inizia un nuovo UTENTE
							 */
							if(isset($order['User'][$numArticlesOrder]['Profile']['phone'])) $user_phone = $order['User'][$numArticlesOrder]['Profile']['phone'];
							else $user_phone = '';
							
							if(isset($order['User'][$numArticlesOrder]['email'])) $user_email = $order['User'][$numArticlesOrder]['email'];
							else $user_email = '';

							if(isset($order['User'][$numArticlesOrder]['Profile']['address'])) {
								$user_address = '';
								$user_address = $order['User'][$numArticlesOrder]['Profile']['address'].' ';
								if(isset($order['User'][$numArticlesOrder]['Profile']['city']) && !empty($order['User'][$numArticlesOrder]['Profile']['city'])) 
									$user_address .= $order['User'][$numArticlesOrder]['Profile']['city'].' ';
								if(isset($order['User'][$numArticlesOrder]['Profile']['postal_code']) && !empty($order['User'][$numArticlesOrder]['Profile']['postal_code'])) 
									$user_address .= $order['User'][$numArticlesOrder]['Profile']['postal_code'].' ';
							}
							else $user_address = '';
							
							$this->exportRowsNum++;
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRGROUP']['LABEL'] = "Utente: ".$order['User'][$numArticlesOrder]['name'];
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRGROUP']['LABEL_ID'] = $order['User'][$numArticlesOrder]['id'];
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRGROUP']['LABEL_PHONE'] = $user_phone;
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRGROUP']['LABEL_EMAIL'] = $user_email;
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRGROUP']['LABEL_ADDRESS'] = $user_address;
						}  // if($user_id_old != $order['User'][$numArticlesOrder]['id'])
			
	
	
						/*
						 * gestione QTA e IMPORTI
						* */
						$qta_modificata = false;
						if($order['Cart'][$numArticlesOrder]['qta_forzato']>0) {
							$qta = $order['Cart'][$numArticlesOrder]['qta_forzato'];
							$qta_modificata = true;
						}
						else {
							$qta = $order['Cart'][$numArticlesOrder]['qta'];
							$qta_modificata = false;
						}
						$importo_modificato = false;
						if($order['Cart'][$numArticlesOrder]['importo_forzato']==0) {
							if($order['Cart'][$numArticlesOrder]['qta_forzato']>0) 
								$importo = ($order['Cart'][$numArticlesOrder]['qta_forzato'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']);
							else {
								$importo = ($order['Cart'][$numArticlesOrder]['qta'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']);
							}
						}
						else {
							$importo = $order['Cart'][$numArticlesOrder]['importo_forzato'];
							$importo_modificato = true;
						}
			
						if($order['Cart'][$numArticlesOrder]['deleteToReferent']=='N') {
							$tot_qta_single_user += $qta;
							$tot_importo_single_user += $importo;
							$tot_qta += $qta;
							$tot_importo += $importo;
						}
						
						$importo_ = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						
						/*
						 * valori di ogni singolo acquisto con le possibili modifiche del referente
						 */
						$this->exportRowsNum++;
						if($order['Cart'][$numArticlesOrder]['deleteToReferent']=='Y')
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['DELETE_TO_REFERENT'] = 'Y';
						else
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['DELETE_TO_REFERENT'] = 'N';
						$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['NUM'] = ($numArticlesOrder+1);
						$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['CODICE'] = $order['Article'][$numArticlesOrder]['codice'];
						/*
						 * per gli articoli DES che prendono gli articoli da un'altro GAS il pregresso potrebbe non avere ArticlesOrder.name
						 */			
						if(!empty($order['ArticlesOrder'][$numArticlesOrder]['name']))
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['NAME'] = $order['ArticlesOrder'][$numArticlesOrder]['name'];
						else
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['NAME'] = $order['Article'][$numArticlesOrder]['name'];
						$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['PREZZO'] = $order['ArticlesOrder'][$numArticlesOrder]['prezzo'];
						$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['PREZZO_'] = $order['ArticlesOrder'][$numArticlesOrder]['prezzo_'];
						$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['PREZZO_E'] = $order['ArticlesOrder'][$numArticlesOrder]['prezzo_e'];
						$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['ARTICLEQTA'] = $order['Article'][$numArticlesOrder]['qta'];
						$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['UM'] = $order['Article'][$numArticlesOrder]['um'];
						$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['UMRIF'] = $order['Article'][$numArticlesOrder]['um_riferimento'];
						$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['PREZZO_UMRIF'] = $this->utilsCommons->getArticlePrezzoUM($order['ArticlesOrder'][$numArticlesOrder]['prezzo'], $order['Article'][$numArticlesOrder]['qta'], $order['Article'][$numArticlesOrder]['um'], $order['Article'][$numArticlesOrder]['um_riferimento']);

						$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['QTA'] = $qta;
						$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['IMPORTO'] = $importo;
						$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['IMPORTO_'] = $importo_;
						$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['IMPORTO_E'] = $importo_.'&nbsp;&euro;';
						$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['ISQTAMOD'] = $qta_modificata;
						$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['ISIMPORTOMOD'] = $importo_modificato;
						
						/*
						 * qta e importo dell'utente
						*/
						if($order['Cart'][$numArticlesOrder]['qta']==0)
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['QTAUSER'] = '-';
						else
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['QTAUSER'] = $order['Cart'][$numArticlesOrder]['qta'];
			
						if($order['Cart'][$numArticlesOrder]['qta']==0)
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['IMPORTOUSER'] = '-';
						else {
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['IMPORTOUSER'] = ($order['Cart'][$numArticlesOrder]['qta'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']);
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['IMPORTOUSER_'] = number_format(($order['Cart'][$numArticlesOrder]['qta'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['IMPORTOUSER_E'] = number_format(($order['Cart'][$numArticlesOrder]['qta'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						}
									
						/*
						 * qta e importo del referente
						*/
						if($order['Cart'][$numArticlesOrder]['qta_forzato']==0)
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['QTAREF'] = '-';
						else
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['QTAREF'] = $order['Cart'][$numArticlesOrder]['qta_forzato'];
						if($order['Cart'][$numArticlesOrder]['qta_forzato']==0)
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['IMPORTOREF'] = '-';
						else
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['IMPORTOREF'] = number_format(($order['Cart'][$numArticlesOrder]['qta_forzato'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			
						/*
						 * importo forzato
						*/
						if($order['Cart'][$numArticlesOrder]['importo_forzato']==0)
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['IMPORTOFORZATO'] = '-';
						else {
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['IMPORTOFORZATO'] = $order['Cart'][$numArticlesOrder]['importo_forzato'];
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['IMPORTOFORZATO_'] = number_format($order['Cart'][$numArticlesOrder]['importo_forzato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['IMPORTOFORZATO_E'] = number_format($order['Cart'][$numArticlesOrder]['importo_forzato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						}	
								
						/*
						 * nota
						 * */
						if(!empty($order['Cart'][$numArticlesOrder]['nota'])) {
							$this->exportRowsNum++;
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATABIS']['NOTA'] = $order['Cart'][$numArticlesOrder]['nota'];
						}
						
						$user_id_old = $order['User'][$numArticlesOrder]['id'];
					} // foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder)
					
					/*
					 * per l'ultimo utente riporto i sub-totali
					*/
					$summary_order_aggregate_tot_importo += $this->_calcolaSubTotaleUser($user_id_old, $tot_qta_single_user, $tot_importo_single_user, $debug);					
										
					/*
					 * calcolo il totale 
					 */
					if(empty($summaryOrderAggregateResults) || 
					   $tot_importo==$summary_order_aggregate_tot_importo) {
						$tmp_importo = $tot_importo;
						$importo_modificato = false;
						$tmp_importo_completo = ($tot_importo + $order['Order']['trasport'] + $order['Order']['cost_more'] + (-1 * $order['Order']['cost_less']));							
					}
					else {
						$tmp_importo = $summary_order_aggregate_tot_importo;
						$importo_modificato = true;
						$tmp_importo_completo = ($summary_order_aggregate_tot_importo + $order['Order']['trasport'] + $order['Order']['cost_more'] - ($order['Order']['cost_less']));
					}
					
					if(!empty($tot_qta) || !empty($tmp_importo)) {					
						$this->exportRowsNum++;
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['QTA'] = $tot_qta;
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO'] = $tmp_importo;
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_'] = number_format($tmp_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_E'] = number_format($tmp_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['ISIMPORTOMOD'] = $importo_modificato;
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_TRASPORTO'] = $order['Order']['trasport'];
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_TRASPORTO_'] = number_format($order['Order']['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_TRASPORTO_E'] = number_format($order['Order']['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_COST_MORE'] = $order['Order']['cost_more'];
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_COST_MORE_'] = number_format($order['Order']['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_COST_MORE_E'] = number_format($order['Order']['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_COST_LESS'] = $order['Order']['cost_less'];
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_COST_LESS_'] = number_format($order['Order']['cost_less'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_COST_LESS_E'] = number_format($order['Order']['cost_less'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_COMPLETO'] = $tmp_importo_completo;
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_COMPLETO_'] = number_format($tmp_importo_completo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_COMPLETO_E'] = number_format($tmp_importo_completo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
					}
					
					if($debug) {
						echo "<pre>";
						print_r($this->exportRows);
						echo "</pre>";
					}
					
					$results['Delivery'][$numDelivery]['Order'][$numOrder]['ExportRows'] = $this->exportRows;
					unset($results['Delivery'][$numDelivery]['Order'][$numOrder]['Article']);
					unset($results['Delivery'][$numDelivery]['Order'][$numOrder]['ArticlesOrder']);
					unset($results['Delivery'][$numDelivery]['Order'][$numOrder]['Cart']);
					unset($results['Delivery'][$numDelivery]['Order'][$numOrder]['User']);
					unset($results['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrdersPos']);
					
				} // end if($order['Order']['id']==$order_id)
			} // ciclo ORDERS foreach($result['Delivery']['Order'] as $numOrder => $order)
		} // end foreach($results['Delivery'] as $numDelivery => $result['Delivery'])			
		
		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
		
		return $results;
	}
	
	/*
	 * crea un oggetto con tutte le modifiche di un acquisto (qta e importo dell'utente o del referente) in base ad una consegna
	*
	*  [TRDATA] => Array (
	*               [NUM] => 5
	*               [NAME] => Bimba - Camicia da notte estiva 10 anni
	*               [PREZZO] => 18.00 €
	*               [ARTICLEQTA] => 1.00
	*               [UMRIF] => PZ
	*               [PREZZO_UMRIF] => 33,00/Kg (prezzo / qta)
	*               [QTA] => 5
	*               [IMPORTO] => 90,00 €
	*               [ISQTAMOD] => 1
	*               [ISIMPORTOMOD] =>
	*               [QTAUSER] => -
	*               [IMPORTOUSER] => -
	*               [QTAREF] => 5
	*               [IMPORTOREF] => 90,00 €
	*               [IMPORTOFORZATO] => -
	*           )
		 */
	public function getCartCompliteDelivery($delivery_id, $results, $debug = false) {
	/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		exit;
	*/	
		//if($debug) echo '<h1>ExportDoc::getCartCompliteDelivery() - consegna '.$delivery_id.'</h1>';
	
		$this->exportRowsNum = -1;
		$this->exportRows = [];
		
		$tot_qta_user_order = 0;
		$tot_importo_user_order = 0;
		$tot_qta_user_delivery = 0;
		$tot_importo_user_delivery = 0;
		
		$user_id_old = 0;
		if(!empty($results)) {
		foreach($results as $numOrder => $order) {

				
			/*
			 * inizia nuovo ordine
			 */
			$order_id_old = 0;
			
			if($debug) {
				echo '<h3>Ordine '.$order['Order']['data_fine'].' '.$order['SuppliersOrganization']['name'].'</h3>';
				
				if($order['Order']['hasTrasport']=='Y')
					echo '<br />Ordine con TRASPORTO - importo '.$order['Order']['trasport'];
				else
				if($order['Order']['hasTrasport']=='N' && $order['Order']['trasport']>0)	echo '<br />Ordine senza TRASPORTO, importo del trasporto '.$order['Order']['trasport'].' => dato INCONGRUO';
				else
					echo '<br />Ordine senza TRASPORTO';
			}
						
			/*
			 * ciclo degli acquisti di uno user su un ordine
			 */
			foreach($order['User'] as $numResult => $user) {
				
				if($debug) {
					echo '<hr />';
					echo 'User '.$user_id_old.' '.$user['User']['id'].'  - Order '.$order_id_old.' '.$user['Order']['id'];
					echo ' - tot_importo_user_order '.$tot_importo_user_order;
					echo ' - tot_importo_user_delivery '.$tot_importo_user_delivery;
					if($user_id_old==$user['User']['id']) echo ' User <span style="color:green;">uguale</span>';
					else  echo ' User <span style="color:red;">diverso</span>';
					if($order_id_old==$user['Order']['id']) echo ' Order <span style="color:green;">uguale</span>';
					else  echo ' Order <span style="color:red;">diverso</span>';
				}
				
				if($order_id_old==0) {
						
					if($numOrder>0) {
						/*
						 * creo il sub totale di un ordine per ogni utente
						*/
						$this->_calcolaSubTotaleUserOrder($user_id_old, $tot_qta_user_order, $tot_importo_user_order, 0, 0, 0, 0, $debug);
				
						$tot_qta_user_order = 0;
						$tot_importo_user_order = 0;
					}
				} // end if($order_id_old==0)
					
				/*
				 * inizia un nuovo UTENTE
				*/
				if($user_id_old != $user['User']['id']) {
					
					if($user_id_old>0) {
						/*
						 * creo il sub totale per ogni utente
						*/
						$this->_calcolaSubTotaleUser($user_id_old, $tot_qta_user_delivery, $tot_importo_user_delivery, 0, 0, 0, 0, $debug);
								
						$tot_qta_user_delivery = 0;
						$tot_importo_user_delivery = 0;
							
					} // if($user_id_old>0)
											
					
					if(isset($user['User']['Profile']['phone'])) $user_phone = $user['User']['Profile']['phone'];
					else $user_phone = '';
					if(isset($user['User']['email'])) $user_email = $user['User']['email'];
					else $user_email = '';
					if(isset($user['User']['Profile']['address'])) $user_address = $user['User']['Profile']['address'];
					else $user_address = '';

					$this->exportRowsNum++;
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRGROUP']['LABEL'] = "Utente: ".$user['User']['name'];
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRGROUP']['LABEL_ID'] = $user['User']['id'];
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRGROUP']['LABEL_PHONE'] = $user_phone;
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRGROUP']['LABEL_EMAIL'] = $user_email;
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRGROUP']['LABEL_ADDRESS'] = $user_address;
					
					if($debug) echo '<br />&nbsp;&nbsp;&nbsp;'.$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRGROUP']['LABEL'];
					
				}  // if($user_id_old != $user['User']['id'])


				/*
				 * inizia un nuovo ORDINE
				*/
				if($order_id_old==0) {						
					$this->exportRowsNum++;
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRORDER']['LABEL'] = $order['SuppliersOrganization']['name'];
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRORDER']['LABEL_ID'] = $order['Order']['id'];
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRORDER']['LABEL_DATA_INIZIO'] = date('d',strtotime($order['Order']['data_inizio'])).'/'.date('n',strtotime($order['Order']['data_inizio'])).'/'.date('Y',strtotime($order['Order']['data_inizio']));
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRORDER']['LABEL_DATA_FINE'] = date('d',strtotime($order['Order']['data_fine'])).'/'.date('n',strtotime($order['Order']['data_fine'])).'/'.date('Y',strtotime($order['Order']['data_fine']));
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRORDER']['LABEL_DATA_FINE_VALIDATION'] = date('d',strtotime($order['Order']['data_fine_validation'])).'/'.date('n',strtotime($order['Order']['data_fine_validation'])).'/'.date('Y',strtotime($order['Order']['data_fine_validation']));
					
					if($debug) echo '<br />&nbsp;&nbsp;&nbsp;'.$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRORDER']['LABEL'];
				} // end if($order_id_old==0)
				
				/*
				 * gestione QTA e IMPORTI
				* */
				$qta_modificata = false;
				if($user['Cart']['qta_forzato']>0) {
					$qta = $user['Cart']['qta_forzato'];
					$qta_modificata = true;
				}
				else {
					$qta = $user['Cart']['qta'];
					$qta_modificata = false;
				}
				$importo_modificato = false;
				if($user['Cart']['importo_forzato']==0) {
					if($user['Cart']['qta_forzato']>0)
						$importo = ($user['Cart']['qta_forzato'] * $user['ArticlesOrder']['prezzo']);
					else {
						$importo = ($user['Cart']['qta'] * $user['ArticlesOrder']['prezzo']);
					}
				}
				else {
					$importo = $user['Cart']['importo_forzato'];
					$importo_modificato = true;
				}
					
				if($user['Cart']['deleteToReferent']=='N') {
					$tot_qta_user_delivery += $qta;
					$tot_importo_user_delivery += $importo;
					$tot_qta_user_order += $qta;
					$tot_importo_user_order += $importo;
				}
				
				$importo_ = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				
				/*
				 * valori di ogni singolo acquisto con le possibili modifiche del referente
				*/
				$this->exportRowsNum++;
				if($user['Cart']['deleteToReferent']=='Y')
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['DELETE_TO_REFERENT'] = 'Y';
				else
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['DELETE_TO_REFERENT'] = 'N';
				$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['NUM'] = ((int)$numResult+1);
				$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['CODICE'] = $user['Article']['codice'];
				/*
				 * per gli articoli DES che prendono gli articoli da un'altro GAS il pregresso potrebbe non avere ArticlesOrder.name
				 */							
				if(!empty($user['ArticlesOrder']['name']))
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['NAME'] = $user['ArticlesOrder']['name'];
				else
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['NAME'] = $user['Article']['name'];
				$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['PREZZO'] = $user['ArticlesOrder']['prezzo'];
				$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['PREZZO_'] = $user['ArticlesOrder']['prezzo_'];
				$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['PREZZO_E'] = $user['ArticlesOrder']['prezzo_e'];
				$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['ARTICLEQTA'] = $user['Article']['qta'];
				$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['UM'] = $user['Article']['um'];
				$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['UMRIF'] = $user['Article']['um_riferimento'];
				$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['PREZZO_UMRIF'] = $this->utilsCommons->getArticlePrezzoUM($user['ArticlesOrder']['prezzo'], $user['Article']['qta'], $user['Article']['um'], $user['Article']['um_riferimento']);
				
				$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['QTA'] = $qta;
				$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['IMPORTO'] = $importo;
				$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['IMPORTO_'] = $importo_;
				$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['IMPORTO_E'] = $importo_.'&nbsp;&euro;';
				$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['ISQTAMOD'] = $qta_modificata;
				$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['ISIMPORTOMOD'] = $importo_modificato;
				
				/*
				 * qta e importo dell'utente
				*/
				if($user['Cart']['qta']==0)
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['QTAUSER'] = '-';
				else
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['QTAUSER'] = $user['Cart']['qta'];
					
				if($user['Cart']['qta']==0)
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['IMPORTOUSER'] = '-';
				else {
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['IMPORTOUSER'] = ($user['Cart']['qta'] * $user['ArticlesOrder']['prezzo']);
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['IMPORTOUSER_'] = number_format(($user['Cart']['qta'] * $user['ArticlesOrder']['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['IMPORTOUSER_E'] = number_format(($user['Cart']['qta'] * $user['ArticlesOrder']['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
				}
									
				/*
				 * qta e importo del referente
				*/
				if($user['Cart']['qta_forzato']==0)
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['QTAREF'] = '-';
				else
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['QTAREF'] = $user['Cart']['qta_forzato'];
				if($user['Cart']['qta_forzato']==0)
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['IMPORTOREF'] = '-';
				else
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['IMPORTOREF'] = number_format(($user['Cart']['qta_forzato'] * $user['ArticlesOrder']['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
					
				/*
				 * importo forzato
				*/
				if($user['Cart']['importo_forzato']==0)
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['IMPORTOFORZATO'] = '-';
				else {
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['IMPORTOFORZATO'] = $user['Cart']['importo_forzato'];
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['IMPORTOFORZATO'] = number_format($user['Cart']['importo_forzato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['IMPORTOFORZATO'] = number_format($user['Cart']['importo_forzato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
				}
					
				/*
				 * nota
				* */
				if(!empty($user['Cart']['nota'])) {
					$this->exportRowsNum++;
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATABIS']['NOTA'] = $user['Cart']['nota'];
				}
				
				$user_id_old = $user['User']['id'];
				$order_id_old = $user['Order']['id'];
				
				if($debug) echo '<br />&nbsp;&nbsp;&nbsp;Articolo&nbsp;'.$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['NAME'].' importo '.$importo;
				
			} // end foreach($result['User'] as $numDelivery => $user) ciclo Users
			
		} // end foreach($results as $numDelivery => $result) ciclo Orders
			
		/*
		 * per l'ultimo utente riporto i totali
		*/
		$this->_calcolaSubTotaleUserOrder($user['User']['id'], $tot_qta_user_order, $tot_importo_user_order, 0, 0, 0, 0, $debug);
		
		$this->_calcolaSubTotaleUser($user['User']['id'], $tot_qta_user_delivery, $tot_importo_user_delivery, 0, 0, 0, 0, $debug);
			
		if($debug) {
			echo "<pre>";
			print_r($this->exportRows);
			echo "</pre>";
		}

		/*
			echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
	
		} // end if(!empty($results)) 
		
		return $this->exportRows;
	}
		
	/*
	 * return $tot_importo_single_user ( puo' essere sovrascritto da $summary_order_aggregate_tot_importo_single_user se valorizzato)
	 *	cosi' ottengo il totale dell'ordine 
	 */
	private function _calcolaSubTotaleUser($user_id, $tot_qta_single_user, $tot_importo_single_user, $debug) {
		
		$summary_order_aggregate_tot_importo_single_user = 0;
		$summary_order_trasport_tot_importo_single_user = 0;
		$summary_order_cost_more_tot_importo_single_user = 0;
		$summary_order_cost_less_tot_importo_single_user = 0;

		/*
		 * per l'UTENTE trattato ctrl se ci sono dati aggregati
		*/ 
		if(!empty($this->summaryOrderAggregateResults)) {
			if(isset($this->summaryOrderAggregateResults['SummaryOrderAggregate']) && $this->summaryOrderAggregateResults['User']['id']==$user_id)
				$summary_order_aggregate_tot_importo_single_user = $this->summaryOrderAggregateResults['SummaryOrderAggregate']['importo'];
			else {
				foreach($this->summaryOrderAggregateResults as $numResult => $summaryOrderAggregateResult) {
					if($summaryOrderAggregateResult['SummaryOrderAggregate']['user_id']==$user_id) {
						$summary_order_aggregate_tot_importo_single_user = $summaryOrderAggregateResult['SummaryOrderAggregate']['importo'];
						unset($this->summaryOrderAggregateResults[$numResult]);
						break;
					}
				}	
			}
		}
					
		/*
		 * per l'UTENTE trattato ctrl se c'e' la spesa di trasporto
		*/
		if(!empty($this->summaryOrderTrasportResults)) {
			if(isset($this->summaryOrderTrasportResults['SummaryOrderTrasport']) && $this->summaryOrderTrasportResults['User']['id']==$user_id)
				$summary_order_trasport_tot_importo_single_user = $this->summaryOrderTrasportResults['SummaryOrderTrasport']['importo_trasport'];
			else {			
				foreach($this->summaryOrderTrasportResults as $numResult => $summaryOrderTrasportResult) {
					if($summaryOrderTrasportResult['SummaryOrderTrasport']['user_id']==$user_id) {
						$summary_order_trasport_tot_importo_single_user = $summaryOrderTrasportResult['SummaryOrderTrasport']['importo_trasport'];
						unset($this->summaryOrderTrasportResults[$numResult]);
						break;
					}
				}
			}	
		}


		/*
		 * per l'UTENTE trattato ctrl se c'e' il costo aggiuntivo
		*/
		if(!empty($this->summaryOrderCostMoreResults)) {
			if(isset($this->summaryOrderCostMoreResults['SummaryOrderCostMore']) && $this->summaryOrderCostMoreResults['User']['id']==$user_id)
				$summary_order_cost_more_tot_importo_single_user = $this->summaryOrderCostMoreResults['SummaryOrderCostMore']['importo_cost_more'];
			else {			
				foreach($this->summaryOrderCostMoreResults as $numResult => $summaryOrderCostMoreResult) {
					$summary_order_cost_more_tot_importo_single_user = 0;
					if($summaryOrderCostMoreResult['SummaryOrderCostMore']['user_id']==$user_id) {
						$summary_order_cost_more_tot_importo_single_user = $summaryOrderCostMoreResult['SummaryOrderCostMore']['importo_cost_more'];
						unset($this->summaryOrderCostMoreResults[$numResult]);
						break;
					}
				}
			}		
		}

		
		/*
		 * per l'UTENTE trattato ctrl se c'e' lo sconto
		*/
		if(!empty($this->summaryOrderCostLessResults)) {
			if(isset($this->summaryOrderCostLessResults['SummaryOrderCostLess']) && $this->summaryOrderCostLessResults['User']['id']==$user_id)
				$summary_order_cost_less_tot_importo_single_user = $this->summaryOrderCostLessResults['SummaryOrderCostLess']['importo_cost_less'];
			else {				
				foreach($this->summaryOrderCostLessResults as $numResult => $summaryOrderCostLessResult) {
					if($summaryOrderCostLessResult['SummaryOrderCostLess']['user_id']==$user_id) {
						$summary_order_cost_less_tot_importo_single_user = $summaryOrderCostLessResult['SummaryOrderCostLess']['importo_cost_less'];
						unset($this->summaryOrderCostLessResults[$numResult]);
						break;
					}
				}
			}			
		}

		
		if($debug) {
			echo '<h3>_calcolaSubTotaleUser()</h3>';
			echo 'UTENTE '.$user_id.' -  sum(Cart.importo) '.$tot_importo_single_user;
			echo '<br />- Aggregati '.$summary_order_aggregate_tot_importo_single_user;
			echo '<br />- Trasporto '.$summary_order_trasport_tot_importo_single_user;
			echo '<br />- Costo aggiuntivo '.$summary_order_cost_more_tot_importo_single_user;
			echo '<br />- Sconto '.$summary_order_cost_less_tot_importo_single_user;
		}
		
		if(empty($tot_qta_single_user) && empty($tot_importo_single_user) && empty($summary_order_aggregate_tot_importo_single_user)) return;
			
		$importo_modificato = false;
		$tot_importo_single_user_completo = 0;
		
		if(empty($summary_order_aggregate_tot_importo_single_user)) {
			$tot_importo_single_user_completo = $tot_importo_single_user;
			$importo_modificato = false;
		}
		else {
			$tot_importo_single_user_completo = $summary_order_aggregate_tot_importo_single_user;
			$tot_importo_single_user = $summary_order_aggregate_tot_importo_single_user;
			$importo_modificato = true;
		}
		
		if(!empty($summary_order_trasport_tot_importo_single_user)) {
			$tot_importo_single_user_completo = ($tot_importo_single_user_completo + $summary_order_trasport_tot_importo_single_user); 
		}
		if(!empty($summary_order_cost_more_tot_importo_single_user)) {
			$tot_importo_single_user_completo = ($tot_importo_single_user_completo + $summary_order_cost_more_tot_importo_single_user); 
		}
		if(!empty($summary_order_cost_less_tot_importo_single_user)) {
			$tot_importo_single_user_completo = ($tot_importo_single_user_completo + $summary_order_cost_less_tot_importo_single_user); 
		}
		
		$this->exportRowsNum++;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['QTA'] = $tot_qta_single_user;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO'] = $tot_importo_single_user;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_'] = number_format($tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_E'] = number_format($tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['ISIMPORTOMOD'] = $importo_modificato;

		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_AGGREGATE'] = $summary_order_aggregate_tot_importo_single_user;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_AGGREGATE_'] = number_format($summary_order_aggregate_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_AGGREGATE_E'] = number_format($summary_order_aggregate_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_TRASPORTO'] = $summary_order_trasport_tot_importo_single_user;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_TRASPORTO_'] = number_format($summary_order_trasport_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_TRASPORTO_E'] = number_format($summary_order_trasport_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_COST_MORE'] = $summary_order_cost_more_tot_importo_single_user;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_COST_MORE_'] = number_format($summary_order_cost_more_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_COST_MORE_E'] = number_format($summary_order_cost_more_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_COST_LESS'] = $summary_order_cost_less_tot_importo_single_user;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_COST_LESS_'] = number_format($summary_order_cost_less_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_COST_LESS_E'] = number_format($summary_order_cost_less_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';

		/*
		 * IMPORTO_COMPLETO = SummaryOrder.importo + $trasport + $cost_more + (-1 * $cost_less)
		 */	
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_COMPLETO'] = $tot_importo_single_user_completo;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_COMPLETO_'] = number_format($tot_importo_single_user_completo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_COMPLETO_E'] = number_format($tot_importo_single_user_completo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						 
		if($debug) echo '<br />&nbsp;&nbsp;&nbsp;_calcolaSubTotaleUser()&nbsp;'.$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_COMPLETO'];
		
		return $tot_importo_single_user;
	}	
	
	/*
	 * per il Cassiere calcolo il totale di un utente rispetto ad un ordine
	 */
	private function _calcolaSubTotaleUserOrder($user_id, $tot_qta_single_user, $tot_importo_single_user, $summary_order_aggregate_tot_importo_single_user, $summary_order_trasport_tot_importo_single_user=0, $summary_order_cost_more_tot_importo_single_user=0, $summary_order_cost_less_tot_importo_single_user=0, $debug) {
		
		if($debug) {
			echo '<h3>_calcolaSubTotaleUserOrder()</h3>';
			echo 'UTENTE '.$user_id.' -  sum(Cart.importo) '.$tot_importo_single_user.' - SummaryOrder.importo (sum(Cart.importo) + trasporto + cost_more + (-1 * cost_less)) '.$summary_order_aggregate_tot_importo_single_user;
			echo '<br />- Trasporto '.$summary_order_trasport_tot_importo_single_user;
			echo '<br />- Costo aggiuntivo '.$summary_order_trasport_tot_importo_single_user;
			echo '<br />- Sconto '.$summary_order_cost_more_tot_importo_single_user;
		}
		
		if(empty($tot_qta_single_user) && empty($tot_importo_single_user) && empty($summary_order_aggregate_tot_importo_single_user)) return;
			
		if(empty($summary_order_aggregate_tot_importo_single_user)) {
			$tmp_importo_single_user = $tot_importo_single_user;
			$importo_modificato = false;
		}
		else
		if(($tot_importo_single_user + $summary_order_trasport_tot_importo_single_user + $summary_order_cost_more_tot_importo_single_user + ($summary_order_cost_more_tot_importo_single_user)).'' == ''.$summary_order_aggregate_tot_importo_single_user) {
			$tmp_importo_single_user = $tot_importo_single_user;
			$importo_modificato = false;
		}
		else {
			$tmp_importo_single_user = ($summary_order_aggregate_tot_importo_single_user - $summary_order_trasport_tot_importo_single_user - $summary_order_cost_more_tot_importo_single_user + (-1* $summary_order_cost_less_tot_importo_single_user));
			$importo_modificato = true;
		}
		
		$this->exportRowsNum++;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['QTA'] = $tot_qta_single_user;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO'] = $tmp_importo_single_user;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_'] = number_format($tmp_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_E'] = number_format($tmp_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['ISIMPORTOMOD'] = $importo_modificato;
		
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_TRASPORTO'] = $summary_order_trasport_tot_importo_single_user;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_TRASPORTO_'] = number_format($summary_order_trasport_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_TRASPORTO_E'] = number_format($summary_order_trasport_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_COST_MORE'] = $summary_order_cost_more_tot_importo_single_user;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_COST_MORE_'] = number_format($summary_order_cost_more_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_COST_MORE_E'] = number_format($summary_order_cost_more_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_COST_LESS'] = $summary_order_cost_less_tot_importo_single_user;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_COST_LESS_'] = number_format($summary_order_cost_less_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_COST_LESS_E'] = number_format($summary_order_cost_less_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		
		/*
		 * IMPORTO_COMPLETO = SummaryOrder.importo + $trasport + $cost_more + (-1 * $cost_less))
		 */			
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_COMPLETO'] = ($tmp_importo_single_user + $summary_order_trasport_tot_importo_single_user + $summary_order_cost_more_tot_importo_single_user + ($summary_order_cost_less_tot_importo_single_user));
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_COMPLETO_'] = number_format(($tmp_importo_single_user + $summary_order_trasport_tot_importo_single_user + $summary_order_cost_more_tot_importo_single_user + ($summary_order_cost_less_tot_importo_single_user)),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_COMPLETO_E'] = number_format(($tmp_importo_single_user + $summary_order_trasport_tot_importo_single_user + $summary_order_cost_more_tot_importo_single_user + ($summary_order_cost_less_tot_importo_single_user)),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		
		if($debug) echo '<br />&nbsp;&nbsp;&nbsp;_calcolaSubTotaleUserOrder()&nbsp;'.$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO'];
	}	
}