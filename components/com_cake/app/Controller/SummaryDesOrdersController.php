<?php
App::uses('AppController', 'Controller');

class SummaryDesOrdersController extends AppController {

	public $components = array('ActionsDesOrder');

	public function beforeFilter() {
		
		parent::beforeFilter();
		 
		/* ctrl ACL */
   		if($this->user->organization['Organization']['hasDes']=='N' || !$this->isDes()) {
   			$this->Session->setFlash(__('msg_not_organization_config'));
   			$this->myRedirect(Configure::read('routes_msg_stop'));
   		}
		/* ctrl ACL */		
	}

	public function admin_index($des_order_id) {
		
		$debug = false;

		/*
		 * ctrl ACL
		 */
		$isTitolareDesSupplier = $this->ActionsDesOrder->isTitolareDesSupplier($this->user, $des_order_id);
		if(!$isTitolareDesSupplier) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));				
		}
				
	   /*
	    *  tutti i dati del DesOrder
		*/
	    App::import('Model', 'DesOrder');
	    $DesOrder = new DesOrder;
	
		$desOrdersResults = $DesOrder->getDesOrder($this->user, $des_order_id);
		/*
		echo "<pre>";
		print_r($desOrdersResults);
		echo "</pre>";	
		*/
		$this->set(compact('desOrdersResults'));		

		$this->set(compact('des_order_id'));
	}
	
	public function admin_setImporto($row_id, $id, $importo=0) {
		if($row_id==null || $id==null) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		$esito = false;
	
		App::import('Model', 'DesOrder');
		$DesOrder = new DesOrder();			 

		$options = array();
		$options['conditions'] = array('SummaryDesOrder.des_id' => $this->user->des_id,
									   'SummaryDesOrder.id' => $id
									);
		$options['recursive'] = -1;
		$results = $this->SummaryDesOrder->find('first', $options);
			
		if (empty($results)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		$results['SummaryDesOrder']['importo'] = $this->importoToDatabase($importo);
		if ($this->SummaryDesOrder->save($results))
			$esito = true;
		else
			$esito = false;
	
		if ($esito)
			$content_for_layout = '<script type="text/javascript">managementCart(\''.$row_id.'\',\'OKIMPORTO\','.$id.',null);</script>';
		else
			$content_for_layout = '<script type="text/javascript">managementCart(\''.$row_id.'\',\'NO\','.$id.',null);</script>';
			
		$this->set('content_for_layout',$content_for_layout);
	
		$this->layout = 'ajax';
		$this->render('/Layouts/ajax');
	}

	public function admin_setNota($row_id, $id) {
		
		if($row_id==null || $id==null) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		$esito = false;

		if ($this->request->is('post') || $this->request->is('put')) {
	
			App::import('Model', 'DesOrder');
			$DesOrder = new DesOrder();			 
	
			$options = array();
			$options['conditions'] = array('SummaryDesOrder.des_id' => $this->user->des_id,
										   'SummaryDesOrder.id' => $id
										);
			$options['recursive'] = -1;
			$results = $this->SummaryDesOrder->find('first', $options);
				
			if (empty($results)) {
				$this->Session->setFlash(__('msg_error_params'));
				$this->myRedirect(Configure::read('routes_msg_exclamation'));
			}
		
			$results['SummaryDesOrder']['nota'] = $this->request->data['nota'];
			if ($this->SummaryDesOrder->save($results))
				$esito = true;
			else
				$esito = false;
		}
		
		if ($esito)
			$content_for_layout = '<script type="text/javascript">managementCart(\''.$row_id.'\',\'OKIMPORTO\','.$id.',null);</script>';
		else
			$content_for_layout = '<script type="text/javascript">managementCart(\''.$row_id.'\',\'NO\','.$id.',null);</script>';
			
		$this->set('content_for_layout',$content_for_layout);
	
		$this->layout = 'ajax';
		$this->render('/Layouts/ajax');
	}
	
}