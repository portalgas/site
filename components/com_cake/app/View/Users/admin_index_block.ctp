<?php
if($isManager) 
	$colspan = '10';
else
	$colspan = '8';
?>
<div class="users">

	<h2 class="ico-users">
		<?php echo __('Users Block');?>
	</h2>
<?php
if(!empty($results)) {
?>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th></th>
			<th><?php echo __('N');?></th>
			<th>Codice</th>
			<th></th>
			<th><?php echo $this->Paginator->sort('Nominativo');?></th>
			<th><?php echo $this->Paginator->sort('Username');?></th>
			<th><?php echo $this->Paginator->sort('Mail');?></th>
			<th>Contatti</th>
			<th><?php echo $this->Paginator->sort('Registrato il');?></th>
			<th><?php echo $this->Paginator->sort('Ultima visita');?></th>
			<?php
			if($isManager) {
				echo '<th>Config</th>';
				echo '<th class="actions">'.__('Actions').'</th>';
			}
	echo '</tr>';
	
	foreach ($results as $i => $result):
	
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1); 
		
		if(!empty($result['User']['lastvisitDate']) && $result['User']['lastvisitDate']!='0000-00-00 00:00:00') 
			$lastvisitDate = $this->Time->i18nFormat($result['User']['lastvisitDate'],"%e %b %Y");
		else 
			$lastvisitDate = "";
		?>
	<tr class="view">
		<td><a action="user_block-<?php echo $result['User']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
		<td><?php echo $numRow;?></td>
		<td><?php echo $result['Profile']['codice']; ?></td>
		<td><?php echo $this->App->drawUserAvatar($user, $result['User']['id'], $result['User']); ?></td>
		<td><?php echo $result['User']['name']; ?></td>
		<td><?php echo $result['User']['username']; ?></td>
		<td><?php  	
			if(!empty($result['User']['email'])) echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a><br />';
		echo '</td>';
		echo '<td>';
		if(!empty($result['Profile']['address'])) echo $result['Profile']['address'].'<br />';
		if(!empty($result['Profile']['phone'])) echo $result['Profile']['phone'].'<br />';
		if(!empty($result['Profile']['phone2'])) echo $result['Profile']['phone2'].'<br />';
		echo '</td>';
		?>
		<td><?php echo $this->Time->i18nFormat($result['User']['registerDate'],"%e %b %Y");?></td>
		<td><?php echo $lastvisitDate;?></td>
		<?php
		if($isManager) {
			echo '<td>';
			echo '<span style="white-space:nowrap;" title="Gestisci gli articoli associati all\'ordine">Associaz. ';
			if($result['Profile']['hasArticlesOrder']=='Y')
				echo '<span style="color:green;">Si</span>';
			else 
				echo '<span style="color:red;">No</span>';
			echo '</span>';
						
			echo '</td>';
			echo '<td class="actions-table-img">';
			echo $this->Html->link(null, Configure::read('App.server').'/administrator/index.php?option=com_users&task=user.edit&id='.$result['User']['id'],array('class' => 'action actionEdit','title' => __('Edit')));
			echo '</td>';
		}
		?>		
	</tr>
	<tr class="trView" id="trViewId-<?php echo $result['User']['id'];?>">
		<td colspan="2"></td>
		<td colspan="<?php echo $colspan;?>" id="tdViewId-<?php echo $result['User']['id'];?>"></td>
	</tr>
<?php endforeach;
echo '</table>';
	}
	else
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud'));	
?>
	<p>
	<?php
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

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('.reset').click(function() {
		jQuery('#FilterUserUsername').val('');	
		jQuery('#FilterUserName').val('');	
	});
	<?php 
	/*
	 * devo ripulire il campo hidden che inizia per page perche' dopo la prima pagina sbaglia la ricerca con filtri
	 */
	?>
	jQuery('.filter').click(function() {
		jQuery("input[name^='page']").val('');
	});
});
</script>