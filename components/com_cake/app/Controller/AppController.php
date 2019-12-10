<?php
App::uses('Controller', 'Controller');
App::uses('UtilsCommons', 'Lib');
App::uses('UtilsCrons', 'Lib');  // ho la gestione degli stati di Order o ProdDelivery

class AppController extends Controller {

    public $components = ['Session',
        'Cookie',
        'ActionsOrder',
        'ActionsProdDelivery',
        'Users',
        'UserGroups'];
		
    public $helpers = ['App',
        'Html',
        'Form',
        'Session',
        'Time',
        'Ajax',
		'MenuOrders'];
		
    public $utilsCommons;

    /*
     * proprieta' dell'oggetto User
     * */
    public $user;

    /*
     * proprieta' in Session e Cookies
     */
    public $delivery_id; // lato back-office $this->user->organization['Organization']['type'] = 'GAS'
    public $order_id;    // lato back-office $this->user->organization['Organization']['type'] = 'GAS'
    public $prod_delivery_id;       // lato back-office  $this->user->organization['Organization']['type'] = 'PROD'
    public $actionsProdDeliveries;  // lato back-office  $this->user->organization['Organization']['type'] = 'PROD'
    public $userGroups;  // elenco di tutti i gruppi
    public $des_supplier_id;
    public $des_id;

	public static function d($var, $debug=false) { // idem in AppController / AppModel / AppHelper
		if($debug) {		
			if(is_array ($var)) {
				foreach($var as $k => $v) {
					echo "<pre>";
					print_r($k);
					echo '  ';
					print_r($v);
					echo "</pre>";
				}
			}
			else {			
				echo "<pre>";
				print_r($var);
				echo "</pre>";
			}
		}
	}

	public static function dd($var, $debug=true) { // idem in AppController / AppModel / AppHelper
		self::d($var, true);
	}
		
	public static function l($var, $debug=false) { // idem in AppController / AppModel / AppHelper
		if(Configure::read('developer.mode') || $debug) {
			if(is_array ($var)) 
				CakeLog::write('debug', print_r($var, true), ['myDebug']);
			else 
				CakeLog::write('debug', $var, ['myDebug']);
		}
	}
	
	public static function x($var) { // idem in AppController / AppModel / AppHelper
		die($var);
	}
		
