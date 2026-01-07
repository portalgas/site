<?php
App::uses('AppModel', 'Model');

class MailsSend extends AppModel {

	public $useTable = 'deliveries';
	public $actsAs = ['Data'];
	public $virtualFields = ['luogoData' => "CONCAT_WS(' - ',Doc.luogo,DATE_FORMAT(Doc.data, '%W, %e %M %Y'))"];

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

	public function mailTest($organization_id, $user, $debug=false) {

		$debug = true;
		/*
		 * dati produttore
		 */
		App::import('Model', 'Order');
		$Order = new Order;
		$options =  [];
		$options['conditions'] = ['Order.organization_id' => $organization_id];
		$options['recursive'] = 1;
		$order = $this->Order->find('first', $options); 
		self::d($order['Order'], $debug);
		self::d($order['SuppliersOrganization'], $debug);
		self::d($order['Delivery'], $debug);
    
		/*
		 * dati mail
		 */
		$subject_mail = 'Test mail';
		$mail_destinatari= ['francesco.actis@gmail.com'];
		
		App::import('Model', 'Mail');
		$Mail = new Mail;

		$options = [];
		$options['template'] = 'confirm_after_incoming_orders_open';
		$options['layout'] = 'default';
		$Email = $Mail->getMailSystem($user, $options);
		$Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->_traslateWww($user->organization['Organization']['www']))]);
		$Email->viewVars(['order' => $order]);
		$Email->viewVars(['user' => $user]);
		$Email->subject($subject_mail);

		if(!Configure::read('mail.send'))  $Email->transport('Debug'); // non invia la mail, verra' stampata a video
		
		$mailResults = $Mail->send($Email, $mail_destinatari, '', $debug);
	}

	/*
	 * invio mail 
	 * 		ordini che si aprono oggi
	 * 		ctrl data_inizio con data_oggi
	 * 		mail_open_send = Y (perche' in Order::add data_inizio = data_oggi)
	*/
	public function mailUsersOrdersOpen($organization_id, $user, $debug=false) {

		$debug = true;

		date_default_timezone_set('Europe/Rome');

		try {
			if($debug)
				echo date("d/m/Y")." - ".date("H:i:s")." Mail agli utenti degli ordini che aperti \n";

			App::import('Model', 'Mail');
			$Mail = new Mail;

			$options = [];
			$options['template'] = 'orders_open';
			$options['layout'] = 'default';
			$Email = $Mail->getMailSystem($user, $options);
			if(!Configure::read('mail.send'))  $Email->transport('Debug'); // non invia la mail, verra' stampata a video
			$Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->_traslateWww($user->organization['Organization']['www']))]);
			$Email->viewVars(['user' => $user]);

			App::import('Model', 'SuppliersOrganizationsReferent');
			$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

			App::import('Model', 'Order');
			$Order = new Order;

			App::import('Model', 'User');
			$User = new User;

			if($debug)
				echo "Estraggo gli ordini che apriranno tra ".(Configure::read('GGMailToAlertOrderOpen')+1)." giorni o con mail_open_send = Y \n";
			
			/*
			* prima di filtrare per users, ctrl che ci siano ordini da inviare
			*/
			$sql = $this->_getSqlUsersOrdersOpen($user, $organization_id, $debug);
			$orderCtrlResults = $Order->query($sql);  
			if(empty($orderCtrlResults)) {
				echo "\n non ci sono ordini che apriranno tra ".(Configure::read('GGMailToAlertOrderOpen')+1)." giorni \n";
				return true;
			}

			if($debug) echo "\nTrovati ".count($orderCtrlResults)." ordini \n";

			/*
			* ciclo UTENTI, se Configure::read('mail.users.testing') restituisce francesco.actis@gmail.com
			*/
			$usersResults = $User->getUsersToMail($organization_id);
			foreach($usersResults as $numResult => $usersResult) {
				$mail = $usersResult['User']['email'];
				$mail2 = $usersResult['UserProfile']['email'];
				$name = $usersResult['User']['name'];
				$username = $usersResult['User']['username'];
				
				$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);

				if($debug)
					echo "<br />\n".$numResult.") tratto l'utente ".$name.', username '.$username." \n";

					/*
					* ordini filtrati per users, ctrl che ci siano ordini da inviare
					*/
					$sql = $this->_getSqlUsersOrdersOpenByUser($user, $organization_id, $usersResult['User']['id'], $debug);
					$orderResults = $Order->query($sql);		
					if(!empty($orderResults)) { 
						if($debug)
							echo " \nTrovati ".count($orderResults)." ordini per lo user ".$usersResult['User']['username']." (".$usersResult['User']['id'].") \n";

						/*
						 * per ogni ordine aggiungo i referenti
						 */
						foreach($orderResults as $numResultOrder => $orderResult) {
							$conditions = [];
							$conditions['SuppliersOrganization.id'] = $orderResult['Order']['supplier_organization_id'];
							$referenti = $SuppliersOrganizationsReferent->getReferentsCompact($user, $conditions, null, 'CRON');
							$orderResults[$numResultOrder]['SuppliersOrganizationsReferents'] = $referenti;
						}
						
						$Email->viewVars(['orders' => $orderResults]);
						$Email->viewVars(['utente' => $usersResult]);

						if(count($orderResults)==1) 
							$subject_mail = $orderResults[0]['SupplierOrganization']['name'].", ordine che si apre oggi";
						else 
							$subject_mail = $this->_organizationNameError($user->organization).", ordini che si aprono oggi";								
						$Email->subject($subject_mail);
						$mailResults = $Mail->send($Email, [$mail2, $mail], "", $debug);
					}   
					else {
						if($debug) echo "Per lo user ".$usersResult['User']['username']." (".$usersResult['User']['id'].") non ci sono ordini da inviare \n";                      
					}
				} // loops Users
				
				/*
					* per gli ordini trovati 
					* UPDATE Order.mail_open_send, Order.mail_open_data
					*/
				foreach ($orderCtrlResults as $orderCtrlResult) {
					$sql ="UPDATE ".Configure::read('DB.prefix')."orders 
							SET
								mail_open_send = 'N',
								mail_open_data = '".date('Y-m-d H:i:s')."'
							WHERE
								organization_id = $organization_id
								and id = ".$orderCtrlResult['Order']['id'];
					self::d($sql, $debug);
					$Order->query($sql);
				}
		}
		catch (Exception $e) {
			echo '<br />UtilsCrons::mailUsersOrdersOpen()<br />'.$e;
		}                    
	}

	public function mailUsersOrdersClose($organization_id, $user, $debug=false) {
		
		$debug = true;

        date_default_timezone_set('Europe/Rome');

		try {
				echo date("d/m/Y")." - ".date("H:i:s")." Mail agli utenti degli ordini che si chiuderanno tra ".(Configure::read('GGMailToAlertOrderClose')+1)." giorni \n";

				App::import('Model', 'Mail');
				$Mail = new Mail;

				$options = [];
				$options['template'] = 'orders_close';
				$options['layout'] = 'default';
				$Email = $Mail->getMailSystem($user, $options);
				if(!Configure::read('mail.send'))  $Email->transport('Debug'); // non invia la mail, verra' stampata a video
				$Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->_traslateWww($user->organization['Organization']['www']))]);
				$Email->viewVars(['user' => $user]);

				App::import('Model', 'SuppliersOrganizationsReferent');
				$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
				
				App::import('Model', 'Order');
				$Order = new Order;

				App::import('Model', 'User');
				$User = new User;
		
				if($debug)
					echo "Estraggo gli ordini che chiuderanno tra ".(Configure::read('GGMailToAlertOrderClose')+1)." giorni \n";
				
				/*
				* prima di filtrare per users, ctrl che ci siano ordini da inviare
				*/    
				$sql = $this->_getSqlUsersOrdersClose($user, $organization_id, $debug);                
                $orderCtrlResults = $Order->query($sql);
				
				if(!empty($orderCtrlResults)) {
					if($debug)
						echo "Trovati ".count($orderCtrlResults)." ordini \n";

					/*
					* ciclo UTENTI, se Configure::read('mail.users.testing') restituisce francesco.actis@gmail.com
					*/
					$usersResults = $User->getUsersToMail($organization_id);	
					foreach($usersResults as $numResult => $usersResult) {

						$mail = $usersResult['User']['email'];
						$mail2 = $usersResult['UserProfile']['email'];
						$name = $usersResult['User']['name'];
						$username = $usersResult['User']['username'];
					
						$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
						
						if($debug)
							echo '<br />'.$numResult.") tratto l'utente ".$name.', username '.$username." \n";

						/*
							* ordini filtrati per users, ctrl che ci siano ordini da inviare
							*/
						$sql = $this->_getSqlUsersOrdersCloseByUser($user, $organization_id, $usersResult['User']['id'], $debug);     
						self::d($sql, $debug);
						$orderResults = $Order->query($sql);
						if(!empty($orderResults)) { 
							if($debug)
								echo "Trovati ".count($orderResults)." ordini per lo user ".$usersResult['User']['username']." (".$usersResult['User']['id'].") \n";

							/*
							* per ogni ordine aggiungo i referenti
							*/
							foreach($orderResults as $numResultOrder => $orderResult) {
								$conditions = [];
								$conditions['SuppliersOrganization.id'] = $orderResult['Order']['supplier_organization_id'];
								$referenti = $SuppliersOrganizationsReferent->getReferentsCompact($user, $conditions, null, 'CRON');
								$orderResults[$numResultOrder]['SuppliersOrganizationsReferents'] = $referenti;
							}
															
							$Email->viewVars(['orders' => $orderResults]);
							$Email->viewVars(['utente' => $usersResult]);

							if(count($orderResults)==1) 
								$subject_mail = $orderResults[0]['SupplierOrganization']['name'].", ordine che si chiuderà tra ".(Configure::read('GGMailToAlertOrderClose')+1)." giorni";
							else 
								$subject_mail = $this->_organizationNameError($user->organization).", ordini che si chiuderanno tra ".(Configure::read('GGMailToAlertOrderClose')+1)." giorni";
							$Email->subject($subject_mail);									
								
							$mailResults = $Mail->send($Email, [$mail2, $mail], "", false);
						}   
						else { 
							if($debug) echo "Per lo user ".$usersResult['User']['username']." (".$usersResult['User']['id'].") non ci sono ordini da inviare \n";
						}
							
					} // loops users 
					
					/*
					* per gli ordini trovati 
					* UPDATE Order.mail_open_send, Order.mail_open_data
					*/
					foreach ($orderCtrlResults as $orderCtrlResult) {
						$sql ="UPDATE ".Configure::read('DB.prefix')."orders 
								SET 
									mail_close_data = '".date('Y-m-d H:i:s')."'
								WHERE 
									organization_id = $organization_id 
									and id = ".$orderCtrlResult['Order']['id'];
						self::d($sql, $debug);
						$Order->query($sql);
					}
                        
				} // end if(!empty($orderCtrlResults))
				else 
					echo "\n non ci sono ordini che apriranno tra ".(Configure::read('GGMailToAlertOrderClose')+1)." giorni \n";
		}
		catch (Exception $e) {
			echo '<br />UtilsCrons::mailUsersOrdersClose()<br />'.$e;
		}			
	}

	private function _getSqlUsersOrdersOpen($user, $organization_id, $debug) {

		if(Configure::read('mail.users.testing')) {
			/*
			 * invio di test
			 * */
			$sql ="SELECT 
					`Order`.*,
					Delivery.*,
					SupplierOrganization.name, SupplierOrganization.frequenza,
					Supplier.descrizione, Supplier.img1    
				FROM 
					".Configure::read('DB.prefix')."orders `Order`,
					".Configure::read('DB.prefix')."deliveries Delivery,  
					".Configure::read('DB.prefix')."suppliers_organizations SupplierOrganization,  
					".Configure::read('DB.prefix')."suppliers Supplier  
				WHERE 
					`Order`.organization_id = $organization_id
					and Delivery.organization_id = $organization_id
					and SupplierOrganization.organization_id = $organization_id 
					and `Order`.delivery_id = Delivery.id
					and SupplierOrganization.id = `Order`.supplier_organization_id 
					and Supplier.id = SupplierOrganization.supplier_id 
					and SupplierOrganization.stato = 'Y'
					and SupplierOrganization.mail_order_open = 'Y'
					and `Order`.isVisibleFrontEnd = 'Y'  and Delivery.isVisibleFrontEnd = 'Y' 
					order by Delivery.data, Supplier.name ";
		}
		else {			
			$sql ="SELECT 
						`Order`.*,
						Delivery.*,
						SupplierOrganization.name, SupplierOrganization.frequenza,
						Supplier.descrizione, Supplier.img1    
					FROM 
						".Configure::read('DB.prefix')."orders `Order`,
						".Configure::read('DB.prefix')."deliveries Delivery,  
						".Configure::read('DB.prefix')."suppliers_organizations SupplierOrganization,  
						".Configure::read('DB.prefix')."suppliers Supplier  
					WHERE 
						`Order`.organization_id = $organization_id
						and Delivery.organization_id = $organization_id
						and SupplierOrganization.organization_id = $organization_id 
						and `Order`.delivery_id = Delivery.id
						and SupplierOrganization.id = `Order`.supplier_organization_id 
						and Supplier.id = SupplierOrganization.supplier_id 
						and SupplierOrganization.stato = 'Y'
						and SupplierOrganization.mail_order_open = 'Y'
						and `Order`.isVisibleFrontEnd = 'Y'  and Delivery.isVisibleFrontEnd = 'Y' 
						and  `Order`.state_code != 'CREATE-INCOMPLETE' and `Order`.state_code != 'CLOSE'
						and (`Order`.data_inizio = CURDATE() - INTERVAL ".Configure::read('GGMailToAlertOrderOpen')." DAY OR `Order`.mail_open_send = 'Y')	
						order by Delivery.data, Supplier.name ";
		}
		self::d($sql, $debug);
		return $sql;
	}
	
	private function _getSqlUsersOrdersOpenByUser($user, $organization_id, $user_id, $debug) {
		if(Configure::read('mail.users.testing')) {
			/*
			 * invio di test
			 * */
			$sql ="SELECT 
						`Order`.*,
						Delivery.*,
						SupplierOrganization.name, SupplierOrganization.frequenza,
						Supplier.descrizione, Supplier.img1    
					FROM 
						".Configure::read('DB.prefix')."orders `Order`,
						".Configure::read('DB.prefix')."deliveries Delivery,  
						".Configure::read('DB.prefix')."suppliers_organizations SupplierOrganization,  
						".Configure::read('DB.prefix')."suppliers Supplier  
						WHERE 
						`Order`.organization_id = $organization_id
						and Delivery.organization_id = $organization_id
						and SupplierOrganization.organization_id = $organization_id 
						and `Order`.delivery_id = Delivery.id
						and SupplierOrganization.id = `Order`.supplier_organization_id 
						and Supplier.id = SupplierOrganization.supplier_id 
						and SupplierOrganization.stato = 'Y'
						and SupplierOrganization.mail_order_open = 'Y'
						and `Order`.isVisibleFrontEnd = 'Y' and Delivery.isVisibleFrontEnd = 'Y' 
						and `Order`.supplier_organization_id not in (
						SELECT supplier_organization_id 
						FROM ".Configure::read('DB.prefix')."bookmarks_mails where user_id = $user_id and organization_id = $organization_id and order_open = 'N')

						order by Delivery.data, Supplier.name "; 
		}
		else {
			$sql ="SELECT 
					`Order`.*,
					Delivery.*,
					SupplierOrganization.name, SupplierOrganization.frequenza,
					Supplier.descrizione, Supplier.img1    
				FROM 
					".Configure::read('DB.prefix')."orders `Order`,
					".Configure::read('DB.prefix')."deliveries Delivery,  
					".Configure::read('DB.prefix')."suppliers_organizations SupplierOrganization,  
					".Configure::read('DB.prefix')."suppliers Supplier  
				WHERE 
					`Order`.organization_id = $organization_id
					and Delivery.organization_id = $organization_id
					and SupplierOrganization.organization_id = $organization_id 
					and `Order`.delivery_id = Delivery.id
					and SupplierOrganization.id = `Order`.supplier_organization_id 
					and Supplier.id = SupplierOrganization.supplier_id 
					and SupplierOrganization.stato = 'Y'
					and SupplierOrganization.mail_order_open = 'Y'
					and `Order`.isVisibleFrontEnd = 'Y' and Delivery.isVisibleFrontEnd = 'Y' 
					and  `Order`.state_code != 'CREATE-INCOMPLETE' and `Order`.state_code != 'CLOSE' 
					and (`Order`.data_inizio = CURDATE() - INTERVAL ".Configure::read('GGMailToAlertOrderOpen')." DAY OR `Order`.mail_open_send = 'Y')

					and `Order`.supplier_organization_id not in (
						SELECT supplier_organization_id 
						FROM ".Configure::read('DB.prefix')."bookmarks_mails where user_id = $user_id and organization_id = $organization_id and order_open = 'N')
					order by Delivery.data, Supplier.name "; 			
		}
		self::d($sql, $debug);
		return $sql;		
	}

	private function _getSqlUsersOrdersClose($user, $organization_id, $debug) {
		if(Configure::read('mail.users.testing')) {
			/*
			 * invio di test
			 * */
			$sql ="SELECT 
						`Order`.*,
						Delivery.*,
						SupplierOrganization.name, SupplierOrganization.frequenza,
						Supplier.descrizione, Supplier.img1   
					FROM 
						".Configure::read('DB.prefix')."orders `Order`,
						".Configure::read('DB.prefix')."deliveries Delivery,  
						".Configure::read('DB.prefix')."suppliers_organizations SupplierOrganization,  
						".Configure::read('DB.prefix')."suppliers Supplier  
						WHERE 
						`Order`.organization_id = $organization_id
						and Delivery.organization_id = $organization_id
						and SupplierOrganization.organization_id = $organization_id 
						and `Order`.delivery_id = Delivery.id
						and SupplierOrganization.id = `Order`.supplier_organization_id 
						and Supplier.id = SupplierOrganization.supplier_id 
						and SupplierOrganization.stato = 'Y'
						and SupplierOrganization.mail_order_close = 'Y'
						and `Order`.state_code != 'CREATE-INCOMPLETE' and `Order`.state_code != 'CLOSE'
						and `Order`.isVisibleFrontEnd = 'Y' 
						and Delivery.isVisibleFrontEnd = 'Y' 
						order by Delivery.data, Supplier.name ";			
		}
		else {
			$sql ="SELECT 
						`Order`.*,
						Delivery.*,
						SupplierOrganization.name, SupplierOrganization.frequenza,
						Supplier.descrizione, Supplier.img1   
					FROM 
						".Configure::read('DB.prefix')."orders `Order`,
						".Configure::read('DB.prefix')."deliveries Delivery,  
						".Configure::read('DB.prefix')."suppliers_organizations SupplierOrganization,  
						".Configure::read('DB.prefix')."suppliers Supplier  
					WHERE 
						`Order`.organization_id = $organization_id
						and Delivery.organization_id = $organization_id
						and SupplierOrganization.organization_id = $organization_id 
						and `Order`.delivery_id = Delivery.id
						and SupplierOrganization.id = `Order`.supplier_organization_id 
						and Supplier.id = SupplierOrganization.supplier_id 
						and SupplierOrganization.stato = 'Y'
						and SupplierOrganization.mail_order_close = 'Y'
						and `Order`.data_fine = CURDATE() + INTERVAL ".Configure::read('GGMailToAlertOrderClose')." DAY 
						and  `Order`.state_code != 'CREATE-INCOMPLETE' and `Order`.state_code != 'CLOSE'
						and `Order`.isVisibleFrontEnd = 'Y' 
						and Delivery.isVisibleFrontEnd = 'Y' 
						order by Delivery.data, Supplier.name ";
		}
		self::d($sql, $debug);
		return $sql;			
	} 
	
	private function _getSqlUsersOrdersCloseByUser($user, $organization_id, $user_id, $debug) {
		if(Configure::read('mail.users.testing')) {
			/*
			 * invio di test
			 * */	
			$sql ="SELECT 
					`Order`.*,
					Delivery.*,
					SupplierOrganization.name, SupplierOrganization.frequenza,
					Supplier.descrizione, Supplier.img1   
				FROM 
					".Configure::read('DB.prefix')."orders `Order`,
					".Configure::read('DB.prefix')."deliveries Delivery,  
					".Configure::read('DB.prefix')."suppliers_organizations SupplierOrganization,  
					".Configure::read('DB.prefix')."suppliers Supplier  
					WHERE 
					`Order`.organization_id = $organization_id
					and Delivery.organization_id = $organization_id
					and SupplierOrganization.organization_id = $organization_id 
					and `Order`.delivery_id = Delivery.id
					and SupplierOrganization.id = `Order`.supplier_organization_id 
					and Supplier.id = SupplierOrganization.supplier_id 
					and SupplierOrganization.stato = 'Y'
					and SupplierOrganization.mail_order_close = 'Y'
					and  `Order`.state_code != 'CREATE-INCOMPLETE' 
					and `Order`.isVisibleFrontEnd = 'Y'  and `Order`.isVisibleFrontEnd = 'Y' 
					and Delivery.isVisibleFrontEnd = 'Y' and Delivery.isVisibleFrontEnd = 'Y'
					and `Order`.supplier_organization_id not in (
					SELECT supplier_organization_id 
					FROM ".Configure::read('DB.prefix')."bookmarks_mails where user_id = $user_id and organization_id = $organization_id and order_close = 'N')
					
					order by Delivery.data, Supplier.name ";
		}
		else {
			$sql ="SELECT 
					`Order`.*,
					Delivery.*,
					SupplierOrganization.name, SupplierOrganization.frequenza,
					Supplier.descrizione, Supplier.img1   
			FROM 
					".Configure::read('DB.prefix')."orders `Order`,
					".Configure::read('DB.prefix')."deliveries Delivery,  
					".Configure::read('DB.prefix')."suppliers_organizations SupplierOrganization,  
					".Configure::read('DB.prefix')."suppliers Supplier  
			WHERE 
					`Order`.organization_id = $organization_id
					and Delivery.organization_id = $organization_id
					and SupplierOrganization.organization_id = $organization_id 
					and `Order`.delivery_id = Delivery.id
					and SupplierOrganization.id = `Order`.supplier_organization_id 
					and Supplier.id = SupplierOrganization.supplier_id 
					and SupplierOrganization.stato = 'Y'
					and SupplierOrganization.mail_order_close = 'Y'
					and `Order`.data_fine = CURDATE() + INTERVAL ".Configure::read('GGMailToAlertOrderClose')." DAY 
					and  `Order`.state_code != 'CREATE-INCOMPLETE' 
					and `Order`.isVisibleFrontEnd = 'Y'  and `Order`.isVisibleFrontEnd = 'Y' 
					and Delivery.isVisibleFrontEnd = 'Y' and Delivery.isVisibleFrontEnd = 'Y'
					
					and `Order`.supplier_organization_id not in (
						SELECT supplier_organization_id 
						FROM ".Configure::read('DB.prefix')."bookmarks_mails where user_id = $user_id and organization_id = $organization_id and order_close = 'N')
							
					order by Delivery.data, Supplier.name ";
		}
		
		self::d($sql, $debug);
		return $sql;							
	}
}