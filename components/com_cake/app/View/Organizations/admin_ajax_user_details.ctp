<?php
$this->App->d($results, false);

if(!empty($results)) {

	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '<th>'.__('N').'</th>';
	echo '<th colspan="2">'.__('GasOrganization').'</th>';
	echo '<th>'.__('gasManager').'</th>';
	echo '<th colspan="2">'.__('Nominative').'</th>';
	echo '<th>'.__('Username').'</th>';
	echo '<th>'.__('UserGroups').'</th>';
	echo '<th>'.__('Stato').'</th>';
	echo '<th>'.__('RegisterDate').'</th>';
	echo '<th>'.__('LastvisitDate').'</th>';
	echo '</tr>';
	
	foreach ($results as $numResult => $result) {

		if(!empty($result['User']['lastvisitDate']) && $result['User']['lastvisitDate']!=Configure::read('DB.field.datetime.empty')) 
			$lastvisitDate = $this->Time->i18nFormat($result['User']['lastvisitDate'],"%e %b %Y");
		else 
			$lastvisitDate = "";
		
		echo '<tr class="view">';
		echo '<td>'.((int)$numResult+1).'</td>';
		echo '<td>';
		echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" /> ';	
		echo '</td>';		
		echo '<td title="'.$result['Organization']['id'].'">';
		echo $result['Organization']['name']; 
		echo '</td>';		
		echo '<td>';
		if(!empty($result['Organization']['Manager']))
		foreach($result['Organization']['Manager'] as $manager) {
			echo $manager['User']['name'];
			if(!empty($manager['User']['email'])) echo ' <a title="'.__('Email send').'" target="_blank" href="mailto:'.$manager['User']['email'].'">'.$manager['User']['email'].'</a>';
			echo '<br />';			
		}
		echo '</td>';		
		echo '<td>'.$this->App->drawUserAvatar($user, $result['User']['id'], $result['User']).'</td>';
		echo '<td>'.$result['User']['name'].'</td>';
		echo '<td>'.$result['User']['username'];
		if(!empty($result['User']['email'])) echo '<br /><a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a>';
		echo '</td>';
		
		echo '<td>';
		if(!empty($result['UserGroupMap']))
		foreach($result['UserGroupMap'] as $userGroup) {
			if(!empty($userGroups[$userGroup['group_id']]['name']))
				echo $userGroups[$userGroup['group_id']]['name'].'<br />';
		}
		echo '</td>';
		
		echo '<td>';
		if ($result['User']['block'] == 0)
			echo '<span style="color:green;">Attivo</span>';
		else
			echo '<span style="color:red;">Disattivato</span>';
		echo '</td>';		
		echo '<td>'.$this->Time->i18nFormat($result['User']['registerDate'],"%e %b %Y").'</td>';
		echo '<td>'.$lastvisitDate.'</td>';
		echo '</tr>';
	}
	echo '</table></div>';
} //end if(!empty($results)) 
else
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => __('msg_search_not_result')));
?>