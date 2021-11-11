<div class="legenda legenda-ico-info" style="float:right;">
	<table>
		<?php
		echo '<tr>';
		foreach ($usersGroups as $group_id => $data) {
			echo '<th>'.$data['name'].'</th>';
		}	
		echo '</tr>';
		echo '<tr>';
		foreach ($usersGroups as $group_id => $data) {
			echo '<td>'.$data['descri'].'</td>';
		}	
		echo '</tr>';
		?>
	</table>
</div>