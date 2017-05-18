<div class="docs">
<?php echo $this->Form->create();?>
	<fieldset style="min-height:600px;">
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th colspan="2">Tipologia di documento</th>
		<th>Filtro</th>
		<th>Formato pdf</th>
		<th>Formato csv</th>
		<th>Formato excel</th>
	</tr>
	
	<?php
	echo $this->element('reportStoreroom');
	?>
	
	</table>
	</fieldset>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('.exportStoreroom').click(function() {
		var id =  jQuery(this).attr('id');
		idArray = id.split('-');
		var id =  jQuery(this).attr('id');
		var action      = idArray[0];
		var doc_formato = idArray[1];
				
		window.open('/administrator/index.php?option=com_cake&controller=Storerooms&action='+action+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');	
	});

});
</script>