<?php
if($isManager) 
	$colspan = '10';
else
	$colspan = '8';

echo '<div class="users">';
echo '<h2 class="ico-users">';
echo __('Users Block');
echo '</h2>';

if(!empty($results)) {
	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '<th></th>';
	echo '<th>'.__('N').'</th>';
	echo '<th>Codice</th>';
	echo '<th></th>';
	echo '<th>'.$this->Paginator->sort('Nominativo').'</th>';
	echo '<th>'.$this->Paginator->sort('Username').'</th>';
	echo '<th>'.$this->Paginator->sort('Mail').'</th>';
	echo '<th>Contatti</th>';
	echo '<th>'.$this->Paginator->sort('Registrato il').'</th>';
	echo '<th>'.$this->Paginator->sort('Ultima visita').'</th>';
	if($isManager) {
		echo '<th>Config</th>';
		echo '<th class="actions">'.__('Actions').'</th>';
	}
	echo '</tr>';
	
	foreach ($results as $numResult => $result) {
	
		$numRow = ((($this->Paginator->counter(['format'=>'{:page}'])-1) * $SqlLimit) + $numResult+1); 
		
		if(!empty($result['User']['lastvisitDate']) && $result['User']['lastvisitDate']!=Configure::read('DB.field.datetime.empty')) 
			$lastvisitDate = $this->Time->i18nFormat($result['User']['lastvisitDate'],"%e %b %Y");
		else 
			$lastvisitDate = "";
		
		echo '<tr class="view">';
		echo '<td><a action="user_block-'.$result['User']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
		echo '<td>'.$numRow.'</td>';
		echo '<td>'.$result['Profile']['codice'].'</td>';
		echo '<td>'.$this->App->drawUserAvatar($user, $result['User']['id'], $result['User']).'</td>';
		echo '<td>'.$result['User']['name'].'</td>';
		echo '<td>'.$result['User']['username'].'</td>';
		echo '<td>'; 	
		if(!empty($result['User']['email'])) 
			echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a><br />';
		echo '</td>';

		echo '<td>';
		if(!empty($result['Profile']['address'])) echo $result['Profile']['address'].'<br />';
		if(!empty($result['Profile']['phone'])) echo $result['Profile']['phone'].'<br />';
		if(!empty($result['Profile']['phone2'])) echo $result['Profile']['phone2'].'<br />';
		echo '</td>';
		
		echo '<td>'.$this->Time->i18nFormat($result['User']['registerDate'],"%e %b %Y").'</td>';
		echo '<td>'.$lastvisitDate.'</td>';
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
			echo $this->Html->link(null, Configure::read('App.server').'/administrator/index.php?option=com_users&task=user.edit&id='.$result['User']['id'],['class' => 'action actionEdit','title' => __('Edit')]);
			echo '</td>';
		}		
		echo '</tr>';
		echo '<tr class="trView" id="trViewId-'.$result['User']['id'].'">';
		echo '<td colspan="2"></td>';
		echo '<td colspan="'.$colspan.'" id="tdViewId-'.$result['User']['id'].'"></td>';
		echo '</tr>';
	}
	echo '</table></div>';
	}
	else
	echo $this->element('boxMsg',['class_msg' => 'message resultsNotFound', 'msg' => __('msg_search_not_result')]);	

	echo '<p>';
	echo $this->Paginator->counter(['format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')]);
	echo '</p>';

	echo '<div class="paging">';
	echo $this->Paginator->prev('< ' . __('previous'), [], null, ['class' => 'prev disabled']);
	echo $this->Paginator->numbers(['separator' => '']);
	echo $this->Paginator->next(__('next') . ' >', [], null, ['class' => 'next disabled']);
	echo '</div>';
echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {
	$('.reset').click(function() {
		$('#FilterUserUsername').val('');	
		$('#FilterUserName').val('');	
	});
});
</script>