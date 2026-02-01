<?php
App::uses('AuthComponent', 'Controller/Component');
 
class User extends AppModel {

	public $actsAs = ['Containable'];
    public $displayField = 'username';
	public $tablePrefix = 'j_';

	/*
	 * ottieni gli Users e i Groups associati e Referenti
	* */
	public function getUsersComplete($user, $conditions=[], $orderBy=null, $debug=false) {
		
		self::d($conditions); 
		
		if(empty($orderBy)) $orderBy = Configure::read('orderUser');
		
		$sql = "SELECT 
					User.organization_id, User.id, User.name, User.username, User.email, 
					User.block, User.can_login, 
					User.activation, User.lastvisitDate, User.registerDate  
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
		
		if(isset($conditions['User.can_login'])) $sql .= ' AND '.$conditions['User.can_login'];	
		if(isset($conditions['User.id'])) $sql .= ' AND '.$conditions['User.id'];
		if(isset($conditions['User.name'])) $sql .= ' AND '.$conditions['User.name'];
		if(isset($conditions['User.username'])) $sql .= ' AND '.$conditions['User.username'];
		if(isset($conditions['UserGroup.group_id'])) $sql .= " AND UserGroup.group_id IN (".$conditions['UserGroup.group_id'].")";  // filtro per gruppi
		$sql .= " GROUP BY User.id, User.name, User.username, User.email   
				  ORDER BY ".$orderBy;
		self::d('User::getUsersComplete() '.$sql, $debug);
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
				self::d('User::getUsersComplete() '.$sql, $debug);
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
					self::d('User::getUsersComplete() '.$sql, $debug);
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
					self::d($userTmp);
					self::d($userProfile->profile); 
					
					$results[$numResult]['Profile'] = $userProfile->profile;
						
					if(!isset($userProfile->profile['hasUserFlagPrivacy']))
						$results[$numResult]['Profile']['UserFlagPrivacy'] = 'N';
					if(!isset($userProfile->profile['hasUserRegistrationExpire']))
						$results[$numResult]['Profile']['hasUserRegistrationExpire'] = 'N';
					if(!isset($userProfile->profile['CF']))
						$results[$numResult]['Profile']['CF'] = '';
						
																
					if(isset($conditions['UserProfile.UserFlagPrivacy']) && $conditions['UserProfile.UserFlagPrivacy']!='ALL') {
						if($userProfile->profile['hasUserFlagPrivacy']!=$conditions['UserProfile.UserFlagPrivacy']) {
							self::d($userProfile->profile['hasUserFlagPrivacy'].' '.$conditions['UserProfile.UserFlagPrivacy']); 
							unset($results[$numResult]);
						}
					}
					if(isset($conditions['UserProfile.UserRegistrationExpire']) && $conditions['UserProfile.UserRegistrationExpire']!='ALL') {
						if($userProfile->profile['hasUserRegistrationExpire']!=$conditions['UserProfile.UserRegistrationExpire'])
							unset($results[$numResult]);						
					}
					if(isset($conditions['UserProfile.CF'])) {
						if(strtoupper($userProfile->profile['cf']) != strtoupper($conditions['UserProfile.CF']))
							unset($results[$numResult]);						
					}
					
					self::d($results[$numResult]); 
										
			} // end foreach ($results as $numResult => $result)
		
		self::d($results, $debug);
		
