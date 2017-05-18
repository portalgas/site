<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('ProdGasSuppliersImport'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
	
echo '<div class="organizations" style="min-height:450px;">';

echo $this->Form->create('ProdGasSuppliersImport',array('id' => 'formGas','type'=>'get'));
?>
	<h1>Copio gli articoli del GAS scelto nell'anagrafica del produttore</h1>
	
		<fieldset>
			<table>
				<tr>
					<?php 
					echo '<td>';
					$options = array('label' => false, 
									'options' => $suppliers,
									'data-placeholder' => 'Scegli un produttore',
									'name' => 'prod_gas_supplier_id',
									'default' => $prod_gas_supplier_id,
									'escape' => false,
									'empty' => Configure::read('option.empty'));
					if(count($suppliers) > Configure::read('HtmlSelectWithSearchNum')) 
						$options += array('class'=> 'selectpicker', 'data-live-search' => true); 
	
					echo $this->Form->input('prod_gas_supplier_id', $options);
					echo '</td>';
					?>
				</tr>	
			</table>
		</fieldset>					
<?php 
echo $this->Form->end(__('Submit')); 

/*
 * dati produttore scelto
 */
if(!empty($prod_gas_supplier_id)) {
	
	echo $this->Form->create('ProdGasSuppliersImport',array('id' => 'formGas2','type'=>'get'));
	echo '<fieldset>';

		/*
		 * Produttore
		 */	
		echo '<table>';
		echo '<tr>';
		echo '<th colspan="2">'.__('Supplier').'</th>';
		echo '<th>Ctrl esistenza Directory</th>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>';
		if(!empty($supplierResults['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$supplierResults['Supplier']['img1']))
			echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$supplierResults['Supplier']['img1'].'" />';	
		echo '</td>';
		echo '<td>';
		echo $supplierResults['Supplier']['name'].' ('.$supplierResults['Supplier']['id'].')';
		echo '</td>';
		echo '<td>';
		echo $path;
		echo '</td>';
		echo '</tr>';
		echo '</table>';	
	
		/*
		 * users
		 */
		if(!empty($userResults)) {
			echo '<h3>Utenti associati al produttore '.count($userResults).'</h3>';
			echo '<table>';
			echo '<tr>';
			echo '<th colspan="2">'.__('Id').'</th>';
			echo '<th>'.__('Username').'</th>';
			echo '<th>'.__('Name').'</th>';
			echo '<th>'.__('Email').'</th>';
			echo '</tr>';				
			foreach($userResults as $userResult) {	
				echo '<tr>';
				echo '<td>';
				echo $this->App->drawUserAvatar($user, $userResult['User']['id'], $userResult['User']);
				echo '</td>';
				echo '<td>';
				echo $userResult['User']['id'];
				echo '</td>';
				echo '<td>';
				echo $userResult['User']['username'];
				echo '</td>';
				echo '<td>';
				echo $userResult['User']['name'];
				echo '</td>';
				echo '<td>';
				echo $userResult['User']['email'];
				echo '</td>';
				echo '</tr>';			
			}
			echo '</table>';
		}
		else
			echo "<h1 style=color:red;><b>Non esistono utenti</b> con User.supplier_id = $prod_gas_supplier_id && User.organization_id = 0</h1>";
		
		echo '<h3>G.A.S. associati al produttore '.count($supplierResults['SuppliersOrganization']).'</h3>'; 

		echo '<table>';
		echo '<tr>';
		echo '<th colspan="2">'.__('G.A.S.').'</th>';
		echo '<th>'.__('SuppliersOrganization').'</th>';
		echo '<th>'.__('prod_gas_supplier_owner_articles').'</th>';
		echo '<th>'.__('prod_gas_supplier_can_view_orders').'</th>';
		echo '<th>'.__('Actions').'</th>';
		echo '</tr>';		
		foreach($supplierResults['SuppliersOrganization'] as $suppliersOrganizationResult) {
				
			echo '<tr>';
			echo '<td>';
			echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$suppliersOrganizationResult['Organization']['img1'].'" alt="'.$suppliersOrganizationResult['Organization']['name'].'" /> ';	
			echo '</td>';			
			echo '<td>';
			echo $suppliersOrganizationResult['Organization']['name'].' ('.$suppliersOrganizationResult['Organization']['id'].')';
			echo '</td>';
			echo '<td>';
			echo $suppliersOrganizationResult['id'];
			echo '</td>';
			echo '<td title="'.__('toolTipProdGasSupplierOwnerArticles').'">'.$this->App->traslateEnum('ProdGasSupplier'.$suppliersOrganizationResult['owner_articles']).'</td>';
			echo '<td title="'.__('toolTipProdGasSupplierCanViewOrders').'" class="stato_'.$this->App->traslateEnum($suppliersOrganizationResult['can_view_orders']).'"></td>';
			echo '<td>';
			if($suppliersOrganizationResult['organization_id']==$user->organization['Organization']['id']) 
				echo ' => lo tratter&ograve; per questo G.A.S.';
			echo '</td>';
			echo '</tr>';
			
			$supplier_organization_id = $suppliersOrganizationResult['id'];			
		}
		echo '</table>';

		echo "<h3>Trovati ".count($results)." articoli</h3>";
		echo '</fieldset>';
		
echo $this->Form->hidden('prod_gas_supplier_id', array('value' => $prod_gas_supplier_id));
echo $this->Form->hidden('supplier_organization_id', array('value' => $supplier_organization_id));
echo $this->Form->hidden('elabora', array('value' => 'OK'));
if(count($results)>0)
	echo $this->Form->end(__('Submit')); 
else
	echo $this->Form->end(); 
		
} // if(!empty($prod_gas_supplier_id)) 
	

/*
 * elabora
 */
if(!empty($elabora)) {
	echo $str_log;
}

echo '</div>';
?>