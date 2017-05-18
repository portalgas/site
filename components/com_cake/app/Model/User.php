<?php
App::uses('AuthComponent', 'Controller/Component');

/*
 *  DROP TRIGGER IF EXISTS `j_users_Trigger`;
 *  DELIMITER |
 *  CREATE TRIGGER `j_users_Trigger` AFTER DELETE ON `j_users`
 *  FOR EACH ROW BEGIN
 *  delete from k_suppliers_organizations_referents where user_id = old.id and organization_id = old.organization_id;
 *  delete from k_summary_payments where user_id = old.id and organization_id = old.organization_id;
 *  delete from k_summary_orders where user_id = old.id and organization_id = old.organization_id;
 *  delete from k_storerooms where user_id = old.id and organization_id = old.organization_id;
 *  delete from k_request_payments where user_id = old.id and organization_id = old.organization_id;
 *  delete from k_carts where user_id = old.id and organization_id = old.organization_id;
 *  
 *  delete from j_user_notes where user_id = old.id;
 *  delete from j_user_profiles where user_id = old.id;
 *  delete from j_user_usergroup_map where user_id = old.id;
 *  END
 *  |
 *  DELIMITER ;
 */
 
class User extends AppModel {

	public $actsAs = array('Containable');
    public $displayField = 'username';
	public $tablePrefix = 'j_';

	/*
	 * ottieni gli Users e i Groups associati e Referenti
	* */
	public function getUsersComplete($user, $conditions, $orderBy=null, $debug=false) {
		
		if(empty($orderBy)) $orderBy = Configure::read('orderUser');
		
		$sql = "SELECT 
					User.id, User.name, User.username, User.email, User.block, 
					User.lastvisitDate, User.registerDate  
				FROM 
					".Configure::read('DB.portalPrefix')."users User,
					".Configure::read('DB.portalPrefix')."user_usergroup_map UserGroup, 
					".Configure::read('DB.portalPrefix')."usergroups AS `Group`  
				WHERE 
					User.organization_id = ".(int)$user->organization['Organization']['id']." 
					AND UserGroup.user_id = User.id
					AND UserGroup.group_id = `Group`.id ";
		 // escludo info@gas.portalgas.it dispensa@gas.portalgas.it			
		$sql .= " AND User.username NOT LIKE '%.portalgas.it' ";
		 
		if(isset($conditions['User.block'])) $sql .= ' AND '.$conditions['User.block'];
		else
			$sql .= " AND User.block = 0 ";  // 0 attivo
		
		if(isset($conditions['User.name'])) $sql .= ' AND '.$conditions['User.name'];
		if(isset($conditions['User.username'])) $sql .= ' AND '.$conditions['User.username'];
		if(isset($conditions['UserGroup.group_id'])) $sql .= " AND UserGroup.group_id IN (".$conditions['UserGroup.group_id'].")";  // filtro per gruppi
		$sql .= " GROUP BY User.id, User.name, User.username, User.email   
				  ORDER BY ".$orderBy;
		if($debug) echo '<br />User::getUsersComplete() '.$sql;
		try {
			$results = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}

		/*
		 * G R O U P
		*/
		if(!empty($results))
			foreach ($results as $i => $result) {
				$sql = "SELECT
							`Group`.id, `Group`.title 
						FROM
							".Configure::read('DB.portalPrefix')."users User,
							".Configure::read('DB.portalPrefix')."user_usergroup_map UserGroup, 
							".Configure::read('DB.portalPrefix')."usergroups AS `Group`   
						WHERE
							User.organization_id = ".(int)$user->organization['Organization']['id']."
							AND UserGroup.user_id = User.id
							AND UserGroup.group_id = `Group`.id 
							AND User.id = ".$result['User']['id'];
				if(isset($conditions['UserGroup.group_id'])) $sql .= " AND UserGroup.group_id IN (".$conditions['UserGroup.group_id'].")";  // filtro per gruppi
				$sql .= " ORDER BY `Group`.title ";
				if($debug) echo '<br />User::getUsersComplete() '.$sql;
				try {
					$groupResults = $this->query($sql);
				}
				catch (Exception $e) {
					CakeLog::write('error',$sql);
					CakeLog::write('error',$e);
				}
				
				foreach ($groupResults as $numGroupResult => $groupResult) 
					$results[$i]['UserGroup'][$numGroupResult] = $groupResult['Group'];
			}
		
		// jimport( 'joomla.user.helper' );
		require_once(Configure::read('App.root').'/libraries/joomla/user/helper.php');
		
		if(!empty($results))
			foreach ($results as $numResult => $result) {
			
				/*
				 * R E F E R E N T I
				*/
				$sql = "SELECT
							SuppliersOrganizationsReferent.type,
							SuppliersOrganization.supplier_id, SuppliersOrganization.name 
						FROM
							".Configure::read('DB.portalPrefix')."users User,
							".Configure::read('DB.prefix')."suppliers_organizations_referents SuppliersOrganizationsReferent,
							".Configure::read('DB.prefix')."suppliers_organizations SuppliersOrganization 
						WHERE
							User.organization_id = ".(int)$user->organization['Organization']['id']."
							AND SuppliersOrganization.organization_id = ".(int)$user->organization['Organization']['id']."
							AND SuppliersOrganizationsReferent.organization_id =  ".(int)$user->organization['Organization']['id']."
							AND SuppliersOrganizationsReferent.user_id = User.id
							AND SuppliersOrganizationsReferent.supplier_organization_id = SuppliersOrganization.id 
							AND SuppliersOrganization.stato = 'Y' 
							AND User.id = ".$result['User']['id']."
						ORDER BY SuppliersOrganization.name ";
					if($debug) echo '<br />User::getUsersComplete() '.$sql; 
					try {
						$supplierResults = $this->query($sql);
					}
					catch (Exception $e) {
						CakeLog::write('error',$sql);
						CakeLog::write('error',$e);
					}
					
					foreach ($supplierResults as $numSupplierResult => $supplierResult) {
						$results[$numResult]['SuppliersOrganization'][$numSupplierResult] = $supplierResult['SuppliersOrganization'];
						$results[$numResult]['SuppliersOrganizationsReferent'][$numSupplierResult] = $supplierResult['SuppliersOrganizationsReferent'];
					}
	
					/*
					 * userprofile
					*/
					$userTmp = JFactory::getUser($result['User']['id']);
					$userProfile = JUserHelper::getProfile($userTmp->id);
						
					$results[$numResult]['Profile'] = $userProfile->profile;
			} // end foreach ($results as $numResult => $result)
		/*
		echo "<pre>User::getUsersComplete() \r";
		print_r($results);
		echo "</pre>";
		*/
		return $results;					
	}

