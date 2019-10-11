<?php
$this->App->d($results);

echo $this->Html->script('moduleUsers-v02.min');

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('DesUsers'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="des">';
echo '<h2 class="ico-users">';		
echo __('Des').' '.$desResults['De']['name'];
echo '</h2>';


if(!empty($desOrganizationsResults)) {

	echo $this->Form->create('Filteruser', ['id' => 'formGasFilter', 'type' => 'get']);
	echo '<fieldset class="filter">';
	echo '<legend>'.__('Filter Users').'</legend>';
	echo '<div class="table-responsive"><table class="table">';
	echo '<tr>';
	echo '<td colspan="2">';
	echo $this->Ajax->autoComplete('FilterUserUsername', Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteDesUsers_username&format=notmpl', ['label' => 'Username', 'name' => 'FilterUserUsername', 'value' => $FilterUserUsername, 'size' => '50', 'escape' => false]);
	echo '</td>';
	echo '<td colspan="2">';
	echo $this->Ajax->autoComplete('FilterUserName', Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteDesUsers_name&format=notmpl', ['label' => 'Nominativo', 'name' => 'FilterUserName', 'value' => $FilterUserName, 'size' => '50', 'escape' => false]);
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
	echo $this->Form->input('hasUserFlagPrivacy', ['label' => __('HasUserFlagPrivacy'), 'options' => $hasUserFlagPrivacys, 'name' => 'FilterUserHasUserFlagPrivacy', 'default' => $FilterUserHasUserFlagPrivacy, 'escape' => false]); 
	echo '</td>';	
	echo '<td>';
	echo $this->Form->input('hasUserRegistrationExpire', ['label' => __('HasUserRegistrationExpire'), 'options' => $hasUserRegistrationExpires, 'name' => 'FilterUserHasUserRegistrationExpire', 'default' => $FilterUserHasUserRegistrationExpire, 'escape' => false]); 
	echo '</td>';	
	echo '<td>';
	echo $this->Form->input('sort', ['label' => __('Sort'), 'options' => $sorts, 'name' => 'FilterUserSort', 'default' => $FilterUserSort, 'escape' => false]);  
	echo '</td>';					
	echo '<td colspan="2">';
	echo $this->Form->end(['label' => __('Filter'), 'class' => 'filter', 'class' => 'filter', 'div' => ['class' => 'submit filter', 'style' => 'display:none']]); 
	echo '</td>';
	echo '</tr>	';
	echo '</table></div>';
	echo '</fieldset>';
	// echo $this->Form->end(); se chiudo non funziona Ajax->autoComplete!
	echo '</form>';

	echo '<div class="panel-group">';

	foreach ($desOrganizationsResults as $numResult0 => $desOrganizationsResult) {
		
		$tmp->user->organization['Organization'] = $desOrganizationsResult['Organization'];
		
		echo '  <div class="panel panel-primary">';
		echo '	<div class="panel-heading">';
		echo '	  <h4 class="panel-title">';
		echo '		<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$numResult0.'">';
		if($numResult0==0)
			echo '<i class="fa fa-lg fa-minus" aria-hidden="true"></i> ';
		else
			echo '<i class="fa fa-lg fa-plus" aria-hidden="true"></i> ';
		echo $desOrganizationsResult['Organization']['name'].'</a>';
		echo '	  </h4>';
		echo '	</div>';
		echo '	<div id="collapse'.$numResult0.'" class="panel-collapse collapse ';
		if($numResult0==0) echo 'in';
		echo '">';
		echo '<div class="panel-body">';
	
		echo '<form>';
		echo $this->element('boxUserFlagPrivacy', ['organization_id' => $tmp->user->organization['Organization']['id'], 'ctrlUserFlagPrivacys' => $desOrganizationsResult['UserFlagPrivacy']]);
		
		if(!empty($desOrganizationsResult['User'])) 
			echo $this->UsersFlagPrivacy->drawRow($tmp->user, $desOrganizationsResult, $isManagerUserDes, $isUserFlagPrivay);
		else 
			echo $this->element('boxMsg',['class_msg' => 'message resultsNotFound', 'msg' => __('msg_search_not_result')]);	
		echo '</form>';
		
		echo '	</div>'; // panel-body
		echo '	</div>'; // panel-collapse
		echo '	</div>'; // panel panel-primary
	}

	echo '	</div>'; // panel-group
} 
else  
	echo $this->element('boxMsg', ['class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono ancora GAS associati"]);
	
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
var action_back_controller = "DesUsers";
var action_back_action = "index";
</script>