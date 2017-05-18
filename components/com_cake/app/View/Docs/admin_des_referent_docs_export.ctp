<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('Des'),array('controller' => 'De', 'action' => 'index'));
$this->Html->addCrumb(__('List DesOrders'),array('controller' => 'DesOrders', 'action' => 'index'));
if(isset($des_order_id) && !empty($des_order_id))
	$this->Html->addCrumb(__('Order home DES'),array('controller'=>'DesOrdersOrganizations','action'=>'index', null, 'des_order_id='.$des_order_id));
$this->Html->addCrumb(__('Export Docs DesOrder'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery("input[name='doc_options']").change(function() {
		var des_supplier_id = jQuery('#des_order_id').val();
		var doc_options = jQuery("input[name='doc_options']:checked").val();
                /*
                 * attivo le opzioni
                 */
                if(doc_options!=undefined) {
                    var id = jQuery("input[name='doc_options']:checked").attr('id');
                    jQuery('.box-options').hide();
                    jQuery('#setting-'+id).show();
                }
		AjaxCallToDocPreview();
	});

	jQuery('#actionExportDoc').click(function() {
		var des_order_id = jQuery('#des_order_id').val();
		var doc_options = jQuery("input[name='doc_options']:checked").val();
		var doc_formato = jQuery("input[name='doc_formato']:checked").val();

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
		jQuery('#actionExportDoc').attr('href', url);

		return true;
	});
});


/*
 * chiamata Ajax per anteprima documento
 */
