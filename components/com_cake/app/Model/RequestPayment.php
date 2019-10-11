<?php
App::uses('AppModel', 'Model');


/*
 * DROP TRIGGER IF EXISTS `k_request_payments_Trigger`;
 * DELIMITER |
 * CREATE TRIGGER `k_request_payments_Trigger` AFTER DELETE ON `k_request_payments`
 *  FOR EACH ROW BEGIN
 * delete from k_summary_payments where request_payment_id = old.id and organization_id = old.organization_id;
 * delete from k_request_payments_generics where request_payment_id = old.id and organization_id = old.organization_id;
 * delete from k_request_payments_orders where request_payment_id = old.id and organization_id = old.organization_id;
 * delete from k_request_payments_storerooms where request_payment_id = old.id and organization_id = old.organization_id;
 * END
 * |
 * DELIMITER ;
 */

class RequestPayment extends AppModel {

	public  function getRequestPaymentByOrderId($user, $order_id, $debug=false) {
        
		App::import('Model', 'RequestPaymentsOrder');
        $RequestPaymentsOrder = new RequestPaymentsOrder();
		$RequestPaymentsOrder->unbindModel(['belongsTo' => ['Order']]);

		$options = [];
		$options['conditions'] = ['RequestPaymentsOrder.organization_id' => $user->organization['Organization']['id'],
								  'RequestPaymentsOrder.order_id' => $order_id];
		$options['recursive'] = 1;
		$requestPaymentsOrderResults = $RequestPaymentsOrder->find('first', $options);		
		self::d($requestPaymentsOrderResults, $debug);
		
		return $requestPaymentsOrderResults;
	}
	
	public  function getRequestPaymentIdByOrderId($user, $order_id, $debug=false) {
        
		$requestPaymentsOrderResults = $this->getRequestPaymentByOrderId($user, $order_id, $debug);		
		$request_payment_id = $requestPaymentsOrderResults['RequestPayment']['id'];

		return $request_payment_id;
	}
	
	public  function getRequestPaymentNumByOrderId($user, $order_id, $debug=false) {
        
		$requestPaymentsOrderResults = $this->getRequestPaymentByOrderId($user, $order_id, $debug);		
		$request_payment_num = $requestPaymentsOrderResults['RequestPayment']['num'];

		return $request_payment_num;
	}
	
