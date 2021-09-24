<?php
$urlBase = Configure::read('App.server').'/administrator/index.php?option=com_content&task=article.edit&id=';
?>
<div class="suppliers">
	<h2 class="ico-suppliers">
		<?php echo __('Suppliers Generics');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('New Supplier'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('New Supplier'))); ?></li>
			</ul>
		</div>
	</h2>

	<?php echo $this->Form->create('Filtersupplier',array('id'=>'formGasFilter','type'=>'get'));?>
		<fieldset class="filter">
			<legend><?php echo __('Filter Suppliers'); ?></legend>
			<table>
				<tr>
					<?php 
					echo '<td>';
					$options = array('label' => '&nbsp;', 
									'options' => $organizations,
									'empty' => __('FilterToOrganizations'),
									'name' => 'FilterSupplierOrganizationId',
									'default' => $FilterSupplierOrganizationId,
									'escape' => false);
					if(count($organizations) > Configure::read('HtmlSelectWithSearchNum')) 
						$options += array('class'=> 'selectpicker', 'data-live-search' => true); 
	
					echo $this->Form->input('organization_id', $options);
					echo '</td>';
					
					if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') {
						echo '<td>';
						echo $this->Form->input('category_supplier_id',array('label' => '&nbsp;','options' => $categories, 'empty' => 'Filtra per categoria','name'=>'FilterSupplierCategoryId','default'=>$FilterSupplierCategoryId,'escape' => false)); 
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
						<?php echo $this->Form->input('stato',array('label' => '&nbsp;','options' => $stato,'name'=>'FilterSupplierStato','default'=>$FilterSupplierStato,'escape' => false)); ?>
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
	<?php					
			echo '<div class="table-responsive"><table class="table table-hover">';
			echo '<tr>';
			echo '<th>'.__('N').'</th>';
					
			if(empty($FilterSupplierOrganizationId))
				echo '<th colspan="3">'.__('GasOrganizations').'</th>';
			else
				echo '<th>'.__('Vote').'</th>';
			
			if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') {
				echo '<th>';
				echo $this->Paginator->sort('category_supplier_id'); 	
				echo '</th>';
			}
			
			echo '<th>'.$this->Paginator->sort('suppliers_deliveries_types',__('SuppliersDeliveriesTypes')).'</th>';
			echo '<th></th>';
			echo '<th>'.$this->Paginator->sort('name',__('Business name')).'</th>';
			echo '<th>'.$this->Paginator->sort('descrizione',__('Description')).'</th>';
			echo '<th>'.__('Place').'</th>';
			echo '<th>'.__('Contacts').'</th>';
			echo '<th>Joomla Contenuto</th>';
			echo '<th>'.$this->Paginator->sort('stato',__('Stato')).'</th>';
			echo '<th>'.$this->Paginator->sort('created').'</th>';
		    echo '<th class="actions">'.__('Actions').'</th>';
	echo '</tr>';
	
	foreach ($results as $i => $result):
		/*
		echo "<pre>";
		print_r($result);
		echo "</pre>";
		*/
		if(!empty($FilterSupplierOrganizationId)) 	
			$rowspan = 1;	
		else
			$rowspan = count($result['SuppliersOrganization']);
		if($rowspan == 0) 
			$rowspan = 1;
		
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1); 
	
		if($result['Supplier']['j_content_id']>0) $class_j_content_id = 'j_content_id_si';
		else $class_j_content_id = 'j_content_id_no';
		
		echo '<tr class="view">';
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
				} // loop Organization		 
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

		echo '<td rowspan="'.$rowspan.'">'.$result['SuppliersDeliveriesType']['name'].'</td>';

		echo '<td rowspan="'.$rowspan.'">';
		if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" />';	
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
		if(!empty($result['Supplier']['mail'])) echo '<br /><a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['Supplier']['mail'].'" class="fa fa-envelope-o fa-lg"></a>';
		if(!empty($result['Supplier']['www'])) echo '<a title="link esterno al sito del produttore" href="'.$this->App->traslateWww($result['Supplier']['www']).'" class="blank fa fa-globe fa-lg"></a>';
		if(!empty($result['Supplier']['slug'])) echo '<a title="link esterno al sito del produttore" href="'.Configure::read('Neo.portalgas.url').'site/produttore/'.$result['Supplier']['slug'].'" class="blank fa fa-globe fa-lg"></a>';
		echo '</td>';
		echo '<td rowspan="'.$rowspan.'" title="'.__('toolJoomlaContent').' - vale '.$result['Supplier']['j_content_id'].'" class="'.$class_j_content_id.'"></td>';
		echo '<td rowspan="'.$rowspan.'" title="'.__('toolTipStatoSupplier').'" class="stato_'.$this->App->traslateEnum($result['Supplier']['stato']).'"></td>';
		echo '<td rowspan="'.$rowspan.'" style="white-space: nowrap;">';
		echo $this->App->formatDateCreatedModifier($result['Supplier']['created']);
		echo '</td>';
		echo '<td rowspan="'.$rowspan.'" class="actions-table-img-3">';
		/*
		 * root, puo' modificare un articolo definitivo con joomla (qui e' sempre root che accede ai Suppliers)
		 *
		 * se lo stato e' T temporaneo modifico l'articolo da SuppliersOrganization perche' devo scegliere l'organization
		 */
		if($isRoot) 
			if($result['Supplier']['stato']=='Y' && !empty($result['Supplier']['j_content_id'])) 
				echo $this->Html->link(null, $urlBase.$result['Supplier']['j_content_id'].'&sort:'.$sort.'&direction:'.$direction.'&page:'.$page,array('class' => 'action actionJContent','title' => __('Edit JContent Defined')));	
		
		echo $this->Html->link(null, array('action' => 'edit', $result['Supplier']['id'].'&sort:'.$sort.'&direction:'.$direction.'&page:'.$page),array('class' => 'action actionEdit','title' => __('Edit'))); 
		echo $this->Html->link(null, array('action' => 'delete', $result['Supplier']['id'].'&sort:'.$sort.'&direction:'.$direction.'&page:'.$page),array('class' => 'action actionDelete','title' => __('Delete'))); 		
		echo '</td>';
	echo '</tr>';
	
	if(!empty($tmpRowsSpan) && empty($FilterSupplierOrganizationId)) {
		echo $tmpRowsSpan;
	}
	
endforeach;

	echo '</table></div>';

	echo $this->element('legendaSupplier');
	
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