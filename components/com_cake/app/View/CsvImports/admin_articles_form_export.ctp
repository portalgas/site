<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
// $this->Html->addCrumb(__('List Articles'), array('controller' => 'Articles', 'action' => 'context_articles_index'));
$this->Html->addCrumb(__('List Articles'), array('controller' => 'Connects', 'action' => 'index', 'c_to' => 'admin/articles&a_to=index-quick'));
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

$options = ['id' => 'supplier_organization_id', 
			'options' => $ACLsuppliersOrganization, 
			'default' => $supplier_organization_id, 'escape' => false];
if(count($ACLsuppliersOrganization) > 1) 
	$options += ['data-placeholder'=> __('FilterToSuppliers'), 'empty' => __('FilterToSuppliers')];			
if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum'))
    $options += ['class'=> 'selectpicker', 'data-live-search' => true];
echo $this->Form->input('supplier_organization_id', $options); 
?>

		<div id="doc-print">
			<h2 class="ico-export-docs">
				<?php echo __('Print Doc');?>
				<div class="actions-img">
					<ul>
						<li>
							<?php echo $this->App->drawFormRadio('typeDoc', 'doc_formato', array('options' => $typeDocOptions,
											 'value' => 'CSV', 'label' => false));
							?>						
						</li>
						<li style="margin-left:25px;"><?php echo $this->Html->link(__('Export Report'), '' ,array('id' => 'actionExportDoc', 'class' => 'action actionPrinter blank', 'title' => __('Export Report'))); ?></li>
					</ul>
				</div>
			</h2>		
		</div>
		
<?php
echo $this->element('legendaCsvExportImport');
echo $this->element('legendaCsvImport', ['array_um' => $array_um, 'rowsMax' => Configure::read('CsvImportRowsMaxArticles'), 'viewVersionSimple' => false]);

echo '</fieldset>';

echo $this->Form->end();
?>
<script type="text/javascript">
$(document).ready(function() {
	
	$(function() {
		$(".blank").attr("target","_blank");
	});
	
	$('#actionExportDoc').click(function() {
		var supplier_organization_id = $('#supplier_organization_id').val();
		
		var doc_options = 'export_file_csv'; /* $("input[name='doc_options']:checked").val(); */
		var doc_formato = $("input[name='data[typeDoc][doc_formato]']:checked").val();

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
		$('#actionExportDoc').attr('href', url);
    	
		return true;
	});	
});
</script>