<?php
App::uses('AppModel', 'Model');

class ProdExportDoc extends AppModel {

	public $useTable = 'prod_deliveries';

	public $exportRowsNum;
	public $exportRows;
	
	public $belongsTo = array(
		'SuppliersOrganization' => array(
			'className' => 'SuppliersOrganization',
			'foreignKey' => 'supplier_organization_id',
			'conditions' => 'SuppliersOrganization.organization_id = SuppliersOrganization.organization_id',
			'fields' => '',
			'order' => ''
		),
		'ProdGroup' => array(
			'className' => 'ProdGroup',
			'foreignKey' => 'prod_group_id',
			'conditions' => 'ProdGroup.organization_id = ProdDelivery.organization_id',
			'fields' => '',
			'order' => ''
		),
		'ProdDeliveriesState' => array(
				'className' => 'ProdDeliveriesState',
				'foreignKey' => 'prod_delivery_state_id',
				'conditions' => '',
				'fields' => '',
				'order' => ''
		)
	);
	
	/*
	 * crea un oggetto con tutte le modifiche di un acquisto (qta e importo dell'utente o del produttore)
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
     *               [QTAUSER] => -
     *               [IMPORTOUSER] => -
     *               [QTAREF] => 5
     *               [IMPORTOREF] => 90,00 €
     *               [IMPORTOFORZATO] => -
     *           )
	 */
	public function getCartComplite($prod_delivery_id, $results, $debug = false) {

		$tot_qta_single_user = 0;
		$tot_importo_single_user = 0;
		$tot_qta = 0;
		$tot_importo = 0;
		$summary_order_tot_importo = 0;
		$user_id_old = 0;
			
		$this->exportRowsNum = -1;
		$this->exportRows = array();
		
		foreach($results as $numResult => $result) {
			
			if($debug) {
				echo '<h3>ProdExportDoc::getCartCompile() - consegna '.$result['ProdDelivery']['id'].' '.$result['ProdDelivery']['name'].'</h3>';
				echo 'user  '.$result['User']['name'].' (id '.$result['User']['id'].' id_old '.$user_id_old.') '.$result['Article']['name'];
			}
			
			/*
			 * per l'UTENTE trattato calcolo TOTALI
			 */
			if($user_id_old != $result['User']['id']) {

				if($user_id_old > 0) {

					/*
					 * creo il sub totale per ogni utente
					 */
					$this->__calcolaSubTotaleUser($user_id_old, $tot_qta_single_user, $tot_importo_single_user, $debug);
				
					$tot_qta_single_user = 0;
					$tot_importo_single_user = 0;	
				}
					
					
				/*
				 * inizia un nuovo UTENTE
				 */
				if(isset($result['User']['Profile']['phone'])) $user_phone = $result['User']['Profile']['phone'];
				else $user_phone = '';
				if(isset($result['User']['email'])) $user_email = $result['User']['email'];
				else $user_email = '';
				if(isset($result['User']['Profile']['address'])) $user_address = $result['User']['Profile']['address'];
				else $user_address = '';
				
				$this->exportRowsNum++;
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRGROUP']['LABEL'] = "Utente: ".$result['User']['name'];
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRGROUP']['LABEL_ID'] = $result['User']['id'];
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRGROUP']['LABEL_PHONE'] = $user_phone;
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRGROUP']['LABEL_EMAIL'] = $user_email;
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRGROUP']['LABEL_ADDRESS'] = $user_address;
			} // end if($user_id_old != $result['User']['id'])
			
	
	
			/*
			 * gestione QTA e IMPORTI
			* */
			if($result['ProdCart']['qta_forzato']>0) 
				$qta = $result['ProdCart']['qta_forzato'];
			else 
				$qta = $result['ProdCart']['qta'];

			if($result['ProdCart']['importo_forzato']==0) {
				if($result['ProdCart']['qta_forzato']>0) 
					$importo = ($result['ProdCart']['qta_forzato'] * $result['ProdDeliveriesArticle']['prezzo']);
				else 
					$importo = ($result['ProdCart']['qta'] * $result['ProdDeliveriesArticle']['prezzo']);
			}
			else 
				$importo = $result['ProdCart']['importo_forzato'];

			if($result['ProdCart']['deleteToReferent']=='N') {
				$tot_qta_single_user += $qta;
				$tot_importo_single_user += $importo;
				$tot_qta += $qta;
				$tot_importo += $importo;
			}
						
			$importo = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						
			/*
			 * valori di ogni singolo acquisto con le possibili modifiche del produttore
			 */
			$this->exportRowsNum++;
			
			if($result['ProdCart']['deleteToReferent']=='Y')
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['DELETE_TO_REFERENT'] = 'Y';
			else
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['DELETE_TO_REFERENT'] = 'N';
			$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['NUM'] = ($numResult+1);
			$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['NAME'] = $result['Article']['name'];
			$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['PREZZO'] = $result['ProdDeliveriesArticle']['prezzo'];
			$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['PREZZO_'] = $result['ProdDeliveriesArticle']['prezzo_'];
			$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['PREZZO_E'] = $result['ProdDeliveriesArticle']['prezzo_e'];
			$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['ARTICLEQTA'] = $result['Article']['qta'];
			$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['UM'] = $result['Article']['um'];
			$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['UMRIF'] = $result['Article']['um_riferimento'];
			$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['PREZZO_UMRIF'] = number_format($result['ProdDeliveriesArticle']['prezzo'] / $result['Article']['qta'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';

			$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['QTA'] = $qta;
			$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['IMPORTO'] = $importo.'&nbsp;&euro;';
			
			/*
			 * qta e importo dell'utente
			*/
			if($result['ProdCart']['qta']==0)
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['QTAUSER'] = '-';
			else
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['QTAUSER'] = $result['ProdCart']['qta'];

			if($result['ProdCart']['qta']==0)
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['IMPORTOUSER'] = '-';
			else
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['IMPORTOUSER'] = number_format(($result['ProdCart']['qta'] * $result['ProdDeliveriesArticle']['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';

			/*
			 * qta e importo del produttore
			*/
			if($result['ProdCart']['qta_forzato']==0)
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['QTAPROD'] = '-';
			else
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['QTAPROD'] = $result['ProdCart']['qta_forzato'];
			if($result['ProdCart']['qta_forzato']==0)
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['IMPORTOPROD'] = '-';
			else
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['IMPORTOPROD'] = number_format(($result['ProdCart']['qta_forzato'] * $result['ProdDeliveriesArticle']['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';

			/*
			 * importo forzato
			*/
			if($result['ProdCart']['importo_forzato']==0)
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['IMPORTOFORZATO'] = '-';
			else
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATA']['IMPORTOFORZATO'] = number_format($result['ProdCart']['importo_forzato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';

			
			/*
			 * nota
			 * */
			if(!empty($result['ProdCart']['nota'])) {
				$this->exportRowsNum++;
				$this->exportRows[$this->exportRowsNum][$result['User']['id']]['TRDATABIS']['NOTA'] = $result['ProdCart']['nota'];
			}
			
			$user_id_old = $result['User']['id'];
			
			unset($results[$numResult]);
			/*unset($results[$numResult]['ProdDelivery']);
			unset($results[$numResult]['ProdDeliveriesArticle']);
			unset($results[$numResult]['ProdCart']);
			unset($results[$numResult]['Article']);
			unset($results[$numResult]['User']);*/
			
		} // end foreach($results as $numResult => $result)					
					
		/*
		 * per l'ultimo utente riporto i sub-totali
		*/
		$this->__calcolaSubTotaleUser($result['User']['id'], $tot_qta_single_user, $tot_importo_single_user, $debug);
		
		if(!empty($tot_qta) || !empty($tot_importo)) {					
			$this->exportRowsNum++;
			$this->exportRows[$this->exportRowsNum][0]['TRTOT']['QTA'] = $tot_qta;
			$this->exportRows[$this->exportRowsNum][0]['TRTOT']['IMPORTO'] = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		}
		
		
		if($debug) {
			echo "<pre>";
			print_r($this->exportRows);
			echo "</pre>";
		}
		
		return $this->exportRows;
	}
	
	private function __calcolaSubTotaleUser($user_id, $tot_qta_single_user, $tot_importo_single_user, $debug) {
		
		if($debug) {
			echo '<h2>__calcolaSubTotaleUser()</h3>';
			echo 'UTENTE '.$user_id.' -  sum(ProdCart.importo) '.$tot_importo_single_user;
		}
		if(empty($tot_qta_single_user) && empty($tot_importo_single_user)) return;
			
		$tmp_importo_single_user = $tot_importo_single_user;
		
		$this->exportRowsNum++;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['QTA'] = $tot_qta_single_user;
		$this->exportRows[$this->exportRowsNum][$user_id]['TRSUBTOT']['IMPORTO'] = number_format($tmp_importo_single_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
	}	
}