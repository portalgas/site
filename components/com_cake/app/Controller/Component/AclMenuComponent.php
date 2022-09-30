<?php 
App::uses('Component', 'Controller');

class AclMenuComponent extends Component {

    public $components = ['Acl', 'Auth', 'Session'];
    private $Controller = null;

    public function initialize(Controller $controller) 
    {
		$this->Controller = $controller;
    }
	
	public function filtraAclMenu($user_id,$results) {

		$controllerLog = $this->Controller;
	
		foreach ($results as $result) {
			
			$controllerLog::d($result, false);
	
			echo $result['Menu']['link'];
			if(!empty($result['Menu']['link'])) {
				if ($this->Acl->check(['User' => ['id' => $user_id]], 'Users/add')) {  // read create update delete
					echo '<br/>MENU lo user '.$user_id.' ha i permessi';
				} else { 
					echo '<br/>MENU lo user '.$user_id.' non ha i permessi';
				} 
			}
		}

		return $results;
	} 
}
?>
