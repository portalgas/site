<?php
App::uses('AppController', 'Controller');

class DesOrdersOrganizationsController extends AppController {
  
   public $components = array('ActionsDesOrder'); 
   public $helpers = array('Html', 'Javascript', 'Ajax');
   
   public function beforeFilter() {
   		parent::beforeFilter();
   		
		/* ctrl ACL */
   		if($this->user->organization['Organization']['hasDes']=='N' || !$this->isDes()) {
   			$this->Session->setFlash(__('msg_not_organization_config'));
   			$this->myRedirect(Configure::read('routes_msg_stop'));
   		}
		/* ctrl ACL */
		   
		if(empty($this->user->des_id)) {
            $this->Session->setFlash(__('msg_des_choice'));
			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Des&action=index';
			$this->myRedirect($url);
        }
		
  		$this->set('isManagerDes', $this->isManagerDes());
   		$this->set('isReferenteDes', $this->isReferenteDes());
   		$this->set('isSuperReferenteDes', $this->isSuperReferenteDes());
   		$this->set('isTitolareDesSupplier', $this->isTitolareDesSupplier());		
   }

   public function admin_index($des_order_id) {

		$debug = false;
		
		if (empty($des_order_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		App::import('Model', 'DesOrder');
	   	$DesOrder = new DesOrder;
	   
   		if($this->isSuperReferenteDes()) {
   				
   		}
   		else {
	 		if(!$DesOrder->aclReferenteDesSupplier($this->user, $des_order_id)) { 
	 			$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));
	  		}
   		}


  		/*
		 * aggiorno lo stato del desOrders
		* */
		$utilsCrons = new UtilsCrons(new View(null));
		if(Configure::read('developer.mode')) echo "<pre>";
		$utilsCrons->desOrdersStatoElaborazione($this->user->des_id, $des_order_id, (Configure::read('developer.mode')) ? true : false);
		if(Configure::read('developer.mode')) echo "</pre>";
		   		
		   				
	   /*
	    *  tutti i dati del DesOrder
	    */
	   	$results = $DesOrder->getDesOrder($this->user, $des_order_id);
	    if(empty($results)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));	    
	    }
	    $this->set(compact('results'));
		
	   /*
		 * escludo il GAS dall'array di tutti i GAS del DES
		 */		
		$desOrganizationsResults = [];
		$desOrganizationIds = '';
		
		if(!empty($results['DesOrdersOrganizations']))
			foreach ($results['DesOrdersOrganizations'] as $resultDesOrdersOrganization) 
				$desOrganizationIds .= $resultDesOrdersOrganization['DesOrdersOrganization']['organization_id'].',';

		
		/*
		* tutti i GAS del DES esclusi nell'DesOrder
		*/
		App::import('Model', 'DesOrganization');
		$DesOrganization = new DesOrganization;
		   
		$options = [];
		$options['conditions'] = ['DesOrganization.des_id' => $this->user->des_id];
		if(!empty($desOrganizationIds)) {
			$desOrganizationIds = substr($desOrganizationIds, 0, strlen($desOrganizationIds)-1);
			$options['conditions'] += ['DesOrganization.organization_id NOT IN ('.$desOrganizationIds.')'];
		}
		$options['recursive'] = 1;
		$desOrganizationsResults = $DesOrganization->find('all', $options);
		$this->set(compact('desOrganizationsResults'));
		
		/*
		 * ctrl ACL, se titolare, se non ci sono ordini, il primo lo può creare solo lui 
		 */
		App::import('Model', 'DesOrdersOrganization');
		$DesOrdersOrganization = new DesOrdersOrganization();

		$options = [];
		$options['conditions'] = ['DesOrdersOrganization.des_id' => $this->user->des_id,
								  'DesOrdersOrganization.des_order_id' => $des_order_id,
								  'Order.state_code != ' => 'CREATE-INCOMPLETE'];
		$options['recursive'] = 0;
		$desOrdersOrganizationResults = $DesOrdersOrganization->find('all', $options);
		$totaliDesOrdersOrganization = count($desOrdersOrganizationResults);
		self::d($totaliDesOrdersOrganization, $debug);
		$this->set(compact('totaliDesOrdersOrganization'));
		
		/*
		 * non e' stato creato alcun ordine, solo il titolare puo'
		 */
		$isTitolareDesSupplier = $this->ActionsDesOrder->isTitolareDesSupplier($this->user, $des_order_id);	
		self::d($suppliersOrganizationResults, $debug);	
		$this->set(compact('isTitolareDesSupplier'));

	   /*
	    *  ctrl se il produttore ha il owner_articles DES o REFERENT + Titolare
	    */
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		   
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
								  'SuppliersOrganization.supplier_id' => $results['Supplier']['id']];
		$options['fields'] = ['id', 'owner_articles', 'owner_organization_id', 'owner_supplier_organization_id'];
		$options['recursive'] = -1;
		$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
		self::d($suppliersOrganizationResults, $debug);
		if(!empty($suppliersOrganizationResults)) {
			if($suppliersOrganizationResults['SuppliersOrganization']['owner_articles']=='DES' && !$isTitolareDesSupplier)
				$acl_owner_articles = true;
			else
			if($suppliersOrganizationResults['SuppliersOrganization']['owner_articles']=='REFERENT' && $isTitolareDesSupplier)
				$acl_owner_articles = true;
			else
			if($suppliersOrganizationResults['SuppliersOrganization']['owner_articles']=='SUPPLIER' && $isTitolareDesSupplier)
				$acl_owner_articles = true;
			else {
				$acl_owner_articles['supplier_organization_id'] = $suppliersOrganizationResults['SuppliersOrganization']['id'];
				$acl_owner_articles['owner_articles'] = $suppliersOrganizationResults['SuppliersOrganization']['owner_articles']; // per esempio REFERENT-TMP
			}
			self::d($acl_owner_articles, $debug);
		}
		else  {
			// il GAS non ha il produttore
			$acl_owner_articles['supplier_organization_id'] = 0;
			$acl_owner_articles['owner_articles'] = '';
		}
		$this->set(compact('acl_owner_articles'));
				
		$group_id = Configure::read('group_id_titolare_des_supplier');
		$desOrderStatesToLegenda = $this->ActionsDesOrder->getDesOrderStatesToLegenda($this->user, $group_id);
		$this->set('desOrderStatesToLegenda', $desOrderStatesToLegenda);	
		
		/*
		 * ctrl se e' referentDesAllGas per il produttore: potra' visualizzare gli ordini dei GAS
		 */ 
		$isReferentDesAllGasDesSupplier = $this->ActionsDesOrder->isReferentDesAllGasDesSupplier($this->user, $des_order_id);		
		$this->set('isReferentDesAllGasDesSupplier', $isReferentDesAllGasDesSupplier);	
   }	
   
   public function admin_edit($des_order_id=0) {
	   
		if (empty($des_order_id)) 
			$des_order_id = $this->request->data['DesOrdersOrganization']['des_order_id'];
		
		if (empty($des_order_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}	   

		App::import('Model', 'DesOrder');
	   	$DesOrder = new DesOrder;
	   	
   		if($this->isSuperReferenteDes()) {
   				
   		}
   		else {
	 		if(!$DesOrder->aclReferenteDesSupplier($this->user, $des_order_id)) { 
	 			$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));
	  		}
   		}

		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->request->data['DesOrdersOrganization']['des_id'] = $this->user->des_id;
			$this->request->data['DesOrdersOrganization']['des_order_id'] = $des_order_id;
			$this->request->data['DesOrdersOrganization']['organization_id'] = $this->user->organization['Organization']['id'];
				
			$this->request->data['DesOrdersOrganization']['data'] = $this->request->data['DesOrdersOrganization']['data_db'];
			
            $this->DesOrdersOrganization->create();
            if ($this->DesOrdersOrganization->save($this->request->data)) {
                $this->Session->setFlash(__('The DesOrdersOrganization has been saved'));
            } else {
                $this->Session->setFlash(__('The DesOrdersOrganization could not be saved. Please, try again.'));
            }	

			$this->myRedirect(Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=DesOrdersOrganizations&action=index&des_order_id=' . $des_order_id);			
		} // end POST

       /*
         *  tutti i dati del DesOrder
         */
        $desOrdersResults = $DesOrder->getDesOrder($this->user, $des_order_id);
    
		$this->set(compact('desOrdersResults'));

		$options = [];
		$options['recursive'] = 0;
 		$options['conditions'] = array('DesOrdersOrganization.des_id' => $this->user->des_id,
										'DesOrdersOrganization.des_order_id' => $des_order_id,
										'DesOrdersOrganization.organization_id' => $this->user->organization['Organization']['id']);
		$this->DesOrdersOrganization->unbindModel(array('belongsTo' => array('De', 'Organization', 'Order')));
   		$this->request->data = $this->DesOrdersOrganization->find('first', $options);
   	   		
   		if(empty($this->request->data['DesOrdersOrganization']['orario'])) {
   		}   		
   	
   		$this->set('des_order_id', $des_order_id);		
   }
   
   public function admin_delete($des_order_id, $id) {
	   
		if (empty($des_order_id) || empty($id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}	   

		App::import('Model', 'DesOrder');
	   	$DesOrder = new DesOrder;
	   
   		if($this->isSuperReferenteDes()) {
   				
   		}
   		else {
	 		if(!$DesOrder->aclReferenteDesSupplier($this->user, $des_order_id)) { 
	 			$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));
	  		}
   		}

		$this->DesOrdersOrganization->id = $id;
		if (!$this->DesOrdersOrganization->exists($this->DesOrdersOrganization->id)) {
			throw new NotFoundException(__('Invalid DesOrdersOrganization'));
		}
		$this->request->onlyAllow('get');
		if ($this->DesOrdersOrganization->delete()) {
			$this->Session->setFlash(__('The DesOrdersOrganization has been deleted.'));
		} else {
			$this->Session->setFlash(__('The DesOrdersOrganization could not be deleted. Please, try again.'));
		}

		/*
		if (!$this->DesOrdersOrganization->exists($id, $this->user->organization['Organization']['id'])) {
			throw new NotFoundException(__('Invalid DesOrdersOrganization'));
		}
		if ($this->request->is(array('get'))) {
			if ($this->De->save($this->request->data)) {
				$this->Session->setFlash(__('The de has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The de could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('De.' . $this->De->primaryKey => $id));
			$this->request->data = $this->De->find('first', $options);
		}

		
		$sql = "DELETE
					".Configure::read('DB.prefix')."_des_orders_organizations 
				WHERE
					organization_id = ".(int)$this->user->organization['Organization']['id']."
					and des_order_id = ".(int)$des_order_id."
					and id = ".(int)$this->order_id;
		self::d($sql, $debug);
		$resultUpdate = $this->DesOrdersOrganization->query($sql);

			
		$options = [];
		$options['recursive'] = -1;
 		$options['conditions'] = array('DesOrdersOrganization.des_id' => $this->user->des_id,
										'DesOrdersOrganization.des_order_id' => $des_order_id,
										'DesOrdersOrganization.organization_id' => $organization_id);
   		$this->DesOrdersOrganization = $this->DesOrdersOrganization->find('first', $options);
		$this->request->onlyAllow('get');
		if ($this->DesOrdersOrganization->delete()) {
			$this->Session->setFlash(__('The DesOrdersOrganization has been deleted.'));
		} else {
			$this->Session->setFlash(__('The DesOrdersOrganization could not be deleted. Please, try again.'));
		}
		*/
		$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=DesOrdersOrganizations&action=index&des_order_id='.$des_order_id;
		$this->myRedirect($url);	
   }
}