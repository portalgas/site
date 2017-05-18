<div class="suppliers">
	<h2 class="ico-suppliersOrganizations">
		<?php echo __('SuppliersOrganizations');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('New Supplier Organization'), array('action' => 'add_index'),array('class' => 'action actionAdd','title' => __('New Supplier Organization'))); ?></li>
			</ul>
		</div>
	</h2>

<?php
if($isSuperReferente) {
?>
	<?php echo $this->Form->create('FilterSuppliersOrganization',array('id'=>'formGasFilter','type'=>'get'));?>
		<fieldset class="filter">
			<legend><?php echo __('Filter Suppliers'); ?></legend>
			<table>
				<tr>
					<?php 
					if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') {
						echo '<td>';
						echo $this->Form->input('category_supplier_id',array('label' => false,'options' => $categories,'empty' => 'Filtra per categoria','name'=>'FilterSuppliersOrganizationCategoryId','default'=>$FilterSuppliersOrganizationCategoryId,'escape' => false)); 
						echo '</td>';
					}
					?>
					</td>
					<td>
						<?php echo $this->Ajax->autoComplete('FilterSuppliersOrganizationName', 
															Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteSuppliersOrganizations_name&format=notmpl',
								   							array('label' => 'Nome','name'=>'FilterSuppliersOrganizationName','value'=>$FilterSuppliersOrganizationName,'size'=>'75','escape' => false)); 
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

<?php
}

if(!empty($results)) {
?>

	<table cellpadding="0" cellspacing="0">
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
	echo '<th>'.$this->Paginator->sort('frequenza').'</th>';
	echo '<th title="'.__('toolTipProdGasSupplierOwnerArticles').'">'.$this->Paginator->sort('owner_articles', __('prod_gas_supplier_owner_articles_short')).'</th>';
	echo '<th title="'.__('toolTipProdGasSupplierCanViewOrders').'">'.$this->Paginator->sort('can_view_orders', __('prod_gas_supplier_can_view_orders_short')).'</th>';
	echo '<th title="'.__('toolTipStato').'" colspan="2">'.$this->Paginator->sort('Stato').'</th>';
	echo '<th class="actions">'.__('Actions').'</th>';
	echo '</tr>';

	foreach ($results as $i => $result):
			$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1);
			 
			echo '<tr class="view">';
			echo '<td><a action="suppliers_organizations-'.$result['SuppliersOrganization']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
			echo '<td>'.$numRow.'</td>';
			if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') 
				echo '<td>'.$result['CategoriesSupplier']['name'].'</td>';

			echo '<td>';
			if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
				echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" />';	
			echo '</td>';
			echo '<td>';
				echo $result['Supplier']['name']; 
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
					if(!empty($result['Supplier']['mail'])) echo '<br /><a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['Supplier']['mail'].'" class="link_mailto"></a>';
					if(!empty($result['Supplier']['www'])) echo '<a title="link esterno al sito del produttore" href="'.$this->App->traslateWww($result['Supplier']['www']).'" class="blank link_www"></a>';
			echo '</td>';
			echo '<td>';
			echo $this->app->drawListSuppliersOrganizationsReferents($user,$result['SuppliersOrganizationsReferent']);
			echo '</td>';
			echo '<td>';
			echo $result['SuppliersOrganization']['frequenza'];		
			echo '</td>';
			echo '<td title="'.__('toolTipProdGasSupplierOwnerArticles').'">'.$this->App->traslateEnum('ProdGasSupplier'.$result['SuppliersOrganization']['owner_articles']).'</td>';
			echo '<td title="'.__('toolTipProdGasSupplierCanViewOrders').'" class="stato_'.$this->App->traslateEnum($result['SuppliersOrganization']['can_view_orders']).'"></td>';
			echo '<td title="'.__('toolTipStato').'" class="stato_'.$this->App->traslateEnum($result['SuppliersOrganization']['stato']).'"></td>';
			
			if($result['Supplier']['stato']=='T') 
				echo '<td title="'.__('toolTipStatoSupplier').'" class="stato_'.$this->App->traslateEnum($result['Supplier']['stato']).'"></td>';
			else
				echo '<td></td>';
			echo '<td class="actions-table-img-3">';			
				/*
				 * se lo stato e' Y definitivo modifico l'articolo da joomla
				 */
				if($result['Supplier']['stato']=='T') 
					echo $this->Html->link(null, array('controller' => 'SuppliersOrganizationsJcontents', 'action' => 'edit', $result['SuppliersOrganization']['id'],'sort:'.$sort,'direction:'.$direction,'page:'.$page),array('class' => 'action actionJContent','title' => __('Edit JContent Temporary')));	
			?>	
			<?php echo $this->Html->link(null, array('action' => 'edit', $result['SuppliersOrganization']['id'],'sort:'.$sort,'direction:'.$direction,'page:'.$page),array('class' => 'action actionEdit','title' => __('Edit'))); ?>
			<?php echo $this->Html->link(null, array('action' => 'delete', $result['SuppliersOrganization']['id'],'sort:'.$sort,'direction:'.$direction,'page:'.$page),array('class' => 'action actionDelete','title' => __('Delete'))); ?>
			</td>
		</tr>
		<tr class="trView" id="trViewId-<?php echo $result['SuppliersOrganization']['id'];?>">
			<td colspan="2"></td>
			<td colspan="<?php echo ($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') ? '15' :'14';?>" id="tdViewId-<?php echo $result['SuppliersOrganization']['id'];?>"></td>
		</tr>
<?php endforeach; ?>
	</table>


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
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => "Non ci sono ancora produttori registrati"));
		
echo '</div>';
?>
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