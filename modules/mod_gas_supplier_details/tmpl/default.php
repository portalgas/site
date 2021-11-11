<?php
// no direct access
defined('_JEXEC') or die;

/*
 * organization_id lo prendo come parametro dal template e glielo passo alla chiamata ajax
 * perche' dopo in AppController organization_id del template e' sempre 0   
 */
if(!empty($j_content_id)) {

	if($organization_id==0)
		$url = "/?option=com_cake&controller=Ajax&action=modules_supplier_details&j_content_id=".$j_content_id."&format=notmpl";
	else 
		$url = "/?option=com_cake&controller=Ajax&action=modules_suppliers_organization_details&organization_id=".$organization_id."&j_content_id=".$j_content_id."&format=notmpl"; 
				
	?>
	<script type="text/javascript">
	jQuery('#supplierDetails').css('background', 'url("/images/cake/ajax-loader.gif") no-repeat scroll center 0 transparent');
	
	jQuery(document).ready(function() {
		jQuery.ajax({
				type: "GET",
				url: "<?php echo $url;?>",
				data: "",
				success: function(response){
					jQuery('#supplierDetails').css('background', 'none repeat scroll 0 0 transparent');
					jQuery('#supplierDetails').html(response);
				},
				error:function (XMLHttpRequest, textStatus, errorThrown) {}
		});
	});
	</script>
	<div id="supplierDetails" class="cakeContainer"></div>	
<?php 
}
?>