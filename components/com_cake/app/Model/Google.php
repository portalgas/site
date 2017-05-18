<?php
App::uses('AppModel', 'Model');
App::uses('TimeHelper', 'View/Helper');
require_once ('/var/www/portalgas'. DS . 'google'. DS .'autoload.php');

/*
 *
 */
class Google extends AppModel {

	public $useTable = false;
	private $timeHelper;
	
	/*
	 * Per ottenere l'ora locale italiana:
	 * - in regime di ora solare (in pratica tra l'ultima domenica di ottobre e l'ultimo sabato del marzo successivo) occorre aggiungere all'orario UTC un'ora. 
	 * - in regime di ora legale (tra l'ultima domenica di marzo e l'ultimo sabato di ottobre) occorre aggiungere due ore all'ora UTC.
	 *  http://www.w3.org/TR/NOTE-datetime
	 */
	private function __getGTM() {
		
		// I - restituisce 1 se c'è l'ora legale, 0 se c'è quella solare.
		$isOraLegale = date("I");
		if($isOraLegale)
			$GTM = '+02:00'; // legale 
		else
			$GTM = '+01:00'; // solare

		return $GTM;	
	}
	
	public function __createClientGoogle($debug) {
		
		unset($_SESSION['access_token']);
		
		$client = new Google_Client();
		$client->setClientId(Configure::read('GoogleClient_id'));
		$client->setApplicationName("portalgas");

		if($debug) {
			echo "<pre>__createClientGoogle() \n";
			print_r($client);
			echo "<pre>";
		}
		
		return $client;
	} 
			
	public function __createServiceCalendarGoogle($client, $debug) {

		$service = null;
	
		try {
			$service_account_name = Configure::read('GoogleService_client_id');  // GoogleService_client_id  GoogleService_email  
			$key_file_location = Configure::read('App.root'). DS . Configure::read('GooglePrivateKeyLocation');
			$privateKey = file_get_contents($key_file_location);	
			
			if($debug) echo '<br />__createServiceCalendarGoogle() - GooglePrivateKeyLocation '.$key_file_location;
			if($debug) echo '<br />__createServiceCalendarGoogle() - service_account_name '.$service_account_name;
			
			$scopes = array(
				'https://www.googleapis.com/auth/calendar',
				'https://www.googleapis.com/auth/calendar.readonly'
			);

			$auth_credentials = new Google_Auth_AssertionCredentials($service_account_name, $scopes,  $privateKey);
			// $auth_credentials->sub = Configure::read('GoogleEmailGmail');  Unauthorized client or scope in request
			$auth_credentials->create_delegated = Configure::read('GoogleEmailGmail');

			$client->setAssertionCredentials($auth_credentials);
			if ($client->getAuth()->isAccessTokenExpired()) {
				$client->getAuth()->refreshTokenWithAssertion($auth_credentials);
			}
			
			$_SESSION['access_token'] = $client->getAccessToken();
			
			$service = new Google_Service_Calendar($client);
			
		} catch (Exception $e) {
			if($debug)
				echo "<br />__createServiceCalendarGoogle() ".$e;
			else {
				CakeLog::write("error", $e);
			}
		}
		
		return $service;
	}	

