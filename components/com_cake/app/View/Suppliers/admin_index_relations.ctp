<?php
$urlBase = Configure::read('App.server').'/administrator/index.php?option=com_content&task=article.edit&id=';
?>
<style>
.white {
	background-color: #fff;
}
</style>
<div class="suppliers">
	<h2 class="ico-suppliers">
		<?php echo __('Suppliers Generics');?>
	</h2>

	<?php echo $this->Form->create('Filtersupplier',array('id'=>'formGasFilter','type'=>'get'));?>
		<fieldset class="filter">
			<legend><?php echo __('Filter Suppliers'); ?></legend>
			<div class="table"><table class="table"> <!-- il div non e' table-responsive se no overflow-x: auto; e selectpicker viene nascosto -->
				<tr>
					<?php 
					echo '<td>';
					$options = ['label' => '&nbsp;', 
									'options' => $organizations,
									'empty' => __('FilterToOrganizations'),
									'name' => 'FilterSupplierOrganizationId',
									'default' => $FilterSupplierOrganizationId,
									'escape' => false];
					if(count($organizations) > Configure::read('HtmlSelectWithSearchNum')) 
						$options += ['class'=> 'selectpicker', 'data-live-search' => true]; 
	
					echo $this->Form->input('organization_id', $options);
					echo '</td>';
					
					if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') {
						echo '<td>';
						echo $this->Form->input('category_supplier_id', ['label' => '&nbsp;', 'options' => $categories,'empty' => __('FilterToCategories'), 'name' => 'FilterSupplierCategoryId', 'default' =>  $FilterSupplierCategoryId, 'escape' => false]); 
						echo '</td>';
					}
					
					echo '<td>';
					$options = ['label' => '&nbsp;', 
									'options' => $geoRegions,
									'empty' => __('FilterToGeoRegions'),
									'name' => 'FilterSupplierRegion',
									'default' => $FilterSupplierRegion,
									'escape' => false];					
					echo $this->Form->input('geo_region_id', $options); 
					echo '</td>';
					
					echo '<td>';
					$options = ['label' => '&nbsp;', 
									'options' => $geoProvinces,
									'empty' => __('FilterToGeoProvinces'),
									'name' => 'FilterSupplierProvince',
									'default' => $FilterSupplierProvince,
									'escape' => false];
					if(count($geoProvinces) > Configure::read('HtmlSelectWithSearchNum')) 
						$options += ['class'=> 'selectpicker', 'data-live-search' => true]; 					
					echo $this->Form->input('geo_province_id', $options); 
					echo '</td>';					
					
					echo '<td>';
					echo $this->Ajax->autoComplete('FilterSupplierName', 
									   Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteRootSuppliers_name&format=notmpl',
										array('label' => 'Nome', 'name' => 'FilterSupplierName','value' => $FilterSupplierName, 'size' => '50', 'escape' => false));
					echo '</td>	';					
					/*
					echo '<td>';
					echo $this->Form->reset('Reset', array('value' => 'Reimposta','class' => 'reset'));
					echo '</td>';
					*/
					echo '<td>';
					echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none')));
					echo '</td>';
				echo '</tr>';
			echo '</table></div>';
		echo '</fieldset>';				

	echo '<div class="table-responsive"><table class="table">';
	echo '<tr>';
			echo '<th></th>';
			
			if(empty($FilterSupplierOrganizationId))
				echo '<th colspan="3">'.__('GasOrganizations').'</th>';
			else
				echo '<th>'.__('Vote').'</th>';
			echo '<th>'.$this->Paginator->sort('name',__('Business name')).'</th>';
			echo '<th></th>';
			echo '<th>'.$this->Paginator->sort('descrizione',__('Description')).'</th>';			
			if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') {
				echo '<th>';
				echo $this->Paginator->sort('category_supplier_id'); 	
				echo '</th>';
			}
			echo '<th>'.$this->Paginator->sort('suppliers_deliveries_types',__('SuppliersDeliveriesTypes')).'</th>';
		
			echo '<th>'.__('Place').'</th>';
			echo '<th>'.__('Contacts').'</th>';
	echo '</tr>';
	$row_css = '';
	foreach ($results as $i => $result) {
	
		if($numRow%2==0)
			$row_css = 'active';
		else
			$row_css = 'white';
		
		if(!empty($FilterSupplierOrganizationId)) 	
			$rowspan = 1;	
		else
			$rowspan = count($result['SuppliersOrganization']);
		if($rowspan == 0) 
			$rowspan = 1;
				
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1); 
	
		if($result['Supplier']['j_content_id']>0) $class_j_content_id = 'j_content_id_si';
		else $class_j_content_id = 'j_content_id_no';
				
		echo '<tr class="'.$row_css.'">';
		echo '<td rowspan="'.$rowspan.'"><a action="supplier_details-'.$result['Supplier']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
		
		if(empty($FilterSupplierOrganizationId)) {
			$tmpRowsSpan = "";
			
			if(!empty($result['SuppliersOrganization'])) {
				foreach ($result['SuppliersOrganization'] as $numResult2 => $suppliersOrganization) {
					if($numResult2==0) {
						echo '<td>';
						echo '<img style="width:20px;padding:0px;" src="'.Configure::read('App.web.img.upload.content').'/'.$suppliersOrganization['Organization']['img1'].'" alt="'.$suppliersOrganization['Organization']['name'].'" /> ';
						echo '</td>';
						echo '<td style="white-space: nowrap;">';
						echo $suppliersOrganization['Organization']['name'];
						echo '</td>';
						echo '<td>';
						if(!empty($suppliersOrganization['Organization']['SuppliersVote']))
							echo $this->App->drawVote($suppliersOrganization['Organization']['SuppliersVote']['voto'], $suppliersOrganization['Organization']['SuppliersVote']['nota']);
						echo '</td>';					
					}
					else {
						$tmpRowsSpan .= '<tr class="'.$row_css.'"><td>';
						$tmpRowsSpan .= '<img style="width:20px;padding:0px;" src="'.Configure::read('App.web.img.upload.content').'/'.$suppliersOrganization['Organization']['img1'].'" alt="'.$suppliersOrganization['Organization']['name'].'" /> ';
						$tmpRowsSpan .= '</td>';
						$tmpRowsSpan .= '<td style="white-space: nowrap;">';
						$tmpRowsSpan .= $suppliersOrganization['Organization']['name'];
						$tmpRowsSpan .= '</td>';
						$tmpRowsSpan .= '<td>';
						if(!empty($suppliersOrganization['Organization']['SuppliersVote']))
							$tmpRowsSpan .= $this->App->drawVote($suppliersOrganization['Organization']['SuppliersVote']['voto'], $suppliersOrganization['Organization']['SuppliersVote']['nota']);
						$tmpRowsSpan .= '</td></tr>';	
					}				
				}		
			}
			else
				echo '<td colspan="3" rowspan="'.$rowspan.'"></td>';				
		}
		else {
			echo '<td>';
			if(!empty($result['SuppliersVote']))
				echo $this->App->drawVote($result['SuppliersVote']['voto'], $result['SuppliersVote']['nota']);
			echo '</td>';			
		}


		echo '<td rowspan="'.$rowspan.'" style="width:50px;">';
		if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" />';	
		echo '</td>';
		
		echo '<td rowspan="'.$rowspan.'">';
		if(!empty($result['Supplier']['slug'])) echo '<a title="link esterno al sito del produttore" href="'.Configure::read('Neo.portalgas.url').'site/produttore/'.$result['Supplier']['slug'].'"">';
		echo $result['Supplier']['name'];
		if(!empty($result['Supplier']['slug'])) echo '</a>';
		echo '</td>';
		echo '<td rowspan="'.$rowspan.'">'.$result['Supplier']['descrizione'].'</td>';
		
		if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') {
			echo '<td rowspan="'.$rowspan.'">';
			echo $result['CategoriesSupplier']['name']; 	
			echo '</td>';
		}
		
		echo '<td rowspan="'.$rowspan.'">'.$result['SuppliersDeliveriesType']['name'].'</td>';

		echo '<td rowspan="'.$rowspan.'">';
		if(!empty($result['Supplier']['indirizzo'])) echo $result['Supplier']['indirizzo'].'&nbsp;<br />';
		if(!empty($result['Supplier']['localita'])) echo $result['Supplier']['localita'].'&nbsp;';
		if(!empty($result['Supplier']['cap'])) echo $result['Supplier']['cap'].'&nbsp;';
		if(!empty($result['Supplier']['provincia'])) echo '('.$result['Supplier']['provincia'].')'; 
		echo '</td>';
		echo '<td rowspan="'.$rowspan.'">';
		echo $result['Supplier']['telefono'];
		if(!empty($result['Supplier']['telefono2'])) echo '<br />'.$result['Supplier']['telefono2'];
		if(!empty($result['Supplier']['mail'])) echo '<br /><a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['Supplier']['mail'].'" class="fa fa-envelope-o fa-lg"></a>';
		if(!empty($result['Supplier']['www'])) echo '<a title="link esterno al sito del produttore" href="'.$this->App->traslateWww($result['Supplier']['www']).'" class="blank blank fa fa-globe fa-lg"></a>';
		echo '</td>';
		echo '</tr>';
 					
		if(!empty($tmpRowsSpan) && empty($FilterSupplierOrganizationId)) {
			echo $tmpRowsSpan;
		}

		echo '<tr class="trView" id="trViewId-'.$result['Supplier']['id'].'" style="display:none;">';
		echo '<td></td>';
		echo '<td colspan="';
		echo ($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') ? '11': '10';
		echo '" id="tdViewId-'.$result['Supplier']['id'].'"></td>';
		echo '</tr>';
		
	} // end loop

	echo '</table></div>';

	echo '<p>';
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	echo '</p>';
	
	echo '<div class="paging">';
	echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
	echo $this->Paginator->numbers(array('separator' => ''));
	echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));

echo '</div>';
echo '</div>';