<?php
/*
echo $this->Html->script('datatables.min');
echo $this->Html->css('datatables.min');
*/
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Suppliers'), array('controller' => 'SuppliersOrganizations', 'action' => 'index'));
$this->Html->addCrumb(__('Add Supplier Organization'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

if(!empty($results)) 
	$labelAdd = "Il produttore non c'è nell'elenco, aggiungo un nuovo produttore";
else
	$labelAdd = "Non sono presenti produttori, aggiungo un nuovo produttore";
?>

<h2 class="ico-suppliers">
	<?php echo __('Add Supplier Organization');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link($labelAdd, array('action' => 'add_new', null, 'sort:'.$sort,'direction:'.$direction,'page:'.$page),array('class' => 'action actionAdd', 'title' => $labelAdd)); ?></li>
		</ul>
	</div>
</h2>


<div class="suppliers form">

<?php
if(!empty($results))
	echo $this->element('legendaSuppliersOrganizationsCtrl');			

/*
 * filtro di ricerca
 */
echo $this->Form->create('FilterSupplier', ['id' => 'formGasFilter', 'type' => 'get']);

echo '<fieldset class="filter">';
echo '<legend>'.__('Filter Suppliers').'</legend>';

echo '<div class="row">';
echo '<div class="col-md-4">';	
echo $this->Form->input('category_supplier_id', ['label' => false, 'options' => $categories, 'empty' => __('FilterToCategories'), 'name' => 'FilterSuppliersOrganizationCategoryId', 'default' => $FilterSuppliersOrganizationCategoryId, 'escape' => false]); 
echo '</div>';
echo '<div class="col-md-4">';	
$options = ['label' => false, 
				'options' => $geoRegions,
				'empty' => __('FilterToGeoRegions'),
				'name' => 'FilterSuppliersOrganizationRegion',
				'default' => $FilterSuppliersOrganizationRegion,
				'escape' => false];					
echo $this->Form->input('geo_region_id', $options); 
echo '</div>';
echo '<div class="col-md-4">';	
$options = ['label' => false, 
				'options' => $geoProvinces,
				'empty' => __('FilterToGeoProvinces'),
				'name' => 'FilterSuppliersOrganizationProvince',
				'default' => $FilterSuppliersOrganizationProvince,
				'escape' => false];
if(count($geoProvinces) > Configure::read('HtmlSelectWithSearchNum')) 
	$options += ['class'=> 'selectpicker', 'data-live-search' => true]; 					
echo $this->Form->input('geo_province_id', $options);
echo '</div>';	
echo '</div>';	// row

echo '<div class="row">';
echo '<div class="col-md-8">';
echo $this->Form->input('FilterSuppliersOrganizationName', ['label' => 'Nome', 'name' => 'FilterSuppliersOrganizationName', 'value' => $FilterSuppliersOrganizationName ,'escape' => false, 'style' => 'width:100%']);
echo '</div>';	
echo '<div class="col-md-4">';	
echo $this->Form->end(['label' => __('Filter'), 'class' => 'filter', 'div' => ['class' => 'submit filter', 'style' => 'display:none']]);
echo '</div>';																
echo '</div>';	// row


if(!$search_execute) {
	echo $this->element('boxMsg', ['msg' => __('msg_search_no_parameter')]);
}
else {

	echo $this->Form->create('SuppliersOrganization', ['id' => 'formGas']);
	echo '<fieldset>';
	echo '<legend></legend>';
		
	if(!empty($results)) {
	
		echo '<div class="table-responsive"><table class="table table-hover">';
		echo '<thead>';
		echo '<tr>';
		if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') echo '<th>'.__('Category').'</th>';
		echo '<th></th>';
		echo '<th>'.__('Business name').'</th>';
		echo '<th>'.__('Description').'</th>';
		echo '<th>'.__('Place').'</th>';
		echo '<th>'.__('Contacts').'</th>';
		echo '<th class="actions">'.__('Actions').'</th>';
		echo '</tr>';
		echo '</thead>';
		
		foreach ($results as $i => $result) {
		
			echo '<tbody';
			echo '<tr class="view">';
			
			if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') {
				echo '<td>';
				if(isset($result['CategorySupplier']['name']))
					echo $result['CategorySupplier']['name']; 
				else
					echo "-";
				echo '</td>';
			}
			echo '<td>';
			if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
				echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" />';
			echo '</td>';
			echo '<td>'.$result['Supplier']['name'].'</td>';
			echo '<td>'.$result['Supplier']['descrizione'].'</td>';
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
			echo '</td>';
			echo '<td>';
			echo '<a action="suppliers-'.$result['Supplier']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
			echo '</td>';
			echo '</tr>';		
			
			echo '<tr class="trView" id="trViewId-'.$result['Supplier']['id'].'" style="display:none;">';
			echo '<td></td>';
			echo '<td colspan="';
			echo ($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') ? '6': '5';
			echo '" id="tdViewId-'.$result['Supplier']['id'].'"></td>';
			echo '</tr>';
			echo '</tbody>';				
		} // end foreach ($results as $i => $result)		
		echo '</table>';
		
	} // end if(!empty($results))
	else
		echo $this->element('boxMsg');
	
	/*
	 * gestisce se importare Supplier o Supplier e Articles
	 */
	echo $this->Form->hidden('supplier_articles', ['id' => 'supplier_articles','value' => 'N']);
				
	echo $this->element('legendaSuppliersOrganizationsAdd', ['results' => $results, 'sort' => $sort,'direction' => $direction, 'page' => $page]);

} // end if(!$search_execute)		

echo '</fieldset>';
echo '</div>';
echo '<div class="actions">';
echo '<h3>'.__('Actions').'</h3>';
echo '<ul>';
echo '<li>'.$this->Html->link(__('List Suppliers Organization'), ['action' => 'index'], ['class'=>'action actionReload']).'</li>';
echo '</ul>';
echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {
	var $rows = $('table#grid tbody tr');
	$('#search-disabled').keyup(function() {
		var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
		
		$rows.show().filter(function() {
			var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
			return !~text.indexOf(val);
		}).hide();		
	});
} );
</script>