	/*
	 *  event gcalendar x notificare le consegne OPEN e non elaborate (Delivery.gcalendar_event_id null)
	 *  senza dettaglio produttori perche' non li ho ancora
	 */	
	public function usersDeliveryInsert($timeHelper, $organization_id, $debug) {
		
		try {
			
		$this->timeHelper = $timeHelper;
			
		echo date("d/m/Y")." - ".date("H:i:s")." Event gcalendar agli utenti con notifica consegna, organization_id $organization_id \n";
		
		$organization = $this->getOrganization($organization_id);
		if(empty($organization['Organization']['gcalendar_id'])) {
			echo "Organization $organization_id non ha valorizzato gcalendar_id \n";
			return;
		}
		
		$gcalendar_id = $organization['Organization']['gcalendar_id'];
		echo "\r".$gcalendar_id;
			
		$j_seo = $organization['Organization']['j_seo'];
			
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		App::import('Model', 'User');
		$User = new User;
		
		/*
		 * estraggo le consegne OPEN non ancora elaborate (Delivery.gcalendar_event_id = null)
		 */
		$options = array();
		$options['conditions'] = array(
									  'Delivery.organization_id' => (int)$organization['Organization']['id'],
									  'Delivery.stato_elaborazione'=> 'OPEN',
									  'Delivery.sys'=> 'N',
									  'DATE(Delivery.data) >= CURDATE()',
									  'Delivery.gcalendar_event_id' => null);
		$options['recursive'] = -1;
		$deliveryResults = $Delivery->find('all', $options);
		
		if(!empty($deliveryResults)) {
		
			$client = $this->__createClientGoogle($debug);
			$service = $this->__createServiceCalendarGoogle($client, $debug);
			
			if(isset($service))
			foreach ($deliveryResults as $deliveryResult) {

				echo "\nElaboro consegna di ".$this->timeHelper->i18nFormat($deliveryResult['Delivery']['data'],"%A %e %B %Y")." a ".$deliveryResult['Delivery']['luogo']." (".$deliveryResult['Delivery']['id'].") \n";
				
				/*
				 *  testo descrizione
				 */
				$body_mail = "";
				if($organization['Organization']['id']==3)  // arcoiris
					$body_mail .= 'Oggi, dalle ore '.substr($deliveryResult['Delivery']['orario_da'], 0, 5).' alle '.substr($deliveryResult['Delivery']['orario_a'], 0, 5)." ci sara' la consegna in ".$deliveryResult['Delivery']['luogo'];
				else
					$body_mail .= 'Il giorno '.$this->timeHelper->i18nFormat($deliveryResult['Delivery']['data'],"%A %e %B %Y")." ci sara' la consegna in ".$deliveryResult['Delivery']['luogo'].", dalle ore ".substr($deliveryResult['Delivery']['orario_da'], 0, 5)." alle ".substr($deliveryResult['Delivery']['orario_a'], 0, 5);
				if(!empty($deliveryResult['Delivery']['nota'])) {
					$body_mail .= "\n\r";
					$body_mail .= $deliveryResult['Delivery']['nota'];
				}
										
				$body_mail .= "\n\r";
				$body_mail .= 'http://www.portalgas.it/home-'.$j_seo;
				$body_mail .= "\n\r";
				
				$event = new Google_Service_Calendar_Event();
				$body_mail = mb_convert_encoding($body_mail,'UTF-8','UTF-8'); // per evitare  json_encode(): Invalid UTF-8 sequence in argument
				$event->setDescription($body_mail);
				if($organization['Organization']['id']==3)  // arcoiris
					$event->setSummary("Consegna Prodotti Gas Arcoiris");
				else
					$event->setSummary("Consegna di ".$this->timeHelper->i18nFormat($deliveryResult['Delivery']['data'],"%A %e %B %Y")." a ".$deliveryResult['Delivery']['luogo']);					
				$event->setLocation($organization['Organization']['localita']);
				$start = new Google_Service_Calendar_EventDateTime();
				$startDateTime = $deliveryResult['Delivery']['data'].'T'.$deliveryResult['Delivery']['orario_da'].$this->__getGTM(); 
				echo "\nstartDateTime: ".$startDateTime;
				$start->setDateTime($startDateTime);
				//$start->setDate($deliveryResult['Delivery']['data']);
				$event->setStart($start);
				
				$end = new Google_Service_Calendar_EventDateTime();
				// $end->setDateTime('2011-06-03T10:25:00.000-07:00');
				$endDateTime = $deliveryResult['Delivery']['data'].'T'.$deliveryResult['Delivery']['orario_a'].$this->__getGTM();
				echo "\nendDateTime: ".$endDateTime;
				$end->setDateTime($endDateTime);
				//$end->setDate($deliveryResult['Delivery']['data']);
				$event->setEnd($end);
						
				$createdEvent = $service->events->insert($gcalendar_id, $event);  // 'primary'

				// echo "\neventId ".$createdEvent->getId();
				$sql ="UPDATE
							".Configure::read('DB.prefix')."deliveries as Delivery
					   SET
							gcalendar_event_id = '".$createdEvent->getId()."',
							modified = '".date('Y-m-d H:i:s')."'
					   WHERE
							organization_id = ".(int)$organization_id."
							and Delivery.id = ".$deliveryResult['Delivery']['id'];
				echo "\n".$sql;
				$Delivery->query($sql);
				
			} // end foreach ($deliveryResults as $deliveryResult) 
		}
		else
			echo "\nnon ci sono consegne OPEN e non ancora elaborate (Delivery.gcalendar_event_id = null) \n";
		
		} catch (Exception $e) {
			if($debug)
				echo "<br />usersDeliveryInsert() ".$e;
			else
				CakeLog::write("error", $e);
		}		
	}

