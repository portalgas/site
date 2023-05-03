<?php
App::uses('AppModel', 'Model');

class Movement extends AppModel {

	public $tablePrefix = false;

	var $name = 'Movement';
	var $displayField = 'name';
	
	private $_movement_type_invoice = 5;    // INVOICE Pagamento fattura a fornitore
	private $_movement_type_userimport = 8; // USERSIMPORT Saldo movimento di cassa

	/*
	 * creo un movimento di cassa al pagamento di una fattura
	 * verifici se per l'anno corrente esiste gia'
	 * quando l'ordine andrà in statiche cambio l'associazione dell order_id
	 * 
	 * data
	 * 	tesoriere_importo_pay
	 * 	tesoriere_data_pay_db
	 *  tesoriere_stato_pay
	 */
	public function insert($user, $organization_id, $order_id, $data) {

		isset($data['tesoriere_stato_pay']) ? $tesoriere_stato_pay = $data['tesoriere_stato_pay']: $tesoriere_stato_pay = 'N';
		if($tesoriere_stato_pay=='N')
			return;

		/*
		* dati ordine
		*/
		App::import('Model', 'Order');
		$Order = new Order;		

		$options = [];
		$options['conditions'] = ['Order.organization_id' => $organization_id, 
									'Order.id' => $order_id];
		$options['recursive'] = 0;
		$order = $Order->find('first', $options);

		isset($data['tesoriere_importo_pay']) ? $tesoriere_importo_pay = $data['tesoriere_importo_pay']: $tesoriere_importo_pay = 0;
		isset($data['tesoriere_data_pay_db']) ? $tesoriere_data_pay_db = $data['tesoriere_data_pay_db'] : $tesoriere_data_pay_db = date('Y-m-d');
		isset($data['tesoriere_data_pay_db']) ? $year = substr($data['tesoriere_data_pay_db'], 0, 4) : $year = date('Y');

		if($year=='1970') $year = date('Y');
		if($tesoriere_data_pay_db==Configure::read('DB.field.date.empty')) $tesoriere_data_pay_db = date('Y-m-d');
		if($tesoriere_importo_pay==0) $tesoriere_importo_pay = $order['Order']['tot_importo'];;
		
		/* 
		 * controllo se esiste gia' un pagamento sull'ordine
		 */ 
		$options = [];
		$options['conditions'] = ['organization_id' => $organization_id,
								'order_id' => $order_id,
								'movement_type_id' => $this->_movement_type_invoice];
		$movement = $this->find('first', $options);
		// debug($movement);

		$datas = [];
		$datas['organization_id'] = $organization_id;
		$datas['movement_type_id'] = 5;
		$datas['order_id'] = $order_id;
		$datas['year'] = $year;
		// importo negativo perche' pagamento di una fattura
		$datas['importo'] = (-1) * $this->importoToDatabase($tesoriere_importo_pay);
		$datas['date'] = $tesoriere_data_pay_db;
		$datas['payment_type'] = 'ALTRO';
		$datas['is_active'] = 1;
		$datas['is_system'] = 0;
		if(!empty($movement)) {
			$datas['id'] = $movement['Movement']['id']; 
		}
		else {
			$datas['name'] = $order['SuppliersOrganization']['name'];
			$datas['descri'] = $order['Delivery']['luogoData'];
		}
		// debug($datas);

		$this->set($datas);
		if (!$this->validates()) {
			return false;
		} 
					
		$this->create();
		if (!$this->save($datas)) {
			return false;
			// debug($this->validationErrors);
		}

		return true;
	} 

	/*
	 * aggiorno il movimento di cassa da order_id a stat_order_id
	 */
	public function update($user, $organization_id, $order_id, $stat_order_id, $debug=false) {

		/* 
		 * controllo se esiste gia' un pagamento sull'ordine
		 */ 
		$options = [];
		$options['conditions'] = ['organization_id' => $organization_id,
								'order_id' => $order_id];
		$movement = $this->find('first', $options);
		// debug($movement);

		$movement['Movement']['order_id'] = null;
		$movement['Movement']['stat_order_id'] = $stat_order_id;
		self::d($movement, $debug);
		
		$this->set($movement);
		if (!$this->validates()) {
			self::d($this->validationErrors, $debug);
			return false;
		} 
					
		if (!$this->save($movement)) {
			// debug($this->validationErrors);
			self::d($this->validationErrors, $debug);
			return false;
		}

		return true;
	} 	
}