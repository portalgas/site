<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('ProdGasSuppliersImport'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
	
echo '<div class="organizations" style="min-height:450px;">';

echo $this->Form->create('ProdGasSuppliersImport',array('id' => 'formGas','type'=>'get'));

echo '<h1>Copio gli articoli del GAS scelto nell\'anagrafica del produttore</h1>';

echo '<p>';
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
echo '</p>';

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
		echo '<div class="table-responsive"><table class="table table-hover">';
		echo '<tr>';
		echo '<th colspan="2">'.__('Supplier').'</th>';
		echo '<th>'.__('Email').'</th>';
		echo '<th>'.__('Name').'</th>';
		echo '<th>Ctrl esistenza Directory</th>';
		echo '<th>Permessi Directory</th>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>';
		if(!empty($supplierResults['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$supplierResults['Supplier']['img1']))
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$supplierResults['Supplier']['img1'].'" />';	
		echo '</td>';
		echo '<td>';
		echo $supplierResults['Supplier']['name'].' ('.$supplierResults['Supplier']['id'].')';
		echo '</td>';
		echo '<td>'.$supplierResults['Supplier']['mail'].'</td>';
		echo '<td>'.$supplierResults['Supplier']['cognome'].' '.$supplierResults['Supplier']['nome'].'</td>';
		echo '<td>';
		echo $path;
		echo '</td>';
		
		$dir_permission = substr(sprintf('%o', fileperms($path)), -4);
		echo '<td>';
		echo $dir_permission;
		if($dir_permission!='0775' || $dir_permission!='775') 
		echo ' <h1 style=color:red;>dev\'essere 0775</h1>';
		echo ' <h1 style=color:red;>dev\'essere www-data:www-data</h1>';
		echo '</td>';
		echo '</tr>';
		echo '</table></div>';	
	
		/*
		 * users
		 */
		$user_assistente = false; 
		if(!empty($userResults)) {
			echo '<h3>Utenti associati al produttore ('.count($userResults).')</h3>';
			echo '<div class="table-responsive"><table class="table table-hover">';
			echo '<tr>';
			echo '<th colspan="2">'.__('Id').'</th>';
			echo '<th>'.__('Username').'</th>';
			echo '<th>'.__('Name').'</th>';
			echo '<th>'.__('Email').'</th>';
			echo '</tr>';				
			foreach($userResults as $userResult) {
			
				if(trim($userResult['User']['name'])=='Assistente PortAlGas')
					$user_assistente = true; 
				 	
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
			echo '</table></div>';
		}
		else {
			echo "<h1 style=color:red;><b>Non esistono utenti</b> con User.supplier_id = $prod_gas_supplier_id && User.organization_id = 0</h1>";
			echo '<h2>Ctrl se esiste un account tra i gasisti SELECT * FROM j_users WHERE email = \''.$supplierResults['Supplier']['mail'].'\'; </h2>';
			echo '<p>Se fosse trovata:<br /><br />il produttore "'.$supplierResults['Supplier']['name'].'" ha come mail '.$supplierResults['Supplier']['mail'].' ma esiste già un gasista con quella mail.<br />Se non viene utilizzata come un gasista (cioè non effettua acquisti e non crea ordini) conviene convertire l\'utenza del gasista in utenza da produttore.<br />Se no dovrò creaerne una nuova ma mi occorre una nuova mail</p>';
			echo '<p>Crealo con ';
			echo '<ul><li><b>nome</b> '.$supplierResults['Supplier']['name'].'</li>';
			echo '<li><b>username</b> '.strtolower(str_replace(" ",".",$supplierResults['Supplier']['name'])).'</li>';
			echo '<li><b>mail</b> '.$supplierResults['Supplier']['mail'].'</li>';
			echo '</ul>';
			echo '</p>';
		}
		if(!$user_assistente) {
			echo '<h3>Creare utente assistenza</h3>';
			echo '<div class="table-responsive"><table class="table table-hover">';
			echo '<tr>';
			echo '<th>j_users.prod_gas_supplier_id</th>';
			echo '<th>'.__('Username').'</th>';
			echo '<th>'.__('Password').'</th>';
			echo '<th>'.__('Name').'</th>';
			echo '<th>'.__('Email').'</th>';
			echo '</tr>';				
			foreach($userResults as $userResult) {
			
				if($userResult['User']['name']=='Assistente PortAlGas')
					$user_assistente = true; 
				 	
				echo '<tr>';
				echo '<td>'.$prod_gas_supplier_id.'</td>';
				echo '<td>produttore@xxxxx.portalgas.it</td>';
				echo '<td>portalgas</td>';
				echo '<td>Assistente PortAlGas</td>';
				echo '<td>produttore@xxxxx.portalgas.it</td>';
				echo '</tr>';			
			}
			echo '</table></div>';			
		}
		
		
		
		echo '<h3>G.A.S. associati al produttore ('.count($supplierResults['SuppliersOrganization']).')</h3>'; 

		echo '<div class="table-responsive"><table class="table table-hover">';
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
			echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$suppliersOrganizationResult['Organization']['img1'].'" alt="'.$suppliersOrganizationResult['Organization']['name'].'" /> ';	
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
		echo '</table></div>';

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
	
	echo '<div class="alert alert-danger" role="alert">';
	echo '<p>Alle utenze create:</p>';
	echo '<ul>';
	echo '<li>Aggiungere il gruppo <b>prodGasSupplierManager</b></li>';
	echo '<li>Elimnare i gruppo <b>GasPagesGas..</b></li>';
	echo '</ul>';
	echo '<div>';
	
	echo '<pre class="shell" rel="Sql per tornare indietro">';
	echo "UPDATE k_articles SET prod_gas_article_id = 0, supplier_id = 0 WHERE supplier_id = ".$prod_gas_supplier_id.";   ";
	echo "DELETE FROM k_prod_gas_articles WHERE supplier_id = ".$prod_gas_supplier_id.";   ";
	echo '</pre>';
	
	echo '<div class="alert alert-danger" role="alert">';
	echo '<p>Non eseguire nuovamente la pagina o duplichi i records!</p>';
	echo $this->Html->link('Rielabora altro produttore', array('action' => 'import'));
	echo '<div>';
		
	echo '<h1><a href="index.php?option=com_cake&controller=ProdGasSuppliersImports&action=import">CLICCA QUI PER RICOMINCIARE</a></h1>';
	?>
	<script>
	$( document ).ready(function() {
		$("input[type='submit']").hide();
		$("input[type='submit']").html("");		
	});
	</script>	
	<?php
}

echo '</div>';
?>