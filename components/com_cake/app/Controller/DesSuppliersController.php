<?php
App::uses('AppController', 'Controller');
/**
 * DesSuppliers Controller
 *
 * @property DesSupplier $DesSupplier
 * @property PaginatorComponent $Paginator
 */
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
            $this->Session->setFlash(__('Devi scegliere il tuo DES'));
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

		$options = array();
 		$options['conditions'] = array('DesOrganization.des_id' => $this->user->des_id);
		$options['fields'] = array('DesOrganization.organization_id','Organization.name','Organization.img1');
		$options['recursive'] = 0;
   		$desOrganizationResults = $DesOrganization->find('all', $options);
		
		App::import('Model', 'DesSuppliersReferent');
		App::import('Model', 'DesOrder');
	
   		$options = array();
   		$options['recursive'] = 0;
   		$options['conditions'] = array('DesSupplier.des_id' => $this->user->des_id);
   		$results = $this->DesSupplier->find('all', $options);

		foreach ($results as $numResult => $result) {

			/*
			 * per ogni DesSuplier estraggo i DesReferenti
			 */
			$DesSuppliersReferent = new DesSuppliersReferent();
			$conditions = array();
			$conditions = array('DesSupplier.des_id' => $result['DesSupplier']['des_id'],
								'DesSupplier.supplier_id' => $result['DesSupplier']['supplier_id']);
									
			$results[$numResult]['DesSuppliersReferents'] = $DesSuppliersReferent->getReferentsDesCompact($conditions, null, $debug);				

			/*
			 * per ogni DesSuplier estraggo totali DesOrder
			 */
			$DesOrder = new DesOrder();
			$options = array();
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
		/*
  		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/ 		
   		$this->set('results', $results);

	}

	public function admin_add() {
		if ($this->request->is('post')) {
			$this->DesSupplier->create();
			
			$this->request->data['DesSupplier']['des_id'] = $this->user->des_id;
			$this->request->data['DesSupplier']['own_organization_id'] = 0;
		
			if ($this->DesSupplier->save($this->request->data)) {
				$this->Session->setFlash(__('The des supplier has been saved'));
				$this->myRedirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The des supplier could not be saved. Please, try again.'));
			}
		}
		
		$options = array();
		$options['conditions'] = array("(Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG')");
		$options['recursive'] = -1;
		$options['order'] = array('Supplier.name');
		$suppliers = $this->DesSupplier->Supplier->find('list', $options);
		
		/*
		 * escludo quelli gia' associati
		 */
   		$options = array();
   		$options['recursive'] = -1;
   		$options['conditions'] = array('DesSupplier.des_id' => $this->user->des_id);
   		$options['fields'] = array('DesSupplier.supplier_id', 'DesSupplier.supplier_id');
   		$results = $this->DesSupplier->find('list', $options);
		foreach($suppliers as $supplier_id => $supplier) {
			if(in_array($supplier_id, $results))
				unset($suppliers[$supplier_id]);
		}
		
		/*
		echo "<pre>";
		print_r($suppliers);
		echo "</pre>";
		*/
		
		$this->set(compact('suppliers'));
	}

	public function admin_delete($id = null) {
		$this->DesSupplier->id = $id;
		if (!$this->DesSupplier->exists()) {
			throw new NotFoundException(__('Invalid des supplier'));
		}
		$this->request->onlyAllow('get', 'delete');
		if ($this->DesSupplier->delete()) {
			$this->Session->setFlash(__('Delete DesSupplier'));
		} else {
			$this->Session->setFlash(__('DesSupplier was not deleted'));
		}
		$this->myRedirect(array('action' => 'index'));
	}
}
