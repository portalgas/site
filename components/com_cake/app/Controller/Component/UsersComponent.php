<?php 
App::uses('Component', 'Controller');

class UsersComponent extends Component {

    private $Controller = null;

    public function initialize(Controller $controller) 
    {
		$this->Controller = $controller;
    }
	
	public function setUser($user, $debug=false) {

		$controllerLog = $this->Controller;
		$this->addParamsJUser($user, $debug);
		
		// $controllerLog::d($user,$debug);
		
		return $user;
	}
	
	public function setUserDes($user, $debug=false) {

		if(!empty($user->des_id)) {
			$ACLsuppliersIdsDes = $this->_addParamsDesJUser($user, $debug);
			$user->set('ACLsuppliersIdsDes', $ACLsuppliersIdsDes); 
		}
		
		return $user;
	}
	
	public function setUserCash($user, $debug=false) {

		$user = $this->_addParamsCashJUser($user, $debug);
		
		return $user;
	}
	
	/*
	 * aggiungo i dati per il prepagati, x BO e FE
	 */
	private function _addParamsCashJUser($user) {

		$controllerLog = $this->Controller;
		
		/*
		 * configurazione Organization
		 */
		App::import('Model', 'Organization');
		$Organization = new Organization;
				 
		$options = [];
		$options['conditions'] = ['Organization.id' => $user->organization['Organization']['id']];
		$options['recursive'] = -1;
		$resultsOrganization = $Organization->find('first', $options);
		$paramsConfig = json_decode($resultsOrganization['Organization']['paramsConfig'], true);	
		$user->set('organization_cash_limit', $paramsConfig['cashLimit']);		
		$user->set('organization_cash_limit_label', __('FE-'.$paramsConfig['cashLimit']));
		$organization_limit_cash_after = $paramsConfig['limitCashAfter'];
		$user->set('organization_limit_cash_after', $organization_limit_cash_after);
		$user->set('organization_limit_cash_after_', number_format($organization_limit_cash_after ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));
		$user->set('organization_limit_cash_after_e', number_format($organization_limit_cash_after ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;');

	
		/*
		 * totale cassa
		 */
		App::import('Model', 'Cash');
		$Cash = new Cash;
		
		$user_cash = $Cash->getTotaleCashToUser($user, $user->id);
		$user->set('user_cash', $user_cash);
		$user->set('user_cash_', number_format($user_cash ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));
		$user->set('user_cash_e', number_format($user_cash ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;');
					
		/*
		 * gestione prepagato
		 */
         if($user->organization['Organization']['cashLimit']=='LIMIT-CASH-USER') {

				App::import('Model', 'CashesUser');
				$CashesUser = new CashesUser;
				
				$options = [];
				$options['conditions'] = ['CashesUser.organization_id' => $user->organization['Organization']['id'],
										  'CashesUser.user_id' => $user->id];
				$options['recursive'] = -1;
				$cashesUserResults = $CashesUser->find('first', $options);
				if(!empty($cashesUserResults)) {
					$user->set('user_limit_type', $cashesUserResults['CashesUser']['limit_type']);
					$user->set('user_limit_after', $cashesUserResults['CashesUser']['limit_after']);
					$user->set('user_limit_after_', $cashesUserResults['CashesUser']['limit_after_']);
					$user->set('user_limit_after_e', $cashesUserResults['CashesUser']['limit_after_e']);
				}					
				         
         } // end if($user->organization['Organization']['cashLimit']=='LIMIT-CASH-USER')
			 
		 return $user;
	}
	
    /*
     * JUser Object ( in libraries/joomla/user/user.php function load($id)
     * aggiungo [ACLsuppliersIdsOrganization], [ACLsuppliersIdsDes]
     * 
     * 	    [id] => 0
     * 	    [name] =>
     * 	    [username] =>
     * 	    [email] =>
     * 	    [params] =>
     * 	    [groups] => []
     * 	    [guest] => 1
     * 	    [organization] => []
     *
     *  private function addParamsJUser()  richiamata anche da OrganizationController:admin_choice 
     * 															OrganizationController:admin_edit
     * 															DesOrganizationController:admin_choice 
     *  	[ACLsuppliersIdsOrganization] = elenco degli ID dei produttori abilitati a gestire
     *  	[ACLsuppliersIdsDes] = elenco degli ID dei produttori del DES abilitati a gestire
     *   [hasArticlesOrder] = Gestisci gli articoli associati all'ordine
     */
    public function addParamsJUser($user) {

		$controllerLog = $this->Controller;
		
        /*
         * ACLsuppliersIdsOrganization    1, 3, 5  supplier_organization_id
         *    se Admin dell'organization
         * 			tutti suppliers_organizations.id dell'organization
         * 		se Referent
         * 			tutti suppliers_organizations.id associati allo user
         */
        $ACLsuppliersIdsOrganization = 0; // contiene stringa supplier_organization_id 1, 3, 5
        if($this->Controller->isSuperReferente()) {
            App::import('Model', 'SuppliersOrganization');
            $SuppliersOrganization = new SuppliersOrganization;

            $ACLsuppliersIdsOrganization = $SuppliersOrganization->getSuppliersOrganizationIds($user);
        } else {
            App::import('Model', 'SuppliersOrganizationsReferent');
            $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

            $ACLsuppliersIdsOrganization = $SuppliersOrganizationsReferent->getSuppliersOrganizationIdsByReferent($user, $user->get('id'));
        }
        $user->set('ACLsuppliersIdsOrganization', $ACLsuppliersIdsOrganization);

        /*
         * ctrl e' associato ad un solo DES 
         */
        if ($user->organization['Organization']['hasDes'] == 'Y') {
            App::import('Model', 'DesOrganization');
            $DesOrganization = new DesOrganization;

            $options = [];
            $options['conditions'] = ['DesOrganization.organization_id' => $user->organization['Organization']['id']]; // non ho scelto il DES, ctrl solo se il suo GAS e' titolare

            $options['fields'] = ['DesOrganization.des_id'];
            $options['recursive'] = -1;
            $desOrganizationResults = $DesOrganization->find('all', $options);

            if (count($desOrganizationResults) == 1) {
                /*
                 * e' associato a 1 solo DES
                 */
                $user->des_id = $desOrganizationResults[0]['DesOrganization']['des_id'];

				$ACLsuppliersIdsDes = $this->_addParamsDesJUser($user, $debug);
				$user->set('ACLsuppliersIdsDes', $ACLsuppliersIdsDes);
            }
        }

		/*
		 * aggiungo i dati per il prepagati, x BO e FE
		 */
		$user = $this->_addParamsCashJUser($user);
		
        /*
         * gestione degli articlesOrders, articolo associati agli ordini Y o N
         */
        if ($user->organization['Organization']['hasArticlesOrder'] == 'Y') {

            App::import('Model', 'User');
            $User = new User;

            $sql = "SELECT
							User.profile_key, User.profile_value
						FROM
						" . Configure::read('DB.portalPrefix') . "users Utente,
						" . Configure::read('DB.portalPrefix') . "user_profiles User
					WHERE
						User.user_id = Utente.id
						AND User.profile_key = 'profile.hasArticlesOrder'
						AND Utente.id = " . $user->get('id');
            if (!$this->Controller->isRoot())
                $sql .= " AND Utente.organization_id = " . (int) $user->organization['Organization']['id'];
            $controllerLog::d($sql, false);
            $results = $User->query($sql);
            if (empty($results))
                $profileResults['User']['hasArticlesOrder'] = 'N';
            else {
                $results = current($results);
                $profileResults['User']['hasArticlesOrder'] = $results['User']['profile_value'];
                $profileResults['User']['hasArticlesOrder'] = substr($profileResults['User']['hasArticlesOrder'], 1, strlen($profileResults['User']['hasArticlesOrder']) - 2);
            }
        } else
            $profileResults['User']['hasArticlesOrder'] = 'N';
	
        $user->set('user', $profileResults);
		
		return $user;
    }
	
    /*
     * JUser Object ( in libraries/joomla/user/user.php function load($id)
     * aggiungo [ACLsuppliersIdsOrganization], [ACLsuppliersIdsDes]
     * 
     * richiamata anche da DesOrganizationController:admin_choice 
     */
    private function _addParamsDesJUser($user, $debug = false) {

		$controllerLog = $this->Controller;
		
        /*
         * ACLsuppliersIdsDes    1, 3, 5  des_suppliers_id
         */
        $ACLsuppliersIdsDes = 0; // contiene stringa des_suppliers_id 1, 3, 5
        if ($this->Controller->isSuperReferenteDes()) {
            App::import('Model', 'DesSupplier');
            $DesSupplier = new DesSupplier;

            $ACLsuppliersIdsDes = $DesSupplier->getDesSuppliersIds($user, $debug);
        } else {
            App::import('Model', 'DesSuppliersReferent');
            $DesSuppliersReferent = new DesSuppliersReferent;

            $ACLsuppliersIdsDes = $DesSuppliersReferent->getDesSupplierIdsByReferent($user, $user->get('id'), $debug);
        }

        if (empty($ACLsuppliersIdsDes))
            $ACLsuppliersIdsDes = 0;

        return $ACLsuppliersIdsDes;
    }

	public function hasUserFlagPrivacy($user, $action, $debug=false) {
		
		$results = false;

		$controllerLog = $this->Controller;
		$controllerLog::d('hasUserFlagPrivacy '.$action, $debug);
		
		if(isset($user->organization['Organization']['hasUserFlagPrivacy']) && $user->organization['Organization']['hasUserFlagPrivacy']=='Y') {
			if (in_array($action, ['tabs', 'tabsUserCart', 'tabsEcomm', 'tabsEcommTabOrdersDelivery', 'tabsEcommTabAllOrders'])) {
			
				App::import('Model', 'UserProfile');
				$UserProfile = new UserProfile;
							 
				$hasUserFlagPrivacy = $UserProfile->getValue($user, $user->id, 'hasUserFlagPrivacy', 'N', $debug);
				
				if($hasUserFlagPrivacy=='N')
					$results = false;
				else
					$results = true;
			}
			else 
				$results = true;			
		}
		else
			$results = true;

		if(!$results) {
			/*
			 * ctrl che ci sia 1 gasista con il ruolo group_id_user_flag_privacy
			 * se != 1 non rimando alla pagina con il testo della privacy 
			 */
			App::import('Model', 'UserGroupMap');
		  	$UserGroupMap = new UserGroupMap();
		  	
		  	$ctrlUserFlagPrivacys = $UserGroupMap->getUserFlagPrivacys($user);
			$totUserFlagPrivacy = count($ctrlUserFlagPrivacys);
			if($totUserFlagPrivacy>1)
				$results = true;
		}
		
		return $results;
	}
	
	public function hasUserRegistrationExpire($user, $action, $debug=false){
		
		$results = false;

		if(empty($user) || (isset($user->id && $user->id==0))
			return true;
			
		$controllerLog = $this->Controller;
		$controllerLog::d('hasUserRegistrationExpire '.$action, $debug);
		
		if($user->organization['Organization']['hasUserRegistrationExpire']=='Y') {
			if (in_array($action, ['tabs', 'tabsUserCart', 'tabsEcomm'])) {
			
				App::import('Model', 'UserProfile');
				$UserProfile = new UserProfile;

				$hasUserRegistrationExpire = $UserProfile->getValue($user, $user->id, 'hasUserRegistrationExpire', 'N', $debug);
				
				$controllerLog::d("------- _COOKIE --------", $debug);
				$controllerLog::d($_COOKIE, $debug);
				$controllerLog::d("------- _COOKIE --------", $debug);
				
				/*
				 * se $_COOKIE['hasUserRegistrationExpire']=='Y' ha gia' letto il messaggio
				 */
				if($hasUserRegistrationExpire=='Y' || 
				   (isset($_COOKIE[Configure::read('Cookies.user.registration.expire')]) && $_COOKIE[Configure::read('Cookies.user.registration.expire')]=='Y')) { 
					$results = true;
					$controllerLog::d("hasUserRegistrationExpire - cookie setting", $debug);
				}	
				else {
					$results = false;
					$controllerLog::d("hasUserRegistrationExpire - cookie NO setting", $debug);
				}
			}
			else 
				$results = true;			
		}
		else
			$results = true;
		
		return $results;
	}	
}
?>