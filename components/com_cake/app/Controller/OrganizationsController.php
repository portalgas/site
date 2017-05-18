<?php

App::uses('AppController', 'Controller');

/**
 * Organizations Controller
 *
 * @property Organization $Organization
 */
class OrganizationsController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();

        /* ctrl ACL */
	   	if ($this->utilsCommons->string_starts_with($this->action, 'admin_')) {		
			if (!$this->isRoot()) {
				$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));
			}
		}
        /* ctrl ACL */
    }

    public function admin_choice() {

        if ($this->request->is('post') || $this->request->is('put') &&
                (isset($this->request->data['Organization']['organization_id']) && !empty($this->request->data['Organization']['organization_id']))) {

            /*
             * pulisco le Session
             */
            $this->Session->delete('delivery_id');
            $this->Session->delete('order_id');

            $this->Session->setFlash(__('Organizzazione scelta'));
            $this->myRedirect(Configure::read('routes_default'));
        } else {
            $options = array();
            //$options['conditions'] = array('Organization.stato' => 'Y');
            $options['order'] = array('Organization.name');
            $results = $this->Organization->find('all', $options);

            $tmp = array();
            foreach ($results as $result) {
                $label = $result['Organization']['name'] . ' ' . $result['Organization']['localita'] . ' (' . $result['Organization']['provincia'] . ')';
                if ($result['Organization']['stato'] == 'N')
                    $label .= " - NON ATTIVA";
                $tmp[$result['Organization']['id']] = $label;
            }

            $organizations = array(0 => 'Nessuna organizzazione');
            $organizations += $tmp;

            $this->set(compact('organizations'));
        }
    }

    public function admin_index() {

        $this->paginate = array('recursive' => 0,
            'order' => 'Organization.id desc',
            'limit' => 100);
        $results = $this->paginate('Organization');

        foreach ($results as $numResult => $result) {

            $paramsConfig = json_decode($result['Organization']['paramsConfig'], true);
            $paramsFields = json_decode($result['Organization']['paramsFields'], true);
            /*
              echo "<pre>";
              print_r($paramsFields);
              echo "</pre>";
             */
            $results[$numResult]['Organization'] += $paramsConfig;
            $results[$numResult]['Organization'] += $paramsFields;

            unset($results[$numResult]['Organization']['paramsConfig']);
            unset($results[$numResult]['Organization']['paramsFields']);

            if (!empty($result['Organization']['paramsPay'])) {
                $paramsPay = json_decode($result['Organization']['paramsPay'], true);
                $results[$numResult]['Organization'] += $paramsPay;
                unset($results[$numResult]['Organization']['paramsPay']);
            } else
                $paramsPay = array();
        }

        $this->set('results', $results);

        /*
         * ricarico i dati dello user (organization, ACL, ..)
         * se lo faccio in admin_edit tiene quelli vecchi
         */
        $this->reloadUserParams();

        $this->set('templates', Configure::read('templates'));
    }

    public function admin_add() {

        $debug = false;

        if ($this->request->is('post') || $this->request->is('put')) {

            $this->__prepare_request_data();

            $paramsConfig = array();
            $paramsConfig += array('hasBookmarsArticles' => $this->request->data['Organization']['hasBookmarsArticles']);
            $paramsConfig += array('hasArticlesOrder' => $this->request->data['Organization']['hasArticlesOrder']);
            $paramsConfig += array('hasVisibility' => $this->request->data['Organization']['hasVisibility']);
            $paramsConfig += array('hasTrasport' => $this->request->data['Organization']['hasTrasport']);
            $paramsConfig += array('hasCostMore' => $this->request->data['Organization']['hasCostMore']);
            $paramsConfig += array('hasCostLess' => $this->request->data['Organization']['hasCostLess']);
            $paramsConfig += array('hasValidate' => $this->request->data['Organization']['hasValidate']);
            $paramsConfig += array('hasStoreroom' => $this->request->data['Organization']['hasStoreroom']);
            $paramsConfig += array('hasStoreroomFrontEnd' => $this->request->data['Organization']['hasStoreroomFrontEnd']);
            $paramsConfig += array('payToDelivery' => $this->request->data['Organization']['payToDelivery']);
            $paramsConfig += array('hasDes' => $this->request->data['Organization']['hasDes']);
            $paramsConfig += array('hasDesReferentAllGas' => $this->request->data['Organization']['hasDesReferentAllGas']);
            $paramsConfig += array('prodSupplierOrganizationId' => $this->request->data['Organization']['prodSupplierOrganizationId']);

            /*
             * ruoli
             * di default gasManager, gasManagerDelivery, gasReferente, gasSuperReferente, utenti
             */
            // referente cassa (pagamento degli utenti alla consegna)
            $paramsConfig += array('hasUserGroupsCassiere' => $this->request->data['Organization']['hasUserGroupsCassiere']);

            /*
             * referente tesoriere (pagamento con richiesta degli utenti dopo consegna)
             * 		gestisce anche il pagamento del suo produttore
             */
            $paramsConfig += array('hasUserGroupsReferentTesoriere' => $this->request->data['Organization']['hasUserGroupsReferentTesoriere']);

            // tesoriere per pagamento ai fornitori
            $paramsConfig += array('hasUserGroupsTesoriere' => $this->request->data['Organization']['hasUserGroupsTesoriere']);
            $paramsConfig += array('hasUserGroupsStoreroom' => $this->request->data['Organization']['hasUserGroupsStoreroom']);

            $this->request->data['Organization']['paramsConfig'] = json_encode($paramsConfig);

            $paramsFields = array();
            $paramsFields += array('hasFieldArticleCodice' => $this->request->data['Organization']['hasFieldArticleCodice']);
            $paramsFields += array('hasFieldArticleIngredienti' => $this->request->data['Organization']['hasFieldArticleIngredienti']);
            $paramsFields += array('hasFieldArticleAlertToQta' => $this->request->data['Organization']['hasFieldArticleAlertToQta']);
            $paramsFields += array('hasFieldPaymentPos' => $this->request->data['Organization']['hasFieldPaymentPos']);
            $paramsFields += array('paymentPos' => $this->request->data['Organization']['paymentPos']);
            $paramsFields += array('hasFieldArticleCategoryId' => $this->request->data['Organization']['hasFieldArticleCategoryId']);
            $paramsFields += array('hasFieldSupplierCategoryId' => $this->request->data['Organization']['hasFieldSupplierCategoryId']);
            $paramsFields += array('hasFieldFatturaRequired' => $this->request->data['Organization']['hasFieldFatturaRequired']);
            $this->request->data['Organization']['paramsFields'] = json_encode($paramsFields);

            /*
             *  pay
             */
            $paramsPay = array();
            $paramsPay += array('payMail' => $this->request->data['Organization']['payMail']);
            $paramsPay += array('payContatto' => $this->request->data['Organization']['payContatto']);
            $paramsPay += array('payIntestatario' => $this->request->data['Organization']['payIntestatario']);
            $paramsPay += array('payIndirizzo' => $this->request->data['Organization']['payIndirizzo']);
            $paramsPay += array('payCap' => $this->request->data['Organization']['payCap']);
            $paramsPay += array('payCitta' => $this->request->data['Organization']['payCitta']);
            $paramsPay += array('payProv' => $this->request->data['Organization']['payProv']);
            $paramsPay += array('payCf' => $this->request->data['Organization']['payCf']);
            $paramsPay += array('payPiva' => $this->request->data['Organization']['payPiva']);
            $this->request->data['Organization']['paramsPay'] = json_encode($paramsPay);

            if ($debug) {
                echo "<pre>";
                print_r($this->request->data);
                echo "</pre>";
                exit;
            }

            $this->Organization->create();
            if ($this->Organization->save($this->request->data)) {
                $this->Session->setFlash(__('The organization has been saved'));
                $this->myRedirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The organization could not be saved. Please, try again.'));
            }
        }

        /*
         * fields default
         */
        $this->request->data['Organization']['hasUserGroupsCassiere'] = 'Y';
        $this->request->data['Organization']['hasUserGroupsReferentTesoriere'] = 'N';
        $this->request->data['Organization']['hasUserGroupsTesoriere'] = 'Y';
        $this->request->data['Organization']['hasUserGroupsStoreroom'] = 'Y';
        $this->request->data['Organization']['payToDelivery'] = 'ON';

        /*
         * configuration
         */
        $hasBookmarsArticles = array('Y' => 'Si', 'N' => 'No');
        $hasArticlesOrder = array('Y' => 'Si', 'N' => 'No');
        $hasVisibility = array('Y' => 'Si', 'N' => 'No');
        $hasTrasport = array('Y' => 'Si', 'N' => 'No');
        $hasCostMore = array('Y' => 'Si', 'N' => 'No');
        $hasCostLess = array('Y' => 'Si', 'N' => 'No');
        $hasValidate = array('Y' => 'Si', 'N' => 'No');
        $hasStoreroom = array('Y' => 'Si', 'N' => 'No');
        $hasStoreroomFrontEnd = array('Y' => 'Si', 'N' => 'No');
        $payToDelivery = array('BEFORE' => 'prima della consegna', 'ON' => 'alla consegna', 'POST' => 'dopo la consegna', 'ON-POST' => 'alla consegna o dopo la consegna');
        $hasDes = array('Y' => 'Si', 'N' => 'No');
        $hasDesReferentAllGas = array('Y' => 'Si', 'N' => 'No');
        $prodSupplierOrganizationId = 0;

        /*
         * ruoli
         */
        $hasUserGroupsCassiere = array('Y' => 'Si', 'N' => 'No');
        $hasUserGroupsReferentTesoriere = array('Y' => 'Si', 'N' => 'No');
        $hasUserGroupsTesoriere = array('Y' => 'Si', 'N' => 'No');
        $hasUserGroupsStoreroom = array('Y' => 'Si', 'N' => 'No');

        /*
         * fields
         */
        $hasFieldArticleCodice = array('Y' => 'Si', 'N' => 'No');
        $hasFieldArticleIngredienti = array('Y' => 'Si', 'N' => 'No');
        $hasFieldArticleAlertToQta = array('Y' => 'Si', 'N' => 'No');
        $hasFieldPaymentPos = array('Y' => 'Si', 'N' => 'No');
        $hasFieldArticleCategoryId = array('Y' => 'Si', 'N' => 'No');
        $hasFieldSupplierCategoryId = array('Y' => 'Si', 'N' => 'No');
        $hasFieldFatturaRequired = array('Y' => 'Si', 'N' => 'No');
        $stato = ClassRegistry::init('Organization')->enumOptions('stato');
        $type = ClassRegistry::init('Organization')->enumOptions('type');
        $this->set(compact('hasBookmarsArticles', 'hasArticlesOrder', 'hasVisibility', 'hasTrasport', 'hasCostMore', 'hasCostLess', 'hasValidate', 'hasStoreroom', 'hasStoreroomFrontEnd', 'payToDelivery', 'hasDes', 'hasDesReferentAllGas', 'prodSupplierOrganizationId', 'hasUserGroupsCassiere', 'hasUserGroupsReferentTesoriere', 'hasUserGroupsTesoriere', 'hasUserGroupsStoreroom', 'hasFieldArticleCodice', 'hasFieldArticleIngredienti', 'hasFieldArticleAlertToQta', 'hasFieldPaymentPos', 'hasFieldArticleCategoryId', 'hasFieldSupplierCategoryId', 'hasFieldFatturaRequired', 'type', 'stato'));

        $this->set('templates', Configure::read('templates'));
    }

    public function admin_edit($id = null) {

        $this->Organization->id = $id;
        if (!$this->Organization->exists()) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        if ($this->request->is('post') || $this->request->is('put')) {

            $this->__prepare_request_data();

            $paramsConfig = array();
            $paramsConfig += array('hasBookmarsArticles' => $this->request->data['Organization']['hasBookmarsArticles']);
            $paramsConfig += array('hasArticlesOrder' => $this->request->data['Organization']['hasArticlesOrder']);
            $paramsConfig += array('hasVisibility' => $this->request->data['Organization']['hasVisibility']);
            $paramsConfig += array('hasTrasport' => $this->request->data['Organization']['hasTrasport']);
            $paramsConfig += array('hasCostMore' => $this->request->data['Organization']['hasCostMore']);
            $paramsConfig += array('hasCostLess' => $this->request->data['Organization']['hasCostLess']);
            $paramsConfig += array('hasValidate' => $this->request->data['Organization']['hasValidate']);
            $paramsConfig += array('hasStoreroom' => $this->request->data['Organization']['hasStoreroom']);
            $paramsConfig += array('hasStoreroomFrontEnd' => $this->request->data['Organization']['hasStoreroomFrontEnd']);
            $paramsConfig += array('payToDelivery' => $this->request->data['Organization']['payToDelivery']);
            $paramsConfig += array('hasDes' => $this->request->data['Organization']['hasDes']);
            $paramsConfig += array('hasDesReferentAllGas' => $this->request->data['Organization']['hasDesReferentAllGas']);
            $paramsConfig += array('prodSupplierOrganizationId' => $this->request->data['Organization']['prodSupplierOrganizationId']);

            /*
             * ruoli
             * di default gasManager, gasManagerDelivery, gasReferente, gasSuperReferente, utenti
             */
            // referente cassa (pagamento degli utenti alla consegna)
            $paramsConfig += array('hasUserGroupsCassiere' => $this->request->data['Organization']['hasUserGroupsCassiere']);
            /*
             * referente tesoriere (pagamento con richiesta degli utenti dopo consegna)
             * 		gestisce anche il pagamento del suo produttore
             */
            $paramsConfig += array('hasUserGroupsReferentTesoriere' => $this->request->data['Organization']['hasUserGroupsReferentTesoriere']);

            // tesoriere per pagamento ai fornitori
            $paramsConfig += array('hasUserGroupsTesoriere' => $this->request->data['Organization']['hasUserGroupsTesoriere']);
            $paramsConfig += array('hasUserGroupsStoreroom' => $this->request->data['Organization']['hasUserGroupsStoreroom']);
            /*
            echo "<pre>";
            print_r($paramsConfig);
            echo "</pre>"; 
            */            
            $this->request->data['Organization']['paramsConfig'] = json_encode($paramsConfig);

            $paramsFields = array();
            $paramsFields += array('hasFieldArticleCodice' => $this->request->data['Organization']['hasFieldArticleCodice']);
            $paramsFields += array('hasFieldArticleIngredienti' => $this->request->data['Organization']['hasFieldArticleIngredienti']);
            $paramsFields += array('hasFieldArticleAlertToQta' => $this->request->data['Organization']['hasFieldArticleAlertToQta']);
            $paramsFields += array('hasFieldPaymentPos' => $this->request->data['Organization']['hasFieldPaymentPos']);
            $paramsFields += array('paymentPos' => $this->request->data['Organization']['paymentPos']);
            $paramsFields += array('hasFieldArticleCategoryId' => $this->request->data['Organization']['hasFieldArticleCategoryId']);
            $paramsFields += array('hasFieldSupplierCategoryId' => $this->request->data['Organization']['hasFieldSupplierCategoryId']);
            $paramsFields += array('hasFieldFatturaRequired' => $this->request->data['Organization']['hasFieldFatturaRequired']);
            $this->request->data['Organization']['paramsFields'] = json_encode($paramsFields);

            /*
             *  pay
             */
            $paramsPay = array();
            $paramsPay += array('payMail' => $this->request->data['Organization']['payMail']);
            $paramsPay += array('payContatto' => $this->request->data['Organization']['payContatto']);
            $paramsPay += array('payIntestatario' => $this->request->data['Organization']['payIntestatario']);
            $paramsPay += array('payIndirizzo' => $this->request->data['Organization']['payIndirizzo']);
            $paramsPay += array('payCap' => $this->request->data['Organization']['payCap']);
            $paramsPay += array('payCitta' => $this->request->data['Organization']['payCitta']);
            $paramsPay += array('payProv' => $this->request->data['Organization']['payProv']);
            $paramsPay += array('payCf' => $this->request->data['Organization']['payCf']);
            $paramsPay += array('payPiva' => $this->request->data['Organization']['payPiva']);
            $this->request->data['Organization']['paramsPay'] = json_encode($paramsPay);

            $this->Organization->create();
            if ($this->Organization->save($this->request->data)) {

                /*
                 * ctrl se ho modificato l'organizzazione che sto usando
                 */
                if ($id == $this->user->organization['Organization']['id']) {
                    $this->Session->setFlash(__('The organization has been saved'));
                } else
                    $this->Session->setFlash(__('The organization has been saved'));

                $this->myRedirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The organization could not be saved. Please, try again.'));
            }
        } else {
            $this->request->data = $this->Organization->read(0, null, $id);
            if (empty($this->request->data)) {
                $this->Session->setFlash(__('msg_error_params'));
                $this->myRedirect(Configure::read('routes_msg_exclamation'));
            }

            $paramsConfig = json_decode($this->request->data['Organization']['paramsConfig'], true);
            $paramsFields = json_decode($this->request->data['Organization']['paramsFields'], true);

            $this->request->data['Organization'] += $paramsConfig;
            $this->request->data['Organization'] += $paramsFields;

            if (!empty($this->request->data['Organization']['paramsPay'])) {
                $paramsPay = json_decode($this->request->data['Organization']['paramsPay'], true);
                $this->request->data['Organization'] += $paramsPay;
            }


            /*
             * configurazione
             */
            $hasBookmarsArticles = array('Y' => 'Si', 'N' => 'No');
            $hasArticlesOrder = array('Y' => 'Si', 'N' => 'No');
            $hasVisibility = array('Y' => 'Si', 'N' => 'No');
            $hasTrasport = array('Y' => 'Si', 'N' => 'No');
            $hasCostMore = array('Y' => 'Si', 'N' => 'No');
            $hasCostLess = array('Y' => 'Si', 'N' => 'No');
            $hasValidate = array('Y' => 'Si', 'N' => 'No');
            $hasStoreroom = array('Y' => 'Si', 'N' => 'No');
            $hasStoreroomFrontEnd = array('Y' => 'Si', 'N' => 'No');
            $payToDelivery = array('BEFORE' => 'prima della consegna', 'ON' => 'alla consegna', 'POST' => 'dopo la consegna', 'ON-POST' => 'alla consegna o dopo la consegna');
            $hasDes = array('Y' => 'Si', 'N' => 'No');
            $hasDesReferentAllGas = array('Y' => 'Si', 'N' => 'No');
            $prodSupplierOrganizationId = 0;

            /*
             * ruoli
             */
            if ($this->request->data['Organization']['type'] == 'GAS') {
                $hasUserGroupsCassiere = array('Y' => 'Si', 'N' => 'No');
                $hasUserGroupsReferentTesoriere = array('Y' => 'Si', 'N' => 'No');

                $hasUserGroupsTesoriere = array('Y' => 'Si', 'N' => 'No');

                $hasUserGroupsStoreroom = array('Y' => 'Si', 'N' => 'No');
            } else
            if ($this->request->data['Organization']['type'] == 'PROD') {
                $hasUserGroupsCassiere = array('N' => 'No');
                $hasUserGroupsReferentTesoriere = array('N' => 'No');

                $hasUserGroupsTesoriere = array('Y' => 'Si');

                $hasUserGroupsStoreroom = array('Y' => 'Si', 'N' => 'No');
            }

            /*
             * fields
             */
            $hasFieldArticleCodice = array('Y' => 'Si', 'N' => 'No');
            $hasFieldArticleIngredienti = array('Y' => 'Si', 'N' => 'No');
            $hasFieldArticleAlertToQta = array('Y' => 'Si', 'N' => 'No');
            $hasFieldPaymentPos = array('Y' => 'Si', 'N' => 'No');
            $hasFieldArticleCategoryId = array('Y' => 'Si', 'N' => 'No');
            $hasFieldSupplierCategoryId = array('Y' => 'Si', 'N' => 'No');
            $hasFieldFatturaRequired = array('Y' => 'Si', 'N' => 'No');
            $stato = ClassRegistry::init('Organization')->enumOptions('stato');
            $type = ClassRegistry::init('Organization')->enumOptions('type');
            $this->set(compact('hasBookmarsArticles', 'hasArticlesOrder', 'hasVisibility', 'hasTrasport', 'hasCostMore', 'hasCostLess', 'hasValidate', 'hasStoreroom', 'hasStoreroomFrontEnd', 'payToDelivery', 'hasDes', 'hasDesReferentAllGas', 'prodSupplierOrganizationId', 'hasUserGroupsCassiere', 'hasUserGroupsReferentTesoriere', 'hasUserGroupsTesoriere', 'hasUserGroupsStoreroom', 'hasFieldArticleCodice', 'hasFieldArticleIngredienti', 'hasFieldArticleAlertToQta', 'hasFieldPaymentPos', 'hasFieldArticleCategoryId', 'hasFieldSupplierCategoryId', 'hasFieldFatturaRequired', 'type', 'stato'));

            $this->set('templates', Configure::read('templates'));
        }
    }

    private function __prepare_request_data() {
        if ($this->request->data['Organization']['j_group_registred'] == null)
            $this->request->data['Organization']['j_group_registred'] = 0;

        if ($this->request->data['Organization']['payToDelivery'] == 'ON') {
            // referente tesoriere (pagamento con richiesta degli utenti dopo consegna)
            $this->request->data['Organization']['hasUserGroupsReferentTesoriere'] = 'N';
        } else
        if ($this->request->data['Organization']['payToDelivery'] == 'POST') {
            // referente cassa (pagamento degli utenti alla consegna)
            // $this->request->data['Organization']['hasUserGroupsCassiere'] = 'N';
        } else
        if ($this->request->data['Organization']['payToDelivery'] == 'ON-POST') {
            
        }

        if ($this->request->data['Organization']['type'] == 'GAS') {
            $this->request->data['Organization']['prodSupplierOrganizationId'] = 0;
        } else
        if ($this->request->data['Organization']['type'] == 'PROD') {

            /*
             * configurazioni
             */
            $this->request->data['Organization']['hasBookmarsArticles'] = 'Y';
            $this->request->data['Organization']['hasArticlesOrder'] = 'Y';

            $this->request->data['Organization']['hasTrasport'] = 'N';
            $this->request->data['Organization']['hasCostMore'] = 'N';
            $this->request->data['Organization']['hasCostLess'] = 'N';
            $this->request->data['Organization']['hasValidate'] = 'N';
            $this->request->data['Organization']['hasStoreroom'] = 'N';
            $this->request->data['Organization']['hasStoreroomFrontEnd'] = 'N';
            $this->request->data['Organization']['payToDelivery'] = 'POST';
            $this->request->data['Organization']['hasDes'] = 'N';
            $this->request->data['Organization']['hasDesReferentAllGas'] = 'N';

            /*
             * ruoli
             */
            $this->request->data['Organization']['hasUserGroupsCassiere'] = 'Y';
            $this->request->data['Organization']['hasUserGroupsReferentTesoriere'] = 'N';
            $this->request->data['Organization']['hasUserGroupsStoreroom'] = 'Y';

            $this->request->data['Organization']['hasUserGroupsTesoriere'] = 'Y';
        }

        /*
          echo "<pre>";
          print_r($this->request->data['Organization']);
          echo "</pre>";
         */
    }

    /*
     * organizations_Trigger
     * 		suppliers_organizations 
     * 		users
     *     deliveries
     */

    public function admin_delete($id = null) {

        $this->Organization->id = $id;
        if (!$this->Organization->exists()) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Organization->delete())
                $this->Session->setFlash(__('Delete Organization'));
            else
                $this->Session->setFlash(__('Organization was not deleted'));
            $this->myRedirect(array('action' => 'index'));
        }

        $options = array();
        $options['conditions'] = array('Organization.id' => $id);
        $options['recursive'] = 1;
        $results = $this->Organization->find('first', $options);
        $this->set(compact('results'));
    }

	public function gmaps() {
		$options = array();
		$options['conditions'] = array('Organization.type' => 'GAS',
									   'Organization.stato' => 'Y');
		$options['order'] = array('Organization.name');
		$options['recursive'] = -1;
		$results = $this->Organization->find('all', $options);

		$this->set('results', $results);
	
		$this->layout = 'default_front_end';
	}	
}