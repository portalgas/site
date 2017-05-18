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
 
class EventsUser extends AppModel {
	
	var $name = 'EventsUser';
	
	/*
	 * passato user_id,user_id inserisco / cancello users legati ad un evento
	 */
	public function insert($user, $event_id, $user_ids, $debug=false) {
		
		/*
		 *  prima cancello tutti gli users associati
		 */
		try {		
			$sql = "DELETE FROM ".Configure::read('DB.prefix')."events_users
						WHERE
					organization_id = ".(int)$user->organization['Organization']['id']."
					AND event_id = $event_id";
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
			$data['EventsUser']['organization_id'] = $user->organization['Organization']['id'];
			$data['EventsUser']['event_id'] = $event_id;
			$data['EventsUser']['user_id'] = $user_id;

			if($debug) {
				echo "<pre>EventsUser::insert() \n ";
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