	/*
	 * ottieni gli Users e i Groups associati
	* */
	public function getUsers($user, $conditions, $orderBy=null) {

		if(empty($orderBy)) $orderBy = Configure::read('orderUser');
		
		/* *****************************************************************
		 * precedente versione, UserGroupMap non filtrava per organization_id
		 $results = array();
		    
		    try {
			$contain = array(
					'User' => array('fields' => 'User.name, User.email',
							'conditions' => array('User.organization_id' => $user->organization['Organization']['id'],
									'User.block' => 0)),
			);
	
			$results = $this->UserGroupMap->find('all', array('conditions' => $conditions,
					'order' => 'group_id',
					'contain' => $contain,
					'recursive' => 0));
			// ordinamento http://book.cakephp.org/2.0/en/core-utility-libraries/set.html#Set::sort
			
			if(empty($orderBy)) $orderBy = Configure::read('orderUser');
			if($orderBy==Configure::read('orderUser'))
				$results =	Set::sort($results, '{n}.User.{n}.name', 'asc');
	
			}
			catch (Exception $e) {
				CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
			}
		***************************************************************** */
		
		$results = array();
		$sql = "SELECT 
					User.organization_id, User.id, User.name, User.username, User.email, 
					UserGroupMap.group_id, UserGroup.id, UserGroup.title 
				FROM
					".Configure::read('DB.portalPrefix')."user_usergroup_map UserGroupMap,
					".Configure::read('DB.portalPrefix')."usergroups UserGroup,
					".Configure::read('DB.portalPrefix')."users User
				WHERE
				UserGroupMap.user_id = User.id
				and UserGroupMap.group_id = UserGroup.id
				and User.block = 0
				and User.organization_id = ".(int)$user->organization['Organization']['id'];
		
		if(isset($conditions['UserGroupMap.group_id']))
			$sql .= " AND UserGroup.id = ".$conditions['UserGroupMap.group_id'];
		if(isset($conditions['UserGroup.id']))
			$sql .= " AND UserGroup.id = ".$conditions['UserGroup.id'];
		if(isset($conditions['UserGroupMap.group_id IN']))
			$sql .= " AND UserGroup.id IN ".$conditions['UserGroupMap.group_id IN'];		
		if(isset($conditions['UserGroupMap.user_id NOT IN']))
			$sql .= " AND UserGroupMap.user_id NOT IN ".$conditions['UserGroupMap.user_id NOT IN'];		

		$sql .= ' ORDER BY '.$orderBy;
		// echo '<br />'.$sql;
		try {
			$results = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}
		
		return $results;
	}
	
