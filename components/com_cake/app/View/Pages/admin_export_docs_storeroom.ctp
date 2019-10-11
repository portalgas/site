<div class="docs">
<?php echo $this->Form->create();?>
	<fieldset style="min-height:600px;">
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th colspan="2">Tipologia di documento</th>
		<th>Filtro</th>
		<th>Preview</th>
		<th>Formato pdf</th>
		<th>Formato csv</th>
		<th>Formato excel</th>
	</tr>
	
	<?php
	echo $this->element('reportStoreroom', ['isUserCurrentStoreroom' => $isUserCurrentStoreroom, 
											'isManager' => $isManager, 
											'deliveries' => $deliveriesStorerooms,
											'preview' => true]);
	?>
	</table>
	
	<div class="clearfix" id="doc-preview" style="display:none;"></div>
	
	</fieldset>
</div>