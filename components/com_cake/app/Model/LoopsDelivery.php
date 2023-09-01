<?php
App::uses('AppModel', 'Model');

class LoopsDelivery extends AppModel {
	
	/*
	 * da una data di partenza (data_master) e dei filtri di ricorsione ($data) ottengo la nuova data di ricorsione
	*/
	public function getDataCopy($data_master, $data, $debug=false) {
	
		if($debug) {
			echo '<h2>__getDataCopy</h2>';
			echo 'data_master '.$data_master;
		}
	
		switch ($data['LoopsDelivery']['type']) {
			case 'WEEK':
				$week_every_week = $data['LoopsDelivery']['week_every_week'];
	
				if($debug) echo '<br />nuova copia: di '.$week_every_week.' settimana/e dopo';
	
				$data_copy = date('Y-m-d', strtotime('+'.$week_every_week.' weeks', strtotime($data_master)));
				break;
			case "MONTH":
	
				switch ($data['LoopsDelivery']['type_month']) {
					case 'MONTH1':
						$month1_day = $data['LoopsDelivery']['month1_day'];
						$month1_every_month = $data['LoopsDelivery']['month1_every_month'];
							
						/*
						 * gestione per il fine mese
						 * per i mese con 2023-08-31 gg creava 2023-10-01
						 */
						if($month1_day==31) {
							list($year, $month, $day) = explode('-', $data_master);
							switch($month) {
								case '01':
									$data_copy = date('Y-m-d', strtotime('+28 days', strtotime($data_master)));
								break;
								case '02':
								case '04':
								case '06':
								case '07':
								case '09':
								case '11':
								case '12':
									$data_copy = date('Y-m-d', strtotime('+31 days', strtotime($data_master)));
								break;
								case '03':
								case '05':
								case '08':
								case '10':
									$data_copy = date('Y-m-d', strtotime('+30 days', strtotime($data_master)));
								break;
							}
						}
						else {
							if($debug) echo '<br />nuova copia: il giorno '.$month1_day.' ogni '.$month1_every_month.' mese/i';
							
							$data_copy = date('Y-m-d', strtotime('+'.$month1_every_month.' months', strtotime($data_master)));
							$data_copy = date('Y', strtotime($data_copy)).'-'.date('m', strtotime($data_copy)).'-'.$month1_day;
								
							$giorni_mese = date('t', strtotime($data_copy));
							if($debug) echo '<br />ctrl se il giorno nel mese ('.$month1_day.') esiste: totale giorni del mese '.$giorni_mese;
							if($month1_day > $giorni_mese)
								$data_copy = date('Y', strtotime($data_copy)).'-'.date('m', strtotime($data_copy)).'-'.$giorni_mese;	
						}
						break;
					case 'MONTH2':
						$month2_every_type = $data['LoopsDelivery']['month2_every_type'];
						$month2_day_week = $data['LoopsDelivery']['month2_day_week'];
						$month2_every_month = $data['LoopsDelivery']['month2_every_month'];
							
						if($debug) echo '<br />nuova copia: il '.$month2_every_type.' giorno '.$month2_day_week.' ogni '.$month2_every_month.' mese/i';
							
						switch ($month2_day_week) {
							case 'SUN':
								$month2_day_week = 0;
								break;
							case 'MON':
								$month2_day_week = 1;
								break;
							case 'TUE':
								$month2_day_week = 2;
								break;
							case 'WED':
								$month2_day_week = 3;
								break;
							case 'THU':
								$month2_day_week = 4;
								break;
							case 'FRI':
								$month2_day_week = 5;
								break;
							case 'SAT':
								$month2_day_week = 6;
								break;
									
						}
							
						if($debug) echo '<br />mese di partenza '.date('m', strtotime($data_master));
						
						/*
						 * calcolo anno
						 */ 
						$year_start = date('Y', strtotime($data_master)); // anno di partenza 
						$month_start = date('m', strtotime($data_master)); // mese di partenza 
						if($month_start+$month2_every_month > 12)
							$year = ($year_start+1);
						else
							$year = $year_start;
						
						if($debug) echo '<br />Anno di partenza '.$year_start.' - dal mese di partenza '.$month_start.' aggiungo '.$month2_every_month.' mesi: '.$year;
						
						$mese_copy = $year.'-'.date('m', strtotime('+'.$month2_every_month.' months', strtotime($data_master))).'-1';
						if($debug) echo '<br />mese copy '.$mese_copy;
						if($debug) echo '<br />il nuovo mese ha '.date('t', strtotime($mese_copy)).' giorni';
						/*
						 * ciclo per trovare il giorno della settimana esatta
						* 	ex il 3 lunedi' del mese
						*/
						for($i=1; $i <= (int)date('t', strtotime($mese_copy)); $i++) {
	
							if($i < 10)
								$giorno_doppia_cifra = '0'.$i;
							else
								$giorno_doppia_cifra = $i;
								
							$mese_copy = $year.'-'.date('m', strtotime('+'.$month2_every_month.' months', strtotime($data_master))).'-'.$giorno_doppia_cifra;
							// if($debug) echo '<br />'.$i.') mese new '.$mese_copy;
	
							$giorno = date('w', strtotime($mese_copy));
							// if($debug) echo '<br />'.$i.') giorno delle settimana '.$giorno.' da confrontare con '.$month2_day_week;
							if($giorno==$month2_day_week) {
								$giorni_della_settimana[] = $mese_copy;
	
								/*
								 if($debug) {
								echo '<br />Trovato!';
								echo "<pre>";
								print_r($giorni_della_settimana);
								echo "</pre>";
								}
								*/
							}
						}
	
						if($month2_every_type=='FIRST')
							$data_copy = $giorni_della_settimana[0];
						else
						if($month2_every_type=='SECOND')
							$data_copy = $giorni_della_settimana[1];
						else
						if($month2_every_type=='THIRD')
							$data_copy = $giorni_della_settimana[2];
						else
						if($month2_every_type=='FOURTH')
							$data_copy = $giorni_della_settimana[3];
						else
						if($month2_every_type=='LAST')
							$data_copy = $giorni_della_settimana[(count($giorni_della_settimana)-1)];
	
						break;
				} // end switch ($data['LoopsDelivery']['type_month'])
				break;
		} // end switch ($data['LoopsDelivery']['type'])
	
		if($debug) echo '<br />data_copy '.$data_copy;
	
		return $data_copy;
	}

    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     * estraggo le consegne ricorsive di oggi
     * 		estraggo per data_master_reale 
     * 		ricalcolo la ricorsione partendo da data_master 
     * 			data_master 	  => data_copy
     * 			data_master_reale => data_copy_reale
     * 			data_copy 		  => calcolo nuova ricorsione
     * 			data_copy_reale   => calcolo nuova ricorsione
     * 			nuova consegna con data_copy_reale
     */	
	public function cron($user, $debug=false) {
		
        App::import('Model', 'Delivery');

        /*
         * faccio CURDATE() - INTERVAL 1 DAY cosi aspetto che sia chiusa la master e prendo quelle del giorno precedente (il cron parte alle 0.35)
         */
        $options = [];
        $options['conditions'] = ['LoopsDelivery.organization_id' => (int) $user->organization['Organization']['id'],
								  'DATE(LoopsDelivery.data_master_reale) = CURDATE() - INTERVAL 1 DAY'];
        $options['recursive'] = -1;
        $loopsDeliveryResults = $this->find('all', $options);

        if ($debug) {
            echo '<h2>Consegne ricorsive</h2>';
            echo "<pre>";
            print_r($loopsDeliveryResults);
            echo "</pre>";
        }

        if (!empty($loopsDeliveryResults)) {

            foreach ($loopsDeliveryResults as $numResult => $loopsDeliveryResult) {

                /*
                 * non faccio + il ctrl se esiste una consegna: si possono creare + consegne per la stessa data
                 * $delivery_just_exist = false;
                 */
                $rules = json_decode($loopsDeliveryResult['LoopsDelivery']['rules'], true);
                $loopsDeliveryResult['LoopsDelivery'] += $rules;

                $data = $loopsDeliveryResult['LoopsDelivery']['data_master_reale'];


                /*
                 * ctrl che non esisti gia' una consegna in quella data => NON +
                 *

                  $Delivery = new Delivery;

                  $options = [];
                  $options['conditions'] = array('Delivery.organization_id' => (int)$user->organization['Organization']['id'],
                  'DATE(Delivery.data)' => $loopsDeliveryResult['LoopsDelivery']['data_copy_reale']);
                  $options['recursive'] = -1;
                  $deliveryResults = $Delivery->find('first', $options);

                  if(empty($deliveryResults)) {
                 */
                // $delivery_just_exist = false;

                $row = [];
                $row['Delivery']['organization_id'] = $user->organization['Organization']['id'];
                $row['Delivery']['luogo'] = $loopsDeliveryResult['LoopsDelivery']['luogo'];
                $row['Delivery']['data'] = $loopsDeliveryResult['LoopsDelivery']['data_copy_reale'];
                $row['Delivery']['orario_da'] = $loopsDeliveryResult['LoopsDelivery']['orario_da'];
                $row['Delivery']['orario_a'] = $loopsDeliveryResult['LoopsDelivery']['orario_a'];
                $row['Delivery']['nota'] = $loopsDeliveryResult['LoopsDelivery']['nota'];
                $row['Delivery']['nota_evidenza'] = $loopsDeliveryResult['LoopsDelivery']['nota_evidenza'];
                $row['Delivery']['isToStoreroom'] = 'N';
                $row['Delivery']['isToStoreroomPay'] = 'N';
                $row['Delivery']['stato_elaborazione'] = 'OPEN';
                $row['Delivery']['isVisibleFrontEnd'] = 'Y';
                $row['Delivery']['isVisibleBackOffice'] = 'Y';
                $row['Delivery']['sys'] = 'N';

                if ($debug) {
                    echo '<h2>Nuova consegna</h2>';
                    echo "<pre>";
                    print_r($row);
                    echo "</pre>";
                }

                $Delivery = new Delivery;
                $Delivery->create();
                if ($Delivery->save($row)) {
                    if ($debug)
						echo "\r\n consegna per il " . $row['Delivery']['data'] . " a " . $row['Delivery']['luogo'] . " creata";
                } else {
                    if ($debug)
						echo "\r\n consegna per il " . $row['Delivery']['data'] . " a " . $row['Delivery']['luogo'] . " NON creata";
                }

                /* } // if(empty($deliveryResults)) 
                  else {
                  if($debug)
                  echo '<br />Consegne gia esistente';

                  $delivery_just_exist = true;
                  }
                 */

                /*
                 * creo nuova ricorsione
                 */
                $row1 = [];
                $row1['LoopsDelivery']['id'] = $loopsDeliveryResult['LoopsDelivery']['id'];
                $row1['LoopsDelivery']['organization_id'] = $user->organization['Organization']['id'];
                $row1['LoopsDelivery']['data_master'] = $loopsDeliveryResult['LoopsDelivery']['data_copy'];
                $row1['LoopsDelivery']['data_master_reale'] = $loopsDeliveryResult['LoopsDelivery']['data_copy_reale'];

                $data_copy = $this->getDataCopy($loopsDeliveryResult['LoopsDelivery']['data_copy'], $loopsDeliveryResult, $debug);

                $row1['LoopsDelivery']['data_copy'] = $data_copy;
                $row1['LoopsDelivery']['data_copy_reale'] = $data_copy;

                if ($debug) {
                    echo '<h2>Aggiorno ricorsione</h2>';
                    echo "<pre>";
                    print_r($row1);
                    echo "</pre>";
                }

                $this->create();
                if ($this->save($row1)) {
                    echo "\r\n consegna ricorsiva creata con data $data_copy";
                } else {
                    echo "\r\n consegna ricorsiva NON creata con data $data_copy";
                }

                /*
                 * invio mail di notifica a chi ha creato la ricorsione
                 */
                if ($loopsDeliveryResult['LoopsDelivery']['flag_send_mail'] == 'Y') {

                    App::import('Model', 'User');
                    $User = new User;

					App::import('Model', 'Mail');
					$Mail = new Mail;
					
					$Email = $Mail->getMailSystem($user);

                    $options = [];
                    $options['conditions'] = ['User.organization_id' => (int) $user->organization['Organization']['id'],
											  'User.id' => $loopsDeliveryResult['LoopsDelivery']['user_id']];
                    $options['recursive'] = -1;
                    $result = $User->find('first', $options);
                    if (!empty($result)) {
                        $name = $result['User']['name'];
                        $mail = $result['User']['email'];
                        $username = $result['User']['username'];

                        if ($debug)
							echo "\r\n tratto l'utente " . $name . ', username ' . $username;

						$body_mail = "";
						if ($delivery_just_exist)
							$body_mail .= 'Tentativo di creare la consegna ricorsiva ' . $this->timeHelper->i18nFormat($row['Delivery']['data'], "%A %e %B %Y") . " ma esisteva gi&agrave;.";
						else
							$body_mail .= 'Creata la consegna ricorsiva ' . $this->timeHelper->i18nFormat($row['Delivery']['data'], "%A %e %B %Y") . " a " . $row['Delivery']['luogo'] . '.';

						$body_mail .= '<br />Prossima consegna sar&agrave; ' . $this->timeHelper->i18nFormat($row1['LoopsDelivery']['data_copy_reale']);

						$body_mail_final = $body_mail;
						echo $body_mail_final;

						$subject_mail = 'Creata la consegna ricorsiva ' . $this->timeHelper->i18nFormat($row['Delivery']['data'], "%A %e %B %Y") . " a " . $row['Delivery']['luogo'];
						$Email->subject($subject_mail);

						$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
						$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->appHelper->traslateWww($user->organization['Organization']['www']))));

						$Email = $Mail->getMailSystem($this->user);
						$mailResults = $Mail->send($Email, $mail, $body_mail_final, $debug);
						if(isset($mailResults['OK'])) {
							$tot_ok++;
							$msg_ok .= $mailResults['OK'].'<br />';							
						}
						else 
						if(isset($mailResults['KO'])) {
							$tot_no++;
							$msg_ok .= $mailResults['KO'].'<br />';	
						}

                    } // if(!empty($results))				
                } // if($row['LoopsDelivery']['flag_send_mail']=='Y')
            } // end foreach ($loopsDeliveryResults as $loopsDeliveryResult)
        } // end if(!empty($loopsDeliveryResults)) 			
	}

	/*
	 * richiamato dal Cron
	 */
	public function creating($user, $loopsDeliveryResults, $create=false, $debug=false) {
        
		 App::import('Model', 'Delivery');
         $Delivery = new Delivery;
        
        /*
         * non faccio + il ctrl se esiste una consegna: si possono creare + consegne per la stessa data
         * $delivery_just_exist = false;
         */
        $rules = json_decode($loopsDeliveryResults['LoopsDelivery']['rules'], true);
        $loopsDeliveryResults['LoopsDelivery'] += $rules;

        $data = $loopsDeliveryResults['LoopsDelivery']['data_master_reale'];
		self::d('Creo nuova consegna con Delivery.data = LoopsDelivery.data_copy_reale '.$loopsDeliveryResults['LoopsDelivery']['data_copy_reale'], $debug);
		self::d('LoopsDelivery.data_master_reale '.$data,$debug);

        /*
         * ctrl che non esisti gia' una consegna in quella data => NON +
         *
         $options = [];
         $options['conditions'] = ['Delivery.organization_id' => (int)$user->organization['Organization']['id'],
 						            'DATE(Delivery.data)' => $loopsDeliveryResults['LoopsDelivery']['data_copy_reale']];
         $options['recursive'] = -1;
         $deliveryResults = $Delivery->find('first', $options);

         if(empty($deliveryResults)) {
         */
         // $delivery_just_exist = false;

        $row = [];
        $row['Delivery']['organization_id'] = $user->organization['Organization']['id'];
        $row['Delivery']['luogo'] = $loopsDeliveryResults['LoopsDelivery']['luogo'];
        $row['Delivery']['data'] = $loopsDeliveryResults['LoopsDelivery']['data_copy_reale'];
        $row['Delivery']['orario_da'] = $loopsDeliveryResults['LoopsDelivery']['orario_da'];
        $row['Delivery']['orario_a'] = $loopsDeliveryResults['LoopsDelivery']['orario_a'];
        $row['Delivery']['nota'] = $loopsDeliveryResults['LoopsDelivery']['nota'];
        $row['Delivery']['nota_evidenza'] = $loopsDeliveryResults['LoopsDelivery']['nota_evidenza'];
        $row['Delivery']['isToStoreroom'] = 'N';
        $row['Delivery']['isToStoreroomPay'] = 'N';
        $row['Delivery']['stato_elaborazione'] = 'OPEN';
        $row['Delivery']['isVisibleFrontEnd'] = 'Y';
        $row['Delivery']['isVisibleBackOffice'] = 'Y';
        $row['Delivery']['sys'] = 'N';

        self::d(['Nuova consegna', $row], $debug);

		$delivery_id = 0;
        if($create) {
	        $Delivery->create();
	        $saveResults = $Delivery->save($row);
	        if ($saveResults) {
				$delivery_id = $Delivery->getLastInsertID();		
	            echo "\r\n consegna per il " . $row['Delivery']['data'] . " a " . $row['Delivery']['luogo'] . " creata";
	        } else {
	            echo "\r\n consegna per il " . $row['Delivery']['data'] . " a " . $row['Delivery']['luogo'] . " NON creata";
	        }
	    }
	    else
	       echo "\r\n SIMULO - consegna per il " . $row['Delivery']['data'] . " a " . $row['Delivery']['luogo'] . " creata";

        /*
         * 	} // if(empty($deliveryResults)) 
	          else {
	          if($debug)
	          echo '<br />Consegne gia esistente';
	
	          $delivery_just_exist = true;
	         }
         * 
         */

        /*
         * creo nuova ricorsione
         */
        $row1 = [];
        $row1['LoopsDelivery']['id'] = $loopsDeliveryResults['LoopsDelivery']['id'];
        $row1['LoopsDelivery']['organization_id'] = $user->organization['Organization']['id'];
        $row1['LoopsDelivery']['data_master'] = $loopsDeliveryResults['LoopsDelivery']['data_copy'];
        $row1['LoopsDelivery']['data_master_reale'] = $loopsDeliveryResults['LoopsDelivery']['data_copy_reale'];
		$row1['LoopsDelivery']['delivery_id'] = $delivery_id;

        $data_copy = $this->getDataCopy($loopsDeliveryResults['LoopsDelivery']['data_copy'], $loopsDeliveryResults, $debug);

        $row1['LoopsDelivery']['data_copy'] = $data_copy;
        $row1['LoopsDelivery']['data_copy_reale'] = $data_copy;

        self::d(['Aggiorno ricorsione', $row1], $debug);

        if($create) {
        	$this->create();
	        $saveResults = $this->save($row1); 
	        if ($saveResults) {
	            echo "\r\n consegna ricorsiva creata (".$delivery_id.") con data $data_copy";
	        } else {
	            echo "\r\n consegna ricorsiva NON creata con data $data_copy";
	        }
		}
		else 
			echo "\r\n SIMULO consegna ricorsiva creata con data $data_copy";

        /*
         * invio mail di notifica a chi ha creato la ricorsione
         */
        if ($create && $loopsDeliveryResults['LoopsDelivery']['flag_send_mail'] == '..Y') {

            App::import('Model', 'User');
            $User = new User;

            App::import('Model', 'Mail');
            $Mail = new Mail;

			$Email = $Mail->getMailSystem($user);
			
            $options = [];
            $options['conditions'] = ['User.organization_id' => (int) $user->organization['Organization']['id'],
                					  'User.id' => $loopsDeliveryResults['LoopsDelivery']['user_id']];
            $options['recursive'] = -1;
            $result = $User->find('first', $options);
            if (!empty($result)) {
                $name = $result['User']['name'];
                $mail = $result['User']['email'];
                $username = $result['User']['username'];

                echo "\r\n tratto l'utente " . $name . ', username ' . $username;

				$body_mail = "";
				if ($delivery_just_exist)
					$body_mail .= 'Tentativo di creare la consegna ricorsiva ' . $this->timeHelper->i18nFormat($row['Delivery']['data'], "%A %e %B %Y") . " ma esisteva gi&agrave;.";
				else
					$body_mail .= 'Creata la consegna ricorsiva ' . $this->timeHelper->i18nFormat($row['Delivery']['data'], "%A %e %B %Y") . " a " . $row['Delivery']['luogo'] . '.';

				$body_mail .= '<br />Prossima consegna sar&agrave; ' . $this->timeHelper->i18nFormat($row1['LoopsDelivery']['data_copy_reale']);

				$body_mail_final = $body_mail;
				echo $body_mail_final;

				$subject_mail = 'Creata la consegna ricorsiva ' . $this->timeHelper->i18nFormat($row['Delivery']['data'], "%A %e %B %Y") . " a " . $row['Delivery']['luogo'];
				$Email->subject($subject_mail);

				$Email->viewVars(array('body_header' => sprintf(Configure::read('Mail.body_header'), $name)));
				$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->appHelper->traslateWww($user->organization['Organization']['www']))));

				$mailResults = $Mail->send($Email, $mail, $body_mail_final, $debug);
				
            } // if(!empty($results))				
        } // if($row['LoopsDelivery']['flag_send_mail']=='Y')	
        
        return true;
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
			),
		),
		'delivery_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
			),
		),
		'luogo' => array(
				'rule' => ['notBlank'],
				'message' => 'Indica il luogo della consegna',
				'allowEmpty' => false
		),
		'data' => array(
				'date' => array(
						'rule' => array('date'),
						'message' => 'Indica la data della consegna',
						'allowEmpty' => false
				),
		),			
	);

	public $belongsTo = [
		'User' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Delivery' => [
			'className' => 'Delivery',
			'foreignKey' => 'delivery_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}