	/*
	 * calcolare il totale dell'importo di una richiesta di pagamento
	 * 	- SUM(tot_importo) ordini associati
	 *  - SUM() voci di spesa generica
	 *  - SUM() dispensa
	*/
	public  function getTotImporto($user, $request_payment_id, $debug=false) {
		
		$tot_importo_orders = 0;
		$tot_importo_storerooms = 0;
		$tot_importo_generics = 0;
	
		try {
			/*
			 * REQUEST_PAYMENT_ORDER, ordini associati
			*/
			$sql = "SELECT
						SUM(`Order`.tot_importo) as tot_importo_orders 
					FROM
						".Configure::read('DB.prefix')."request_payments_orders as RequestPaymentsOrder,
						".Configure::read('DB.prefix')."orders as `Order`
					WHERE
						RequestPaymentsOrder.organization_id = ".(int)$user->organization['Organization']['id']."
					    and `Order`.organization_id = ".(int)$user->organization['Organization']['id']."
					    and RequestPaymentsOrder.order_id = `Order`.id
					    and `Order`.isVisibleBackOffice = 'Y'
					    and RequestPaymentsOrder.request_payment_id = ".$request_payment_id;
			self::d($sql, $debug);
			$results = $this->query($sql);
			$tot_importo_orders = $results[0][0]['tot_importo_orders'];
						
			/*
			 * ctrl configurazione Organization
			*/
			if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {

				App::import('Model', 'Storeroom');
				$Storeroom = new Storeroom;

				$storeroomUser = $Storeroom->getStoreroomUser($user);
							
				App::import('Model', 'RequestPaymentsStoreroom');
				$RequestPaymentsStoreroom = new RequestPaymentsStoreroom;
			
				$options = [];
				$options['conditions'] = ['RequestPaymentsStoreroom.organization_id' => $user->organization['Organization']['id'],
										  'RequestPaymentsStoreroom.request_payment_id' => $request_payment_id];
				$options['recursive'] = -1;
				$requestPaymentsStoreroomResults = $RequestPaymentsStoreroom->find('all', $options);
							
				foreach($requestPaymentsStoreroomResults as $requestPaymentsStoreroomResult) {
					
					
				
					$options = [];
					$options['conditions'] = ['Storeroom.organization_id' => (int)$user->organization['Organization']['id'],
										  'Storeroom.user_id != ' => $storeroomUser['User']['id'],
										  'Storeroom.delivery_id' => $requestPaymentsStoreroomResult['RequestPaymentsStoreroom']['delivery_id'],
										  'Storeroom.stato' => 'Y'];
					$options['recursive'] = -1;
					$storeroomResults = $Storeroom->find('all', $options);					
					foreach($storeroomResults as $storeroomResult) {
						$tot_importo_storerooms = ($tot_importo_storerooms + ($storeroomResult['Storeroom']['qta'] * $storeroomResult['Storeroom']['prezzo']));
					}
				}			
			}

			/*
			 * REQUEST_PAYMENT_GENERICS, voci generica
			*/
			$sql = "SELECT
						SUM(`RequestPaymentsOrdersGeneric`.importo) as tot_importo_generics  
					FROM
						".Configure::read('DB.prefix')."request_payments_generics as RequestPaymentsOrdersGeneric
					WHERE
						RequestPaymentsOrdersGeneric.organization_id = ".(int)$user->organization['Organization']['id']."
					    and RequestPaymentsOrdersGeneric.request_payment_id = ".$request_payment_id;
			self::d($sql, $debug);
			$results = $this->query($sql);
			$tot_importo_generics = $results[0][0]['tot_importo_generics'];
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}

		return ($tot_importo_orders + $tot_importo_storerooms + $tot_importo_generics);
	}
	
