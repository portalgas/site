<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('Gest MonitoringOrders'), array('controller' => 'MonitoringOrders', 'action' => 'home'));
$this->Html->addCrumb(__('Gest MonitoringSuppliersOrganizations'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));


echo '<h2 class="ico-monitoring-suppliers-organization">';
echo __('Monitoring Suppliers Organizations');
echo '<div class="actions-img">';			
echo '	<ul>';
echo '		<li>'.$this->Html->link(__('Gest MonitoringSuppliersOrganizations'), array('action' => 'index'),array('class' => 'action actionAdd','title' => __('Gest MonitoringOrders'))).'</li>';
echo '	</ul>';
echo '</div>';
echo '</h2>';


if(!empty($results)) {
	
	echo '<table cellpadding = "0" cellspacing = "0">';
	echo '<tr>';
	echo '<th></th>';
	echo '<th>'.__('N').'</th>';
	echo '<th colspan="2">';
	echo __('Business name');
	echo '</th>';
	echo '<th>';
	echo __('Utente');
	echo '</th>';
	echo '<th style="text-align:center;">';
	echo __('MonitoringMailOrderDataFine');
	echo '</th>';
	/*echo '<th style="text-align:center;">';
	echo __('MonitoringMailOrderClose');
	echo '</th>';*/
	echo '<th>'.__('Suppliers Organizations Referents').'</th>';
	echo '</tr>';
	
	foreach ($results as $numResult => $result) {
		
		echo '<tr class="view">';
		echo '<td><a action="suppliers_organizations-'.$result['SuppliersOrganization']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
		echo '<td>'.($numResult+1).'</td>';	
		echo '	<td>';
		if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
			echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
		echo '	</td>';	
		echo '	<td>';
		echo $result['SuppliersOrganization']['name'];
		echo '	</td>';
		echo '	<td>';
		echo $result['User']['name'];
		echo '	</td>';
		echo '<td title="'.__('toolTipStato').'" class="stato_'.$this->App->traslateEnum($result['MonitoringSuppliersOrganization']['mail_order_data_fine']).'"></td>';
		echo '	</td>';
		//echo '<td title="'.__('toolTipStato').'" class="stato_'.$this->App->traslateEnum($result['MonitoringSuppliersOrganization']['mail_order_close']).'"></td>';
		//echo '</td>';
		echo '<td>';
		echo $this->app->drawListSuppliersOrganizationsReferents($user,$result['SuppliersOrganizationsReferent']);
		echo '</td>';				
		echo '</tr>';
		echo '<tr class="trView" id="trViewId-'.$result['SuppliersOrganization']['id'].'">';
		echo '	<td colspan="2"></td>';
		echo '	<td colspan="5" id="tdViewId-'.$result['SuppliersOrganization']['id'].'"></td>';
		echo '</tr>';		
	
	} // end foreach ($results as $i => $result)
	
	echo '</table>';
				
	
} // end if(!empty($results)) 
else 
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => "Non ci sono ancora produttori da monitorare"));
?>
