<?php
App::uses('AppModel', 'Model');

class Storeroom extends AppModel {

	public $useTable = 'storerooms';

	/*
	 * non faccio l'override perche' in _doSave function exists($id)
	 */
    public function existsDisabled($id=0, $organization_id=0) {
		
		if(empty($organization_id) || empty($id)) {
			return false;
		}

		/* con first non funziona! */
		return (bool)$this->find('count', [ 
				'conditions' => [
						$this->alias . '.id' => $id,
						$this->alias . '.organization_id' => $organization_id
				],
				'recursive' => -1,
				'callbacks' => false
			]);

		/*
		$results = $this->find('first', [
				'conditions' => [
						$this->alias . '.id' => $id,
						$this->alias . '.organization_id' => $organization_id
				],
				'recursive' => -1,
				'callbacks' => false
			]);
			
		if(empty($results))
			return false;
		else
			return true;
		*/
    }
   
	/*   
	 * Articoli in dispensa
	 * 		$conditions['Delivery.organization_id']   e' in JOIN perche' quand'e' in dispensa la consegna e' 0
	 * 		$conditions['User.id']						     puo' essere l'utente dispensa o quello in sessione
	 * 		$conditions['Cart.article_id']					 filtro di un articolo
	 * 		$conditions['Article.supplier_organization_id']  filtro per produttore
	* */
	public function getArticlesToStoreroom($user, $conditions, $orderBy=null, $debug=false) {

		$results = [];
		if($user->organization['Organization']['hasStoreroom']=='Y') {
			
			$this->virtualFields = [
				'prezzo_db' => 0,
				'importo' => 0,
			];
			
			self::d('getArticlesToStoreroom', $debug);
			self::d($conditions, $debug);
	
			if(isset($orderBy['Delivery'])) $order = $orderBy['Delivery'];
			else
			if(isset($orderBy['SuppliersOrganization'])) $order = $orderBy['SuppliersOrganization'];
			else $order = 'Article.name ASC';
			
			$sql = "SELECT
						Delivery.luogo, Delivery.data, 
						SuppliersOrganization.id, SuppliersOrganization.name, SuppliersOrganization.supplier_id,
						Article.*,
						Storeroom.*,
						FORMAT(Storeroom.prezzo,2) as Storeroom__prezzo, Storeroom.prezzo as Storeroom__prezzo_db,
						FORMAT((Storeroom.prezzo * Storeroom.qta), 2) as Storeroom__importo,
						User.id, User.name
					FROM
						".Configure::read('DB.prefix')."articles Article,
						".Configure::read('DB.prefix')."storerooms Storeroom LEFT JOIN ".Configure::read('DB.prefix')."deliveries Delivery 
						ON (Storeroom.delivery_id = Delivery.id and Storeroom.organization_id = Delivery.organization_id), 
						".Configure::read('DB.prefix')."suppliers_organizations SuppliersOrganization,
						".Configure::read('DB.portalPrefix')."users User
					WHERE
						Storeroom.organization_id = ".(int)$user->organization['Organization']['id']." 
						and User.organization_id = ".(int)$user->organization['Organization']['id']." 
						and SuppliersOrganization.organization_id = ".(int)$user->organization['Organization']['id']."
						and Article.supplier_organization_id = SuppliersOrganization.owner_supplier_organization_id
						and Article.organization_id = SuppliersOrganization.owner_organization_id 
						and Storeroom.article_id = Article.id
						and Storeroom.article_organization_id = Article.organization_id
						and Storeroom.user_id = User.id
						and Article.stato = 'Y' 
						and SuppliersOrganization.stato = 'Y'
						and User.block = 0  
						";
			if(isset($conditions['Delivery.id'])) {
				if($conditions['Delivery.id']=='> 0' && $conditions['Delivery.id']!='0')
					$sql .= " and Delivery.id ".$conditions['Delivery.id']." and Delivery.organization_id = ".(int)$user->organization['Organization']['id']." and Delivery.isVisibleFrontEnd = 'Y' and Delivery.isVisibleBackOffice = 'Y' ";
				else
				if($conditions['Delivery.id']>0)
					$sql .= " and Delivery.id = ".$conditions['Delivery.id']." and Delivery.organization_id = ".(int)$user->organization['Organization']['id']." and Delivery.isVisibleFrontEnd = 'Y' and Delivery.isVisibleBackOffice = 'Y' ";
			}
			if(isset($conditions['Storeroom.id'])) $sql .= " and Storeroom.id = ".$conditions['Storeroom.id']; 
			if(isset($conditions['User.id'])) $sql .= " and User.id = ".$conditions['User.id']; // puo' essere l'utente dispensa o quello in sessione
			if(isset($conditions['Article.supplier_organization_id'])) $sql .= " and Article.supplier_organization_id = ".$conditions['Article.supplier_organization_id'];  // filtro per produttore
			if(isset($conditions['SuppliersOrganization.id'])) $sql .= " and SuppliersOrganization.id = ".$conditions['SuppliersOrganization.id'];  // filtro per produttore
			if(isset($conditions['Article.id'])) $sql .= " and Article.id = ".$conditions['Article.id'];
			
			$sql .= " ORDER BY ".$order;
			self::d($sql, $debug);
			try {
				$results = $this->query($sql);
			}
			catch (Exception $e) {
				CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
			}
		}
			
		self::d($results, $debug);

		return $results;
	}

