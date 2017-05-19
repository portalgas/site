<?php
/*
 * Model/EventType.php
 * CakePHP Full Calendar Plugin
 *
 * Copyright (c) 2010 Silas Montgomery
 * http://silasmontgomery.com
 *
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */
 
class DocsCreateUser extends AppModel {
	
	public $useTable = 'docs_users';

	/*
	 * passato user_id,user_id inserisco / cancello users legati ad un evento
	 */
	public function insert($user, $doc_id, $user_ids, $debug=false) {
		
		App::import('Model', 'Counter');
		
		/*
		 *  prima cancello tutti gli users associati
		 */
		try {		
			$sql = "DELETE FROM ".Configure::read('DB.prefix')."docs_users
						WHERE
					organization_id = ".(int)$user->organization['Organization']['id']."
					AND doc_id = $doc_id";
			if($debug) echo '<br />'.$sql;
			$result = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}
		
		$arr_user_ids = explode(',', $user_ids);
		foreach ($arr_user_ids as $user_id) {
			$data = array();
			$data['DocsCreateUser']['organization_id'] = $user->organization['Organization']['id'];
			$data['DocsCreateUser']['doc_id'] = $doc_id;
			$data['DocsCreateUser']['user_id'] = $user_id;
			
			$Counter = new Counter;				
			$data['DocsCreateUser']['num'] = $Counter->getCounterAndUpdate($user, 'docs_users');

			if($debug) {
				echo "<pre>DocsCreateUser::insert() \n ";
				print_r($data);
				echo "</pre>";			
			}
		
			$this->create();
			if(!$this->save($data)) {
				break;
				return false;
			}
		}

		return true;
	}	
	
	var $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id'
		)
	);	
}
?>
