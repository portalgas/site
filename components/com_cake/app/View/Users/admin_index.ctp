<?php
$this->App->d($results);

echo $this->Html->script('moduleUsers-v02.min');

if($isManager) 
	$colspan = '12';
else
	$colspan = '10';

echo '<div class="users">';
echo '<h2 class="ico-users">';
echo __('Users');
echo '<div class="actions-img">';			
echo '	<ul>';
echo '		<li><a href="/administrator/index.php?option=com_users" class="action actionUser" title="Gestione completa">Gestione completa</a></li>';
echo '	</ul>';
echo '</div>';		
echo '</h2>';

echo $this->Form->create('Filteruser', ['id'=>'formGasFilter','type'=>'get']);
echo '<fieldset class="filter">';
echo '<legend>'.__('Filter Users').'</legend>';

echo '<div class="table-responsive"><table class="table">';
echo '	<tr>';
echo '		<td>';
echo $this->Ajax->autoComplete('FilterUserUsername', 
					   Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteUsers_username&format=notmpl',
						array('label' => 'Username','name'=>'FilterUserUsername','value'=>$FilterUserUsername,'size'=>'50','escape' => false));
echo '</td>';
echo '<td>';
echo $this->Ajax->autoComplete('FilterUserName', 
							Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteUsers_name&format=notmpl',
							array('label' => 'Nominativo','name'=>'FilterUserName','value'=>$FilterUserName,'size'=>'50','escape' => false));
echo '</td>';
echo '<td>';
echo $this->Form->input('block', ['label' => __('Stato'), 'options' => $block, 'name' => 'FilterUserBlock', 'default' => $FilterUserBlock, 'escape' => false]); 
echo '</td>';	
echo '<td>';
echo $this->Form->input('	can_login', ['label' => __('CanLogin'), 'options' => $can_logins, 'name' => 'FilterUserCanLogin', 'default' => $FilterUserCanLogin, 'escape' => false]); 
echo '</td>';	
echo '<td>';
echo $this->Form->input('sort', ['label' => __('Sort'), 'options' => $sorts, 'name' => 'FilterUserSort', 'default' => $FilterUserSort, 'escape' => false]);  
echo '</td>';

echo '<td>';
echo $this->Form->end(['label' => __('Filter'), 'class' => 'filter', 'class' => 'filter','div' => ['class' => 'submit filter', 'style' => 'display:none']]);
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="5">';
$arrFilterUserUserGroups = explode(',', $FilterUserUserGroups);

foreach ($userGroups as $group_id => $label) {
	echo '<label class="checkbox-inline">';
	echo '<input type="checkbox" name="userGroups" value="'.$group_id.'" ';
	if(in_array($group_id, $arrFilterUserUserGroups)) echo 'checked';
	echo ' />';							
	echo $label.'</label> ';
}
echo '<input type="hidden" value="" name="FilterUserUserGroups" />';
echo '</td>';
echo '</tr>';
echo '</table></div>';
echo '</fieldset>';
echo $this->Form->end();

