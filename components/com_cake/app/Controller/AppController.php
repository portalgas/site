<?php

App::uses('Controller', 'Controller');
App::uses('UtilsCommons', 'Lib');
App::uses('UtilsCrons', 'Lib');  // ho la gestione degli stati di Order o ProdDelivery

class AppController extends Controller {

    public $components = array('Session',
        'Cookie',
        'ActionsOrder',
        'ActionsProdDelivery',
        'UserGroups');
    public $helpers = array('App',
        'Html',
        'Form',
        'Session',
        'Time',
        'Ajax',
		'MenuOrders');
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

    public function beforeFilter() {

        $debug = false;

        date_default_timezone_set('Europe/Rome');

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


        if ($debug) {
            echo "<pre>------- BEFORE APPController ---------";
            print_r($this->user);
            echo "</pre>------- BEFORE APPController ---------";
        }

        /*
         *  B A C K O F F I C E
         */
        if ($this->params['prefix'] == 'admin') {

            /*
             * D E S, se ho + des 
             */
            if (isset($this->request->data['DesOrganization']['des_id'])) {
                $this->user->set('des_id', $this->request->data['DesOrganization']['des_id']);
                $this->__addParamsDesJUser($this->user, $debug);
            }

            if ($this->__resourcesDesEnabled($this->name, $this->action) && empty($this->user->des_id)) {
                /*
                 * se sono associato ad un solo DES
                 */
                App::import('Model', 'DesOrganization');
                $DesOrganization = new DesOrganization;

                $options = array();
                $options['conditions'] = array('DesOrganization.organization_id' => $this->user->organization['Organization']['id']);
                $options['fields'] = array('DesOrganization.des_id');
                $options['recursive'] = -1;
                $desOrganizationResults = $DesOrganization->find('all', $options);
                /*
                  echo "<pre>";
                  print_r($desOrganizationResults);
                  echo "</pre>";
                 */
                if (count($desOrganizationResults) == 1) {
                    $des_id = $desOrganizationResults[0]['DesOrganization']['des_id'];
                    // echo "<br />des_id ".$des_id;
                    $this->user->set('des_id', $des_id);
                    $this->__addParamsDesJUser($this->user, $debug);
                } else {
                    $this->Session->setFlash(__('msg_des_not_selected'));
                    if (!$debug)
                        $this->myRedirect(array('controller' => 'DesOrganizations', 'action' => 'choice', 'admin' => true));
                }
            }

            /*
             * root
             */
            if ($this->isRoot()) {

                if (!$this->__resourcesRootEnabled($this->name, $this->action) && empty($this->user->organization)) {
                    $this->Session->setFlash(__('msg_organization_not_selected'));
                    if (!$debug)
                        $this->myRedirect(array('controller' => 'organizations', 'action' => 'choice', 'admin' => true));
                }

                /*
                 * carica l'organization in Session solo se cambia
                 */
                if (isset($this->request->data['Organization']['organization_id'])) {
                    $this->user->set('organization', $this->__getOrganization($this->request->data['Organization']['organization_id']));
                    $this->__addParamsJUser($this->user);
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
                    $this->user->set('organization', $this->__getOrganization($this->user->get('organization_id')));
                    $this->__addParamsJUser($this->user);
                }

                if ($this->user->get('supplier_id') > 0 && empty($this->user->supplier)) {
                    $this->user->set('supplier', $this->__getSupplier($this->user->get('supplier_id')));
                    $this->__addParamsJUser($this->user);
                }
            } // end if($this->isRoot())

            /*
             * cookies setcookie(name,value,expire,path,domain,secure,httponly);
             * e session
             * */
            if ($this->user->organization['Organization']['type'] == 'GAS') {
                if (isset($this->request->pass['delivery_id'])) {
                    $delivery_id = $this->request->pass['delivery_id'];
                    if ($delivery_id != '' && $delivery_id != null && !is_numeric($delivery_id)) {
                        $this->Session->setFlash(__('msg_error_params'));
                        $this->myRedirect(Configure::read('routes_msg_exclamation'));
                    }
                    setcookie('delivery_id', $delivery_id, time() + 86400 * 365 * 1, Configure::read('App.server'));  // (86400 secs per day for 1 years)
                    $this->Session->write('delivery_id', $delivery_id);
                } else
                if (isset($_COOKIE['delivery_id']) && !empty($_COOKIE['delivery_id']))
                    $this->Session->write('delivery_id', $_COOKIE['delivery_id']);

                if (isset($this->request->pass['order_id'])) {
                    $order_id = $this->request->pass['order_id'];
                    if ($order_id != '' && $order_id != null && !is_numeric($order_id)) {
                        $this->Session->setFlash(__('msg_error_params'));
                        $this->myRedirect(Configure::read('routes_msg_exclamation'));
                    }
                    setcookie('order_id', $order_id, time() + 86400 * 365 * 1, Configure::read('App.server'));
                    $this->Session->write('order_id', $order_id);
                } else
                if (isset($_COOKIE['order_id']) && !empty($_COOKIE['order_id']))
                    $this->Session->write('order_id', $_COOKIE['order_id']);

                $this->delivery_id = $this->Session->read('delivery_id');
                $this->order_id = $this->Session->read('order_id');

                $this->set('delivery_id', $this->delivery_id);
                $this->set('order_id', $this->order_id);

                //if(Configure::read('developer.mode')) echo 'AppController delivery_id '.$this->delivery_id.' order_id '.$this->order_id.'<br />';
            }
            else
            if ($this->user->organization['Organization']['type'] == 'PROD') {
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
                $this->myRedirect(Configure::read('routes_msg_exclamation'));
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
                    $this->user->set('organization', $this->__getOrganization($this->user->organization['Organization']['id']));
                else
                    $this->user->set('organization', $this->__getOrganization($organization_id));
            }
        }

        // affinche' le date sul db e nelle view vengano tradotte il italiano
        $sql = "SET NAMES 'utf8'";
        $this->{$this->modelClass}->query($sql);
        $sql = "set lc_time_names = 'it_IT'";
        $this->{$this->modelClass}->query($sql);

        if ($debug) {
            echo "<pre>------- POST APPController ---------";
            print_r($this->user);
            echo "</pre>------- POST APPController ---------";
        }

        $this->set('user', $this->user);
    }

