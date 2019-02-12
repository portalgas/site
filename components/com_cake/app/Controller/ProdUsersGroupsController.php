<?php
App::uses('AppController', 'Controller');

class ProdUsersGroupsController extends AppController {

	public $components = array('Paginator');
	
	public function beforeFilter() {
		parent::beforeFilter();
	}
	
	/*
	 * elenco degli utenti associati al gruppo: gestisco SORTABLE
	 */
	public function admin_index($prod_group_id) {
		
		if(empty($prod_group_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		/*
		 * gruppo degli utenti
		 */
		App::import('Model', 'ProdGroup');
		$ProdGroup = new ProdGroup;
		
		$options = [];
		$options['conditions'] = array('ProdGroup.organization_id' => $this->user->organization['Organization']['id'],
									   'ProdGroup.id' => $prod_group_id);
		$options['recursive'] = -1;
		$prodGroupResults = $ProdGroup->find('first', $options);
		$this->set('prodGroup', $prodGroupResults);
		
		/*
		 * users associati
		 */
		$options = [];
		$options['conditions'] = array('ProdUsersGroup.organization_id' => $this->user->organization['Organization']['id'],
						    			'ProdUsersGroup.prod_group_id' => $prod_group_id,
										'User.block' => 0);
		$options['recursive'] = 0;
		$results = $this->ProdUsersGroup->find('all', $options);
		$prodUsersGroups = [];
		foreach ($results as $numResult => $result) {
			$prodUsersGroups[$numResult] = $result;
			$prodUsersGroups[$numResult]['User']['label'] = $this->ProdUsersGroup->getUserLabel($result);
		}
		
		$this->set('prodUsersGroups', $prodUsersGroups);
		$this->set('prodGroup', $prodGroupResults);
	}

	/*
	 * aggiungo gli utenti al gruppo
	*/
	public function admin_add($prod_group_id) {
		
		if(empty($prod_group_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
				
		if ($this->request->is('post')) {
			
			$msg = "";
			$user_ids = $this->request->data['ProdUsersGroup']['user_ids'];
			$arr_user_ids = explode(',',$user_ids);
			
			/*
			 * ottengo l'ultimo ProdUsersGroup.sort cosi' lo incremento da li: gli utenti vengono aggiunti per ultimo
			 */
			$maxSort = $this->ProdUsersGroup->getMaxSort($this->user, $prod_group_id);
			 
			foreach($arr_user_ids as $numResult => $user_id) {

				$data['ProdUsersGroup']['organization_id'] = $this->user->organization['Organization']['id'];
				$data['ProdUsersGroup']['user_id'] = $user_id;
				$data['ProdUsersGroup']['prod_group_id'] = $prod_group_id;
				$data['ProdUsersGroup']['sort'] = ($numResult + $maxSort);
				
				$this->ProdUsersGroup->create();
				if (!$this->ProdUsersGroup->save($data)) {
					$msg .= "<br />utente $user_id non associato!";
				} else {
					
				}				
			}
			
			if(!empty($msg)) 
				$this->Session->setFlash(__('The prod users group could not be saved. Please, try again.').$msg);
			else
				$this->Session->setFlash(__('The prod users group has been saved.'));
				
			return $this->myRedirect(['action' => 'index']);	
		} // end if ($this->request->is('post')) 

		/*
		 * gruppo degli utenti
		*/
		App::import('Model', 'ProdGroup');
		$ProdGroup = new ProdGroup;
		
		$options = [];
		$options['conditions'] = array('ProdGroup.organization_id' => $this->user->organization['Organization']['id'],
										'ProdGroup.id' => $prod_group_id);
		$options['recursive'] = -1;
		$prodGroupResults = $ProdGroup->find('first', $options);
		$this->set('prodGroup', $prodGroupResults);
				
		/*
		 * lista degli utenti ancora da associare
		 */
		$users = $this->ProdUsersGroup->getUsersListToAssocite($this->user, $prod_group_id);
		$this->set(compact('users'));
	}

	public function admin_delete($id = null) {
		$this->ProdUsersGroup->id = $id;
		if (!$this->ProdUsersGroup->exists($this->user->organization['Organization']['id'])) {
			throw new NotFoundException(__('Invalid prod users group'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->ProdUsersGroup->delete()) {
			$this->Session->setFlash(__('The prod users group has been deleted.'));
		} else {
			$this->Session->setFlash(__('The prod users group could not be deleted. Please, try again.'));
		}
		return $this->myRedirect(['action' => 'index']);
	}
	
	public function admin_sort_users($prod_group_id, $original_sort, $new_sort) {

		$debug = false;
		
		/*
		 * l'id di chi e' cambiato
		*/
		$options['conditions'] = array('ProdUsersGroup.organization_id' => $this->user->organization['Organization']['id'],
				'ProdUsersGroup.prod_group_id' => $prod_group_id,
				'ProdUsersGroup.sort' => $original_sort);
		$options['recursive'] = -1;
		$options['fields'] = array('id');
		$results = $this->ProdUsersGroup->find('first', $options);
		$id_original_sort = $results['ProdUsersGroup']['id'];
		if($debug) echo "<br />id_original_sort (filtrato con sort $original_sort) ".$id_original_sort;
		
		/*
		 *  l'id di chi e' stato cambiato
		 */
		$options['conditions'] = array('ProdUsersGroup.organization_id' => $this->user->organization['Organization']['id'],
										'ProdUsersGroup.prod_group_id' => $prod_group_id,
										'ProdUsersGroup.sort' => $new_sort);
		$options['recursive'] = -1;
		$options['fields'] = array('id');
		$results = $this->ProdUsersGroup->find('first', $options);
		$id_new_sort = $results['ProdUsersGroup']['id'];
		if($debug) echo "<br />id_new_sort (filtrato con sort $new_sort) ".$id_new_sort;

		/*
		 * aggiorno l'id di chi e' cambiato con la nuova posizione
		 */
		$data['ProdUsersGroup']['id'] = $id_original_sort;
		$data['ProdUsersGroup']['sort'] = $new_sort;
		
		self::d($data,$debug);
				
		$this->ProdUsersGroup->create();
		$this->ProdUsersGroup->save($data);
		
		/*
		 * aggiorno  l'id di chi e' stato cambiato con la vecchia posizione
		*/
		$data['ProdUsersGroup']['id'] = $id_new_sort;
		$data['ProdUsersGroup']['sort'] = $original_sort;
		
		self::d($data,$debug);
				
		$this->ProdUsersGroup->create();
		$this->ProdUsersGroup->save($data);
		
		$this->layout = 'ajax';
		$this->render('/Layouts/ajax');
	}
}
