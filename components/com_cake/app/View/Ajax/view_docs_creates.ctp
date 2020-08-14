<?php
$this->App->d($results);
?>
<div class="related">

<?php if (!empty($results)): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('N');?></th>
		<th></th>
		<th><?php echo __('Nominative');?></th>
		<th><?php echo __('Username');?></th>
		<th><?php echo __('Mail');?></th>
		<th><?php echo __('Num');?></th>
		<th><?php echo __('Year');?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>	
	<?php
	foreach ($results as $numResult => $result):		
	?>
		<tr>
			<td><?php echo ((int)$numResult+1);?></td>
			<td><?php echo $this->App->drawUserAvatar($user, $result['User']['id'], $result['User']); ?></td>
			<td><?php echo $result['User']['name']; ?></td>
			<td><?php echo $result['User']['username']; ?></td>
			<td><?php echo '<a href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a></td>';
			echo '<td>';
			if($result['DocsCreateUser']['num']==0)
				echo '';
			else
				echo $result['DocsCreateUser']['num'];
			echo '</td>';
			echo '<td>';
			if($result['DocsCreateUser']['year']==0)
				echo '';
			else
				echo $result['DocsCreateUser']['year'];
			echo '</td>';
			echo '<td>';
			echo $this->Html->link(null, array('controller' => 'DocsCreates', 'action' => 'pdf_create', null, 'doc_id='.$result['DocsCreateUser']['doc_id'].'&user_id='.$result['User']['id'].'&format=notmpl'),array('class' => 'action actionPdf','title' => __('Preview'), 'target' => '_blank'));
			echo '</td>';
		echo '</tr>';
	endforeach; 
	echo '</table>';

else: 
	echo $this->element('boxMsg',array('class_msg' => 'notice'));
endif; ?>
</div>