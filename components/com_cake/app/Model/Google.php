<?php
App::uses('AppModel', 'Model');
App::uses('TimeHelper', 'View/Helper');

require_once ('/var/www/portalgas'. DS . 'google'. DS .'autoload.php');

class Google extends AppModel {

	public $useTable = false;
	private $timeHelper;
	
	/*
	 * Per ottenere l'ora locale italiana:
	 * - in regime di ora solare (in pratica tra l'ultima domenica di ottobre e l'ultimo sabato del marzo successivo) occorre aggiungere all'orario UTC un'ora. 
	 * - in regime di ora legale (tra l'ultima domenica di marzo e l'ultimo sabato di ottobre) occorre aggiungere due ore all'ora UTC.
	 *  http://www.w3.org/TR/NOTE-datetime
	 */
	private function _getGTM() {
		
		// I - restituisce 1 se c'è l'ora legale, 0 se c'è quella solare.
		$isOraLegale = date("I");
		if($isOraLegale)
			$GTM = '+02:00'; // legale 
		else
			$GTM = '+01:00'; // solare

		return $GTM;	
	}
	
	public function _createClientGoogle($debug) {
		
		unset($_SESSION['access_token']);
		
		$client = new Google_Client();
		$client->setClientId(Configure::read('GoogleClient_id'));
		$client->setApplicationName("portalgas");

		if($debug) {
			echo "<pre>_createClientGoogle() \n";
			print_r($client);
			echo "<pre>";
		}
		
		return $client;
	} 
			
	public function _createServiceCalendarGoogle($client, $debug) {

		$service = null;
	
		try {
			$service_account_name = Configure::read('GoogleService_client_id');  // GoogleService_client_id  GoogleService_email  
			$key_file_location = Configure::read('App.root'). DS . Configure::read('GooglePrivateKeyLocation');
			$privateKey = file_get_contents($key_file_location);	
			
			if($debug) echo '<br />_createServiceCalendarGoogle() - GooglePrivateKeyLocation '.$key_file_location;
			if($debug) echo '<br />_createServiceCalendarGoogle() - service_account_name '.$service_account_name;
			
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
				echo "<br />_createServiceCalendarGoogle() ".$e;
			else {
				CakeLog::write("error", $e);
			}
		}
		
		return $service;
	}	

