<?php
$urlBase = Configure::read('App.server').'/administrator/index.php?option=com_content&task=article.edit&id=';
?>
<div class="suppliers">
	<h2 class="ico-suppliers">
		<?php echo __('Suppliers Generics');?>
	</h2>

	<?php echo $this->Form->create('Filtersupplier',array('id'=>'formGasFilter','type'=>'get'));?>
		<fieldset class="filter">
			<legend><?php echo __('Filter Suppliers'); ?></legend>
			<table>
				<tr>
					<?php 
					echo '<td>';
					$options = array('label' => false, 
									'options' => $organizations,
									'empty' => __('FilterToOrganizations'),
									'name' => 'FilterSupplierOrganizationId',
									'default' => $FilterSupplierOrganizationId,
									'escape' => false);
					if(count($organizations) > Configure::read('HtmlSelectWithSearchNum')) 
						$options += array('class'=> 'selectpicker', 'data-live-search' => true); 
						echo $this->Form->input('organization_id', $options);					echo '</td>';
					
					if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') {
						echo '<td>';
						echo $this->Form->input('category_supplier_id',array('label' => false,'options' => $categories,'empty' => __('FilterToCategories'),'name'=>'FilterSupplierCategoryId','default'=>$FilterSupplierCategoryId,'escape' => false)); 
						echo '</td>';
					}
					?>
					<td>
						<?php echo $this->Ajax->autoComplete('FilterSupplierName', 
															   Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteRootSuppliers_name&format=notmpl',
								   								array('label' => 'Nome','name'=>'FilterSupplierName','value'=>$FilterSupplierName,'size'=>'75','escape' => false));
						?>
					</td>						
					<td>
						<?php echo $this->Form->reset('Reset', array('value' => 'Reimposta','class' => 'reset')); ?>
					</td>
					<td>
						<?php echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); ?>
					</td>
				</tr>	
			</table>
		</fieldset>					
					
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo __('N');?></th>
			
			<?php 
			if(empty($FilterSupplierOrganizationId))
				echo '<th colspan="3">G.A.S.</th>';
			else
				echo '<th>'.__('Vote').'</th>';
			
			if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') {
				echo '<th>';
				echo $this->Paginator->sort('category_supplier_id'); 	
				echo '</th>';
			}
			?>
			<th></th>
			<th><?php echo $this->Paginator->sort('name',__('Business name'));?></th>
			<th><?php echo $this->Paginator->sort('descrizione',__('Description'));?></th>
			<th><?php echo __('Place');?></th>
			<th><?php echo __('Contacts');?></th>
	</tr>
	<?php
	foreach ($results as $i => $result):
	
		if(!empty($FilterSupplierOrganizationId)) 	
			$rowspan = 1;	
		else
			$rowspan = count($result['SuppliersOrganization']);
		if($rowspan == 0) 
			$rowspan = 1;
				
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1); 
	
		if($result['Supplier']['j_content_id']>0) $class_j_content_id = 'j_content_id_si';
		else $class_j_content_id = 'j_content_id_no';
		
		echo '<tr class="view-2">';
		echo '<td rowspan="'.$rowspan.'">'.$numRow.'</td>';
		
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
						$tmpRowsSpan .= '<tr><td>';
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

			
		if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') {
			echo '<td rowspan="'.$rowspan.'">';
			echo $result['CategoriesSupplier']['name']; 	
			echo '</td>';
		}
		
		echo '</td>';
		echo '<td rowspan="'.$rowspan.'">';
		if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
			echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" />';	
		echo '</td>';
		
		echo '<td rowspan="'.$rowspan.'">'.$result['Supplier']['name'].'</td>';
		echo '<td rowspan="'.$rowspan.'">'.$result['Supplier']['descrizione'].'</td>';
		echo '<td rowspan="'.$rowspan.'">';
		if(!empty($result['Supplier']['indirizzo'])) echo $result['Supplier']['indirizzo'].'&nbsp;<br />';
		if(!empty($result['Supplier']['localita'])) echo $result['Supplier']['localita'].'&nbsp;';
		if(!empty($result['Supplier']['cap'])) echo $result['Supplier']['cap'].'&nbsp;';
		if(!empty($result['Supplier']['provincia'])) echo '('.$result['Supplier']['provincia'].')'; 
		echo '</td>';
		echo '<td rowspan="'.$rowspan.'">';
		echo $result['Supplier']['telefono'];
		if(!empty($result['Supplier']['telefono2'])) echo '<br />'.$result['Supplier']['telefono2'];
		if(!empty($result['Supplier']['mail'])) echo '<br /><a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['Supplier']['mail'].'" class="link_mailto"></a>';
		if(!empty($result['Supplier']['www'])) echo '<a title="link esterno al sito del produttore" href="'.$this->App->traslateWww($result['Supplier']['www']).'" class="blank link_www"></a>';
		echo '</td>';
	echo '</tr>';
	
	if(!empty($tmpRowsSpan) && empty($FilterSupplierOrganizationId)) {
		echo $tmpRowsSpan;
	}
				
endforeach;

	echo '</table>';

	echo '<p>';
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	echo '</p>';
	
	echo '<div class="paging">';
	echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
	echo $this->Paginator->numbers(array('separator' => ''));
	echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
	<?php 
	/*
	 * devo ripulire il campo hidden che inizia per page perche' dopo la prima pagina sbaglia la ricerca con filtri
	 */
	?>
	jQuery('.filter').click(function() {
		jQuery("input[name^='page']").val('');
	});
	
});		
</script>