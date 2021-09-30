<?php
App::uses('AppModel', 'Model');
App::uses('CakeEmail', 'Network/Email');

class Mail extends AppModel {

	/*
	 * crea oggetto mail per invio mail di sistema
	 */
	 public function getMailSystem($user) {
		$Email = new CakeEmail(Configure::read('EmailConfig'));
		$Email->helpers(array('Html', 'Text'));
		$Email->template('default');
		$Email->emailFormat('html');
	
		$Email->replyTo(Configure::read('Mail.no_reply_mail'), Configure::read('Mail.no_reply_name'));
		$Email->from(array(Configure::read('SOC.mail') => Configure::read('SOC.name')));
		$Email->sender(Configure::read('SOC.mail'), Configure::read('SOC.name'));
	
		$Email->viewVars(array('header' => $this->drawLogo($user->organization)));
		
		return $Email;
	}
	
	/*
	 * $mails = [$UserProfile.email, User.email] perche' restituisco il risultato solo della  
	 */
	public function send($Email, $mails, $body_mail, $debug=false) {

		$results = [];
		$_mails = [];
		
		if(!is_array($mails))
			$_mails[] = $mails;
		else 
			$_mails = $mails;
		
		foreach($_mails as $mail) {
			
			$mail = trim($mail);
			
			// profile.email = "mail"
			if(substr($mail, 0, 1)=='"') // primo carattere
				$mail = substr($mail, 1, strlen($mail));
			if(substr($mail, -1, 1)=='"') // ultimo carattere
				$mail = substr($mail, 0, strlen($mail)-1);
			$mail = trim($mail);
				
			if(!empty($mail)) { 
			
				self::d("Mail::send - tratto la mail ".$mail, $debug);
									
				/*
				non + perche' mail2 = UserProfile.email
				$results['KO'] = 'Mail vuota!';
				return $results;
				*/
			
				$exclude = false;
				foreach(Configure::read('EmailExcludeDomains') as $emailExcludeDomain) {
					self::d('Mail::send - EmailExcludeDomains '.$mail.' - '.$emailExcludeDomain, $debug);
					if(strpos($mail, $emailExcludeDomain)!==false) {
						$exclude = true;
						break;
					}
				}
				
				if($exclude)  {	
					self::d("EXCLUDE mail TO: ".$mail, $debug);
					$results['OK'] = $mail.' (modalita DEBUG)';
				}
				else {
					$Email->viewVars(array('content_info' => $this->_getContentInfo()));
					
					if(!Configure::read('mail.send')) $Email->transport('Debug');
					
					if($debug) {
						if (!Configure::read('mail.send'))
							self::d("Mail::send - inviata a " . $mail . " (modalita DEBUG)", $debug);
						else
							self::d("Mail::send - inviata a " . $mail, $debug);
											
						self::d("Mail::send - mail TO: ".$mail." body_mail ".$body_mail, $debug);
					}

					try {
						$Email->to($mail);
						$Email->send($body_mail);
						
						if (!Configure::read('mail.send'))
							$results['OK'] = $mail.' (modalita DEBUG)';
						else
							$results['OK'] = $mail;
					} catch (Exception $e) {
						$results['KO'] = $mail;
						CakeLog::write("error", 'mail '.$mail, ['mails']);
						CakeLog::write("error", $body_mail, ['mails']);
						CakeLog::write("error", $e, ['mails']);
					}
				}
			} // end if(empty($mail)) 
			self::d($results, $debug);
		} // loops mails
		
		return $results;
	}
							
	public function drawLogo($organization=null) {
	
		if(isset($organization))
			$logo_url = 'https://'.Configure::read('SOC.site').Configure::read('App.img.loghi').'/'.$organization['Organization']['id'].'/'.Configure::read('Mail.logo');
		else
			$logo_url = 'https://'.Configure::read('SOC.site').Configure::read('App.img.loghi').'/0/'.Configure::read('Mail.logo');
	
		$str = '<a href="https://'.Configure::read('SOC.site').'" target="_blank"><img border="0" src="'.$logo_url.'" /></a>';
		return $str;
	}
	
	public function _getContentInfo() {

		App::import('Model', 'Msg');
		$Msg = new Msg;	

		$results = $Msg->getRandomMsg();
		if(!empty($results)) 
			$str = $results['Msg']['testo'];
		else
			$str = '';
		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
		return $str;
	}
	
	public $belongsTo = [
			'User' => [
					'className' => 'User',
					'foreignKey' => 'user_id',
					'conditions' => 'User.organization_id = Mail.organization_id',
					'fields' => '',
					'order' => ''
			]
	];
}