	/*
	 *  event gcalendar x aggiornare la consegna 
	 *		(prima di Configure::read('GGEventGCalendarToAlertDeliveryOn') gg dall'apertura)
	 *		Delivery.gcalendar_event_id IS NOT null
	 *  con dettaglio produttori
	 */	
	public function usersDeliveryUpdate($timeHelper, $organization_id, $debug) {
		
		try {
		
		$this->timeHelper = $timeHelper;
		
		echo date("d/m/Y")." - ".date("H:i:s")." Event gcalendar UPDATE con dettaglio consegna \n";
		$organization = $this->getOrganization($organization_id);
		
		if(empty($organization['Organization']['gcalendar_id'])) {
			echo "Organization non ha valorizzato gcalendar_id \n";
			return;
		}
		
		$gcalendar_id = $organization['Organization']['gcalendar_id'];
		echo "\r".$gcalendar_id;
			
		$j_seo = $organization['Organization']['j_seo'];
			
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		App::import('Model', 'Order');
		
		App::import('Model', 'Supplier');
		
		App::import('Model', 'User');
		$User = new User;

		/*
		 * estraggo le consegne che si apriranno domani
		 */
		$options = array();
		$options['conditions'] = array(
									  'Delivery.organization_id' => (int)$organization['Organization']['id'],
									  'DATE(Delivery.data) = CURDATE() + INTERVAL '.Configure::read('GGEventGCalendarToAlertDeliveryOn').' DAY ', 
									  'Delivery.sys'=> 'N',
									  'DATE(Delivery.data) >= CURDATE()',
									  'Delivery.gcalendar_event_id IS NOT null');
		$options['recursive'] = -1;
		$deliveryResults = $Delivery->find('all', $options);
	
		if(!empty($deliveryResults)) {
		
			$client = $this->__createClientGoogle($debug);
			$service = $this->__createServiceCalendarGoogle($client, $debug);
		
			foreach ($deliveryResults as $deliveryResult) {
				
				echo "\nElaboro consegna (".$deliveryResult['Delivery']['id'].") di ".$this->timeHelper->i18nFormat($deliveryResult['Delivery']['data'],"%A %e %B %Y")." a ".$deliveryResult['Delivery']['luogo']." con eventId ".$deliveryResult['Delivery']['gcalendar_event_id']."\n";
				
				/*
				 * estraggo gli ordini associati alla consegna
				*/
				
				$Order = new Order;
				
				$Order->unbindModel(array('belongsTo' => array('Delivery')));
				$options = array();
				$options['conditions'] = array('Order.delivery_id' => $deliveryResult['Delivery']['id'],
											'Order.organization_id' => (int)$organization['Organization']['id'],
											'Order.isVisibleBackOffice' => 'Y',
											'Order.state_code !=' => 'CREATE-INCOMPLETE');
				$options['recursive'] = 0;
				$options['fields'] = array('SuppliersOrganization.name', 'SuppliersOrganization.frequenza', 'SuppliersOrganization.supplier_id');
				$options['order'] = array('SuppliersOrganization.name');
				$orderResults = $Order->find('all', $options);
				echo "\n trovati ".count($orderResults)." orders \n";
				if(empty($orderResults))
					return;
				
				$tmpProduttori = "";
				foreach ($orderResults as $numResult => $orderResult) {
					
					echo "\nElaboro ordine del produttore ".$orderResult['SuppliersOrganization']['name']." \n";
					
					/*
					 * Suppliers per l'immagine
					* */
					$Supplier = new Supplier;
						
					$options = array();
					$options['conditions'] = array('Supplier.id' => $orderResult['SuppliersOrganization']['supplier_id']);
					$options['fields'] = array('Supplier.descrizione', 'Supplier.img1');
					$options['recursive'] = -1;
					$SupplierResults = $Supplier->find('first', $options);
					
					$tmpProduttori .= "\n\r";
					$tmpProduttori .= ($numResult+1).') ';

					$tmpProduttori .= $orderResult['SuppliersOrganization']['name'];
					if(!empty($SupplierResults['Supplier']['descrizione'])) $tmpProduttori .= ' ('.$SupplierResults['Supplier']['descrizione'].')';
					if(!empty($SupplierResults['SuppliersOrganization']['frequenza'])) $tmpProduttori .= ' Frequenza '.$orderResult['SuppliersOrganization']['frequenza'];	
				} // end foreach ($orderResults as $orderResult)
				
				/*
				 *  testo descrizione
				 */
				$body_mail = "";
				if($organization['Organization']['id']==3)  // arcoiris
					$body_mail .= 'Oggi, dalle ore '.substr($deliveryResult['Delivery']['orario_da'], 0, 5).' alle '.substr($deliveryResult['Delivery']['orario_a'], 0, 5)." ci sara' la consegna in ".$deliveryResult['Delivery']['luogo'];
				else	
					$body_mail .= 'Il giorno '.$this->timeHelper->i18nFormat($deliveryResult['Delivery']['data'],"%A %e %B %Y")." ci sara' la consegna in ".$deliveryResult['Delivery']['luogo'].", dalle ore ".substr($deliveryResult['Delivery']['orario_da'], 0, 5)." alle ".substr($deliveryResult['Delivery']['orario_a'], 0, 5);
				if(!empty($deliveryResult['Delivery']['nota'])) {
					$body_mail .= "\n\r";
					$body_mail .= $deliveryResult['Delivery']['nota'];
				}
				
				$body_mail .= "\n\r";
				$body_mail .= 'Elenco dei produttori presenti alla consegna:';
				$body_mail .= $tmpProduttori;
										
				$body_mail .= "\n\r";
				$body_mail .= 'http://www.portalgas.it/home-'.$j_seo;

				
				$event = new Google_Service_Calendar_Event();
				$body_mail = mb_convert_encoding($body_mail,'UTF-8','UTF-8'); // per evitare  json_encode(): Invalid UTF-8 sequence in argument
				$event->setDescription($body_mail);
				if($organization['Organization']['id']==3)  // arcoiris
					$event->setSummary("Consegna Prodotti Gas Arcoiris");
				else
					$event->setSummary("Consegna di ".$this->timeHelper->i18nFormat($deliveryResult['Delivery']['data'],"%A %e %B %Y")." a ".$deliveryResult['Delivery']['luogo']);
				$event->setLocation($organization['Organization']['localita']);
				
				$start = new Google_Service_Calendar_EventDateTime();
				$startDateTime = $deliveryResult['Delivery']['data'].'T'.$deliveryResult['Delivery']['orario_da'].$this->__getGTM();  
				echo "\nstartDateTime: ".$startDateTime;
				$start->setDateTime($startDateTime);;
				//$start->setDate($deliveryResult['Delivery']['data']);
				$event->setStart($start);
				
				$end = new Google_Service_Calendar_EventDateTime();
				// $end->setDateTime('2011-06-03T10:25:00.000-07:00');
				$endDateTime = $deliveryResult['Delivery']['data'].'T'.$deliveryResult['Delivery']['orario_a'].$this->__getGTM();  
				echo "\nendDateTime: ".$endDateTime;
				$end->setDateTime($endDateTime);;
				//$end->setDate($deliveryResult['Delivery']['data']);
				$event->setEnd($end);

				
				$updatedEvent = $service->events->update($gcalendar_id, $deliveryResult['Delivery']['gcalendar_event_id'], $event);  // 'primary'
				
				echo "\nupdatedEvent->getUpdated ".$updatedEvent->getUpdated();
				
			} // end foreach ($deliveryResults as $deliveryResult) 
		}
		else
			echo "\nnon ci sono consegne che apriranno tra ".(Configure::read('GGEventGCalendarToAlertDeliveryOn')+1)." giorni e Delivery.gcalendar_event_id IS NOT null \n";
		
		} catch (Exception $e) {
			if($debug)
				echo "<br />usersDeliveryInsert() ".$e;
			else
				CakeLog::write("error", $e);
		}		
	}
}