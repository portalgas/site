<?php
$debug = false;

echo '<div class="suppliers">';
echo '<h2 class="ico-suppliersOrganizations">';
echo __('SuppliersOrganizations');
echo '<div class="actions-img">';
echo '<ul>';
echo '<li>'.$this->Html->link(__('New Supplier Organization'), ['action' => 'add_index'], ['class' => 'action actionAdd','title' => __('New Supplier Organization')]).'</li>';
echo '</ul>';
echo '</div>';
echo '</h2>';

if($isSuperReferente) {

	echo $this->Form->create('FilterSuppliersOrganization', ['id'=>'formGasFilter','type'=>'get']);
	echo '<fieldset class="filter">';
	echo '<legend>'.__('Filter Suppliers').'</legend>';
	
	echo '<div class="row">';
	
	if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') {
		echo '<div class="col-md-4">';	
		echo $this->Form->input('category_supplier_id',array('label' => '&nbsp;', 'options' => $categories,'empty' => 'Filtra per categoria','name'=>'FilterSuppliersOrganizationCategoryId','default'=>$FilterSuppliersOrganizationCategoryId,'escape' => false)); 
		echo '</div>';	
	}
	echo '<div class="col-md-4">';	
	
	$options = ['label' => __('FilterToSuppliersOnlyVisible'), 
				'options' => $ACLsuppliersOrganization,
				'name' => 'FilterSuppliersOrganizationId', 'default' => $FilterSuppliersOrganizationId, 'escape' => false];
	if(count($ACLsuppliersOrganization) > 1) 
		$options += ['data-placeholder'=> __('FilterToSuppliers'), 'empty' => __('FilterToSuppliers')];									
	if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
		$options += ['class'=> 'selectpicker', 'data-live-search' => true];
	echo $this->Form->input('FilterSuppliersOrganizationId',$options);
	
		/*	echo $this->Ajax->autoComplete('FilterSuppliersOrganizationName', 
								   Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteSuppliersOrganizations_name&format=notmpl',
									array('label' => 'Nome', 'class' => 'form-control', 'name'=>'FilterSuppliersOrganizationName','value'=>$FilterSuppliersOrganizationName,'escape' => false));	*/														
	echo '</div>';	
	echo '<div class="col-md-2">';
	echo $this->Form->input('stato',array('label' => __('Stato'), 'class' => 'form-control', 'options' => $stato,'name'=>'FilterSuppliersOrganizationStato','default'=>$FilterSuppliersOrganizationStato,'escape' => false)); 
	echo '</div>';	
	echo '<div class="col-md-1">';	
	echo $this->Form->reset('Reset', array('value' => 'Reimposta','class' => 'reset')); 
	echo '</div>';	
	echo '<div class="col-md-1">';	
	echo $this->Form->submit(__('Filter'), array('label' => __('Filter'), 'type' => 'submit', 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); 
	echo '</div>';		
	echo '</div>';
				
	echo '</fieldset>';	
	echo $this->Form->end(); 	
}

