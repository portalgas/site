<?php
App::uses('AppModel', 'Model');

class Event extends AppModel {
	var $name = 'Event';
	var $displayField = 'title';
	
	/*
	 * call cron mailEvents
	 */
	public function sendNotificationMail($timeHelper, $appHelper, $user, $debug=false) {
		
		if(empty($user))
			return;
				
		try {
			
			$this->timeHelper = $timeHelper;
			$this->appHelper = $appHelper;

			App::import('Model', 'Mail');
			$Mail = new Mail;
		
			$Email = $Mail->getMailSystem($user);
			
			if($debug)
				echo date("d/m/Y")." - ".date("H:i:s")." Events agli utenti con date_alert_mail = CURDATE(), organization_id ".$user->organization['Organization']['id']." \n";
			
			$j_seo = $user->organization['Organization']['j_seo'];
		
			/*
			 * estraggo Event con Event.date_alert_mail = CURDATE()
			 */
			$sql = "SELECT Event.*, EventType.name, User.id, User.name, User.email, User.username  
					FROM ".Configure::read('DB.prefix')."event_types EventType, ".Configure::read('DB.prefix')."events as Event 
						LEFT JOIN ".Configure::read('DB.portalPrefix')."users User ON (User.organization_id = Event.organization_id and User.id = Event.user_id) 
					WHERE 
					 Event.organization_id = ".$user->organization['Organization']['id']."
					and EventType.organization_id = ".$user->organization['Organization']['id']."
					and EventType.id = Event.event_type_id 
					and DATE(Event.date_alert_mail) = CURDATE()";
			if($debug)
				echo "\n".$sql;
			$results = $this->query($sql);
			if(!empty($results)) 
			foreach ($results as $numResult => $result) {
				echo "\nTratto l'Event ".$result['Event']['title']." \n";
				
				$responsabile_user_id = $result['User']['id'];
				$responsabile_user = [];
				if(!empty($responsabile_user_id)) {
					$responsabile_user[0]['User']['id'] = $result['User']['id'];
					$responsabile_user[0]['User']['name'] = $result['User']['name'];
					$responsabile_user[0]['User']['email'] = $result['User']['email'];
					$responsabile_user[0]['User']['username'] = $result['User']['username'];
				}
				
				/*
				 * estraggo users
				 */
				$sql = "SELECT User.id, User.name, User.email, User.username 
						FROM ".Configure::read('DB.prefix')."events_users EventsUser, ".Configure::read('DB.portalPrefix')."users User 
						WHERE 
						 EventsUser.organization_id = ".$user->organization['Organization']['id']."
						and User.organization_id = ".$user->organization['Organization']['id']."
						and EventsUser.event_id = ".$result['Event']['id']."
						and EventsUser.user_id = User.id ";
				if(!empty($responsabile_user_id))
					$sql .= "and User.id != $responsabile_user_id ";
				$sql .= " order by User.id";
				// echo "\n".$sql;
				$usersResults = $this->query($sql);
		
				if(!empty($responsabile_user_id))
					$usersResults = array_merge($usersResults, $responsabile_user);
				/*
				echo "<pre>User UNION with Referente Event ";
				print_r($usersResults);
				echo "</pre>";
				*/
				if(!empty($usersResults)) 
				foreach ($usersResults as $numResultUser => $usersResult) {
						$name = $usersResult['User']['name'];
						$mail = $usersResult['User']['email'];
						$username = $usersResult['User']['username'];
						
						if($debug)
							echo "<br />\n".$numResultUser.") tratto l'utente ".$name.', username '.$username;
						
						if(!empty($mail)) {
							$body_mail = "";
							$body_mail .= 'Il giorno '.$this->timeHelper->i18nFormat($result['Event']['start'],"%A %e %B %Y alle %H:%M")." c'è un'attività alla quale dovrai partecipare: ";
							$body_mail .= $result['EventType']['name']." ".$result['Event']['title'];
							if(!empty($result['Event']['nota'])) {
								$body_mail .= '<div style="float:right;width:75%;margin-top:5px;">';
								$body_mail .= '<span style="color:red;">Nota</span> ';
								$body_mail .= $result['Event']['nota'];
								$body_mail .= '</div>';							
							}

							$url = 'https://www.portalgas.it/home-'.$j_seo.'/events';
								
						    $body_mail .= '<div style="clear: both; float: none; margin: 5px 0 15px;">'; 
							$body_mail .= 'Autenticati e <a target="_blank" href="'.$url.'">clicca qui per maggior dettaglio</a>';
							$body_mail .= '</div>';	

							$body_mail_final = $body_mail;
							if($debug && $numResult==0) echo "<br />\n".$body_mail_final;
							
							$subject_mail = $this->_organizationNameError($user->organization).", attività ".$result['EventType']['name']." ".$result['Event']['title'];
							$Email->subject($subject_mail);
							
							$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
							$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->appHelper->traslateWww($user->organization['Organization']['www']))));
							
							$mailResults = $Mail->send($Email, $mail, $body_mail_final, $debug);
							
						} // end if(!empty($mail)) 
							
				} // end loop Users
			}  // end loop Events		
		} catch (Exception $e) {
			if($debug)
				echo "<br />sendNotificationMail() ".$e;
			else
				CakeLog::write("error", $e);
		}		
	}
	
	var $validate = array(
		'title' => array(
			'notempty' => array(
				'rule' => ['notBlank'],
			),
		),
		'start' => array(
			'date' => array(
				'rule'       => 'datetime',
				'message'    => 'Inserisci una data di apertura valida',
				'allowEmpty' => false
			),
			'dateMinore' => array(
				'rule'       =>  array('date_comparison', '<=', 'end'),
				'message'    => 'La data di apertura non può essere posteriore della data di chiusura',
			)
		),
		'end' => array(
			'date' => array(
				'rule'       => 'datetime',
				'message'    => 'Inserisci una data di chiusura valida',
				'allowEmpty' => false
			),
			'dateMaggiore' => array(
				'rule'       =>  array('date_comparison', '>=', 'start'),
				'message'    => 'La data di chiusura non può essere antecedente della data di apertura',
			)
		),
		'date_alert_mail' => array(
			'date' => array(
				'rule'       => 'date',
				'message'    => 'Inserisci una data di notifica per mail valida',
				'allowEmpty' => true
			),
			'dateMaggiore' => array(
				'rule'       =>  array('date_comparison', '<', 'start'),
				'message'    => "La data di notifica per mail dell'attività non può essere antecedente della data di apertura",
			),
		),
		'date_alert_fe' => array(
			'date' => array(
				'rule'       => 'date',
				'message'    => 'Inserisci una data di notifica a Front-end valida',
				'allowEmpty' => true
			),
			'dateMaggiore' => array(
				'rule'       =>  array('date_comparison', '<', 'start'),
				'message'    => "La data di notifica a Front-end dell'attività non può essere antecedente della data di apertura",
			),
		),
	);

	function date_comparison($field=[], $operator, $field2) {
		foreach( $field as $key => $value1 ){
			$value2 = $this->data[$this->alias][$field2];
			
			if(empty($value2))
				return true;
			
			if (!Validation::comparison($value1, $operator, $value2))
				return false;
		}
		return true;
	}
	
	var $belongsTo = array(
		'EventType' => array(
			'className' => 'EventType',
			'foreignKey' => 'event_type_id'
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id'
		),
	);

	public $hasMany = array(
		'EventsUser' => array(
				'className' => 'EventsUser',
				'foreignKey' => 'user_id',
				'dependent' => false,
				'conditions' =>  '',
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
?>