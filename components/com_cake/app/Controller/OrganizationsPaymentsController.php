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
			$paramsPay = [];
			$paramsPay += array('payMail' => $this->request->data['OrganizationsPayment']['payMail']);
			$paramsPay += array('payContatto' => $this->request->data['OrganizationsPayment']['payContatto']);
			$paramsPay += array('payIntestatario' => $this->request->data['OrganizationsPayment']['payIntestatario']);
			$paramsPay += array('payIndirizzo' => $this->request->data['OrganizationsPayment']['payIndirizzo']);
			$paramsPay += array('payCap' => $this->request->data['OrganizationsPayment']['payCap']);
			$paramsPay += array('payCitta' => $this->request->data['OrganizationsPayment']['payCitta']);
			$paramsPay += array('payProv' => $this->request->data['OrganizationsPayment']['payProv']);
			$paramsPay += array('payCf' => $this->request->data['OrganizationsPayment']['payCf']);
            $paramsPay += array('payPiva' => $this->request->data['OrganizationsPayment']['payPiva']);
            $paramsPay += array('payType' => $this->request->data['OrganizationsPayment']['payType']);
			$this->request->data['OrganizationsPayment']['paramsPay'] = json_encode($paramsPay);
			
			self::d($this->request->data, $debug);
			
			$this->request->data['OrganizationsPayment']['id'] = $this->user->organization['Organization']['id'];
			
			$this->OrganizationsPayment->create();
			if ($this->OrganizationsPayment->save($this->request->data['OrganizationsPayment'])) {
				$this->Session->setFlash(__('The organization has been saved'));
			} else {
				$this->Session->setFlash(__('The organization could not be saved. Please, try again.'));
			}			
		} // POST
		
		$options = [];
		$options['conditions'] = ['OrganizationsPayment.id' => $this->user->organization['Organization']['id']];
		$options['recursive'] = 1;
	
        $this->request->data = $this->OrganizationsPayment->find('first', $options);

		$paramsPay = json_decode($this->request->data['OrganizationsPayment']['paramsPay'], true);
        if(!empty($paramsPay))
    		$this->request->data['OrganizationsPayment'] += $paramsPay;

		App::import('Model', 'Template');
        $Template = new Template;
		
		$options = [];
		$options['conditions'] = ['Template.id' => $this->request->data['OrganizationsPayment']['template_id']];
		$options['recursive'] = -1;
        $templateResults = $Template->find('first', $options);

		$this->request->data += $templateResults;
				
		self::d($this->request->data, $debug);

		$options = [];
		$options['recursive'] = -1;
        $templateResults = $Template->find('all', $options);
		$this->set('templateResults',$templateResults);
	
		$table_plan = & JTable::getInstance('Content', 'JTable');
		$table_plan_return  = $table_plan->load(array('id'=>103));
		$this->set('table_plan', $table_plan);
	//	echo $table_plan->intro_text;
	//	echo $table_plan->full_text;
	
		// pdf
		$pdf_url = '';
		$pdf_label = 'documento canone annuale relativo all\'anno ';
		$year = date('Y'); 
		if(file_exists(Configure::read('App.root').Configure::read('App.doc.upload.organizations.pays').DS.$year.DS.$this->user->organization['Organization']['id'].'.pdf')) {
			$pdf_url = '/images/pays/'.$year.'/'.$this->user->organization['Organization']['id'].'.pdf';
			$pdf_label = $pdf_label.$year;
		}
		
		if(empty($pdf_url)) {
			$year--;; 
			if(file_exists(Configure::read('App.root').Configure::read('App.doc.upload.organizations.pays').DS.$year.DS.$this->user->organization['Organization']['id'].'.pdf')) {
				$pdf_url = '/images/pays/'.$year.'/'.$this->user->organization['Organization']['id'].'.pdf';
				$pdf_label = $pdf_label.$year;
			}	
		}
				
		$this->set(compact('pdf_url', 'pdf_label'));
	}
	
	/*
	 * insert/update articolo in joomla
	 */			
	private function _gestJContent($data, $results, $debug=false) {

		$table = JTable::getInstance('Content', 'JTable', []);
		
		$data = array(
				'catid' => $results['CategoriesSupplier']['j_category_id'],
				'title' => $results['Supplier']['name'],
				'intro_text' => $data['SuppliersOrganizationsJcontent']['intro_text'],
				'full_text' => $data['SuppliersOrganizationsJcontent']['full_text'],
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