    public function reloadUserParams() {

        $debug = false;

        if ($debug) {
            echo "<pre>BEFORE ";
            print_r($this->user->get('ACLsuppliersIdsOrganization'));
            echo "</pre>";
        }

        $this->user->set('organization', $this->__getOrganization($this->user->organization['Organization']['id']));
        $this->__addParamsJUser($this->user);

        if ($debug) {
            echo "<pre>POST ";
            print_r($this->user->get('ACLsuppliersIdsOrganization'));
            echo "</pre>";
        }
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
          echo $_SERVER['HTTP_USER_AGENT'];
          echo "<pre>";
          print_r($_SERVER);
          echo "</pre>";
         */

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
     * anche in AppHelper
     */

    public function isUserPermissionArticlesOrder($user) {
        if ($user->organization['Organization']['hasArticlesOrder'] == 'Y' && $user->user['User']['hasArticlesOrder'] == 'Y')
            return true;
        else
            return false;
    }

    /*
     * in base allo stato dell'ordine
     * setto l'action possibile sull'ordine
     */

    public function actionToEditOrder($user, $results) {

        $actionToEditOrder = array();

        if (isset($results['Order'])) {

            if ($this->isUserPermissionArticlesOrder($user)) { // l'utente gestisce l'associazione degli articoli con l'ordine
                if ($results['Order']['state_code'] == 'CREATE-INCOMPLETE')
                    $actionToEditOrder = array('controller' => 'ArticlesOrders', 'action' => 'admin_add', 'title' => __('Add ArticlesOrder Error'));
                else
                if ($results['Order']['state_code'] == 'OPEN' ||
                        $results['Order']['state_code'] == 'OPEN-NEXT' ||
                        $results['Order']['state_code'] == 'PROCESSED-BEFORE-DELIVERY' ||
                        $results['Order']['state_code'] == 'PROCESSED-ON-DELIVERY' ||
                        $results['Order']['state_code'] == 'PROCESSED-POST-DELIVERY')
                    $actionToEditOrder = array('controller' => 'ArticlesOrders', 'action' => 'admin_index', 'title' => __('List Articles Orders'));
                else
                if ($results['Order']['state_code'] == 'WAIT-PROCESSED-TESORIERE' ||
                        $results['Order']['state_code'] == 'PROCESSED-TESORIERE' ||
                        $results['Order']['state_code'] == 'TO-PAYMENT' ||
                        $results['Order']['state_code'] == 'CLOSE')
                    $actionToEditOrder = array();
            }
            else {  // l'utente non gestisce l'associazione degli articoli con l'ordine
                if ($results['Order']['state_code'] == 'WAIT-PROCESSED-TESORIERE' ||
                        $results['Order']['state_code'] == 'PROCESSED-TESORIERE' ||
                        $results['Order']['state_code'] == 'TO-PAYMENT' ||
                        $results['Order']['state_code'] == 'CLOSE')
                    $actionToEditOrder = array();
                else
                    $actionToEditOrder = array('controller' => 'Articles', 'action' => 'context_order_index', 'title' => __('List Articles'));
            }
        }

        return $actionToEditOrder;
    }

    /*
     * in base allo stato dell'ordine
     * setto l'action possibile di un articolo
     */

    public function actionToEditArticle($user, $results) {

        $actionToEditArticle = array();
        if (isset($results['Order'])) {

            if ($this->isUserPermissionArticlesOrder($user)) {  // l'utente gestisce l'associazione degli articoli con l'ordine
                if ($results['Order']['state_code'] == 'CREATE-INCOMPLETE')
                    $actionToEditArticle = array('controller' => 'ArticlesOrders', 'action' => 'admin_add', 'title' => __('Add ArticlesOrder Error'));
                else
                if ($results['Order']['state_code'] == 'OPEN' ||
                        $results['Order']['state_code'] == 'OPEN-NEXT' ||
                        $results['Order']['state_code'] == 'PROCESSED-BEFORE-DELIVERY' ||
                        $results['Order']['state_code'] == 'PROCESSED-ON-DELIVERY' ||
                        $results['Order']['state_code'] == 'PROCESSED-POST-DELIVERY')
                    $actionToEditArticle = array('controller' => 'ArticlesOrders', 'action' => 'admin_edit', 'title' => __('Edit ArticlesOrder'));
                else
                if ($results['Order']['state_code'] == 'WAIT-PROCESSED-TESORIERE' ||
                        $results['Order']['state_code'] == 'PROCESSED-TESORIERE' ||
                        $results['Order']['state_code'] == 'TO-PAYMENT' ||
                        $results['Order']['state_code'] == 'CLOSE')
                    $actionToEditArticle = array();
            }
            else { // l'utente non gestisce l'associazione degli articoli con l'ordine
                if ($results['Order']['state_code'] == 'WAIT-PROCESSED-TESORIERE' ||
                        $results['Order']['state_code'] == 'PROCESSED-TESORIERE' ||
                        $results['Order']['state_code'] == 'TO-PAYMENT' ||
                        $results['Order']['state_code'] == 'CLOSE')
                    $actionToEditArticle = array();
                else
                    $actionToEditArticle = array('controller' => 'Articles', 'action' => 'admin_context_order_edit', 'title' => __('Edit Article'));
            }
        }

        return $actionToEditArticle;
    }

    /* ovveride lib/Cake/Controller/Controller.php 
     * code original 
     * 		$this->myRedirect(array('action' => 'index'));
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

            if (strpos($_SERVER['REQUEST_URI'], '/administrator/') === false)
                $newUrl = Configure::read('App.server') . '/';
            else
                $newUrl = Configure::read('App.server') . '/administrator/index.php';

            $newUrl .= '?';
            $newUrl .= 'option=' . $params['option'];
            $newUrl .= '&controller=' . $params['controller'];
            $newUrl .= '&action=' . $params['action'];
            if (isset($params['id']))
                $newUrl .= '&id=' . $params['id'];
            if (isset($url['supplier_organization_id']))
                $newUrl .= '&supplier_organization_id=' . $params['supplier_organization_id'];
            unset($params['option']);
            unset($params['controller']);
            unset($params['action']);
            unset($params['id']);
            unset($params['supplier_organization_id']);

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
              echo "<pre>";
              print_r($newUrl);
              echo "</pre>";
              exit;
             */

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

    public function importoToDatabase($importo) {
        // elimino le migliaia
        $importo = str_replace('.', '', $importo);

        // converto eventuali decimanali
        $importo = str_replace(',', '.', $importo);

        if (strpos($importo, '.') === false)
            $importo = $importo . '.00';

        return $importo;
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
     * 	    [groups] => Array ()
     * 	    [guest] => 1
     * 	    [organization] => Array()
     *
     *  private function __addParamsJUser()  richiamata anche da OrganizationController:admin_choice 
     * 															OrganizationController:admin_edit
     * 															DesOrganizationController:admin_choice 
     *  	[ACLsuppliersIdsOrganization] = elenco degli ID dei produttori abilitati a gestire
     *  	[ACLsuppliersIdsDes] = elenco degli ID dei produttori del DES abilitati a gestire
     *   [hasArticlesOrder] = Gestisci gli articoli associati all'ordine
     */

    private function __addParamsJUser($user) {

        /*
         * ACLsuppliersIdsOrganization    1, 3, 5  supplier_organization_id
         *    se Admin dell'organization
         * 			tutti suppliers_organizations.id dell'organization
         * 		se Referent
         * 			tutti suppliers_organizations.id associati allo user
         */
        $ACLsuppliersIdsOrganization = 0; // contiene stringa supplier_organization_id 1, 3, 5
        if ($this->isSuperReferente()) {
            App::import('Model', 'SuppliersOrganization');
            $SuppliersOrganization = new SuppliersOrganization;

            $ACLsuppliersIdsOrganization = $SuppliersOrganization->getSuppliersOrganizationIds($user);
        } else {
            App::import('Model', 'SuppliersOrganizationsReferent');
            $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

            $ACLsuppliersIdsOrganization = $SuppliersOrganizationsReferent->getSuppliersOrganizationIdsByReferent($user, $user->get('id'));
        }
        $this->user->set('ACLsuppliersIdsOrganization', $ACLsuppliersIdsOrganization);

        /*
         * ctrl e' associato ad un solo DES 
         */
        if ($user->organization['Organization']['hasDes'] == 'Y') {
            App::import('Model', 'DesOrganization');
            $DesOrganization = new DesOrganization;

            $options = array();
            $options['conditions'] = array('DesOrganization.organization_id' => $user->organization['Organization']['id']); // non ho scelto il DES, ctrl solo se il suo GAS e' titolare

            $options['fields'] = array('DesOrganization.des_id');
            $options['recursive'] = -1;
            $desOrganizationResults = $DesOrganization->find('all', $options);

            if (count($desOrganizationResults) == 1) {
                /*
                 * e' associato a 1 solo DES
                 */
                $user->des_id = $desOrganizationResults[0]['DesOrganization']['des_id'];

                $this->__addParamsDesJUser($user);
            }
        }


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
						AND Utente.id = " . $this->user->get('id');
            if (!$this->isRoot())
                $sql .= " AND Utente.organization_id = " . (int) $user->organization['Organization']['id'];
            // echo '<br />'.$sql;
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

        $this->user->set('user', $profileResults);
    }

    /*
     * JUser Object ( in libraries/joomla/user/user.php function load($id)
     * aggiungo [ACLsuppliersIdsOrganization], [ACLsuppliersIdsDes]
     * 
     * richiamata anche da DesOrganizationController:admin_choice 
     */

    protected function __addParamsDesJUser($user, $debug = false) {

        /*
         * ACLsuppliersIdsDes    1, 3, 5  des_suppliers_id
         */
        $ACLsuppliersIdsDes = 0; // contiene stringa des_suppliers_id 1, 3, 5
        if ($this->isSuperReferenteDes()) {
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

        $this->user->set('ACLsuppliersIdsDes', $ACLsuppliersIdsDes);
    }

    /*
     * configurazione organization[Organization] 
     * 		dati 
     *  	paramsConfig hasArticlesOrder, hasVisibility, hasTrasport, hasCostMore, hasCostLess, hasStoreroom, payToDelivery, hasDes, prodSupplierOrganizationId
     *  	paramsFields hasFieldArticleCodice, hasFieldArticleIngredienti, hasFieldArticleAlertToQta, hasFieldArticleCategoryId, hasFieldSupplierCategoryId,
     *  				 hasFieldFatturaRequired)
     */

    private function __getOrganization($organization_id = 0) {

        $results = array();

        if ($organization_id > 0) {
            App::import('Model', 'Organization');
            $Organization = new Organization;

            $options = array();
            $options['conditions'] = array('Organization.id' => (int) $organization_id);
            $options['fields'] = array('id', 'name', 'www', 'type', 'template_id', 'j_seo', 'j_group_registred', 'j_page_category_id', 'paramsConfig', 'paramsFields', 'lat', 'lng');
            $options['recursive'] = -1;
            $results = $Organization->find('first', $options);

            $paramsConfig = json_decode($results['Organization']['paramsConfig'], true);
            $paramsFields = json_decode($results['Organization']['paramsFields'], true);

            $results['Organization'] += $paramsConfig;
            $results['Organization'] += $paramsFields;

            unset($results['Organization']['paramsConfig']);
            unset($results['Organization']['paramsFields']);
        }

        return $results;
    }

    private function __getSupplier($supplier_id = 0) {

        $results = array();

        if ($supplier_id > 0) {
            App::import('Model', 'Supplier');
            $Supplier = new Supplier;

            $options = array();
            $options['conditions'] = array('Supplier.id' => (int) $supplier_id);
            $options['recursive'] = -1;
            $results = $Supplier->find('first', $options);
        }

        return $results;
    }

    /*
     * dagli Id dei produttori (ACLsuppliersIdsOrganization) 
     * estraggo la LIST dei produttori
     * 
     * call in Article/admin_add admin_edit admin_index
     * 		   Order/admin_add admin_edit 
     */

    public function getACLsuppliersOrganization() {

        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;

        $options = array();
        $options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
            'SuppliersOrganization.id IN (' . $this->user->get('ACLsuppliersIdsOrganization') . ')');
        $options['order'] = array('SuppliersOrganization.name');
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

        $options = array();
        $options['conditions'] = array('DesSupplier.des_id' => $this->user->des_id,
            'DesSupplier.id IN (' . $this->user->get('ACLsuppliersIdsDes') . ')',
            "(Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG')");
        $options['fields'] = array('DesSupplier.id', 'Supplier.name');
        $options['order'] = array('Supplier.name');
        $options['recursive'] = 1;
        $ACLsuppliersIdsDes = $DesSupplier->find('list', $options);

        /*
          echo "<pre>";
          print_r($options);
          print_r($ACLsuppliersIdsDes);
          echo "</pre>";
         */

        return $ACLsuppliersIdsDes;
    }

    /*
     * $url = $_SERVER['REQUEST_URI'],
     * ma in Order:admin_sotto_menu()
     * 		$_SERVER['REQUEST_URI']  = ...controller=Orders&action=sotto_menu
     * 		$_SERVER['HTTP_REFERER'] = $url
     */

    public function getToUrlControllerAction($url) {

        $pageCurrent = array('controller' => '', 'action' => '');

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
     * $modulo: sono in quel modulo e ctrl se ho anche altir moduli che possono andare in conflitto
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

        $debug = false;

        $alertModuleConflicts = '';

        App::import('Model', 'Order');
        $Order = new Order;


        $options = array();
        $options['conditions'] = array('Order.organization_id' => $user->organization['Organization']['id'],
            'Order.id' => $order_id);
        $options['fields'] = array('state_code', 'typeGest', 'hasTrasport', 'hasCostMore', 'hasCostLess', 'trasport', 'cost_more', 'cost_less');
        $options['recursive'] = -1;
        $results = $Order->find('first', $options);

        if ($debug) {
            echo 'Order.state_code ' . $results['Order']['state_code'] . '<br />';
            echo 'modulo ' . $modulo . '<br />';
            echo "<pre>";
            print_r($results);
            echo "</pre>";
        }

        /*
         *  ctrl valido solo quando il refeente puo' gestire in modo completo l'ordine
         */
        if ($results['Order']['state_code'] == 'PROCESSED-POST-DELIVERY' ||
                $results['Order']['state_code'] == 'INCOMING-ORDER') {
            switch ($modulo) {
                case 'managementCartsOne':
                    if ($results['Order']['typeGest'] == 'AGGREGATE') {

                        App::import('Model', 'SummaryOrder');
                        $SummaryOrder = new SummaryOrder;

                        $summaryOrdeResults = $SummaryOrder->select_to_order($user, $order_id);
                        if (!empty($summaryOrdeResults))
                            $alertModuleConflicts = 'summary_order_just_populate';
                    }
                    else
                    if ($results['Order']['typeGest'] == 'SPLIT') {
                        $alertModuleConflicts = 'order_change_qta';
                    }

                    if (empty($alertModuleConflicts)) {
                        if (($results['Order']['hasTrasport'] == 'Y' && $results['Order']['trasport'] != '0.00') ||
                                ($results['Order']['hasCostMore'] == 'Y' && $results['Order']['cost_more'] != '0.00') ||
                                ($results['Order']['hasCostLess'] == 'Y' && $results['Order']['cost_less'] != '0.00'))
                            $alertModuleConflicts = 'order_change_carts_one';
                    }
                    break;
                case 'managementCartsGroupByUsers':
                    if (($results['Order']['hasTrasport'] == 'Y' && $results['Order']['trasport'] != '0.00') ||
                            ($results['Order']['hasCostMore'] == 'Y' && $results['Order']['cost_more'] != '0.00') ||
                            ($results['Order']['hasCostLess'] == 'Y' && $results['Order']['cost_less'] != '0.00'))
                        $alertModuleConflicts = 'summary_order_change';
                    break;
            }
        } /* end if($results['Order']['state_code'] */

        if ($results['Order']['hasTrasport'] == 'Y' && $results['Order']['trasport'] != '0.00')
            $orderHasTrasport = 'Y';
        else
            $orderHasTrasport = 'N';

        if ($results['Order']['hasCostMore'] == 'Y' && $results['Order']['cost_more'] != '0.00')
            $orderHasCostMore = 'Y';
        else
            $orderHasCostMore = 'N';

        if ($results['Order']['hasCostLess'] == 'Y' && $results['Order']['cost_less'] != '0.00')
            $orderHasCostLess = 'Y';
        else
            $orderHasCostLess = 'N';

        /*
         * ctrl se ho un cookie settato per 
         * 		quel conflitto 
         * 		quell'ordine
         */
        if (isset($_COOKIE[$alertModuleConflicts])) {
            if ($_COOKIE[$alertModuleConflicts] == $order_id)
                $popUpDisabled = true;
            else
                $popUpDisabled = false;
        }

        if ($debug) {
            echo "<pre>_COOKIE[$alertModuleConflicts] ";
            print_r($_COOKIE);
            echo "</pre>";

            echo '<br />alertModuleConflicts ' . $alertModuleConflicts;
            echo '<br />orderHasTrasport ' . $orderHasTrasport;
            echo '<br />orderHasCostMore ' . $orderHasCostMore;
            echo '<br />orderHasCostLess ' . $orderHasCostLess;
        }

        $this->set('orderHasTrasport', $orderHasTrasport);
        $this->set('orderHasCostMore', $orderHasCostMore);
        $this->set('orderHasCostLess', $orderHasCostLess);
        $this->set('popUpDisabled', $popUpDisabled);
        $this->set('alertModuleConflicts', $alertModuleConflicts);
    }

    /*
     * gestisco il box con i dati dell'ordine
     */

    protected function __boxOrder($user, $delivery_id, $order_id, $opts) {

        /*
          App::import('Model', 'Delivery');
          $Delivery = new Delivery;

          $conditions = array('Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
          'Delivery.isVisibleBackOffice' => 'Y',
          'Delivery.stato_elaborazione' => 'OPEN');

          $deliveries = $Delivery->find('list',array('fields'=>array('id', 'luogoData'),'conditions'=>$conditions,'order'=>'data ASC','recursive'=>-1));
          if(empty($deliveries)) {
          $this->Session->setFlash(__('NotFoundDeliveries'));
          $this->myRedirect(Configure::read('routes_msg_exclamation'));
          }
          $this->set(compact('deliveries'));
         */

        App::import('Model', 'Order');
        $Order = new Order;

        $options = array();
        $options['conditions'] = array('Order.organization_id' => (int) $user->organization['Organization']['id'],
            'Delivery.organization_id' => (int) $user->organization['Organization']['id'],
            'Order.id' => $order_id,
            'Delivery.id' => $delivery_id);
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
        $permissions = array('isReferentGeneric' => $this->isReferentGeneric(),
            'isTesoriereGeneric' => $this->isTesoriereGeneric());
        $this->set('permissions', $permissions);

        $this->set('results', $results);
        $this->set('call_action', $this->action);
    }

    /*
     * gestisco il box con i dati della consegna del produttore
     */

    protected function __boxProdDelivery($user, $prod_delivery_id, $opts) {

        App::import('Model', 'ProdDelivery');
        $ProdDelivery = new ProdDelivery;

        $options = array();
        $options['conditions'] = array('ProdDelivery.organization_id' => (int) $user->organization['Organization']['id'],
            'ProdDelivery.id' => $prod_delivery_id);
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

    private function __resourcesRootEnabled($controller, $action) {

        $controllersEnabled = array('organizations',
            'ordersactions',
            'organizationspays',
            'templatesordersstates',
            'templatesordersstatesOrdersActions',
            'suppliers',
            'categoriessuppliers',
            'configurations',
            'logs',
            'mails',
            'helps');

        $controller = strtolower($controller);
        $action = strtolower($action);
        /*
          echo "<br />__resourcesRootEnabled() controller ".$controller;
          echo "<pre>";
          print_r($controllersEnabled);
          echo "<pre>in_array(controller, controllersEnabled) ".in_array($controller, $controllersEnabled);
         */
        if (in_array($controller, $controllersEnabled) || ($action == 'admin_msg_stop_browser'))
            return true;
        else
            return false;
    }

    /*
     *  risorse alle quali per accedere devo aver valorizzato des_id
     */

    private function __resourcesDesEnabled($controller, $action) {

        $controllersEnabled = array('desorganizations',
            'dessuppliersreferents',
            'desusergroupmap',
            'desorders',
            'des');

        $controller = strtolower($controller);
        $action = strtolower($action);

        // echo "<br />__resourcesDesEnabled() controller ".$controller." action ".$action;

        if ($controller == 'desorganizations' && $action == 'admin_choice')
            return false;
        else
        if (in_array($controller, $controllersEnabled))
            return true;
        else
            return false;
    }

}