    public function beforeFilter() {

        $debug = false;

        date_default_timezone_set('Europe/Rome');
		setlocale(LC_ALL,  'it_IT', 'it', 'it_IT.utf8', 'it_IT.iso88591');
		// self::d(localeconv());
		
		/*
		 * gestione sito offline
		 */
        $actionWithPermission = ['admin_msg_stop', 'msg_stop', 'admin_choice'];

        if (Configure::read('sys_site_offline') == 'Y' && !in_array($this->action, $actionWithPermission)) {
            $this->Session->setFlash(__('msg_site_offline'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
 
 
		$browsers = $this->getBrowser();
		$this->set(compact('browsers'));
		
		if (empty($this->utilsCommons))
            $this->utilsCommons = new UtilsCommons();

        $this->user = JFactory::getUser();
        unset($this->user->password);
        unset($this->user->password_clear);

        /*
         * UserGroups, elenco di tutti i gruppi dell'organization
         * */
        $this->userGroups = $this->UserGroups->getUserGroups($this->user);
        if (!$this->isRoot())
            unset($this->userGroups[Configure::read('group_id_root_supplier')]);


        self::d(["------- BEFORE APPController ---------", $this->user], $debug);
		
        /*
         *  B A C K O F F I C E
         */
        if ($this->params['prefix'] == 'admin') {

			/*
             * D E S 
             */
			if(isset($this->request->data['DesOrganization']['des_id'])) {
				$this->user->set('des_id', $this->request->data['DesOrganization']['des_id']); // ho scelto il DES
				$this->user = $this->Users->setUserDes($this->user, $debug);
			}
		
            if ($this->_resourcesDesEnabled($this->name, $this->action, $debug) && empty($this->user->des_id)) {
                /*
                 * se sono associato ad un solo DES
                 */
                App::import('Model', 'DesOrganization');
                $DesOrganization = new DesOrganization;

                $options = [];
                $options['conditions'] = ['DesOrganization.organization_id' => $this->user->organization['Organization']['id']];
                $options['fields'] = ['DesOrganization.des_id'];
                $options['recursive'] = -1;
                $desOrganizationResults = $DesOrganization->find('all', $options);
                
				self::d($desOrganizationResults, $debug);
				
                if (count($desOrganizationResults) == 1) {
                    $des_id = $desOrganizationResults[0]['DesOrganization']['des_id'];
                    self::d("des_id ".$des_id, $debug);
                    $this->user->set('des_id', $des_id);
                    $this->user = $this->Users->setUserDes($this->user, $debug);
                } else {
                    $this->Session->setFlash(__('msg_des_not_selected'));
                    if(!$debug)
                        $this->myRedirect(['controller' => 'DesOrganizations', 'action' => 'choice', 'admin' => true]);
                }
            }

            /*
             * root
             */
            if ($this->isRoot()) {
                if (!$this->_resourcesRootEnabled($this->name, $this->action) && empty($this->user->organization)) {
                    $this->Session->setFlash(__('msg_organization_not_selected'));
                    if(!$debug)
                        $this->myRedirect(['controller' => 'organizations', 'action' => 'choice', 'admin' => true]);
                }

                /*
                 * carica l'organization in Session solo se cambia
                 */
                if (isset($this->request->data['Organization']['organization_id'])) {
                    $this->user->set('organization', $this->_getOrganization($this->request->data['Organization']['organization_id']));
                    
					$this->user = $this->Users->setUser($this->user, $debug);
                }
            }
            /*
             * not root
             */ else {

                /*
                 * organization prendo quella da table.Users
                 * e la carico solo la prima volta
                 */
                if (empty($this->user->organization)) {
                    $this->user->set('organization', $this->_getOrganization($this->user->get('organization_id')));
                    
					$this->user = $this->Users->setUser($this->user, $debug);	
                }
            } // end if($this->isRoot())

            /*
             * cookies setcookie(name,value,expire,path,domain,secure,httponly);
             * e session
             * */
			switch($this->user->organization['Organization']['type']) {
				case 'GAS':
				case 'PRODGAS':
					/*
					 * precedenza a dati in POST per gasista che ha 2 tab aperti
					 */				
					if (isset($this->request->data['delivery_id'])) {
						$delivery_id = $this->request->data['delivery_id'];
						if ($delivery_id != '' && $delivery_id != null && !is_numeric($delivery_id)) {
							$this->Session->setFlash(__('msg_error_params'));
							$this->myRedirect(Configure::read('routes_msg_exclamation'));
						}
						setcookie('delivery_id', $delivery_id, time() + 86400 * 365 * 1, Configure::read('App.server'));  // (86400 secs per day for 1 years)
						$this->Session->write('delivery_id', $delivery_id);
					} 
					else			
					if (isset($this->request->data['Order']['delivery_id'])) {
						$delivery_id = $this->request->data['Order']['delivery_id'];
						if ($delivery_id != '' && $delivery_id != null && !is_numeric($delivery_id)) {
							$this->Session->setFlash(__('msg_error_params'));
							$this->myRedirect(Configure::read('routes_msg_exclamation'));
						}
						setcookie('delivery_id', $delivery_id, time() + 86400 * 365 * 1, Configure::read('App.server'));  // (86400 secs per day for 1 years)
						$this->Session->write('delivery_id', $delivery_id);
					} 
					else					
					if (isset($this->request->pass['delivery_id'])) {
						$delivery_id = $this->request->pass['delivery_id'];
						if ($delivery_id != '' && $delivery_id != null && !is_numeric($delivery_id)) {
							$this->Session->setFlash(__('msg_error_params'));
							$this->myRedirect(Configure::read('routes_msg_exclamation'));
						}
						setcookie('delivery_id', $delivery_id, time() + 86400 * 365 * 1, Configure::read('App.server'));  // (86400 secs per day for 1 years)
						$this->Session->write('delivery_id', $delivery_id);
					} 
					else
					if (isset($_COOKIE['delivery_id']) && !empty($_COOKIE['delivery_id']))
						$this->Session->write('delivery_id', $_COOKIE['delivery_id']);

					/*
					 * precedenza a dati in POST per gasista che ha 2 tab aperti
					 */	
					if (isset($this->request->data['order_id'])) {
						$order_id = $this->request->data['order_id'];
						if ($order_id != '' && $order_id != null && !is_numeric($order_id)) {
							$this->Session->setFlash(__('msg_error_params'));
							$this->myRedirect(Configure::read('routes_msg_exclamation'));
						}
						setcookie('order_id', $order_id, time() + 86400 * 365 * 1, Configure::read('App.server'));
						$this->Session->write('order_id', $order_id);
					} 
					else
					if (isset($this->request->data['Order']['id'])) {
						$order_id = $this->request->data['Order']['id'];
						if ($order_id != '' && $order_id != null && !is_numeric($order_id)) {
							$this->Session->setFlash(__('msg_error_params'));
							$this->myRedirect(Configure::read('routes_msg_exclamation'));
						}
						setcookie('order_id', $order_id, time() + 86400 * 365 * 1, Configure::read('App.server'));
						$this->Session->write('order_id', $order_id);
					} 
					else
					if (isset($this->request->data['ArticlesOrder']['order_id'])) {
						$order_id = $this->request->data['ArticlesOrder']['order_id'];
						if ($order_id != '' && $order_id != null && !is_numeric($order_id)) {
							$this->Session->setFlash(__('msg_error_params'));
							$this->myRedirect(Configure::read('routes_msg_exclamation'));
						}
						setcookie('order_id', $order_id, time() + 86400 * 365 * 1, Configure::read('App.server'));
						$this->Session->write('order_id', $order_id);
					} 
					else
					if (isset($this->request->pass['order_id'])) {
						$order_id = $this->request->pass['order_id'];
						if ($order_id != '' && $order_id != null && !is_numeric($order_id)) {
							$this->Session->setFlash(__('msg_error_params'));
							$this->myRedirect(Configure::read('routes_msg_exclamation'));
						}
						setcookie('order_id', $order_id, time() + 86400 * 365 * 1, Configure::read('App.server'));
						$this->Session->write('order_id', $order_id);
					} 
					else
					if (isset($_COOKIE['order_id']) && !empty($_COOKIE['order_id']))
						$this->Session->write('order_id', $_COOKIE['order_id']);
					//debug('order_id '.$order_id);
					$this->delivery_id = $this->Session->read('delivery_id');
					$this->order_id = $this->Session->read('order_id');
					/*
					self::dd($this->request->data);
					self::dd($this->request->pass);
					self::dd($this->order_id);
					*/
					$this->set('delivery_id', $this->delivery_id);
					$this->set('order_id', $this->order_id);

					//if(Configure::read('developer.mode')) echo 'AppController delivery_id '.$this->delivery_id.' order_id '.$this->order_id.'<br />';
				
					if (isset($this->request->pass['prod_delivery_id'])) {
						$prod_delivery_id = $this->request->pass['prod_delivery_id'];
						if ($prod_delivery_id != '' && $prod_delivery_id != null && !is_numeric($prod_delivery_id)) {
							$this->Session->setFlash(__('msg_error_params'));
							$this->myRedirect(Configure::read('routes_msg_exclamation'));
						}
						setcookie('prod_delivery_id', $prod_delivery_id, time() + 86400 * 365 * 1, Configure::read('App.server'));  // (86400 secs per day for 1 years)
						$this->Session->write('prod_delivery_id', $prod_delivery_id);
					} else
					if (isset($_COOKIE['prod_delivery_id']) && !empty($_COOKIE['prod_delivery_id']))
						$this->Session->write('prod_delivery_id', $_COOKIE['prod_delivery_id']);


					$this->prod_delivery_id = $this->Session->read('prod_delivery_id');

					$this->set('prod_delivery_id', $this->prod_delivery_id);

					//if(Configure::read('developer.mode')) echo 'AppController delivery_id '.$this->delivery_id.' order_id '.$this->order_id.'<br />';
				break;
				case 'PROD':
				
				break;
				default:
				//	self::x(__('msg_error_org_type').' ['.$this->user->organization['Organization']['type'].']');
				break;				
            }

            /*
             * recupero eventuali parametri passati nell'url come filtri e li metto in sessione
             *  i campi del filtro devono chiamarsi Configure::read('Filter.prefix').$this->modelClass.$NomeCampo = FilterUserEmail
             */
            if (isset($this->request->params['pass']))
                foreach ($this->request->params['pass'] as $key => $value) {
                    if ($this->utilsCommons->string_starts_with($key, Configure::read('Filter.prefix'))) {

                        // echo '<br />'.$key.' :'.$value;

                        if ($value!='')
                            $this->Session->write($key, $value);
                        else
                            $this->Session->delete($key);
                    }
                }


            /*
             *  ACL, ctrl che si abbiamo i permessi per accedere all'url
             *  
             * 		Organization.template_id
             * 		Order.state_code (OPEN, PROCESSED-BEFORE-DELIVERY ...)
             * 		User.group_id    (referente, cassiere, tesoriere)
             * 		Controllor (Order)
             * 		Action     (edit)
             */
            $group_id = $this->ActionsOrder->getGroupIdToReferente($this->user);
            if (!$this->ActionsOrder->isACL($this->user, $group_id, $this->order_id, $this->name, $this->action, $debug)) {
                $this->Session->setFlash(__('msg_not_order_state'));
                if ($debug)
                    exit;
                $this->myRedirect(Configure::read('routes_msg_not_order_state'));
            }
        }
        /*
         * F R O N T E N D
         * dal templates ho il parametro organization_id e organizationSEO
         */
        else {
            /*
             * prendo organization_id dal templates e lo setto in user->org_id
             * 		se empty($organization_id)
             * 			- siamo con il template PORTALE
             * 			- o chiamata Ajax ($params->organizationSEO = portale perche' e' quello di default)
             * per le chiamate dAjax (mod_gas_supplier_articles, mod_gas_supplier_details) $organiation_id sempre 0 perche' il template non c'e'
             *    per le pagine pubbliche: lo prende dalla pagina chiamante
             *    per le pagine con login: le prendo da $user 
             */
            $app = JFactory::getApplication();
            $params = $app->getTemplate(true)->params;
            $organization_id = $params->get('organizationId');
            if (!empty($organization_id)) {
                $this->user->set('org_id', $organization_id);

                // echo '<h1>'.$organization_id.'</h1>';
                /*
                 * se lo user e' loggato setto l'organization dello user
                 * se no lo prendo dal template.params
                 */
                if (!empty($this->user->organization['Organization']['id']))
                    $this->user->set('organization', $this->_getOrganization($this->user->organization['Organization']['id']));
                else
                    $this->user->set('organization', $this->_getOrganization($organization_id));
            }
			

			/*
			 * aggiungo i dati per il prepagati, x BO e FE
			 */
			$this->user = $this->Users->setUserCash($this->user);			
        }

        // affinche' le date sul db e nelle view vengano tradotte il italiano
        $sql = "SET NAMES 'utf8'";
        $this->{$this->modelClass}->query($sql);
        $sql = "set lc_time_names = 'it_IT'";
        $this->{$this->modelClass}->query($sql);

		self::d(["------- POST APPController ---------", $this->user], $debug);

        $this->set('user', $this->user);
    }

    public function reloadUserParams() {

        $debug = false;

        self::d(["BEFORE", $this->user->get('ACLsuppliersIdsOrganization')], $debug);

        $this->user->set('organization', $this->_getOrganization($this->user->organization['Organization']['id']));
        $this->Users->addParamsJUser($this->user);

        self::d(["POST", $this->user->get('ACLsuppliersIdsOrganization')], $debug);
    }

    /*
     * escludo IE quando richiamo i report (ExportDocs)
     */

    public function ctrlHttpReferer($considera_IE = 'Y') {

        if (empty($this->utilsCommons))
            $this->utilsCommons = new UtilsCommons();

        $HTTP_REFERERS = Configure::read('App.server'); // http://'.$_SERVER['HTTP_HOST']

        $continua = false;
        if (isset($_SERVER['HTTP_REFERER'])) {
            if ($this->utilsCommons->string_starts_with($_SERVER['HTTP_REFERER'], $HTTP_REFERERS))
                $continua = true;
        } else
            $continua = false;

        /*
         * nel caso di ie8 ie9 HTTP_REFERER non e' valorizzato
         * if(preg_match('/(?i)msie [1-9]/',$_SERVER['HTTP_USER_AGENT']))
         *   non funziona con IE 11, HTTP_USER_AGENT = Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko
         */
        if ($considera_IE == 'N' && preg_match('/(?i)msie/', $_SERVER['HTTP_USER_AGENT'])) {
            $continua = true;
        }

        if (!Configure::read('developer.mode') && !$continua) {
            $this->Session->setFlash(__('msg_error_http_referer'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
    }

    public function isRoot() {
        if ($this->user->get('id') != 0 && in_array(Configure::read('group_id_root'), $this->user->getAuthorisedGroups()))
            return true;
        else
            return 0;
    }

    public function isRootSupplier() {
        if ($this->user->get('id') != 0 && in_array(Configure::read('group_id_root_supplier'), $this->user->getAuthorisedGroups()))
            return true;
        else
            return 0;
    }

    /*
     * manager
     */

    public function isManager() {
        if ($this->user->get('id') != 0 && in_array(Configure::read('group_id_manager'), $this->user->getAuthorisedGroups()))
            return true;
        else
            return 0;
    }

    public function isManagerDelivery() {
        if ($this->user->get('id') != 0 && in_array(Configure::read('group_id_manager_delivery'), $this->user->getAuthorisedGroups()))
            return true;
        else
            return 0;
    }

    /*
     * referente
     */

    public function isReferente() {
        if ($this->user->get('id') != 0) {
            if (Configure::read('developer.mode'))
                return true;

            if (in_array(Configure::read('group_id_referent'), $this->user->getAuthorisedGroups()))
                return true;
        }
        return false;
    }

    /*
     * super-referente, gestisce tutti i produttori 
     */

    public function isSuperReferente() {
        if ($this->user->get('id') != 0 && in_array(Configure::read('group_id_super_referent'), $this->user->getAuthorisedGroups())) 
			return true;
		else 
            return 0;
    }

    /*
     * referente cassa (pagamento degli utenti alla consegna)
     */

    public function isCassiere() {
        if ($this->user->get('id') != 0 && in_array(Configure::read('group_id_cassiere'), $this->user->getAuthorisedGroups()))
            return true;
        else
            return 0;
    }

    /*
     * referente cassa (pagamento degli utenti alla consegna) dei produttori di cui e' referente
     */

    public function isReferentCassiere() {
        if ($this->user->get('id') != 0 && in_array(Configure::read('group_id_referent_cassiere'), $this->user->getAuthorisedGroups()))
            return true;
        else
            return 0;
    }

    /*
     * referente tesoriere (pagamento con richiesta degli utenti dopo consegna)
     * 		gestisce anche il pagamento del suo produttore
     */

    public function isReferentTesoriere() {
        if ($this->user->get('id') != 0 && in_array(Configure::read('group_id_referent_tesoriere'), $this->user->getAuthorisedGroups()))
            return true;
        else
            return 0;
    }

    public function isReferentGeneric() {
        if ($this->user->get('id') != 0 && (
                in_array(Configure::read('group_id_referent'), $this->user->getAuthorisedGroups()) ||
                in_array(Configure::read('group_id_super_referent'), $this->user->getAuthorisedGroups()) ||
                in_array(Configure::read('group_id_referent_tesoriere'), $this->user->getAuthorisedGroups())
                ))
            return true;
        else
            return 0;
    }

    public function isCassiereGeneric() {
        if ($this->user->get('id') != 0 && (
                in_array(Configure::read('group_id_cassiere'), $this->user->getAuthorisedGroups()) ||
                in_array(Configure::read('group_id_referent_cassiere'), $this->user->getAuthorisedGroups())
                ))
            return true;
        else
            return 0;
    }

    /*
     *  pagamento ai fornitori
     */

    public function isTesoriere() {
        if ($this->user->get('id') != 0 && in_array(Configure::read('group_id_tesoriere'), $this->user->getAuthorisedGroups()))
            return true;
        else
            return 0;
    }

    public function isTesoriereGeneric() {
        if ($this->user->get('id') != 0 && (
                in_array(Configure::read('group_id_referent_tesoriere'), $this->user->getAuthorisedGroups()) ||
                in_array(Configure::read('group_id_tesoriere'), $this->user->getAuthorisedGroups())
                ))
            return true;
        else
            return 0;
    }

    public function isStoreroom() {
        if ($this->user->get('id') != 0 && in_array(Configure::read('group_id_storeroom'), $this->user->getAuthorisedGroups()))
            return true;
        else
            return 0;
    }

    /*
     * DES
     */

    public function isDes() {
        if ($this->user->get('id') != 0 && (
                in_array(Configure::read('group_id_manager_des'), $this->user->getAuthorisedGroups()) ||
                in_array(Configure::read('group_id_referent_des'), $this->user->getAuthorisedGroups()) ||
                in_array(Configure::read('group_id_super_referent_des'), $this->user->getAuthorisedGroups()) ||
                in_array(Configure::read('group_id_titolare_des_supplier'), $this->user->getAuthorisedGroups()) ||
                in_array(Configure::read('group_id_des_supplier_all_gas'), $this->user->getAuthorisedGroups())
                ))
            return true;
        else
            return 0;
    }

    public function isManagerDes() {
        if ($this->user->get('id') != 0 && in_array(Configure::read('group_id_manager_des'), $this->user->getAuthorisedGroups()))
            return true;
        else
            return 0;
    }

    public function isReferenteDes() {
        if ($this->user->get('id') != 0) {
            if (Configure::read('developer.mode'))
                return true;

            if (in_array(Configure::read('group_id_referent_des'), $this->user->getAuthorisedGroups()))
                return true;
        }
        return false;
    }

    public function isSuperReferenteDes() {
        if ($this->user->get('id') != 0 && in_array(Configure::read('group_id_super_referent_des'), $this->user->getAuthorisedGroups()))
            return true;
        else
            return 0;
    }

    public function isTitolareDesSupplier() {
        if ($this->user->get('id') != 0 && in_array(Configure::read('group_id_titolare_des_supplier'), $this->user->getAuthorisedGroups()))
            return true;
        else
            return 0;
    }

    public function isReferentDesAllGas() {
        if ($this->user->get('id') != 0 && in_array(Configure::read('group_id_des_supplier_all_gas'), $this->user->getAuthorisedGroups()))
            return true;
        else
            return 0;
    }

	public function isManagerUserDes() {
        if ($this->user->get('id') != 0 && in_array(Configure::read('group_id_user_manager_des'), $this->user->getAuthorisedGroups()))
            return true;
        else
            return 0;
    }
    
	public function isUserFlagPrivay() {
        if ($this->user->get('id') != 0 && in_array(Configure::read('group_id_user_flag_privacy'), $this->user->getAuthorisedGroups()))
            return true;
        else
            return 0;
    }
	
    /*
     * gestisce i calendar events
     */

    public function isManagerEvents() {
        if ($this->user->get('id') != 0 && in_array(Configure::read('group_id_events'), $this->user->getAuthorisedGroups()))
            return true;
        else
            return 0;
    }

    /*
     * verifica se un utente ha la gestione degli articoli sugli ordini
     * dipende da 
     * 		- Organization.hasArticlesOrder
     * 		- User.hasArticlesOrder
     * 
     * anche in AppHelper, AppModel
     */ 
    public function isUserPermissionArticlesOrder($user) {
        if ($user->organization['Organization']['hasArticlesOrder'] == 'Y' && $user->user['User']['hasArticlesOrder'] == 'Y')
            return true;
        else
            return false;
    }

    /* ovveride lib/Cake/Controller/Controller.php 
     * code original 
     * 		$this->myRedirect(['action' => 'index']);
     * cake imposta il controller e l'action nell'url /cake/user/index
     * qui si impostano nella queryString /administrator/index.php?option=com_cake&controller=Articles&action=context_articles_index&id=64&direction:asc
     * */
    public function myRedirect($url, $status = null, $exit = true) {

        /*
         * non tratto gli indirizzi assoluti
         */
        if (is_array($url)) {

            $params = $_REQUEST;
   
            if (isset($url['option']))
                $params['option'] = $url['option'];
            if (isset($url['controller']))
                $params['controller'] = $url['controller'];
            if (isset($url['action']))
                $params['action'] = $url['action'];
            if (isset($url['id']))
                $params['id'] = $url['id'];
            if (isset($url['supplier_organization_id']))
                $params['supplier_organization_id'] = $url['supplier_organization_id'];
            if (isset($url['organization_id']))
                $params['organization_id'] = $url['organization_id'];
            if (isset($url['delivery_id']))
                $params['delivery_id'] = $url['delivery_id'];
            if (isset($url['order_id']))
                $params['order_id'] = $url['order_id'];
            if (isset($url['article_organization_id']))
                $params['article_organization_id'] = $url['article_organization_id'];
            if (isset($url['article_id']))
                $params['article_id'] = $url['article_id'];
            if (isset($url['user_id']))
                $params['user_id'] = $url['user_id'];
            if (isset($url['type']))
                $params['type'] = $url['type'];

            if (strpos($_SERVER['REQUEST_URI'], '/administrator/') === false)
                $newUrl = Configure::read('App.server') . '/';
            else
                $newUrl = Configure::read('App.server') . '/administrator/index.php';

            $newUrl .= '?';
            $newUrl .= 'option=' . $params['option'];
            $newUrl .= '&controller=' . $params['controller'];
            $newUrl .= '&action=' . $params['action'];
            if (isset($params['id'])) {
                $newUrl .= '&id=' . $params['id'];
            	unset($params['id']);
            }    
            if (isset($url['supplier_organization_id'])) {
                $newUrl .= '&supplier_organization_id=' . $params['supplier_organization_id'];
                unset($params['supplier_organization_id']);
            }             
            if (isset($url['organization_id'])) {
                $newUrl .= '&organization_id=' . $params['organization_id'];
                unset($params['organization_id']);
            }        
            if (isset($url['delivery_id'])) {
                $newUrl .= '&delivery_id=' . $params['delivery_id'];
                unset($params['delivery_id']);
            }        
            if (isset($url['order_id'])) {
                $newUrl .= '&order_id=' . $params['order_id'];
                unset($params['order_id']);
            }        
            if (isset($url['article_organization_id'])) {
                $newUrl .= '&article_organization_id=' . $params['article_organization_id'];
                unset($params['article_organization_id']);
            }        
            if (isset($url['article_id'])) {
                $newUrl .= '&article_id=' . $params['article_id'];
                unset($params['article_id']);
            }        
            if (isset($url['user_id'])) {
                $newUrl .= '&user_id=' . $params['user_id'];
                unset($params['user_id']);
            }        
            if (isset($url['type'])) {
                $newUrl .= '&type=' . $params['type'];
                unset($params['type']);
            }    
            unset($params['option']);
            unset($params['controller']);
            unset($params['action']);

            // TODO nella $_REQUEST mi trovato order_id e delivery_id che erano presi dal cookies!!
            unset($params['order_id']);
            unset($params['delivery_id']);
            unset($params['__utma']);
            unset($params['__utmz']);
            unset($params['jpanesliders_panel-sliders']);

            foreach ($params as $key => $value) {
                if (empty($value))
                    $newUrl .= '&' . $key;  // direction:asc
                else
                if (!is_array($value))
                    $newUrl .= '&' . $key . '=' . $value; // id=65
            }
        } else
            $newUrl = $url;  // url assoluto
            
        /*
         * non piu utilizzato perche' tolti gli header all'oggetto Lib/Network/CakeResponse.php
          $this->response->header('Location', $newUrl);
          $this->response->send();
          $this->_stop();
         */
        header("Location: $newUrl");
        exit;
    }

    /*
     * idem model
     */

    public function importoToDatabase($importo, $debug=false) {
    	
    	self::l('importoToDatabase PRE '.$importo, $debug);
    	
        // elimino le migliaia
        $importo = str_replace('.', '', $importo);

        // converto eventuali decimanali
        $importo = str_replace(',', '.', $importo);

        if (strpos($importo, '.') === false)
            $importo = $importo . '.00';

		self::l('importoToDatabase POST '.$importo, $debug);

        return $importo;
    }

    /*
     * dagli Id dei produttori (ACLsuppliersIdsOrganization) 
     * estraggo la LIST dei produttori
     * 
     * call in Article/admin_add admin_edit admin_index
     * 		   Order/admin_add admin_edit 
	 *
	 * posso filtrare per SuppliersOrganization.owner_articles = REFERENT/SUPPLIER, in Article::add escludo i produttori NON gestiti dal GAS
	 */

    public function getACLsuppliersOrganization($owner_articles='') {

        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;
		
        $options = [];
        $options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id']];
		if(strpos($this->user->get('ACLsuppliersIdsOrganization'), ",")===false)
			$options['conditions'] += ['SuppliersOrganization.id' => $this->user->get('ACLsuppliersIdsOrganization')];
		else
			$options['conditions'] += ['SuppliersOrganization.id IN ' => explode(",", $this->user->get('ACLsuppliersIdsOrganization'))];
        
		if(!empty($owner_articles))
			$options['conditions'] += ['SuppliersOrganization.owner_articles' => $owner_articles];
		
		self::d($options, false);
		
		$options['order'] = ['SuppliersOrganization.name'];
        $options['recursive'] = -1;
        $ACLsuppliersOrganization = $SuppliersOrganization->find('list', $options);

        return $ACLsuppliersOrganization;
    }

    /*
     * dagli Id dei produttori (ACLsuppliersIdsDes) 
     * estraggo la LIST dei produttori
     */

    public function getACLsuppliersIdsDes() {

        App::import('Model', 'DesSupplier');
        $DesSupplier = new DesSupplier;

        $options = [];
        $options['conditions'] = ['DesSupplier.des_id' => $this->user->des_id,
								'DesSupplier.id IN (' . $this->user->get('ACLsuppliersIdsDes') . ')',
								"(Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG')"];
        $options['fields'] = ['DesSupplier.id', 'Supplier.name'];
        $options['order'] = ['Supplier.name'];
        $options['recursive'] = 1;
        $ACLsuppliersIdsDes = $DesSupplier->find('list', $options);

        self::d([$options, $ACLsuppliersIdsDes], false);

        return $ACLsuppliersIdsDes;
    }

    /*
     * $url = $_SERVER['REQUEST_URI'],
     * ma in Order:admin_sotto_menu()
     * 		$_SERVER['REQUEST_URI']  = ...controller=Orders&action=sotto_menu
     * 		$_SERVER['HTTP_REFERER'] = $url
     */

    public function getToUrlControllerAction($url) {

        $pageCurrent = ['controller' => '', 'action' => ''];

        if (!empty($url)) {
            $arrayUrl = parse_url($url);

            $arrayUrlQuery = explode('&', $arrayUrl['query']);

            foreach ($arrayUrlQuery as $value) {
                $arrayFinale = explode('=', $value);
                if (strtolower($arrayFinale[0]) == 'controller' || strtolower($arrayFinale[0]) == 'action')
                    $pageCurrent[strtolower($arrayFinale[0])] = strtolower($arrayFinale[1]);
            }
        }

        return $pageCurrent;
    }

    /*
     * stesso codice AppHelper
     */

    public function traslateWww($str) {

        if (strpos($str, 'http://') === false && strpos($str, 'https://') === false)
            $str = 'http://' . $str;

        return $str;
    }

    /*
     * $modulo: sono in quel modulo e ctrl se ho anche altri moduli che possono andare in conflitto
     * 			managementCartsOne (Gestisci gli acquisti nel dettaglio) con 
     * 				Order.typeGest.AGGREGATE per SummaryOrder
     * 				Order.typeGest.SPLIT     per Order.qta
     * 
     *      		Order.trasport
     * 				Order.hasCostMore
     * 				Order.hasCostLess
     * 
     * 			managementCartsGroupByUsers (Gestisci gli acquisti aggregati per importo) con 
     * 				Order.trasport
     * 				Order.hasCostMore
     * 				Order.hasCostLess
     */
    public function ctrlModuleConflicts($user, $order_id, $modulo, $debug = false) {

		App::import('Model', 'OrderLifeCycle');
		$OrderLifeCycle = new OrderLifeCycle;
		
		$options = [];
		$options['moduleConflicts'] = $modulo;
		
		$esito = $OrderLifeCycle->beforeRendering($user, $order_id, $this->request->params['controller'], $this->action, $options, $debug);
		if(isset($esito['ctrlModuleConflicts'])) {
			/*
			 * ctrl se ho un cookie settato per 
			 * 		quel conflitto 
			 * 		quell'ordine
			 */
			 
			self::l([$esito, $_COOKIE], false);
			 
			if (isset($_COOKIE[$esito['ctrlModuleConflicts']['alertModuleConflicts']])) {
				if ($_COOKIE[$esito['ctrlModuleConflicts']['alertModuleConflicts']] == $this->order_id) {
					$popUpDisabled = true;
				}	
				else
					$popUpDisabled = false;
			}

	        $this->set('orderSummaryOrderAggregate', $esito['ctrlModuleConflicts']['orderHasSummaryOrderAggregate']);
			$this->set('orderHasCostMore', $esito['ctrlModuleConflicts']['orderHasCostMore']);
			$this->set('orderHasCostLess', $esito['ctrlModuleConflicts']['orderHasCostLess']);
			$this->set('alertModuleConflicts', $esito['ctrlModuleConflicts']['alertModuleConflicts']);
			$this->set('popUpDisabled', $popUpDisabled);
			
		} // end if(isset($esito['ctrlModuleConflicts']))
    }

    /*
     * gestisco il box con i dati dell'ordine
     */
    public function _boxOrder($user, $delivery_id, $order_id, $opts) {

		$debug = false;
		
        /*
          App::import('Model', 'Delivery');
          $Delivery = new Delivery;

          $conditions = ['Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
				          'Delivery.isVisibleBackOffice' => 'Y',
				          'Delivery.stato_elaborazione' => 'OPEN'];

          $deliveries = $Delivery->find('list',array('fields'=>array('id', 'luogoData'),'conditions'=>$conditions,'order'=>'data ASC','recursive'=>-1));
          if(empty($deliveries)) {
          $this->Session->setFlash(__('NotFoundDeliveries'));
          $this->myRedirect(Configure::read('routes_msg_exclamation'));
          }
          $this->set(compact('deliveries'));
         */

        App::import('Model', 'Order');
        $Order = new Order;
		
        App::import('Model', 'OrderLifeCycle');
        $OrderLifeCycle = new OrderLifeCycle;

        $options = [];
        $options['conditions'] = ['Order.organization_id' => (int) $user->organization['Organization']['id'],
								'Delivery.organization_id' => (int) $user->organization['Organization']['id'],
								'Order.id' => $order_id,
								'Delivery.id' => $delivery_id];
        if (isset($opts['conditions']))
            $options['conditions'] += $opts['conditions'];
        $options['recursive'] = 1;
        $results = $Order->find('first', $options);
        if (empty($results)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * ctrl visibilita' in backOffice
         */
        if ($results['Delivery']['isVisibleBackOffice'] == 'N') {
            $this->Session->setFlash(__('msg_not_delivery_visible_backoffice'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        if ($results['Order']['isVisibleBackOffice'] == 'N') {
            $this->Session->setFlash(__('msg_not_order_visible_backoffice'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * permission per abilitazione modifica del carrello
         */
        $permissions = ['isReferentGeneric' => $this->isReferentGeneric(),
						 'isTesoriereGeneric' => $this->isTesoriereGeneric()];
        $this->set('permissions', $permissions);

        /*
         * gestione eventuale msg se sono nel modulo exportDocs
         */		
		$msg = $OrderLifeCycle->beforeRendering($this->user, $results, $this->request->params['controller'], $this->action, $options, $debug);
		if(isset($msg['msgExportDocs']) && !empty($msg['msgExportDocs']))
			$results['msgExportDocs'] = $msg['msgExportDocs'];
		else
			$results['msgExportDocs'] = '';
		
		$this->set('results', $results);
    }

    /*
     * gestisco il box con i dati della consegna del produttore
     */
    public function _boxProdDelivery($user, $prod_delivery_id, $opts) {

        App::import('Model', 'ProdDelivery');
        $ProdDelivery = new ProdDelivery;

        $options = [];
        $options['conditions'] = ['ProdDelivery.organization_id' => (int) $user->organization['Organization']['id'],
								  'ProdDelivery.id' => $prod_delivery_id];
        if (isset($opts['conditions']))
            $options['conditions'] += $opts['conditions'];
        $options['recursive'] = 1;
        $results = $ProdDelivery->find('first', $options);
        if (empty($results)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * ctrl visibilita' in backOffice
         */
        if ($results['ProdDelivery']['isVisibleBackOffice'] == 'N') {
            $this->Session->setFlash(__('msg_not_delivery_visible_backoffice'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $this->set('results', $results);
        $this->set('call_action', $this->action);
    }

    /* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
    /* ::                                                                         : */
    /* ::  This routine calculates the distance between two points (given the     : */
    /* ::  latitude/longitude of those points). It is being used to calculate     : */
    /* ::  the distance between two locations using GeoDataSource(TM) Products    : */
    /* ::                                                                         : */
    /* ::  Definitions:                                                           : */
    /* ::    South latitudes are negative, east longitudes are positive           : */
    /* ::                                                                         : */
    /* ::  Passed to function:                                                    : */
    /* ::    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)  : */
    /* ::    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)  : */
    /* ::    unit = the unit you desire for results                               : */
    /* ::           where: 'M' is statute miles (default)                         : */
    /* ::                  'K' is kilometers                                      : */
    /* ::                  'N' is nautical miles                                  : */
    /* ::  Worldwide cities and other features databases with latitude longitude  : */
    /* ::  are available at http://www.geodatasource.com                          : */
    /* ::                                                                         : */
    /* ::  For enquiries, please contact sales@geodatasource.com                  : */
    /* ::                                                                         : */
    /* ::  Official Web site: http://www.geodatasource.com                        : */
    /* ::                                                                         : */
    /* ::         GeoDataSource.com (C) All Rights Reserved 2015		   		     : */
    /* ::                                                                         : */
    /* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
    function distance($lat1, $lon1, $lat2, $lon2, $unit) {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    /*
     *  risorse alle quali accedere senza organiation_id
     */
    private function _resourcesRootEnabled($controller, $action) {

        $controllersEnabled = ['organizations',
							'ordersactions',
							'organizationspays',
							'templatesordersstates',
							'templatesordersstatesOrdersActions',
							'suppliers',
							'categoriessuppliers',
							'configurations',
							'logs',
							'mails',
							'helps',
							'cakeerror'];

        $controller = strtolower($controller);
        $action = strtolower($action);
        
        if (in_array($controller, $controllersEnabled) || ($action == 'admin_msg_stop_browser'))
            return true;
        else
            return false;
    }

    /*
     *  risorse alle quali per accedere devo aver valorizzato des_id
     */
    private function _resourcesDesEnabled($controller, $action, $debug=false) {

        $controllersEnabled = ['desorganizations',
							'dessuppliersreferents',
							'desusergroupmap',
							'desorders',
							'des'];

        $controller = strtolower($controller);
        $action = strtolower($action);

		self::d("App::_resourcesDesEnabled() controller ".$controller." action ".$action, $debug);
		
        if ($controller == 'desorganizations' && $action == 'admin_choice')
            return false;
        else
        if (in_array($controller, $controllersEnabled))
            return true;
        else
            return false;
    }

	public function getBrowser($debug=false) {
		$browsers = [];
		if(isset($_SERVER["HTTP_USER_AGENT"])) {
			$userAgent = $_SERVER["HTTP_USER_AGENT"];
			$browsers['userAgent'] = $userAgent;
			$browsers['ie11'] = strpos($userAgent, 'Trident/7.0; rv:11.0') ? true : false; // Internet Explorer IE11
			$browsers['msie'] = strpos($userAgent, 'MSIE') ? true : false; // Internet Explorer
			$browsers['firefox'] = strpos($userAgent, 'Firefox') ? true : false; // Firefox
			$browsers['safari'] = strpos($userAgent, 'Safari') ? true : false; // Webkit powered browser
			$browsers['chrome'] = strpos($userAgent, 'Chrome') ? true : false; // Webkit powered browser		
		}

		self::d($browsers, $debug);
		
		return $browsers;
	}
	
    /*
     * configurazione organization[Organization] 
     * 		dati 
     *  	paramsConfig hasArticlesOrder, hasVisibility, hasTrasport, hasCostMore, hasCostLess, hasStoreroom, hasDes, prodSupplierOrganizationId
     *  	paramsFields hasFieldArticleCodice, hasFieldArticleIngredienti, hasFieldArticleAlertToQta, hasFieldArticleCategoryId, hasFieldSupplierCategoryId,
     *  				 hasFieldFatturaRequired)
     */
    private function _getOrganization($organization_id = 0) {

        $results = [];

        if ($organization_id > 0) {
            App::import('Model', 'Organization');
            $Organization = new Organization;

			$Organization->unbindModel(['hasMany' => ['Delivery', 'User']]);

            $options = [];
            $options['conditions'] = ['Organization.id' => (int) $organization_id];
            $options['recursive'] = 0;
            $results = $Organization->find('first', $options);
			
            $paramsFields = json_decode($results['Organization']['paramsFields'], true);
            $paramsConfig = json_decode($results['Organization']['paramsConfig'], true);

			/*
			 * configurazione preso dal template
			 */
			$paramsConfig['payToDelivery'] = $results['Template']['payToDelivery'];
			$paramsConfig['orderForceClose'] = $results['Template']['orderForceClose'];
			$paramsConfig['orderUserPaid'] = $results['Template']['orderUserPaid'];
			$paramsConfig['orderSupplierPaid'] = $results['Template']['orderSupplierPaid'];
			$paramsConfig['ggArchiveStatics'] = $results['Template']['ggArchiveStatics'];

            $results['Organization'] += $paramsConfig;
            $results['Organization'] += $paramsFields;

            unset($results['Organization']['paramsConfig']);
            unset($results['Organization']['paramsFields']);
			
			if($results['Organization']['type']=='PRODGAS') {
				
				/*
				 * estraggo i produttori legati al PRODGAS, posso essere + di 1 ma per ora ne gestisco uno
				 */
				App::import('Model', 'SuppliersOrganization');
				$SuppliersOrganization = new SuppliersOrganization;
		
				$SuppliersOrganization->unbindModel(['belongsTo' => ['Organization', 'CategoriesSupplier']]);

				$options = [];
				$options['conditions'] = ['SuppliersOrganization.organization_id' => (int) $organization_id];
				$options['recursive'] = 0;
				$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
				
				$results['Supplier'] = $suppliersOrganizationResults;
			}
        }

        return $results;
    }
	
	/* 
	 * ctrl se lo user puo' utilizzare quel organization_id 
	 *	DES quando utilizza piu' GAS
	 */ 
	public function aclOrganizationIdinUso($organization_id=0, $debug=false) {
		
        if ((empty($organization_id) || $this->isRoot()) || 
		    ($organization_id==$this->user->organization['Organization']['id'])) {
            $organization_id = $this->user->organization['Organization']['id'];
        }
		else {
			/* DES */	
			if ($this->user->organization['Organization']['hasDes'] == 'N' || $this->user->organization['Organization']['hasDesUserManager'] != 'Y' || empty($this->user->des_id)) {
	            return false;
	        }
		
	        App::import('Model', 'DesOrganization');
	        $DesOrganization = new DesOrganization;
					
	        /*
	         * tutti i GAS del DES
	         */
	        $options = [];
	        $options['conditions'] = ['DesOrganization.des_id' => $this->user->des_id,
									  'DesOrganization.organization_id' => $organization_id];
	        $options['recursive'] = 1;
	        $desOrganizationsResults = $DesOrganization->find('first', $options);
			if(empty($desOrganizationsResults)) {
	            return false;		
			}	        
		}
				
		return $organization_id;
	}
}
