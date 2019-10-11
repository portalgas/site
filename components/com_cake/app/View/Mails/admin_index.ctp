<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Mails'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="mails">
	<h2 class="ico-config">
		<?php echo __('List Mails');?>
	</h2>
  
	<div class="table-responsive"><table class="table table-hover">
	<tr>
			<?php
			if($isRoot || $isManager)
				echo '<th>'.$this->Paginator->sort('User.name').'</th>';
			?>
			<th><?php echo $this->Paginator->sort('mittente');?></th>
			<th><?php echo $this->Paginator->sort(__('dest_options'));?></th>
			<th><?php echo $this->Paginator->sort(__('dest_options_qta'));?></th>
			<th><?php echo $this->Paginator->sort('subject');?></th>
			<th>Testo della mail</th>
			<th>Allegato</th>
			<th><?php echo $this->Paginator->sort('created');?></th>
			<?php
			echo '<th>'.__('Actions').'</th>';
			?>			
	</tr>
	<?php
	foreach ($results as $numResult => $result):
		echo '<tr>';
		if($isRoot || $isManager)
			echo '<td>'.$result['User']['name'].'</td>';
		?>		
		<td><?php echo $result['Mail']['mittente']; ?></td>
		<td><?php echo $this->App->traslateEnum($result['Mail']['dest_options']); ?></td>
		<td><?php echo $this->App->traslateEnum($result['Mail']['dest_options_qta']); ?></td>
		<td><?php echo $result['Mail']['subject']; ?></td>
		<td style="cursor:pointer;"><?php 
		if(!empty($result['Mail']['body'])) {
			if(strlen($result['Mail']['body']) > 150) 
				$intro = strip_tags(substr($result['Mail']['body'], 0, 150)).'<b>...</b>';
			else
				$intro = $result['Mail']['body'];
			
			echo '<span title="'.strip_tags($result['Mail']['body']).'">'.$intro.'</span>';
		}
		
		echo '</td>';
		echo '<td>';
		if(!empty($result['Mail']['allegato'])) {
			$ico = $this->App->drawDocumentIco($result['Mail']['allegato']);
			echo '<img style="cursor:pointer;" src="'.$ico.'" title="'.$result['Mail']['allegato'].'" />';
		}
		echo '</td>';
		
		echo '<td style="white-space: nowrap;">';
		echo $this->App->formatDateCreatedModifier($result['Mail']['created']);
		echo '</td>';
		
		
		echo '<td  class="actions-table-img">';
		echo $this->Html->link(null, array('action' => 'send', null, 'id='.$result['Mail']['id']),array('class' => 'action actionCopy','title' => __('Copy')));
		if($isRoot) 
			echo $this->Html->link(null, array('action' => 'delete', $result['Mail']['id']),array('class' => 'action actionDelete','title' => __('Delete')));
		echo '</td>';			
	
			
	echo '</tr>';
endforeach;

echo '</table></div>';
echo '<p>';

	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>

	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>