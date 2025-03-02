<?php
// no direct access
defined('_JEXEC') or die;
?>
<form role="select" class="navbar-form" accept-charset="utf-8" method="get" id="organizationChoiceForm" action="<?php echo $_SERVER['PHP_SELF'];?>">
	<fieldset style="border: medium none;">
		<legend style="display:none;">Scegli l'organizzazione</legend>
			<select id="org_id" name="org_id" size="1" data-live-search="true" class="selectpicker">
				<?php 
				if($organization_id==0) 
					echo '<option selected value="0">Scegli il Gas</option>';
								
				foreach ($list as $item) {
					echo '<option ';
					if($organization_id==$item->id) echo 'selected';
					echo ' value="'.$item->id.'">'.$item->name;
					
					echo ' - '.$item->localita.' ('.$item->provincia.')';
					
					echo '</option>';
				}?>
			</select>
	</fieldset>
</form>

<script type="text/javascript">
jQuery('#org_id').change(function() {
	var org_id = jQuery('#org_id').val();

	<?php 
	if(isset($_SERVER['HTTP_HOST']))
		$host = 'http://'.$_SERVER['HTTP_HOST'];
	else 
		$host = '';
	foreach ($list as $item) {
		echo 'if(org_id=='.$item->id.')';
		echo "\r\n";
		echo 'jQuery("#organizationChoiceForm").attr("action", "/home-'.$item->j_seo.'");';
        // echo 'jQuery("#organizationChoiceForm").attr("action", "http://neo.portalgas.local/gas/'.$item->j_seo.'/home");';
		echo "\r\n";
		echo 'else';
		echo "\r\n";
	}?>
	if(org_id==0) 
		jQuery('#organizationChoiceForm').attr('action','<?php echo $_SERVER['PHP_SELF'];?>');
	
	jQuery('#organizationChoiceForm').submit();	
});
</script>