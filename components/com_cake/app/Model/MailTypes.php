<?php
App::uses('AppModel', 'Model');

/*
* $param_config_mail_order_types tipologie persistite in Organization.paramConfig ["DEFAULT","CONFIRM_AFTER_INCOMING"]
*/ 
class MailTypes extends AppModel {

	public $useTable = false;
	
	public function getMailOrderTypes($user) {
		$results = [];
		$results['DEFAULT'] = 'Default';
		$results['CONFIRM_AFTER_INCOMING'] = "Conferma il giorno dell'arrivo della merce";

		return $results;
	} 

	public function getMailOrderTypeDefault($user) {
		$results = [];
		$results['DEFAULT'] = 'Default';
		return 'DEFAULT';
	}

	public function getOrganizationMailOrderTypes($user) {
		if(!isset($user->organization['Organization']['mailOrderTypes']) || empty($user->organization['Organization']['mailOrderTypes'])) {
			$results = [];
			$results['DEFAULT'] = 'Default';
			return $results;
		}

		$mail_order_types = $this->getMailOrderTypes($user);

		$results = [];
		foreach($user->organization['Organization']['mailOrderTypes'] as $param_config_mail_order_type) {
			$results[$param_config_mail_order_type] = $mail_order_types[$param_config_mail_order_type];
		}
		
		return $results;
	} 
}