<?php
App::uses('AppController', 'Controller');

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
            $options = [];
            $options['conditions'] = ['Organization.type' => 'GAS'];
            $options['order'] = ['Organization.name'];
            $gasResults = $this->Organization->find('all', $options);
			
            $options = [];
            $options['conditions'] = ['Organization.type' => 'PRODGAS'];
            $options['order'] = ['Organization.name'];
            $prodgasResults = $this->Organization->find('all', $options);

            $options = [];
            $options['conditions'] = ['Organization.type' => 'PACT'];
            $options['order'] = ['Organization.name'];
            $pactResults = $this->Organization->find('all', $options);

            $gasTmp = [];
            foreach ($gasResults as $result) {
				
				$paramsConfig = json_decode($result['Organization']['paramsConfig'], true);
  
                $label = $result['Organization']['name'] . ' ' . $result['Organization']['localita'] . ' (' . $result['Organization']['provincia'] . ')';
				
				//$payToDelivery = $this->Organization->getPayToDelivery($paramsConfig['payToDelivery']);
				//$label .= ' '.__('Payment').' '.$payToDelivery;
				$label .= ' '.$result['Template']['name'];
				$label .= ' ('.$result['Template']['id'].')';
				
                if ($result['Organization']['stato'] == 'N')
                    $label .= " - NON ATTIVA";
                $gasTmp[$result['Organization']['id']] = $label;
            }

            $prodgasTmp = [];
            foreach ($prodgasResults as $result) {
				
                $label = $result['Organization']['name'];
				
                if ($result['Organization']['stato'] == 'N')
                    $label .= " - NON ATTIVA";
                $prodgasTmp[$result['Organization']['id']] = $label;
            }

            $pactTmp = [];
            foreach ($pactResults as $result) {
				
                $label = $result['Organization']['name'];
				
                if ($result['Organization']['stato'] == 'N')
                    $label .= " - NON ATTIVA";
                $pactTmp[$result['Organization']['id']] = $label;
            }
			
            $organizations = [0 => 'Nessuna organizzazione',
							  'GAS' => $gasTmp,
							  'PRODGAS' => $prodgasTmp,
							  'PACT' => $pactTmp];

            $this->set(compact('organizations'));
        }
    }

    public function admin_index() {

        // GAS
    	if(isset($this->request->params['pass']['type']))
	        $type = $this->request->params['pass']['type']; 
	    if(isset($this->request->data['Organization']['type']))
	    	$type = $this->request->data['Organization']['type'];
        else 
        	$type='GAS';
        $this->set(compact('type'));

        $SqlLimit = 100;
        $this->paginate = ['recursive' => 0,
        					'conditions' => ['Organization.type' => $type],
				            'order' => 'Organization.id desc',
				            'maxLimit' => $SqlLimit, 'limit' => $SqlLimit];

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
                $paramsPay = [];
        }

        $this->set(compact('results', 'type'));

        /*
         * ricarico i dati dello user (organization, ACL, ..)
         * se lo faccio in admin_edit tiene quelli vecchi
         */
        $this->reloadUserParams();
    }

   public function admin_step_add() {
   
	   $results = $this->Organization->find('all', ['fields' => ['MAX(Organization.id) AS max_id']]);
	
	   $max_id = $results[0][0]['max_id'];
	   $max_id++;
	   $this->set('max_id', $max_id);
   }

	/*
	 * ctrl se i dati sono coerenti con il template
	 */
    public function admin_index_ctrl() {

		$debug = false;
		
		App::import('Model', 'UserGroupMap');
		$UserGroupMap = new UserGroupMap;
		
		$SqlLimit = 100;
        $this->paginate = ['conditions' => ['Organization.type' => 'GAS'],
							'recursive' => 0,
							'order' => 'Organization.id desc',
							'maxLimit' => $SqlLimit, 'limit' => $SqlLimit];
        $results = $this->paginate('Organization');

        foreach ($results as $numResult => $result) {

			$tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $result['Organization']['id']]);
			
            $paramsConfig = json_decode($result['Organization']['paramsConfig'], true);
            $paramsFields = json_decode($result['Organization']['paramsFields'], true);
            
			/*
              echo "<pre>";
              print_r($paramsConfig);
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
                $paramsPay = [];
			
			/*
			 * ctrl user Assistente
			 */
			$UserAssistenteResults = [];
			$sql = "SELECT User.* 
					FROM
					".Configure::read('DB.portalPrefix')."user_usergroup_map m,
					".Configure::read('DB.portalPrefix')."usergroups g,
					".Configure::read('DB.portalPrefix')."users User
						WHERE
						m.user_id = User.id
						and m.group_id = g.id
						and m.group_id = ".Configure::read('group_id_manager')."
						and User.block = 0
						and User.organization_id = ".(int)$result['Organization']['id']."
						and User.email like '%.portalgas.it' "; // Assistente PortAlGas
			self::d($sql, $debug);
			try {
				$userAssistenteResults = $this->Organization->query($sql);			
			}
			catch (Exception $e) {
				CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
			}
			$results[$numResult]['Organization']['userAssistenteResults'] = $userAssistenteResults;
			
			/*
			 * ctrl storeroom
			 */
			if($paramsConfig['hasStoreroom']=='Y' || $paramsConfig['hasStoreroomFrontEnd']=='Y')  {
			
				$storeroom_tot_users = $UserGroupMap->getTotUserByGroupId($tmp_user, Configure::read('group_id_storeroom'), $debug);
				
				$results[$numResult]['Organization']['storeroom_tot_users'] = $storeroom_tot_users;
				
				if($storeroom_tot_users==1)
					$results[$numResult]['Organization']['ctrl_storeroom'] = 'OK';
				else
					$results[$numResult]['Organization']['ctrl_storeroom'] = 'KO';
			}
			else
				$results[$numResult]['Organization']['ctrl_storeroom'] = 'OK';
		
			/*
			 * ctrl ruoli 
			 */
			App::import('Model', 'Template');
			$Template = new Template;
			
			$options = [];
			$options['conditions'] = ['Template.id' => $result['Organization']['template_id']];
			$options['recursive'] = -1;
			$templateResults = $Template->find('first', $options);
			if(empty($templateResults))
				self::x("Organization::validateData() templateResults empty!");
			
			$tesoriere_tot_users = $UserGroupMap->getTotUserByGroupId($tmp_user, Configure::read('group_id_tesoriere'), $debug);
			$cassiere_tot_users = $UserGroupMap->getTotUserByGroupId($tmp_user, Configure::read('group_id_cassiere'), $debug);
			
			/*
			 * cassiere
			 */ 
			if($templateResults['Template']['hasCassiere'] == 'Y') {
				if($cassiere_tot_users==0) {
					$results[$numResult]['Organization']['ctrl_hasCassiere'] = 'KO';
				} 
				else
					$results[$numResult]['Organization']['ctrl_hasCassiere'] = 'OK';				
			}
			else {
				$results[$numResult]['Organization']['ctrl_hasCassiere'] = 'OK';
			}

			/*
			 * tesoriere
			 */ 					
			if($templateResults['Template']['hasTesoriere'] == 'Y') {
				if($tesoriere_tot_users==0) {
					$results[$numResult]['Organization']['ctrl_hasTesoriere'] = 'KO';
				}
				else
					$results[$numResult]['Organization']['ctrl_hasTesoriere'] = 'OK';	
			}
			else {
				$results[$numResult]['Organization']['ctrl_hasTesoriere'] = 'OK';	
			}
					
			/*
			 * ctrl pay POST ON ON-POST
			 * non + =< lo faccio dal templates
			switch($result['Template']['payToDelivery']) {
				case "POST":
					$tesoriere_tot_users = $UserGroupMap->getTotUserByGroupId($tmp_user, Configure::read('group_id_tesoriere'), $debug);
					
					if($tesoriere_tot_users==0)
						$results[$numResult]['Organization']['ctrl_payToDelivery'] = 'KO';
					else
						$results[$numResult]['Organization']['ctrl_payToDelivery'] = 'OK';					
				break;
				case "ON":
					
					
					if($cassiere_tot_users==0)
						$results[$numResult]['Organization']['ctrl_payToDelivery'] = 'KO';
					else
						$results[$numResult]['Organization']['ctrl_payToDelivery'] = 'OK';	
				
				break;
				case "ON-POST":
					$tesoriere_tot_users = $UserGroupMap->getTotUserByGroupId($tmp_user, Configure::read('group_id_tesoriere'), $debug);
					
					if($tesoriere_tot_users==0)
						$results[$numResult]['Organization']['ctrl_payToDelivery'] = 'KO';
					else
						$results[$numResult]['Organization']['ctrl_payToDelivery'] = 'OK';	
					
					$cassiere_tot_users = $UserGroupMap->getTotUserByGroupId($tmp_user, Configure::read('group_id_cassiere'), $debug);
					
					if($cassiere_tot_users==0)
						$results[$numResult]['Organization']['ctrl_payToDelivery'] = 'KO';
					else
						$results[$numResult]['Organization']['ctrl_payToDelivery'] = 'OK';		
				break;
				default:
					self::x("Per Organization ".$result['Organization']['id']." payToDelivery NON permesso ".$paramsConfig['payToDelivery']);
				break;
			}
			*/
			
        }
        
		self::d($results, false);
		
        $this->set('results', $results);
    }
	
    public function admin_add() {

        $debug = false;

        // GAS
    	if(isset($this->request->params['pass']['type']))
	        $type = $this->request->params['pass']['type']; 
	    if(isset($this->request->data['Organization']['type']))
	    	$type = $this->request->data['Organization']['type'];
        else 
        	$type='GAS';
        $this->set(compact('type'));

        if ($this->request->is('post') || $this->request->is('put')) {

            $this->request->data = $this->Organization->validateData($this->request->data, $debug);

            $paramsConfig = [];
            $paramsConfig += ['hasArticlesGdxp' => $this->request->data['Organization']['hasArticlesGdxp']];
            $paramsConfig += ['hasOrdersGdxp' => $this->request->data['Organization']['hasOrdersGdxp']];
            $paramsConfig += ['hasBookmarsArticles' => $this->request->data['Organization']['hasBookmarsArticles']];
            $paramsConfig += ['hasDocuments' => $this->request->data['Organization']['hasDocuments']];
            $paramsConfig += ['hasArticlesOrder' => $this->request->data['Organization']['hasArticlesOrder']];
            $paramsConfig += ['hasVisibility' => $this->request->data['Organization']['hasVisibility']];
            $paramsConfig += ['hasTrasport' => $this->request->data['Organization']['hasTrasport']];
            $paramsConfig += ['hasCostMore' => $this->request->data['Organization']['hasCostMore']];
            $paramsConfig += ['hasCostLess' => $this->request->data['Organization']['hasCostLess']];
            $paramsConfig += ['hasValidate' => $this->request->data['Organization']['hasValidate']];
            $paramsConfig += ['hasCashFilterSupplier' => $this->request->data['Organization']['hasCashFilterSupplier']];
            $paramsConfig += ['hasStoreroom' => $this->request->data['Organization']['hasStoreroom']];
            $paramsConfig += ['hasStoreroomFrontEnd' => $this->request->data['Organization']['hasStoreroomFrontEnd']];
            $paramsConfig += ['canOrdersClose' => $this->request->data['Organization']['canOrdersClose']];
            $paramsConfig += ['canOrdersDelete' => $this->request->data['Organization']['canOrdersDelete']];
            $paramsConfig += ['cashLimit' => $this->request->data['Organization']['cashLimit']];
            $paramsConfig += ['limitCashAfter' => $this->request->data['Organization']['limitCashAfter']];
            $paramsConfig += ['hasDes' => $this->request->data['Organization']['hasDes']];
            $paramsConfig += ['hasDesReferentAllGas' => $this->request->data['Organization']['hasDesReferentAllGas']];
            $paramsConfig += ['hasDesUserManager' => $this->request->data['Organization']['hasDesUserManager']];
            $paramsConfig += ['prodSupplierOrganizationId' => $this->request->data['Organization']['prodSupplierOrganizationId']];
            $paramsConfig += ['hasUsersRegistrationFE' => $this->request->data['Organization']['hasUsersRegistrationFE']];

			$paramsConfig += ['hasUserFlagPrivacy' => $this->request->data['Organization']['hasUserFlagPrivacy']];
			$paramsConfig += ['hasUserRegistrationExpire' => $this->request->data['Organization']['hasUserRegistrationExpire']];
			$paramsConfig += ['userRegistrationExpireDate' => $this->request->data['Organization']['userRegistrationExpireDate']];
			
            /*
             * ruoli
             * di default gasManager, gasManagerDelivery, gasReferente, gasSuperReferente, utenti
             */
            // referente cassa (pagamento degli utenti alla consegna)
            $paramsConfig += ['hasUserGroupsCassiere' => $this->request->data['Organization']['hasUserGroupsCassiere']];

            /*
             * referente tesoriere (pagamento con richiesta degli utenti dopo consegna)
             * 		gestisce anche il pagamento del suo produttore
             */
            $paramsConfig += ['hasUserGroupsReferentTesoriere' => $this->request->data['Organization']['hasUserGroupsReferentTesoriere']];

            // tesoriere per pagamento ai fornitori
            $paramsConfig += ['hasUserGroupsTesoriere' => $this->request->data['Organization']['hasUserGroupsTesoriere']];
            $paramsConfig += ['hasUserGroupsStoreroom' => $this->request->data['Organization']['hasUserGroupsStoreroom']];

            $this->request->data['Organization']['paramsConfig'] = json_encode($paramsConfig);

            $paramsFields = [];
            $paramsFields += ['hasFieldArticleCodice' => $this->request->data['Organization']['hasFieldArticleCodice']];
            $paramsFields += ['hasFieldArticleIngredienti' => $this->request->data['Organization']['hasFieldArticleIngredienti']];
            $paramsFields += ['hasFieldArticleAlertToQta' => $this->request->data['Organization']['hasFieldArticleAlertToQta']];
            $paramsFields += ['hasFieldPaymentPos' => $this->request->data['Organization']['hasFieldPaymentPos']];
            $paramsFields += ['paymentPos' => $this->request->data['Organization']['paymentPos']];
            $paramsFields += ['hasFieldArticleCategoryId' => $this->request->data['Organization']['hasFieldArticleCategoryId']];
            $paramsFields += ['hasFieldSupplierCategoryId' => $this->request->data['Organization']['hasFieldSupplierCategoryId']];
            $paramsFields += ['hasFieldFatturaRequired' => $this->request->data['Organization']['hasFieldFatturaRequired']];
            $this->request->data['Organization']['paramsFields'] = json_encode($paramsFields);

            /*
             *  pay
             */
            $paramsPay = [];
            $paramsPay += ['payMail' => $this->request->data['Organization']['payMail']];
            $paramsPay += ['payContatto' => $this->request->data['Organization']['payContatto']];
            $paramsPay += ['payIntestatario' => $this->request->data['Organization']['payIntestatario']];
            $paramsPay += ['payIndirizzo' => $this->request->data['Organization']['payIndirizzo']];
            $paramsPay += ['payCap' => $this->request->data['Organization']['payCap']];
            $paramsPay += ['payCitta' => $this->request->data['Organization']['payCitta']];
            $paramsPay += ['payProv' => $this->request->data['Organization']['payProv']];
            $paramsPay += ['payCf' => $this->request->data['Organization']['payCf']];
            $paramsPay += ['payPiva' => $this->request->data['Organization']['payPiva']];
            $this->request->data['Organization']['paramsPay'] = json_encode($paramsPay);

            self::d($this->request->data, $debug);

            $this->Organization->create();
            if ($this->Organization->save($this->request->data)) {
                $this->Session->setFlash(__('The organization has been saved'));
                $this->myRedirect(['action' => 'index']);
            } else {
                $this->Session->setFlash(__('The organization could not be saved. Please, try again.'));
            }
        } // end if ($this->request->is('post') || $this->request->is('put')) 

        /*
         * fields default
         */
        $this->request->data['Organization']['hasUserGroupsCassiere'] = 'Y';
        $this->request->data['Organization']['hasUserGroupsReferentTesoriere'] = 'N';
        $this->request->data['Organization']['hasUserGroupsTesoriere'] = 'Y';
        $this->request->data['Organization']['hasUserGroupsStoreroom'] = 'N';
        $this->request->data['Organization']['canOrdersClose'] = 'ALL';
        $this->request->data['Organization']['canOrdersDelete'] = 'ALL';
        $this->request->data['Organization']['cashLimit'] = 'LIMIT-NO';
		$this->request->data['Organization']['limitCashAfter'] = '0.00';
		
        /*
         * configuration
         */
        $hasArticlesGdxp = ['Y' => 'Si', 'N' => 'No'];
        $hasOrdersGdxp = ['Y' => 'Si', 'N' => 'No'];
        $hasBookmarsArticles = ['Y' => 'Si', 'N' => 'No'];
        $hasDocuments = ['Y' => 'Si', 'N' => 'No'];
        $hasArticlesOrder = ['Y' => 'Si', 'N' => 'No'];
        $hasVisibility = ['Y' => 'Si', 'N' => 'No'];
        $hasTrasport = ['Y' => 'Si', 'N' => 'No'];
        $hasCostMore = ['Y' => 'Si', 'N' => 'No'];
        $hasCostLess = ['Y' => 'Si', 'N' => 'No'];
        $hasValidate = ['Y' => 'Si', 'N' => 'No'];
        $hasCashFilterSupplier = ['Y' => 'Si', 'N' => 'No'];
        $hasStoreroom = ['Y' => 'Si', 'N' => 'No'];
        $hasStoreroomFrontEnd = ['Y' => 'Si', 'N' => 'No'];
        $canOrdersClose = ['ALL' => __('ALL'), 'SUPER-REFERENT' => __('gasSuperReferente'), 'REFERENT' => __('gasReferente')];
        $canOrdersDelete = ['ALL' => __('ALL'), 'SUPER-REFERENT' => __('gasSuperReferente'), 'REFERENT' => __('gasReferente')];
        $cashLimit = $this->Organization->getCashLimit();
        $limitCashAfter = '0.00';
        $hasDes = ['Y' => 'Si', 'N' => 'No'];
        $hasDesReferentAllGas = ['Y' => 'Si', 'N' => 'No'];
		$hasDesUserManager = ['Y' => 'Si', 'N' => 'No']; 
        $prodSupplierOrganizationId = 0;
		$hasUsersRegistrationFE = ['Y' => 'Si', 'N' => 'No'];

		$hasUserFlagPrivacy = ['Y' => 'Si', 'N' => 'No'];
		$hasUserRegistrationExpire = ['Y' => 'Si', 'N' => 'No'];
		$userRegistrationExpireDate = '';
		
        /*
         * ruoli
         */
        $hasUserGroupsCassiere = ['Y' => 'Si', 'N' => 'No'];
        $hasUserGroupsReferentTesoriere = ['Y' => 'Si', 'N' => 'No'];
        $hasUserGroupsTesoriere = ['Y' => 'Si', 'N' => 'No'];
        $hasUserGroupsStoreroom = ['Y' => 'Si', 'N' => 'No'];

        /*
         * fields
         */
        $hasFieldArticleCodice = ['Y' => 'Si', 'N' => 'No'];
        $hasFieldArticleIngredienti = ['Y' => 'Si', 'N' => 'No'];
        $hasFieldArticleAlertToQta = ['Y' => 'Si', 'N' => 'No'];
        $hasFieldPaymentPos = ['Y' => 'Si', 'N' => 'No'];
        $hasFieldArticleCategoryId = ['Y' => 'Si', 'N' => 'No'];
        $hasFieldSupplierCategoryId = ['Y' => 'Si', 'N' => 'No'];
        $hasFieldFatturaRequired = ['Y' => 'Si', 'N' => 'No'];
        $stato = ClassRegistry::init('Organization')->enumOptions('stato');
        $type = ClassRegistry::init('Organization')->enumOptions('type');
        $this->set(compact('hasArticlesGdxp', 'hasOrdersGdxp', 'hasBookmarsArticles', 'hasDocuments', 'hasArticlesOrder', 'hasVisibility', 'hasTrasport', 'hasCostMore', 'hasCostLess', 'hasValidate', 'hasCashFilterSupplier', 'hasStoreroom', 'hasStoreroomFrontEnd', 'canOrdersClose', 'canOrdersDelete', 'cashLimit', 'limitCashAfter', 'hasDes', 'hasDesReferentAllGas', 'hasDesUserManager', 'prodSupplierOrganizationId', 'hasUsersRegistrationFE', 'hasUserGroupsCassiere', 'hasUserGroupsReferentTesoriere', 'hasUserGroupsTesoriere', 'hasUserGroupsStoreroom', 'hasFieldArticleCodice', 'hasFieldArticleIngredienti', 'hasFieldArticleAlertToQta', 'hasFieldPaymentPos', 'hasFieldArticleCategoryId', 'hasFieldSupplierCategoryId', 'hasFieldFatturaRequired', 'type', 'stato', 'hasUserFlagPrivacy', 'hasUserRegistrationExpire', 'userRegistrationExpireDate'));

		/*
		 * template
		 */ 
		 $options = [];
		 $options = ['order' => 'Template.name asc'];
		 $templates = $this->Organization->Template->find('list', $options);		
         $this->set('templates', $templates);
    }

    public function admin_edit($id = null) {

		$debug = false;

        $this->Organization->id = $id;
        if (!$this->Organization->exists($this->Organization->id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        // GAS
    	if(isset($this->request->params['pass']['type']))
	        $type = $this->request->params['pass']['type']; 
	    if(isset($this->request->data['Organization']['type']))
	    	$type = $this->request->data['Organization']['type'];
        else 
        	$type='GAS';
        $this->set(compact('type'));

        if ($this->request->is('post') || $this->request->is('put')) {

            $this->request->data = $this->Organization->validateData($this->request->data, $debug);

            $paramsConfig = [];
            $paramsConfig += ['hasArticlesGdxp' => $this->request->data['Organization']['hasArticlesGdxp']];
            $paramsConfig += ['hasOrdersGdxp' => $this->request->data['Organization']['hasOrdersGdxp']];
            $paramsConfig += ['hasBookmarsArticles' => $this->request->data['Organization']['hasBookmarsArticles']];
            $paramsConfig += ['hasDocuments' => $this->request->data['Organization']['hasDocuments']];
            $paramsConfig += ['hasArticlesOrder' => $this->request->data['Organization']['hasArticlesOrder']];
            $paramsConfig += ['hasVisibility' => $this->request->data['Organization']['hasVisibility']];
            $paramsConfig += ['hasTrasport' => $this->request->data['Organization']['hasTrasport']];
            $paramsConfig += ['hasCostMore' => $this->request->data['Organization']['hasCostMore']];
            $paramsConfig += ['hasCostLess' => $this->request->data['Organization']['hasCostLess']];
            $paramsConfig += ['hasValidate' => $this->request->data['Organization']['hasValidate']];
            $paramsConfig += ['hasCashFilterSupplier' => $this->request->data['Organization']['hasCashFilterSupplier']];
            $paramsConfig += ['hasStoreroom' => $this->request->data['Organization']['hasStoreroom']];
            $paramsConfig += ['hasStoreroomFrontEnd' => $this->request->data['Organization']['hasStoreroomFrontEnd']];
            $paramsConfig += ['canOrdersClose' => $this->request->data['Organization']['canOrdersClose']];
            $paramsConfig += ['canOrdersDelete' => $this->request->data['Organization']['canOrdersDelete']];
            $paramsConfig += ['cashLimit' => $this->request->data['Organization']['cashLimit']];
            $paramsConfig += ['limitCashAfter' => $this->request->data['Organization']['limitCashAfter']];
            $paramsConfig += ['hasDes' => $this->request->data['Organization']['hasDes']];
            $paramsConfig += ['hasDesReferentAllGas' => $this->request->data['Organization']['hasDesReferentAllGas']];
            $paramsConfig += ['hasDesUserManager' => $this->request->data['Organization']['hasDesUserManager']];
            $paramsConfig += ['prodSupplierOrganizationId' => $this->request->data['Organization']['prodSupplierOrganizationId']];
            $paramsConfig += ['hasUsersRegistrationFE' => $this->request->data['Organization']['hasUsersRegistrationFE']];

			$paramsConfig += ['hasUserFlagPrivacy' => $this->request->data['Organization']['hasUserFlagPrivacy']];
            $paramsConfig += ['hasUserRegistrationExpire' => $this->request->data['Organization']['hasUserRegistrationExpire']];
            $paramsConfig += ['userRegistrationExpireDate' => $this->request->data['Organization']['userRegistrationExpireDate']];
            
			
            /*
             * ruoli
             * di default gasManager, gasManagerDelivery, gasReferente, gasSuperReferente, utenti
             */
            // referente cassa (pagamento degli utenti alla consegna)
            $paramsConfig += ['hasUserGroupsCassiere' => $this->request->data['Organization']['hasUserGroupsCassiere']];
            /*
             * referente tesoriere (pagamento con richiesta degli utenti dopo consegna)
             * 		gestisce anche il pagamento del suo produttore
             */
            $paramsConfig += ['hasUserGroupsReferentTesoriere' => $this->request->data['Organization']['hasUserGroupsReferentTesoriere']];

            // tesoriere per pagamento ai fornitori
            $paramsConfig += ['hasUserGroupsTesoriere' => $this->request->data['Organization']['hasUserGroupsTesoriere']];
            $paramsConfig += ['hasUserGroupsStoreroom' => $this->request->data['Organization']['hasUserGroupsStoreroom']];
            /*
            echo "<pre>";
            print_r($paramsConfig);
            echo "</pre>"; 
            */            
            $this->request->data['Organization']['paramsConfig'] = json_encode($paramsConfig);

            $paramsFields = [];
            $paramsFields += ['hasFieldArticleCodice' => $this->request->data['Organization']['hasFieldArticleCodice']];
            $paramsFields += ['hasFieldArticleIngredienti' => $this->request->data['Organization']['hasFieldArticleIngredienti']];
            $paramsFields += ['hasFieldArticleAlertToQta' => $this->request->data['Organization']['hasFieldArticleAlertToQta']];
            $paramsFields += ['hasFieldPaymentPos' => $this->request->data['Organization']['hasFieldPaymentPos']];
            $paramsFields += ['paymentPos' => $this->request->data['Organization']['paymentPos']];
            $paramsFields += ['hasFieldArticleCategoryId' => $this->request->data['Organization']['hasFieldArticleCategoryId']];
            $paramsFields += ['hasFieldSupplierCategoryId' => $this->request->data['Organization']['hasFieldSupplierCategoryId']];
            $paramsFields += ['hasFieldFatturaRequired' => $this->request->data['Organization']['hasFieldFatturaRequired']];
            $this->request->data['Organization']['paramsFields'] = json_encode($paramsFields);

            /*
             *  pay
             */
            $paramsPay = [];
            $paramsPay += ['payMail' => $this->request->data['Organization']['payMail']];
            $paramsPay += ['payContatto' => $this->request->data['Organization']['payContatto']];
            $paramsPay += ['payIntestatario' => $this->request->data['Organization']['payIntestatario']];
            $paramsPay += ['payIndirizzo' => $this->request->data['Organization']['payIndirizzo']];
            $paramsPay += ['payCap' => $this->request->data['Organization']['payCap']];
            $paramsPay += ['payCitta' => $this->request->data['Organization']['payCitta']];
            $paramsPay += ['payProv' => $this->request->data['Organization']['payProv']];
            $paramsPay += ['payCf' => $this->request->data['Organization']['payCf']];
            $paramsPay += ['payPiva' => $this->request->data['Organization']['payPiva']];
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

                $this->myRedirect(['action' => 'index']);
            } else {
                $this->Session->setFlash(__('The organization could not be saved. Please, try again.'));
            }
        } else {
            $this->request->data = $this->Organization->read($id, 0);
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
            $hasArticlesGdxp = ['Y' => 'Si', 'N' => 'No'];
            $hasOrdersGdxp = ['Y' => 'Si', 'N' => 'No'];
            $hasBookmarsArticles = ['Y' => 'Si', 'N' => 'No'];
            $hasDocuments = ['Y' => 'Si', 'N' => 'No'];
            $hasArticlesOrder = ['Y' => 'Si', 'N' => 'No'];
            $hasVisibility = ['Y' => 'Si', 'N' => 'No'];
            $hasTrasport = ['Y' => 'Si', 'N' => 'No'];
            $hasCostMore = ['Y' => 'Si', 'N' => 'No'];
            $hasCostLess = ['Y' => 'Si', 'N' => 'No'];
            $hasValidate = ['Y' => 'Si', 'N' => 'No'];
            $hasCashFilterSupplier = ['Y' => 'Si', 'N' => 'No'];
            $hasStoreroom = ['Y' => 'Si', 'N' => 'No'];
            $hasStoreroomFrontEnd = ['Y' => 'Si', 'N' => 'No'];
            $canOrdersClose = ['ALL' => __('ALL'), 'SUPER-REFERENT' => __('gasSuperReferente'), 'REFERENT' => __('gasReferente')];
			$canOrdersDelete = ['ALL' => __('ALL'), 'SUPER-REFERENT' => __('gasSuperReferente'), 'REFERENT' => __('gasReferente')];
			$cashLimit = $this->Organization->getCashLimit();
	        $limitCashAfter = '0.00';            
			$hasDes = ['Y' => 'Si', 'N' => 'No'];
            $hasDesReferentAllGas = ['Y' => 'Si', 'N' => 'No'];
			$hasDesUserManager = ['Y' => 'Si', 'N' => 'No'];
            $prodSupplierOrganizationId = 0;
			$hasUsersRegistrationFE = ['Y' => 'Si', 'N' => 'No'];

			$hasUserFlagPrivacy = ['Y' => 'Si', 'N' => 'No'];
			$hasUserRegistrationExpire = ['Y' => 'Si', 'N' => 'No'];
			$userRegistrationExpireDate = '';
			
            /*
             * ruoli
             */
            if ($this->request->data['Organization']['type'] == 'GAS') {
                $hasUserGroupsCassiere = ['Y' => 'Si', 'N' => 'No'];
                $hasUserGroupsReferentTesoriere = ['Y' => 'Si', 'N' => 'No'];

                $hasUserGroupsTesoriere = ['Y' => 'Si', 'N' => 'No'];

                $hasUserGroupsStoreroom = ['Y' => 'Si', 'N' => 'No'];
            } else
            if ($this->request->data['Organization']['type'] == 'PROD') {
                $hasUserGroupsCassiere = ['N' => 'No'];
                $hasUserGroupsReferentTesoriere = ['N' => 'No'];

                $hasUserGroupsTesoriere = ['Y' => 'Si'];

                $hasUserGroupsStoreroom = ['Y' => 'Si', 'N' => 'No'];
            }

            /*
             * fields
             */
            $hasFieldArticleCodice = ['Y' => 'Si', 'N' => 'No'];
            $hasFieldArticleIngredienti = ['Y' => 'Si', 'N' => 'No'];
            $hasFieldArticleAlertToQta = ['Y' => 'Si', 'N' => 'No'];
            $hasFieldPaymentPos = ['Y' => 'Si', 'N' => 'No'];
            $hasFieldArticleCategoryId = ['Y' => 'Si', 'N' => 'No'];
            $hasFieldSupplierCategoryId = ['Y' => 'Si', 'N' => 'No'];
            $hasFieldFatturaRequired = ['Y' => 'Si', 'N' => 'No'];
            $stato = ClassRegistry::init('Organization')->enumOptions('stato');
            $type = ClassRegistry::init('Organization')->enumOptions('type');
        $this->set(compact('hasArticlesGdxp', 'hasOrdersGdxp', 'hasBookmarsArticles', 'hasDocuments', 'hasArticlesOrder', 'hasVisibility', 'hasTrasport', 'hasCostMore', 'hasCostLess', 'hasValidate', 'hasCashFilterSupplier', 'hasStoreroom', 'hasStoreroomFrontEnd', 'canOrdersClose', 'canOrdersDelete', 'cashLimit', 'limitCashAfter', 'hasDes', 'hasDesReferentAllGas', 'hasDesUserManager', 'prodSupplierOrganizationId', 'hasUsersRegistrationFE', 'hasUserGroupsCassiere', 'hasUserGroupsReferentTesoriere', 'hasUserGroupsTesoriere', 'hasUserGroupsStoreroom', 'hasFieldArticleCodice', 'hasFieldArticleIngredienti', 'hasFieldArticleAlertToQta', 'hasFieldPaymentPos', 'hasFieldArticleCategoryId', 'hasFieldSupplierCategoryId', 'hasFieldFatturaRequired', 'type', 'stato', 'hasUserFlagPrivacy', 'hasUserRegistrationExpire', 'userRegistrationExpireDate'));

			/*
			 * template
			 */ 
			 $options = [];
			 $options = ['order' => 'Template.name asc'];
			 $templates = $this->Organization->Template->find('list', $options);		
	         $this->set('templates', $templates);            
        }
    }

    public function admin_delete($id = null) {

        $this->Organization->id = $id;
        if (!$this->Organization->exists($this->Organization->id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Organization->delete())
                $this->Session->setFlash(__('Delete Organization'));
            else
                $this->Session->setFlash(__('Organization was not deleted'));
            $this->myRedirect(['action' => 'index']);
        }

        $options = [];
        $options['conditions'] = array('Organization.id' => $id);
        $options['recursive'] = 1;
        $results = $this->Organization->find('first', $options);
        $this->set(compact('results'));
    }

	public function gmaps() {
		$options = [];
        $options['conditions'] = ['Organization.stato' => 'Y', 'Organization.type' => 'GAS'];
        $options['order'] = ['Organization.name'];
		$options['recursive'] = -1;
		$results = $this->Organization->find('all', $options);

		$this->set('results', $results);
	
		$this->layout = 'default_front_end';
	}

	public function admin_ajax_joomla_group($title_group) {

		$debug = false;
		
		$id_group = 0;
		$results = [];
		
		if(!empty($title_group)) {
					
			$title = 'GasPages'.$title_group;
						
			JModelLegacy::addIncludePath( JPATH_ADMINISTRATOR .'/components/com_users/models/', 'UsersModel' );

			$groupModel = JModelLegacy::getInstance( 'Group', 'UsersModel' );
			$groupData = array(
				'title' => $title,
				'parent_id' => Configure::read('group_id_user')); // group 	Registered

			$esito = $groupModel->save( $groupData );
			if($esito==1) {
				$sql = "SELECT * FROM ".Configure::read('DB.portalPrefix')."usergroups where title = '$title';";
				$results = $this->Organization->query($sql);
				
				self::d([$sql, $results], $debug);
								
				if(!empty($results) && isset($results[0])) {
					$id_group = $results[0]['j_usergroups']['id'];
					
					$title = 'Registred'.$title_group;
					
					/*
					 * insert level
					 */
					$sql = "INSERT INTO ".Configure::read('DB.portalPrefix')."viewlevels (title, ordering, rules) values ('".$title."', 0, '[".$id_group."]');";
					
					self::d($sql, $debug);
					
					$insertResults = $this->Organization->query($sql);
				}
			}			
		}
	
		$this->set('sql', $sql);
		$this->set('id_group', $id_group);

		if($debug) {
			echo "<pre>Organizations::admin_ajax_joomla_group() id_group \n ";
			print_r($id_group);
			echo "</pre>";		
		}
					
        $this->layout = 'ajax';
        $this->render('/Organizations/admin_ajax_joomla_group');
	}
	
	public function admin_ajax_joomla_template($organizationId, $gasAlias, $gasUpperCase, $gasAliaSEO) {

		$sql = '';
		$results = [];

		if(!empty($organizationId) && !empty($gasAlias) && !empty($gasUpperCase) && !empty($gasAliaSEO)) {
			
			$params = '{"organizationId":'.$organizationId.',"organizationSEO":"'.$gasAliaSEO.'"}';

			$sql .= "INSERT INTO `".Configure::read('DB.portalPrefix')."template_styles` (`template`, `client_id`, home, title, params) values ('V01', 0, 0, 'V01 $gasUpperCase', '$params'); <br />";
		}
	
		$this->set('sql', $sql);
		
        $this->layout = 'ajax';
        $this->render('/Organizations/admin_ajax_joomla_template');
	}	

	public function admin_ajax_joomla_category($title_group) {

		$sql = '';
		$id_category = 0;
		$results = [];
		
		if(!empty($title_group)) {
			
		  $title = 'Pages '.$title_group;
			
		  $basePath = JPATH_ADMINISTRATOR . '/components/com_categories';
		  require_once $basePath . '/tables/category.php';
		  $db =& JFactory::getDbo();
		  $catmodel = new CategoriesTableCategory($db);
		  $catData = array(
			 'id' => 0,
			 'parent_id' => 1,
			 'level' => 1,
			 'checked_out' => 0,
			 'checked_out_time' => Configure::read('DB.field.datetime.empty'),
			 'path' => 'gas-'.strtolower($title_group),
			 'extension' => 'com_content',
			 'title' => $title,
			 'alias' => 'gas-'.strtolower($title_group),
			 'description' => '',
			 'published' => 1,
			 'params' => '{"category_layout":"","image":""}',
			 'metadata' => '{"author":"","robots":""}',
			 'language' => '*'
		  );
		  $esito = $catmodel->save( $catData);
	  
		  if($esito==1) {
				$sql = "SELECT * FROM ".Configure::read('DB.portalPrefix')."categories where title = '$title';";
				$results = $this->Organization->query($sql);
								
				if(!empty($results) && isset($results[0])) {
					$id_category = $results[0]['j_categories']['id'];
					
					/*
					 * bugs
					 */
					 $sql = "UPDATE ".Configure::read('DB.portalPrefix')."categories SET parent_id=1, level=1 WHERE ID= ".$id_category." ;";
					 $updateResults = $this->Organization->query($sql);
				}
			}			
		}
	
		$this->set('sql', $sql);
		$this->set('id_category', $id_category);
		
        $this->layout = 'ajax';
        $this->render('/Organizations/admin_ajax_joomla_category');
	}

	public function admin_ajax_joomla_menu($organizationId, $gasAlias, $gasUpperCase, $gasAliaSEO) {

		$sql = '';
		$results = [];
		
		if(!empty($organizationId) && !empty($gasAlias) && !empty($gasUpperCase) && !empty($gasAliaSEO)) {
			
			$sql = "SELECT id FROM ".Configure::read('DB.portalPrefix')."template_styles where params like '{\"organizationId\":".$organizationId."%';";
			$results = $this->Organization->query($sql);
			if(!empty($results) && isset($results[0])) {
				$id_template_styles = $results[0]['j_template_styles']['id'];
				// echo $id_template_styles;
			}
			else {
				debug("No result ".$sql);
			}
			
			$sql = "SELECT id FROM ".Configure::read('DB.portalPrefix')."viewlevels where title = 'Registred".$gasUpperCase."';";
			$results = $this->Organization->query($sql);
			if(!empty($results) && isset($results[0])) {
				$id_viewlevels = $results[0]['j_viewlevels']['id'];
				// echo $id_viewlevels;
			}
			else {
				debug("No result ".$sql);
			}

			/*
			 * menutype string(24)
			 */
			$topmenu_name = 'topmenu-'.$gasAliaSEO;
			if(strlen($topmenu_name)>24)
				$topmenu_name = substr ($topmenu_name, 0, 23);
			
			$sql = ""; 
		    $sql .= "INSERT INTO `".Configure::read('DB.portalPrefix')."menu_types` (`menutype`, `title`) values ('$topmenu_name', 'Top menu ".$gasUpperCase."'); <br />";
		  	// $insertResults = $this->Organization->query($sql);
			
			$sql .= "<h2>Seleziono tutte le voce del menù Top menu Gas GassePiossasco e \"Seleziona il menu per Spostare/Copiare\"</h2>";
			
			$sql .= "UPDATE ".Configure::read('DB.portalPrefix')."menu set title = '".$gasUpperCase."' where menutype = '$topmenu_name' and title like '%(2)'; <br />";
			
			$sql .= "UPDATE ".Configure::read('DB.portalPrefix')."menu set alias = REPLACE(alias, 'gassepiossasco-2', '".$gasAlias."') where menutype = '$topmenu_name'; <br />";
			
			$sql .= "UPDATE ".Configure::read('DB.portalPrefix')."menu set path = REPLACE(path, 'gassepiossasco-2', '".$gasAlias."') where menutype = '$topmenu_name'; <br />";
			 
			$sql .= "UPDATE ".Configure::read('DB.portalPrefix')."menu set alias = REPLACE(alias, 'gassepiossasco', '".$gasAlias."') where menutype = '$topmenu_name'; <br />";
			
			$sql .= "UPDATE ".Configure::read('DB.portalPrefix')."menu set path = REPLACE(path, 'gassepiossasco', '".$gasAlias."') where menutype = '$topmenu_name'; <br />";
			 
			$sql .= "UPDATE ".Configure::read('DB.portalPrefix')."menu set params = REPLACE(params, 'GassePiossasco', '".$gasUpperCase."') where menutype = '$topmenu_name'; <br />";
			 
			// template 			 
			$sql .= "UPDATE ".Configure::read('DB.portalPrefix')."menu set template_style_id = $id_template_styles where menutype = '$topmenu_name';<br />";

			// livello d'accesso 			 
			$sql .= "UPDATE ".Configure::read('DB.portalPrefix')."menu set access = $id_viewlevels where access!=1 and menutype = '$topmenu_name';<br />";
		}
	
		$this->set('sql', $sql);
		
        $this->layout = 'ajax';
        $this->render('/Organizations/admin_ajax_joomla_menu');
	}	
	
	public function admin_ajax_joomla_modules($organizationId, $gasAlias, $gasUpperCase, $gasAliaSEO) {

		$sql = '';
		$results = [];
		$modules = ['163' => 'Documenti del GAS', 
				  '119' => 'Facebook LikeBox', 
				  '118' => 'Facebook Html',
				  '109' => 'Gas - Contenuto immagine'];

		if(!empty($organizationId) && !empty($gasAlias) && !empty($gasUpperCase) && !empty($gasAliaSEO)) {
			
			$sql = "SELECT id FROM ".Configure::read('DB.portalPrefix')."menu where alias = 'home-".$gasAliaSEO."'";
			$results = $this->Organization->query($sql);
			if(!empty($results) && isset($results[0])) {
				$menu_id = $results[0]['j_menu']['id'];

				$sql = 'Eseguito <br />';
				foreach ($modules as $id => $name) {
					$sql .= "INSERT INTO `".Configure::read('DB.portalPrefix')."modules_menu` (`moduleid`, `menuid`) values ($id, $menu_id); <br />";

					$insertResults = $this->Organization->query($sql);
				}
			}
			else {
				debug("No result ".$sql);
				$sql = '';
			}
		}
	
		$this->set('sql', $sql);
		
        $this->layout = 'ajax';
        $this->render('/Organizations/admin_ajax_joomla_modules');
	}	

	public function admin_get_user_details($q='', $format = 'notmpl') {
		
		/*
		 * elenco di tutti i gruppi dell'organization userGroupsComponent
		*/
		$this->set('userGroups',$this->userGroups);
		
		$debug=false;
		
        App::import('Model', 'User');
        $User = new User;
		 
		$User->unbindModel(['hasMany' => ['Cart']]);
		$User->bindModel(['belongsTo' => ['Organization' => ['className' => 'Organization', 'foreignKey' => 'organization_id']]]);

        $options = [];
		$options['conditions'] = ['OR' => ['lower(User.username) LIKE' => '%' . strtolower(addslashes($q)) . '%',
								           'lower(User.email) LIKE' => '%' . strtolower(addslashes($q)) . '%']];
		$options['recursive'] = 1;
		self::d($options, $debug);
        $results = $User->find('all', $options);

		if(!empty($results))
		foreach($results as $numResult => $result) {
			/*
			 * maganer del GAS
			 */
			$tmp_user = $this->utilsCommons->createObjUser(['Organization' => $result['Organization']]); 
			$conditions = ['UserGroup.id' => Configure::read('group_id_manager')];
			$results[$numResult]['Organization']['Manager'] = $User->getUsers($tmp_user, $conditions);
		}
		self::d($results, $debug);
		$this->set(compact('results'));
		
        $this->layout = 'ajax';
        $this->render('/Organizations/admin_ajax_user_details');		
	}
}