	/*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     *  event gcalendar x notificare le consegne OPEN e non elaborate (Delivery.gcalendar_event_id null)
     *  senza dettaglio produttori perche' non li ho ancora
     */
	public function usersDeliveryInsert($user, $timeHelper, $debug) {
		
		try {
			
		$this->timeHelper = $timeHelper;
			
		echo date("d/m/Y")." - ".date("H:i:s")." Event gcalendar agli utenti con notifica consegna, organization_id ".$user->organization['Organization']['id']." \n";
		
		$gcalendar_id = $user->organization['Organization']['gcalendar_id'];
		echo "\r".$gcalendar_id;
		
		if(empty($gcalendar_id)) {
			echo "Organization ".$user->organization['Organization']['id']." non ha valorizzato gcalendar_id [".$user->organization['Organization']['gcalendar_id']."] \n";
			return;
		}
					
		$j_seo = $user->organization['Organization']['j_seo'];
			
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		App::import('Model', 'User');
		$User = new User;
		
		/*
		 * estraggo le consegne OPEN non ancora elaborate (Delivery.gcalendar_event_id = null)
		 */
		$options = [];
		$options['conditions'] = ['Delivery.organization_id' => (int)$user->organization['Organization']['id'],
								  'Delivery.stato_elaborazione'=> 'OPEN',
								  'Delivery.sys'=> 'N',
								  'Delivery.type' => 'GAS',  // GAS-GROUP
								  'DATE(Delivery.data) >= CURDATE()',
								  'Delivery.gcalendar_event_id' => null];
		$options['recursive'] = -1;
		$deliveryResults = $Delivery->find('all', $options);
		
		if(!empty($deliveryResults)) {
		
			$client = $this->_createClientGoogle($debug);
			$service = $this->_createServiceCalendarGoogle($client, $debug);
			
			if(isset($service))
			foreach ($deliveryResults as $deliveryResult) {

				echo "\nElaboro consegna di ".$this->timeHelper->i18nFormat($deliveryResult['Delivery']['data'],"%A %e %B %Y")." a ".$deliveryResult['Delivery']['luogo']." (".$deliveryResult['Delivery']['id'].") \n";
				
				/*
				 *  testo descrizione
				 */
				$body_mail = "";
				if($user->organization['Organization']['id']==3)  // arcoiris
					$body_mail .= 'Oggi, dalle ore '.substr($deliveryResult['Delivery']['orario_da'], 0, 5).' alle '.substr($deliveryResult['Delivery']['orario_a'], 0, 5)." ci sara' la consegna in ".$deliveryResult['Delivery']['luogo'];
				else
					$body_mail .= 'Il giorno '.$this->timeHelper->i18nFormat($deliveryResult['Delivery']['data'],"%A %e %B %Y")." ci sara' la consegna in ".$deliveryResult['Delivery']['luogo'].", dalle ore ".substr($deliveryResult['Delivery']['orario_da'], 0, 5)." alle ".substr($deliveryResult['Delivery']['orario_a'], 0, 5);
				if(!empty($deliveryResult['Delivery']['nota'])) {
					$body_mail .= "\n\r";
					$body_mail .= $deliveryResult['Delivery']['nota'];
				}
										
				$body_mail .= "\n\r";
				$body_mail .= 'https://www.portalgas.it/home-'.$j_seo;
				$body_mail .= "\n\r";
				
				$event = new Google_Service_Calendar_Event();
				$body_mail = mb_convert_encoding($body_mail,'UTF-8','UTF-8'); // per evitare  json_encode(): Invalid UTF-8 sequence in argument
				$event->setDescription($body_mail);
				if($user->organization['Organization']['id']==3)  // arcoiris
					$event->setSummary("Consegna Prodotti Gas Arcoiris");
				else
					$event->setSummary("Consegna di ".$this->timeHelper->i18nFormat($deliveryResult['Delivery']['data'],"%A %e %B %Y")." a ".$deliveryResult['Delivery']['luogo']);					
				$event->setLocation($user->organization['Organization']['localita']);
				$start = new Google_Service_Calendar_EventDateTime();
				$startDateTime = $deliveryResult['Delivery']['data'].'T'.$deliveryResult['Delivery']['orario_da'].$this->_getGTM(); 
				echo "\nstartDateTime: ".$startDateTime;
				$start->setDateTime($startDateTime);
				//$start->setDate($deliveryResult['Delivery']['data']);
				$event->setStart($start);
				
				$end = new Google_Service_Calendar_EventDateTime();
				// $end->setDateTime('2011-06-03T10:25:00.000-07:00');
				$endDateTime = $deliveryResult['Delivery']['data'].'T'.$deliveryResult['Delivery']['orario_a'].$this->_getGTM();
				echo "\nendDateTime: ".$endDateTime;
				$end->setDateTime($endDateTime);
				//$end->setDate($deliveryResult['Delivery']['data']);
				$event->setEnd($end);
						
				$createdEvent = $service->events->insert($gcalendar_id, $event);  // 'primary'

				// echo "\neventId ".$createdEvent->getId();
				$sql ="UPDATE ".Configure::read('DB.prefix')."deliveries as Delivery
					   SET
							gcalendar_event_id = '".$createdEvent->getId()."',
							modified = '".date('Y-m-d H:i:s')."'
					   WHERE
							organization_id = ".$user->organization['Organization']['id']."
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
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     *  event gcalendar x aggiornare la consegna (prima di Configure::read('GGEventGCalendarToAlertDeliveryOn') gg dall'apertura)
     *  con dettaglio produttori
     */
	public function usersDeliveryUpdate($user, $timeHelper, $debug) {
		
		try {
		
		$this->timeHelper = $timeHelper;
		
		echo date("d/m/Y")." - ".date("H:i:s")." Event gcalendar UPDATE con dettaglio consegna \n";
		
		$gcalendar_id = $user->organization['Organization']['gcalendar_id'];
		echo "\r".$gcalendar_id;
		
		if(empty($gcalendar_id)) {
			echo "Organization ".$user->organization['Organization']['id']." non ha valorizzato gcalendar_id [".$user->organization['Organization']['gcalendar_id']."] \n";
			return;
		}
			
		$j_seo = $user->organization['Organization']['j_seo'];
			
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		App::import('Model', 'Order');
		
		App::import('Model', 'Supplier');
		
		App::import('Model', 'User');
		$User = new User;

		/*
		 * estraggo le consegne che si apriranno domani
		 */
		$options = [];
		$options['conditions'] = ['Delivery.organization_id' => (int)$user->organization['Organization']['id'],
								  'DATE(Delivery.data) = CURDATE() + INTERVAL '.Configure::read('GGEventGCalendarToAlertDeliveryOn').' DAY ', 
								  'Delivery.sys'=> 'N',
								  'Delivery.type' => 'GAS',  // GAS-GROUP
								  'DATE(Delivery.data) >= CURDATE()',
								  'Delivery.gcalendar_event_id IS NOT null'];
		$options['recursive'] = -1;
		$deliveryResults = $Delivery->find('all', $options);
	
		if(!empty($deliveryResults)) {
		
			$client = $this->_createClientGoogle($debug);
			$service = $this->_createServiceCalendarGoogle($client, $debug);
		
			foreach ($deliveryResults as $deliveryResult) {
				
				echo "\nElaboro consegna (".$deliveryResult['Delivery']['id'].") di ".$this->timeHelper->i18nFormat($deliveryResult['Delivery']['data'],"%A %e %B %Y")." a ".$deliveryResult['Delivery']['luogo']." con eventId ".$deliveryResult['Delivery']['gcalendar_event_id']."\n";
				
				/*
				 * estraggo gli ordini associati alla consegna
				*/
				
				$Order = new Order;
				
				$Order->unbindModel(array('belongsTo' => array('Delivery')));
				$options = [];
				$options['conditions'] = ['Order.delivery_id' => $deliveryResult['Delivery']['id'],
										'Order.organization_id' => (int)$user->organization['Organization']['id'],
										'Order.isVisibleBackOffice' => 'Y',
										'Order.state_code !=' => 'CREATE-INCOMPLETE'];
				$options['recursive'] = 0;
				$options['fields'] = ['SuppliersOrganization.name', 'SuppliersOrganization.frequenza', 'SuppliersOrganization.supplier_id'];
				$options['order'] = ['SuppliersOrganization.name'];
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
						
					$options = [];
					$options['conditions'] = ['Supplier.id' => $orderResult['SuppliersOrganization']['supplier_id']];
					$options['fields'] = ['Supplier.descrizione', 'Supplier.img1'];
					$options['recursive'] = -1;
					$SupplierResults = $Supplier->find('first', $options);
					
					$tmpProduttori .= "\n\r";
					$tmpProduttori .= ((int)$numResult+1).') ';

					$tmpProduttori .= $orderResult['SuppliersOrganization']['name'];
					if(!empty($SupplierResults['Supplier']['descrizione'])) $tmpProduttori .= ' ('.$SupplierResults['Supplier']['descrizione'].')';
					if(!empty($SupplierResults['SuppliersOrganization']['frequenza'])) $tmpProduttori .= ' Frequenza '.$orderResult['SuppliersOrganization']['frequenza'];	
				} // end foreach ($orderResults as $orderResult)
				
				/*
				 *  testo descrizione
				 */
				$body_mail = "";
				if($user->organization['Organization']['id']==3)  // arcoiris
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
				$body_mail .= 'https://www.portalgas.it/home-'.$j_seo;

				
				$event = new Google_Service_Calendar_Event();
				$body_mail = mb_convert_encoding($body_mail,'UTF-8','UTF-8'); // per evitare  json_encode(): Invalid UTF-8 sequence in argument
				$event->setDescription($body_mail);
				if($user->organization['Organization']['id']==3)  // arcoiris
					$event->setSummary("Consegna Prodotti Gas Arcoiris");
				else
					$event->setSummary("Consegna di ".$this->timeHelper->i18nFormat($deliveryResult['Delivery']['data'],"%A %e %B %Y")." a ".$deliveryResult['Delivery']['luogo']);
				$event->setLocation($user->organization['Organization']['localita']);
				
				$start = new Google_Service_Calendar_EventDateTime();
				$startDateTime = $deliveryResult['Delivery']['data'].'T'.$deliveryResult['Delivery']['orario_da'].$this->_getGTM();  
				echo "\nstartDateTime: ".$startDateTime;
				$start->setDateTime($startDateTime);;
				//$start->setDate($deliveryResult['Delivery']['data']);
				$event->setStart($start);
				
				$end = new Google_Service_Calendar_EventDateTime();
				// $end->setDateTime('2011-06-03T10:25:00.000-07:00');
				$endDateTime = $deliveryResult['Delivery']['data'].'T'.$deliveryResult['Delivery']['orario_a'].$this->_getGTM();  
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
	
	public function cronUsersGmaps($user, $debug) {
        
		echo date("d/m/Y") . " - " . date("H:i:s") . " Dall'indirizzo cerca lng e lat per organization_id ".$user->organization['Organization']['id']." \n";

        /*
         * userprofile
         *
         * PHP Fatal error:  Call to undefined function jimport()
         * jimport( 'joomla.user.helper' );
         * define('JPATH_PLATFORM', dirname(__FILE__));
         * require(Configure::read('App.root').'/libraries/joomla/user/helper.php');
         */

        App::import('Model', 'User');
        $User = new User;

        $options = [];
        $options['conditions'] = ['User.organization_id' => $user->organization['Organization']['id']];
        $options['order'] = Configure::read('orderUser');
        $options['recursive'] = -1;
        $results = $User->find('all', $options);

        /*
          echo "<pre>";
          print_r($options);
          print_r($results);
          echo "</pre>";
         */

        $tot_user_elaborati = 0;
        foreach ($results as $numResult => $result) {

            $userProfile = $this->_getProfileUser($result['User']['id']);

            $lat = $this->_getProfileUserValue($userProfile, 'profile.lat');
            $lng = $this->_getProfileUserValue($userProfile, 'profile.lng');
            $address = $this->_getProfileUserValue($userProfile, 'profile.address');
            $city = $this->_getProfileUserValue($userProfile, 'profile.city');
            $cap = $this->_getProfileUserValue($userProfile, 'profile.postal_code');

            // echo "\n Tratto lo user ".$result['User']['id'].' '.$result['User']['username'].' coordinate '.$lat.' '.$lng.' - address '.$address.' '.$city;

            if ($tot_user_elaborati <= 10 && $lat == '' && $lng == '') {

                if ($address != '' && $city != '') {

                    if ($debug)
                        echo "\n tot_user_elaborati " . $tot_user_elaborati;

                    /* if($debug) {
                      echo "<pre>";
                      print_r($userProfile);
                      echo "</pre>";
                      }
                     */

                    $address = $results[$numResult]['Profile']['gmaps'] = $address . ' ' . $city . ' ' . $cap;

                    $tot_user_elaborati++;
                    $coordinate = $this->_gmap($address, $debug);

                    if ($debug) {
                        echo "<pre>";
                        print_r($coordinate);
                        echo "</pre>";
                    }

                    if (!empty($coordinate)) {
                        $lat = str_replace(",", ".", $coordinate['lat']);
                        $lng = str_replace(",", ".", $coordinate['lng']);

                        $sql = 'INSERT INTO ' . Configure::read('DB.portalPrefix') . 'user_profiles VALUES (' . $result['User']['id'] . ', "profile.lat", "\"' . $lat . '\"" , 10 )';
                        echo "\n " . $sql;
                        $executeInsert = $User->query($sql);

                        $sql = 'INSERT INTO ' . Configure::read('DB.portalPrefix') . 'user_profiles VALUES (' . $result['User']['id'] . ', "profile.lng", "\"' . $lng . '\"" , 11 )';
                        echo "\n " . $sql;
                        $executeInsert = $User->query($sql);
                    }
                }  // if($tot_user_elaborati<=10 && $lat!='' && $lng!='')   
            }    // if($address!='' && $city!='')  
        } // foreach ($results as $numResult => $result) 
	}
	
	public function cronSuppliersGmaps($debug) {
        
		echo date("d/m/Y") . " - " . date("H:i:s") . " Dall'indirizzo cerca lng e lat \n";

        App::import('Model', 'Supplier');
        $Supplier = new Supplier;

        $options = [];
        $options['order'] = array('Supplier.id');
        $options['recursive'] = -1;
        $results = $Supplier->find('all', $options);


        $tot_supplier_elaborati = 0;
        foreach ($results as $numResult => $result) {

            /* if($debug) {
              echo "<pre>";
              print_r($result);
              echo "</pre>";
              }
             */

            if ($tot_supplier_elaborati <= 10 && empty($result['Supplier']['lat']) && empty($result['Supplier']['lng'])) {

                if (!empty($result['Supplier']['localita'])) {

                    if ($debug)
                        echo "\n tot_supplier_elaborati " . $tot_supplier_elaborati;

                    if ($debug) {
                        echo "<pre>";
                        print_r($result);
                        echo "</pre>";
                    }

                    $address = $result['Supplier']['gmaps'] = $result['Supplier']['indirizzo'] . ' ' . $result['Supplier']['localita'] . ' ' . $result['Supplier']['cap'];

                    $tot_supplier_elaborati++;
                    $coordinate = $this->_gmap($address, $debug);

                    if ($debug) {
                        echo "<pre>";
                        print_r($coordinate);
                        echo "</pre>";
                    }

                    if (!empty($coordinate)) {
                        $lat = str_replace(",", ".", $coordinate['lat']);
                        $lng = str_replace(",", ".", $coordinate['lng']);

                        $sql = 'UPDATE ' . Configure::read('DB.prefix') . 'suppliers set lat = "' . $lat . '", lng = "' . $lng . '" WHERE id = ' . $result['Supplier']['id'];
                        echo "\n " . $sql;
                        $executeUpdate = $Supplier->query($sql);
                    }
                }  // if(!empty($result['Supplier']['localita']))
            }    // if($tot_supplier_elaborati<=10 && empty($result['Supplier']['lat']) && empty($result['Supplier']['lng'])) 
        } // foreach ($results as $numResult => $result) 		
	}
	
    private function _gmap($address, $debug = false) {

        $esito = "";

        $url = "https://maps.google.com/maps/api/geocode/json?sensor=false&address=";

        $url = $url . urlencode($address);

        $resp_json = $this->__curl_file_get_contents($url);
        $resp = json_decode($resp_json, true);

        if ($debug)
            echo "\n " . $url . ' ' . $resp['status'];


        if ($resp['status'] == 'OK') {
            if (isset($resp['results'][0]))
                $esito = $resp['results'][0]['geometry']['location'];
        } else {
            if ($debug) {
                echo "<pre>";
                print_r($resp);
                echo "</pre>";
                echo '<br/>' . $url;
            }
        }

        if (empty($esito)) {
            $esito['lat'] = Configure::read('LatLngNotFound');
            $esito['lng'] = Configure::read('LatLngNotFound');
        }

        return $esito;
    }
	
    private function _getProfileUser($user_id = 0) {

        App::import('Model', 'User');
        $User = new User;

        $sql = "SELECT profile_key, profile_value 
					FROM " . Configure::read('DB.portalPrefix') . "user_profiles 
					WHERE user_id = " . $user_id;
        $userProfile = $User->query($sql);

        return $userProfile;
    }

    /*
      [0] => Array
      (
      [j_user_profiles] => Array
      (
      [profile_key] => profile.region
      [profile_value] => "MI"
      )

      )
     */

    private function _getProfileUserValue($userProfile, $key) {

        $debug = false;

        /* if($debug) {
          echo "<pre>";
          print_r($userProfile);
          echo "</pre>";
          }
         */

        $value = '';
        foreach ($userProfile as $profile) {
            if ($profile['j_user_profiles']['profile_key'] == $key) {
                $value = $profile['j_user_profiles']['profile_value'];
                if (!empty($value))
                    $value = substr($value, 1, strlen($value) - 2);

                if ($debug)
                    echo '<br />' . $profile['j_user_profiles']['profile_key'] . ' ' . $key . ' => ' . $profile['j_user_profiles']['profile_value'] . ' ' . $value;

                break;
            }
        }

        return $value;
    }	
}