<?php 
App::uses('UtilsCommons', 'Lib');
      
class UsersFlagPrivacyHelper extends AppHelper {
		
	private $debug =  false;

	/*  
	 * aclAction = isManagerUser / isManagerUserDes
	 */
	public function drawRow($user, $results, $aclAction=false, $isUserFlagPrivay=false, $options=[]) {

		$str = '';
		
		if($aclAction || $isUserFlagPrivay) {
			if($isUserFlagPrivay && !empty($user->organization['Organization']['hasUserFlagPrivacy']) && $user->organization['Organization']['hasUserFlagPrivacy']=='Y')
				$str .= '<button data-attr-organization-id="'.$results['Organization']['id'].'" type="button" class="actionUserFlagPrivacy btn btn-danger" data-dismiss="modal" style="float:right;margin-bottom:5px;margin-right:5px;">'.__('SubmitUserFlagPrivacy').'</button>';
			if($aclAction && !empty($user->organization['Organization']['hasUserRegistrationExpire']) && $user->organization['Organization']['hasUserRegistrationExpire']=='Y')
				$str .= '<button data-attr-organization-id="'.$results['Organization']['id'].'" type="button" class="actionUserRegistrationExpire btn btn-danger" data-dismiss="modal" style="float:right;margin-bottom:5px;">'.__('SubmitUserRegistrationExpire').'</button>';
		}
		
		$str .= '<div class="table-responsive"><table class="table table-hover">';
		$str .= '<tr>';
		$str .= '<th>'.__('N').'</th>';
		//$str .= '<th>Codice</th>';
		$str .= '<th></th>';
		$str .= '<th>'.__('Nominative').'</th>';
		//$str .= '<th>'.__('Username').'</th>';
		//$str .= '<th>'.__('Mail').'</th>';
		$str .= '<th>'.__('Contacts').'</th>';
		$str .= '<th>'.__('dataRichEnter').'</th>';
		$str .= '<th>'.__('RegisterDate').'</th>';
		$str .= '<th>'.__('LastvisitDate').'</th>';
		$str .= '<th>'.__('LastCart').'</th>';
		if($aclAction || $isUserFlagPrivay) {
			if(!empty($user->organization['Organization']['hasUserFlagPrivacy']) && $user->organization['Organization']['hasUserFlagPrivacy']=='Y')
				$str .= '<th>'.__('HasUserFlagPrivacyQuestionShort').'</th>';
			if(!empty($user->organization['Organization']['hasUserRegistrationExpire']) && $user->organization['Organization']['hasUserRegistrationExpire']=='Y')
				$str .= '<th>'.__('HasUserRegistrationExpireQuestionShort').'</th>';
			$str .= '<th>'.__('HasUserBlockQuestionShort');
			$str .= '<span style="float:right;">'.$this->drawTooltip(__('User Block'), __('toolTipUserBlock'),$type='INFO',$pos='LEFT').'</span>';
			$str .= '</th>';
			$str .= '<th>';
			if($aclAction)
				__('nota');
			echo '</th>';
			// $str .= '<th class="actions">'.__('Actions').'</th>';
		}
		$str .= '</tr>';
		if(isset($results['User']))
		foreach ($results['User'] as $numResult => $result) {
			
			// $this->dd($result);
			
			if(!isset($result['Profile']['hasUserFlagPrivacy']))
				$result['Profile']['hasUserFlagPrivacy'] = 'N';
				
			if(!isset($result['Profile']['hasUserRegistrationExpire']))
				$result['Profile']['hasUserRegistrationExpire'] = 'N';
					
			if ($result['User']['block'] == 0)
				$result['User']['block'] = 'Y';
			else
				$result['User']['block'] = 'N';	
				
			if(!empty($result['User']['lastvisitDate']) && $result['User']['lastvisitDate']!=Configure::read('DB.field.datetime.empty')) 
				$lastvisitDate = $this->Time->i18nFormat($result['User']['lastvisitDate'],"%e %b %Y");
			else 
				$lastvisitDate = "";
		
			if(!empty($result['Cart']['date']) && $result['Cart']['date']!=Configure::read('DB.field.datetime.empty')) 
				$lastCartDate = $this->Time->i18nFormat($result['Cart']['date'],"%e %b %Y");
			else 
				$lastCartDate = "";
			
			if(!empty($result['Profile']['nota']))
				$img_nota = Configure::read('App.img.cake').'/actions/32x32/playlist.png';
			else
				$img_nota = Configure::read('App.img.cake').'/actions/32x32/filenew.png';
			
			$str .= '<tr class="view">';
			$str .= '<td>'.((int)$numResult+1).'</td>';
			$str .= '<td>'.$this->drawUserAvatar($user, $result['User']['id'], $result['User']).'</td>';
			$str .= '<td>';
			if(!empty($result['Profile']['codice']))
				$str .= $result['Profile']['codice'].'<br />';
			$str .= $result['User']['name'].'<br />';
			$str .= $result['User']['username'].'<br />';
			if(!empty($result['Profile']['cf']))
				$str .= $result['Profile']['cf'].'<br />';
			$str .= '</td>';
			$str .= '<td>'; 	
			if(!empty($result['User']['email'])) 
				$str .= '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a><br />';
			if(!empty($result['Profile']['address'])) $str .= $result['Profile']['address'].'<br />';
			if(!empty($result['Profile']['phone'])) $str .= $result['Profile']['phone'].'<br />';
			if(!empty($result['Profile']['phone2'])) $str .= $result['Profile']['phone2'].'<br />';
			$str .= '</td>';
			
                    
            $str .= '<td>';
			
			$str .= '<img alt="" src="' . Configure::read('App.img.cake') . '/blank32x32.png" id="submitEcomm-' . $result['User']['id'] . '" class="buttonCarrello submitEcomm" />';
			$str .= '<div id="msgEcomm-' . $result['User']['id'] . '" class="msgEcomm"></div>';
					
			$str .= $this->Form->input('DataRichEnter' . $result['User']['id'], ['id' => 'UserDataRichEnter' . $result['User']['id'], 'label' => false, 'type' => 'text',  'class' => 'callUpdateDate', 'value' => $this->Time->i18nFormat($result['Profile']['dataRichEnter'], "%e-%m-%Y"), 
																				  'data-attr-organization_id' => $results['Organization']['id'], 'data-attr-user_id' => $result['User']['id'], 'data-attr-field_db' => 'dataRichEnter']);
            $str .= $this->Ajax->datepicker('UserDataRichEnter' . $result['User']['id'], ['dateFormat' => 'dd-mm-yy', 'altField' => '#dataRichEnterDb_' . $result['User']['id'], 'altFormat' => 'yy-mm-dd']);
            $str .= '<input type="hidden" id="dataRichEnterDb_' . $result['User']['id'] . '" name="data[User][UserDataRichEnterDb_' . $result['User']['id'] . ']" value="' . $result['Profile']['dataRichEnter'] . '" />';
            $str .= '</td>';		
			$str .= '<td>'.$this->Time->i18nFormat($result['User']['registerDate'],"%e %b %Y").'</td>';
			$str .= '<td>'.$lastvisitDate.'</td>';
			$str .= '<td>'.$lastCartDate.'</td>';
			if($aclAction || $isUserFlagPrivay) {
				if(!empty($user->organization['Organization']['hasUserFlagPrivacy']) && $user->organization['Organization']['hasUserFlagPrivacy']=='Y') {

					if($result['Profile']['hasUserFlagPrivacy']=='Y')
						$title = __('HasUserFlagPrivacyQuestion').' il '.$result['Profile']['dataUserFlagPrivacy'];
					else
						$title = __('HasUserFlagPrivacyQuestionN');
						
					if($isUserFlagPrivay) {
						$str .= '<td style="cursor:pointer;" data-attr-user-id="'.$result['User']['id'].'" data-attr-organization-id="'.$result['User']['organization_id'].'" data-attr-field="hasUserFlagPrivacy" class="userProfileUpdate stato_'.$this->traslateEnum($result['Profile']['hasUserFlagPrivacy']).'" title="'.$title.'" ></td>';
					}
					else {
						// solo lettura
						$str .= '<td class="stato_'.$this->traslateEnum($result['Profile']['hasUserFlagPrivacy']).'" title="'.$title.'" ></td>';						
					}	
				}

				if($aclAction && !empty($user->organization['Organization']['hasUserRegistrationExpire']) && $user->organization['Organization']['hasUserRegistrationExpire']=='Y') {
					if($result['Profile']['hasUserRegistrationExpire']=='Y')
						$title = __('HasUserRegistrationExpireQuestion');
					else
						$title = __('HasUserRegistrationExpireQuestionN');				
					$str .= '<td style="cursor:pointer;" data-attr-user-id="'.$result['User']['id'].'" data-attr-organization-id="'.$result['User']['organization_id'].'" data-attr-field="hasUserRegistrationExpire" class="userProfileUpdate stato_'.$this->traslateEnum($result['Profile']['hasUserRegistrationExpire']).'" title="'.$title.'" ></td>';
				}
				/*
				else {
					// solo lettura
					$str .= '<td class="stato_'.$this->traslateEnum($result['Profile']['hasUserRegistrationExpire']).'" title="'.$title.'" ></td>';
				}
				*/
				
				if($aclAction)
					$str .= '<td style="cursor:pointer;" data-attr-user-id="'.$result['User']['id'].'" data-attr-organization-id="'.$result['User']['organization_id'].'" data-attr-field="block" class="userUpdate stato_'.$this->traslateEnum($result['User']['block']).'" title="'.__('HasUserBlockQuestion').'" ></td>';
				else  // solo lettura
					$str .= '<td class="stato_'.$this->traslateEnum($result['User']['block']).'" title="'.__('HasUserBlockQuestion').'" ></td>';
				/*
				$str .= '<td class="actions-table-img">';
				$str .= $this->Html->link(null, Configure::read('App.server').'/administrator/index.php?option=com_users&task=user.edit&id='.$result['User']['id'],['class' => 'action actionEdit','title' => __('Edit')]);
				$str .= '</td>';
				*/

				$str .= '<td>';
				if($aclAction)
					$str .= '<img id="notaUser-'.$result['User']['id'].'" data-attr-user-id="'.$result['User']['id'].'" data-attr-organization-id="'.$result['User']['organization_id'].'" style="cursor:pointer;" class="notaUser" alt="Aggiungi una nota" src="'.$img_nota.'"></span>';
				$str .= '</td>';					
			}		
			$str .= '</tr>';				
		}
		$str .= '</table></div>';
	 
		return $str;
	}
}		
?>