<?php
App::uses('AppController', 'Controller');

class DesSuppliersController extends AppController {

	public $components = array('Paginator');

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
		
		/* ctrl ACL */
		if(!$this->isManagerDes()) {
			 $this->Session->setFlash(__('msg_not_permission'));
			 $this->myRedirect(Configure::read('routes_msg_stop'));
		}
		/* ctrl ACL */
                
  		$this->set('isManagerDes', $this->isManagerDes());
   		$this->set('isReferenteDes', $this->isReferenteDes());
   		$this->set('isSuperReferenteDes', $this->isSuperReferenteDes());
   		$this->set('isTitolareDesSupplier', $this->isTitolareDesSupplier());		
    }

	public function admin_index() {

		$debug = false;

		/*
		 *  list GAS del DES, per ctrl che i produttori del DES siano associati ad ogni GAS
		 */
   		App::import('Model', 'DesOrganization');
		$DesOrganization = new DesOrganization;

		$options = [];
 		$options['conditions'] = ['DesOrganization.des_id' => $this->user->des_id];
		$options['fields'] = ['DesOrganization.organization_id','Organization.name','Organization.img1'];
		$options['recursive'] = 0;
   		$desOrganizationResults = $DesOrganization->find('all', $options);
		
		App::import('Model', 'DesSuppliersReferent');
		App::import('Model', 'DesOrder');
	
   		$options = [];
   		$options['recursive'] = 0;
   		$options['conditions'] = ['DesSupplier.des_id' => $this->user->des_id];
   		$results = $this->DesSupplier->find('all', $options);

		foreach ($results as $numResult => $result) {

			/*
			 * per ogni DesSuplier estraggo i DesReferenti
			 */
			$DesSuppliersReferent = new DesSuppliersReferent();
			$conditions = [];
			$conditions = array('DesSupplier.des_id' => $result['DesSupplier']['des_id'],
								'DesSupplier.supplier_id' => $result['DesSupplier']['supplier_id']);
									
			$results[$numResult]['DesSuppliersReferents'] = $DesSuppliersReferent->getReferentsDesCompact($conditions, null, $debug);				

			/*
			 * per ogni DesSuplier estraggo totali DesOrder
			 */
			$DesOrder = new DesOrder();
			$options = [];
			$options['conditions'] = array('DesOrder.des_id' => $result['DesSupplier']['des_id'],
											'DesOrder.des_supplier_id' => $result['DesSupplier']['id']);
			$results[$numResult]['DesOrder']['totali'] = $DesOrder->find('count', $options);
			
			/*
		     *  ctrl che il GAS abbia associato il produttore 	
		     */
			$hasOrganizationsSupplier = true;
			$i=0;
			foreach($desOrganizationResults as $desOrganizationResult) {
				$hasOrganizationSupplier = $this->DesSupplier->hasOrganizationSupplier($this->user, $desOrganizationResult['DesOrganization']['organization_id'], $result['DesSupplier']['id'], $debug); 
				if(!$hasOrganizationSupplier) {
					$results[$numResult]['DesOrganization'][$i] = $desOrganizationResult;
					$i++;
					$hasOrganizationsSupplier = false;
				}
			}
			
			$results[$numResult]['DesSupplier']['hasOrganizationsSupplier'] = $hasOrganizationsSupplier;
		}
		
		self::d($results,false);
		
   		$this->set('results', $results);

	}

	public function admin_add() {

		if ($this->request->is('post')) {
			$this->DesSupplier->create();
			
			$this->request->data['DesSupplier']['des_id'] = $this->user->des_id;
			$this->request->data['DesSupplier']['own_organization_id'] = 0;

			if ($this->DesSupplier->save($this->request->data)) {
				$this->Session->setFlash(__('The des supplier has been saved'));
				$this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The des supplier could not be saved. Please, try again.'));
			}
		} // end if ($this->request->is('post'))
		
        $supplier_ids = [];

		/*
		 * estraggo quelli gia' associati per escluderli dalla lista
		 */
   		$options = [];
   		$options['recursive'] = -1;
   		$options['conditions'] = ['DesSupplier.des_id' => $this->user->des_id];
   		$options['fields'] = ['DesSupplier.supplier_id'];
   		$results = $this->DesSupplier->find('all', $options);
        if(!empty($results))
        foreach($results as $result) {
            array_push($supplier_ids, $result['DesSupplier']['supplier_id']);
        }

        $supplier_states = ['Y', 'T', 'PG'];

        $options = [];
        $options['conditions'] = ['Supplier.stato' => $supplier_states];
        if(!empty($supplier_ids))
            $options['conditions'] += ['Supplier.id NOT IN ' => $supplier_ids];
        $options['recursive'] = -1;
        $options['order'] = ['Supplier.name'];
        $suppliers = $this->DesSupplier->Supplier->find('list', $options);
        self::d($options,false);
        self::d($suppliers,false);

		$this->set(compact('suppliers'));
	}

	public function admin_delete($id = null) {
	
		$debug = false;
		$continua = true;
	
		$this->DesSupplier->id = $id;
		if (!$this->DesSupplier->exists($this->DesSupplier->id)) {
			throw new NotFoundException(__('Invalid des supplier'));
		}
		$this->request->onlyAllow('get', 'delete');

		/*
		 * riporto il SuppliersOrganizations a owner_articles = REFERENT se = DES
		 */		
   		$options = [];
   		$options['recursive'] = -1;
   		$options['conditions'] = ['DesSupplier.des_id' => $this->user->des_id, 
   								   'DesSupplier.id' => $id];
   		$options['recursive'] = -1;						   
   		$desSupplierResults = $this->DesSupplier->find('first', $options);
		self::d($desSupplierResults, $debug);

        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;
   		
   		$options = [];
   		$options['recursive'] = -1;
   		$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'], 
   								   'SuppliersOrganization.supplier_id' => $desSupplierResults['DesSupplier']['supplier_id']];
   		$options['recursive'] = -1;
   		$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
   		
   		self::d($suppliersOrganizationResults, $debug);
		
		if(!empty($suppliersOrganizationResults) && 
			$suppliersOrganizationResults['SuppliersOrganization']['owner_articles']=='DES') {
				
				/*
				 * associo il gestore del listino al proprio GAS
				 */
				$suppliersOrganizationResults['SuppliersOrganization']['owner_articles'] = 'REFERENT';
				$suppliersOrganizationResults['SuppliersOrganization']['owner_organization_id'] = $this->user->organization['Organization']['id'];
				$suppliersOrganizationResults['SuppliersOrganization']['owner_supplier_organization_id'] = $suppliersOrganizationResults['SuppliersOrganization']['id'];
	            
	            $SuppliersOrganization->create();
	            if (!$SuppliersOrganization->save($suppliersOrganizationResults)) {
					$this->Session->setFlash(__('DesSupplier was not deleted'));
					$continua = false;
				}				
			}
		
		if($continua) {
			if ($this->DesSupplier->delete()) {
				$this->Session->setFlash(__('Delete DesSupplier'));
			} else {
				$this->Session->setFlash(__('DesSupplier was not deleted'));
			}
		}
		
		$this->myRedirect(['action' => 'index']);
	}
}
