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
	
	public function send($Email, $mail, $body_mail, $debug=false) {
	
		$Email->viewVars(array('content_info' => $this->__getContentInfo()));
		
		if(!Configure::read('mail.send')) $Email->transport('Debug');
		
		if($debug) 
			echo "<br />mail TO: ".$mail." body_mail ".$body_mail;

		try {
			$Email->send($body_mail);
		} catch (Exception $e) {
			CakeLog::write("error", $e, array("mails"));
		}
	}
	
	public function drawLogo($organization=null) {
	
		if(isset($organization))
			$logo_url = 'http://'.Configure::read('SOC.site').Configure::read('App.img.loghi').'/'.$organization['Organization']['id'].'/'.Configure::read('Mail.logo');
		else
			$logo_url = 'http://'.Configure::read('SOC.site').Configure::read('App.img.loghi').'/0/'.Configure::read('Mail.logo');
	
		$str = '<a href="http://'.Configure::read('SOC.site').'" target="_blank"><img border="0" src="'.$logo_url.'" /></a>';
		return $str;
	}
	
	private function __getContentInfo() {

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
	
	public $belongsTo = array(
			'User' => array(
					'className' => 'User',
					'foreignKey' => 'user_id',
					'conditions' => 'User.organization_id = Mail.organization_id',
					'fields' => '',
					'order' => ''
			)
	);
}