	 /*
	  * ottieni gli Users senza filtrare per organization_id e i Groups associati
	  * per group_id_root_supplier e group_id_root
	 * */
	 public function getUsersNoOrganization($conditions, $orderBy=null) {
	 	
	 	if(empty($orderBy)) $orderBy = Configure::read('orderUser');
	 	/*
	 	 * precedente versione, UserGroupMap non filtrava per organization_id
	 	$results = array(); 
	 	try {
		 	$contain = array(
		 			'User' => array('fields' => 'User.name, User.email',
		 							'conditions' => array('User.block' => 0)),
		 	);
		 	 	
		 	$results = $this->UserGroupMap->find('all', array('conditions' => $conditions,
		 													  'order' => 'group_id',
		 										 			  'contain' => $contain,
		 													  'recursive' => 0));
		 	// ordinamento http://book.cakephp.org/2.0/en/core-utility-libraries/set.html#Set::sort
		 	if(empty($orderBy)) $orderBy = Configure::read('orderUser');
		 	if($orderBy==Configure::read('orderUser'))
			 	$results =	Set::sort($results, '{n}.User.{n}.name', 'asc');

		}
		catch (Exception $e) {
		 	CakeLog::write('error',$sql);
		 	CakeLog::write('error',$e);
		}	
		*/
	 	 	 
		$results = array();
		$sql = "SELECT
					User.id, User.name, User.username, User.email,
					UserGroupMap.group_id, UserGroup.id, UserGroup.title
				FROM
					".Configure::read('DB.portalPrefix')."user_usergroup_map UserGroupMap,
					".Configure::read('DB.portalPrefix')."usergroups UserGroup,
					".Configure::read('DB.portalPrefix')."users User
				WHERE
				UserGroupMap.user_id = User.id
				and UserGroupMap.group_id = UserGroup.id
				and User.block = 0 ";
		
		if(isset($conditions['UserGroupMap.group_id']))
			$sql .= " AND UserGroup.id = ".$conditions['UserGroupMap.group_id'];
		if(isset($conditions['UserGroup.id']))
			$sql .= " AND UserGroup.id = ".$conditions['UserGroup.id'];
		if(isset($conditions['UserGroupMap.group_id IN']))
			$sql .= " AND UserGroup.id IN ".$conditions['UserGroupMap.group_id IN'];
		if(isset($conditions['UserGroupMap.user_id NOT IN']))
			$sql .= " AND UserGroup.id NOT IN ".$conditions['UserGroupMap.user_id NOT IN'];
		
		$sql .= ' ORDER BY '.$orderBy;
		// echo '<br />'.$sql;
		try {
			$results = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}
		
		return $results;
	 }

	 /*
	  * get lista User.id e User.name per le <select> 
	  * 
	  * in Mail:index() $label_fields = array('name', 'email')
	  * */
	 public function getUsersList($user, $conditions, $orderBy=null, $label_fields = array('name')) {
	 
	  	$results = $this->getUsers($user, $conditions, $orderBy);
	 	
	 	$resultsList = array();
	 	foreach($results as $user) {
			$label = "";
			foreach ($label_fields as $label_field)
				$label .= $user['User'][$label_field].' ';
			
			$label = substr($label, 0, (strlen($label)-1));
			$resultsList[$user['User']['id']] = $label;
	 	}
	 	
		/*	 	
	 	echo "<pre>";
	 	print_r($resultsList);
	 	echo "</pre>";
		*/
		
	 	return $resultsList;
	 }
	 
