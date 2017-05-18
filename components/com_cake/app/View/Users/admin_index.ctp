<?php
if($isManager) 
	$colspan = '10';
else
	$colspan = '8';
?>
<div class="users">

	<h2 class="ico-users">
		<?php echo __('Users');?>
	</h2>


	<?php echo $this->Form->create('Filteruser',array('id'=>'formGasFilter','type'=>'get'));?>
		<fieldset class="filter">
			<legend><?php echo __('Filter Users'); ?></legend>
			<table>
				<tr>
					<td>
						<?php	echo $this->Ajax->autoComplete('FilterUserUsername', 
									   Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteUsers_username&format=notmpl',
										array('label' => 'Username','name'=>'FilterUserUsername','value'=>$FilterUserUsername,'size'=>'50','escape' => false));
						?>
					</td>
					<td>
						<?php echo $this->Ajax->autoComplete('FilterUserName', 
									Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteUsers_name&format=notmpl',
									array('label' => 'Nominativo','name'=>'FilterUserName','value'=>$FilterUserName,'size'=>'50','escape' => false));
						?>
					</td>
					<?php 
					/* td>
						<?php  // echo $this->Form->reset('Reset', array('value' => 'Reimposta','class' => 'reset')); ?>
					</td
					*/
					?>
					<td>
						<?php echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'class' => 'filter','div' => array('class' => 'submit filter', 'style' => 'display:none'))); ?>
					</td>
				</tr>	
				<tr>
					<td colspan="3">
						<?php 
						$arrFilterUserUserGroups = split(',',$FilterUserUserGroups);
						
						foreach ($userGroups as $group_id => $label) {
							echo '<div style="clear: none;float:left;margin-right:10px;">';
							echo '<input type="checkbox" name="userGroups" value="'.$group_id.'" ';
							if(in_array($group_id, $arrFilterUserUserGroups)) echo 'checked';
							echo ' />';
							echo '<label for="userGroups'.$group_id.'">';
							echo $label;
							echo '</label>';
							echo '</div>';
						}
						echo '<input type="hidden" value="" name="FilterUserUserGroups" />';
						?>					
					</td>
				</tr>
			</table>
		</fieldset>
	<?php echo $this->Form->end();

	if(!empty($results)) {
	?>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th></th>
			<th><?php echo __('N');?></th>
			<th>Codice</th>
			<th></th>
			<th><?php echo __('Nominativo');?></th>
			<th><?php echo __('Username');?></th>
			<th><?php echo __('Mail');?></th>
			<th>Contatti</th>
			<th><?php echo __('Registrato il');?></th>
			<th><?php echo __('Ultima visita');?></th>
			<?php
			if($isManager) {
				echo '<th>Config</th>';
				echo '<th class="actions">'.__('Actions').'</th>';
			}
	echo '</tr>';
	
	foreach ($results as $numResult => $result):
	
		if(!empty($result['User']['lastvisitDate']) && $result['User']['lastvisitDate']!='0000-00-00 00:00:00') 
			$lastvisitDate = $this->Time->i18nFormat($result['User']['lastvisitDate'],"%e %b %Y");
		else 
			$lastvisitDate = "";
		?>
	<tr class="view">
		<td><a action="suppliers_organizations_referents-<?php echo $result['User']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
		<td><?php echo ($numResult+1);?></td>
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
<?php 
endforeach;
echo '</table>';
	}
	else
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud'));	
?>	
</div>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('#formGasFilter').submit(function() {
	
		var userGroupIds = "";
		jQuery("input[name='userGroups']").each(function() {
		  if(jQuery(this).is(":checked")) {
		     userGroupId = jQuery(this).val();
		     userGroupIds += userGroupId+",";
		  } 
		});
		
		if(userGroupIds!="")  {
			userGroupIds = userGroupIds.substring(0,(userGroupIds.length-1));
			jQuery('input[name=FilterUserUserGroups]').val(userGroupIds);	
		}
		return true;
	});

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