	/*
	 * estre gli articoli in dispensa gia' prenotati Storeroom.user_id != storeroomUser
	 * se valorizzo user_id ctrl per quel determinato user
	 */
	public function getArticlesJustBooked($user, $storeroomUser, $article_organization_id, $article_id, $user_id=0, $delivery_id=0, $orderBy=null) {

		$results = [];
		if($user->organization['Organization']['hasStoreroom']=='Y') {
			
			$this->virtualFields = [
				'prezzo_db' => 0,
				'importo' => 0,
			];
			
			if(!isset($orderBy)) 
				$order = 'User.username ASC';
			else 
				$order = 'Article.name ASC';
			
			$sql = "SELECT
						Delivery.luogo, Delivery.data, Storeroom.*,
						FORMAT(Storeroom.prezzo,2) as Storeroom__prezzo, Storeroom.prezzo as Storeroom__prezzo_db,
						FORMAT((Storeroom.prezzo * Storeroom.qta), 2) as Storeroom__importo,
						User.id, User.name, User.username, User.email 
					FROM
						".Configure::read('DB.prefix')."storerooms Storeroom LEFT JOIN ".Configure::read('DB.prefix')."deliveries Delivery 
						ON (Storeroom.delivery_id = Delivery.id and Storeroom.organization_id = Delivery.organization_id),   
						".Configure::read('DB.portalPrefix')."users User
					WHERE
						Storeroom.organization_id = ".(int)$user->organization['Organization']['id']." 
						and Storeroom.article_id = ".(int)$article_id." 
						and Storeroom.article_organization_id = ".(int)$article_organization_id." 
						and Storeroom.user_id = User.id
						and User.block = 0 
						and User.organization_id = ".(int)$storeroomUser['User']['organization_id']." 
						and User.id != ".$storeroomUser['User']['id'];	
			if(!empty($user_id))
				$sql .= " and User.id = ".$user_id;
			if(!empty($delivery_id))
				$sql .= " and Delivery.id = ".$delivery_id;				
			$sql .= " ORDER BY ".$order;
			self::d($sql, false);
			try {
				$results = $this->query($sql);
			}
			catch (Exception $e) {
				CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
			}
		}
			
		return $results;
	}

	/*
	 * ottieni lo user che gestisce la dispensa
	 * dev'essere solo 1
	 * */
	public function getStoreroomUser($user) {

		$storeroomUser = [];
		
		if($user->organization['Organization']['hasStoreroom']=='Y') {
			
			$sql = "SELECT User.organization_id, User.id, User.name, User.username, User.email 
					FROM
						".Configure::read('DB.portalPrefix')."user_usergroup_map m,
						".Configure::read('DB.portalPrefix')."usergroups g,
						".Configure::read('DB.portalPrefix')."users User 
					WHERE
						m.user_id = User.id
						and m.group_id = g.id
						and m.group_id = ".Configure::read('group_id_storeroom')."
						and User.block = 0
						and User.organization_id = ".(int)$user->organization['Organization']['id']." LIMIT 0,1";
			self::d($sql, false);
			try {
				$storeroomUser = $this->query($sql);
			}
			catch (Exception $e) {
				CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
			}			
		
			if(!empty($storeroomUser)) $storeroomUser = current($storeroomUser);
		}
		/*
		echo "<pre>";
		print_r($storeroomUser);
		echo "</pre>";
		*/
		return $storeroomUser;		
	}
	
