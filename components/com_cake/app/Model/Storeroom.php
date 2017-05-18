<?php
App::uses('AppModel', 'Model');

class Storeroom extends AppModel {

	public $useTable = 'storerooms';

	/*   
	 * Articoli in dispensa
	 * 		$conditions['Delivery.organization_id']   e' in JOIN perche' quand'e' in dispensa la consegna e' 0
	 * 		$conditions['User.id']
	 * 		$conditions['Cart.article_id']					 filtro di un articolo
	 * 		$conditions['Article.supplier_organization_id']  filtro per produttore
	* */
	public function getArticlesToStoreroom($user, $conditions, $orderBy=null) {

		$results = array();
		if($user->organization['Organization']['hasStoreroom']=='Y') {			
			$this->virtualFields = array(
				'prezzo_db' => 0,
				'importo' => 0,
			);
			
			/*
			echo "<pre>getArticlesToStoreroom";
			print_r($conditions);
			echo "</pre>";
			*/
	
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
						ON (Storeroom.delivery_id = Delivery.id ), 
						".Configure::read('DB.prefix')."suppliers_organizations SuppliersOrganization,
						".Configure::read('DB.portalPrefix')."users User
					WHERE
						Storeroom.organization_id = ".(int)$user->organization['Organization']['id']." 
						and Article.organization_id = ".(int)$user->organization['Organization']['id']." 
						and SuppliersOrganization.organization_id = ".(int)$user->organization['Organization']['id']." 
						and User.organization_id = ".(int)$user->organization['Organization']['id']." 
						and Storeroom.article_id = Article.id
						and Storeroom.article_organization_id = Article.organization_id
						and Storeroom.user_id = User.id
						and SuppliersOrganization.id = Article.supplier_organization_id
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
			if(isset($conditions['Article.id'])) $sql .= " and Article.id = ".$conditions['Article.id'];
			
			$sql .= " ORDER BY ".$order;
			//echo '<br />'.$sql;
			try {
				$results = $this->query($sql);
			}			catch (Exception $e) {				CakeLog::write('error',$sql);				CakeLog::write('error',$e);			}
		}
			
		return $results;
	}
	/*
	 * ottieni lo user che gestisce la dispensa
	 * dev'essere solo 1
	 * */
	public function getStoreroomUser($user) {

		$storeroomUser = array();		
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
			//echo '<br />'.$sql;
			try {				$storeroomUser = $this->query($sql);			}			catch (Exception $e) {				CakeLog::write('error',$sql);				CakeLog::write('error',$e);			}			
		
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
	public function getCartsToStoreroom($user, $order_id) {
		
		$storeroomUser = $this->getStoreroomUser($user);
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		$conditions = array('Cart.user_id' => $storeroomUser['User']['id'],
							'Cart.order_id' => $order_id);
		$results = $ArticlesOrder->getArticoliDellUtenteInOrdine($user ,$conditions);
		
		return $results;
	}
	
	/*
	 * riporta gli Articoli acquistati in Dispensa
	 * se cancello la consegna o modifico una consegna da isToStoreroom = Y a N
	 * */
	public function riportaArticoliAcquistatiInDispensa($user, $delivery_id) {
		
		$storeroomUser = $this->getStoreroomUser($user);
		$sql = "UPDATE					".Configure::read('DB.prefix')."storerooms				SET					delivery_id = 0,					user_id = ".$storeroomUser['User']['id']."				WHERE					delivery_id = ".(int)$delivery_id."					and organization_id = ".(int)$user->organization['Organization']['id'];
			// echo '<br />'.$sql; 			try {
				$results = $this->query($sql);
			}
			catch (Exception $e) {
				CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
			}		
	}
	
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'User.organization_id = Storeroom.organization_id',
			'fields' => '',
			'order' => ''
		),
		'Delivery' => array(
			'className' => 'Delivery',
			'foreignKey' => 'delivery_id',
			'conditions' => 'Delivery.organization_id = Storeroom.organization_id',
			'fields' => '',
			'order' => ''
		),
		'Article' => array(
			'className' => 'Article',
			'foreignKey' => 'article_id',
			'conditions' => 'Article.organization_id = Storeroom.organization_id',
			'fields' => '',
			'order' => ''
		)			
	);
	
	/* il dato arriva già corretto
	public function beforeSave($options = array()) {
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