	 /*
	  * Ajax::admin_box_users con $reportOptions=='report-users-cart'
	  * 	estrae solo gli users che hanno effettuato acquisti in base all'ordine
	  */
	 public function getUserWithCartByOrder($user, $conditions, $orderBy=null) {
	    /*
	 	echo "<pre>getUserWithCartByOrder";
	 	print_r($conditions);
	 	echo "</pre>";
	 	*/
	 	$orderBy = Configure::read('orderUser');
	 	 
	 	$sql = "SELECT
				 	User.id, User.name, User.username, User.email  
				FROM 
					".Configure::read('DB.prefix')."articles_orders ArticlesOrder, 
					".Configure::read('DB.prefix')."carts Cart, 
					".Configure::read('DB.portalPrefix')."users User
			    WHERE
				    Cart.organization_id = ".(int)$user->organization['Organization']['id']."
				 	and ArticlesOrder.organization_id = ".(int)$user->organization['Organization']['id']."
				 	and User.organization_id = ".(int)$user->organization['Organization']['id']."
				    and ArticlesOrder.stato != 'N'
				 	and ArticlesOrder.order_id = Cart.order_id
				 	and ArticlesOrder.article_id = Cart.article_id
				 	and  Cart.user_id = User.id 
				 	and User.block = 0";
		if(isset($conditions['ArticlesOrder.order_id']))     $sql .= " and ArticlesOrder.order_id = ".$conditions['ArticlesOrder.order_id'];
		if(isset($conditions['ArticlesOrder.article_id']))   $sql .= " and ArticlesOrder.article_id = ".$conditions['ArticlesOrder.article_id'];
		$sql .= " GROUP BY User.id, User.name, User.username, User.email ";
		$sql .= " ORDER BY ".$orderBy;
		// echo '<br />'.$sql; 
		$results = $this->query($sql);
				
		return $results;
	 }

	 /*
	  * AjaxGasCodes::admin_box_users con $reportOptions=='report-users-cart'
	 * 	estrae solo gli users che hanno effettuato acquisti in base alla consegna
	 */
	 public function getUserWithCartByProdDelivery($user, $conditions, $orderBy=null) {
	 	/*
	 	 echo "<pre>getUserWithCartByProdDelivery";
	 	print_r($conditions);
	 	echo "</pre>";
	 	*/
	 	$orderBy = Configure::read('orderUser');
	 
	 	$sql = "SELECT
				 	User.id, User.name, User.username, User.email
				FROM
					".Configure::read('DB.prefix')."prod_deliveries_articles ProdDeliveriesArticle,
					".Configure::read('DB.prefix')."prod_carts ProdCart,
					".Configure::read('DB.portalPrefix')."users User
			    WHERE
				    ProdCart.organization_id = ".(int)$user->organization['Organization']['id']."
				 	and ProdDeliveriesArticle.organization_id = ".(int)$user->organization['Organization']['id']."
				 	and User.organization_id = ".(int)$user->organization['Organization']['id']."
				    and ProdDeliveriesArticle.stato != 'N'
				 	and ProdDeliveriesArticle.prod_delivery_id = ProdCart.prod_delivery_id
				 	and ProdDeliveriesArticle.article_id = ProdCart.article_id
				 	and  ProdCart.user_id = User.id ";
	 	if(isset($conditions['ProdDeliveriesArticle.prod_delivery_id']))     $sql .= " and ProdDeliveriesArticle.prod_delivery_id = ".$conditions['ProdDeliveriesArticle.prod_delivery_id'];
	 	if(isset($conditions['ProdDeliveriesArticle.article_id']))   $sql .= " and ProdDeliveriesArticle.article_id = ".$conditions['ProdDeliveriesArticle.article_id'];
	 	$sql .= " GROUP BY User.id, User.name, User.username, User.email ";
	 	$sql .= " ORDER BY ".$orderBy;
	 	//echo '<br />'.$sql;
	 	$results = $this->query($sql);
	 
	 	return $results;
	 }
	 
