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
				$options = ['label' => false,
							'id'=>'supplier_organization_id',								 
							'options' => $suppliersOrganization,
							'escape' => false];
				if(count($suppliersOrganization) > 1)
					$options += ['data-placeholder' => __('FilterToSuppliers'), 'empty' => __('FilterToSuppliers')]; 
				if(count($suppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
					$options += ['class'=> 'selectpicker', 'data-live-search' => true]; 				
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
			
			<p>Opzioni stampa</p>

			<div class="input ">
				<label class="control-label" for="filterType">Visualizza le tipologie </label>
				<label class="radio-inline" for="filterTypeY1">
					<input checked="checked" value="Y" id="filterTypeY1" name="filterType1" type="radio"> Si</label>
				<label class="radio-inline" for="filterTypeN1">
					<input value="N" id="filterTypeN1" name="filterType1" type="radio"> No</label>
			</div>
			<div class="input ">
				<label class="control-label" for="filterCategory">Visualizza le categorie </label>
				<label class="radio-inline" for="filterCategoryY1">
					<input checked="checked" value="Y" id="filterCategoryY1" name="filterCategory1" type="radio"> Si</label>
				<label class="radio-inline" for="filterCategoryN1">
					<input value="N" id="filterCategoryN1" name="filterCategory1" type="radio"> No</label>
			</div>
			<div class="input ">
				<label class="control-label" for="filterNota">Visualizza le note </label>
				<label class="radio-inline" for="filterNotaY1">
					<input checked="checked" value="Y" id="filterNotaY1" name="filterNota1" type="radio"> Si</label>
				<label class="radio-inline" for="filterNotaN1">
					<input value="N" id="filterNotaN1" name="filterNota1" type="radio"> No</label>
			</div>
			<?php 
			if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y') {
			?>			
				<div class="input ">
					<label class="control-label" for="filterIngredienti">Visualizza gli ingredienti </label>
					<label class="radio-inline" for="filterIngredientiY1">
						<input checked="checked" value="Y" id="filterIngredientiY1" name="filterIngredienti1" type="radio"> Si</label>
					<label class="radio-inline" for="filterIngredientiN1">
						<input value="N" id="filterIngredientiN1" name="filterIngredienti1" type="radio"> No</label>
				</div>
			<?php 
			}
			?>											
		</td>
	</tr>


	<script type="text/javascript">
	$(document).ready(function() {
	
		$('.exportArticles').click(function() {
			var supplier_organization_id = $('#supplier_organization_id').val();
			if(supplier_organization_id=="") {
				alert("<?php echo __('jsAlertSupplierRequired');?>");
				return false;
			}
			
			var id =  $(this).attr('id');
			idArray = id.split('-');
			var action      = idArray[0];
			var doc_formato = idArray[1];
	
			/*
			 * filtri
			 */
			var filterType = $("input[name='filterType1']:checked").val();
			var filterCategory = $("input[name='filterCategory1']:checked").val();
			var filterNota = $("input[name='filterNota1']:checked").val();
			var filterIngredienti = 'N';
			<?php 
			if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y') {
			?>
			filterIngredienti = $("input[name='filterIngredienti1']:checked").val();	
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
