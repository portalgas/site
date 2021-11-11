<?php
$this->App->d($results);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('Des'),array('controller' => 'Des', 'action' => 'index'));
$this->Html->addCrumb(__('List DesSuppliers'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<h2 class="ico-organizations">';
echo __('List DesSuppliers');
echo '<div class="actions-img">';
echo '<ul>';
echo '<li>'.$this->Html->link(__('Add DesSupplier'), array('controller' => 'DesSuppliers', 'action' => 'add'), array('class' => 'action actionAdd','title' => __('Add DesSupplier'))).'</li>';
echo '</ul>';
echo '</div>';
echo '</h2>';
?>
<style>
.cakeContainer ul {
    margin: 0;
    padding: 0;
}
.cakeContainer ul, .cakeContainer li {
	list-style: outside none none;
    margin: 0;
    padding: 0;
}
</style>
    
<?php
if(!empty($results)) {
	
	echo '<table cellpadding="0" cellspacing="0">';
	
	echo '<tr>';
	echo '	<th></th>';
	echo '	<th>'.__('N').'</th>';
	echo '	<th colspan="2">'.__('DesSupplier').'</th>';
	echo '	<th colspan="2">'.__('OwnOrganizationId').'</th>';
	echo '	<th>'.__('Titolari').'</th>';
	echo '	<th>'.__('DesSuppliersReferents').'</th>';
	echo '	<th>Ordini associati</th>';
	echo '	<th>'.__('Actions').'</th>';
	echo '</tr>';		
	
	foreach ($results as $numResult => $result) {
			
		echo '<tr>';
		echo '<td></td>';
		echo '<td>'.((int)$numResult+1).'</td>';
			
		echo '<td>';
		if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" />';	
		echo '</td>';			
		echo '<td>'.$result['Supplier']['name'];
		if(!empty($result['Supplier']['descrizione']))
			echo ' - '.$result['Supplier']['descrizione'];
		echo '</td>';
			
		/*
		 * GAS Titolare
		 */		
		if(empty($result['OwnOrganization']['id'])) {
			$msg = "Il produttore non ha un GAS titolare associato";
			echo '<td colspan="3">';
			echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => $msg));
			echo '</td>';
		}
		else {
			echo '<td>';
			echo '<img style="padding:0;width:25px" src="'.Configure::read('App.web.img.upload.content').'/'.$result['OwnOrganization']['img1'].'" alt="'.$result['OwnOrganization']['name'].'" />';
			echo '</td>';
			echo '<td>'.$result['OwnOrganization']['name'].'</td>';		
	
			/*
			 * Titolari
			 */
			echo '<td>';
			echo '<ul>';
			foreach ($result['DesSuppliersReferents'] as $numResult2 => $desSuppliersReferentsResult) {
				if($desSuppliersReferentsResult['DesSuppliersReferent']['group_id'] == Configure::read('group_id_titolare_des_supplier')) {
					echo '<li>';
					// echo '<img style="width:20px;padding:0px;" src="'.Configure::read('App.web.img.upload.content').'/'.$desSuppliersReferentsResult['Organization']['img1'].'" alt="'.$desSuppliersReferentsResult['Organization']['name'].'" />';
					echo $desSuppliersReferentsResult['User']['name'];
					// echo ' <b>'.__($desSuppliersReferentsResult['DesSuppliersReferent']['UserGroups']['name']).'</b>';
					echo '</li>';
				}
			}
			echo '</ul>';
			echo '</td>';
		}
			


		/*
		 * Referenti
		 */
		echo '<td>';
		echo '<ul>';
		foreach ($result['DesSuppliersReferents'] as $numResult2 => $desSuppliersReferentsResult) {
			if($desSuppliersReferentsResult['DesSuppliersReferent']['group_id'] == Configure::read('group_id_referent_des')) {
				echo '<li>';
				echo '<img style="width:20px;padding:0px;" src="'.Configure::read('App.web.img.upload.content').'/'.$desSuppliersReferentsResult['Organization']['img1'].'" alt="'.$desSuppliersReferentsResult['Organization']['name'].'" />';
				echo $desSuppliersReferentsResult['User']['name'];
				// echo ' <b>'.__($desSuppliersReferentsResult['DesSuppliersReferent']['UserGroups']['name']).'</b>';
				echo '</li>';
			}
		}
		echo '</ul>';
		echo '</td>';

		echo '<td>'.$result['DesOrder']['totali'].'</td>';
			
		echo '<td class="actions-table-img">';
		echo $this->Html->link(null, array('controller' => 'DesSuppliers', 'action' => 'delete', $result['DesSupplier']['id']),array('class' => 'action actionDelete','title' => __('Delete'))); 
		echo '</td>';		
		echo '</tr>';
		
		/*
		 *  GAS senza l'associazione con il produttore
		 */
		if(!$result['DesSupplier']['hasOrganizationsSupplier']) {
			$msg = '';			
			foreach ($result['DesOrganization'] as $numResult2 => $desSuppliersReferentsResult) 
				$msg .= 'Il G.A.S. '.$desSuppliersReferentsResult['Organization']['name'].' non ha il produttore associato<br />';
			
			echo '<tr>';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td colspan="8">';
			echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => $msg));
			echo '</td>';
			echo '</tr>';
							
		}		
	}	
	echo '</table>';	
	
} // end if(!empty($results)) 
?>