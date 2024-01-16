<?php
echo '<table>';
echo '<tr>';
echo '<th>'.__('Role').'</th>';
echo '<th>'.__('Description').'</th>';
echo '<th style="text-align:center">Utenti<br />associati</th>';
echo '<th>'.__('Actions').'</th>';
echo '</tr>';

foreach ($userGroups as $group_id => $data) {
	
	echo '<tr>';
	echo '<td>';
	echo $data['name'];
	echo '</td>';
	echo '<td>';
	echo $data['descri'];
	echo '</td>';
	echo '<td style="text-align:center">';
	echo $data['tot_users'];
	echo '</td>';
	echo '<td class="actions-table-img">';
	if(empty($data['join']))	
		echo $this->Html->link(null, array('controller' => 'UserGroupMaps', 'action' => 'index', null, 'group_id='.$group_id), ['class' => 'action actionEdit','title' => __('Edit')]);
	else
	if($data['join']=='Supplier')	
		echo $this->Html->link(null, array('controller' => 'SuppliersOrganizationsReferents', 'action' => 'index', null, 'group_id='.$group_id), ['class' => 'action actionEdit','title' => __('Edit')]);
	else
	if($data['join']=='neo') {
		echo $this->Html->link(null, ['controller' => 'Connects', 'action' => 'index', 'c_to' => 'admin/user-groups&a_to=index&group_id='.$group_id], ['target' => '_blank', 'class' => 'action actionEdit','title' => __('Edit')]);
	}	 	
	echo '</td>';
	echo '</tr>';
}
echo '</table>';
?> 