if(!empty($results)) {
?>
	<div class="table-responsive"><table class="table table-hover">
	<tr>
	<th></th>
	<th><?php echo __('N');?></th>
	<?php
		if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') 
			echo '<th>'.__('Category').'</th>';
	
	echo '<th></th>';
	echo '<th>'.$this->Paginator->sort('name',__('Business name')).'</th>';
	echo '<th>'.__('TotArticlesActives').'</th>';
	echo '<th>'.__('Place').'</th>';
	echo '<th>'.__('Contacts').'</th>';
	echo '<th>'.__('Suppliers Organizations Referents').'</th>';
	if($user->organization['Organization']['hasDes'] == 'Y')
		echo '<th>'.__('OwnOrganizationId').'</th>';
	else
		echo '<th>'.$this->Paginator->sort('frequenza').'</th>';
	echo '<th title="'.__('toolTipProdGasSupplierOwnerArticles').'">'.$this->Paginator->sort('owner_articles', __('prod_gas_supplier_owner_articles_short')).'</th>';
	echo '<th title="'.__('toolTipProdGasSupplierCanViewOrders').'">'.$this->Paginator->sort('can_view_orders', __('prod_gas_supplier_can_view_orders_short')).'</th>';
	echo '<th title="'.__('toolTipStato').'" colspan="2">'.$this->Paginator->sort('Stato').'</th>';
	echo '<th class="actions">'.__('Actions').'</th>';
	echo '</tr>';

	foreach ($results as $numResult => $result) {
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $numResult+1);
		 
		echo '<tr class="view">';
		echo '<td><a action="suppliers_organizations-'.$result['SuppliersOrganization']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
		echo '<td>'.$numRow.'</td>';
		if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') 
			echo '<td>'.$result['CategoriesSupplier']['name'].'</td>';

		echo '<td>';
		if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" />';	
		echo '</td>';
		echo '<td>';

		if(!empty($result['Supplier']['slug'])) echo '<a title="link esterno al sito del produttore" href="'.Configure::read('Neo.portalgas.url').'site/produttore/'.$result['Supplier']['slug'].'"">';
		echo $result['Supplier']['name'];
		if(!empty($result['Supplier']['slug'])) echo '</a>';

		if(!empty($result['Supplier']['descrizione']))
			echo '<br /><small>'.$result['Supplier']['descrizione'].'</small>';
		echo '</td>';
		echo '<td style="text-align:center;">';
		echo $result['Articles']['totArticles'];
		echo '</td>';
		echo '<td>';
		
			   if(!empty($result['Supplier']['indirizzo'])) echo $result['Supplier']['indirizzo'].'&nbsp;<br />';
			   if(!empty($result['Supplier']['localita'])) echo $result['Supplier']['localita'].'&nbsp;';
				if(!empty($result['Supplier']['cap'])) echo $result['Supplier']['cap'].'&nbsp;';
				if(!empty($result['Supplier']['provincia'])) echo '('.$result['Supplier']['provincia'].')'; 
		echo '</td>';
		echo '<td>';
		echo $result['Supplier']['telefono'];
				if(!empty($result['Supplier']['telefono2'])) echo '<br />'.$result['Supplier']['telefono2'];
				if(!empty($result['Supplier']['mail'])) echo '<br /><a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['Supplier']['mail'].'" class="fa fa-envelope-o fa-lg"></a>';
				if(!empty($result['Supplier']['www'])) echo '<a title="link esterno al sito del produttore" href="'.$this->App->traslateWww($result['Supplier']['www']).'" class="blank fa fa-globe fa-lg"></a>';
		echo '</td>';
		echo '<td>';
		echo $this->app->drawListSuppliersOrganizationsReferents($user,$result['SuppliersOrganizationsReferent']);
		echo '</td>';
		echo '<td>';
		if($debug) {
			echo 'Supplier.stato '.$result['Supplier']['stato'].'<br />';
			echo 'Supplier.stato '.$result['SuppliersOrganization']['owner_articles'].'<br />';
		}
		
		if($user->organization['Organization']['hasDes'] == 'Y' && $result['SuppliersOrganization']['owner_articles']=='DES') {
			/*
			 * dati owner_articles listino REFERENT / DES / SUPPLIER 
			 */			
			if(!empty($result['DesSupplier'])) {
				if($result['De']['isGasTitolare']) {
					echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$user->organization['Organization']['img1'].'" alt="'.$user->organization['Organization']['name'].'" /> ';	
					echo '<br />'.$user->organization['Organization']['name']; 				
				}
				else {
					echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['DesOrganization']['img1'].'" alt="'.$result['DesOrganization']['name'].'" /> ';	
					echo '<br />'.$result['DesOrganization']['name']; 				
				}
			}			
		} else {
			echo $result['SuppliersOrganization']['frequenza'];		
		}
		echo '</td>';
		echo '<td title="'.__('toolTipProdGasSupplierOwnerArticles').'">'.$this->App->traslateEnum('ProdGasSupplier'.$result['SuppliersOrganization']['owner_articles']).'</td>';
		if($result['SuppliersOrganization']['owner_articles']=='SUPPLIER')
            echo '<td title="'.__('toolTipProdGasSupplierCanViewOrders').'" class="stato_'.$this->App->traslateEnum($result['SuppliersOrganization']['can_view_orders']).'"></td>';
		else
            echo '<td></td>';
        echo '<td title="'.__('toolTipStato').'" class="stato_'.$this->App->traslateEnum($result['SuppliersOrganization']['stato']).'"></td>';
		
		if($result['Supplier']['stato']=='T') 
			echo '<td title="'.__('toolTipStatoSupplier').'" class="stato_'.$this->App->traslateEnum($result['Supplier']['stato']).'"></td>';
		else
			echo '<td></td>';
		echo '<td class="actions-table-img-3">';			
		echo $this->Html->link(null, array('controller' => 'Articles', 'action' => 'context_articles_index', null,'FilterArticleFlagPresenteArticlesorders=ALL&FilterArticleSupplierId='.$result['SuppliersOrganization']['id']),array('class' => 'action actionList','title' => __('List Articles')));
		/*
		 * se lo stato e' Y definitivo modifico l'articolo da joomla
		 */
		if($result['Supplier']['stato']=='T') 
			echo $this->Html->link(null, array('controller' => 'SuppliersOrganizationsJcontents', 'action' => 'edit', $result['SuppliersOrganization']['id'],'sort:'.$sort,'direction:'.$direction,'page:'.$page),array('class' => 'action actionJContent','title' => __('Edit JContent Temporary')));
		echo $this->Html->link(null, array('action' => 'edit', $result['SuppliersOrganization']['id'],'sort:'.$sort,'direction:'.$direction,'page:'.$page),array('class' => 'action actionEdit','title' => __('Edit')));
		echo $this->Html->link(null, array('action' => 'delete', $result['SuppliersOrganization']['id'],'sort:'.$sort,'direction:'.$direction,'page:'.$page),array('class' => 'action actionDelete','title' => __('Delete'))); 
		echo '</td>';
		echo '</tr>';
		echo '<tr class="trView" id="trViewId-'.$result['SuppliersOrganization']['id'].'">';
		echo '<td colspan="2"></td>';
		echo '<td colspan="';
		echo ($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') ? '15' :'14';
		echo '" id="tdViewId-'.$result['SuppliersOrganization']['id'].'"></td>';
		echo '</tr>';
	} // end loop
	echo '</table></div>';
?>

	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>

	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));

	 	echo '</div>';
	} 
	else 
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono ancora produttori registrati"));
		
echo '</div>';
?>