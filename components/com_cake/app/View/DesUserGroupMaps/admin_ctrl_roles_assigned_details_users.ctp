<?php
/*
echo "<pre>";
print_r($results);
echo "</pre>";
*/					

echo '<table cellpadding="0" cellspacing="0">';
echo '<tr>';
echo '<th colspan="3">'.__('Users').'</th>';
echo '<th>Ruoli amibente D.E.S.</th>';
echo '<th>Ruoli sugli ordini</th>';
echo '<th style="width:10px"></th>';
echo '<th>Esito</th>';
echo '</tr>';

if(!empty($results)) {		
	foreach ($results as $numResult => $result) {	
					
			$ruoli_consistenti = false;
					
			echo '<tr>';		
			echo '<td>';
			$tmpUser->organization['Organization']['id'] = $result['User']['organization_id'];
			echo $this->App->drawUserAvatar($tmpUser, $result['User']['id'], $result['User']);							
			echo '</td>';			
			echo '<td>'.$result['User']['name'].'</td>';
			echo '<td>';
			if(!empty($result['User']['email']))
				echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a>'; 							
			echo '</td>';
			echo '<td>';
			if(isset($result['User']['Group']))
			foreach ($result['User']['Group'] as $key => $group_id) {	
				echo $userGroups[$group_id]['name'].'<br />';
			}					
			echo '</td>';
			echo '<td>';
			if(isset($result['User']['GroupOrder']))
			foreach ($result['User']['GroupOrder'] as $key => $group_id) {
				if($group_id==Configure::read('group_id_referent'))
					echo __('UserGroupsReferent').'<br />';
				else
				if($group_id==Configure::read('group_id_super_referent'))
					echo __('UserGroupsSuperReferent').'<br />';
					
				$ruoli_consistenti = true;
			}					
			echo '</td>';
			
			if($ruoli_consistenti) {
				echo '<td style="background-color:green;"></td>';
				echo '<td>';
				echo "L'utente gestirà l'ordine condiviso e l'ordine";
				echo '</td>';
			}
			else {
				echo '<td style="background-color:red;"></td>';
				echo '<td>';
				echo "L'utente gestirà <b>solo</b> l'ordine condiviso";
				echo '</td>';
			}
			
			echo '</tr>';
	} // loop Referenti	
	echo '</table>';														
}
else
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => "Non ci sono referenti associati!"));				
				
echo '</table>';
?>