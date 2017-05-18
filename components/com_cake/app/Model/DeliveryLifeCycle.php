<?php
App::uses('AppModel', 'Model');

class DeliveryLifeCycle extends AppModel {

	public $useTable = 'deliveries';
	
	/**
	 * hasMany associations
	 *
	 * @var array
	 */
	public $hasMany = array(
			'Order' => array(
					'className' => 'Order',
					'foreignKey' => 'delivery_id',
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

	/*
	 *  elimino le consegne 
	 *		- scadute DATE(Delivery.data) < CURDATE()
	 *		- senza ordini associati
	 */	
	public function deliveriesExpiredWithoutOrdersDelete($user, $delivery_id=0, $debug) {
	
        try {	
            $sql = "SELECT
						Delivery.id, count(`Order`.id) as tot_order
				   FROM
						 " . Configure::read('DB.prefix') . "deliveries Delivery
						LEFT JOIN " . Configure::read('DB.prefix') . "orders `Order` ON (`Order`.delivery_id = Delivery.id AND `Order`.organization_id = " . (int) $user->organization['Organization']['id'] . ")
				   WHERE
						Delivery.organization_id = " . (int) $user->organization['Organization']['id'] . "
						AND Delivery.sys = 'N' 
						and Delivery.isVisibleFrontEnd = 'Y' and Delivery.isVisibleFrontEnd = 'Y' 
						and DATE(Delivery.data) < CURDATE() ";
            if (!empty($delivery_id))
                $sql .= " AND Delivery.id = " . (int) $delivery_id;
            $sql .= " GROUP BY Delivery.id 
					  ORDER BY Delivery.id ";
            // if($debug) echo $sql."\n";
            $results = $this->query($sql);
            if ($debug)
                echo "Estratte " . count($results) . " consegne SCADUTE => elimino quelle SENZA Ordini \n";
			foreach ($results as $result) {
				if($result[0]['tot_order']==0) { 
					$sql = "DELETE FROM " . Configure::read('DB.prefix') . "deliveries 
							WHERE organization_id = " . (int) $user->organization['Organization']['id'] . " 
							AND id = ".$result['Delivery']['id'];
					if ($debug) echo "CANCELLO la consegna ".$result['Delivery']['id']." con ".$result[0]['tot_order']." ordini $sql \n";				
					$this->query($sql);
				}
				else {
					if ($debug) echo "ESCLUDO la consegna ".$result['Delivery']['id']." con ".$result[0]['tot_order']." ordini \n";
				}
			}
        } catch (Exception $e) {
            if ($debug)
                echo '<br />DeliveryLifeCycle::deliveriesExpiredWithoutOrdersDelete()<br />' . $e;
        }
    }				
    
    public function deliveriesToClose($user, $delivery_id=0, $debug) {

        if ($debug) {
            echo "\n".date("d/m/Y") . " - " . date("H:i:s") . " Porto le consegne a Delivery.stato_elaborazione = CLOSE con ";
            echo "tutti gli ordini in stato_elaborazione = CLOSE ";
            if ($user->organization['Organization']['hasUserGroupsTesoriere'] == 'Y')
                echo "e Order.tesoriere_stato_pay = Y \n";
            else
                echo " \n";
            if($user->organization['Organization']['payToDelivery']=='POST' || $user->organization['Organization']['payToDelivery']=='ON-POST')
                echo "e RequestPayment.stato_elaborazione = CLOSE  \n";    
            if($user->organization['Organization']['hasStoreroom'] == 'Y' && $user->organization['Organization']['hasStoreroomFrontEnd'] == 'Y')
                echo "e isToStoreroomPay = Y \n";
        }

        try {			
            /*
             * estraggo tutte le consegne aperte e quanti ordini ha associati
             */
            $sql = "SELECT
						Delivery.id, count(`Order`.id) as tot_order
				   FROM
						 " . Configure::read('DB.prefix') . "deliveries Delivery, 
						 `" . Configure::read('DB.prefix') . "orders` `Order` 
				   WHERE
						Delivery.organization_id = " . (int) $user->organization['Organization']['id'] . "
						AND `Order`.organization_id = " . (int) $user->organization['Organization']['id'] . "
						AND Delivery.stato_elaborazione = 'OPEN' 
						AND Delivery.sys = 'N' 
						AND `Order`.delivery_id = Delivery.id 
						and `Order`.isVisibleFrontEnd = 'Y'  and `Order`.isVisibleFrontEnd = 'Y' 
						and Delivery.isVisibleFrontEnd = 'Y' and Delivery.isVisibleFrontEnd = 'Y' ";
            if ($user->organization['Organization']['hasStoreroom'] == 'Y' && $user->organization['Organization']['hasStoreroomFrontEnd'] == 'Y')
                $sql .= " AND (Delivery.isToStoreroom = 'Y' && Delivery.isToStoreroomPay = 'Y' || Delivery.isToStoreroom = 'N') ";
            if (!empty($delivery_id))
                $sql .= " AND Delivery.id = " . (int) $delivery_id;
            $sql .= " GROUP BY Delivery.id 
					  ORDER BY Delivery.id ";
            // if($debug) echo $sql."\n";
            $results = $this->query($sql);
            if ($debug)
                echo "Estratte " . count($results) . " consegne OPEN\n\n";

            /*
             * ciclo tutte le consegne e ctrl che abbiamo tutti gli ordini 
             *		- con state_code = CLOSE
             * 		- RequestPayment.stato_elaborazione = CLOSE 
			 *      - se tutti gli utenti hanno pagato SummaryOrder.importo = SummaryOrder.importo_pagato li chiudo
             *		- Order.tesoriere_stato_pay = 'Y'
             */
            foreach ($results as $result) {

                $sql = "SELECT
						count(`Order`.id) as tot_order_close 
				   FROM
						 " . Configure::read('DB.prefix') . "deliveries Delivery,
						 `" . Configure::read('DB.prefix') . "orders` `Order`
				   WHERE
						Delivery.organization_id = " . (int) $user->organization['Organization']['id'] . "
						AND `Order`.organization_id = " . (int) $user->organization['Organization']['id'] . "
						AND Delivery.id = " . $result['Delivery']['id'] . "
						AND `Order`.delivery_id = Delivery.id
						AND `Order`.isVisibleFrontEnd = 'Y'  and `Order`.isVisibleFrontEnd = 'Y' 
						AND `Order`.state_code = 'CLOSE' ";
                if ($user->organization['Organization']['hasUserGroupsTesoriere'] == 'Y')
                    $sql .= " AND `Order`.tesoriere_stato_pay = 'Y' ";
                // if($debug) echo $sql."\n";
                $ordersResults = current($this->query($sql));

                if ($debug) {
                    echo "Per la consegna " . $result['Delivery']['id'] . " estratti " . $ordersResults[0]['tot_order_close'] . " ordini CLOSE ";
                    if ($user->organization['Organization']['hasUserGroupsTesoriere'] == 'Y')
                        echo "e Order.tesoriere_stato_pay = 'Y' ";

                    echo "su un totale " . $result[0]['tot_order'];
					
					if ($ordersResults[0]['tot_order_close'] == $result[0]['tot_order']) 
						echo " => potrei chiudere la consegna \n";
					else
						echo " => non potrei chiudere la consegna \n";
                }

				/*
				 * ctrl che le richieste di pagamento siano CLOSE
				 */
				if($user->organization['Organization']['payToDelivery']=='POST' || $user->organization['Organization']['payToDelivery']=='ON-POST') {
					$sql = "SELECT RequestPayment.id, RequestPayment.num, RequestPayment.stato_elaborazione FROM 
								" . Configure::read('DB.prefix') . "request_payments_orders as RequestPaymentsOrder, 
								" . Configure::read('DB.prefix') . "request_payments RequestPayment  
							WHERE
							RequestPaymentsOrder.organization_id = " . (int) $user->organization['Organization']['id'] . " 
							AND RequestPaymentsOrder.organization_id = RequestPayment.organization_id
							AND RequestPaymentsOrder.request_payment_id = RequestPayment.id
							AND RequestPayment.stato_elaborazione != 'CLOSE'
							AND RequestPaymentsOrder.delivery_id = " . $result['Delivery']['id'];	
			                // if($debug) echo $sql."\n";
			                $requestPaymentResults = $this->query($sql);	
							/*
							echo "<pre>";
							print_r($requestPaymentResults);
			                echo "</pre>";
							*/
							if(empty($requestPaymentClose)) {
			                	$requestPaymentClose=true;
								if($debug) echo "Nessun ordine e' legata ad una RICHIESTA DI PAGAMENTO chiusa => potrei chiudere la consegna \n";								
			                }
							else {
 			                	$requestPaymentClose=false;
								if($debug) echo "Alcuni ordini sono legati ad una RICHIESTA DI PAGAMENTO non chiusa => non potrei chiudere la consegna \n";
							}			
				}
				else
					$requestPaymentClose=true;
				
				
                /*
                 * per una consegna
                 * 	il totale degli ordini e' = al totale degli ordini chiudi 
                 */
                if ($ordersResults[0]['tot_order_close'] == $result[0]['tot_order'] && $requestPaymentClose) {
                    $sql = "UPDATE `" . Configure::read('DB.prefix') . "deliveries`
						   SET
								stato_elaborazione = 'CLOSE',
								modified = '" . date('Y-m-d H:i:s') . "'
						   WHERE
						   		organization_id = " . (int) $user->organization['Organization']['id'] . "
						   		and id = " . $result['Delivery']['id'];
                    if ($debug)
                        echo $sql . "\n";
                    $this->query($sql);

                    if ($debug)
                        echo "	per la consegna " . $result['Delivery']['id'] . " aggiorno lo stato a CLOSE \n";
                }
                else
                if ($debug)
                    echo "	per la consegna " . $result['Delivery']['id'] . " NON aggiorno lo stato a CLOSE \n \n";
            } // end foreach
	
 
        } catch (Exception $e) {
            if ($debug)
                echo '<br />DeliveryLifeCycle::deliveriesToClose()<br />' . $e;
        }
    }				
}