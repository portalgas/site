<?php
$this->App->d($results, false);
  
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('ProdGasSuppliersImport'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="organizations">';

echo '<h3>'.__('Suppliers').'</h3>';

echo '<div class="btn-group">';
 echo ' <button type="button" class="btn btn-info btn-view" data-attr-view="view_all">Visualizzazione completa</button>';
echo '  <button type="button" class="btn btn-primary btn-view" data-attr-view="only_prod">Visualizzazione sintesi</button>';
echo '</div>';

echo '<div class="table-responsive"><table class="table table-hover">';
echo '<tr>';
echo '<th>N.</th>';
echo '<th colspan="2">'.__('Organization').'</th>';
echo '<th>'.__('Supplier').'</th>';
echo '<th></th>';
echo '<th style="text-align:center;">'.__('prod_gas_supplier_owner_articles').'</th>';
echo '<th style="text-align:center;">'.__('prod_gas_supplier_can_view_orders').'</th>';
echo '<th style="text-align:center;">'.__('prod_gas_supplier_can_view_orders_users').'</th>';
echo '<th colspan="2">Sql per settare la gestione del listino aricoli al produttore</th>';
echo '</tr>';

foreach($results as $numResult => $result) {

	$this->App->d($result, false);
	
	echo '<tr>';
	echo '<td>'.($numResult+1).'</td>';
	echo '<td>';
	echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" /> ';	
	echo '</td>';	
	echo '<td>'.$result['Organization']['name'].' ('.$result['Organization']['id'].')</td>';
	if(!isset($result['Supplier']['SuppliersOrganization'])) { 
		
		$msg = "Non associato ad un produttore: <ul><li><b>Scegli l'Organization</b>,</li>";
		$msg .= "<li>".$this->Html->link("Importa il produttore (Root -> produttori dell'organizzazione ".$result['Organization']['name'].")", ['controller' => 'SuppliersOrganizations','action' => 'add_index', null, 'search='.$result['Organization']['name']], ['target' => '_blank', 'title' => __('Add Supplier Organization')])." scegliendo il GAS con il <b>listino</b> del produttore,</li>";
		/*
		 * lo fa /var/portalgas/org_prodgas_new.sh
		$msg .= "<li>INSERT INTO k_categories_articles (organization_id, parent_id, lft, rght, name) values (".$result['Organization']['id'].", null, 1, 2, 'Generale');</li>";
		*/
		$msg .= "<li>".$this->Html->link(__('Articles Gest Categories'), ['controller' => 'CategoriesArticles','action' => 'gest_categories'], ['target' => '_blank', 'title' => __('Articles Gest Categories')])." scegliendo il GAS con il <b>listino</b> del produttore,</li>";
		$msg .= "<li>Associa l'amministratore ai gruppi prodGasSupplierManager [".Configure::read('prod_gas_supplier_manager')."] / SuperReferent [".Configure::read('group_id_super_referent')."]</li></ul>";

		echo '<td colspan="7">';
		echo $this->element('boxMsg',['class_msg' => 'notice','msg' => $msg]);
		echo '</td>';
	}	
	else {
		echo '<td colspan="5">';
		echo $result['Supplier']['Supplier']['name'].' (Supplier.id '.$result['Supplier']['Supplier']['id'].' - SuppliersOrganization.id '.$result['Supplier']['SuppliersOrganization']['id'].')';
				
		$msg = '';
		if($result['Supplier']['Supplier']['owner_organization_id']==0) {
			$msg = 'Supplier con id '.$result['Supplier']['Supplier']['id'].' deve avere owner_organization_id a '.$result['Organization']['id'].'<br />';
			$msg .= ' '.$result['Supplier']['Supplier']['sql_update_supplier'].'<br />';
		}
		if($result['Supplier']['Supplier']['has_account']=='N')
			$msg = 'Supplier con id '.$result['Supplier']['Supplier']['id'].' deve avere has_account a Y<br />';

		if($result['Supplier']['SuppliersOrganization']['organization_id']!=$result['Supplier']['SuppliersOrganization']['owner_organization_id'])
			$msg = 'SuppliersOrganization con id '.$result['Supplier']['SuppliersOrganization']['id'].' deve avere owner_organization_id a '.$result['Supplier']['SuppliersOrganization']['organization_id'].'<br />';
		if($result['Supplier']['SuppliersOrganization']['id']!=$result['Supplier']['SuppliersOrganization']['owner_supplier_organization_id'])
			$msg = 'SuppliersOrganization con id '.$result['Supplier']['SuppliersOrganization']['id'].' deve avere owner_supplier_organization_id a '.$result['Supplier']['SuppliersOrganization']['id'].'<br />';
		if($result['Supplier']['SuppliersOrganization']['owner_articles']!='REFERENT')
			$msg = 'SuppliersOrganization con id '.$result['Supplier']['SuppliersOrganization']['id'].' deve avere owner_articles a REFERENT<br />';
		
		if(!empty($msg))
			echo $this->element('boxMsg',['class_msg' => 'notice','msg' => $msg]);
			
		/*
		 * img
		 */
		 echo '<td colspan="2" class="no_prod">';
		  
		 if($results[$numResult]['Dir']['articles']) {
			 echo 'chown -R www-data:www-data '.$results[$numResult]['Dir']['articles_path'].'<br />';
			 echo 'chmod 775 '.$results[$numResult]['Dir']['articles_path'].'<br />';
	     }
		 else 
			echo $this->element('boxMsg',['class_msg' => 'notice','msg' => $results[$numResult]['Dir']['articles_path']]);	 
		
		 if($result['Organization']['sql_update_organization']=='OK') {
		 	echo 'Organization.img1 '.$result['Organization']['img1'];
		 }
		 else {
			 if(empty($result['Supplier']['Supplier']['img1'])) {
				echo 'cp '.Configure::read('App.root').'/images/organizations/contents/0.jpg '.Configure::read('App.root').'/images/organizations/contents/prodgas-'.$result['Organization']['id'].'.jpg';  
			 } 
			 else {
				echo 'cp '.Configure::read('App.root').'/images/organizations/contents/'.$result['Supplier']['Supplier']['img1'].' '.Configure::read('App.root').'/images/organizations/contents/prodgas-'.$result['Organization']['id'].'.jpg';  		 
			 }
		 	 echo '<br />'.$result['Organization']['sql_update_organization'];
		 }	 
		 echo '</td>';		
	}
	echo '</tr>';			
	
	/*
	 * GAS associati
	 */
	if(!isset($result['Supplier']['Organization'])) {
		echo '<tr>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td colspan="7">';
		echo $this->element('boxMsg',['class_msg' => 'notice','msg' => "Non associato ad un G.A.S.: scegli il G.A.S., importa il produttore"]);		
		echo '</td>';
		echo '</tr>';		
	}
	else {
		foreach($result['Supplier']['Organization'] as $organization) {
		
			if($organization['SuppliersOrganization']['owner_articles']=='SUPPLIER')
				echo '<tr class="">';
			else
				echo '<tr class="no_prod">';
			
			echo '<td></td>';
			echo '<td></td>';
			echo '<td></td>';

			echo '<td>';
				echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$organization['Organization']['img1'].'"  alt="'.$organization['Organization']['name'].'" />';	
			echo '</td>';
			echo '<td>';
			echo $organization['Organization']['name'];
			echo '</td>';
			echo '<td style="text-align:center;">';
			if($organization['SuppliersOrganization']['owner_articles']=='SUPPLIER') 
				echo '<label class="btn btn-info">'.$this->App->traslateEnum('ProdGasSupplier'.$organization['SuppliersOrganization']['owner_articles']).'</label>';
			else
				echo $this->App->traslateEnum('ProdGasSupplier'.$organization['SuppliersOrganization']['owner_articles']);
			echo '</td>';
			echo '<td title="'.__('toolTipProdGasSupplierCanViewOrders').'" class="stato_'.$this->App->traslateEnum($organization['SuppliersOrganization']['can_view_orders']).'"></td>';
			echo '<td title="'.__('toolTipProdGasSupplierCanViewOrdersUsers').'" class="stato_'.$this->App->traslateEnum($organization['SuppliersOrganization']['can_view_orders_users']).'"></td>';
			echo '<td title="'.$organization['msg'].'" class="stato_'.$this->App->traslateEnum($organization['code']).'"></td>';
			echo '<td>';
			echo $organization['msg'].'<br />';
			echo $organization['sql'];
			echo '</td>';
			echo '</tr>';		
		}
		
		/*
		 * Users associati al gruppo Configure::read('prod_gas_supplier_manager');  // 62
		 */
		if(!isset($result['User']) || empty($result['User'])) {
		
			$msg = "No users dell'Organization associati ai gruppi prodGasSupplierManager [".Configure::read('prod_gas_supplier_manager')."] / SuperReferent [".Configure::read('group_id_super_referent')."]<br />".$result['Users']['sql_update_user']; 
			echo '<tr class="no_prod">';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td colspan="7">'; 
			echo $this->element('boxMsg',['class_msg' => 'notice','msg' => $msg]);	
			echo '</td>';
			echo '</tr>';		
		}
		else {
			echo '<tr class="no_prod">';
			echo '<td></td>';
			echo '<td></td>';
			echo '<th colspan="8">';
			echo "Users del Organization associati ai gruppi prodGasSupplierManager [".Configure::read('prod_gas_supplier_manager')."] / SuperReferent [".Configure::read('group_id_super_referent')."]";
			echo '</th>';
			echo '</tr>';

			echo '<tr class="no_prod">';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td colspan="4">';
			echo "INSERT INTO j_user_usergroup_map (user_id, group_id) VALUES (, 62);";
			echo '</td>';
			echo '<td colspan="4">';
			echo "INSERT INTO j_user_usergroup_map (user_id, group_id) VALUES (, 19);";			
			echo '</td>';
			echo '</tr>';
					
			$user_id_old = 0;
			foreach($result['User'] as $numResult => $user) {
				
				if($user_id_old != $user['User']['id']) {
					echo '<tr class="no_prod">';
					echo '<td></td>';
					echo '<td></td>';
					echo '<td>';
					echo $this->App->drawUserAvatar($user, $user['User']['id'], $user['User']);	
					echo '</td>';
					echo '<td colspan="3">';
					echo $user['User']['name'].' ['.$user['User']['id'].']';
					echo '</td>';
					echo '<td></td>';
					echo '<td colspan="3">';
					echo $user['UserGroup']['title'].' ['.$user['UserGroup']['id'].']';
					echo '</td>';
					echo '</tr>';	
				}
				else {
					echo '<tr class="no_prod">';
					echo '<td></td>';
					echo '<td></td>';
					echo '<td></td>';
					echo '<td colspan="3"></td>';
					echo '<td></td>';
					echo '<td colspan="3">';
					echo $user['UserGroup']['title'].' ['.$user['UserGroup']['id'].']';
					echo '</td>';
					echo '</tr>';					
				}

				$user_id_old = $user['User']['id'];
			}
		} // end if(!isset($result['Users']))			
			
	} // end if(!isset($result['Supplier']['Organization']))	
}
echo '</table></div>';			

echo '<pre class="shell no_prod" rel="sql per inserire nuovo produttore">';
echo "INSERT INTO ".Configure::read('DB.prefix')."organizations (name,type,paramsConfig,paramsFields,paramsPay,stato,created,modified) VALUES (%NOME-PRODUTTORE%,'PRODGAS','{\"hasBookmarsArticles\":\"N\",\"hasArticlesOrder\":\"Y\",\"hasVisibility\":\"N\",\"hasUsersRegistrationFE\":\"N\"}','{\"hasFieldArticleCodice\":\"Y\",\"hasFieldArticleIngredienti\":\"Y\",\"hasFieldArticleCategoryId\":\"Y\"}','{}','Y','".date("Y-m-d")." 00:00:00','".date("Y-m-d")." 00:00:00');";
echo '</pre>';

echo '<pre class="shell no_prod" rel="script per inserire le categorie e permessi cartelle">';
echo '/var/portalgas/cron/config.conf settare la variabile {ORGANIZATION-ID}<br />';
echo 'eseguire /var/portalgas/org_prodgas_new.sh {ORGANIZATION-ID}<br />';
echo '<br />';
echo 'Directory articles, users e permessi<br />';
echo 'crea k_categories_articles.name = \'Generale\'<br />';
echo '</pre>';

echo '</div>';
?>
<script>
$(document).ready(function() {
	$(".btn-view").click(function() {
		var view = $(this).attr('data-attr-view');
		
		if(view=='view_all') {
			$('.no_prod').show();
		}
		else
		if(view=='only_prod') {
			$('.no_prod').hide();
		}
	});
});
</script>