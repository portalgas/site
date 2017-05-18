<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('Des'),array('controller' => 'Des', 'action' => 'index'));
$this->Html->addCrumb(__('List DesUserGroupMaps'),array('controller' => 'DesUserGroupMaps', 'action' => 'intro'));
$this->Html->addCrumb(__('Gest').' '.$userGroups[$group_id]['name']);
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="tesoriere form" style="min-height:450px;">
<?php echo $this->Form->create('UserGroupMap', array('id' => 'formGas'));?>
	<fieldset>
		<legend><?php echo __('Gest').' '.$userGroups[$group_id]['name']; ?></legend>
		<?php
		$options = array('id' => 'user_id', 'class'=> 'selectpicker', 'data-live-search' => true); 
		echo $this->Form->input('users', $options);
		?>
	</fieldset>
<?php 
echo $this->Form->hidden('group_id',array('id' => 'group_id','value' => $group_id));
echo $this->Form->end(__('Submit'));
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List') /* .' '.$userGroups[$group_id]['name'] */ , array('action' => 'index', null, 'group_id='.$group_id),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#formGas').submit(function() {
			
		var user_id = jQuery('#user_id').val();
		if(user_id=='' || user_id==undefined) {
			alert("<?php echo __('jsAlertUserToRoleRequired');?> <?php echo $userGroups[$group_id]['name'];?>");
			jQuery('#user_id').focus();
			return false;
		}

		return true;
	});	
});
</script>