	/*
	 * ottengo tutti gli acquisti della dispensa in un ordine
	 */
	public function getCartsToStoreroom($user, $order_id, $debug=false) {
		
		$results = [];
		
		$storeroomUser = $this->getStoreroomUser($user);
		if(!empty($storeroomUser)) {
			App::import('Model', 'ArticlesOrder');
			$ArticlesOrder = new ArticlesOrder;
			
			$conditions = ['Cart.user_id' => $storeroomUser['User']['id'],
							'Cart.order_id' => $order_id];
			$results = $ArticlesOrder->getArticoliDellUtenteInOrdine($user ,$conditions, null, null, $debug);
		}
				
		return $results;
	}

	/*
	 * ottengo tutti gli utenti che in una data consegna hanno prenotazioni in dispensa
	 */
	public function getUsersDeliveryBuy($user, $storeroomUser, $delivery_id, $debug=false) {
		
	 	$orderBy = Configure::read('orderUser');
	 
	 	$sql = "SELECT
				 	User.id, User.name, User.username, User.email
				FROM
					".Configure::read('DB.portalPrefix')."users User, 
					".Configure::read('DB.prefix')."storerooms `Storeroom`,
					".Configure::read('DB.prefix')."deliveries Delivery 
	 			WHERE
 					Storeroom.organization_id = ".(int)$user->organization['Organization']['id']."
 					and Delivery.organization_id = ".(int)$user->organization['Organization']['id']."
 					and User.organization_id = ".(int)$user->organization['Organization']['id']."
 					and Storeroom.user_id = User.id 
 					and Storeroom.delivery_id = Delivery.id 
 					and User.id != ".$storeroomUser['User']['id']."
 					and Delivery.id = ".$delivery_id;
 		$sql .= " GROUP BY User.id, User.name, User.username, User.email ";
 		$sql .= " ORDER BY ".$orderBy;
 		self::d($sql, $debug);
 		$results = $this->query($sql);
		
		return $results;
	}
		
	/*
	 * riporta gli Articoli acquistati in Dispensa
	 * se cancello la consegna o modifico una consegna da isToStoreroom = Y a N
	 * */
	public function riportaArticoliAcquistatiInDispensa($user, $delivery_id) {
		
		$storeroomUser = $this->getStoreroomUser($user);
		$sql = "UPDATE
					".Configure::read('DB.prefix')."storerooms
				SET
					delivery_id = 0,
					user_id = ".$storeroomUser['User']['id']."
				WHERE
					delivery_id = ".(int)$delivery_id."
					and organization_id = ".(int)$user->organization['Organization']['id'];
			self::d($sql, false);
			try {
				$results = $this->query($sql);
			}
			catch (Exception $e) {
				CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
			}		
	}
	
	/*
	 * se non ci sono consegne valide (quelle per la dispensa) non fa comparire la voce di menu "Aggiungi una richiesta pagamento di dispensa"
	 */	
	public function deliveriesToRequestPayment($user, $debug=false)	{
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		$options['conditions'] = ['Delivery.organization_id' => (int)$user->organization['Organization']['id'],
								'Delivery.isVisibleBackOffice' => 'Y',
								'Delivery.isToStoreroom' => 'Y',
								'Delivery.isToStoreroomPay' => 'N',
								'Delivery.sys' => 'N',
								'Delivery.stato_elaborazione' => 'OPEN',
								'DATE(Delivery.data) < CURDATE()'];
		$options['fields'] = ['Delivery.id', 'Delivery.luogoData'];
		$options['order'] = ['Delivery.data ASC'];
		$options['recursive'] = -1;
		
		$results = $Delivery->find('list', $options);
		if($debug) {
			echo "<pre>";
			print_r($options);
			print_r($results);
			echo "</pre>";
		}
		return $results;
	}
	