	/*
	 * estraggo i dettagli di una richiesta di pagamento
	 * 	- ordini associati
	 *  - voci di spesa generica
	 *  - dispensa
	 */
	public  function getAllDetails($user, $request_payment_id, $conditions=[], $debug=false) {

		$results = [];

		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
			
		App::import('Model', 'SuppliersOrganizationsReferent');
		$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
			
		try {
			/*
			 * REQUEST_PAYMENT
			*/
			$conditionsLocal = ['RequestPayment.organization_id' => $user->organization['Organization']['id'],
							    'RequestPayment.id' => $request_payment_id];
			$requestResults = $this->find('first', array('conditions' => $conditionsLocal, 'recursive' => -1));
			$results = $requestResults;
			/*
			 * REQUEST_PAYMENT_ORDER, ordini associati
			 *
			*/
			$sql = "SELECT
						Delivery.id, Delivery.organization_id, Delivery.luogo, Delivery.data,
						`Order`.id, `Order`.supplier_organization_id, `Order`.tot_importo, `Order`.tesoriere_nota, `Order`.tesoriere_doc1, `Order`.tesoriere_fattura_importo, `Order`.state_code,  
						SuppliersOrganization.name, Supplier.img1, RequestPaymentsOrder.id
					FROM
						".Configure::read('DB.prefix')."request_payments_orders as RequestPaymentsOrder,
						".Configure::read('DB.prefix')."deliveries as Delivery,
						".Configure::read('DB.prefix')."orders as `Order`,
						".Configure::read('DB.prefix')."suppliers_organizations as SuppliersOrganization, 
						".Configure::read('DB.prefix')."suppliers as Supplier 
					WHERE
						RequestPaymentsOrder.organization_id = ".(int)$user->organization['Organization']['id']."
						and Delivery.organization_id = ".(int)$user->organization['Organization']['id']."
					    and `Order`.organization_id = ".(int)$user->organization['Organization']['id']."
					    and SuppliersOrganization.organization_id = ".(int)$user->organization['Organization']['id']."
					    and RequestPaymentsOrder.order_id = `Order`.id
					    and `Order`.delivery_id = Delivery.id
						and `Order`.supplier_organization_id = SuppliersOrganization.id
						and `Supplier`.id = SuppliersOrganization.supplier_id
					    and Delivery.isVisibleBackOffice = 'Y'
					    and `Order`.isVisibleBackOffice = 'Y'
					    and RequestPaymentsOrder.request_payment_id = ".$results['RequestPayment']['id']."
					ORDER BY
						Delivery.data asc, SuppliersOrganization.name
					";
			self::d($sql, $debug);
			$orderResults = $this->query($sql);
			$results['Order'] = $orderResults;
			
			/*
			 * Referenti
			* */
			foreach($orderResults as $numOrder => $result) {
				$conditionsLocal = array('SuppliersOrganizationsReferent.organization_id' => $user->organization['Organization']['id'],
						'SuppliersOrganizationsReferent.supplier_organization_id' => $result['Order']['supplier_organization_id']);
			
				$ReferentResults = $SuppliersOrganizationsReferent->find('all', ['conditions'=> $conditionsLocal,'recursive'=>0]);
					
				$results['Order'][$numOrder]['Referenti'] = $ReferentResults;
			}
			
			
			/*
			 * SUMMARY_PAYMENT, importo (come somma degli importi di tutti gli ordini) che ogni utente deve pagare
			*/
			App::import('Model', 'SummaryPayment');
			$SummaryPayment = new SummaryPayment;
								
			$options['conditions'] = ['SummaryPayment.organization_id' => $user->organization['Organization']['id'],
									  'SummaryPayment.request_payment_id' => $results['RequestPayment']['id']];
			if(isset($conditions['SummaryPayment.stato'])) $options['conditions'] += ['SummaryPayment.stato' => $conditions['SummaryPayment.stato']];
			if(isset($conditions['User.name']))            $options['conditions'] += ['User.name' => $conditions['User.name']];
			$options['order'] = Configure::read('orderUser');
			$options['recursive'] = 1;
			$summaryPaymentResults = $SummaryPayment->find('all', $options);
			$results['SummaryPayment'] = $summaryPaymentResults;

			/*
			 * ctrl configurazione Organization
			*/
			$requestPaymentsStoreroomResults = [];
			if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
			
				/*
				 * ottento informazioni su eventuali RequestPaymentsStoreroom
				*/
				App::import('Model', 'Storeroom');
				$Storeroom = new Storeroom;

				$Storeroom->unbindModel(array('belongsTo' => array('Delivery')));
				$Storeroom->Delivery->unbindModel(array('hasMany' => array('Order', 'Delivery')));
				$Storeroom->Article->unbindModel(['hasMany' => ['ArticlesOrder']]);
				$Storeroom->User->unbindModel(array('hasMany' => array('Cart')));
				
				$storeroomUser = $Storeroom->getStoreroomUser($user);
							
				App::import('Model', 'RequestPaymentsStoreroom');
				$RequestPaymentsStoreroom = new RequestPaymentsStoreroom;
				
				$conditionsLocal = ['RequestPaymentsStoreroom.organization_id' => (int)$user->organization['Organization']['id'],
									'RequestPaymentsStoreroom.request_payment_id' => $results['RequestPayment']['id']];
				$requestPaymentsStoreroomResults = $RequestPaymentsStoreroom->find('all',array('conditions'=> $conditionsLocal, 'order'=>'Delivery.data ASC','recursive'=>1));
				foreach($requestPaymentsStoreroomResults as $numResult => $requestPaymentsStoreroomResult) {

					$delivery_id = $requestPaymentsStoreroomResult['Delivery']['id'];
						
					$options = [];
					$options['conditions'] = ['Storeroom.organization_id' => (int)$user->organization['Organization']['id'],
											  'Storeroom.user_id != ' => $storeroomUser['User']['id'],
											  'Storeroom.delivery_id' => $delivery_id,
											  'Storeroom.stato' => 'Y'];
			        $options['order'] = [Configure::read('orderUser'), 'Article.supplier_organization_id', 'Storeroom.name'];
			        $options['recursive'] = 1;
					$storeroomResults = $Storeroom->find('all', $options);
					self::d($conditions, $debug);
					self::d($storeroomResults, $debug);
										
					/*
					 * aggiungo informazione sul produttore
					 */
					if(!empty($storeroomResults)) {
						foreach ($storeroomResults as $numResult2 => $storeroomResult) {
						// self::dd($storeroomResult['Article'], $debug);
							$conditionsLocal = ['SuppliersOrganization.id' => $storeroomResult['Article']['supplier_organization_id']];
							$tmpUser->organization['Organization']['id'] = $storeroomResult['Article']['organization_id'];
							$suppliersOrganization = $SuppliersOrganization->getSuppliersOrganization($tmpUser, $conditionsLocal);
							$storeroomResults[$numResult2]['SuppliersOrganization'] = current($suppliersOrganization);
						}
					}

					$results['Storeroom'][$numResult] = $requestPaymentsStoreroomResult;
					$results['Storeroom'][$numResult]['Storeroom'] = $storeroomResults;
									
				}
			}
			
			/*
			 * ottento informazioni su eventuali RequestPaymentsGeneric
			*/
			App::import('Model', 'RequestPaymentsGeneric');
			$RequestPaymentsGeneric = new RequestPaymentsGeneric;
			
			$conditionsLocal = [];
			$conditionsLocal['conditions'] = ['RequestPaymentsGeneric.organization_id' => (int)$user->organization['Organization']['id'],
									 		  'RequestPaymentsGeneric.request_payment_id' => $results['RequestPayment']['id']];
			$conditionsLocal['order'] = ['RequestPaymentsGeneric.created' => 'ASC'];
			$conditionsLocal['recursive'] = 1;
			$requestPaymentsGenericResults = $RequestPaymentsGeneric->find('all', $conditionsLocal);
			
			App::import('Model', 'User');
			foreach ($requestPaymentsGenericResults as $numResult => $requestPaymentsGenericResult) {
				$User = new User;

				$conditions = [];
				$conditions['conditions'] = ['User.organization_id' => (int)$user->organization['Organization']['id'],
										  	'User.id' => $requestPaymentsGenericResult['RequestPaymentsGeneric']['user_id']];
				$conditions['fields'] = ['User.name', 'User.username', 'User.email'];
				$conditions['recursive'] = -1;
				$userResults = $User->find('first', $conditions);
				
				$requestPaymentsGenericResults[$numResult]['User'] = $userResults['User'];
				
			}
			$results['PaymentsGeneric'] = $requestPaymentsGenericResults;		
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}
					
