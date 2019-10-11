<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('Des'),array('controller' => 'Des', 'action' => 'index'));
$this->Html->addCrumb(__('List DesUserGroupMaps'),array('controller' => 'DesUserGroupMaps', 'action' => 'intro'));
$this->Html->addCrumb(__('List Users UserGroups').': '.$userGroups[$group_id]['name']);
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="users">
	
	<h2 class="ico-users">
		<?php echo $userGroups[$group_id]['name'].' <small><i>('.$userGroups[$group_id]['descri'].')</i></small>';?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('Gest').' '.$userGroups[$group_id]['name'], array('action' => 'edit', null, 'group_id='.$group_id),array('class' => 'action actionEdit','title' => __('Gest').' '.$userGroups[$group_id]['name'])); ?></li>
			</ul>
		</div>
	</h2>
	
<?php
if(!empty($results)) {
?>
	
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo __('N');?></th>
			<th><?php echo __('Nominative');?></th>
			<th><?php echo __('Username');?></th>
			<th><?php echo __('Mail');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($results as $numResult => $result):?>
	<tr>
		<td><?php echo ($numResult+1); ?></td>
		<td>		
			<?php echo $result['User']['name']; ?>
		</td>
		<td>
			<?php echo $result['User']['username']; ?>
		</td>
		<td>
			<?php echo $result['User']['email']; ?>
		</td>
		<td class="actions-table-img">
			<?php 
			echo $this->Html->link(null, array('action' => 'delete', $result['User']['id'], 'group_id='.$group_id), array('class' => 'action actionDelete','title' => __('Delete')));
			?>
		</td>		
	</tr>
<?php endforeach; ?>
	</table>
	
	<script type="text/javascript">
	$(document).ready(function() {
	
		$(".actionDelete").each(function () {
			$(this).click(function() {
				if(!confirm("Sei sicuro di voler eliminare l'utente dal ruolo <?php echo $userGroups[$group_id]['name'];?>?"))
					return false;
				else
					return true;
			});
		});
	});
	</script>
	
<?php
} // end if(!empty($results) 
else 
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => __('msg_search_not_result')));
	
?>
</div>