<?php
App::uses('AppModel', 'Model');

 
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
			self::d($sql, $debug);
			$result = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}
		
		$arr_user_ids = explode(',', $user_ids);
		foreach ($arr_user_ids as $user_id) {
			$data = [];
			$data['DocsCreateUser']['organization_id'] = $user->organization['Organization']['id'];
			$data['DocsCreateUser']['doc_id'] = $doc_id;
			$data['DocsCreateUser']['user_id'] = $user_id;
						
			/*
			 * setto il num progressivo reale quando invio la mail
			 */
			$data['DocsCreateUser']['num'] = 0;
			$data['DocsCreateUser']['year'] = 0;

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
	
	public function getLastNum($user, $year, $debug=false) {
    	
		$num_last = 0;
		
		$options = [];
    	$options['conditions'] = ['DocsCreateUser.organization_id' => $user->organization['Organization']['id'],
    							  'DocsCreateUser.year' => $year];
    	$options['fields'] = ['MAX(DocsCreateUser.num) as num_last '];
    	$results = $this->find('all', $options); 
    	
		if(isset($results[0]) && isset($results[0][0]) && !empty($results[0][0]['num_last'])) 
			$num_last = $results[0][0]['num_last'];
		
    	/*
    	echo "<pre>";
    	print_r($options);
    	print_r($results);
    	echo "</pre>";
		*/
		
		return ($num_last+1);		
	}
	
	var $belongsTo = array(
		'DocsCreate' => array(
			'className' => 'DocsCreate',
			'foreignKey' => 'doc_id'
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id'
		)
	);	
}
?>
