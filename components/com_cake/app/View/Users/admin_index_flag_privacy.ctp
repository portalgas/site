<?php
$this->App->d($results);

echo $this->Html->script('moduleUsers-v02.min');

echo '<div class="users">';
echo '<h2 class="ico-users">';
if($user->organization['Organization']['hasUserFlagPrivacy']=='Y' && $user->organization['Organization']['hasUserRegistrationExpire']=='Y') 
  echo __('UsersFlagPrivacyAndRegistrationExpire');
else
if($user->organization['Organization']['hasUserFlagPrivacy']=='Y') 
  echo __('UsersFlagPrivacy');  
else
if($user->organization['Organization']['hasUserRegistrationExpire']=='Y')
  echo __('UsersRegistrationExpire'); 
echo '</h2>';
   
echo $this->Form->create('Filteruser', ['id' => 'formGasFilter', 'type' => 'get']);
echo '<fieldset class="filter">';
echo '<legend>'.__('Filter Users').'</legend>';
echo '<div class="table-responsive"><table class="table">';
echo '<tr>';
echo '<td colspan="2">';
echo $this->Ajax->autoComplete('FilterUserUsername', Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteUsers_username&format=notmpl', array('label' => 'Username', 'name' => 'FilterUserUsername', 'value' => $FilterUserUsername, 'size' => '50', 'escape' => false));
echo '</td>';
echo '<td colspan="2">';
echo $this->Ajax->autoComplete('FilterUserName', Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteUsers_name&format=notmpl', array('label' => 'Nominativo', 'name' => 'FilterUserName', 'value' => $FilterUserName, 'size' => '50', 'escape' => false));
echo '</td>';
echo '<td colspan="2">';
echo $this->Form->input('FilterUserProfileCF', ['label' => __('Cf'), 'name' => 'FilterUserProfileCF', 'value' => $FilterUserProfileCF, 'size' => '50', 'escape' => false]);
echo '</td>';					
echo '</tr>	';
echo '<tr>	';
echo '<td>';
echo $this->Form->input('block', ['label' => __('Stato'), 'options' => $block, 'name' => 'FilterUserBlock', 'default' => $FilterUserBlock, 'escape' => false]); 
echo '</td>';	
echo '<td>';
if($user->organization['Organization']['hasUserFlagPrivacy']=='Y') 
  echo $this->Form->input('hasUserFlagPrivacy', ['label' => __('HasUserFlagPrivacy'), 'options' => $hasUserFlagPrivacys, 'name' => 'FilterUserHasUserFlagPrivacy', 'default' => $FilterUserHasUserFlagPrivacy, 'escape' => false]); 
echo '</td>';	
echo '<td>';
if($user->organization['Organization']['hasUserRegistrationExpire']=='Y')
  echo $this->Form->input('hasUserRegistrationExpire', ['label' => __('HasUserRegistrationExpire'), 'options' => $hasUserRegistrationExpires, 'name' => 'FilterUserHasUserRegistrationExpire', 'default' => $FilterUserHasUserRegistrationExpire, 'escape' => false]); 
echo '</td>';	
echo '<td>';
echo $this->Form->input('sort', array('label' => __('Sort'), 'options' => $sorts, 'name' => 'FilterUserSort', 'default' => $FilterUserSort, 'escape' => false));  
echo '</td>';	
echo '<td colspan="2">';
echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); 
echo '</td>';
echo '</tr>	';
echo '</table></div>';
echo '</fieldset>';
// echo $this->Form->end(); se chiudo non funziona Ajax->autoComplete!
echo '</form>';

echo '<form>';

if($user->organization['Organization']['hasUserFlagPrivacy']=='Y') 
  echo $this->element('boxUserFlagPrivacy', ['organization_id' => $user->organization['Organization']['id'], 'ctrlUserFlagPrivacys' => $ctrlUserFlagPrivacys]);

if(!empty($results)) 
	echo $this->UsersFlagPrivacy->drawRow($user, $results, $isManager, $isUserFlagPrivay);
else 
	echo $this->element('boxMsg',['class_msg' => 'message resultsNotFound', 'msg' => __('msg_search_not_result')]);	
echo '</form>';
		
echo '</div>';
?>
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

<style>
.update {cursor:pointer}
</style>

<script type="text/javascript">
var jsAlertConfirmUserFlagPrivacy = "<?php echo __('jsAlertConfirmUserFlagPrivacy');?>";
var jsAlertConfirmUserRegistrationExpire = "<?php echo __('jsAlertConfirmUserRegistrationExpire');?>";
var action_back_controller = "Users";
var action_back_action = "index_flag_privacy";
</script>