	 /*
	  *    estrae solo gli users che hanno effettuato acquisti in base alla consegna
	  *
	  *    $modalita = CRON se metodo richiamato da UtilsCrons::mailUsersDelivery() perche' esclude jimport()
	  */
	 public function getUserWithCartByDelivery($user, $conditions, $orderBy=null, $modalita='', $debug=false) {
	 	
	 	if($debug) {
			echo '<h3>getUserWithCartByDelivery</h3>';
			echo "<pre>";
			print_r($conditions);
			echo "</pre>";
		}
	 		 	 
	 	$orderBy = Configure::read('orderUser');
	 
	 	$sql = "SELECT
				 	User.id, User.name, User.username, User.email
				FROM
					".Configure::read('DB.prefix')."articles_orders ArticlesOrder,
					".Configure::read('DB.prefix')."carts Cart,
					".Configure::read('DB.portalPrefix')."users User, 
					".Configure::read('DB.prefix')."orders `Order`,
					".Configure::read('DB.prefix')."deliveries Delivery 
	 			WHERE
 					Cart.organization_id = ".(int)$user->organization['Organization']['id']."
 					and ArticlesOrder.organization_id = ".(int)$user->organization['Organization']['id']."
 					and User.organization_id = ".(int)$user->organization['Organization']['id']."
 					and `Order`.organization_id = ".(int)$user->organization['Organization']['id']."
 					and Delivery.organization_id = ".(int)$user->organization['Organization']['id']."
 					and ArticlesOrder.stato != 'N'
 					and ArticlesOrder.order_id = Cart.order_id
 					and ArticlesOrder.article_id = Cart.article_id
 					and  Cart.user_id = User.id 
	 				and ArticlesOrder.order_id = `Order`.id
					and `Order`.delivery_id = Delivery.id ";
 		if(isset($conditions['Delivery.id']))   $sql .= " and Delivery.id = ".$conditions['Delivery.id'];
 		$sql .= " GROUP BY User.id, User.name, User.username, User.email ";
 		$sql .= " ORDER BY ".$orderBy;
 		if($debug) echo '<br />'.$sql;
 		$results = $this->query($sql);

		//if($modalita != 'CRON') jimport( 'joomla.user.helper' );
		if($modalita != 'CRON')
			require_once(Configure::read('App.root').'/libraries/joomla/user/helper.php');
 		
 		if(!empty($results)) 
 			foreach ($results as $numResult => $result) {

	 			/*
	 			 * R E F E R E N T I
	 			*/
 				$sql = "SELECT
							SuppliersOrganizationsReferent.type,
							SuppliersOrganization.name
						FROM
							".Configure::read('DB.portalPrefix')."users User,
							".Configure::read('DB.prefix')."suppliers_organizations_referents SuppliersOrganizationsReferent,
							".Configure::read('DB.prefix')."suppliers_organizations SuppliersOrganization
 						WHERE
 							User.organization_id = ".(int)$user->organization['Organization']['id']."
 							AND SuppliersOrganization.organization_id = ".(int)$user->organization['Organization']['id']."
 							AND SuppliersOrganizationsReferent.organization_id =  ".(int)$user->organization['Organization']['id']."
 							AND SuppliersOrganizationsReferent.user_id = User.id
 							AND SuppliersOrganizationsReferent.supplier_organization_id = SuppliersOrganization.id
 							AND User.id = ".$result['User']['id']."
						ORDER BY SuppliersOrganization.name ";
	 			if($debug) echo '<br />'.$sql;
	 			$supplierResults = $this->query($sql);
	 		
	 			foreach ($supplierResults as $numSupplierResult => $supplierResult) {
		 			$results[$numResult]['SuppliersOrganization'][$numSupplierResult] = $supplierResult['SuppliersOrganization'];
					$results[$numResult]['SuppliersOrganizationsReferent'][$numSupplierResult] = $supplierResult['SuppliersOrganizationsReferent'];
				}

				/*
				 * userprofile
				 * D A T I   A N A G R A F I C I
				*/
				if($modalita != 'CRON') {
					$userTmp = JFactory::getUser($result['User']['id']);
					$userProfile = JUserHelper::getProfile($userTmp->id);
						
					$results[$numResult]['Profile'] = $userProfile->profile;
				}
				
			} // foreach ($results as $i => $result)

	 	if($debug) {
			echo "<pre>";
			print_r($results);
			echo "</pre>";
		}
		
		return $results;
	 }

