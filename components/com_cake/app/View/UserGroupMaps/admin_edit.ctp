<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('UserGroups'),array('controller' => 'UserGroupMaps', 'action' => 'intro'));
$this->Html->addCrumb(__('Gest').' '.$userGroups[$group_id]['name']);
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="tesoriere form" style="min-height:400px;">
<?php echo $this->Form->create('UserGroupMap', array('id' => 'formGas'));?>
	<fieldset>
		<legend><?php echo __('Gest').' '.$userGroups[$group_id]['name']; ?></legend>
		<?php
		echo $this->Form->input('users', array('id' => 'user_id', 'class'=> 'selectpicker', 'data-live-search' => true));
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
		<li><?php echo $this->Html->link(__('List').' '.$userGroups[$group_id]['name'], array('action' => 'index', null, 'group_id='.$group_id),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<script type="text/javascript">
$(document).ready(function() {
	$('#formGas').submit(function() {
			
		var user_id = $('#user_id').val();
		if(user_id=='' || user_id==undefined) {
			alert("<?php echo __('jsAlertUserToRoleRequired');?> <?php echo $userGroups[$group_id]['name'];?>");
			$('#user_id').focus();
			return false;
		}

		return true;
	});	
});
</script>