<?php
App::uses('AppModel', 'Model');

class UserProfile extends AppModel {

	public $useTable = 'user_profiles'; 
	public $tablePrefix = 'j_';	
	public $prefix_profile_key = 'profile.';
	    
    public $primaryKey = ['user_id', 'profile_key'];
	
	public function getValue($user, $user_id, $profile_key, $profile_value_default='', $debug=false) {
	
		$results = '';
			
		$options = [];
		$options['conditions'] = ['UserProfile.profile_key' => $this->prefix_profile_key.$profile_key,
								  'UserProfile.user_id' => $user_id];
		$options['fields'] = ['UserProfile.profile_value'];
		$options['recursive'] = -1;
		$userProfileResults = $this->find('first', $options);
		self::d([$options, $userProfileResults], $debug);
		
		if(!empty($userProfileResults)) {
			$results = $userProfileResults['UserProfile']['profile_value'];
			
			self::d($results);
			
			/* 
			 * elimino i " 
			 */
			 $results = substr($results, 1);
			 $results = substr($results, 0, strlen($results)-1);
		}
		else
		    $results = $profile_value_default;
		
		self::d('UserProfile::getValue('.$profile_key.') '.$results, $debug);
		
		return $results;
	}
	
	public function setValue($user, $user_id, $profile_key, $profile_value, $debug=false) {
	
		$results = true;
		$userProfileResults = [];
			
		$options = [];
		$options['conditions'] = ['UserProfile.profile_key' => $this->prefix_profile_key.$profile_key,
								  'UserProfile.user_id' => $user_id];
		$options['recursive'] = -1;
		$userProfileResults = $this->find('first', $options);
		self::d([$options, $userProfileResults], $debug);
		if(!empty($userProfileResults)) {

			$userProfileResults['profile_value'] = '"'.$profile_value.'"';
			
			/*
			 * il model con doppia key non funziona
			 */
			$sql = "UPDATE ".$this->tablePrefix.$this->useTable."  
					SET profile_value = '\"".addslashes($profile_value)."\"'  
					WHERE user_id = ".$user_id." AND profile_key = '".$this->prefix_profile_key.$profile_key."'";
			self::d($sql, $debug);
			try {
				$this->query($sql);
			}
			catch (Exception $e) {
				CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
			}			 
		}
		else {

			/*
			 * il model con doppia key non funziona
			 */
			$sql = "INSERT into ".$this->tablePrefix.$this->useTable."  
					(user_id,profile_value,profile_key,ordering) VALUES 
					(".$user_id.", '\"".addslashes($profile_value)."\"', '".$this->prefix_profile_key.$profile_key."',0)";
			self::d($sql, $debug);
			try {
				$this->query($sql);
			}
			catch (Exception $e) {
				CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
			}			 
		}
		
		return $results;
	}		
}