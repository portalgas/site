<?php

/**
 * Static content controller.
 *
 * This file will render views from views/pages/
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('AppController', 'Controller');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();

        /*
         * Se errore invio al mail 
         */
        if (Configure::read('sys_send_mail_error') == 'Y') {
            $actionWithPermission = array('admin_msg_question', 'msg_question',
                'admin_msg_stop', 'msg_stop',
                'admin_msg_exclamation', 'msg_exclamation'
            );

            if (in_array($this->action, $actionWithPermission)) {

                $debug = false;

                $body_mail = '';
                $body_mail .= '<h2>Utente ' . $this->user->name . ' (' . $this->user->id . ') - Organization ' . $this->user->organization['Organization']['name'] . ' (' . $this->user->organization['Organization']['id'] . ')</h2>';
                $body_mail .= 'Username ' . $this->user->username . ' Email ' . $this->user->email;
                $body_mail .= '<h2>Gruppi</h2>';
                App::import('Model', 'UserGroup');

                $user_userGroups = [];
                $body_mail .= '<ul>';
                foreach ($this->user->groups as $numResult => $group_id) {

                    $UserGroup = new UserGroup;

                    $options = [];
                    $options['conditions'] = ['UserGroup.id' => $group_id];
                    $options['recursive'] = -1;
                    $results = $UserGroup->find('first', $options);

                    $group_name = $results['UserGroup']['title'];

                    $body_mail .= '<li>' . $group_name . ': ' . $this->userGroups[$results['UserGroup']['id']]['descri'] . '</li>';
                }
                $body_mail .= '</ul>';
                $body_mail .= '<br />User.hasArticlesOrder ' . $this->user->user['User']['hasArticlesOrder'];

                $body_mail .= '<h2>Produttori</h2>';
                $body_mail .= 'ACLsuppliersIdsOrganization ' . $this->user->ACLsuppliersIdsOrganization;

                $body_mail .= '<h2>Variabili del Server</h2>';
                if (isset($_SERVER['HTTP_USER_AGENT']))
                    $body_mail .= '<br />HTTP_USER_AGENT ' . $_SERVER['HTTP_USER_AGENT'];
                else
                    $body_mail .= '<br />HTTP_USER_AGENT non valorizzato';

                if (isset($_SERVER['HTTP_COOKIE']))
                    $body_mail .= '<br />HTTP_COOKIE ' . $_SERVER['HTTP_COOKIE'];
                else
                    $body_mail .= '<br />HTTP_COOKIE non valorizzato <span style="color:red">allowAdminAccess</span>';

                if (isset($_SERVER['HTTP_REFERER']))
                    $body_mail .= '<br />HTTP_REFERER ' . $_SERVER['HTTP_REFERER'];
                else
                    $body_mail .= '<br />HTTP_REFERER non valorizzato';


                App::import('Model', 'Mail');
                $Mail = new Mail;

                $Email = $Mail->getMailSystem($this->user);

                $subject_mail = "Page " . $this->action;
                $Email->subject($subject_mail);
                if (!empty($this->user->organization['Organization']['www']))
                    $Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))]);
                else
                    $Email->viewVars(['body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))]);

                $name = Configure::read('SOC.name');
                $mail = Configure::read('SOC.mail-contatti');

                $Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
                $Email->to($mail);

                $Mail->send($Email, $mail, $body_mail, $debug);
            }
        } // if(Configure::read('sys_send_mail_error')=='Y') 
    }

    /* 
     * script per mantenere la session 
    */
    public function admin_ping() {
        
        // $this->layout = 'default_front_end';
        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');     
    }

    public function ping() {
        
        // $this->layout = 'default_front_end';
        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');     
    }

    public function admin_home() {
    
        // debug($this->user);

		switch ($this->user->organization['Organization']['type']) {
			case 'GAS':
            case 'SOCIALMARKET':
				$this->_admin_home_gas();
            break;
            case 'PRODGAS':
                // debug($this->user->organization['Supplier']['SuppliersOrganization']['id']);
                $this->_admin_home_prod_gas();
            break;
            case 'PACT':
                $this->_admin_home_pact();
			break;
			case 'PROD':
				$this->_admin_home_prod();  // per ora non utilizzato: sono i produttori che avrebbero un loro ecommerce
			break;
			default:
				self::x("PagesController::admin_home Organization.type [".$this->user->organization['Organization']['type']."] non valido");
			break;
		}
    }

    private function _admin_home_gas() {

        $debug = false;

		App::import('Model', 'OrganizationsPayment');
		$OrganizationsPayment = new OrganizationsPayment;

        /*
         * ctrl i dati del pagamento del GAS
         */
        if ($this->user->organization['Organization']['type']=='GAS' && ($this->isManager() || $this->isTesoriere())) {
            if(!$OrganizationsPayment->isPaymentComplete($this->user)) {
                $this->Session->setFlash(__('msg_organization_payment_incomplete'));
                $this->myRedirect(['controller' => 'OrganizationsPayments', 'action' => 'edit', 'admin' => true]);
            }
		} // if ($this->isManager())

        $this->set('isRoot', $this->isRoot());
        $this->set('isManager', $this->isManager());
        $this->set('isManagerDelivery', $this->isManagerDelivery());
        $this->set('isReferentGeneric', $this->isReferentGeneric());
        $this->set('isSuperReferente', $this->isSuperReferente());
        $this->set('isReferente', $this->isReferente());
        $this->set('isTesoriere', $this->isTesoriere());
        $this->set('isTesoriereGeneric', $this->isTesoriereGeneric());
        $this->set('isCassiere', $this->isCassiereGeneric());
        $this->set('isStoreroom', $this->isStoreroom());
        /* 
        * gas-groups (sotto gruppi)
        */        
        $this->set('isGasGropusManagerGroups', $this->isGasGropusManagerGroups());
        $this->set('isGasGropusManagerDelivery', $this->isGasGropusManagerDelivery());
        $this->set('isGasGropusManagerParentOrders', $this->isGasGropusManagerParentOrders());
        $this->set('isGasGropusManagerOrders', $this->isGasGropusManagerOrders());
        
        $this->set('userGroups', $this->userGroups);

		/*
		 * promozioni
		 */
        App::import('Model', 'ProdGasPromotionsOrganizationsManager');
        $ProdGasPromotionsOrganizationsManager = new ProdGasPromotionsOrganizationsManager;
	
		$rules = [];
		$rules['isSuperReferente'] = $this->isSuperReferente();
		$rules['isReferente'] = $this->isReferente();

		$prodGasPromotionsOrganizationsresults = $ProdGasPromotionsOrganizationsManager->getWaitingPromotions($this->user, $rules, $debug);
		$this->set(compact('prodGasPromotionsOrganizationsresults'));
		
        /*
         * per i referenti gli ordini
         */
        $ordersResults = [];
        if ($this->isReferentGeneric()) {
        
            App::import('Model', 'Order');
            $Order = new Order;

            $options['conditions'] = [];
            /*
             * anche il superReferente vede solo i suoi produttori
             */
            if ($this->isSuperReferente()) {
                App::import('Model', 'SuppliersOrganizationsReferent');
                $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
                $SuppliersOrganizationsReferentResults = $SuppliersOrganizationsReferent->getSuppliersOrganizationByReferent($this->user, $this->user->id);

                $ids = '';
                foreach ($SuppliersOrganizationsReferentResults as $SuppliersOrganizationsReferentResult) {
                    /*
                     * potrebbe essere un referente di un produttore che non esiste +
                     */
                    if(!empty($SuppliersOrganizationsReferentResult['SuppliersOrganization']['id']))
                        $ids .= $SuppliersOrganizationsReferentResult['SuppliersOrganization']['id'] . ',';
                }

                if (!empty($ids)) {
                    $ids = substr($ids, 0, (strlen($ids) - 1));

                    $options['conditions'] += ['0' => 'Order.supplier_organization_id IN (' . $ids . ')'];
                } else {
                    /*
                     * se super-ref non e' referenti di alcun produttore non vede gli ordini
                     */
                    $options['conditions'] += ['0' => 'Order.supplier_organization_id IN (-1)'];
                }
            } else {
                $options['conditions'] += ['Order.supplier_organization_id IN (' . $this->user->get('ACLsuppliersIdsOrganization') . ')'];
            }

            $options['conditions'] += ['Delivery.organization_id' => $this->user->organization['Organization']['id'],
						                'Order.organization_id' => $this->user->organization['Organization']['id'],
						                '1' => '(Order.state_code = \'OPEN\' OR Order.state_code = \'RI-OPEN-VALIDATE\' OR Order.state_code = \'PROCESSED-BEFORE-DELIVERY\')',
						                'Delivery.isVisibleBackOffice' => 'Y',
						                'Delivery.stato_elaborazione' => 'OPEN'];
            $options['recursive'] = 0;
            $options['limit'] = 10;
            $ordersResults = $Order->find('all', $options);
            
			self::d([$options, $ordersResults],false);
			
            foreach ($ordersResults as $numResult => $ordersResult) {

                /*
                 * ctrl se l'ordine dev'essere validato (ArticlesOrder.pezzi_confezione > 1) per la gestione dei colli
                 */
                if ($Order->isOrderToValidate($this->user, $ordersResult['Order']['id']))
                    $ordersResults[$numResult]['Order']['toValidate'] = true;
                else
                    $ordersResults[$numResult]['Order']['toValidate'] = false;

                /*
                 * ctrl se l'ordine ha settato delle quantita' massime > 0
                 */
                if ($Order->isOrderToQtaMassima($this->user, $ordersResult['Order']['id']))
                    $ordersResults[$numResult]['Order']['toQtaMassima'] = true;
                else
                    $ordersResults[$numResult]['Order']['toQtaMassima'] = false;

                /*
                 * ctrl se l'ordine ha settato delle quantita' minime sugli acquisti di tutto l'ordine > 0
                 */
                if ($Order->isOrderToQtaMinimaOrder($this->user, $ordersResult['Order']['id']))
                    $ordersResults[$numResult]['Order']['toQtaMinimaOrder'] = true;
                else
                    $ordersResults[$numResult]['Order']['toQtaMinimaOrder'] = false;

                /*
                 *  aggiungo eventuali calcoli dei limiti sulla Order.qta_massima e Order.importo_massimo
                 */
                if (($ordersResult['Order']['state_code'] == 'OPEN' || $ordersResult['Order']['state_code'] == 'RI-OPEN-VALIDATE') && ($ordersResult['Order']['qta_massima'] > 0))
                    $ordersResults[$numResult]['Order']['qta_massima_current'] = $Order->getTotQuantitaArticlesOrder($this->user, $ordersResult, $debug);
                else
                    $ordersResults[$numResult]['Order']['qta_massima_current'] = 0;


                if (($ordersResult['Order']['state_code'] == 'OPEN' || $ordersResult['Order']['state_code'] == 'RI-OPEN-VALIDATE') && ($ordersResult['Order']['importo_massimo'] > 0))
                    $ordersResults[$numResult]['Order']['importo_massimo_current'] = $Order->getTotImportoArticlesOrder($this->user, $ordersResult['Order']['id'], $debug);
                else
                    $ordersResults[$numResult]['Order']['importo_massimo_current'] = 0;
            }

            $this->set(compact('ordersResults'));
        } // end if($this->isReferentGeneric())

		/*
		 * msg personalizzato per maganer / tesoriere
		 */
        if ($this->isManager() || $this->isTesoriere()) {			
			$msg = $OrganizationsPayment->hasMgs($this->user);
            if(!empty($msg)) {
                $this->Session->setFlash($msg);
            }			
        } // if ($this->isManager())

        /*
         * msg al Cassiere se ci sono consegne scadute ma OPEN => dovra' chiuderle 
		 *
		 * non +, nell'elenco ordine gestisco la chiusura
        $alertDeliveriesToClose = false;
        if ($this->isCassiere()) {
            App::import('Model', 'Cassiere');
            $Cassiere = new Cassiere;

            $deliveriesResults = $Cassiere->getDeliveriesToClose($this->user, false, $debug);

            if (!empty($deliveriesResults))
                $alertDeliveriesToClose = true;
        }
        $this->set('alertDeliveriesToClose', $alertDeliveriesToClose);
         */
		 
        /*
         * ruolo, ottengo il nome del gruppo di joomla
        if (Configure::read('developer.mode')) {

            App::import('Model', 'UserGroup');

            $user_userGroups = [];
            foreach ($this->user->groups as $numResult => $group_id) {

                $UserGroup = new UserGroup;

                $options = [];
                $options['conditions'] = ['UserGroup.id' => $group_id];
                $options['recursive'] = -1;
                $results = $UserGroup->find('first', $options);

                $group_name = $results['UserGroup']['title'];

                $user_userGroups[$numResult] = $group_name . ': ' . $this->userGroups[$results['UserGroup']['id']]['descri'];
            }

            $this->set('user_userGroups', $user_userGroups);
        }
        */
        
        if(isset($this->user->organization['Organization']['hasGasGroups']) && $this->user->organization['Organization']['hasGasGroups']=='Y')
        $this->render('admin_home_gas_groups');
        else
            $this->render('admin_home');
    }

    private function _admin_home_prod_gas() {
        $this->render('admin_home_prod_gas');
    }

    private function _admin_home_pact() {
        $this->render('admin_home_pact');
    }
	
    private function _admin_home_prod() {
        $this->render('admin_home_prod');
    }

    public function admin_msg_stop_browser() {
        $this->layout = 'default_front_end';
        $this->render('/Pages/msg_stop');
    }

    public function msg_stop() {
        $this->layout = 'default_front_end';
    }

	public function msg_not_order_state() {
		
		$debug = false;
		
		App::import('Model', 'Order');
		$Order = new Order;		
	
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $this->order_id];
		$options['recursive'] = 0;
		$results = $Order->find('first', $options);
		
		$this->set(compact('results'));
						
		$this->layout = 'default_front_end';
	}

    public function msg_question() {
        $this->layout = 'default_front_end';
    }

    public function msg_exclamation() {
        $this->layout = 'default_front_end';
    }

    public function msg_frontend_cart_preview() {
        $this->layout = 'default_front_end';
    }

    /*
     * Organization.type = PROD pagina per l'utente non associato ad alcun gruppo
     */

    public function msg_frontend_prod_user_group_not() {
        $this->layout = 'default_front_end';
    }

    /*
     * $this->user->organization['Organization']['hasUserFlagPrivacy'] = Y
     */
	public function msg_user_flag_privacy() {
	
		// $debug=true;
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			self::d($this->request->data, false);

			App::import('Model', 'UserProfile');
			$UserProfile = new UserProfile;
						 
			$UserProfile->setValue($this->user, $this->user->id, 'hasUserFlagPrivacy', 'Y', $debug);
			$UserProfile->setValue($this->user, $this->user->id, 'dataUserFlagPrivacy', date('Y-m-d H:i:s'), $debug);
			
			$this->Session->setFlash(__('msg_user_registration_expire_success'));
			$this->myRedirect(Configure::read('routes_fe_default'));
		}
        	
		App::import('Model', 'UserGroupMap');
	  	$UserGroupMap = new UserGroupMap();

	  	$userFlagPrivacy = [];
	  	$userFlagPrivacy = $UserGroupMap->getUserFlagPrivacy($this->user);
	  	$this->set(compact('userFlagPrivacy'));
		        	
		$this->layout = 'default_front_end';
        $this->render('/Pages/msg_user_flag_privacy');
    }
	
    public function msg_user_registration_expire() {
        $this->render('/Pages/msg_user_registration_expire');
    }
	
    public function msg_frontend_prod_delivery_not() {
        $this->layout = 'default_front_end';
    }

    public function admin_msg_stop() {
        $this->render('/Pages/msg_stop');
    }
    
	public function admin_msg_not_order_state() {
		
		$debug = false;
		
		App::import('Model', 'Order');
		$Order = new Order;		
	
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $this->order_id];
		$options['recursive'] = 0;
		$results = $Order->find('first', $options);
		
		$this->set(compact('results'));
        
		/*
		 * invio mail 
         */
        if (Configure::read('sys_send_mail_error') == 'Y') {
    		$body_mail = '';
    		$body_mail .= '<h2>Utente ' . $this->user->name . ' (' . $this->user->id . ') - Organization ' . $this->user->organization['Organization']['name'] . ' (' . $this->user->organization['Organization']['id'] . ')</h2>';
    		$body_mail .= 'Username ' . $this->user->username . ' Email ' . $this->user->email;
    		$body_mail .= '<h2>Variabili del Server</h2>';
    		if (isset($_SERVER['HTTP_USER_AGENT']))
    			$body_mail .= '<br /><br />HTTP_USER_AGENT ' . $_SERVER['HTTP_USER_AGENT'];
    		else
    			$body_mail .= '<br /><br />HTTP_USER_AGENT non valorizzato';

    		if (isset($_SERVER['HTTP_COOKIE']))
    			$body_mail .= '<br /><br />HTTP_COOKIE ' . $_SERVER['HTTP_COOKIE'];
    		else
    			$body_mail .= '<br /><br />HTTP_COOKIE non valorizzato <span style="color:red">allowAdminAccess</span>';

    		if (isset($_SERVER['HTTP_REFERER']))
    			$body_mail .= '<br /><br />HTTP_REFERER ' . $_SERVER['HTTP_REFERER'];
    		else
    			$body_mail .= '<br /><br />HTTP_REFERER non valorizzato';

    		$body_mail .= '<h2>Order</h2>';
    		$body_mail .= 'order_id ' . $results['Order']['id'];
    		self::d($body_mail, $debug);
    		
    		App::import('Model', 'Mail');
    		$Mail = new Mail;

    		$Email = $Mail->getMailSystem($this->user);

    		$subject_mail = "Page " . $this->action;
    		$Email->subject($subject_mail);
    		if (!empty($this->user->organization['Organization']['www']))
    			$Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))]);
    		else
    			$Email->viewVars(['body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))]);

    		$name = Configure::read('SOC.name');
    		$mail = 'francesco.actis@gmail.com'; // @portalgas.it e' tra EmailExcludeDomains
    		self::d('mail TO '.$mail, $debug);

    		$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
    		$Email->to($mail);

    		$Mail->send($Email, $mail, $body_mail, false);
    	} // end if (Configure::read('sys_send_mail_error') == 'Y')

		$this->render('/Pages/msg_not_order_state');
    }
	
    public function admin_msg_question() {
        $this->render('/Pages/msg_question');
    }

    public function admin_msg_exclamation() {
        $this->render('/Pages/msg_exclamation');
    }
   
    public function error_permission_guest() {
        $msgArray = CakeSession::read('Message');
        if (empty($msgArray) || !isset($msgArray['Message']['flash']))
            $this->Session->setFlash(__('msg_not_permission_guest'));

        $this->layout = 'default_front_end';
        $this->render('/Pages/message');
    }

    public function exportDocsUserIntro() {

        if ($this->user->id == 0 || $this->user->organization_id != $this->user->organization['Organization']['id']) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        $user_id = $this->user->get('id');
        $this->_exportDocsUserIntro($user_id);

        $this->layout = 'default_front_end';
    }

    public function admin_export_docs_root() {

        if (!$this->isRoot()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
    }

    public function admin_export_docs_user_intro() {
 
        $user_id = $this->user->get('id');
        $this->_exportDocsUserIntro($user_id);

        $this->set('isRoot', $this->isRoot());
        $this->set('isManager', $this->isManager());
        $this->set('isManagerDelivery', $this->isManagerDelivery());
        $this->set('isReferentGeneric', $this->isReferentGeneric());
        $this->set('isSuperReferente', $this->isSuperReferente());
        $this->set('isReferente', $this->isReferente());
        $this->set('isTesoriere', $this->isTesoriere());
        $this->set('isTesoriereGeneric', $this->isTesoriereGeneric());
        $this->set('isCassiere', $this->isCassiereGeneric());
        $this->set('isStoreroom', $this->isStoreroom());
		
        /*
         * stampa carrello di un utente scelto 
         */
        if ($this->isRoot() || $this->isManager()) {

            App::import('Model', 'User');
            $User = new User;

            $options = [];
            $options['conditions'] = ['User.organization_id' => (int) $this->user->organization['Organization']['id'],
                					  'User.block' => 0];
            $options['fields'] = ['User.id', 'name'];
            $options['recursive'] = -1;
            $options['order'] = Configure::read('orderUser');
            $users = $User->find('list', $options);

            $this->set(compact('users'));
        } // end if($this->isRoot() || $this->isManager())

        /*
         * stampa richiesta di pagamento di un utente scelto 
         */
        if ($this->user->organization['Template']['payToDelivery'] == 'POST' || $this->user->organization['Template']['payToDelivery'] == 'ON-POST') {
            if ($this->isRoot() || $this->isManager() || $this->isTesoriereGeneric()) {

                App::import('Model', 'RequestPayment');
                $RequestPayment = new RequestPayment;

                $options = [];
                $options['conditions'] = ['RequestPayment.organization_id' => $this->user->organization['Organization']['id'],
                    					  'RequestPayment.stato_elaborazione' => 'OPEN'];
                $options['order'] = 'RequestPayment.created DESC';
                $options['recursive'] = -1;
                $tmpResults = $RequestPayment->find('all', $options);

                $requestPaymentsListResults = [];
                foreach ($tmpResults as $numResult => $tmpResult) {
                    $requestPaymentsListResults[$tmpResult['RequestPayment']['id']] = 'Richiesta di pagamento num. ' . $tmpResult['RequestPayment']['num'];
                }
                $this->set(compact('requestPaymentsListResults'));
            } // end if($this->isRoot() || $this->isManager() || $this->isTesoriereGeneric())
        }

        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;

        $conditions = [];
        $suppliersOrganizationResults = $SuppliersOrganization->getSuppliersOrganization($this->user, $conditions);
		
        $this->layout = 'default';
    }

    private function _exportDocsUserIntro($user_id = 0) {

        if (empty($user_id)) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        App::import('Model', 'Delivery');
        $Delivery = new Delivery;


        /*
         * R E Q U E S T - P A Y M E N T S - dello U S E R 
         */
        if ($this->user->organization['Template']['payToDelivery'] == 'POST' || $this->user->organization['Template']['payToDelivery'] == 'ON-POST') {
            App::import('Model', 'SummaryPayment');
            $SummaryPayment = new SummaryPayment;

            $options = [];
            $options['conditions'] = ['SummaryPayment.organization_id' => $this->user->organization['Organization']['id'],
						                'RequestPayment.organization_id' => $this->user->organization['Organization']['id'],
						                'SummaryPayment.stato !=' => 'PAGATO',
						                'RequestPayment.stato_elaborazione' => 'OPEN',
						                'User.id' => $user_id];
            $options['order'] = 'RequestPayment.created DESC';
            $options['recursive'] = 1;
            $requestPaymentsResults = $SummaryPayment->find('all', $options);
            $this->set(compact('requestPaymentsResults'));
        }

        /*
         * D E L I V E R I E S
         */
        $options = [];
        $options['conditions'] = ['Delivery.organization_id' => (int) $this->user->organization['Organization']['id'],
            'Delivery.isVisibleBackOffice' => 'Y',
            'Delivery.sys' => 'N',
            'Delivery.type'=> 'GAS', // GAS-GROUP
            'DATE(Delivery.data) >= CURDATE() - INTERVAL ' . Configure::read('GGinMenoPerEstrarreDeliveriesCartInTabs') . ' DAY '];
        $options['order'] = ['Delivery.data' => 'asc'];
        $options['fields'] = ['Delivery.id', 'Delivery.luogoData'];
        $options['recursive'] = 1;
        $deliveries = $Delivery->find('list', $options);

        /*
         * ctrl se inserire anche la consegna Da definire, ctrl se ha ordini
         */
        $options = [];
        $options['conditions'] = ['Delivery.organization_id' => (int) $this->user->organization['Organization']['id'],
						            'Delivery.isVisibleBackOffice' => 'Y',
                                    'Delivery.type' => 'GAS',  // GAS-GROUP
						            'Delivery.sys' => 'Y'];
        $options['order'] = ['Delivery.data ASC'];
        //$options['fields'] = ['id', 'luogoData'];
        $options['recursive'] = 1;
        $deliveriesSys = $Delivery->find('all', $options);
        if (!empty($deliveriesSys[0]['Order'])) {
            $deliveries[$deliveriesSys[0]['Delivery']['id']] = $deliveriesSys[0]['Delivery']['luogo'];
        }
        $this->set(compact('deliveries'));


        /*
         * S U P P L I E R S
         */
        $suppliersOrganization = [];

        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;

        $suppliersOrganizationResults = $SuppliersOrganization->getSuppliersOrganization($this->user, $conditions = []);
        if (!empty($suppliersOrganizationResults))
            foreach ($suppliersOrganizationResults as $suppliersOrganizationResult) {
                $suppliersOrganization[$suppliersOrganizationResult['SuppliersOrganization']['id']] = $suppliersOrganizationResult['SuppliersOrganization']['name'];
            }
        $this->set(compact('suppliersOrganization'));
        
        if($this->user->organization['Organization']['hasDes']=='Y') 
            $this->_articlesDes();
        
        
        /*
         * get elenco ordini per filtrare articoli
         */
        App::import('Model', 'Order');
        $Order = new Order;

        $options = [];
        $options['conditions'] = ['Order.organization_id' => (int) $this->user->organization['Organization']['id'],
					            'Delivery.organization_id' => (int) $this->user->organization['Organization']['id'],
					            'Order.isVisibleBackOffice' => 'Y',
					            'Delivery.isVisibleBackOffice' => 'Y',
					            'Delivery.stato_elaborazione' => 'OPEN'];

        $options['order'] = ['Delivery.data ASC, Order.data_inizio ASC'];
        $options['recursive'] = 1;
        $results = $Order->find('all', $options);
        $orders = [];
        if (!empty($results))
            foreach ($results as $result) {
                if ($result['Delivery']['sys'] == 'N')
                    $label = $result['Delivery']['luogoData'];
                else
                    $label = $result['Delivery']['luogo'];

                if ($result['Order']['data_fine_validation'] != Configure::read('DB.field.date.empty'))
                    $data_fine = $result['Order']['data_fine_validation_'];
                else
                    $data_fine = $result['Order']['data_fine_'];

                $orders[$result['Order']['id']] = $label . ' ' . $result['SuppliersOrganization']['name']; /* .' - dal '.$result['Order']['data_inizio_'].' al '.$data_fine; */
            }
        $this->set(compact('orders'));

        /*
         * filtri per anagrafica utenti
         */
        $filterUserGroups = [Configure::read('group_id_user') => __("UserGroupsUser"),
            Configure::read('group_id_manager') => __("UserGroupsManager"),
            Configure::read('group_id_manager_delivery') => __("UserGroupsManagerDelivery"),
            Configure::read('group_id_referent') => __("UserGroupsReferent"),
            Configure::read('group_id_super_referent') => __("UserGroupsSuperReferent"),
            Configure::read('group_id_tesoriere') => __("UserGroupsTesoriere")];

        /*
         * referente cassa (pagamento degli utenti alla consegna)
         */
        if ($this->user->organization['Template']['payToDelivery'] == 'ON' || $this->user->organization['Template']['payToDelivery'] == 'ON-POST')
            $filterUserGroups += array(Configure::read('group_id_cassiere') => __("UserGroupsCassiere"));

        $this->set(compact('filterUserGroups'));
		
		/*
		 * docsCreates
		 */
        App::import('Model', 'DocsCreateUser');
        $DocsCreateUser = new DocsCreateUser;

		$options = [];
		$options['conditions'] = ['DocsCreateUser.organization_id' => $this->user->organization['Organization']['id'],
								  'DocsCreateUser.user_id' => $this->user->id,
								  'DocsCreate.stato' => 'Y',
								  'DocsCreate.organization_id' => $this->user->organization['Organization']['id']];
		$options['order'] = ['DocsCreate.created ASC'];
		$options['recursive'] = 1;
		$docsCreatesResults = $DocsCreateUser->find('all', $options);
		
		$newDocsCreatesResults = [];
		if(!empty($docsCreatesResults)) 
			foreach($docsCreatesResults as $docsCreatesResult) {
				$newDocsCreatesResults[$docsCreatesResult['DocsCreate']['id']] =  $docsCreatesResult['DocsCreateUser']['num'].'/'.$docsCreatesResult['DocsCreateUser']['year'].' '.$docsCreatesResult['DocsCreate']['name'];
			}
		$this->set('docsCreatesResults', $newDocsCreatesResults);
		
		/*
		 * storeroom
		 */
		$this->_export_docs_storeroom();
		
        /*
         * gestione desUserManager 
         */
        $organizationsResults = [];
        if ($this->user->organization['Organization']['hasDes'] == 'Y' && 
            $this->user->organization['Organization']['hasDesUserManager'] == 'Y' && 
            !empty($this->user->des_id) && 
            $this->isManagerUserDes()
            ) {
            
	        App::import('Model', 'DesOrganization');
	        $DesOrganization = new DesOrganization;
	                    
	        /*
	         * tutti i GAS del DES
	         */
	        $options = [];
	        $options['conditions'] = ['DesOrganization.des_id' => $this->user->des_id];
	        $options['order'] = ['Organization.name' => 'asc'];
	        $options['recursive'] = 1;
	        $desOrganizationsResults = $DesOrganization->find('all', $options);
	        if(!empty($desOrganizationsResults)) 
	        foreach($desOrganizationsResults as $desOrganizationsResult)
	        	$organizationsResults[$desOrganizationsResult['Organization']['id']] = $desOrganizationsResult['Organization']['name'];            
        }
        
        if(empty($organizationsResults)) {
        	$organizationsResults[$this->user->organization['Organization']['id']] = $this->user->organization['Organization']['name'];
        }
        
        $this->set(compact('organizationsResults'));		
    }

    public function admin_utility_docs_cassiere() {

        if (!$this->isCassiereGeneric()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
    }

    public function admin_export_docs_cassiere() {

        if (!$this->isCassiereGeneric()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        /*
         * elenco consegne `Order`.state_code = 'PROCESSED-ON-DELIVERY' OR `Order`.state_code = 'CLOSE'
         */
        if ($this->user->organization['Template']['payToDelivery'] == 'ON' ||
                $this->user->organization['Template']['payToDelivery'] == 'ON-POST') {

            if ($this->isCassiere()) {

                $sql = "SELECT
						Delivery.id, Delivery.luogo, Delivery.data 
					FROM
						" . Configure::read('DB.prefix') . "orders `Order`,
						" . Configure::read('DB.prefix') . "deliveries Delivery 
					WHERE
						`Order`.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						and Delivery.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						and Delivery.isVisibleBackOffice = 'Y' 
						and Delivery.stato_elaborazione = 'OPEN' 
						and Delivery.sys = 'N' 
						and (`Order`.state_code = 'PROCESSED-ON-DELIVERY' OR `Order`.state_code = 'CLOSE') 					
						and `Order`.delivery_id = Delivery.id 
						ORDER BY Delivery.data ASC";
            } else
            if ($this->isReferentCassiere()) {

                $sql = "SELECT
						Delivery.id, Delivery.luogo, Delivery.data 
					FROM
						" . Configure::read('DB.prefix') . "suppliers_organizations SuppliersOrganization,
						" . Configure::read('DB.prefix') . "orders `Order`,
						" . Configure::read('DB.prefix') . "deliveries Delivery 
					WHERE
						 SuppliersOrganization.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						and `Order`.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						and Delivery.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						and Delivery.isVisibleBackOffice = 'Y' 
						and Delivery.stato_elaborazione = 'OPEN' 
						and Delivery.sys = 'N' 
						and (`Order`.state_code = 'PROCESSED-ON-DELIVERY' OR `Order`.state_code = 'CLOSE') 					
						and `Order`.delivery_id = Delivery.id 
						and `Order`.supplier_organization_id = SuppliersOrganization.id 
						and SuppliersOrganization.id IN (" . $this->user->get('ACLsuppliersIdsOrganization') . ")
						ORDER BY Delivery.data ASC";
            }

            if ($debug) debug($sql);
            $results = $this->Page->query($sql);

            if (!empty($results))
                foreach ($results as $result) {
                    $DeliveryData = date('d', strtotime($result['Delivery']['data'])) . '/' . date('n', strtotime($result['Delivery']['data'])) . '/' . date('Y', strtotime($result['Delivery']['data']));
                    $deliveries[$result['Delivery']['id']] = $DeliveryData . ' - ' . $result['Delivery']['luogo'];
                }
            $this->set('deliveries', $deliveries);

            /*
             * cash
             */
            App::import('Model', 'User');
            $User = new User;

            App::import('Model', 'Cash');
            $Cash = new Cash;

            $options = [];
            $options['conditions'] = ['User.organization_id' => $this->user->organization['Organization']['id'],
                					  'User.block' => 0];

            $options['recursive'] = -1;
            $options['order'] = Configure::read('orderUser');
            $cashs = $User->find('all', $options);

            foreach ($cashs as $numResult => $result) {

                $options = [];
                $options['conditions'] = ['Cash.organization_id' => $this->user->organization['Organization']['id'],
                    					  'Cash.user_id' => $result['User']['id']];
                $userResults = $Cash->find('first', $options);
                if (!empty($userResults))
                    $cashs[$numResult]['Cash'] = $userResults['Cash'];
                else {
                    $cashs[$numResult]['Cash']['importo'] = '0.00';
                    $cashs[$numResult]['Cash']['importo_'] = '0,00';
                    $cashs[$numResult]['Cash']['importo_e'] = '0,00 &euro;';
                    $cashs[$numResult]['Cash']['nota'] = '';
                }
            }

            $this->set(compact('cashs'));

            /*
             * POS
             */
            if ($this->user->organization['Organization']['hasFieldPaymentPos'] == 'Y') {

                $years_pos = [];
                for ($i = 2015; $i <= date('Y'); $i++)
                    $years_pos[$i] = $i;
                $this->set(compact('years_pos'));
            }
        } // end if($this->user->organization['Template']['payToDelivery']=='ON' || $this->user->organization['Template']['payToDelivery']=='ON-POST') {

        $years = [];
        for ($i = 2016; $i <= date('Y'); $i++)
            $years[$i] = $i;
        $this->set(compact('years'));            
    }

    public function admin_export_docs_request_payment($request_payment_id=0) {

		if (empty($request_payment_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		if(!$this->isTesoriereGeneric() && !$this->isReferentTesoriere()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
        
		App::import('Model', 'RequestPayment');
		$RequestPayment = new RequestPayment;
        
        $options = [];
		$options['conditions'] = ['RequestPayment.organization_id' => $this->user->organization['Organization']['id'],
								  'RequestPayment.id' => $request_payment_id];
		$options['recursive'] = -1; 
		$requestPaymentResults = $RequestPayment->find('first', $options);
	
		$this->set('requestPaymentResults', $requestPaymentResults);
		
		$tot_importo = $RequestPayment->getTotImporto($this->user, $request_payment_id);
		$this->set('tot_importo', $tot_importo);
		
        App::import('Model', 'SummaryPayment');
        $SummaryPayment = new SummaryPayment;
		
        /*
         * stampa richiesta di pagamento di un utente scelto 
         */
        if ($this->user->organization['Template']['payToDelivery'] == 'POST' || $this->user->organization['Template']['payToDelivery'] == 'ON-POST') {
            if ($this->isRoot() || $this->isManager() || $this->isTesoriereGeneric()) {

		            App::import('Model', 'User');
		            $User = new User;
		
		            $options = [];
		            $options['conditions'] = ['User.organization_id' => (int) $this->user->organization['Organization']['id'],
		                					'User.block' => 0];
		            $options['fields'] = ['User.id', 'User.name'];
		            $options['recursive'] = -1;
		            $options['order'] = Configure::read('orderUser');
		            $users = $User->find('list', $options);
		
		            $this->set(compact('users'));
		            
            } // end if($this->isRoot() || $this->isManager() || $this->isTesoriereGeneric())
        }
        		
        $this->set('isRoot', $this->isRoot());
        $this->set('isManager', $this->isManager());
        $this->set('isTesoriereGeneric', $this->isTesoriereGeneric());		
        
	}

    public function admin_export_docs_delivery($delivery_id) {
        $this->set(compact('delivery_id'));
    }

    public function admin_export_docs_articles() {

        $user_id = $this->user->get('id');
        if ($user_id == 0) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        /*
         * S U P P L I E R S
         */
        $suppliersOrganization = [];

        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;

        $suppliersOrganizationResults = $SuppliersOrganization->getSuppliersOrganization($this->user, $conditions = []);
        if (!empty($suppliersOrganizationResults))
            foreach ($suppliersOrganizationResults as $suppliersOrganizationResult) {
                $suppliersOrganization[$suppliersOrganizationResult['SuppliersOrganization']['id']] = $suppliersOrganizationResult['SuppliersOrganization']['name'];
            }
        $this->set(compact('suppliersOrganization'));

        if($this->user->organization['Organization']['hasDes']=='Y') 
            $this->_articlesDes();
        
        /*
         * A C L - S U P P L I E R S
         * estraggo solo i produttori del referente per una stampa piu' completa
         */
        $options = [];
        $options['conditions'] = ['SuppliersOrganization.organization_id = ' . (int) $this->user->organization['Organization']['id'],
            'SuppliersOrganization.stato != ' => 'N',
            'SuppliersOrganization.id IN (' . $this->user->get('ACLsuppliersIdsOrganization') . ')'];
        $options['order'] = ['SuppliersOrganization.name'];
        $options['recursive'] = -1;
        $aclSuppliersOrganization = $SuppliersOrganization->find('list', $options);
        $this->set(compact('aclSuppliersOrganization'));
    }

    public function admin_export_docs_users() {
    
	    $this->set('isRoot', $this->isRoot());
        $this->set('isManager', $this->isManager());
        $this->set('isTesoriereGeneric', $this->isTesoriereGeneric());
    
        /*
         * filtri per anagrafica utenti
         */
        $filterUserGroups = [Configure::read('group_id_user') => __("UserGroupsUser"),
            Configure::read('group_id_manager') => __("UserGroupsManager"),
            Configure::read('group_id_manager_delivery') => __("UserGroupsManagerDelivery"),
            Configure::read('group_id_referent') => __("UserGroupsReferent"),
            Configure::read('group_id_super_referent') => __("UserGroupsSuperReferent"),
            Configure::read('group_id_tesoriere') => __("UserGroupsTesoriere"),
            Configure::read('group_id_generic') => __("UserGroupsGeneric")];

        /*
         * referente cassa (pagamento degli utenti alla consegna)
         */
        if ($this->user->organization['Template']['payToDelivery'] == 'ON' || $this->user->organization['Template']['payToDelivery'] == 'ON-POST')
            $filterUserGroups += [Configure::read('group_id_cassiere') => __("UserGroupsCassiere")];

        $this->set(compact('filterUserGroups'));
        
        /*
         * gestione desUserManager 
         */
        $organizationsResults = [];
        if ($this->user->organization['Organization']['hasDes'] == 'Y' && 
            $this->user->organization['Organization']['hasDesUserManager'] == 'Y' && 
            !empty($this->user->des_id) && 
            $this->isManagerUserDes()
            ) {
            
	        App::import('Model', 'DesOrganization');
	        $DesOrganization = new DesOrganization;
	                    
	        /*
	         * tutti i GAS del DES
	         */
	        $options = [];
	        $options['conditions'] = ['DesOrganization.des_id' => $this->user->des_id];
	        $options['order'] = ['Organization.name' => 'asc'];
	        $options['recursive'] = 1;
	        $desOrganizationsResults = $DesOrganization->find('all', $options);
	        if(!empty($desOrganizationsResults)) 
	        foreach($desOrganizationsResults as $desOrganizationsResult)
	        	$organizationsResults[$desOrganizationsResult['Organization']['id']] = $desOrganizationsResult['Organization']['name'];
	        	            
        }
        
        if(empty($organizationsResults)) {
        	$organizationsResults[$this->user->organization['Organization']['id']] = $this->user->organization['Organization']['name'];
        }
        
        $this->set(compact('organizationsResults'));
    }

    public function admin_export_docs_storeroom() {
		$this->_export_docs_storeroom();
	}
	
    public function _export_docs_storeroom() {
		
        App::import('Model', 'Storeroom');
        $Storeroom = new Storeroom;
		
		$storeroomUser = $Storeroom->getStoreroomUser($this->user);
		
		/*
		 *  ctrl se lo user corrente e' la dispensa
		 */	
		$isUserCurrentStoreroom = false;
		if(!empty($storeroomUser) && $storeroomUser['User']['id']==$this->user->get('id') && 
			$storeroomUser['User']['organization_id']==$this->user->organization['Organization']['id']) {
				$isUserCurrentStoreroom = true;	
		}		
		$this->set('isUserCurrentStoreroom',$isUserCurrentStoreroom);	
        $this->set('isManager', $this->isManager());
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
 		$options = [];
		$options['conditions'] = ['Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
									  'Delivery.isVisibleBackOffice' => 'Y',
									  'Delivery.isToStoreroom' => 'Y',
 									  'Delivery.sys'=> 'N',
									   'Delivery.type'=> 'GAS', // GAS-GROUP
									  'Delivery.stato_elaborazione' => 'OPEN'];
        $options['fields'] = ['id', 'luogoData'];
		$options['order'] = 'data ASC';
		$options['recursive'] = -1;
		$deliveriesStorerooms = $Delivery->find('list', $options);
		$this->set(compact('deliveriesStorerooms'));

        /*
         * D E L I V E R I E S - T o - C A R T S
         */	
        $cartsDeliveries = [];	
		if($isUserCurrentStoreroom) {
	        $options = [];
	        $options['conditions'] = array('Delivery.organization_id' => (int) $this->user->organization['Organization']['id'],
	            'Delivery.isVisibleBackOffice' => 'Y',
	            'Delivery.sys' => 'N',
                'Delivery.type'=> 'GAS', // GAS-GROUP
	            'DATE(Delivery.data) >= CURDATE() - INTERVAL ' . Configure::read('GGinMenoPerEstrarreDeliveriesInTabs') . ' DAY ');
	        $options['order'] = ['Delivery.data' => 'asc'];
	        $options['fields'] = array('Delivery.id', 'Delivery.luogoData');
	        $options['recursive'] = 1;
	        $cartsDeliveries = $Delivery->find('list', $options);
	
	        /*
	         * ctrl se inserire anche la consegna Da definire, ctrl se ha ordini
	         */
	        $options = [];
	        $options['conditions'] = array('Delivery.organization_id' => (int) $this->user->organization['Organization']['id'],
	            'Delivery.isVisibleBackOffice' => 'Y',
                'Delivery.type' => 'GAS',  // GAS-GROUP
	            'Delivery.sys' => 'Y');
	        $options['order'] = array('data ASC');
	        //$options['fields'] = array('id', 'luogoData');
	        $options['recursive'] = 1;
	        $deliveriesSys = $Delivery->find('all', $options);
	        if (!empty($deliveriesSys[0]['Order'])) {
	            $cartsDeliveries[$deliveriesSys[0]['Delivery']['id']] = $deliveriesSys[0]['Delivery']['luogo'];
	        }
	        $this->set(compact('cartsDeliveries'));
		}	
	}
	
    /*
     * articoli per produttori D E S 
     */    
    private function _articlesDes() {

        $desSupplierResults = [];

        App::import('Model', 'DesOrganization');
        App::import('Model', 'DesSupplier');

        $DesOrganization = new DesOrganization;
        $DesOrganization->unbindModel(array('belongsTo' => array('Organization')));

        $options = [];
        $options['conditions'] = array('DesOrganization.organization_id' => $this->user->organization['Organization']['id']);
        $options['order'] = array('De.name');
        $options['recursive'] = 0;
        $desOrganizationResults = $DesOrganization->find('all', $options);

        /*
         * loop per ogni DES, lo user puo' non aver scelto il DES 
         */
        foreach($desOrganizationResults as $numResult => $desOrganizationResult) {
            $DesSupplier = new DesSupplier();
            $DesSupplier->unbindModel(array('hasMany' => array('DesOrder')));
            $DesSupplier->unbindModel(array('belongsTo' => array('OwnOrganization')));

            $options = [];
            $options['conditions'] = array('DesSupplier.des_id' => $desOrganizationResult['De']['id']);
            $options['recursive'] = 1;
            $desSupplierTmpResults = $DesSupplier->find('all', $options);                    

            foreach($desSupplierTmpResults as $desSupplierResult) {
               $desSupplierResults[$desOrganizationResult['De']['id']][$desSupplierResult['DesSupplier']['id']] = $desSupplierResult['Supplier']['name'];
            }
        }

        $this->set(compact('desOrganizationResults'));
        $this->set(compact('desSupplierResults'));
                    
    } 
                            
}