		return $results;					
	}

	public function getUsersToMailByIds($user, $user_ids=[], $debug=false) {
                
        $newUsersResults = [];
        
         self::d($user_ids, $debug);        
         
		if(is_object($user)) 
			$organization_id = $user->organization['Organization']['id'];
		else 
			$organization_id = $user;
		
		$usersResults = $this->getUsersToMail($user, $debug);
		
		/*
		 * escludo gli user_id che non fanno parte dell'array
		 */
		if(!empty($user_ids) && !empty($usersResults)) {
			foreach($usersResults as $numResult => $usersResult) {
				$found=false;
				foreach($user_ids as $numResult2 => $user_id) {
				
					self::d($usersResult['User']['id'].' - '.$user_id, $debug);
				
					if($usersResult['User']['id']==$user_id) {
						$found=true;
						unset($user_ids[$numResult2]);
						unset($usersResults[$numResult]);	
						array_push($newUsersResults, $usersResult);
						break;
					}
				} // loops user_ids
	
			} // loops Users
		}
		
		self::d($newUsersResults, $debug);

        return $newUsersResults;
	}	

	/*
	 * block 0 / 1
	 */
	public function getAllUsers($user, $debug=false) {
         
		if(is_object($user)) 
			$organization_id = $user->organization['Organization']['id'];
		else 
			$organization_id = $user;
		
		$options = [];
		$options['conditions'] = ['User.organization_id' => $organization_id];
		$options['order'] = Configure::read('orderUser');
		$options['recursive'] = 0;

		$usersResults = $this->find('all', $options);

		self::d($options, $debug);
		self::d($usersResults, $debug);

        return $usersResults;
	}
		
	public function getUsersToMail($user, $debug=false) {
		
		$usersResults = [];

		if(Configure::read('mail.users.testing')) {
			$usersResults[0]['User'] = [];
			$usersResults[0]['User']['email'] = 'francesco.actis@gmail.com';
			$usersResults[0]['UserProfile']['email'] = null;
			$usersResults[0]['User']['name'] = 'Francesco & Sara';
			$usersResults[0]['User']['username'] = 'fractis@libero.it';
			$usersResults[0]['User']['id'] = 2798;	
			return $usersResults;	
		}

		if(is_object($user)) 
			$organization_id = $user->organization['Organization']['id'];
		else 
			$organization_id = $user;
		
		$this->bindModel(['belongsTo' => 
				['UserProfile' => 
					['className' => 'UserProfile',
									'foreignKey' => '',
									'conditions' => "UserProfile.user_id = User.id and UserProfile.profile_key = 'profile.email'",
									'fields' => 'UserProfile.profile_value']]]);
	
		$options = [];
		$options['conditions'] = ['User.organization_id' => $organization_id,
								  'User.block'=> 0];
		$options['fields'] = ['User.id','User.name','User.email','User.username', 'UserProfile.profile_value as email'];
		$options['order'] = Configure::read('orderUser');
		$options['recursive'] = 0;

		$usersResults = $this->find('all', $options);

		self::d($options, $debug);
		self::d($usersResults, $debug);

        return $usersResults;
	}	
	
	/*
	 * ottieni gli Users e i Groups associati
	 *
	 * se non filtro x group_id lo user e' ripetuto
	* */
	public function getUsers($user, $conditions=[], $orderBy=null, $debug=false) {

		if(empty($orderBy)) $orderBy = Configure::read('orderUser');
		
		/* *****************************************************************
		 * precedente versione, UserGroupMap non filtrava per organization_id
		 $results = [];
		    
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
		
		$results = [];
		$sql = "SELECT 
					User.organization_id, User.id, User.name, User.username, User.email, User.lastVisitDate, 
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
		
		if(isset($conditions['User.id NOT IN']))
			$sql .= " AND User.id NOT IN ".$conditions['User.id NOT IN'];
		if(isset($conditions['User.email NOT .portalgas.it']))
			$sql .= " AND User.email NOT LIKE '%".$conditions['User.email NOT .portalgas.it']."'";
		if(isset($conditions['UserGroupMap.group_id']))
			$sql .= " AND UserGroup.id = ".$conditions['UserGroupMap.group_id'];
		if(isset($conditions['UserGroup.id']))
			$sql .= " AND UserGroup.id = ".$conditions['UserGroup.id'];
		if(isset($conditions['UserGroupMap.group_id IN']))
			$sql .= " AND UserGroup.id IN ".$conditions['UserGroupMap.group_id IN'];	
		if(isset($conditions['UserGroupMap.group_id NOT IN']))
			$sql .= " AND UserGroup.id NOT IN ".$conditions['UserGroupMap.group_id NOT IN'];		
		if(isset($conditions['UserGroupMap.user_id NOT IN']))
			$sql .= " AND UserGroupMap.user_id NOT IN ".$conditions['UserGroupMap.user_id NOT IN'];		
		if(isset($conditions['UserGroupMap.user_id IN']))
			$sql .= " AND UserGroupMap.user_id IN ".$conditions['UserGroupMap.user_id IN'];	
		
		$sql .= ' ORDER BY '.$orderBy;
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
	 	$results = []; 
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
	 	 	 
		$results = [];
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
		self::d($sql, false);
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
	 public function getUsersList($user, $conditions, $orderBy=null, $label_fields = ['name']) {
	 
	  	$results = $this->getUsers($user, $conditions, $orderBy);
	 	
	 	$resultsList = [];
	 	foreach($results as $user) {
			$label = "";
			foreach ($label_fields as $label_field)
				$label .= $user['User'][$label_field].' ';
			
			$label = substr($label, 0, (strlen($label)-1));
			$resultsList[$user['User']['id']] = $label;
	 	}
	 	
		// debug($resultsList);
	 	
	 	return $resultsList;
	 }
	 
	 /*
	  * Ajax::admin_box_users con $reportOptions=='report-users-cart'
	  * 	estrae solo gli users che hanno effettuato acquisti in base all'ordine
	  */
	 public function getUserWithCartByOrder($user, $conditions, $orderBy=null, $debug=false) {

	 	self::d($conditions, $debug); 
	 	
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
		if(isset($conditions['ArticlesOrder.article_ids']))   $sql .= " and ArticlesOrder.article_id IN (".$conditions['ArticlesOrder.article_ids'].")";
		$sql .= " GROUP BY User.id, User.name, User.username, User.email ";
		$sql .= " ORDER BY ".$orderBy;
		self::d($sql, $debug); 
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
	 	self::d($sql, false);
	 	$results = $this->query($sql);
	 
	 	return $results;
	 }
	 
	 /*
	  *    estrae solo gli users che hanno effettuato acquisti in base alla consegna
	  *
	  *    $modalita = CRON se metodo richiamato da UtilsCrons::mailUsersDelivery() perche' esclude jimport()
	  */
	 public function getUserWithCartByDelivery($user, $conditions, $orderBy=null, $modalita='', $debug=false) {
	 	
	 	self::d('getUserWithCartByDelivery', $debug);
	 	self::d($conditions, $debug); 
	 		 	 
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
 					and Cart.user_id = User.id 
					and User.block = 0 
	 				and ArticlesOrder.order_id = `Order`.id
					and `Order`.delivery_id = Delivery.id ";
 		if(isset($conditions['Delivery.id']))   $sql .= " and Delivery.id = ".$conditions['Delivery.id'];
 		$sql .= " GROUP BY User.id, User.name, User.username, User.email ";
 		$sql .= " ORDER BY ".$orderBy;
 		self::d($sql, $debug);
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
							and User.block = 0
						ORDER BY SuppliersOrganization.name ";
	 			self::d($sql, $debug);
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

	 	self::d($results, $debug);
		
		return $results;
	 }

	 /*
	  *    estrae solo gli users che hanno effettuato acquisti in base alla consegna
	  *		filtrando per gli ordini di cui lo user e' referente $this->user->get('ACLsuppliersIdsOrganization')
	  *		(per ex group_id_referent_cassiere)
	  */
	 public function getUserWithCartByDeliveryACLReferent($user, $conditions, $orderBy=null,  $debug=false) {
	 	
	 	self::d('getUserWithCartByDeliveryACLReferent', $debug);
		self::d($conditions, $debug);
			 		 	 
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
					and User.block = 0 
	 				and ArticlesOrder.order_id = `Order`.id
					and `Order`.delivery_id = Delivery.id 
					and `Order`.supplier_organization_id = SuppliersOrganization.id 
					and SuppliersOrganization.id IN (".$user->get('ACLsuppliersIdsOrganization').")
					";
 		if(isset($conditions['Delivery.id']))   $sql .= " and Delivery.id = ".$conditions['Delivery.id'];
 		$sql .= " GROUP BY User.id, User.name, User.username, User.email ";
 		$sql .= " ORDER BY ".$orderBy;
 		self::d($sql, $debug);
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
							and User.block = 0
						ORDER BY SuppliersOrganization.name ";
	 			self::d($sql, $debug);
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

	 	self::d($results, $debug);
		
		return $results;
	 }

	 /*
	  * original code /administrator/components/com_users/models/user.php
	 * 	associo un utente alla tabella joomla.user_usergroup_map con il gruppo passato (ex gasReferente)
	 * 	cancello un utente con il gruppo passato (ex gasReferente) dalla tabella joomla.user_usergroup_map
	 * */
	 public function joomlaBatchUser($group_id, $user_id, $action, $debug=false)
	 {
	 	self::d('joomlaBatchUser action '.$action, $debug);
	 	
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
	 		$query->columns([$db->quoteName('user_id'), $db->quoteName('group_id')]);
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
	 
	 /*
	  * call Deliveries::tabsUserCartPreview()
	  */
	 public function getUsernameToUsernameCrypted($usernameCrypted) {
	 	 
		$salt = Configure::read('Security.salt');
			
		/* 
		 * To Decrypt:
		 * php 7.4 non supportato
		 * $username = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($usernameCrypted), MCRYPT_MODE_ECB));
	 	*/
        try {
	 	    $username = $this->utilsCommons->decoding($usernameCrypted);
        } catch (Exception $e) {
            CakeLog::write('error', 'getUsernameToUsernameCrypted '.$usernameCrypted);
            CakeLog::write('error', $e);
        }
		
	 	return $username;
	 }
	 
	 public $hasMany = [
		'Cart' => [
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
		],
		'SuppliersOrganizationsReferent' => [
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
		],
		'UserGroupMap' => [
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
		]
	];
}