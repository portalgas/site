<?php
$this->App->d($ctrlUserFlagPrivacys);


$totUserFlagPrivacy = count($ctrlUserFlagPrivacys);

switch($totUserFlagPrivacy) {
	case 0:
		$msg = "Nessun gasista ha il ruolo di ".__('UserGroupsUserFlagPrivacy');
		$css = 'legenda-ico-alert';
		$link = $this->Html->link('Gestisci il ruolo '.__('UserGroupsUserFlagPrivacy'), ['controller' => 'UserGroupMaps', 'action' => 'edit', null, 'group_id='.Configure::read('group_id_user_flag_privacy')]);		
	break;
	case 1:
		$msg = "Gasista con il ruolo di ".__('UserGroupsUserFlagPrivacy');
		$css = 'legenda-ico-info';	
		$link;
	break;
	default:
		$msg = "Solo un gasista deve avere il ruolo ".__('UserGroupsUserFlagPrivacy');
		$css = 'legenda-ico-alert';		
		$link = $this->Html->link('Gestisci il ruolo '.__('UserGroupsUserFlagPrivacy'), ['controller' => 'UserGroupMaps', 'action' => 'edit', null, 'group_id='.Configure::read('group_id_user_flag_privacy')]);		
	break;
}

echo '<div class="legenda '.$css.'" style="float:none;">';
echo '	<table>';
echo '		<tr>';
echo '			<td>';
echo $msg;
if($totUserFlagPrivacy>0) {
	echo '<ul>';
	foreach($ctrlUserFlagPrivacys as $key => $ctrlUserFlagPrivacy) {
		echo '<li>'.$ctrlUserFlagPrivacy['User'][0]['name'].'</li>';
	}
	echo '</ul>';
}
echo $link;
echo '			</td>';
echo '		</tr>';
echo '	</table>';
echo '</div>';