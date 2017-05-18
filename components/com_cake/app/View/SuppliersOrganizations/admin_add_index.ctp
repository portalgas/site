<?php
/*
echo $this->Html->script('datatables.min');
echo $this->Html->css('datatables.min');
*/
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
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
echo $this->Form->create('FilterSupplier',array('id'=>'formGasFilter','type'=>'get'));?>
	<fieldset class="filter" style="padding:5px;">
		<legend><?php echo __('Filter Suppliers'); ?></legend>
		<table>
			<tr>
				<?php 
				echo '<td>';
				echo $this->Form->input('category_supplier_id',array('label' => false,'options' => $categories,'empty' => 'Filtra per categoria','name'=>'FilterSuppliersOrganizationCategoryId','default'=>$FilterSuppliersOrganizationCategoryId,'escape' => false)); 
				echo '</td>';
				echo '<td>';
				echo $this->Form->input('provincia',array('label' => false,'options' => $filterProvinciaResults,'empty' => 'Filtra per provincia','name'=>'FilterSuppliersOrganizationProvincia','default'=>$FilterSuppliersOrganizationProvincia,'escape' => false)); 
				echo '</td>';
				echo '<td>';
				echo $this->Form->input('cap',array('label' => false,'options' => $filterCapResults,'empty' => 'Filtra per CAP','name'=>'FilterSuppliersOrganizationCap','default'=>$FilterSuppliersOrganizationCap,'escape' => false)); 
				echo '</td>';
				/*
				echo $this->Ajax->autoComplete('FilterSuppliersOrganizationName', 
														   Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteSuppliers_name&format=notmpl',
							   								array('label' => 'Nome','name'=>'FilterSuppliersOrganizationName','value'=>$FilterSuppliersOrganizationName,'size'=>'75','escape' => false));
				*/
				echo '<td>';
				echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none')));
				echo '</td>';
			echo '</tr>';
			echo '<tr>';
			echo '<td colspan="4" style="text-align:center">';
			echo '<input type="text" id="search" placeholder="Ricerca tra i dati sottostante" style="font-size:20px;padding: 2px 3px; width: 90%;">';
			echo '</td>';
			echo '</tr>';
		echo '</table>';
	echo '</fieldset>';	
echo $this->Form->create('SuppliersOrganization', array('id' => 'formGas'));?>
	<fieldset>
		<legend></legend>
		
			<?php
			if(!empty($results)) {
			?>
			<table id="grid" class="display" cellspacing="0" width="100%">
			<thead>
				<tr>
						<?php if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') echo '<th>Categoria</th>';?>
						<th></th>
						<th>Ragione sociale</th>
						<th>Descrizione</th>
						<th>Localit&agrave;</th>
						<th>Contatti</th>
						<th class="actions"><?php echo __('Actions');?></th>
				</tr>
			</thead>	
				<?php
				foreach ($results as $i => $result):
				 ?>
				<tbody>
				<tr class="view">
					<?php 
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
						echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" />';
					echo '</td>';
					?>
					
					<td><?php echo $result['Supplier']['name']; ?></td>
					<td><?php echo $result['Supplier']['descrizione']; ?></td>
					<td>
						<?php 
						   if(!empty($result['Supplier']['indirizzo'])) echo $result['Supplier']['indirizzo'].'&nbsp;<br />';
						   if(!empty($result['Supplier']['localita'])) echo $result['Supplier']['localita'].'&nbsp;';
							if(!empty($result['Supplier']['cap'])) echo $result['Supplier']['cap'].'&nbsp;';
							if(!empty($result['Supplier']['provincia'])) echo '('.$result['Supplier']['provincia'].')'; 
						?>
					</td>
					<td>
						<?php echo $result['Supplier']['telefono'];
							if(!empty($result['Supplier']['telefono2'])) echo '<br />'.$result['Supplier']['telefono2'];
							if(!empty($result['Supplier']['mail'])) echo '<br /><a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['Supplier']['mail'].'" class="link_mailto"></a>';
						?>
					</td>
					<td><a action="suppliers-<?php echo $result['Supplier']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
				</tr>
				<tr class="trView" id="trViewId-<?php echo $result['Supplier']['id'];?>" style="display:none;">
					<td></td>
					<td colspan="<?php  echo ($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') ? '6': '5';?>" id="tdViewId-<?php echo $result['Supplier']['id'];?>"></td>
				</tr>
				</tbody>				
			<?php endforeach; 		
			echo '</table>';
		
		} // end if(!empty($results))

		/*
		 * gestisce se importare Supplier o Supplier e Articles
		 */
		echo $this->Form->hidden('supplier_articles',array('id' => 'supplier_articles','value' => 'N'));
					
		echo $this->element('legendaSuppliersOrganizationsAdd', array('results' => $results, 'sort' => $sort,'direction' => $direction, 'page' => $page));			
		?>
		
	</fieldset>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Suppliers Organization'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	var $rows = jQuery('table#grid tbody tr');
	jQuery('#search').keyup(function() {
		var val = jQuery.trim(jQuery(this).val()).replace(/ +/g, ' ').toLowerCase();
		
		$rows.show().filter(function() {
			var text = jQuery(this).text().replace(/\s+/g, ' ').toLowerCase();
			return !~text.indexOf(val);
		}).hide();		
	});
} );
</script>