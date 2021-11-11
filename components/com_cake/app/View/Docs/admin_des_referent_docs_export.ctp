<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('Des'),array('controller' => 'De', 'action' => 'index'));
$this->Html->addCrumb(__('List DesOrders'),array('controller' => 'DesOrders', 'action' => 'index'));
if(isset($des_order_id) && !empty($des_order_id))
	$this->Html->addCrumb(__('Order home DES'),array('controller'=>'DesOrdersOrganizations','action'=>'index', null, 'des_order_id='.$des_order_id));
$this->Html->addCrumb(__('Export Docs DesOrder'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<script type="text/javascript">
$(document).ready(function() {

	$("input[name='doc_options']").change(function() {
		var des_supplier_id = $('#des_order_id').val();
		var doc_options = $("input[name='doc_options']:checked").val();
                /*
                 * attivo le opzioni
                 */
                if(doc_options!=undefined) {
                    var id = $("input[name='doc_options']:checked").attr('id');
                    $('.box-options').hide();
                    $('#setting-'+id).show();
                }
		AjaxCallToDocPreview();
	});

	$('#actionExportDoc').click(function() {
		var des_order_id = $('#des_order_id').val();
		var doc_options = $("input[name='doc_options']:checked").val();
		var doc_formato = $("input[name='data[typeDoc][doc_formato]']:checked").val();

		if(doc_options==null) {
			alert("<?php echo __('jsAlertPrintFormatRequired');?>");
			return false;
		}
		if(doc_formato==null) {
			alert("<?php echo __('jsAlertPrintTypeRequired');?>");
			return false;
		}

                var parametersFilter = setExportDocsParameters(doc_options);

		var url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToDes&des_order_id='+des_order_id+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&'+parametersFilter+'&format=notmpl';
		/* console.log(url); */
		$('#actionExportDoc').attr('href', url);

		return true;
	});
});


/*
 * chiamata Ajax per anteprima documento
 */
function AjaxCallToDocPreview() {
	var des_order_id = $('#des_order_id').val();
	var doc_options = $("input[name='doc_options']:checked").val();
	var doc_formato = $("input[name='data[typeDoc][doc_formato]']:checked").val();
	
	if(des_order_id =='' || doc_options=='') return;
	
        var parametersFilter = setExportDocsParameters(doc_options);
        
	var url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToDes&des_order_id='+des_order_id+'&doc_options='+doc_options+'&doc_formato=PREVIEW&'+parametersFilter+'&format=notmpl';
	/* console.log('AjaxCallToDocPreview '+url); */
	var idDivTarget = 'doc-preview';
	ajaxCallBox(url, idDivTarget);	
}
</script>

<h2 class="ico-export-docs">
	<?php echo __('Export Docs DesOrder');?>
</h2>


<div class="docs">
<?php 
echo $this->element('boxDesOrder', array('results' => $desOrdersResults));	
echo $this->Form->create();
?>
	<fieldset>
	
	<div class="row">
	<div class="col-md-1"></div>	
	<div class="col-md-6">
	
		<label class="control-label">Opzioni</label>

		<div class="radio">
			<label><input type="radio" name="doc_options" id="des-referent-to-supplier" value="des-referent-to-supplier" /><?php echo __('des_referent_to_supplier');?></label>
		</div>
		<?php
		if($isToValidate || $toQtaMassima || $toQtaMinimaOrder) { 
		?>		
		<div class="radio">
			<label><input type="radio" name="doc_options" id="des-referent-to-supplier-monitoring" value="des-referent-to-supplier-monitoring" /><?php echo __('des_referent_to_supplier_monitoring');?></label>
		</div>
		<?php
		}
		?>		
		<div class="radio">
			<label><input type="radio" name="doc_options" id="des-referent-to-supplier-details" value="des-referent-to-supplier-details" /><?php echo __('des_referent_to_supplier_details');?></label>
		</div>
		<div class="radio">
			<label><input type="radio" name="doc_options" id="des-referent-to-supplier-split-org" value="des-referent-to-supplier-split-org" /><?php echo __('des_referent_to_supplier_split_org');?></label>
		</div>
		<?php
		if($isToValidate || $toQtaMassima || $toQtaMinimaOrder) {
		?>		
		<div class="radio">
			<label><input type="radio" name="doc_options" id="des-referent-to-supplier-split-org-monitoring" value="des-referent-to-supplier-split-org-monitoring" /><?php echo __('des_referent_to_supplier_split_org_monitoring');?></label>
		</div>
		<?php
		}
		?>		
	</div>
	<div class="col-md-5">


            <div id="setting-des-referent-to-supplier" class="box-options">
			<div class="doc-options">
				<label class="control-label">Visualizzo il <b>codice</b> dell'articolo</label>&nbsp;&nbsp;
				<label class="radio-inline"><input type="radio" name="codice1" id="codice1_N" value="N" /> No</label>
				<label class="radio-inline"><input type="radio" name="codice1" id="codice1_Y" value="Y" checked /> Si</label>
			</div>                       
            </div>
            
            <div id="setting-des-referent-to-supplier-monitoring" class="box-options">
			<div class="doc-options">
				<label class="control-label">Visualizzo il <b>codice</b> dell'articolo</label>&nbsp;&nbsp;
				<label class="radio-inline"><input type="radio" name="codice5" id="codice5_N" value="N" /> No</label>
				<label class="radio-inline"><input type="radio" name="codice5" id="codice5_Y" value="Y" checked /> Si</label>
			</div>                        
            </div>
	
            <div id="setting-des-referent-to-supplier-details" class="box-options">
			<div class="doc-options">
				<label class="control-label">Visualizzo il <b>codice</b> dell'articolo</label>&nbsp;&nbsp;
				<label class="radio-inline"><input type="radio" name="codice2" id="codice2_N" value="N" /> No</label>
				<label class="radio-inline"><input type="radio" name="codice2" id="codice2_Y" value="Y" checked /> Si</label>
			</div>
		
                        <!-- <div class="doc-options">
                                <label for="pezzi_confezione" style="width:auto !important;">Visualizzo i <b>colli</b> degli articoli</label>&nbsp;&nbsp;
                                <label class="radio-inline"><input type="radio" name="pezzi_confezione" id="pezzi_confezione_N" value="N" checked /> No</label>
                                <label class="radio-inline"><input type="radio" name="pezzi_confezione" id="pezzi_confezione_Y" value="Y" /> Si</label>
                        </div -->
                    </div>
                    

            <div id="setting-des-referent-to-supplier-split-org" class="box-options">
			<div class="doc-options">
				<label class="control-label">Visualizzo il <b>codice</b> dell'articolo</label>&nbsp;&nbsp;
				<label class="radio-inline"><input type="radio" name="codice3" id="codice3_N" value="N" /> No</label>
				<label class="radio-inline"><input type="radio" name="codice3" id="codice3_Y" value="Y" checked /> Si</label>
			</div>                        
            </div>
            
            <div id="setting-des-referent-to-supplier-split-org-monitoring" class="box-options">
			<div class="doc-options">
				<label class="control-label">Visualizzo il <b>codice</b> dell'articolo</label>&nbsp;&nbsp;
				<label class="radio-inline"><input type="radio" name="codice4" id="codice4_N" value="N" /> No</label>
				<label class="radio-inline"><input type="radio" name="codice4" id="codice4_Y" value="Y" checked /> Si</label>
			</div>                        
            </div>
            
                                
	</div>
    </div> <!-- row -->
	
	<?php
	echo '<div class="row">';
	echo '<h2 class="ico-export-docs">';
	echo __('Print Doc');
	echo '<div class="actions-img">';
	echo '<ul>';
	echo '<li>';
	echo $this->App->drawFormRadio('typeDoc','doc_formato',array('options' => array('PDF'=>'Pdf','CSV'=>'Csv','EXCEL'=>'Excel'), 
										'value'=> 'PDF', 'label' => false, 'default' => 'PDF'));		
	echo '</li>';
	echo '<li style="padding-left:25px;">';
	echo $this->Html->link(__('Print Doc'), '' ,array('id' => 'actionExportDoc', 'class' => 'action actionPrinter blank', 'title' => __('Print Doc')));
	echo '</li>';
	echo '</ul>';
	echo '</div>';
	echo '</h2>';
	echo '</div> <!-- row -->';	 
	?>
	
	<div id="doc-preview" style="min-height:50px;background: rgba(0, 0, 0, 0) none repeat scroll 0 0;"></div>
	
	<?php
	echo $this->Form->hidden('des_order_id',array('id' => 'des_order_id','value' => $des_order_id));
	?>
	
	</fieldset>
</div>

<?php
echo $this->element('menuDesOrderLaterale');
?>
<style type="text/css">
.box-options {
	border:1px solid #DEDEDE;
	border-radius:8px;
	margin:10px;
	padding:8px; 
	display:none;
}
</style>
<script type="text/javascript">
$(document).ready(function() {
	$("input[name='codice1']").change(function() {			
		AjaxCallToDocPreview();
	});
	$("input[name='codice2']").change(function() {			
		AjaxCallToDocPreview();
	});
	$("input[name='codice3']").change(function() {			
		AjaxCallToDocPreview();
	});
	$("input[name='codice4']").change(function() {			
		AjaxCallToDocPreview();
	});
	$("input[name='codice5']").change(function() {			
		AjaxCallToDocPreview();
	});
	$("input[name='pezzi_confezione']").change(function() {			
		AjaxCallToDocPreview();
	});
});
</script>