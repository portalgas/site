<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List ProdGroups'), array('controller' => 'ProdGroups', 'action' => 'index'));
$this->Html->addCrumb(__('List ProdUsersGroups'),array('controller'=>'ProdUsersGroups','action'=>'index', $prodGroup['ProdGroup']['id']));
$this->Html->addCrumb(__('Add ProdUsersGroups'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="prod_users_groups form">
<?php echo $this->Form->create('ProdUsersGroup',array('id' => 'formGas'));?>
	<fieldset>
		<legend><?php echo __('Add ProdUsersGroups'); ?></legend>
		
	<?php
		if(!empty($users)) {
			echo $this->Form->select('master_user_id', $users, array('multiple' => true, 'size' =>10));
			
			echo $this->Form->select('user_id', array(), array('multiple' => true, 'size' => 10, 'style' => 'min-width:300px'));
			
			echo $this->Form->hidden('user_ids',array('id' => 'user_ids','value' => ''));
		
			echo '</fieldset>';
			
			echo $this->Form->hidden('prod_group_id', array('value' => $prodGroup['ProdGroup']['id']));
			echo $this->Form->end(__('Submit'));
		}
		else 
			echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono ancora utenti registrati"));
	?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List ProdUsersGroups'), array('controller'=>'ProdUsersGroups','action'=>'index', $prodGroup['ProdGroup']['id']),array('class'=>'action actionReload'));?></li>
	</ul>
</div>


<script type="text/javascript">     
$(document).ready(function() {
	$('#ProdUsersGroupMasterUserId').click(function() {
		$("#ProdUsersGroupMasterUserId option:selected" ).each(function (){			
			$('#ProdUsersGroupUserId').append($("<option></option>")
	         .attr("value",$(this).val())
	         .text($(this).text()));
	         
	         $(this).remove();
		});
	});
	
	$('#ProdUsersGroupUserId').click(function() {
		$("#ProdUsersGroupUserId option:selected" ).each(function (){			
			$('#ProdUsersGroupMasterUserId').append($("<option></option>")
	         .attr("value",$(this).val())
	         .text($(this).text()));
	         
	         $(this).remove();
		});
	});
	
	$('#formGas').submit(function() {
		var user_ids = '';
		$("#ProdUsersGroupUserId option" ).each(function (){	
			user_ids +=  $(this).val()+',';
		});
		user_ids = user_ids.substring(0,user_ids.length-1);
		
		if(user_ids=='') {
			alert("Devi selezionare almeno un utente");
			return false;
		}
		
		$('#user_ids').val(user_ids);
		
		return true;
	});
	
});
</script>