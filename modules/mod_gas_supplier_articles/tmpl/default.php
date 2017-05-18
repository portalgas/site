<?php
// no direct access
defined('_JEXEC') or die;

/* * organization_id lo prendo come parametro dal template e glielo passo alla chiamata ajax* perche' dopo in AppController organization_id del template e' sempre 0*/
if(!empty($j_content_id)) {
	?>
	<script type="text/javascript">
	jQuery('#supplierArticles').css('background', 'url("/images/cake/ajax-loader.gif") no-repeat scroll center 0 transparent');
	
	jQuery(document).ready(function() {
		jQuery.ajax({
				type: "GET",
				url: "/?option=com_cake&controller=Ajax&action=modules_supplier_articles&j_content_id=<?php echo $j_content_id;?>&format=notmpl",
				data: "",
				success: function(response) {
					jQuery('#supplierArticles').css('background', 'none repeat scroll 0 0 transparent');
					jQuery('#supplierArticles').html(response);
				},
				error:function (XMLHttpRequest, textStatus, errorThrown) {}
		});
	});
	</script>
	<br /><div id="supplierArticles" class="cakeContainer"></div>
<?php 
}
?>