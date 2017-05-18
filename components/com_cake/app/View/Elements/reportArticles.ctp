<?php
if($type=='FE') {
?>

<?php
}
else {
?>
	<tr>
		<td><a action="Articles" class="actionTrConfig openTrConfig" href="#" title="<?php echo __('Href_title_expand_config');?>"></a></td>
		<td>Tutti gli <b>articoli</b> del produttore</td>
		<td>
			<?php
				$options = array(
						 'data-placeholder' => 'Scegli il produttore',
						 'label' => false,
						 'id'=>'supplier_organization_id',								 
						 'options' => $suppliersOrganization,
						 'empty' => 'Scegli il produttore',
						 'escape' => false);
				if(count($suppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
					$options += array('class'=> 'selectpicker', 'data-live-search' => true); 				
				echo $this->Form->input('supplier_organization_id',$options);
			?>
		</td>
		<td><a class="exportArticles" id="articlesSupplierOrganization-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli del produttore <?php echo __('formatFilePdf');?>"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
		<td><a class="exportArticles" id="articlesSupplierOrganization-CSV" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli del produttore <?php echo __('formatFileCsv');?>"><img alt="CSV" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/spreadsheet.png"></a></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="exportArticles" id="articlesSupplierOrganization-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli del produttore '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
			?>
		</td>		
	</tr>
	<tr class="trConfig" id="trConfigId-Articles">
		<td></td>
		<td colspan="5" id="tdConfigId-Articles">
			
			<div class="left label" style="width:125px !important;">Opzioni stampa</div>
			<div class="left radio">
				<p>
					<label for="filterType">Visualizza le tipologie</label>
					<input type="radio" id="filterTypeY1" name="filterType1" value="Y" checked /><label for="filterTypeY1W">Si</label>
					<input type="radio" id="filterTypeN1" name="filterType1" value="N" /><label for="filterTypeN1">No</label>
				</p>
				<p>
					<label for="filterCategory">Visualizza le categorie</label>
					<input type="radio" id="filterCategoryY1" name="filterCategory1" value="Y" checked /><label for="filterCategoryY1">Si</label>
					<input type="radio" id="filterCategoryN1" name="filterCategory1" value="N" /><label for="filterCategoryN1">No</label>
				</p>	
				<p>
					<label for="filterNota">Visualizza le note</label>
					<input type="radio" id="filterNotaY1" name="filterNota1" value="Y" checked /><label for="filterNota1">Si</label>
					<input type="radio" id="filterNotaN1" name="filterNota1" value="N" /><label for="filterNotaN1">No</label>
				</p>
				<?php 
				if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y') {
				?>
				<p>
					<label for="filterIngredientiY">Visualizza gli ingredienti</label>
					<input type="radio" id="filterIngredientiY1" name="filterIngredienti1" value="Y" checked /><label for="filterIngredientiY1">Si</label>
					<input type="radio" id="filterIngredientiN1" name="filterIngredienti1" value="N" /><label for="filterIngredientiN1">No</label>
				</p>
				<?php 
				}
				?>							
			</div>							
		</td>
	</tr>


	<script type="text/javascript">
	jQuery(document).ready(function() {
	
		jQuery('.exportArticles').click(function() {
			var supplier_organization_id = jQuery('#supplier_organization_id').val();
			if(supplier_organization_id=="") {
				alert("<?php echo __('jsAlertSupplierRequired');?>");
				return false;
			}
			
			var id =  jQuery(this).attr('id');
			idArray = id.split('-');
			var action      = idArray[0];
			var doc_formato = idArray[1];
	
			/*
			 * filtri
			 */
			var filterType = jQuery("input[name='filterType1']:checked").val();
			var filterCategory = jQuery("input[name='filterCategory1']:checked").val();
			var filterNota = jQuery("input[name='filterNota1']:checked").val();
			var filterIngredienti = 'N';
			<?php 
			if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y') {
			?>
			filterIngredienti = jQuery("input[name='filterIngredienti1']:checked").val();	
			<?php 
			}
			?>
					
			window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&supplier_organization_id='+supplier_organization_id+'&filterType='+filterType+'&filterCategory='+filterCategory+'&filterNota='+filterNota+'&filterIngredienti='+filterIngredienti+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');	
		});
	
	});
	</script>
<?php
}
?>