	/*
	 * estrae le consegne per il front-end: solo le future
	 */
	public function getDeliveriesFE($user) {
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		$options = [];
		$options['conditions'] = ['DATE(Delivery.data) >= CURDATE() ',
								'Delivery.organization_id = '.(int)$user->organization['Organization']['id'],
								'Delivery.isToStoreroom' => 'Y',
								'Delivery.isVisibleBackOffice' => 'Y',
								'Delivery.sys'=> 'N',
								'Delivery.stato_elaborazione' => 'OPEN'];
		$options['fields'] = ['Delivery.id', 'luogoData'];
		$options['order'] = ['data ASC'];
		$options['recursive'] = 1;
		$deliveries = $Delivery->find('list', $options);

		return $deliveries;
	}
	
	/*
	 * estrae le consegne per il back-office
	 */	
	public function getDeliveries($user) {
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		/* 
		 * prendo anche quelle scadute perche' si deve permettere di associare, 
		 * ma ctrl che non siano legate a richieste di pagamento 
		 */
		$options = [];
		$options['conditions'] = ['Delivery.organization_id = '.(int)$user->organization['Organization']['id'],
								'Delivery.isToStoreroom' => 'Y',
								'Delivery.isVisibleBackOffice' => 'Y',
								'Delivery.sys'=> 'N',
								'Delivery.stato_elaborazione' => 'OPEN'];
		$options['fields'] = ['Delivery.id', 'luogoData'];
		$options['order'] = ['data ASC'];
		$options['recursive'] = 1;
		$deliveries = $Delivery->find('list', $options);

		/*
		 * escludo consegne gia' associate con richieste di pagamento
		 */
		if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') {
			App::import('Model', 'RequestPaymentsStoreroom');
			$RequestPaymentsStoreroom = new RequestPaymentsStoreroom;
	
			foreach($deliveries as $delivery_id => $delivery_name) {
				$options = [];
				$options['conditions'] = ['RequestPaymentsStoreroom.organization_id' => $user->organization['Organization']['id'],
							              'RequestPaymentsStoreroom.delivery_id' => $delivery_id];
				$options['recursive'] = -1;	
				$requestPaymentsStoreroomResults = $RequestPaymentsStoreroom->find('count',$options);
				if($requestPaymentsStoreroomResults>0) {
					unset($deliveries[$delivery_id]);
				}
			}		
		}	

		return $deliveries;
	}

	public $belongsTo = [
		'User' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'User.organization_id = Storeroom.organization_id',
			'fields' => '',
			'order' => ''
		],
		'Delivery' => [
			'className' => 'Delivery',
			'foreignKey' => 'delivery_id',
			'conditions' => 'Delivery.organization_id = Storeroom.organization_id',
			'fields' => '',
			'order' => ''
		],
		'Article' => [
			'className' => 'Article',
			'foreignKey' => 'article_id',
			'conditions' => 'Article.organization_id = Storeroom.article_organization_id',
			'fields' => '',
			'order' => ''
		]
	];
	
	/* il dato arriva già corretto
	public function beforeSave($options = []) {
		if(!empty($this->data['Storeroom']['prezzo'])) {
			$this->data['Storeroom']['prezzo'] =  $this->importoToDatabase($this->data['Storeroom']['prezzo']);
		}
		return true;
	}
	*/
	
	public function afterFind($results, $primary = true) {
		foreach ($results as $key => $val) {
			if(!empty($val)) {
				if (isset($val['Storeroom']['prezzo'])) {
					$results[$key]['Storeroom']['prezzo_'] = number_format($val['Storeroom']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Storeroom']['prezzo_e'] = $results[$key]['Storeroom']['prezzo_'].' &euro;';
					$results[$key]['Storeroom']['importo'] = ($val['Storeroom']['prezzo']*$val['Storeroom']['qta']);
					$results[$key]['Storeroom']['importo_'] = number_format(($val['Storeroom']['prezzo']*$val['Storeroom']['qta']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Storeroom']['importo_e'] = $results[$key]['Storeroom']['importo_'].' &euro;';
				}
			}				
		}
		return $results;
	}	
}