	 /*
	  *    estrae solo gli users che hanno effettuato acquisti in base alla consegna
	  *		filtrando per gli ordini di cui lo user e' referente $this->user->get('ACLsuppliersIdsOrganization')
	  *		(per ex group_id_referent_cassiere)
	  */
	 public function getUserWithCartByDeliveryACLReferent($user, $conditions, $orderBy=null,  $debug=false) {
	 	
	 	if($debug) {
			echo '<h3>getUserWithCartByDeliveryACLReferent</h3>';
			echo "<pre>";
			print_r($conditions);
			echo "</pre>";
		}
	 		 	 
	 	$orderBy = Configure::read('orderUser');
	 
	 	$sql = "SELECT
				 	User.id, User.name, User.username, User.email
				FROM
					".Configure::read('DB.prefix')."articles_orders ArticlesOrder,
					".Configure::read('DB.prefix')."carts Cart,
					".Configure::read('DB.portalPrefix')."users User, 
					".Configure::read('DB.prefix')."suppliers_organizations SuppliersOrganization,
					".Configure::read('DB.prefix')."orders `Order`,
					".Configure::read('DB.prefix')."deliveries Delivery 
	 			WHERE
 					Cart.organization_id = ".(int)$user->organization['Organization']['id']."
 					and ArticlesOrder.organization_id = ".(int)$user->organization['Organization']['id']."
 					and User.organization_id = ".(int)$user->organization['Organization']['id']."
 					and SuppliersOrganization.organization_id = ".(int)$user->organization['Organization']['id']."
 					and `Order`.organization_id = ".(int)$user->organization['Organization']['id']."
 					and Delivery.organization_id = ".(int)$user->organization['Organization']['id']."
 					and ArticlesOrder.stato != 'N'
 					and ArticlesOrder.order_id = Cart.order_id
 					and ArticlesOrder.article_id = Cart.article_id
 					and  Cart.user_id = User.id 
	 				and ArticlesOrder.order_id = `Order`.id
					and `Order`.delivery_id = Delivery.id 
					and `Order`.supplier_organization_id = SuppliersOrganization.id 
					and SuppliersOrganization.id IN (".$user->get('ACLsuppliersIdsOrganization').")
					";
 		if(isset($conditions['Delivery.id']))   $sql .= " and Delivery.id = ".$conditions['Delivery.id'];
 		$sql .= " GROUP BY User.id, User.name, User.username, User.email ";
 		$sql .= " ORDER BY ".$orderBy;
 		if($debug) echo '<br />'.$sql;
 		$results = $this->query($sql);
	 
 		jimport( 'joomla.user.helper' );
 		
 		if(!empty($results)) 
 			foreach ($results as $numResult => $result) {

	 			/*
	 			 * R E F E R E N T I
	 			*/
 				$sql = "SELECT
							SuppliersOrganizationsReferent.type,
							SuppliersOrganization.name
						FROM
							".Configure::read('DB.portalPrefix')."users User,
							".Configure::read('DB.prefix')."suppliers_organizations_referents SuppliersOrganizationsReferent,
							".Configure::read('DB.prefix')."suppliers_organizations SuppliersOrganization
 						WHERE
 							User.organization_id = ".(int)$user->organization['Organization']['id']."
 							AND SuppliersOrganization.organization_id = ".(int)$user->organization['Organization']['id']."
 							AND SuppliersOrganizationsReferent.organization_id =  ".(int)$user->organization['Organization']['id']."
 							AND SuppliersOrganizationsReferent.user_id = User.id
 							AND SuppliersOrganizationsReferent.supplier_organization_id = SuppliersOrganization.id
 							AND User.id = ".$result['User']['id']."
						ORDER BY SuppliersOrganization.name ";
	 			if($debug) echo '<br />'.$sql;
	 			$supplierResults = $this->query($sql);
	 		
	 			foreach ($supplierResults as $numSupplierResult => $supplierResult) {
		 			$results[$numResult]['SuppliersOrganization'][$numSupplierResult] = $supplierResult['SuppliersOrganization'];
					$results[$numResult]['SuppliersOrganizationsReferent'][$numSupplierResult] = $supplierResult['SuppliersOrganizationsReferent'];
				}

				/*
				 * userprofile
				 * D A T I   A N A G R A F I C I
				*/
				$userTmp = JFactory::getUser($result['User']['id']);
				$userProfile = JUserHelper::getProfile($userTmp->id);
					
				$results[$numResult]['Profile'] = $userProfile->profile;
				
			} // foreach ($results as $i => $result)

	 	if($debug) {
			echo "<pre>";
			print_r($results);
			echo "</pre>";
		}
		
		return $results;
	 }

