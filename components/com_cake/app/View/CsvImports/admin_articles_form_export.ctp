<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Articles'), array('controller' => 'Articles', 'action' => 'context_articles_index'));
$this->Html->addCrumb(__('Csv Export-Import'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="csv">';
echo '<h2 class="ico-db-remove">';		
echo __('Csv Export-Import');
echo '<div class="actions-img">';			
echo '	<ul>';
echo '		<li>'.$this->Html->link(__('Csv Import-Export'), array('action' => 'articles_form_import'),array('class' => 'action actionAdd','title' => __('Csv Import-Export'))).'</li>';
echo '	</ul>';
echo '</div>';
echo '</h2>';


echo $this->Form->create();
echo '<fieldset class="filter">';
echo '<legend>'.__('ExportImport').'</legend>';	

$options = array('id'=>'supplier_organization_id', 'options' => $ACLsuppliersOrganization,'empty' => 'Filtra per produttore', 'default' => $supplier_organization_id, 'escape' => false);
if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum'))
    $options += array('class'=> 'selectpicker', 'data-live-search' => true);
echo $this->Form->input('supplier_organization_id', $options); 
?>

		<div id="doc-print">
			<h2 class="ico-export-docs">
				<?php echo __('Print Doc');?>
				<div class="actions-img">
					<ul>
						<li><?php echo $this->Form->input('typeDoc', array(
											 'type' => 'radio',
											 'name' => 'doc_formato',
											 'fieldset' => false,
											 'legend' => false,
											 'div' => array('class' => ''),
											 'options' => $typeDocOptions,
											 'default' => 'CSV',
									   ));
							?>
						</li>
						<li><?php echo $this->Html->link(__('Export Report'), '' ,array('id' => 'actionExportDoc', 'class' => 'action actionPrinter blank', 'title' => __('Export Report'))); ?></li>
					</ul>
				</div>
			</h2>		
		</div>
		
<?php
echo $this->element('legendaCsvExportImport');
echo $this->element('legendaCsvImport', array('array_um' => $array_um, 'rowsMax' => Configure::read('CsvImportRowsMaxArticles')));

echo '</fieldset>';

echo $this->Form->end();
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	
	jQuery(function() {
		jQuery(".blank").attr("target","_blank");
	});
	
	jQuery('#actionExportDoc').click(function() {
		var supplier_organization_id = jQuery('#supplier_organization_id').val();
		
		var doc_options = 'export_file_csv'; /* jQuery("input[name='doc_options']:checked").val(); */
		var doc_formato = jQuery("input[name='doc_formato']:checked").val();

		if(supplier_organization_id=='') {
			alert("<?php echo __('jsAlertSupplierRequired');?>");
			return false;
		}
		if(doc_options==null) {
			alert("<?php echo __('jsAlertPrintFormatRequired');?>");
			return false;
		}
		
		if(doc_formato==null) {
			alert("<?php echo __('jsAlertPrintTypeRequired');?>");
			return false;
		}

		url = '/administrator/index.php?option=com_cake&controller=CsvImports&action=articles_export&supplier_organization_id='+supplier_organization_id+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&format=notmpl';
		jQuery('#actionExportDoc').attr('href', url);
    	
		return true;
	});	
});
</script>