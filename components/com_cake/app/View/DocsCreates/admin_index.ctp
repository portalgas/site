<?php
/*
echo "<pre>";
print_r($results);
echo "</pre>";
*/
?>
<div class="DocsCreates">
	<h2 class="ico-DocsCreates">
		<?php echo __('DocsCreates');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('New DocsCreate'), array('controller' => 'DocsCreates', 'action' => 'add'), array('class' => 'action actionAdd','title' => __('New DocsCreate'))); ?></li>
			</ul>
		</div>
	</h2>
	
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('Id');?></th>
			<th><?php echo $this->Paginator->sort('Name');?></th>	
			<th><?php echo $this->Paginator->sort('Text');?></th>	
			<th><?php echo $this->Paginator->sort('Date');?></th>
			<th style="text-align:center;"><?php echo __('totale utenti');?></th>	
			<th><?php echo $this->Paginator->sort('Created');?></th>
			<th class="actions"><?php echo __('Actions');?></th>			
	</tr>
	<?php	
	foreach ($results as $result) {

			echo '<tr valign="top">';
			echo '<td>';
			echo $result['DocsCreate']['id']; 
			echo '</td>';

			echo '<td>';
			echo $result['DocsCreate']['txt_name'];
			echo '</td>';
			
			echo '<td>';
			echo $result['DocsCreate']['txt_testo'];
			echo '</td>';
			
			echo '<td>';
			echo $result['DocsCreate']['txt_data'];
			echo '</td>';

			echo '<td style="text-align:center;">';
			echo count($result['DocsCreateUser']);
			echo '</td>';
			echo '<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['DocsCreate']['created']).'</td>';
			echo '<td class="actions-table-img-3">';
			echo $this->Html->link(null, array('action' => 'edit', null, 'doc_id='.$result['DocsCreate']['id']),array('class' => 'action actionEdit','title' => __('Edit'))); 
			echo $this->Html->link(null, array('action' => 'delete', null, 'doc_id='.$result['DocsCreate']['id']),array('class' => 'action actionDelete','title' => __('Delete'))); 
			echo '</td>';			
			echo '</tr>';
	} 
	
echo '</div>';