<?php
App::uses('AppModel', 'Model');


class MonitoringSuppliersOrganization extends AppModel {
        
    /*
     * call Cron::mailMonitoringSuppliersOrganizationsOrdersDataFine
     */
    public function mail_order_data_fine($organization_id, $debug = false) {

        try {
            echo date("d/m/Y") . " - " . date("H:i:s") . " Mail ai referenti degli ordini scaduti e che hanno i produttori monitorati\n";

			/*
			 * se richiamo getObjUserLocal di myAppModel dal cron non funziona
			 */
            $user = $this->_getObjUserLocal($organization_id, $debug);
          
            App::import('Model', 'Order');
            $Order = new Order;

            App::import('Model', 'SuppliersOrganizationsReferent');
            $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

            App::import('Model', 'Mail');
            $Mail = new Mail;
                    
			$Email = $Mail->getMailSystem($user);
					
            $sql = "SELECT 
                        `Order`.*, Delivery.*, SuppliersOrganization.name, Supplier.descrizione, Supplier.img1   
                   FROM 
                        " . Configure::read('DB.prefix') . "orders `Order`,
                        " . Configure::read('DB.prefix') . "deliveries Delivery,  
                        " . Configure::read('DB.prefix') . "suppliers_organizations SuppliersOrganization,  
                        " . Configure::read('DB.prefix') . "suppliers Supplier, 
                        " . Configure::read('DB.prefix') . "monitoring_suppliers_organizations MonitoringSuppliersOrganization 
                   WHERE 
                        `Order`.organization_id = $organization_id
                        and Delivery.organization_id = $organization_id
                        and SuppliersOrganization.organization_id = $organization_id 
                        and MonitoringSuppliersOrganization.organization_id = $organization_id 
                        and `Order`.delivery_id = Delivery.id
                        and SuppliersOrganization.id = `Order`.supplier_organization_id 
                        and Supplier.id = SuppliersOrganization.supplier_id 
                        and SuppliersOrganization.stato = 'Y'
                        and (`Order`.data_fine = CURDATE()-1 or  `Order`.data_fine_validation = CURDATE()-1)  
                        and  `Order`.state_code != 'CREATE-INCOMPLETE' 
                        and `Order`.isVisibleFrontEnd = 'Y'  and `Order`.isVisibleFrontEnd = 'Y' 
                        and Delivery.isVisibleFrontEnd = 'Y' and Delivery.isVisibleFrontEnd = 'Y' 
                        and MonitoringSuppliersOrganization.supplier_organization_id = SuppliersOrganization.id
                        and MonitoringSuppliersOrganization.mail_order_data_fine = 'Y' 
                        order by Delivery.data, Supplier.name ";
            self::d($sql, $debug);
            $orderResults = $Order->query($sql);

            if (!empty($orderResults)) {
                echo "Trovati " . count($orderResults) . " ordini \n";

                foreach ($orderResults as $orderResult) {
                    /*
                    echo "<pre>";
                    print_r($orderResult);
                    echo "</pre>";
                    */
                    if ($debug)
                        echo "\n" . 'Ordine ' . $orderResult['SuppliersOrganization']['name'] . ' (' . $orderResult['Order']['id'] . ") \n";
                    
                    $subject_mail = "ATTENZIONE: inviare ordine a ".$orderResult['SuppliersOrganization']['name'];
                    $Email->subject($subject_mail);

                    $body_mail_final = "";
                    $body_mail_final .= "<br />";
                    $body_mail_final .= "L'ordine ";
                    $body_mail_final .= "<b>" . $orderResult['SuppliersOrganization']['name'] . '</b>';
                    $body_mail_final .= ' è terminato oggi: ricordati di <b>elaborarlo</b> e di <b>inviarlo</b> al produttore.';
                    if($debug)
                        echo "\n" . $body_mail_final;

                    /*
                     * estraggo i referenti
                     */
                    $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

                    $conditions = ['User.block' => 0,
                                   'SuppliersOrganization.id' => $orderResult['Order']['supplier_organization_id']];
                    $results = $this->getReferentsCompact($user, $conditions, null, 'CRON');
                    self::d($results, $debug);

                    foreach ($results as $numResult => $result) {

                        $mail = $result['User']['email'];
                        $name = $result['User']['name'];

                        $username = $result['User']['username'];

                        echo "\n" . $numResult . ") tratto l'utente " . $name . ', username ' . $username;

						$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
						$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->_traslateWww($user->organization['Organization']['www']))));

						if ($numResult == 0)
							echo $body_mail_final;

						$mailResults = $Mail->send($Email, $mail, $body_mail_final, $debug);

                    } // end loop users
                
                } // loop orders
            } // end if (!empty($orderResults)) 
        } catch (Exception $e) {
            echo '<br />UtilsCrons::mailMonitoringSuppliersOrganizationsOrdersDataFine()<br />' . $e;
        }
    }
	
	private function getReferentsCompact($user, $conditions, $orderBy=null, $modalita='') {
		
		$results = [];
		
		if(empty($orderBy)) $orderBy = Configure::read('orderUser');
		
		// in profile.phone elimino i ""
		$sql = "SELECT 
					User.organization_id, User.id, User.username, User.name, User.email, 
					SuppliersOrganizationsReferent.type, SuppliersOrganizationsReferent.group_id, 
					SuppliersOrganization.name, SuppliersOrganization.frequenza  
				FROM 
					".Configure::read('DB.portalPrefix')."users User,
					".Configure::read('DB.prefix')."suppliers_organizations_referents SuppliersOrganizationsReferent,
					".Configure::read('DB.prefix')."suppliers_organizations SuppliersOrganization
				WHERE 
					User.organization_id = ".(int)$user->organization['Organization']['id']." 
					and SuppliersOrganization.organization_id = ".(int)$user->organization['Organization']['id']."
					and SuppliersOrganizationsReferent.organization_id =  ".(int)$user->organization['Organization']['id']."
					and SuppliersOrganizationsReferent.user_id = User.id
					and SuppliersOrganizationsReferent.supplier_organization_id = SuppliersOrganization.id ";
		if(isset($conditions['User.block'])) $sql .= " and User.block = ".$conditions['User.block'];  // 0 attivo
		if(isset($conditions['SuppliersOrganization.id'])) $sql .= " and SuppliersOrganization.id = ".$conditions['SuppliersOrganization.id'];  // filtro per produttore
		if(isset($conditions['SuppliersOrganizationsReferent.group_id'])) $sql .= " and SuppliersOrganizationsReferent.group_id = ".$conditions['SuppliersOrganizationsReferent.group_id'];  // filtro per gruppo
		if(isset($conditions['SuppliersOrganizationsReferent.type'])) $sql .= " and SuppliersOrganizationsReferent.type = '".$conditions['SuppliersOrganizationsReferent.type']."'"; 
		$sql .= " ORDER BY ".$orderBy;
		self::d($sql, false);
		try {
			$results = $this->query($sql);

			App::import('Model', 'UserGroup');
				
			//if($modalita != 'CRON') jimport( 'joomla.user.helper' );
			//require_once(Configure::read('App.root').'/libraries/joomla/user/helper.php');

			foreach($results as $numResult => $result) {
				
				/*
				 * userprofile
				if($modalita != 'CRON') {
					$result['User']['id'];
					$userTmp = JFactory::getUser($result['User']['id']);
					$userProfile = JUserHelper::getProfile($userTmp->id);
					$results[$numResult]['Profile'] = $userProfile->profile;
				}
				*/
				
				/*
				 * ruolo
				*/
				$UserGroup = new UserGroup;
					
				$options = [];
				$options['conditions'] =['UserGroup.id' => $result['SuppliersOrganizationsReferent']['group_id']];
				$options['recursive'] = -1;
				$userGroupResults = $UserGroup->find('first', $options);				
				$group_name = $userGroupResults['UserGroup']['title'];
				
				$results[$numResult]['SuppliersOrganizationsReferent']['UserGroups']['name'] = $group_name;
				$results[$numResult]['SuppliersOrganizationsReferent']['UserGroups']['descri'] = $this->userGroups[$userGroupResults['UserGroup']['id']]['descri'];
			}
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}

		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
		
		return $results;
	}
	
    public $validate = array(
        'organization_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
            ),
        ),
        'supplier_organization_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
            ),
        ),
        'user_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
            ),
        ),
    );
    public $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',
            'conditions' => 'User.organization_id = MonitoringSuppliersOrganization.organization_id',
            'fields' => '',
            'order' => ''
        ),
        'SuppliersOrganization' => array(
            'className' => 'SuppliersOrganization',
            'foreignKey' => 'supplier_organization_id',
            'conditions' => 'SuppliersOrganization.organization_id = MonitoringSuppliersOrganization.organization_id',
            'fields' => '',
            'order' => ''
        ),
    );

}
