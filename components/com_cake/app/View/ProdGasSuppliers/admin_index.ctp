<?php
$this->App->d($results, false);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('ProdGasSupplier home').' '.$user->name);
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="organizations">';

echo '<h3>'.__('GasOrganizations').'</h3>';

echo '<div class="table-responsive"><table class="table table-hover">';
echo '<tr>';
echo '<th colspan="2">'.__('GasOrganizations').'</th>';
echo '<th>'.__('Place').'</th>';
echo '<th>Mail G.A.S.</th>';
echo '<th>Mail referenti</th>';
echo '<th style="text-align:center;">'.__('prod_gas_supplier_owner_articles').'</th>';
echo '<th style="text-align:center;">'.__('prod_gas_supplier_can_view_orders').'</th>';
echo '<th style="text-align:center;">'.__('prod_gas_supplier_can_view_orders_users').'</th>';
if($user->organization['Organization']['hasPromotionGas']=='Y' || $user->organization['Organization']['hasPromotionGasUsers']=='Y')
	echo '<th style="text-align:center;">'.__('prod_gas_supplier_can_promotions').'</th>';
echo '<th class="actions">'.__('Actions').'</th>';
echo '</tr>';

$this->App->d($results, false);
	
/*
 * GAS associati
 */
if(!isset($results['Supplier']['Organization'])) {
	echo '<tr>';
	echo '<td></td>';
	echo '<td colspan="5">';
	echo $this->element('boxMsg',['class_msg' => 'notice','msg' => "Non associato ad un G.A.S."]);
	echo '</td>';
	echo '</tr>';		
}
else {
	foreach($results['Supplier']['Organization'] as $result) {
		echo '<tr>';
		echo '<td>';
			echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'"  alt="'.$result['Organization']['name'].'" />';	
		echo '</td>';
		echo '<td>';
		echo $result['Organization']['name'];
		echo '</td>';
		echo '<td>';
		if(!empty($result['Organization']['localita'])) echo $result['Organization']['localita'];
		if(!empty($result['Organization']['provincia'])) echo ' ('.$result['Organization']['provincia'].')';
		echo '</td>';
		echo '<td>';
		if(!empty($result['Organization']['mail'])) echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.h($result['Organization']['mail']).'" class="fa fa-envelope-o fa-lg"></a><br />';
		echo '</td>';
		echo '<td>';
		if(!empty($result['SuppliersOrganizationsReferents'])) {
			echo '<b>'.__('gasReferente').'</b>';
			echo '<ul style="margin: 0;padding: 0;">';
			foreach($result['SuppliersOrganizationsReferents'] as $SuppliersOrganizationsReferent) {
				echo '<li>';
				echo h($SuppliersOrganizationsReferent['User']['name']).' ';
				if(!empty($SuppliersOrganizationsReferent['User']['email'])) echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.h($SuppliersOrganizationsReferent['User']['email']).'" class="fa fa-envelope-o fa-lg"></a> ';
				if(!empty($SuppliersOrganizationsReferent['Profile']['phone'])) echo $SuppliersOrganizationsReferent['Profile']['phone'];
				echo '</li>';
			}
			echo '</ul>';
		}
		else {
			/*
			* il gas non ha un referente per il produttore, cerco super referente
			*/	
			echo '<b>'.__('gasSuperReferente').'</b>';
			echo '<ul style="margin: 0;padding: 0;">';
			foreach($result['GasSuperReferente'] as $gasSuperReferente) {
				echo '<li>';
				echo h($gasSuperReferente['User']['name']).' ';
				if(!empty($gasSuperReferente['User']['email'])) echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.h($gasSuperReferente['User']['email']).'" class="fa fa-envelope-o fa-lg"></a> ';
				echo '</li>';
			}
			echo '</ul>';					
		}
		echo '</td>';
		echo '<td style="text-align:center;">';
		if($result['SuppliersOrganization']['owner_articles']=='SUPPLIER') 
			echo '<label class="btn btn-info">'.$this->App->traslateEnum('ProdGasSupplier'.$result['SuppliersOrganization']['owner_articles']).'</label>';
		else
			echo $this->App->traslateEnum('ProdGasSupplier'.$result['SuppliersOrganization']['owner_articles']);
		echo '</td>';
		echo '<td title="'.__('toolTipProdGasSupplierCanViewOrders').'" class="stato_'.$this->App->traslateEnum($result['SuppliersOrganization']['can_view_orders']).'"></td>';
		echo '<td title="'.__('toolTipProdGasSupplierCanViewOrdersUsers').'" class="stato_'.$this->App->traslateEnum($result['SuppliersOrganization']['can_view_orders_users']).'"></td>';

		if($user->organization['Organization']['hasPromotionGas']=='Y' || $user->organization['Organization']['hasPromotionGasUsers']=='Y')
			echo '<td title="'.__('toolTipProdGasSupplierCanPromotions').'" class="stato_'.$this->App->traslateEnum($result['SuppliersOrganization']['can_promotions']).'"></td>';

		echo '<td class="actions-table-img">';
		if($result['SuppliersOrganization']['can_view_orders']=='Y' || $result['SuppliersOrganization']['can_view_orders_users']=='Y')
			echo $this->Html->link(null, array('controller' => 'ProdGasOrders','action' => 'index', null, 'organization_id='.$result['Organization']['id']),array('class' => 'action actionList','title' => __('List Orders')));	
		echo '</td>';
		echo '</tr>';		
	}			
} // end if(!isset($result['Supplier']['Organization']))	
echo '</table></div>';

echo '</div>';
?>