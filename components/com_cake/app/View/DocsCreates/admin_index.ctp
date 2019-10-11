<?php
$this->App->d($results);
?>
<div class="DocsCreates">
	<h2 class="ico-export-docs">
		<?php echo __('DocsCreates');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('New DocsCreate'), array('controller' => 'DocsCreates', 'action' => 'add'), array('class' => 'action actionAdd','title' => __('New DocsCreate'))); ?></li>
			</ul>
		</div>
	</h2>
	
	<?php
	echo $this->element('legendaDocsCreatesLastNum', array('num_last' => $num_last));
	echo '<div class="clearfix"></div>';
	
	if(!empty($results)) {
	?>
		<div class="table-responsive"><table class="table table-hover">
		<tr>
			<th></th>
			<th><?php echo $this->Paginator->sort('Id');?></th>
			<th><?php echo $this->Paginator->sort('Name');?></th>	
			<th><?php echo $this->Paginator->sort('Body');?></th>	
			<th><?php echo $this->Paginator->sort('Date');?></th>
			<th style="text-align:center;"><?php echo __('totale utenti');?></th>	
			<th style="text-align:center;"><?php echo $this->Paginator->sort('stato',__('Stato'));?></th>
			<th><?php echo $this->Paginator->sort('Created');?></th>
			<th style="text-align:center;">Inviato</th>
			<th class="actions"><?php echo __('Actions');?></th>			
		</tr>
		<?php	
		foreach ($results as $numResult => $result) {

				echo '<tr valign="top">';
				echo '<td><a action="docs_creates-'.$result['DocsCreate']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
				echo '<td>';
				echo ($numResult+1); 
				echo '</td>';

				echo '<td>';
				echo $result['DocsCreate']['name'];
				echo '</td>';
				
				echo '<td>';
				echo $result['DocsCreate']['txt_testo'];
				echo '</td>';
				
				echo '<td>';
				echo $this->Time->i18nFormat($result['DocsCreate']['txt_data'],"%A %e %B %Y");
				echo '</td>';

				echo '<td style="text-align:center;">';
				echo count($result['DocsCreateUser']);
				echo '</td>';
				echo '<td class="stato_'.$this->App->traslateEnum($result['DocsCreate']['stato']).'" title="'.__('toolTipStato').'" ></td>';
				echo '<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['DocsCreate']['created']).'</td>';
				echo '<td style="white-space: nowrap;text-align:center;">';
				if($result['DocsCreate']['mail_send_data']==Configure::read('DB.field.datetime.empty')) 
					echo 'Da inviare';
				else
					echo $this->Time->i18nFormat($result['DocsCreate']['mail_send_data'],"%A %e %B %Y");
				echo '</td>';
				echo '<td class="actions-table-img-5">';
				if($result['DocsCreate']['mail_send_data']==Configure::read('DB.field.datetime.empty')) {
					echo $this->Html->link(null, array('action' => 'edit', null, 'doc_id='.$result['DocsCreate']['id']),array('class' => 'action actionEdit','title' => __('Edit'))); 
					if($result['DocsCreate']['stato']=='Y' && count($result['DocsCreateUser'])>0)
						echo $this->Html->link(null, array('action' => 'mail', null, 'doc_id='.$result['DocsCreate']['id']),array('class' => 'action actionMail','title' => __('MailSend'))); 
					echo $this->Html->link(null, array('action' => 'delete', null, 'doc_id='.$result['DocsCreate']['id']),array('class' => 'action actionDelete','title' => __('Delete'))); 
					echo $this->Html->link(null, array('action' => 'pdf_create', null, 'doc_id='.$result['DocsCreate']['id'].'&user_id='.$user->id.'&format=notmpl'),array('class' => 'action actionPdf','title' => __('Preview'), 'target' => '_blank')); 
				}
				else {
					if($result['DocsCreate']['stato']=='Y' && count($result['DocsCreateUser'])>0)
						echo $this->Html->link(null, array('action' => 'pdf_print_all', null, 'doc_id='.$result['DocsCreate']['id']),array('class' => 'action actionPdf','title' => __('PrintAllDocs')));				
				}
				echo '</td>';
				echo '</tr>';
				echo '<tr class="trView" id="trViewId-'.$result['DocsCreate']['id'].'">';
				echo '	<td colspan="2"></td>';
				echo '	<td colspan="8" id="tdViewId-'.$result['DocsCreate']['id'].'"></td>';
				echo '</tr>';
		
		} 
		echo '</table></div>';
		
		
		echo '<p>';
		echo $this->Paginator->counter(array(
		'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
		));
		echo '</p>';
	
		echo '<div class="paging">';

		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
		
		echo '</div>';

	} 
	else  
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => __('msg_search_not_result')));

echo '</div>';
?>