<?php
App::uses('AppModel', 'Model');

class ExportDoc extends AppModel {

	public $useTable = 'deliveries';
	public $actsAs = array('Data');
	public $virtualFields = array('luogoData' => "CONCAT_WS(' - ',ExportDoc.luogo,DATE_FORMAT(ExportDoc.data, '%W, %e %M %Y'))");
	
	public $exportRowsNum;
	public $exportRows;
	
	public $hasMany = array(
			'Order' => array(
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
			)
	);
	
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
     *   	- eventuali totali impostati dal referente (SummaryOrder) in Carts::managementCartsGroupByUsers o dal tesoriere
     *   			$resultsSummaryOrder = $SummaryOrder->select_to_order($this->user, $order_id) 
	 * 		- spese di trasporto  (SummaryOrderTrasport)
	 * 				$resultsSummaryOrderTrasport = $SummaryOrderTrasport->select_to_order($this->user, $order_id) 
	 * 		- costi aggiuntivi (SummaryOrderCostMore)
	 * 				$resultsSummaryOrderCostMore = $SummaryOrderCostMore->select_to_order($this->user, $order_id) 
	 * 		- sconti (SummaryOrderCostLess)
	 * 				$resultsSummaryOrderCostLess = $SummaryOrderCostLess->select_to_order($this->user, $order_id) 
	 */
	public function getCartCompliteOrder($order_id, $results, $resultsSummaryOrder=array(), $resultsSummaryOrderTrasport=array(), $resultsSummaryOrderCostMore=array(), $resultsSummaryOrderCostLess=array(), $debug = false) {

		/*
		 * creo una copia di $resultsSummaryOrder, da qui faccio unset degli utenti trattati
		 * perche' puo' capitare che in $resultsSummaryOrder ho uno user che non c'e' in $results
		 */ 
		$resultsSummaryOrder2 = $resultsSummaryOrder; // se l'ordine e' ancora aperto e' vuoto

		foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {
			
		    /*
			 * lo commento se no mi escludo gli eventuali dati inseriti ex-novo da SummaryOrder
			 * if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0)
			 */
			foreach($result['Delivery']['Order'] as $numOrder => $order) {
			
				if($order['Order']['id']==$order_id) {
					
					if($debug) {
						echo '<h1>ExportDoc::getCartCompliteOrder() - ordine '.$order['Order']['id'].'</h1>';
						
						echo '<h2>Trasporto</h2>';
						if($order['Order']['hasTrasport']=='Y')	{
							echo 'Ordine con TRASPORTO - importo '.$order['Order']['trasport'];
							if(empty($resultsSummaryOrderTrasport))
								echo ' => importo del trasporto non suddiviso per gli utenti (resultsSummaryOrderTrasport NON valorizzato)';
							else 
								echo ' => importo del trasporto suddiviso per gli utenti (resultsSummaryOrderTrasport valorizzato)';
						}
						else 	
						if($order['Order']['hasTrasport']=='N' && $order['Order']['trasport']>0)	echo '<br />Ordine senza TRASPORTO, importo del trasporto '.$order['Order']['trasport'].' => dato INCONGRUO';
						else 
							echo 'Ordine senza TRASPORTO';
						
						echo '<h2>Costo aggiuntivi</h2>';
						if($order['Order']['hasCostMore']=='Y')	{
							echo 'Ordine con Costo Aggiuntivo - importo '.$order['Order']['cost_more'];
							if(empty($resultsSummaryOrderCostMore))
								echo ' => importo del costo aggiuntivo non suddiviso per gli utenti (resultsSummaryOrderCostMore NON valorizzato)';
							else
								echo ' => importo del costo aggiuntivo suddiviso per gli utenti (resultsSummaryOrderCostMore valorizzato)';
						}
						else
						if($order['Order']['hasCostMore']=='N' && $order['Order']['cost_more']>0)	echo '<br />Ordine senza COSTO AGGIUNTIVO, importo del costo aggiuntivo '.$order['Order']['cost_more'].' => dato INCONGRUO';
						else
							echo 'Ordine senza COSTO AGGIUNTIVO';
						
						echo '<h2>Sconto</h2>';
						if($order['Order']['hasCostLess']=='Y')	{
							echo 'Ordine con Sconto - importo '.$order['Order']['cost_less'];
							if(empty($resultsSummaryOrderCostLess))
								echo ' => importo dello sconto non suddiviso per gli utenti (resultsSummaryOrderCostLess NON valorizzato)';
							else
								echo ' => importo dello sconto aggiuntivo suddiviso per gli utenti (resultsSummaryOrderCostLess valorizzato)';
						}
						else
						if($order['Order']['hasCostLess']=='N' && $order['Order']['cost_less']>0)	echo '<br />Ordine senza SCONTO, importo dello sconto '.$order['Order']['cost_less'].' => dato INCONGRUO';
						else
							echo 'Ordine senza SCONTO';
												
						echo '<h2>Dati del carrello aggregati per ogni utente (sum(Cart.importo + trasporto + costo aggiuntivo - sconto))</h2>';
						if(empty($resultsSummaryOrder)) 
							echo 'nessuna aggregazione (resultsSummaryOrder NON valorizzato)';
						else
							echo ' effettuata aggregazione (resultsSummaryOrder valorizzato)';
					}
					
					
					$tot_qta_single_user = 0;
					$tot_importo_single_user = 0;
					$tot_qta = 0;
					$tot_importo = 0;
					$summary_order_tot_importo_single_user = 0;
					$summary_order_trasport_tot_importo_single_user = 0;
					$summary_order_cost_more_tot_importo_single_user = 0;
					$summary_order_cost_less_tot_importo_single_user = 0;
					$summary_order_tot_importo = 0;
					$user_id_old = 0;
					
					$this->exportRowsNum = -1;
					$this->exportRows = array();
					if(isset($order['ArticlesOrder']))
					foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {
				
						/*
						 * per l'UTENTE trattato calcolo TOTALI
						 */
						if($user_id_old != $order['User'][$numArticlesOrder]['id']) {
			
							if($numArticlesOrder>0) {

								/*
								 * per l'UTENTE trattato ctrl se il totale e' stato modificato in Carts::managementCartsGroupByUsers
								*/
								foreach($resultsSummaryOrder as $numSummaryOrder => $resultSummaryOrder) {
									$summary_order_tot_importo_single_user = 0;
									if($resultSummaryOrder['SummaryOrder']['user_id']==$user_id_old) {
										$summary_order_tot_importo_single_user = $resultSummaryOrder['SummaryOrder']['importo'];
										$summary_order_tot_importo += $resultSummaryOrder['SummaryOrder']['importo'];
										unset($resultsSummaryOrder2[$numSummaryOrder]);
										break;
									}
								}
								
								/*
								 * per l'UTENTE trattato ctrl se c'e' la spesa di trasporto
								*/
								foreach($resultsSummaryOrderTrasport as $numSummaryOrderTrasport => $resultSummaryOrderTrasport) {
									$summary_order_trasport_tot_importo_single_user = 0;
									if($resultSummaryOrderTrasport['SummaryOrderTrasport']['user_id']==$user_id_old) {
										$summary_order_trasport_tot_importo_single_user = $resultSummaryOrderTrasport['SummaryOrderTrasport']['importo_trasport'];
										break;
									}
								}

								/*
								 * per l'UTENTE trattato ctrl se c'e' il costo aggiuntivo
								*/
								foreach($resultsSummaryOrderCostMore as $numSummaryOrderCostMore => $resultSummaryOrderCostMore) {
									$summary_order_cost_more_tot_importo_single_user = 0;
									if($resultSummaryOrderCostMore['SummaryOrderCostMore']['user_id']==$user_id_old) {
										$summary_order_cost_more_tot_importo_single_user = $resultSummaryOrderCostMore['SummaryOrderCostMore']['importo_cost_more'];
										break;
									}
								}
								
								/*
								 * per l'UTENTE trattato ctrl se c'e' lo sconto
								*/
								foreach($resultsSummaryOrderCostLess as $numSummaryOrderCostLess => $resultSummaryOrderCostLess) {
									$summary_order_cost_less_tot_importo_single_user = 0;
									if($resultSummaryOrderCostLess['SummaryOrderCostLess']['user_id']==$user_id_old) {
										$summary_order_cost_less_tot_importo_single_user = $resultSummaryOrderCostLess['SummaryOrderCostLess']['importo_cost_less'];
										break;
									}
								}
								
								/*
								 * creo il sub totale per ogni utente
								 */
								$this->__calcolaSubTotaleUser($user_id_old, $tot_qta_single_user, $tot_importo_single_user, $summary_order_tot_importo_single_user, $summary_order_trasport_tot_importo_single_user, $summary_order_cost_more_tot_importo_single_user, $summary_order_cost_less_tot_importo_single_user, $debug);
									
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
							if(isset($order['User'][$numArticlesOrder]['Profile']['address'])) $user_address = $order['User'][$numArticlesOrder]['Profile']['address'];
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
						
						$importo = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						
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
						$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['IMPORTO'] = $importo.'&nbsp;&euro;';
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
						else
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['IMPORTOUSER'] = number_format(($order['Cart'][$numArticlesOrder]['qta'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			
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
						else
							$this->exportRows[$this->exportRowsNum][$order['User'][$numArticlesOrder]['id']]['TRDATA']['IMPORTOFORZATO'] = number_format($order['Cart'][$numArticlesOrder]['importo_forzato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			
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
					 * ctrl se il totale e' stato modificato in Carts::managementCartsGroupByUsers
					 * 		se e' stato aggiungo un utente ex-novo con unset()
					*/
					foreach($resultsSummaryOrder as $numSummaryOrder => $resultSummaryOrder) {
						if($resultSummaryOrder['SummaryOrder']['user_id']==$user_id_old) {
							$summary_order_tot_importo_single_user = $resultSummaryOrder['SummaryOrder']['importo'];
							$summary_order_tot_importo += $resultSummaryOrder['SummaryOrder']['importo'];
							unset($resultsSummaryOrder2[$numSummaryOrder]);
							break;
						}
					}

					/*
					 * ctrl se c'e' la spesa di trasporto
					*/
					foreach($resultsSummaryOrderTrasport as $numSummaryOrderTrasport => $resultSummaryOrderTrasport) {
						if($resultSummaryOrderTrasport['SummaryOrderTrasport']['user_id']==$user_id_old) {
							$summary_order_trasport_tot_importo_single_user = $resultSummaryOrderTrasport['SummaryOrderTrasport']['importo_trasport'];
							break;
						}
					}

					/*
					 * ctrl se c'e' il costo aggiuntivo
					*/
					foreach($resultsSummaryOrderCostMore as $numSummaryOrderCostMore => $resultSummaryOrderCostMore) {
						if($resultSummaryOrderCostMore['SummaryOrderCostMore']['user_id']==$user_id_old) {
							$summary_order_cost_more_tot_importo_single_user = $resultSummaryOrderCostMore['SummaryOrderCostMore']['importo_cost_more'];
							break;
						}
					}
					
					/*
					 * ctrl se c'e' lo sconto
					*/
					foreach($resultsSummaryOrderCostLess as $numSummaryOrderCostLess => $resultSummaryOrderCostLess) {
						if($resultSummaryOrderCostLess['SummaryOrderCostLess']['user_id']==$user_id_old) {
							$summary_order_cost_less_tot_importo_single_user = $resultSummaryOrderCostLess['SummaryOrderCostLess']['importo_cost_less'];
							break;
						}
					}
					
					/*
					 * per l'ultimo utente riporto i sub-totali
					*/
					$this->__calcolaSubTotaleUser($order['User'][$numArticlesOrder]['id'], $tot_qta_single_user, $tot_importo_single_user, $summary_order_tot_importo_single_user, $summary_order_trasport_tot_importo_single_user, $summary_order_cost_more_tot_importo_single_user, $summary_order_cost_less_tot_importo_single_user, $debug);
					
					/*
					 * TOTALI
					 * ctrl se in Carts::managementCartsGroupByUsers 
					*/	
					foreach($resultsSummaryOrder2 as $numSummaryOrder => $resultSummaryOrder) {
				
						if(isset($resultSummaryOrder['User']['Profile']['phone'])) $user_phone = $resultSummaryOrder['User']['Profile']['phone'];
						else $user_phone = '';
						if(isset($resultSummaryOrder['User']['email'])) $user_email = $resultSummaryOrder['User']['email'];
						else $user_email = '';
						if(isset($resultSummaryOrder['User']['Profile']['address'])) $user_address = $resultSummaryOrder['User']['Profile']['address'];
						else $user_address = '';
						
						$this->exportRowsNum++;
						$this->exportRows[$this->exportRowsNum][$resultSummaryOrder['User'][$numArticlesOrder]['id']]['TRGROUP']['LABEL'] = "Utente: ".$resultSummaryOrder['User']['name'];
						$this->exportRows[$this->exportRowsNum][$resultSummaryOrder['User'][$numArticlesOrder]['id']]['TRGROUP']['LABEL_ID'] = $resultSummaryOrder['User']['id'];
						$this->exportRows[$this->exportRowsNum][$resultSummaryOrder['User'][$numArticlesOrder]['id']]['TRGROUP']['LABEL_PHONE'] = $user_phone;
						$this->exportRows[$this->exportRowsNum][$resultSummaryOrder['User'][$numArticlesOrder]['id']]['TRGROUP']['LABEL_EMAIL'] = $user_email;
						$this->exportRows[$this->exportRowsNum][$resultSummaryOrder['User'][$numArticlesOrder]['id']]['TRGROUP']['LABEL_ADDRESS'] = $user_address;
						
						$this->exportRowsNum++;
						$this->exportRows[$this->exportRowsNum][$resultSummaryOrder['User'][$numArticlesOrder]['id']]['TRSUBTOT']['QTA'] = 0;
						$this->exportRows[$this->exportRowsNum][$resultSummaryOrder['User'][$numArticlesOrder]['id']]['TRSUBTOT']['IMPORTO'] = number_format($resultSummaryOrder['SummaryOrder']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						$this->exportRows[$this->exportRowsNum][$resultSummaryOrder['User'][$numArticlesOrder]['id']]['TRSUBTOT']['IMPORTO_DOUBLE'] = $resultSummaryOrder['SummaryOrder']['importo']; // importo non convertito per eventali somme in View 
						$this->exportRows[$this->exportRowsNum][$resultSummaryOrder['User'][$numArticlesOrder]['id']]['TRSUBTOT']['ISIMPORTOMOD'] = true;
						
						/*
						 *  trasporto
						 */
						foreach($resultsSummaryOrderTrasport as $numSummaryOrderTrasport => $resultSummaryOrderTrasport) {
							if($resultSummaryOrderTrasport['SummaryOrderTrasport']['user_id']==$resultSummaryOrder['User']['id']) {
								$summary_order_trasport_tot_importo_single_user = $resultSummaryOrderTrasport['SummaryOrderTrasport']['importo_trasport'];
								break;
							}
						}						
						$this->exportRows[$this->exportRowsNum][$resultSummaryOrder['User'][$numArticlesOrder]['id']]['TRSUBTOT']['IMPORTO_TRASPORTO'] = number_format($summary_order_trasport_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						
						/*
						 *  costo aggiuntivo
						 */
						foreach($resultsSummaryOrderCostMore as $numSummaryOrderCostMore => $resultSummaryOrderCostMore) {
							if($resultSummaryOrderCostMore['SummaryOrderCostMore']['user_id']==$resultSummaryOrder['User']['id']) {
								$summary_order_cost_more_tot_importo_single_user = $resultSummaryOrderCostMore['SummaryOrderCostMore']['importo_cost_more'];
								break;
							}
						}
						$this->exportRows[$this->exportRowsNum][$resultSummaryOrder['User'][$numArticlesOrder]['id']]['TRSUBTOT']['IMPORTO_COST_MORE'] = number_format($summary_order_cost_more_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						
						/*
						 *  sconto
						 */
						foreach($resultsSummaryOrderCostLess as $numSummaryOrderCostLess => $resultSummaryOrderCostLess) {
							if($resultSummaryOrderCostLess['SummaryOrderCostLess']['user_id']==$resultSummaryOrder['User']['id']) {
								$summary_order_cost_less_tot_importo_single_user = $resultSummaryOrderCostLess['SummaryOrderCostLess']['importo_cost_less'];
								break;
							}
						}
						$this->exportRows[$this->exportRowsNum][$resultSummaryOrder['User'][$numArticlesOrder]['id']]['TRSUBTOT']['IMPORTO_COST_LESS'] = number_format($summary_order_cost_less_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						
						/*
						 * IMPORTO_COMPLETO = SummaryOrder.importo + $trasport + $cost_more + (-1 * $cost_less)
						 */						
						$this->exportRows[$this->exportRowsNum][$resultSummaryOrder['User'][$numArticlesOrder]['id']]['TRSUBTOT']['IMPORTO_COMPLETO'] = number_format(($resultSummaryOrder['SummaryOrder']['importo'] + $summary_order_trasport_tot_importo_single_user + $summary_order_cost_more_tot_importo_single_user + ($summary_order_cost_less_tot_importo_single_user)),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						$this->exportRows[$this->exportRowsNum][$resultSummaryOrder['User'][$numArticlesOrder]['id']]['TRSUBTOT']['IMPORTO_COMPLETO_DOUBLE'] = ($resultSummaryOrder['SummaryOrder']['importo'] + $summary_order_trasport_tot_importo_single_user + $summary_order_cost_more_tot_importo_single_user + ($summary_order_cost_less_tot_importo_single_user)); // importo non convertito per eventali somme in View
						
						$summary_order_tot_importo += $resultSummaryOrder['SummaryOrder']['importo'];
					}			

			
					/*
					 * calcolo il totale 
					 */
					if(empty($resultsSummaryOrder) || // se l'ordine e' ancora aperto e' vuoto
					   $tot_importo==$summary_order_tot_importo) {
						$tmp_importo = $tot_importo;
						$importo_modificato = false;
						$tmp_importo_completo = ($tot_importo + $order['Order']['trasport'] + $order['Order']['cost_more'] + (-1 * $order['Order']['cost_less']));							
					}
					else {
						$tmp_importo = ($summary_order_tot_importo - $order['Order']['trasport'] - $order['Order']['cost_more'] + (-1 * $order['Order']['cost_less']));
						$importo_modificato = true;
						$tmp_importo_completo = $summary_order_tot_importo;
					}
					
					if(!empty($tot_qta) || !empty($tmp_importo)) {					
						$this->exportRowsNum++;
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['QTA'] = $tot_qta;
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO'] = number_format($tmp_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_DOUBLE'] = $tmp_importo; // importo non convertito per eventali somme in View					
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['ISIMPORTOMOD'] = $importo_modificato;
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_TRASPORTO'] = number_format($order['Order']['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_COST_MORE'] = number_format($order['Order']['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_COST_LESS'] = number_format($order['Order']['cost_less'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_COMPLETO'] = number_format($tmp_importo_completo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
						$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO_COMPLETO_DOUBLE'] = $tmp_importo_completo; // importo non convertito per eventali somme in View
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
		$this->exportRows = array();
		
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
						$this->__calcolaSubTotaleUserOrder($user_id_old, $tot_qta_user_order, $tot_importo_user_order, 0, 0, 0, 0, $debug);
				
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
						$this->__calcolaSubTotaleUser($user_id_old, $tot_qta_user_delivery, $tot_importo_user_delivery, 0, 0, 0, 0, $debug);
								
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
				
				$importo = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				
				/*
				 * valori di ogni singolo acquisto con le possibili modifiche del referente
				*/
				$this->exportRowsNum++;
				if($user['Cart']['deleteToReferent']=='Y')
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['DELETE_TO_REFERENT'] = 'Y';
				else
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['DELETE_TO_REFERENT'] = 'N';
				$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['NUM'] = ($numResult+1);
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
				$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['IMPORTO'] = $importo.'&nbsp;&euro;';
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
				else
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['IMPORTOUSER'] = number_format(($user['Cart']['qta'] * $user['ArticlesOrder']['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
					
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
				else
					$this->exportRows[$this->exportRowsNum][$user['User']['id']]['TRDATA']['IMPORTOFORZATO'] = number_format($user['Cart']['importo_forzato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
					
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
		$this->__calcolaSubTotaleUserOrder($user['User']['id'], $tot_qta_user_order, $tot_importo_user_order, 0, 0, 0, 0, $debug);
		
		$this->__calcolaSubTotaleUser($user['User']['id'], $tot_qta_user_delivery, $tot_importo_user_delivery, 0, 0, 0, 0, $debug);
			
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
		
	private function __calcolaSubTotaleUser($user_id, $tot_qta_single_user, $tot_importo_single_user, $summary_order_tot_importo_single_user, $summary_order_trasport_tot_importo_single_user=0,  $summary_order_cost_more_tot_importo_single_user=0, $summary_order_cost_less_tot_importo_single_user=0, $debug) {
		/*
		if($debug) {
			echo '<h3>__calcolaSubTotaleUser()</h3>';
			echo 'UTENTE '.$user_id.' -  sum(Cart.importo) '.$tot_importo_single_user.' - SummaryOrder.importo (sum(Cart.importo) + trasporto + cost_more + (-1 * cost_less)) '.$summary_order_tot_importo_single_user;
			echo '<br />- Trasporto '.$summary_order_trasport_tot_importo_single_user;
			echo '<br />- Costo aggiuntivo '.$summary_order_cost_more_tot_importo_single_user;
			echo '<br />- Sconto '.$summary_order_cost_less_tot_importo_single_user;
		}
		*/
		if(empty($tot_qta_single_user) && empty($tot_importo_single_user) && empty($summary_order_tot_importo_single_user)) return;
			
		if(empty($summary_order_tot_importo_single_user)) {
			$tmp_importo_single_user = $tot_importo_single_user;
			$importo_modificato = false;
		}
		else
		if(($tot_importo_single_user + $summary_order_trasport_tot_importo_single_user + $summary_order_cost_more_tot_importo_single_user + ($summary_order_cost_more_tot_importo_single_user)).'' == ''.$summary_order_tot_importo_single_user) {
			$tmp_importo_single_user = $tot_importo_single_user;
			$importo_modificato = false;
		}
		else {
			$tmp_importo_single_user = ($summary_order_tot_importo_single_user - $summary_order_trasport_tot_importo_single_user - $summary_order_cost_more_tot_importo_single_user + (-1* $summary_order_cost_less_tot_importo_single_user));
			$importo_modificato = true;
		}
		
		$this->exportRowsNum++;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['QTA'] = $tot_qta_single_user;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO'] = number_format($tmp_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_DOUBLE'] = $tmp_importo_single_user;  // importo non convertito per eventali somme in View	
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['ISIMPORTOMOD'] = $importo_modificato;

		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_TRASPORTO'] = number_format($summary_order_trasport_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_COST_MORE'] = number_format($summary_order_cost_more_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_COST_LESS'] = number_format($summary_order_cost_less_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';

		/*
		 * IMPORTO_COMPLETO = SummaryOrder.importo + $trasport + $cost_more + (-1 * $cost_less)
		 */	
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_COMPLETO'] = number_format(($tmp_importo_single_user + $summary_order_trasport_tot_importo_single_user + $summary_order_cost_more_tot_importo_single_user + ($summary_order_cost_less_tot_importo_single_user)),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO_COMPLETO_DOUBLE'] = ($tmp_importo_single_user + $summary_order_trasport_tot_importo_single_user + $summary_order_cost_more_tot_importo_single_user + ($summary_order_cost_less_tot_importo_single_user)); // importo non convertito per eventali somme in View
						 
		if($debug) echo '<br />&nbsp;&nbsp;&nbsp;__calcolaSubTotaleUser()&nbsp;'.$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO'];
	}	
	
	/*
	 * per il Cassiere calcolo il totale di un utente rispetto ad un ordine
	 */
	private function __calcolaSubTotaleUserOrder($user_id, $tot_qta_single_user, $tot_importo_single_user, $summary_order_tot_importo_single_user, $summary_order_trasport_tot_importo_single_user=0, $summary_order_cost_more_tot_importo_single_user=0, $summary_order_cost_less_tot_importo_single_user=0, $debug) {
		
		if($debug) {
			echo '<h3>__calcolaSubTotaleUserOrder()</h3>';
			echo 'UTENTE '.$user_id.' -  sum(Cart.importo) '.$tot_importo_single_user.' - SummaryOrder.importo (sum(Cart.importo) + trasporto + cost_more + (-1 * cost_less)) '.$summary_order_tot_importo_single_user;
			echo '<br />- Trasporto '.$summary_order_trasport_tot_importo_single_user;
			echo '<br />- Costo aggiuntivo '.$summary_order_trasport_tot_importo_single_user;
			echo '<br />- Sconto '.$summary_order_cost_more_tot_importo_single_user;
		}
		
		if(empty($tot_qta_single_user) && empty($tot_importo_single_user) && empty($summary_order_tot_importo_single_user)) return;
			
		if(empty($summary_order_tot_importo_single_user)) {
			$tmp_importo_single_user = $tot_importo_single_user;
			$importo_modificato = false;
		}
		else
		if(($tot_importo_single_user + $summary_order_trasport_tot_importo_single_user + $summary_order_cost_more_tot_importo_single_user + ($summary_order_cost_more_tot_importo_single_user)).'' == ''.$summary_order_tot_importo_single_user) {
			$tmp_importo_single_user = $tot_importo_single_user;
			$importo_modificato = false;
		}
		else {
			$tmp_importo_single_user = ($summary_order_tot_importo_single_user - $summary_order_trasport_tot_importo_single_user - $summary_order_cost_more_tot_importo_single_user + (-1* $summary_order_cost_less_tot_importo_single_user));
			$importo_modificato = true;
		}
		
		$this->exportRowsNum++;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['QTA'] = $tot_qta_single_user;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO'] = number_format($tmp_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_DOUBLE'] = $tmp_importo_single_user; // importo non convertito per eventali somme in View
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['ISIMPORTOMOD'] = $importo_modificato;
		
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_TRASPORTO'] = number_format($summary_order_trasport_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_COST_MORE'] = number_format($summary_order_cost_more_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_COST_LESS'] = number_format($summary_order_cost_less_tot_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		
		/*
		 * IMPORTO_COMPLETO = SummaryOrder.importo + $trasport + $cost_more + (-1 * $cost_less))
		 */			
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_COMPLETO'] = number_format(($tmp_importo_single_user + $summary_order_trasport_tot_importo_single_user + $summary_order_cost_more_tot_importo_single_user + ($summary_order_cost_less_tot_importo_single_user)),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOTORDER']['IMPORTO_COMPLETO_DOUBLE'] = ($tmp_importo_single_user + $summary_order_trasport_tot_importo_single_user + $summary_order_cost_more_tot_importo_single_user + ($summary_order_cost_less_tot_importo_single_user)); // importo non convertito per eventali somme in View
		
		if($debug) echo '<br />&nbsp;&nbsp;&nbsp;__calcolaSubTotaleUserOrder()&nbsp;'.$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO'];
	}	
}