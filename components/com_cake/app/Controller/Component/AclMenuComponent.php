<?php 
class AclMenuComponent extends Component {

    public $components = array('Acl','Auth','Session');

	public function filtraAclMenu($user_id,$results) {

			foreach ($results as $result) {
			/*
			echo "<pre>";
			print_r($result);
			echo "</pre>";
			*/
			echo $result['Menu']['link'];
			if(!empty($result['Menu']['link'])) {
				if ($this->Acl->check(array('User' => array('id' => $user_id)), 'Users/add')) {  // read create update delete
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
