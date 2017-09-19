<?php 
echo '<div class="storerooms" id="ajaxContent">';

echo '<h2>'.__('Storeroom').'</h2>';

/* ******************************************************
echo $this->Form->create('FilterStoreroom',array('id'=>'formGasFilter','type'=>'get'));
?>
	<fieldset class="filter">
		<legend><?php echo __('Filter Storeroom'); ?></legend>
		<table>
			<tr>
				<td>
					<?php 
					$options = array('label' => false, 'options' => $suppliersOrganizations,
									 'empty' => 'Filtra per produttore','name'=>'FilterStoreroomSupplierId',
									 'default'=>$FilterStoreroomSupplierId,'escape' => false);
					
					if(count($suppliersOrganizations) > Configure::read('HtmlSelectWithSearchNum')) 
						$options += array('class'=> 'selectpicker', 'data-live-search' => true); 						
					echo $this->Form->input('supplier_organization_id', $options); ?>
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
****************************************************** */
?>
	
<div class="table">
	<table class="table table-hover">
		<thead>
			<tr>
				<th><?php echo __('N');?></th>
				<th colspan="2"><?php echo __('Name');?></th>
				<th><?php echo __('Conf');?></th>
				<th><?php echo __('PrezzoUnita');?></th>
				<th><?php echo __('Prezzo/UM');?></th>
				<th><?php echo __('qta');?></th>

				<?php
				if($user->organization['Organization']['hasStoreroomFrontEnd']=='Y')
					echo '<th class="actions">'.__('Actions').'</th>';
				?>
		</tr>
		</thead>
		<tbody>
	<?php
	$supplier_organization_id_old = 0;
	foreach ($results as $i => $result): 
		/*
		echo "<pre>";
		print_r($result);
		echo "</pre>";
		*/
		if($result['SuppliersOrganization']['id']!=$supplier_organization_id_old) {
			echo '<tr>';
			echo '<td colspan="'.($user->organization['Organization']['hasStoreroomFrontEnd']=='Y'?'8':'7').'" class="trGroup">'.__('Supplier').': ';
			echo '	<span>';
			if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
				echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
			echo '	</span>';			
			echo $result['SuppliersOrganization']['name'];
			echo '</td>';
			echo '</tr>';
		}
	
		echo '<tr>';
		echo '<td>'.($i+1).'</td>';
		echo '<td>';
		if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
			echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
		}		
		echo '</td>';		
		echo '<td>';	
		echo $result['Storeroom']['name']; 
		echo '</td>';
		echo '<td>'.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'</td>';
		echo '<td>'.number_format($result['Storeroom']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;</td>';
		echo '<td>'.$this->App->getArticlePrezzoUM($result['Storeroom']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']).'</td>';
		echo '<td>';
		echo '<b>'.$result['Storeroom']['qta'].'</b> in dispensa';
		echo '</td>';
		if($user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
			echo '<td class="actions-table">';
			//echo $this->Html->link('Acquista il prodotto dalla dispensa', array('action' => 'storeroomToUser', $storeroom['Storeroom']['id']),array('title' => __('Storeroom To User')));
			//echo '<a title="Acquista il prodotto dalla dispensa" href="/storeroomToUser?id='.$storeroom['Storeroom']['id'].'">'.__('Storeroom To User').'</a>';
			echo '<a title="Acquista il prodotto dalla dispensa" href="javascript:viewContentAjax(\''.$result['Storeroom']['id'].'\')">Acquista il prodotto dalla dispensa</a>';
			echo '</td>';
		}			
		?>
	</tr>
<?php 
	$supplier_organization_id_old=$result['SuppliersOrganization']['id'];
	endforeach; ?>
	
		</tbody>
</table>

	</div>
</div>
	

<script type="text/javascript">
<?php
if($user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
?>
	function viewContentAjax(id) {
		$('#ajaxContent').animate({opacity:0});
		var url = "/?option=com_cake&controller=Storerooms&action=storeroomToUser&id="+id+"&format=notmpl";
		$('#ajaxContent').load(url);
		$('#ajaxContent').animate({opacity:1},1500);
		return;
	}
<?php
}
?>


$(document).ready(function() {
	<?php 
	/*
	 * devo ripulire il campo hidden che inizia per page perche' dopo la prima pagina sbaglia la ricerca con filtri
	 */
	?>
	$('.filter').click(function() {
		$("input[name^='page']").val('');
	});
	
});		
</script>
