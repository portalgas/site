<?php
App::uses('AppController', 'Controller');

class OrganizationsPaymentsController extends AppController {
	
	public function beforeFilter() {
		parent::beforeFilter();

		/* ctrl ACL */
		if(!$this->isManager()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		/* ctrl ACL */
	}

	public function admin_edit() {

		$debug = false;
		
		if ($this->request->is('post') || $this->request->is('put')) {
			/*
			 *  pay
			 */
			$paramsPay = array();
			$paramsPay += array('payMail' => $this->request->data['OrganizationsPayment']['payMail']);
			$paramsPay += array('payContatto' => $this->request->data['OrganizationsPayment']['payContatto']);
			$paramsPay += array('payIntestatario' => $this->request->data['OrganizationsPayment']['payIntestatario']);
			$paramsPay += array('payIndirizzo' => $this->request->data['OrganizationsPayment']['payIndirizzo']);
			$paramsPay += array('payCap' => $this->request->data['OrganizationsPayment']['payCap']);
			$paramsPay += array('payCitta' => $this->request->data['OrganizationsPayment']['payCitta']);
			$paramsPay += array('payProv' => $this->request->data['OrganizationsPayment']['payProv']);
			$paramsPay += array('payCf' => $this->request->data['OrganizationsPayment']['payCf']);
			$paramsPay += array('payPiva' => $this->request->data['OrganizationsPayment']['payPiva']);
			$this->request->data['OrganizationsPayment']['paramsPay'] = json_encode($paramsPay);
			
			if($debug) {
				echo "<pre>";
				print_r($this->request->data);
				echo "</pre>";
				exit;
			}
			
			$this->request->data['OrganizationsPayment']['id'] = $this->user->organization['Organization']['id'];
			
			$this->OrganizationsPayment->create();
			if ($this->OrganizationsPayment->save($this->request->data['OrganizationsPayment'])) {
				$this->Session->setFlash(__('The organization has been saved'));
			} else {
				$this->Session->setFlash(__('The organization could not be saved. Please, try again.'));
			}			
		} // POST
		
		$options = array();
		$options['conditions'] = array('OrganizationsPayment.id' => $this->user->organization['Organization']['id']);
		$options['recursive'] = 1;
	
        $this->request->data = $this->OrganizationsPayment->find('first', $options);
		
		$paramsPay = json_decode($this->request->data['OrganizationsPayment']['paramsPay'], true);
		$this->request->data['OrganizationsPayment'] += $paramsPay;
		/*
  		echo "<pre>";
		print_r($this->request->data);
        echo "</pre>";
		*/
	
		$table_plan = & JTable::getInstance('Content', 'JTable');
		$table_plan_return  = $table_plan->load(array('id'=>103));
		$this->set('table_plan', $table_plan);
	//	echo $table_plan->introtext;
	//	echo $table_plan->fulltext;		
	}
	
	/*
	 * insert/update articolo in joomla
	 */			
	private function __gestJContent($data, $results, $debug=false) {

		$table = JTable::getInstance('Content', 'JTable', array());
		
		$data = array(
				'catid' => $results['CategoriesSupplier']['j_category_id'],
				'title' => $results['Supplier']['name'],
				'introtext' => $data['SuppliersOrganizationsJcontent']['introtext'],
				'fulltext' => $data['SuppliersOrganizationsJcontent']['fulltext'],
				'state' => 1,
		);
		
		if(!empty($results['Supplier']['j_content_id'])) // update
			$id = $results['Supplier']['j_content_id'];
		$data += array('id' => $id);

		if($debug) {
			echo "Dati x articolo Joomla<pre>";
			print_r($data);
			echo "</pre>";
		}
		
		// Bind data
		if (!$table->bind($data))
		{
			$this->Session->setFlash($table->getError());
			if($debug) echo '<h2>'.$table->getError().'</h2>';
		}
		
		// Check the data.
		if (!$table->check())
		{
			$this->Session->setFlash($table->getError());
			if($debug) echo '<h2>'.$table->getError().'</h2>';
		}
		
		// Store the data.
		if (!$table->store())
		{
			$this->Session->setFlash($table->getError());
			if($debug) echo '<h2>'.$table->getError().'</h2>';
		}

		if(!empty($results['Supplier']['j_content_id'])) // update
			$id = $results['Supplier']['j_content_id'];
		else
			$id = $table->get('id');
			
		if($debug) 
			echo '<br />Id Table Joomla '.$id;
		
		return $id;
	}	
}