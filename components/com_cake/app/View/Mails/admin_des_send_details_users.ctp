<?php
/*
echo "<pre>";
print_r($results);
echo "</pre>";
*/					

echo '<table cellpadding="0" cellspacing="0">';
echo '<tr>';
echo '<th colspan="2">'.__('Organizations').'</th>';
echo '<th colspan="3">'.__('Destinatari').'</th>';
echo '</tr>';
		
foreach ($results as $numResult => $result) {	
			
	echo '<tr class="view-2">';
	echo '<td>';
	if(!empty($result['Organization']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Organization']['img1']))
		echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" />';	
	echo '</td>';			
	echo '<td>'.$result['Organization']['name'].'</td>';
	echo '<td colspan="3">';
	/*
	 *  referenti
	 */
	if(empty($result['Organization']['Referenti'])) {
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => "Non ci sono referenti associati!"));				
	}
	else {
		
		echo '<table>';
		foreach ($result['Organization']['Referenti'] as $numResult3 => $referente) {
		
			/*
			 * escludo lo user
			 */
			if($referente['User']['id']!=$user->id) {		
				echo '<tr>';
				echo '<td>';
				$tmpUser->organization['Organization']['id'] = $referente['User']['organization_id'];
				echo $this->App->drawUserAvatar($tmpUser, $referente['User']['id'], $referente['User']);							
				echo '</td>';			
				echo '<td>'.$referente['User']['name'].'</td>';
				echo '<td>';
				if(!empty($referente['User']['email']))
					echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$referente['User']['email'].'">'.$referente['User']['email'].'</a>'; 							
				echo '</td>';
				echo '<td>';
				foreach ($referente['User']['Group'] as $key => $group_id) {	
					echo $userGroups[$group_id]['name'].'<br />';
				}					
				echo '</td>';
				echo '</tr>';
			}
		} // loop Referenti	
		echo '</table>';
								
	} // if(empty($result['Organization']['Referenti']))
					
	echo '</td>';
	echo '</tr>';
} // loop Organization
				
echo '</table>';
?>