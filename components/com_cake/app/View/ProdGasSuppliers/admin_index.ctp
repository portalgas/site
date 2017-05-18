<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('ProdGasSupplier home'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
		
<?php 	
echo '<div class="organizations">';

echo $this->Form->create('ProdGasSupplier',array('id' => 'formGas'));
?>
<fieldset>

			
	<?php 
	if(count($results)>0) {
	?>
		<table cellpadding="0" cellspacing="0">
		<tr>
				<th><?php echo __('N');?></th>
				<th colspan="2"></th>
				<th style="text-align:center;"><?php echo __('prod_gas_supplier_owner_articles');?></th>
				<th style="text-align:center;"><?php echo __('prod_gas_supplier_can_view_orders');?></th>
				<th style="text-align:center;"><?php echo __('prod_gas_supplier_can_view_orders_users');?></th>
				<th class="actions"><?php echo __('Actions');?></th>		
		</tr>
		<?php
		foreach ($results as $numResult => $result):
						
			echo '<tr class="view">';
			
			echo '<td>'.($numResult+1).'</td>';


			echo '<td>';
				echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'"  alt="'.$result['Organization']['name'].'" />';	
			echo '</td>';
			echo '<td>';
			echo $result['Organization']['name'];
			echo '</td>';
			echo '<td style="text-align:center;">'.$this->App->traslateEnum('ProdGasSupplier'.$result['SuppliersOrganization']['owner_articles']).'</td>';
			echo '<td title="'.__('toolTipProdGasSupplierCanViewOrders').'" class="stato_'.$this->App->traslateEnum($result['SuppliersOrganization']['can_view_orders']).'"></td>';
			echo '<td title="'.__('toolTipProdGasSupplierCanViewOrdersUsers').'" class="stato_'.$this->App->traslateEnum($result['SuppliersOrganization']['can_view_orders_users']).'"></td>';

			echo '<td class="actions-table-img">';
			if($result['SuppliersOrganization']['can_view_orders']=='Y' || $result['SuppliersOrganization']['can_view_orders_users']=='Y')
				echo $this->Html->link(null, array('controller' => 'ProdGasOrders','action' => 'index', null, 'organization_id='.$result['Organization']['id']),array('class' => 'action actionPrinter','title' => __('List Orders')));

			if($result['SuppliersOrganization']['owner_articles']=='SUPPLIER')
				echo $this->Html->link(null, array('controller' => 'ProdGasArticlesSyncronizes','action' => 'index', null, 'organization_id='.$result['Organization']['id']),array('class' => 'action actionBackup','title' => __('ProdGasArticlesSyncronizes')));
			echo '</td>';
		echo '</tr>';
		
		endforeach;
		
		echo '</table>';
		
	} 
	else // if(count($results)>0)
		echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono ancora GAS associati."));

echo '</fieldset>';
echo $this->Form->end();
echo '</div>';
?>