function AjaxCallToDocPreview() {
	var des_order_id = jQuery('#des_order_id').val();
	var doc_options = jQuery("input[name='doc_options']:checked").val();
	var doc_formato = jQuery("input[name='doc_formato']:checked").val();
	
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
	
	<div id="doc-options">
		<div class="left label">Opzioni formato</div>
		<div class="left radio">
			<p>
				<input type="radio" name="doc_options" id="des-referent-to-supplier" value="des-referent-to-supplier" /><label for="des-referent-to-supplier"><?php echo __('des_referent_to_supplier');?></label>
			</p>
			<?php
			if($isToValidate || $toQtaMassima || $toQtaMinimaOrder) { 
				echo '<p>';
				echo' <input type="radio" name="doc_options" id="des-referent-to-supplier-monitoring" value="des-referent-to-supplier-monitoring" /><label for="des-referent-to-supplier-monitoring">'.__('des_referent_to_supplier_monitoring').'</label>';
				echo '</p>';
			}
			?>					
			<p>
				<input type="radio" name="doc_options" id="des-referent-to-supplier-details" value="des-referent-to-supplier-details" /><label for="des-referent-to-supplier-details"><?php echo __('des_referent_to_supplier_details');?></label>
			</p>		
			<p>
				<input type="radio" name="doc_options" id="des-referent-to-supplier-split-org" value="des-referent-to-supplier-split-org" /><label for="des-referent-to-supplier-split-org"><?php echo __('des_referent_to_supplier_split_org');?></label>
			</p>
			<?php
			if($isToValidate || $toQtaMassima || $toQtaMinimaOrder) { 
				echo '<p>';
				echo' <input type="radio" name="doc_options" id="des-referent-to-supplier-split-org-monitoring" value="des-referent-to-supplier-split-org-monitoring" /><label for="des-referent-to-supplier-split-org-monitoring">'.__('des_referent_to_supplier_split_org_monitoring').'</label>';
				echo '</p>';
			}
			?>			
		</div>

                <style type="text/css">
                .box-options {
                        border:1px solid #DEDEDE;
                        border-radius:8px;
                        margin:10px;
                        padding:8px; 
                        display:none;
                }
                </style>
                <div class="left setting" style="width:35%;">

            <div id="setting-des-referent-to-supplier" class="box-options">
			<p>
				<label for="codice1" style="width:auto !important;">Visualizzo il <b>codice</b> dell'articolo</label>&nbsp;&nbsp;
				<input type="radio" name="codice1" id="codice1_N" value="N" /> No
				<input type="radio" name="codice1" id="codice1_Y" value="Y" checked /> Si
			</p>                        
            </div>
            
            <div id="setting-des-referent-to-supplier-monitoring" class="box-options">
			<p>
				<label for="codice5" style="width:auto !important;">Visualizzo il <b>codice</b> dell'articolo</label>&nbsp;&nbsp;
				<input type="radio" name="codice5" id="codice5_N" value="N" /> No
				<input type="radio" name="codice5" id="codice5_Y" value="Y" checked /> Si
			</p>                        
            </div>
	
            <div id="setting-des-referent-to-supplier-details" class="box-options">
			<p>
				<label for="codice2" style="width:auto !important;">Visualizzo il <b>codice</b> dell'articolo</label>&nbsp;&nbsp;
				<input type="radio" name="codice2" id="codice2_N" value="N" /> No
				<input type="radio" name="codice2" id="codice2_Y" value="Y" checked /> Si
			</p>
		
                        <!-- p>
                                <label for="pezzi_confezione" style="width:auto !important;">Visualizzo i <b>colli</b> degli articoli</label>&nbsp;&nbsp;
                                <input type="radio" name="pezzi_confezione" id="pezzi_confezione_N" value="N" checked /> No
                                <input type="radio" name="pezzi_confezione" id="pezzi_confezione_Y" value="Y" /> Si
                        </p -->
                    </div>
                    

            <div id="setting-des-referent-to-supplier-split-org" class="box-options">
			<p>
				<label for="codice3" style="width:auto !important;">Visualizzo il <b>codice</b> dell'articolo</label>&nbsp;&nbsp;
				<input type="radio" name="codice3" id="codice3_N" value="N" /> No
				<input type="radio" name="codice3" id="codice3_Y" value="Y" checked /> Si
			</p>                        
            </div>
            
            <div id="setting-des-referent-to-supplier-split-org-monitoring" class="box-options">
			<p>
				<label for="codice4" style="width:auto !important;">Visualizzo il <b>codice</b> dell'articolo</label>&nbsp;&nbsp;
				<input type="radio" name="codice4" id="codice4_N" value="N" /> No
				<input type="radio" name="codice4" id="codice4_Y" value="Y" checked /> Si
			</p>                        
            </div>
            
                                
	       </div>
        </div>


	<div id="doc-print">
	
		<h2 class="ico-export-docs">
			<?php echo __('Print Doc');?>
			<div class="actions-img">
				<ul>
					<li><?php echo $this->Form->input('typeDoc', array(
										 'id' => 'typeDoc',
								         'type' => 'radio',
								         'name' => 'doc_formato',
								         'fieldset' => false,
								         'legend' => false,
								         'div' => array('class' => ''),
								         'options' => array('PDF'=>'Pdf','CSV'=>'Csv','EXCEL'=>'Excel'),
								         'default' => 'PDF',
								   ));
						?>
					</li>
					<li><?php echo $this->Html->link(__('Print Doc'), '' ,array('id' => 'actionExportDoc', 'class' => 'action actionPrinter blank', 'title' => __('Print Doc'))); ?></li>
				</ul>
			</div>
		</h2>	
	<div/>

	<div id="doc-preview" style="min-height:50px;background: rgba(0, 0, 0, 0) none repeat scroll 0 0;"></div>
	
	<?php
	echo $this->Form->hidden('des_order_id',array('id' => 'des_order_id','value' => $des_order_id));
	?>
	
	</fieldset>
</div>

<?php
echo $this->element('menuDesOrderLaterale');
?>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("input[name='codice1']").change(function() {			
		AjaxCallToDocPreview();
	});
	jQuery("input[name='codice2']").change(function() {			
		AjaxCallToDocPreview();
	});
	jQuery("input[name='codice3']").change(function() {			
		AjaxCallToDocPreview();
	});
	jQuery("input[name='codice4']").change(function() {			
		AjaxCallToDocPreview();
	});
	jQuery("input[name='codice5']").change(function() {			
		AjaxCallToDocPreview();
	});
	jQuery("input[name='pezzi_confezione']").change(function() {			
		AjaxCallToDocPreview();
	});
});
</script>