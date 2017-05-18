<?php
App::uses('AppModel', 'Model');

class Counter extends AppModel {

    public $name = 'Counter';
    
    /*
     * table: request_payments
     */
    public function getCounterAndUpdate($user, $table) {
		$data = $this->__getCounter($user, $table);
		$this->__update($user, $data);
		return $data['Counter']['counter'];
	}
	
    public function getCounter($user, $table) {
		$data = $this->__getCounter($user, $table);
		return $data['Counter']['counter'];
	}
	
	private function __getCounter($user, $table) {
    	$counter = 1;
    	
    	$options['conditions'] = array('Counter.organization_id' => $user->organization['Organization']['id'],
    									'Counter.table' => $table);
    	$options['fields'] = array('id', 'counter');
    	
    	$results = $this->find('first', $options); 
    	if(empty($results)) {
    		$data['Counter']['organization_id'] = $user->organization['Organization']['id'];
    		$data['Counter']['table'] = $table;
    		$data['Counter']['counter'] = $counter;
    	}
    	else {
    		$counter = ($results['Counter']['counter']+1);

    		$data['Counter']['id'] = $results['Counter']['id'];
    		$data['Counter']['organization_id'] = $user->organization['Organization']['id'];
    		$data['Counter']['table'] = $table;
    		$data['Counter']['counter'] = $counter;
    	}
    	/*
    	echo "<pre>";
    	print_r($data);
    	echo "</pre>";
    	*/
		
		return $data;
	}
	
	private function __update($user, $data) {
    	$this->create();
    	$this->save($data);
    }
}