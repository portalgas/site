<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('Des'),array('controller' => 'Des', 'action' => 'index'));
$this->Html->addCrumb(__('List DesUserGroupMaps'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<table>';
echo '<tr>';
echo '<th>'.__('Role').'</th>';
echo '<th>'.__('Description').'</th>';
echo '<th style="text-align:center">Utenti del D.E.S.<br />associati</th>';
echo '<th style="text-align:center">Utenti del proprio G.A.S.<br />associati</th>';
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
	echo $data['tot_users_all_des'];
	echo '</td>';
	echo '<td style="text-align:center">';
	echo $data['tot_users'];
	echo '</td>';
	echo '<td class="actions-table-img">';
	if(empty($data['join']))	
		echo $this->Html->link(null, array('controller' => 'DesUserGroupMaps', 'action' => 'index', null, 'group_id='.$group_id),array('class' => 'action actionEdit','title' => __('Edit')));
	else
	if($data['join']=='DesSupplier')	
		echo $this->Html->link(null, array('controller' => 'DesSuppliersReferents', 'action' => 'index', null, 'group_id='.$group_id), array('class' => 'action actionEdit','title' => __('Edit')));
	echo '</td>';
	echo '</tr>';
}
echo '</table>';
?> 