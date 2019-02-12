<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('AppController', 'Controller');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class MonitoringOrdersController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();
		
		if(!$this->isSuperReferente()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
	}

	public function admin_home() {
	
		$debug = false;
		
		App::import('Model', 'Order');
		$Order = new Order;
		
		$belongsTo = array(
						'className' => 'MonitoringOrder',
						'foreignKey' => '',
						'conditions' => array('MonitoringOrder.organization_id = Order.organization_id and MonitoringOrder.order_id = Order.id'),
						'fields' => '',
						'order' => '');
		
		$Order->bindModel(array('belongsTo' => array('MonitoringOrder' => $belongsTo)));
		
		$options = [];
		$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
									   'Order.state_code != ' => 'CLOSE',
									   'MonitoringOrder.user_id' => $this->user->id);
		$options['order'] = array('Delivery.data asc, Order.data_inizio');
		$options['recursive'] = 1;
		$results = $Order->find('all', $options);
		
		/*
		 *  aggiungo eventuali calcoli dei limiti sulla Order.qta_massima e Order.importo_massimo
		 */
		foreach ($results as $numResult => $result) {

			if(($result['Order']['state_code']=='OPEN' || $result['Order']['state_code']=='RI-OPEN-VALIDATE') && ($result['Order']['qta_massima']>0))
				$results[$numResult]['Order']['qta_massima_current'] = $Order->getTotQuantitaArticlesOrder($this->user, $result, $debug);
			else
				$results[$numResult]['Order']['qta_massima_current'] = 0;
		
		
			if(($result['Order']['state_code']=='OPEN' || $result['Order']['state_code']=='RI-OPEN-VALIDATE') && ($result['Order']['importo_massimo']>0))
				$results[$numResult]['Order']['importo_massimo_current'] = $Order->getTotImportoArticlesOrder($this->user, $result['Order']['id'], $debug);
			else
				$results[$numResult]['Order']['importo_massimo_current'] = 0;
		}

		self::d($results, false);
		
		$this->set(compact('results'));
	}
	
	public function admin_index() {
	
		$debug = false;
	
		if ($this->request->is('post') || $this->request->is('put')) {

			/*
			echo "<pre>";
			print_r($this->request->data['MonitoringOrder']);
			echo "</pre>";
			*/
			if(isset($this->request->data['MonitoringOrder']))  {   
				foreach($this->request->data['MonitoringOrder'] as $order_id => $data) {
					
					if($debug) {
						echo "<pre>";
						print_r($data);
						echo "</pre>";						
					}
					
					/*
					 * se diversi c'e' stato un cambiamento
					 */ 
					if($data['new'] != $data['old']) {
					
						if($data['old']=='true' && $data['new']=='false') {
							/*
							 * lo cancello
							 */
							 $this->MonitoringOrder->id = $data['monitoring_order_id'];
							 $this->MonitoringOrder->delete();
						}
						else
						if($data['old']=='false' && $data['new']=='true') {
							/*
							 * lo inserisco
							 */
							$row = [];
							$row['MonitoringOrder']['organization_id'] = $this->user->organization['Organization']['id'];
							$row['MonitoringOrder']['order_id'] = $order_id;
							$row['MonitoringOrder']['user_id'] = $this->user->id;
	
							if($debug) {
								echo "<pre>";
								print_r($row);
								echo "</pre>";						
							}						
							$this->MonitoringOrder->create();
							$this->MonitoringOrder->save($row); 
							
						}
					} // if($data['new'] != $data['old'])
	
				} // end foreach($this->request->data['Monitoring'] as $order_id => $data)
			
				$this->Session->setFlash(__('MonitoringOrders has been saved'));
				
			} // end if(isset($this->request->data['MonitoringOrder'])) 
						
		} // end if ($this->request->is('post') || $this->request->is('put')) 
	
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
	
		$options = [];
		$options['conditions'] = array('Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
										'Delivery.isVisibleBackOffice' => 'Y',
										'Delivery.sys'=> 'N',
										'Delivery.stato_elaborazione' => 'OPEN');
		$options['fields'] = array('id', 'luogoData');
		$options['order'] = 'data ASC';
		$options['recursive'] = -1;
		$deliveries = $Delivery->find('list', $options);
		
		/*
		 * ctrl se inserire anche la consegna Da definire, ctrl se ha ordini
		 */
		$options = [];
		$options['conditions'] = array('Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
										'Delivery.isVisibleBackOffice' => 'Y',
										'Delivery.sys' => 'Y');
		$options['order'] = array('data ASC');
		//$options['fields'] = array('id', 'luogoData');
		$options['recursive'] = 1;					
		$deliveriesSys = $Delivery->find('all', $options);
		if(!empty($deliveriesSys[0]['Order'])) {
			$deliveries[$deliveriesSys[0]['Delivery']['id']] = $deliveriesSys[0]['Delivery']['luogo'];
		}		 
		
		if(empty($deliveries)) {
			$this->Session->setFlash(__('NotFoundDeliveries'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$this->set(compact('deliveries'));
	}
	
	/*
	 * estraggo tutti gli ordini e le eventuali associazioni con MonitoringOrders
	 */
	public function admin_orders_index($delivery_id=0) {
	
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		App::import('Model', 'SuppliersOrganization');
			
		$Delivery->id = $this->delivery_id;
		if (!$Delivery->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		App::import('Model', 'Order');
		$Order = new Order;
		
		$belongsTo = array(
						'className' => 'MonitoringOrder',
						'foreignKey' => '',
						'conditions' => array('MonitoringOrder.organization_id' => $this->user->organization['Organization']['id'],
											  'MonitoringOrder.order_id = Order.id',
											  'MonitoringOrder.user_id' => $this->user->id),
						'fields' => '',
						'order' => '');
		
		$Order->bindModel(array('belongsTo' => array('MonitoringOrder' => $belongsTo)));
		
		$options = [];
		$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
										'Order.delivery_id' => $delivery_id);
		$options['order'] = array('Delivery.data asc, Order.data_inizio');
		$options['recursive'] = 1;
		$results = $Order->find('all', $options);
		
		self::d($results, false);
		
		$this->set('results', $results);
		
		$this->layout = 'ajax';			
	}
}