	 /*
	  * original code /administrator/components/com_users/models/user.php
	 * 	associo un utente alla tabella joomla.user_usergroup_map con il gruppo passato (ex gasReferente)
	 * 	cancello un utente con il gruppo passato (ex gasReferente) dalla tabella joomla.user_usergroup_map
	 * */
	 public function joomlaBatchUser($group_id, $user_id, $action, $debug=false)
	 {
	 	if($debug) echo '<br />joomlaBatchUser action '.$action;
	 	
	 	// Get the DB object
	 	$db = JFactory::getDbo();
	 
	 	// Non-super admin cannot work with super-admin group
	 	if ((!JFactory::getUser()->get('isRoot') && JAccess::checkGroup($group_id, 'core.admin')) || $group_id < 1)
	 	{
	 		echo ' <span style="color:red;">Error</span> Gruppo ('.$group_id.') non valido'; // .JText::_('COM_USERS_ERROR_INVALID_GROUP');
	 		return false;
	 	}
	 
	 	switch ($action)
	 	{
	 		// Sets users to a selected group   Mai utilizzato
	 		case 'set':
	 			$doDelete	= 'all';
	 			$doAssign	= true;
	 			break;
	 
	 			// Remove users from a selected group
	 		case 'del':
	 			$doDelete	= 'group';
	 			break;
	 
	 			// Add users to a selected group
	 		case 'add':
	 		default:
	 			$doAssign	= true;
	 			break;
	 	}
	 
	 	// Remove the users from the group if requested.
	 	if (isset($doDelete))
	 	{
	 		$query = $db->getQuery(true);
	 
	 		// Remove users from the group
	 		$query->delete($db->quoteName('#__user_usergroup_map'));
	 		$query->where($db->quoteName('user_id') . ' IN (' . $user_id . ')');
	 		
	 		// Only remove users from selected group
	 		if ($doDelete == 'group')
	 		{
	 			$query->where($db->quoteName('group_id') . ' = ' . (int) $group_id);
	 		}
	 		if($debug) echo "\n".$query;
	 		
	 		$db->setQuery($query);
	 
	 		// Check for database errors.
	 		if (!$db->query())
	 		{
	 			echo ' <span style="color:red;">Error</span> '.$db->getErrorMsg();
	 			return false;
	 		}
	 	}
	 
	 	// Assign the users to the group if requested.
	 	if (isset($doAssign))
	 	{
	 		$query = $db->getQuery(true);
	 
	 		// First, we need to check if the user is already assigned to a group
	 		$query->select($db->quoteName('user_id'));
	 		$query->from($db->quoteName('#__user_usergroup_map'));
	 		$query->where($db->quoteName('group_id') . ' = ' . (int) $group_id);
	 		$db->setQuery($query);
	 		if($debug) echo "\n".$query;
	 		$users = $db->loadColumn();
	 
	 		// Build the values clause for the assignment query.
	 		$query->clear();
	 		$groups = false;
	 		if (!in_array($user_id, $users))
	 		{
	 			$query->values($user_id . ',' . $group_id);
	 			$groups = true;
	 		}
	 
	 		// If we have no users to process, throw an error to notify the user
	 		if (!$groups)
	 		{
	 			if($debug) echo ' <span style="color:yellow;background-color: #000000;">ALERT</span> L\'utente(i) è già assegnato al gruppo selezionato.'; // .JText::_('COM_USERS_ERROR_NO_ADDITIONS');
	 			return false;
	 		}
	 
	 		$query->insert($db->quoteName('#__user_usergroup_map'));
	 		$query->columns(array($db->quoteName('user_id'), $db->quoteName('group_id')));
	 		if($debug) echo "\n".$query;
	 		$db->setQuery($query);
	 
	 		// Check for database errors.
	 		if (!$db->query())
	 		{
	 			echo ' <span style="color:red;">Error</span> '.$db->getErrorMsg();
	 			return false;
	 		}
	 	}
	 
	 	return true;
	 }
	 