if(!empty($results)) {
	?>
	<div class="table-responsive"><table class="table table-hover">
	<tr>
			<th></th>
			<th><?php echo __('N');?></th>
			<th>Codice</th>
			<th></th>
			<th><?php echo __('Nominative');?></th>
			<th><?php echo __('Username');?></th>
			<th><?php echo __('Mail');?></th>
			<th>Contatti</th>
			<th><?php echo __('RegisterDate');?></th>
			<th><?php echo __('LastvisitDate');?></th>
			<th><?php echo __('LastCart');?></th>
			<?php
			echo '<th>'.__('HasUserBlockQuestionShort');
			echo '<span style="float:right;">'.$this->App->drawTooltip(__('User Block'), __('toolTipUserBlock'), $type='INFO',$pos='LEFT').'</span>';
			echo '</th>';	
			echo '<th>'.__('HasUserCanLoginQuestionShort');
			echo '<span style="float:right;">'.$this->App->drawTooltip(__('User Can Login'), __('toolTipUserCanLogin'), $type='INFO',$pos='LEFT').'</span>';
			echo '</th>';	
			/*
			echo '<th>'.__('HasUserActivationQuestionShort');
			echo '<span style="float:right;">'.$this->App->drawTooltip(__('User Activation'), __('toolTipUserActivation'), $type='INFO',$pos='LEFT').'</span>';
			echo '</th>';
			*/			
			if($isManager) {
				echo '<th>'.__('nota').'</th>';
				echo '<th class="actions">'.__('Actions').'</th>';
			}
	echo '</tr>';
	
	foreach ($results as $numResult => $result):
		
		// debug($result);
		$can_login = $result['User']['can_login'];
		if($can_login) $can_login = 1; // sono invertiti
		else $can_login = 0; 

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

		$activation = $result['User']['activation'];
		if(empty($activation))
			$flag_activation = 0;
		else
			$flag_activation = 1;
		
		echo '<tr class="view">';
		echo '<td><a action="suppliers_organizations_referents-'.$result['User']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
		echo '<td>';
		echo ((int)$numResult+1);
		echo '</td>';
		echo '<td>';
		echo $result['Profile']['codice'];
		echo '</td>';
		echo '<td>';
		echo $this->App->drawUserAvatar($user, $result['User']['id'], $result['User']);
		echo '</td>';
		echo '<td>';
		echo $result['User']['name']; 
		echo '</td>';
		echo '<td>';
		echo $result['User']['username'];
		echo '</td>';
		echo '<td>';
		if(!empty($result['User']['email'])) echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a><br />';
		echo '</td>';
		echo '<td>';
		if(!empty($result['Profile']['address'])) echo $result['Profile']['address'].'<br />';
		if(!empty($result['Profile']['phone'])) echo $result['Profile']['phone'].'<br />';
		if(!empty($result['Profile']['phone2'])) echo $result['Profile']['phone2'].'<br />';
		echo '</td>';
		
		echo '<td>'.$this->Time->i18nFormat($result['User']['registerDate'],"%e %b %Y").'</td>';
		echo '<td>'.$lastvisitDate.'</td>';
		echo '<td>'.$lastCartDate.'</td>';

			
		if($isManager) {
			echo '<td style="cursor:pointer;" data-attr-user-id="'.$result['User']['id'].'" data-attr-field="block" class="userUpdateNoDES stato_'.$this->App->traslateEnum($result['User']['block']).'" title="'.__('HasUserBlockQuestion').'" ></td>';
			echo '<td style="cursor:pointer;" data-attr-user-id="'.$result['User']['id'].'" data-attr-field="can_login" class="userUpdateNoDES stato_'.$can_login.'" title="'.__('HasUserCanLoginQuestion').'" ></td>';
	
			/*
			echo '<td style="cursor:pointer;" data-attr-user-id="'.$result['User']['id'].'" data-attr-field="activation" class="userUpdateNoDES stato_'.$this->App->traslateEnum($flag_activation).'" title="'.__('HasUserActivationQuestion').'" ></td>';
			*/

			/*echo '<td>';
			echo '<span style="white-space:nowrap;" title="Gestisci gli articoli associati all\'ordine">Associaz. ';
			if($result['Profile']['hasArticlesOrder']=='Y')
				echo '<span style="color:green;">Si</span>';
			else 
				echo '<span style="color:red;">No</span>';
			echo '</span>';		
			echo '</td>';
			*/
			echo '<td>';
			echo '<img id="notaUser-'.$result['User']['id'].'" data-attr-user-id="'.$result['User']['id'].'" data-attr-organization-id="'.$result['User']['organization_id'].'" style="cursor:pointer;" class="notaUser" alt="Aggiungi una nota" src="'.$img_nota.'"></span>';
			echo '</td>';
					
			echo '<td class="actions-table-img">';
			echo $this->Html->link(__('Edit'), Configure::read('App.server').'/administrator/index.php?option=com_users&task=user.edit&id='.$result['User']['id'],array('class' => 'btn btn-primary','title' => __('Edit')));
			echo '</td>';			
		}
		else { // solo lettura
			echo '<td class="stato_'.$this->App->traslateEnum($result['User']['block']).'" title="'.__('HasUserBlockQuestion').'" ></td>';
			echo '<td class="stato_'.$this->App->traslateEnum($result['User']['can_login']).'" title="'.__('HasUserCanLoginQuestion').'" ></td>';
			// echo '<td class="stato_'.$this->App->traslateEnum($flag_activation).'" title="'.__('HasUserActivationQuestion').'" ></td>';
		}
		?>		
	</tr>
	<tr class="trView" id="trViewId-<?php echo $result['User']['id'];?>">
		<td colspan="2"></td>
		<td colspan="<?php echo $colspan;?>" id="tdViewId-<?php echo $result['User']['id'];?>"></td>
	</tr>
<?php 
endforeach;
echo '</table></div>';
	}
	else
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => __('msg_search_not_result')));	
?>	
</div>

<div id="dialogmodal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><?php echo __('User Nota');?></h4>
      </div>
      <div class="modal-body">
        <p><textarea class="noeditor" id="notaUser" name="nota" style="width: 100%;" rows="10"></textarea>
		<div class="clearfix"></div>
		</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo __('Close');?></button>
        <button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo __('Submit');?></button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
$(document).ready(function() {

	$('#formGasFilter').submit(function() {
	
		var userGroupIds = "";
		$("input[name='userGroups']").each(function() {
		  if($(this).is(":checked")) {
		     userGroupId = $(this).val();
		     userGroupIds += userGroupId+",";
		  } 
		});
		
		if(userGroupIds!="")  {
			userGroupIds = userGroupIds.substring(0,(userGroupIds.length-1));
			$('input[name=FilterUserUserGroups]').val(userGroupIds);	
		}
		return true;
	});

	$('.reset').click(function() {
		$('#FilterUserUsername').val('');	
		$('#FilterUserName').val('');	
	});
});
</script>