		return $results;
	}
	
	/*
	 * call Ajax::admin_view_user_all_modify() per /ExportDocs/tesoriere_to_users_all_modify.ctp - anteprima da RequestPayment/admin_edit_open.ctp ...
	 * call ExportDoc::admin_userRequestPayment($request_payment_id, $doc_formato) 				 - pdf per l'utente lato backoffice
	 * call ExportDoc::userRequestPayment($request_payment_id, $doc_formato)                     - pdf per l'utente lato frontend
	 * */
	public function userRequestPayment($user, $user_id, $request_payment_id, $doc_formato, $debug=false) {
		
		$results = [];
		try {
			App::import('Model', 'SummaryPayment');
			App::import('Model', 'Delivery');
			
			/*
			 * R E Q U E S T P A Y M E N T S - O R D E R 
			 * ottengo gli ordini legati alla richiesta di pagamento
			 *
			 * associo anche SummaryOrder per verificare che non sia stato gia' pagato SummaryOrder.saldato_a CASSIERE / TESORIERE
			 */
			$sql = "SELECT 
						`Order`.id, `Order`.delivery_id,
						`Order`.hasTrasport, `Order`.trasport, `Order`.hasCostMore, `Order`.cost_more, `Order`.hasCostLess, `Order`.cost_less 
					FROM 
						".Configure::read('DB.prefix')."summary_orders as SummaryOrder,
						".Configure::read('DB.prefix')."request_payments_orders as RequestPaymentsOrder, 
						".Configure::read('DB.prefix')."orders as `Order`  
					WHERE 
						RequestPaymentsOrder.organization_id = ".(int)$user->organization['Organization']['id']." 
					    and `Order`.organization_id = ".(int)$user->organization['Organization']['id']." 
						and SummaryOrder.organization_id = ".(int)$user->organization['Organization']['id']."
					    and RequestPaymentsOrder.order_id = `Order`.id
						and SummaryOrder.order_id = `Order`.id  
						and SummaryOrder.user_id = ".$user_id." 
					    and RequestPaymentsOrder.request_payment_id = ".$request_payment_id."
					ORDER BY 
						 `Order`.delivery_id, `Order`.id ";
			self::d($sql, $debug);
			$results['RequestPaymentsOrder'] = $this->query($sql);
			/*
			echo "<pre>";
			print_r($results);
			echo "</pre>";
			*/
			if(!empty($results['RequestPaymentsOrder'])) {
				
				App::import('Model', 'ExportDoc');
				App::import('Model', 'SummaryOrderAggregate');
				App::import('Model', 'SummaryOrderTrasport');
				App::import('Model', 'SummaryOrderCostMore');
				App::import('Model', 'SummaryOrderCostLess');
				
				foreach($results['RequestPaymentsOrder'] as $numResult => $result) {
					$delivery_id = $result['Order']['delivery_id'];
					$order_id = $result['Order']['id'];	

					/*
					 * ottengo tutti i dati degli acquisti
					*/
					$Delivery = new Delivery;
					
					$options = array('orders' => true, 'storerooms' => false, 'summaryOrders' => false,
							'articlesOrdersInOrderAndCartsAllUsers'=>true,  // estraggo SOLO gli articoli acquistati da TUTTI gli utente in base all'ordine
							'suppliers'=>true, 'referents'=>true);
						
					$conditionsLocal = array('Delivery' => array('Delivery.isVisibleBackOffice' => 'Y',
							'Delivery.id' => $delivery_id),
							'Order' => array('Order.isVisibleBackOffice' => 'Y',
											'Order.id' => $order_id),
							'User' => array('User.id' => $user_id),
							'Cart' => array('Cart.deleteToReferent' => 'N'));
					$orderBy = array('Article' => 'Article.name');
						
					$tmpResults = $Delivery->getDataWithoutTabs($user, $conditionsLocal, $options, $orderBy);
		

					$SummaryOrderAggregate = new SummaryOrderAggregate;
					$resultsSummaryOrderAggregate = $SummaryOrderAggregate->select_to_order($user, $order_id, $user_id);
						
					$resultsSummaryOrderTrasport = [];					
					if($result['Order']['hasTrasport']=='Y' && floatval($result['Order']['trasport']) > 0) {	
						$SummaryOrderTrasport = new SummaryOrderTrasport;
						$resultsSummaryOrderTrasport = $SummaryOrderTrasport->select_to_order($user, $order_id);
					}
					
					$resultsSummaryOrderCostMore = [];
					if($result['Order']['hasCostMore']=='Y' && floatval($result['Order']['cost_more']) > 0) {                    
						$SummaryOrderCostMore = new SummaryOrderCostMore;
						$resultsSummaryOrderCostMore = $SummaryOrderCostMore->select_to_order($user, $order_id);
                    }
                             
					$resultsSummaryOrderCostLess = [];
					if($result['Order']['hasCostLess']=='Y' && floatval($result['Order']['cost_less']) > 0) {
						$SummaryOrderCostLess = new SummaryOrderCostLess;
						$resultsSummaryOrderCostLess = $SummaryOrderCostLess->select_to_order($user, $order_id);
					}
					
					$ExportDoc = new ExportDoc;
					$cartCompileresults = $ExportDoc->getCartCompliteOrder($order_id, $tmpResults, $resultsSummaryOrderAggregate, $resultsSummaryOrderTrasport, $resultsSummaryOrderCostMore, $resultsSummaryOrderCostLess);
							
					$results['RequestPaymentsOrder'][$numResult] = $cartCompileresults;				
				} // end foreach($results['RequestPaymentsOrder'] as $numResult => $result)
				
				
/*
				$delivery_id_selected = '';
				$order_id_selected = '';
				foreach($results['RequestPaymentsOrder'] as $result) {
					$delivery_id_selected .= $result['Order']['delivery_id'].',';
					$order_id_selected .= $result['Order']['id'].',';
				}
				$delivery_id_selected = substr($delivery_id_selected , 0, strlen($delivery_id_selected)-1);
				$order_id_selected = substr($order_id_selected , 0, strlen($order_id_selected)-1);
*/
				/*
				 * ottengo tutti i dati degli acquisti  
				 */
/*				
				$Delivery = new Delivery;
				
				$options = array('orders' => true, 'storerooms' => false, 'summaryOrders' => false,
									'articlesOrdersInOrderAndCartsAllUsers'=>true,  // estraggo SOLO gli articoli acquistati da TUTTI gli utente in base all'ordine
									'suppliers'=>true, 'referents'=>true);
					
				$conditionsLocal = array('Delivery' => array('Delivery.isVisibleBackOffice' => 'Y',
														'Delivery.id IN ('.$delivery_id_selected.')'),
									'Order' => array('Order.isVisibleBackOffice' => 'Y',
													 'Order.id IN ('.$order_id_selected.')'),
									'User' => array('User.id' => $user_id),
									'Cart' => array('Cart.deleteToReferent' => 'N'));
				$orderBy = array('Article' => 'Article.name');
					
				$tmpResults = $Delivery->getDataWithoutTabs($user, $conditionsLocal, $options, $orderBy);
				
				$array_order_id = explode(',',$order_id_selected);
				foreach ($array_order_id as $numResult => $order_id) {
*/				

/*				
					App::import('Model', 'SummaryOrderAggregate');
					$SummaryOrderAggregate = new SummaryOrderAggregate;
					$resultsSummaryOrderAggregate = $SummaryOrderAggregate->select_to_order($user, $order_id, $user_id);
					
					App::import('Model', 'ExportDoc');
					$ExportDoc = new ExportDoc;
					$cartCompileresults[$numResult] = $ExportDoc->getCartCompliteOrder($order_id, $tmpResults, $resultsSummaryOrderAggregate, $resultsSummaryOrderTrasport);
				}
			
				$results['RequestPaymentsOrder'] = $cartCompileresults;
*/				

			} // end if(!empty($results)) 

	
			
			/*
	 		 * R E Q U E S T P A Y M E N T S - S T O R E R O O M
			 */
			if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
				$sql = "SELECT
							RequestPaymentsStoreroom.*,
							Storeroom.name, Storeroom.qta, Storeroom.prezzo, Storeroom.article_id,
							Article.qta, Article.um, Article.um_riferimento, Article.prezzo     
						FROM
							".Configure::read('DB.prefix')."request_payments_storerooms as RequestPaymentsStoreroom,
							".Configure::read('DB.prefix')."summary_payments as SummaryPayment,
							".Configure::read('DB.prefix')."storerooms as Storeroom,
							".Configure::read('DB.prefix')."articles as Article 
						WHERE
							RequestPaymentsStoreroom.organization_id = ".(int)$user->organization['Organization']['id']."
							and SummaryPayment.organization_id = ".(int)$user->organization['Organization']['id']."
							and Storeroom.organization_id = ".(int)$user->organization['Organization']['id']."
							and SummaryPayment.request_payment_id = RequestPaymentsStoreroom.request_payment_id
							and Storeroom.delivery_id = RequestPaymentsStoreroom.delivery_id
							and Storeroom.user_id = SummaryPayment.user_id
							and Storeroom.article_organization_id = Article.organization_id
							and Storeroom.article_id = Article.id
							and Storeroom.stato = 'Y' 
							and SummaryPayment.user_id = ".(int)$user_id." 
							and RequestPaymentsStoreroom.request_payment_id = ".(int)$request_payment_id;
				self::d($sql, $debug);
				$subResults = $this->query($sql);
				$results['RequestPaymentsStoreroom'] = $subResults;
			} // end if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y')
				
			/*
			 * R E Q U E S T P A Y M E N T S - G E N E R I C 
			 */
			$sql = "SELECT
						RequestPaymentsGeneric.*
					FROM
						".Configure::read('DB.prefix')."request_payments_generics as RequestPaymentsGeneric,
							".Configure::read('DB.prefix')."summary_payments as SummaryPayments 
					WHERE
						RequestPaymentsGeneric.organization_id = ".(int)$user->organization['Organization']['id']."
						and SummaryPayments.organization_id = ".(int)$user->organization['Organization']['id']."
						and SummaryPayments.request_payment_id = RequestPaymentsGeneric.request_payment_id
						and SummaryPayments.user_id = ".(int)$user_id."
						and RequestPaymentsGeneric.user_id = ".(int)$user_id." 
						and RequestPaymentsGeneric.request_payment_id = ".(int)$request_payment_id;
			self::d($sql, $debug);
			$subResults = $this->query($sql);
			$results['RequestPaymentsGeneric'] = $subResults;
			
			/*
			 * S U M M A R Y - P A Y M E N T S
			* ottengo i dati della richiesta di pagamento
			*/
			$SummaryPayment = new SummaryPayment;
			$options['conditions'] = array('SummaryPayment.organization_id' => $user->organization['Organization']['id'],
										   'SummaryPayment.request_payment_id' => $request_payment_id,
										   'SummaryPayment.user_id' => $user_id);
			$options['recursive'] = -1;
			$subResults = $SummaryPayment->find('first', $options);
			$results['SummaryPayment'] = $subResults['SummaryPayment']; 
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
			if($debug) echo $e;
		}
		if($debug) {
			echo "<pre>";
			print_r($results);
			echo "</pre>";
		}
		
		return $results;
	}
	
	public $validate = array(
		'organization_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'user_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	/*
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'User.organization_id = RequestPayment.organization_id',
			'fields' => '',
			'order' => ''
		)
	);
	*/

	public $belongsTo = array(
			'User' => array(
				'className' => 'User',
				'foreignKey' => 'user_id',
				'conditions' => 'User.organization_id = RequestPayment.organization_id',
				'fields' => '',
				'order' => ''
			),
	);
	
	public $hasMany = array(
			'RequestPaymentsOrder' => array(
					'className' => 'RequestPaymentsOrder',
					'foreignKey' => 'request_payment_id',
					'dependent' => false,
					'conditions' => '',
					'fields' => '',
					'order' => '',
					'limit' => '',
					'offset' => '',
					'exclusive' => '',
					'finderQuery' => '',
					'counterQuery' => ''
			),
			'SummaryPayment' => array(
					'className' => 'SummaryPayment',
					'foreignKey' => 'request_payment_id',
					'dependent' => false,
					'conditions' => '',
					'fields' => '',
					'order' => '',
					'limit' => '',
					'offset' => '',
					'exclusive' => '',
					'finderQuery' => '',
					'counterQuery' => ''
			)
	);	
}