	 public function getUsernameCrypted($username) {
	 	
	 	$salt = Configure::read('Security.salt');
	 	
	 	/*
	 	 * crea stringa cifrata ma non leggibile
	 	*/
	 	$encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $username, MCRYPT_MODE_ECB);
	 	/*
	 	 * converte stringa cifrata in modo leggibile (MGCP+iQL/0qPiL2H62c+WXrnY856xfided9FJhjarEU=)
	 	*/
	 	$encrypted_base64 = base64_encode($encrypted);	

	 	return $encrypted_base64;
	 }
	 
	 public function getUsernameToUsernameCrypted($usernameCrypted) {
	 	 
		$salt = Configure::read('Security.salt');
			
		//To Decrypt:
		$username = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($usernameCrypted), MCRYPT_MODE_ECB));
	 
	 	return $username;
	 }
	 
	 /*
	 * creo link della mail /preview-carrello?E=3456434&O=451&R=fHqbzWjOK6GaWezgE4mycHsphSPsE9HhincbgjTmDjY=&format=html
	 * 	E = random, non serve a niente
	 *  O = (tolgo i primi 2 numeri e poi organization_id) organization_id
	 *  R = username crittografata User->getUsernameCrypted()
	 *  D = (tolgo i primi 2 numeri e poi delivery_id) delivery_id
	 *  org_id serve per mod_gas_organization_choice	  */
	 public function getUrlCartPreview($user, $username, $delivery_id) {
	 	
	 	$tmp = "";

	 	$E = '';
	 	$O = '';
	 	$R = '';
	 	$D = '';
	 	$org_id = '';
	 	
	 	$E = $this->utilsCommons->random_string($length=5);
	 	
	 	$O = rand (10, 99).$user->organization['Organization']['id'];
	 	
	 	$R = urlencode($this->getUsernameCrypted($username));
	 	
	 	$D = rand (10, 99).$delivery_id;
	 	
	 	$org_id = $user->organization['Organization']['id'];

	 	$tmp = 'E='.$E.'&O='.$O.'&R='.$R.'&D='.$D.'&org_id='.$org_id;
	 	
	 	return $tmp;
	 }
	 
	 /*
	  * creo url senza lo username, 
	  * in Cron::mailUsersOrdersOpen, quando ciclo per utenti ho gia' creato il messaggio per consegna
	  */
	 public function getUrlCartPreviewNoUsername($user, $delivery_id) {
	 	 
	 	$tmp = "";
	 
	 	$E = '';
	 	$O = '';
	 	$R = '';
	 	$D = '';
	 	$org_id = '';
	 	 
	 	$E = $this->utilsCommons->random_string($length=5);
	 	 
	 	$O = rand (10, 99).$user->organization['Organization']['id'];
	 	 
	 	$R = "{u}";
	 	 
	 	$D = rand (10, 99).$delivery_id;
	 	 
	 	$org_id = $user->organization['Organization']['id'];
	 
	 	$tmp = 'E='.$E.'&O='.$O.'&R='.$R.'&D='.$D.'&org_id='.$org_id;
	 	 
	 	return $tmp;
	 }
	 
	 public $hasMany = array(
		'Cart' => array(
			'className' => 'Cart',
			'foreignKey' => 'user_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'SuppliersOrganizationsReferent' => array(
			'className' => 'SuppliersOrganizationsReferent',
			'foreignKey' => 'user_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'UserGroupMap' => array(
			'className' => 'UserGroupMap',
			'foreignKey' => 'user_id',
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