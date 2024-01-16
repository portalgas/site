<?php
$msg = "Elenco dei gasisti che non hanno alcuna referenza con un produttore ordinati per data di registrazione discendente";
echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => $msg));	

if(!empty($results)) {
	?>
	<div class="table-responsive"><table class="table table-hover">
	<tr>
			<th><?php echo __('N');?></th>
			<th></th>
			<th><?php echo __('Nominative');?></th>
			<th><?php echo __('Username');?></th>
			<th><?php echo __('Mail');?></th>
			<th><?php echo __('RegisterDate');?></th>
			<th><?php echo __('LastvisitDate');?></th>
			<?php		
			if($isManager) {
				echo '<th class="actions">'.__('Actions').'</th>';
			}
	echo '</tr>';
	
	foreach ($results as $numResult => $result):
		
		// debug($result);
		if(!empty($result['User']['lastvisitDate']) && $result['User']['lastvisitDate']!=Configure::read('DB.field.datetime.empty')) 
			$lastvisitDate = $this->Time->i18nFormat($result['User']['lastvisitDate'],"%e %b %Y");
		else 
			$lastvisitDate = "";
		
		echo '<tr class="view">';
		echo '<td>';
		echo ((int)$numResult+1);
		echo '</td>';
		echo '<td>';
		echo $this->App->drawUserAvatar($user, $result['User']['id'], $result['User']);
		echo '</td>';
		echo '<td>';
		echo $result['User']['name']; 
		echo '</td>';
		echo '<td>';
		echo $result['User']['username'];
		echo '</td>';
		echo '<td>';
		if(!empty($result['User']['email'])) echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a><br />';
		echo '</td>';
		
		echo '<td>'.$this->Time->i18nFormat($result['User']['registerDate'],"%e %b %Y").'</td>';
		echo '<td>'.$lastvisitDate.'</td>';

		if($isManager) {
			echo '<td class="actions-table-img">';
			echo $this->Html->link(__('Edit'), Configure::read('App.server').'/administrator/index.php?option=com_users&task=user.edit&id='.$result['User']['id'],array('class' => 'btn btn-primary','title' => __('Edit')));
			echo '</td>';			
		}
		?>		
	</tr>
<?php 
endforeach;
echo '</table></div>';
	}
	else
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => __('msg_search_not_result')));	
?>	
</div>