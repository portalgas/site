<?php
App::uses('AppModel', 'Model');


class Msg extends AppModel {
	
	public function getRandomMsg() {
		$results = $this->find('first', array( 
							 'order' => 'rand()',
							 'limit' => 1,
							 'conditions' => array('Msg.flag_attivo' => 'Y') 
							 ));
							 
		return $results;
	}
}